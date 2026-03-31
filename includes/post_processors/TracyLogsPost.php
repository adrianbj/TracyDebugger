// Post processor for Tracy Logs panel - delete all Tracy log files
// TRACY LOGS
// delete Tracy logs if requested
if($this->wire('input')->post->deleteTracyLogs && $this->wire('session')->CSRF->validate()) {
    wireRmdir($logFolder, true);
    wireMkdir($logFolder);
}
