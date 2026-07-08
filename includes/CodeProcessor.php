<?php namespace ProcessWire;

use Tracy\Debugger;

unset($this->wire('input')->cookie->tracyCodeError);
setcookie("tracyCodeError", "", time()-3600);
// Background execution support: accept run ID and make PHP resilient to connection abort
$tracyRunId = isset($_POST['runId']) ? preg_replace('/[^a-zA-Z0-9_.]/', '', $_POST['runId']) : '';
$tracyRunStatusDir = $this->wire('config')->paths->assets . 'TracyDebugger/console_runs/';
$tracyRunCacheDir = $this->wire('config')->paths->cache . 'TracyDebugger/console_runs/';
if($tracyRunId) {
    if(!is_dir($tracyRunStatusDir)) {
        wireMkdir($tracyRunStatusDir, true);
        file_put_contents($tracyRunStatusDir . '.htaccess', "Options -Indexes\n<IfModule mod_rewrite.c>\n    RewriteEngine On\n    RewriteRule ^(.+)\\.[0-9]+\\.json$ $1.json [L]\n</IfModule>\n<IfModule mod_headers.c>\n    Header set Cache-Control \"no-cache, no-store, must-revalidate\"\n</IfModule>\n");
    }
    if(!is_dir($tracyRunCacheDir)) wireMkdir($tracyRunCacheDir, true);
    /* write initial "running" status marker (no sensitive data — publicly accessible) */
    file_put_contents($tracyRunStatusDir . $tracyRunId . '.json', json_encode(array(
        'status' => 'running'
    )));
    /* PID lives in the non-public cache dir so the cancel endpoint can target
       this process without exposing process ids over HTTP. The file is held
       under LOCK_EX for the lifetime of the request — the OS releases the lock
       when the request ends for ANY reason (clean exit, fatal, SIGKILL, FPM
       request_terminate_timeout). Other endpoints test the lock via LOCK_SH +
       LOCK_NB to know whether this script is genuinely still running, since
       checking the PID itself is unreliable: PHP-FPM workers persist across
       requests, so the PID in the file stays alive long after the script ends. */
    if(function_exists('getmypid')) {
        $tracyPidFh = fopen($tracyRunCacheDir . $tracyRunId . '.pid', 'w');
        if($tracyPidFh) {
            flock($tracyPidFh, LOCK_EX);
            fwrite($tracyPidFh, (string) getmypid());
            fflush($tracyPidFh);
            TracyDebugger::$consolePidHandle = $tracyPidFh;
        }
    }
    /* MySQL connection id is recorded so the cancel endpoint can KILL the
       in-flight query and release any locks the worker is holding — SIGKILL
       on the PHP process closes the TCP socket but does not necessarily
       abort an already-running query (e.g. one stuck in "waiting for handler
       commit") */
    try {
        $tracyDbConnId = (int) $this->wire('database')->query('SELECT CONNECTION_ID()')->fetchColumn();
        if($tracyDbConnId) file_put_contents($tracyRunCacheDir . $tracyRunId . '.connid', (string) $tracyDbConnId);
    } catch(\Exception $e) {}
    ignore_user_abort(true);
    set_time_limit(0);
}
TracyDebugger::$consoleRunId = $tracyRunId;
TracyDebugger::$consoleRunStatusDir = $tracyRunStatusDir;
TracyDebugger::$consoleRunCacheDir = $tracyRunCacheDir;
if($this->wire('input')->post->allowBluescreen !== 'true') {
    set_error_handler(__NAMESPACE__.'\tracyConsoleErrorHandler');
    set_exception_handler(__NAMESPACE__.'\tracyConsoleExceptionHandler');
}
if(TracyDebugger::getDataValue('use_php_session') === 1 || TracyDebugger::$tracyVersion == '2.7.x') {
    Debugger::$disableShutdownHandler = true;
}
register_shutdown_function(__NAMESPACE__.'\tracyConsoleShutdownHandler');

// remove location links from dumps - not really meaningful for console
TracyDebugger::$fromConsole = true;

/* Capture $files->send() / WireHttp::sendFile() calls upstream of PW's @ob_end_clean(),
   which would otherwise silently discard the ob_start() buffer above without firing
   its callback. Stages the file to the console_downloads cache and emits the JSON
   download envelope as the inline AJAX response body. */
