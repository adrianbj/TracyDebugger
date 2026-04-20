<?php namespace ProcessWire;
// Post processor for Page Recorder panel - trash or clear recorded pages
// PAGE RECORDER
// trash / clear recorded pages if requested
if(($this->wire('input')->post->trashRecordedPages || $this->wire('input')->post->clearRecordedPages) && $this->wire('session')->CSRF->validate()) {
    if($this->wire('input')->post->trashRecordedPages) {
        foreach($this->data['recordedPages'] as $pid) {
            $this->wire('pages')->trash($this->wire('pages')->get($pid));
        }
    }
    $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
    unset($configData['recordedPages']);
    $this->wire('modules')->saveModuleConfigData($this, $configData);
    $this->wire('session')->redirect($this->httpReferer);
}
