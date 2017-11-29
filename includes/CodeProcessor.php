<?php

unset($this->wire('input')->cookie->tracyCodeError);
setcookie("tracyCodeError", "", time()-3600, '/');

set_error_handler('tracyConsoleErrorHandler');
set_exception_handler('tracyConsoleExceptionHandler');

// remove location links from dumps - not really meaningful for console
\TracyDebugger::$fromConsole = true;

// populate API variables, eg so $page equals $this->wire('page')
$pwVars = function_exists('wire') ? $this->fuel : \ProcessWire\wire('all');
foreach($pwVars->getArray() as $key => $value) {
    $$key = $value;
}

if($user->isSuperuser()) {

    $page = $pages->get((int)$_POST['pid']);
    if(isset($_POST['tracyConsole'])) {
        $code = $_POST['code'];
    }
    elseif(isset($_POST['tracySnippetRunner']) && isset($_POST['file']) && $_POST['file'] != '') {
        $code = file_get_contents($_POST['file']);
    }
    else {
        $code = null;
    }

    // ready.php and finished.php weren't being loaded, so include here to monitor any bd() etc calls they might have
    // the other approach to fix this is to call an external CodeProcessor.php file via ajax as per PM with @bernhard
    $readyPath = $this->wire('config')->paths->root . 'site/ready.php';
    $finishedPath = $this->wire('config')->paths->root . 'site/finished.php';
    if(file_exists($readyPath)) include_once($readyPath);
    if(file_exists($finishedPath)) include_once($finishedPath);

    $cachePath = $this->wire('config')->paths->cache . 'TracyDebugger/';
    if(!is_dir($cachePath)) if(!wireMkdir($cachePath)) {
        throw new WireException("Unable to create cache path: $cachePath");
    }

    $this->file = $cachePath.(isset($_POST['tracyConsole']) ? 'consoleCode.php' : 'snippetRunner.php');
    $code = trim($code);
    $tokens = token_get_all($code);
    $nextStringIsNamespace = false;
    $nameSpace = null;
    foreach($tokens as $token) {
        switch($token[0]) {
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
    $openPHP = '<' . '?php';
    $inPwCheck = 'if(!defined("PROCESSWIRE")) die("no direct access");';
    $setVars = '$page = $pages->get('.$page->id.'); ';
    if(isset($_POST['fid']) && $_POST['fid'] != '') $setVars .= '$field = $fields->get('.(int)$_POST['fid'].'); ';
    if(isset($_POST['tid']) && $_POST['tid'] != '') $setVars .= '$template = $templates->get('.(int)$_POST['tid'].'); ';
    if(isset($_POST['mid']) && $_POST['mid'] != '') $setVars .= '$module = $modules->get("'.$this->wire('sanitizer')->name($_POST['mid']).'"); ';

    $codePrefixes = "$openPHP $nameSpace $inPwCheck $setVars";
    // close php after codePrefixes if there is a PHP open tag somewhere in the code,
    // or it starts with an < without the ? which indicates an HTML opening tag
    if(strpos($code, '<?') !== false || (substr($code, 0, 1) === '<' && substr($code, 1, 1) !== '?')) {
        $codePrefixes .= '?>';
    }
    $code = "$codePrefixes\n$code";

    if(!file_put_contents($this->file, $code, LOCK_EX)) throw new WireException("Unable to write file: $this->file");
    if($this->wire('config')->chmodFile) chmod($this->file, octdec($this->wire('config')->chmodFile));

    if($page->template != 'admin' && $this->wire('input')->post->accessTemplateVars === "true") {
        // make vars from the page template available to the console code
        // get all current vars
        $currentVars = get_defined_vars();
        // get vars from the page's template file
        ob_start();
        foreach($this->wire('session')->tracyIncludedFiles as $key => $path) {
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
    foreach($this->wire('session')->tracyGetData as $k => $v) {
        $this->wire('input')->get->$k = $v;
    }

    $postData = $this->wire('session')->tracyPostData;
    foreach($this->wire('input')->post as $k => $v) {
        unset($this->wire('input')->post->$k);
    }
    foreach($postData as $k => $v) {
        $this->wire('input')->post->$k = $v;
    }

    foreach($this->wire('session')->tracyWhitelistData as $k => $v) {
        $this->wire('input')->whitelist->$k = $v;
    }


    // if in admin then $t won't have been instantiated above so do it now
    if(!isset($t) || !$t instanceof TemplateFile) $t = new TemplateFile($this->file);

    \Tracy\Debugger::timer('consoleCode');
    $initialMemory = memory_get_usage();
    // output rendered result of code
    try {
        echo $t->render();
    }
    catch (\Exception $e) {
        tracyConsoleExceptionHandler($e);
    }
    echo '
    <div style="border-top: 1px dotted #cccccc; color:#A9ABAB; border-bottom: 1px solid #cccccc; color:#A9ABAB; font-size: 10px; padding: 3px; margin:20px 0 20px 0;">' .
        round((\Tracy\Debugger::timer('consoleCode')*1000), 2) . 'ms, ' .
        number_format((memory_get_usage() - $initialMemory) / 1000000, 2, '.', ' ') . ' MB
    </div>';

    // fix for updating AJAX bar when SessionHandlerDB is installed
    if(\TracyDebugger::$tracyVersion != 'legacy' && $this->wire('modules')->isInstalled('SessionHandlerDB')) {
        \Tracy\Debugger::getBar()->render();
        \Tracy\Debugger::$showBar = FALSE;
    }

    exit;
}


// error handler function
function tracyConsoleErrorHandler($errno, $errstr, $errfile, $errline) {

    // this prevents silenced(@) errors from being captured by this custom error handler
    if (error_reporting() === 0) {
        // continue script execution, skipping standard PHP error handler
        return true;
    }

    // ignore any include/require errors - we are including all files by their full path via
    // $this->wire('session')->tracyIncludedFiles anyway, so the errors caused by relative paths won't matter
    if (strpos($errstr, 'include') !== false || strpos($errstr, 'require') !== false) {
        return;
    }
    else {
        $customErrStr = $errstr . ' on line: ' . (strpos($errfile, 'cache/TracyDebugger') !== false ? $errline - 1 : $errline) . (strpos($errfile, 'cache/TracyDebugger') !== false ? '' : ' in ' . str_replace(wire('config')->paths->cache . 'FileCompiler/', '../', $errfile));
        $customErrStrLog = $customErrStr . (strpos($errfile, 'cache/TracyDebugger') !== false ? ' in Tracy Console Panel' : '');
        \TD::fireLog($customErrStrLog);
        \TD::log($customErrStrLog, 'error');

        setcookie('tracyCodeError', 'Error: '.$customErrStr, time() + (10 * 365 * 24 * 60 * 60), '/');

        // echo and exit approach allows us to send error to Tracy console dump area
        // this means that the browser will receive a 200 when it may have been a 500,
        // but think that is ok in this case
        echo 'Error: '.$customErrStr;
        echo '<div style="border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
        // exit obviously causing scripts to halt even for notices/warnings which we don't want, so get rid of for now
        //exit;
    }
}


// exception handler function
function tracyConsoleExceptionHandler($err) {

    $errstr = $err->getMessage();
    $errfile = $err->getFile();
    $errline = $err->getLine();

    $customErrStr = $errstr . ' on line: ' . (strpos($errfile, 'cache/TracyDebugger') !== false ? $errline - 1 : $errline) . (strpos($errfile, 'cache/TracyDebugger') !== false ? '' : ' in ' . str_replace(wire('config')->paths->cache . 'FileCompiler/', '../', $errfile));
    $customErrStrLog = $customErrStr . (strpos($errfile, 'cache/TracyDebugger') !== false ? ' in Tracy Console Panel' : '');
    \TD::fireLog($customErrStrLog);
    \TD::log($customErrStrLog, 'error');

    setcookie('tracyCodeError', 'Exception: '.$customErrStr, time() + (10 * 365 * 24 * 60 * 60), '/');

    echo 'Exception: '.$customErrStr;
    echo '<div style="border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
}