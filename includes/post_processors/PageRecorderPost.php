<?php namespace ProcessWire;
// Post processor for Page Recorder panel - trash or clear recorded pages
// PAGE RECORDER
// trash / clear recorded pages if requested
if(($this->wire('input')->post->trashRecordedPages || $this->wire('input')->post->clearRecordedPages) && $this->wire('session')->CSRF->validate()) {
    if($this->wire('input')->post->trashRecordedPages) {
        // the list holds pages recorded in any Tracy user's requests, so only trash those this user may trash
        foreach($this->data['recordedPages'] as $pid) {
            $p = $this->wire('pages')->get((int) $pid);
            if($p->id && $p->trashable()) $this->wire('pages')->trash($p);
        }
    }
    $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
    unset($configData['recordedPages']);
    $this->wire('modules')->saveModuleConfigData($this, $configData);
    $this->wire('session')->redirect($this->httpReferer);
}
