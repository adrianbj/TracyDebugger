<?php namespace ProcessWire;
// Post processor for ProcessWire Logs panel - delete all ProcessWire log files
// PROCESSWIRE LOGS
// delete ProcessWire logs if requested
if($this->wire('input')->post->deleteProcessWireLogs && $this->wire('session')->CSRF->validate()) {
    $files = glob($this->wire('config')->paths->logs.'*');
    foreach($files as $file) {
        if(is_file($file)) {
            unlink($file);
        }
    }
}
