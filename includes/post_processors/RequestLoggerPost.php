<?php namespace ProcessWire;
// Post processor for Request Logger panel - enable/disable page logging
// REQUEST LOGGER
// enable/disable page logging
// superusers (and local / switched users) only: logging captures other visitors' requests and rendered pages
if((TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser) && ($this->wire('input')->post->tracyRequestLoggerEnableLogging || $this->wire('input')->post->tracyRequestLoggerDisableLogging) && $this->wire('session')->CSRF->validate()) {
    $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
    $requestLoggerLogPageId = (int) $this->wire('input')->post->requestLoggerLogPageId;
    if($this->wire('input')->post->tracyRequestLoggerEnableLogging) {
        if(!isset($configData['requestLoggerPages'])) $configData['requestLoggerPages'] = array();
        array_push($configData['requestLoggerPages'], $requestLoggerLogPageId);
    }
    else {
        if(($key = array_search($requestLoggerLogPageId, $configData['requestLoggerPages'])) !== false) {
            unset($configData['requestLoggerPages'][$key]);
        }
        $data = $this->wire('cache')->get("tracyRequestLogger_id_*_page_".$requestLoggerLogPageId);
        if(count($data) > 0) {
            foreach($data as $id => $datum) {
                $this->wire('cache')->delete($id);
            }
        }
    }
    $this->wire('modules')->saveModuleConfigData($this, $configData);
    $this->wire('session')->redirect($this->httpReferer);
}
