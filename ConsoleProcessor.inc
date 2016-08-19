<?php

set_error_handler('tracyConsoleErrorHandler');

// remove location links from dumps - not really meaningful for console
\Tracy\Debugger::$showLocation = FALSE;

$pwVars = function_exists('wire') ? $this->fuel : \ProcessWire\wire('all');

$user = wire('user');
$page = wire('page');
$pages = wire('pages');
$config = wire('config');

if($user->isSuperuser()) {
    $page = $pages->get((int)$_POST['pid']);
    // convert from URL-safe base64 encoded
    $code = base64_decode(str_pad(strtr($_POST['code'], '-_', '+/'), strlen($_POST['code']) % 4, '=', STR_PAD_RIGHT));

    $cachePath = $config->paths->cache . 'TracyDebugger/';
    if(!is_dir($cachePath)) if(!wireMkdir($cachePath)) {
        throw new WireException("Unable to create cache path: $cachePath");
    }

    $file = $cachePath.'consoleCode.php';
    $code = trim($code);
    $openPHP = '<' . '?php';
    $firstLine = 'if(!defined("PROCESSWIRE")) die("no direct access");';

    if(substr($code, 0, strlen($openPHP)) !== $openPHP) {
        // prepend open PHP tag to code if not already present
        $code = "$openPHP\n$firstLine\n$code";
    } else {
        // otherwise insert our $firstLine security check
        $code = str_replace($openPHP, "$openPHP\n$firstLine\n", $code);
    }
    if(!file_put_contents($file, $code, LOCK_EX)) throw new WireException("Unable to write file: $file");
    if(wire('config')->chmodFile) chmod($file, octdec(wire('config')->chmodFile));

    if($page->template != 'admin' && wire('input')->post->accessTemplateVars === "true") {
        // make vars from the page template available to the console code
        // get all current vars
        $currentVars = get_defined_vars();
        // get vars from the page's template file
        ob_start();
        require_once($page->template->filename);
        $templateVars = get_defined_vars();
        ob_end_clean();
        // remove the current vars from the list
        foreach($currentVars as $key => $value) {
            unset($templateVars[$key]);
        }
        unset($templateVars['currentVars']);

        // this needs to be here, not before the template != 'admin' conditional
        // because it is converted to an integer during output buffering
        $t = new TemplateFile($file);

        // populate template with all $templateVars
        foreach($templateVars as $key => $value) {
            $t->set($key, $value);
        }
    }

    // if in admin then $t won't have been instantiated above so do it now
    if(!isset($t) || !$t instanceof TemplateFile) $t = new TemplateFile($file);

    // populate API variables ensuring $page refers to the page calling it, not this Process page
    foreach($pwVars->getArray() as $key => $value) {
        if($key != 'page') $t->set($key, $value);
    }
    $t->set('page', $page);

    // output rendered result of code
    try {
        echo $t->render();
    }
    catch (Exception $e) {
        tracyConsoleErrorHandler(null, $e->getMessage(), $e->getFile(), $e->getLine());
    }
    exit;
}


// error handler function
function tracyConsoleErrorHandler($errno, $errstr, $errfile, $errline) {
    $customErrStr = $errstr . ' on line: ' . (strpos($errfile, 'cache/TracyDebugger') !== false ? $errline - 2 : $errline) . (strpos($errfile, 'cache/TracyDebugger') !== false ? '' : ' in ' . str_replace(wire('config')->paths->cache . 'FileCompiler/', '../', $errfile));
    $customErrStrLog = $customErrStr . (strpos($errfile, 'cache/TracyDebugger') !== false ? ' in Tracy Console Panel' : '');
    \TD::fireLog($customErrStrLog);
    \TD::log($customErrStrLog, 'error');

    // echo and exit approach allows us to send error to Tracy console dump area
    // this means that the browser will receive a 200 when it may have been a 500,
    // but think that is ok in this case
    echo '<br />Error: '.$customErrStr;
    exit;
}
