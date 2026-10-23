<?php namespace ProcessWire;
// Post processor for Tracy Logs panel - delete all Tracy log files
// TRACY LOGS
// delete Tracy logs if requested
// superusers (and local / switched users), or users with PW's logs-edit permission, as for PW's own Logs page
if((TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser || $this->wire('user')->hasPermission('logs-edit')) && $this->wire('input')->post->deleteTracyLogs && $this->wire('session')->CSRF->validate()) {
    wireRmdir($logFolder, true);
    wireMkdir($logFolder);
}