$this->wire()->addHookBefore('WireHttp::sendFile', function($event) use ($tracyRunId, $tracyRunStatusDir, $tracyRunCacheDir) {
    if(!TracyDebugger::$fromConsole || !$tracyRunId) return;
    if(TracyDebugger::$downloadStaged) return;

    $filename = $event->arguments(0);
    $options  = $event->arguments(1);
    if(!is_array($options)) $options = array();

    if($filename === false) {
        $downloadName = isset($options['downloadFilename']) ? basename($options['downloadFilename']) : '';
        if(!$downloadName) return; // let original sendFile() throw its own WireException
        $payload = isset($options['data']) ? $options['data'] : '';
    } else {
        $base = (isset($options['downloadFilename']) && $options['downloadFilename'] !== '')
            ? $options['downloadFilename']
            : pathinfo($filename, PATHINFO_BASENAME);
        $downloadName = basename($base);
        $payload = null; // sentinel: copy from $filename
    }
    if(!$downloadName) return;

    $ext = strtolower(pathinfo($downloadName, PATHINFO_EXTENSION));
    $cts = wire('config')->fileContentTypes;
    $contentType = isset($cts[$ext]) ? $cts[$ext] : (isset($cts['?']) ? $cts['?'] : 'application/octet-stream');
    $contentType = ltrim($contentType, '+');

    $dir = wire('config')->paths->cache . 'TracyDebugger/console_downloads/' . $tracyRunId . '/';
    if(!is_dir($dir) && !wireMkdir($dir, true)) return; // fall through to original sendFile()
    $dest = $dir . $downloadName;
    $ok = $payload === null ? @copy($filename, $dest) : (file_put_contents($dest, $payload) !== false);
    if(!$ok) return;

    if(file_put_contents($dest . '.meta.json', json_encode(array('contentType' => $contentType))) === false) {
        TD::log('TracyDebugger: failed to write download sidecar at ' . $dest . '.meta.json', 'error');
    }

    /* Stash the envelope and mark $downloadStaged. We deliberately do NOT drain
       buffers, echo the envelope, or exit. Reasoning:
       - $t->render() in CodeProcessor.php has its own nested ob_start; draining
         it mid-execution breaks render()'s internal accounting and loses output.
       - In a debug console, anything the user typed after $files->send() (more
         d() dumps, echoes, etc) should also reach the panel. Honoring PW's
         default exit=>true would silently drop it.
       The natural output continues to accumulate in the existing buffer stack
       and is wrapped into the final JSON envelope by tracyConsoleDownloadBufferHandler
       at the script's final flush. */
    $envelope = array(
        'status'      => 'download',
        'url'         => wire('config')->urls->httpRoot . '?tracyConsoleDownload=1&runId=' . urlencode($tracyRunId) . '&filename=' . urlencode($downloadName),
        'filename'    => $downloadName,
        'contentType' => $contentType,
        'preOutput'   => '',
        'postOutput'  => '',
    );
    TracyDebugger::$downloadStaged = true;
    TracyDebugger::$pendingDownloadEnvelope = $envelope;

    /* Snapshot the innermost buffer contents at the moment $files->send() fires.
       The callback at script end has the full output stream as its $buffer; the
       portion that was already there at hook time is the "pre-download" output,
       and the rest is "post-download". The JS uses this to render the download
       banner chronologically between the two. */
    $cur = ob_get_contents();
    TracyDebugger::$preDownloadOutput = ($cur === false) ? '' : $cur;

    /* Strip any download-style headers the user (or PW) already set, so the final
       JSON envelope reaches the panel as application/json rather than as an
       attachment. */
    header_remove('Content-Disposition');
    header_remove('Content-Length');

    $event->replace = true;
    $event->return  = $payload === null ? @filesize($filename) : strlen($payload);
});

// populate API variables, eg so $page equals $this->wire('page')
$pwVars = $this->wire('all');
foreach($pwVars->getArray() as $key => $value) {
    $$key = $value;
}

