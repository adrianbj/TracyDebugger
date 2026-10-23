<?php namespace ProcessWire;

// Console snippets are run from the Console, so like the Console they're for superusers (and local / switched
// users) only: another Tracy user writing one could plant code a superuser later runs
if(!TracyDebugger::$allowedSuperuser && !TracyDebugger::$validLocalUser && !TracyDebugger::$validSwitchedUser) {
    http_response_code(403);
    exit;
}

// validate CSRF token
$csrfToken = isset($_POST['csrfToken']) ? $_POST['csrfToken'] : '';
if(!$csrfToken || !hash_equals((string)$this->wire('session')->tracyConsoleToken, $csrfToken)) {
    http_response_code(403);
    echo 'CSRF token validation failed';
    exit;
}

$snippetsPath = $this->wire('config')->paths->site.TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/';

if(!is_dir($snippetsPath)) if(!wireMkdir($snippetsPath, true)) {
    throw new WireException("Unable to create snippets path: $snippetsPath");
}
TracyDebugger::denyWebAccess($snippetsPath);

// sanitize snippet name to prevent path traversal
$snippetName = basename($_POST['snippetname']);
// and no dotfiles: the folder's .htaccess keeps it off the web
if($snippetName === '' || strpos($snippetName, '.') === 0) {
    http_response_code(400);
    echo 'Invalid snippet name';
    exit;
}

if(isset($_POST['deletesnippet']) && file_exists($snippetsPath.$snippetName)) {
    unlink($snippetsPath.$snippetName);
}
elseif(isset($_POST['snippetcode'])) {
    $this->wire('files')->filePutContents($snippetsPath.$snippetName, TracyDebugger::getDataValue('consoleCodePrefix') . json_decode($_POST['snippetcode']));
}
else {
    echo str_replace(TracyDebugger::getDataValue('consoleCodePrefix'), '', file_get_contents($snippetsPath.$snippetName));
}

exit;
