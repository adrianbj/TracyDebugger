<?php namespace ProcessWire;
// Post processor for Page Recorder panel - trash or clear recorded pages
// PAGE RECORDER
// trash / clear recorded pages if requested
if(($this->wire('input')->post->trashRecordedPages || $this->wire('input')->post->clearRecordedPages) && $this->wire('session')->CSRF->validate()) {
    // the list holds pages recorded in any Tracy user's requests, so only trash those this user may trash; and
    // only superusers (and local / switched users) clear the whole list: for other users the pages they
    // couldn't trash, or for a clear, can't edit, stay on it
    $tracyPageRecorderAll = TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser;
    $keptPages = array();
    foreach((array) $this->data['recordedPages'] as $pid) {
        $p = $this->wire('pages')->get((int) $pid);
        if($this->wire('input')->post->trashRecordedPages) {
            if($p->id && $p->trashable()) $this->wire('pages')->trash($p);
            elseif($p->id && !$tracyPageRecorderAll) $keptPages[] = $pid;
        }
        elseif($p->id && !$tracyPageRecorderAll && !$p->editable()) {
            $keptPages[] = $pid;
        }
    }
    $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
    unset($configData['recordedPages']);
    if($keptPages) $configData['recordedPages'] = $keptPages;
    $this->wire('modules')->saveModuleConfigData($this, $configData);
    $this->wire('session')->redirect($this->httpReferer);
}