if(TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser) {

    // validate CSRF token, falling back to a same-origin check so a stale
    // baked-in token (session changed under an open console tab) doesn't fail
    $csrfToken = isset($_POST['csrfToken']) ? $_POST['csrfToken'] : '';
    if(!TracyDebugger::validTracyRequest($this->wire('session')->tracyConsoleToken, $csrfToken)) {
        http_response_code(403);
        echo 'CSRF token validation failed';
        exit;
    }

    /* cache all session data we need, then release session lock immediately
       so the user isn't blocked from browsing while code executes */
    $tracySessionCache = array(
        'includedFiles' => $this->wire('session')->tracyIncludedFiles,
        'getData' => $this->wire('session')->tracyGetData,
        'postData' => $this->wire('session')->tracyPostData,
        'whitelistData' => $this->wire('session')->tracyWhitelistData,
    );
    session_write_close();
    /* release Tracy's own FileSession lock — it holds LOCK_EX for the
       entire request and blocks other tabs that share the tracy-session cookie */
    $tracySessionStorage = Debugger::getSessionStorage();
    if($tracySessionStorage instanceof \Tracy\FileSession) {
        $ref = new \ReflectionProperty($tracySessionStorage, 'file');
        // setAccessible required on PHP < 8.1; deprecated on PHP 8.5+; no-op in between.
        if(PHP_VERSION_ID < 80100) $ref->setAccessible(true);
        $fileHandle = $ref->getValue($tracySessionStorage);
        if(is_resource($fileHandle)) {
            flock($fileHandle, LOCK_UN);
        }
    }

    $page = $pages->get((int)$_POST['pid']);
    if(isset($_POST['tracyConsole'])) {
        $code = $_POST['code'];
    }
    else {
        $code = '';
    }

    // ready.php and finished.php are already executed by PW during normal bootstrap
    // before this code runs (CodeProcessor is invoked from a ProcessWire::ready hook).
    // Re-including them here would risk duplicate hook registrations and side effects.
    // Any bd() calls in those files are already captured by Tracy during bootstrap.

    $cachePath = $this->wire('config')->paths->cache . 'TracyDebugger/';

    $this->file = $cachePath.'consoleCode' . ($tracyRunId ? '_' . $tracyRunId : '') . '.php';
    $tokens = token_get_all($code);
    $nextStringIsNamespace = false;
    $nameSpace = null;
    $containsPhpOpenTag = false;
    foreach($tokens as $token) {
        switch($token[0]) {
            case T_OPEN_TAG:
                $containsPhpOpenTag = true;
                break;

            case T_NAMESPACE:
                $nextStringIsNamespace = true;
                break;

            case T_STRING:
                if($nextStringIsNamespace) {
                    $nextStringIsNamespace = false;
                    $nameSpace = $token[1];
                }
                break;
        }
    }
    if($nameSpace) {
        $nameSpace = 'namespace ' . $nameSpace . ';';
        $code = str_replace($nameSpace, '', $code);
    }
    else {
        $nameSpace = 'namespace ProcessWire;';
    }

    $openPHP = '<' . '?php';
    $inPwCheck = 'if(!defined("PROCESSWIRE")) die("no direct access");';
    $setVars = '$page = $pages->get('.$page->id.'); $pages->uncacheAll();';
    if(isset($_POST['fid']) && $_POST['fid'] != '') $setVars .= '$field = $fields->get('.(int)$_POST['fid'].'); ';
    if(isset($_POST['tid']) && $_POST['tid'] != '') $setVars .= '$template = $templates->get('.(int)$_POST['tid'].'); ';
    if(isset($_POST['mid']) && $_POST['mid'] != '') $setVars .= '$module = $modules->getModule("'.$this->wire('sanitizer')->name($_POST['mid']).'", array("configOnly" => true)); ';

    $codePrefixes = "$openPHP $nameSpace $inPwCheck $setVars";

    // close php after codePrefixes if there is a PHP open tag somewhere in the code
    // or it starts with a < without the ? which indicates an HTML opening tag
    if($containsPhpOpenTag || (substr(trim($code), 0, 1) === '<' && substr(trim($code), 1, 1) !== '?')) {
        $codePrefixes .= '?>';
    }
    $code = "$codePrefixes\n$code";

    if(!$this->wire('files')->filePutContents($this->file, $code, LOCK_EX)) {
        throw new WireException("Unable to write file: $this->file");
    }
    if($tracyRunId) {
        $this->wire('files')->filePutContents($cachePath . 'consoleCode.php', $code, LOCK_EX);
    }

    if($this->wire('input')->cookie->tracyCodeReturn != "no") {

        if($this->wire('input')->post->dbBackup === "true") {

            if(PHP_VERSION_ID >= 70300) {
                setcookie('tracyDbBackup', 1, ['expires' => time() + 3600, 'path' => '/', 'samesite' => 'Strict']);
                setcookie('tracyDbBackupFilename', $input->post->text('backupFilename'), ['expires' => time() + 3600, 'path' => '/', 'samesite' => 'Strict']);
            } else {
                setcookie('tracyDbBackup', 1, time() + 3600, '/');
                setcookie('tracyDbBackupFilename', $input->post->text('backupFilename'), time() + 3600, '/');
            }

            $backupDir = $this->wire('config')->paths->assets . 'backups/database/';
            $filename = basename($this->wire('sanitizer')->filename($input->post('backupFilename')), '.sql');

            if(empty($filename)) {
                $filename = 'tracy-console-' . date('Y-m-d-H-i-s');
                $files = glob($backupDir . "tracy-console-*");
                if($files && count($files) >= TracyDebugger::getDataValue('consoleBackupLimit')) {
                    $oldest = null;
                    $oldestMtime = PHP_INT_MAX;
                    foreach($files as $f) {
                        $m = filemtime($f);
                        if($m !== false && $m < $oldestMtime) {
                            $oldestMtime = $m;
                            $oldest = $f;
                        }
                    }
                    if($oldest !== null) unlink($oldest);
                }
            }
            $_filename = $filename;
            $filename .= '.sql';

            if(preg_match('/^(.+)-(\d+)$/', $_filename, $matches)) {
                $_filename = $matches[1];
                $n = $matches[2];
            } else {
                $n = 0;
            }

            while(file_exists($backupDir . $filename)) {
                $filename = $_filename . "-" . (++$n) . ".sql";
            }

            if(!file_exists($backupDir)) wireMkdir($backupDir);

            $backup = new WireDatabaseBackup($backupDir);
            $backup->setDatabase($this->wire('database'));
            $backup->setDatabaseConfig($this->wire('config'));
            $file = $backup->backup(array('filename' => $filename));
        }

        if($page->template != 'admin' && $this->wire('input')->post->accessTemplateVars === "true") {
            // make vars from the page template available to the console code
            // get all current vars
            $currentVars = get_defined_vars();
            // get vars from the page's template file
            ob_start();
            $includedFiles = $tracySessionCache['includedFiles'];
            foreach($includedFiles as $key => $path) {
                if($path != $this->file && $path != $page->template->filename) {
                    // These files are replayed only for their function/class definitions, which
                    // escape any scope; running them in a closure keeps their stray variables out
                    // of the harvested template vars, and the catch stops one context-dependent
                    // file (e.g. a partial expecting TemplateFile-injected variables, which can
                    // fatal under PHP 8) from aborting the replay and the console run with it.
                    try {
                        (static function($p) { include_once($p); })($path);
                    }
                    catch(\Throwable $e) {
                        // skip - this file's variables were never template-scoped anyway
                    }
                }
            }
            // template file is excluded above and included now, after all others, to prevent include errors to
            // relative file paths preventing access to all variables/functions - happens especially when filecompiler is off
            // (kept in this scope deliberately: its variables are what we harvest below)
            try {
                include_once($page->template->filename);
            }
            catch(\Throwable $e) {
                // degrade to no/partial template vars rather than killing the console run
            }

            $templateVars = get_defined_vars();
            ob_end_clean();
            // remove the current vars from the list
            foreach($currentVars as $key => $value) {
                unset($templateVars[$key]);
            }
            unset($templateVars['currentVars']);

            // this needs to be here, not before the template != 'admin' conditional
            // because it is converted to an integer during output buffering
            $t = new TemplateFile($this->file);

            // populate template with all $templateVars
            foreach($templateVars as $key => $value) {
                $t->set($key, $value);
            }
        }

        // re-populate various $input properties from version stored in session
        $getData = $tracySessionCache['getData'];
        if($getData) {
            foreach($getData as $k => $v) {
                $this->wire('input')->get->$k = $v;
            }
        }

        $postData = $tracySessionCache['postData'];
        if($postData) {
            foreach($this->wire('input')->post as $k => $v) {
                unset($this->wire('input')->post->$k);
            }
            foreach($postData as $k => $v) {
                $this->wire('input')->post->$k = $v;
            }
        }

        $whitelistData = $tracySessionCache['whitelistData'];
        if($whitelistData) {
            foreach($whitelistData as $k => $v) {
                $this->wire('input')->whitelist->$k = $v;
            }
        }

        // if in admin then $t won't have been instantiated above so do it now
        if(!isset($t) || !$t instanceof TemplateFile) $t = new TemplateFile($this->file);

        Debugger::timer('consoleCode');
        $initialMemory = memory_get_usage();

        // capture output so we can write it to cache file for background polling.
        // The callback detects download responses (Content-Disposition: attachment) and
        // rewrites the buffer to a JSON download envelope at request-end flush.
        ob_start(__NAMESPACE__.'\tracyConsoleDownloadBufferHandler');
        try {
            echo $t->render();
        }
        catch (\Exception $e) {
            tracyConsoleExceptionHandler($e);
        }
        $renderedOutput = ob_get_clean();

        $timeStr = TracyDebugger::formatTime(Debugger::timer('consoleCode'), false);
        $memStr = TracyDebugger::human_filesize((max((memory_get_usage() - $initialMemory), 0)), false);
        $metricsHtml = '
        <div class="tracyConsoleMetrics" style="border-top: 1px dotted #cccccc; color:#A9ABAB; border-bottom: 1px solid #cccccc; color:#A9ABAB; font-size: 10px; padding: 3px; margin: 10px 0 0 0;">' .
            $timeStr . ', ' . $memStr . '
        </div>';

        /* When $files->send() staged a download: $renderedOutput now contains the
           full script output (all d() dumps from before and after $files->send(),
           plus any error HTML echoed by writeError if an exception fired). Build
           the envelope from $renderedOutput directly — splitting at the snapshot
           taken at the moment of $files->send() so the panel JS can render the
           banner chronologically. This works for both the normal path and the
           catch path (where writeError already overwrote the cache file with an
           'error' envelope — we overwrite it back to 'download' here). Set
           $downloadDelivered so the outer-buffer callback at script-end shutdown
           doesn't double-wrap. */
        if(TracyDebugger::$downloadStaged && is_array(TracyDebugger::$pendingDownloadEnvelope)) {
            $env = TracyDebugger::$pendingDownloadEnvelope;
            list($pre, $post) = tracyConsoleSplitAtSnapshot($renderedOutput, TracyDebugger::$preDownloadOutput);
            $env['preOutput']  = $pre;
            $env['postOutput'] = $post;
            if($tracyRunId) {
                file_put_contents($tracyRunCacheDir . $tracyRunId . '.json', json_encode($env));
                file_put_contents($tracyRunStatusDir . $tracyRunId . '.json', json_encode(array('status' => 'complete')));
            }
            TracyDebugger::$pendingDownloadEnvelope = $env;
            TracyDebugger::$downloadDelivered = true;
            tracyConsoleReleasePidLock();
            header_remove('Content-Disposition');
            header_remove('Content-Type');
            header_remove('Content-Length');
            header('Content-Type: application/json');
            $renderedOutput = json_encode($env);
        }

        // write result to protected cache file, update public status marker.
        // Skip on the download path: the wrap-up above already wrote the envelope to cache.
        if($tracyRunId && !TracyDebugger::$downloadStaged) {
            $result = array(
                'status' => 'complete',
                'output' => $renderedOutput . $metricsHtml,
                'time' => $timeStr,
                'memory' => $memStr
            );
            file_put_contents($tracyRunCacheDir . $tracyRunId . '.json', json_encode($result));
            file_put_contents($tracyRunStatusDir . $tracyRunId . '.json', json_encode(array('status' => 'complete')));
            /* release the flock now so the panel stops seeing this run as live the
               instant the work is done — anything that runs after this (echo to a
               closed connection, AJAX-bar render, etc.) must not keep blocking the
               liveness probe. The OS would release the lock at request end anyway,
               but doing it here drops the window from "until shutdown finishes" to
               "now". */
            tracyConsoleReleasePidLock();
        }

        // also output inline for normal (non-timed-out) responses.
        // On the download path, $renderedOutput is the JSON envelope and the metrics
        // div must NOT be appended (would corrupt the JSON).
        echo $renderedOutput;
        if(!TracyDebugger::$downloadStaged) {
            echo $metricsHtml;
        }

        // fix for updating AJAX bar
        if(TracyDebugger::$tracyVersion == '2.7.x') {
            Debugger::getBar()->render();
            Debugger::$showBar = false;
        }

    }

    if($tracyRunId) {
        @unlink($this->file);
        @unlink($tracyRunCacheDir . $tracyRunId . '.connid');
    }
    exit;
}


