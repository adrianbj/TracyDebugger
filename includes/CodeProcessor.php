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

// populate API variables, eg so $page equals $this->wire('page')
$pwVars = $this->wire('all');
foreach($pwVars->getArray() as $key => $value) {
    $$key = $value;
}

if(TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser) {

    // validate CSRF token
    $csrfToken = isset($_POST['csrfToken']) ? $_POST['csrfToken'] : '';
    if(!$csrfToken || !hash_equals((string)$this->wire('session')->tracyConsoleToken, $csrfToken)) {
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
                if($files) {
                    if(count($files) >= TracyDebugger::getDataValue('consoleBackupLimit')) {
                        array_multisort(
                            array_map('filemtime', $files),
                            SORT_NUMERIC,
                            SORT_ASC,
                            $files
                        );
                        unlink($files[0]);
                    }
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
                    include_once($path);
                }
            }
            // template file is excluded above and included now, after all others, to prevent include errors to
            // relative file paths preventing access to all variables/functions - happens especially when filecompiler is off
            include_once($page->template->filename);

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

        // capture output so we can write it to cache file for background polling
        ob_start();
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
        <div style="border-top: 1px dotted #cccccc; color:#A9ABAB; border-bottom: 1px solid #cccccc; color:#A9ABAB; font-size: 10px; padding: 3px; margin: 10px 0 0 0;">' .
            $timeStr . ', ' . $memStr . '
        </div>';

        // write result to protected cache file, update public status marker
        if($tracyRunId) {
            $result = array(
                'status' => 'complete',
                'output' => $renderedOutput . $metricsHtml,
                'time' => $timeStr,
                'memory' => $memStr
            );
            file_put_contents($tracyRunCacheDir . $tracyRunId . '.json', json_encode($result));
            file_put_contents($tracyRunStatusDir . $tracyRunId . '.json', json_encode(array('status' => 'complete')));
        }

        // also output inline for normal (non-timed-out) responses
        echo $renderedOutput;
        echo $metricsHtml;

        // fix for updating AJAX bar
        if(TracyDebugger::$tracyVersion == '2.7.x') {
            Debugger::getBar()->render();
            Debugger::$showBar = false;
        }

    }

    if($tracyRunId) @unlink($this->file);
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
        writeError(array('type' => 'Error', 'line' => $errline, 'message' => $errstr, 'file' => $errfile));
    }
}

// exception handler function
function tracyConsoleExceptionHandler($err) {
    writeError(array('type' => 'Exception', 'line' => $err->getLine(), 'message' => $err->getMessage(), 'file' => $err->getFile()));
}

// fatal error / shutdown handler function
function tracyConsoleShutdownHandler() {
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

function writeError($error) {
    $customErrStr = $error['message'] . ' on line: ' . (strpos($error['file'], 'cache'.DIRECTORY_SEPARATOR.'TracyDebugger') !== false ? $error['line'] - 1 : $error['line']) . (strpos($error['file'], 'cache'.DIRECTORY_SEPARATOR.'TracyDebugger') !== false ? '' : ' in ' . str_replace(wire('config')->paths->cache . 'FileCompiler'.DIRECTORY_SEPARATOR, '../', $error['file']));
    $customErrStrLog = $customErrStr . (strpos($error['file'], 'cache'.DIRECTORY_SEPARATOR.'TracyDebugger') !== false ? ' in Tracy Console Panel' : '');
    TD::log($customErrStrLog, 'error');

    if(PHP_VERSION_ID >= 70300) {
        setcookie('tracyCodeError', $error['type'].': '.$customErrStr, ['expires' => time() + (10 * 365 * 24 * 60 * 60), 'path' => '/', 'samesite' => 'Strict']);
    } else {
        setcookie('tracyCodeError', $error['type'].': '.$customErrStr, time() + (10 * 365 * 24 * 60 * 60), '/');
    }

    // echo and exit approach allows us to send error to Tracy console dump area
    // this means that the browser will receive a 200 when it may have been a 500,
    // but think that is ok in this case
    echo $error['type'].': '.$customErrStr;
    // write error to protected cache, update public status marker
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
    }
}
