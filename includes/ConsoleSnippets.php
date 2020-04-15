<?php

$snippetsPath = $this->wire('config')->paths->site.\TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/';

if(isset($_POST['deletesnippet']) && file_exists($snippetsPath.$_POST['snippetname'])) {
    unlink($snippetsPath.$_POST['snippetname']);
}
elseif(isset($_POST['snippetcode'])) {
    file_put_contents($snippetsPath.$_POST['snippetname'], \TracyDebugger::getDataValue('consoleCodePrefix') . "\n" . json_decode($_POST['snippetcode']));
}
else {
    echo str_replace(\TracyDebugger::getDataValue('consoleCodePrefix') . "\n", '', file_get_contents($snippetsPath.$_POST['snippetname']));
}

exit;