// error handler function
function tracyConsoleErrorHandler($errno, $errstr, $errfile, $errline) {
    // this prevents silenced(@) errors from being captured by this custom error handler
    if(error_reporting() === 0) {
        // continue script execution, skipping standard PHP error handler
        return true;
    }

    // ignore any include/require errors - we are including all files by their full path via
    // $this->wire('session')->tracyIncludedFiles anyway, so the errors caused by relative paths won't matter
    if(strpos($errstr, 'include') !== false || strpos($errstr, 'require') !== false) {
        return;
    }
    else {
        // Only fatal-equivalent severities terminate the script. For non-fatal
        // notices/warnings the script keeps running (e.g. a getimagesize() warning
        // fired mid-dump), so we must NOT mark the console run as 'error' and
        // release the pid lock — doing so causes the poller to stop and the dump
        // output that follows never reaches the result pane.
        $terminal = in_array($errno, array(E_USER_ERROR, E_RECOVERABLE_ERROR), true);
        writeError(array('type' => 'Error', 'line' => $errline, 'message' => $errstr, 'file' => $errfile), $terminal);
    }
}

// exception handler function
function tracyConsoleExceptionHandler($err) {
    writeError(array('type' => 'Exception', 'line' => $err->getLine(), 'message' => $err->getMessage(), 'file' => $err->getFile()), true);
}

