<?php namespace ProcessWire;

$snippetsPath = $this->wire('config')->paths->site.TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/';

if(!is_dir($snippetsPath)) if(!wireMkdir($snippetsPath, true)) {
    throw new WireException("Unable to create snippets path: $snippetsPath");
}

if(isset($_POST['deletesnippet']) && file_exists($snippetsPath.$_POST['snippetname'])) {
    unlink($snippetsPath.$_POST['snippetname']);
}
elseif(isset($_POST['snippetcode'])) {
    $this->wire('files')->filePutContents($snippetsPath.$_POST['snippetname'], TracyDebugger::getDataValue('consoleCodePrefix') . json_decode($_POST['snippetcode']));
}
else {
    echo str_replace(TracyDebugger::getDataValue('consoleCodePrefix'), '', file_get_contents($snippetsPath.$_POST['snippetname']));
}

exit;