/* Release the flock held on the .pid file. Idempotent — safe to call from any
   exit path (success, error, fatal, shutdown). Releasing early lets the active-runs
   and cancel endpoints see the run as no longer live without waiting for the OS
   to clean up at process termination. */
function tracyConsoleReleasePidLock() {
    $fh = TracyDebugger::$consolePidHandle;
    if(is_resource($fh)) {
        @flock($fh, LOCK_UN);
        @fclose($fh);
    }
    TracyDebugger::$consolePidHandle = null;
}

// fatal error / shutdown handler function
function tracyConsoleShutdownHandler() {
    /* always release the lock first, regardless of error state, so the panel
       stops reporting "Running..." the moment this request actually ends */
    tracyConsoleReleasePidLock();
    // this prevents silenced(@) errors from being captured by this custom error handler
    if(error_reporting() === 0) {
        // continue script execution, skipping standard PHP error handler
        return true;
    }

    $lasterror = error_get_last();

    // convert error constants to strings,
    // otherwise the error's numerical value (http://php.net/manual/en/errorfunc.constants.php) is returned
    $errorStrings = array(
        E_ERROR => 'ERROR',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_USER_ERROR => 'E_USER_ERROR',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_PARSE => 'E_PARSE'
    );

    // ignore any include/require errors - we are including all files by their full path via
    // $this->wire('session')->tracyIncludedFiles anyway, so the errors caused by relative paths won't matter
    if($lasterror && (strpos($lasterror['message'], 'include') !== false || strpos($lasterror['message'], 'require') !== false)) {
        return;
    }
    elseif($lasterror) {
        switch ($lasterror['type']) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_PARSE:
                // remove PHP's "fatal error" message so we can display just our cleaner version
                @ob_end_clean();
                $lasterror['type'] = $errorStrings[$lasterror['type']];
                writeError($lasterror);
        }
    }
}

function writeError($error, $terminal = true) {
    $customErrStr = $error['message'] . ' on line: ' . (strpos($error['file'], 'cache'.DIRECTORY_SEPARATOR.'TracyDebugger') !== false ? $error['line'] - 1 : $error['line']) . (strpos($error['file'], 'cache'.DIRECTORY_SEPARATOR.'TracyDebugger') !== false ? '' : ' in ' . str_replace(wire('config')->paths->cache . 'FileCompiler'.DIRECTORY_SEPARATOR, '../', $error['file']));
    $customErrStrLog = $customErrStr . (strpos($error['file'], 'cache'.DIRECTORY_SEPARATOR.'TracyDebugger') !== false ? ' in Tracy Console Panel' : '');
    TD::log($customErrStrLog, 'error');

    // echo always — goes into the active ob_start buffer so the inline error is
    // captured alongside any dump output produced before/after the error fires.
    // Styled to match TD::renderDumpError's block so a warning fired during d()/db()
    // looks consistent with the throwable-during-dump fallback path.
    // attach an agent-copy button + [data-tracy-md-source] payload so a single
    // error block is copyable on its own and "Copy all" picks it up in DOM order
    // alongside any dumps (matches the dump copy-button layout in TD).
    $agentErrButton = TD::buildAgentTextCopyButton($error['type'] . ': ' . $customErrStr, 'Copy this error as plaintext for an AI agent');
    echo '<div style="position:relative; border: 1px solid #d9d9d9; border-left: 3px solid #c00; padding: 6px 28px 6px 10px; background: #fff3f3; color: #c00; font-family: monospace; white-space: normal; margin-bottom: 5px;">'
        . $agentErrButton
        . '<strong>' . htmlspecialchars($error['type'], ENT_QUOTES, 'UTF-8') . ':</strong> '
        . htmlspecialchars($customErrStr, ENT_QUOTES, 'UTF-8')
        . '</div>';

    if(!$terminal) return;

    if(PHP_VERSION_ID >= 70300) {
        setcookie('tracyCodeError', $error['type'].': '.$customErrStr, ['expires' => time() + (10 * 365 * 24 * 60 * 60), 'path' => '/', 'samesite' => 'Strict']);
    } else {
        setcookie('tracyCodeError', $error['type'].': '.$customErrStr, time() + (10 * 365 * 24 * 60 * 60), '/');
    }

    // write error to protected cache, update public status marker
    // this means the browser will receive a 200 when it may have been a 500,
    // but that's ok — the error text is what the console pane wants to show
    $runId = TracyDebugger::$consoleRunId;
    $statusDir = TracyDebugger::$consoleRunStatusDir;
    $cacheDir = TracyDebugger::$consoleRunCacheDir;
    if($runId && $statusDir && $cacheDir) {
        $result = array(
            'status' => 'error',
            'output' => $error['type'].': '.$customErrStr,
            'time' => '',
            'memory' => ''
        );
        file_put_contents($cacheDir . $runId . '.json', json_encode($result));
        file_put_contents($statusDir . $runId . '.json', json_encode(array('status' => 'error')));
        tracyConsoleReleasePidLock();
    }
}

/* ob_start callback that detects file-download responses (Content-Disposition: attachment),
   stages the binary under cache/TracyDebugger/console_downloads/<runId>/<filename>, and
   rewrites the buffer to a JSON {status:'download',...} envelope. Pass-through otherwise. */
function tracyConsoleDownloadBufferHandler($buffer, $phase) {
    if(!($phase & PHP_OUTPUT_HANDLER_FINAL)) return $buffer;

    $runId     = TracyDebugger::$consoleRunId;
    $statusDir = TracyDebugger::$consoleRunStatusDir;
    $cacheDir  = TracyDebugger::$consoleRunCacheDir;
    if(!$runId || !$statusDir || !$cacheDir) return $buffer;

    /* CodeProcessor's wrap-up already built the envelope, wrote the cache file,
       and assigned $renderedOutput. This callback fires at outer-buffer shutdown
       after that; the buffer here is the JSON envelope text — pass through. */
    if(TracyDebugger::$downloadDelivered) return $buffer;

    /* If the WireHttp::sendFile hook already staged a download, this buffer is the
       natural script output (d() dumps before AND after $files->send()). Wrap it
       into the envelope, write the cache file, flip status to complete, and emit
       the final JSON. (Reached only when the script exited before CodeProcessor's
       wrap-up could run — e.g. user called exit() during $t->render().) */
    if(TracyDebugger::$downloadStaged) {
        $env = TracyDebugger::$pendingDownloadEnvelope;
        if(!is_array($env)) return $buffer;
        list($pre, $post) = tracyConsoleSplitAtSnapshot($buffer, TracyDebugger::$preDownloadOutput);
        $env['preOutput']  = $pre;
        $env['postOutput'] = $post;
        file_put_contents($cacheDir . $runId . '.json', json_encode($env));
        file_put_contents($statusDir . $runId . '.json', json_encode(array('status' => 'complete')));
        TracyDebugger::$pendingDownloadEnvelope = $env;
        tracyConsoleReleasePidLock();
        header_remove('Content-Disposition');
        header_remove('Content-Type');
        header_remove('Content-Length');
        header('Content-Type: application/json');
        return json_encode($env);
    }

    $disposition = tracyConsoleFindHeader('Content-Disposition');
    if(!$disposition || stripos($disposition, 'attachment') === false) return $buffer;

    /* On fatal, drop the partial buffer and let the existing shutdown error envelope reach the panel. */
    $lastError = error_get_last();
    if($lastError && in_array($lastError['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR), true)) {
        return $buffer;
    }

    $filename = null;
    if(preg_match('/filename="([^"]+)"/i', $disposition, $m)) {
        $filename = basename($m[1]);
    }
    elseif(preg_match('/filename=([^;\s]+)/i', $disposition, $m)) {
        $filename = basename($m[1]);
    }
    if(!$filename) $filename = 'download_' . $runId . '.bin';

    $contentType = tracyConsoleFindHeader('Content-Type');
    if(!$contentType) $contentType = 'application/octet-stream';

    $dir = wire('config')->paths->cache . 'TracyDebugger/console_downloads/' . $runId . '/';
    if(!is_dir($dir) && !wireMkdir($dir, true)) {
        return tracyConsoleDownloadErrorEnvelope('Failed to create download directory');
    }
    $path = $dir . $filename;
    if(file_put_contents($path, $buffer) === false) {
        return tracyConsoleDownloadErrorEnvelope('Failed to write download file');
    }
    if(file_put_contents($path . '.meta.json', json_encode(array('contentType' => $contentType))) === false) {
        TD::log('TracyDebugger: failed to write download sidecar at ' . $path . '.meta.json', 'error');
    }

    $envelope = array(
        'status'      => 'download',
        'url'         => wire('config')->urls->httpRoot . '?tracyConsoleDownload=1&runId=' . urlencode($runId) . '&filename=' . urlencode($filename),
        'filename'    => $filename,
        'contentType' => $contentType,
    );

    /* Write cache first, then flip status — polling clients gate on status. */
    file_put_contents($cacheDir . $runId . '.json', json_encode($envelope));
    file_put_contents($statusDir . $runId . '.json', json_encode(array('status' => 'complete')));
    TracyDebugger::$downloadStaged = true;
    tracyConsoleReleasePidLock();

    header_remove('Content-Disposition');
    header_remove('Content-Type');
    header_remove('Content-Length');
    header('Content-Type: application/json');
    return json_encode($envelope);
}

/* Find the snapshot taken when $files->send() fired inside the final output
   stream, and split the stream at the end of the snapshot. Tries:
     1. raw snapshot (matches the exception path, where TemplateFile::render()
        skips its trim() and the buffer still has leading whitespace)
     2. ltrim'd snapshot (matches the normal path, where render() trimmed)
   Returns [pre, post]. If neither match, returns [buffer, ''] so the banner
   falls back to appending at the end. */
function tracyConsoleSplitAtSnapshot($buffer, $snapshot) {
    if($snapshot !== '') {
        $pos = strpos($buffer, $snapshot);
        if($pos !== false) {
            $splitAt = $pos + strlen($snapshot);
            return array(substr($buffer, 0, $splitAt), substr($buffer, $splitAt));
        }
        $snapLtrim = ltrim($snapshot);
        if($snapLtrim !== '') {
            $pos = strpos($buffer, $snapLtrim);
            if($pos !== false) {
                $splitAt = $pos + strlen($snapLtrim);
                return array(substr($buffer, 0, $splitAt), substr($buffer, $splitAt));
            }
        }
    }
    return array($buffer, '');
}

/* Look up a header from headers_list() by prefix (case-insensitive). */
function tracyConsoleFindHeader($name) {
    $prefix = $name . ':';
    $prefixLen = strlen($prefix);
    foreach(headers_list() as $h) {
        if(strncasecmp($h, $prefix, $prefixLen) === 0) {
            return trim(substr($h, $prefixLen));
        }
    }
    return null;
}

/* Build a JSON error envelope and strip download-style headers. */
function tracyConsoleDownloadErrorEnvelope($message) {
    header_remove('Content-Disposition');
    header_remove('Content-Type');
    header_remove('Content-Length');
    header('Content-Type: application/json');
    TracyDebugger::$downloadStaged = true;
    return json_encode(array(
        'status' => 'error',
        'output' => '<div style="padding:8px; color:#c00;">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>',
    ));
}
