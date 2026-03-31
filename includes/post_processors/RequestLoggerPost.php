// Post processor for Request Logger panel - enable/disable page logging
// REQUEST LOGGER
// enable/disable page logging
if(($this->wire('input')->post->tracyRequestLoggerEnableLogging || $this->wire('input')->post->tracyRequestLoggerDisableLogging) && $this->wire('session')->CSRF->validate()) {
    $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
    if($this->wire('input')->post->tracyRequestLoggerEnableLogging) {
        if(!isset($configData['requestLoggerPages'])) $configData['requestLoggerPages'] = array();
        array_push($configData['requestLoggerPages'], $this->wire('input')->post->requestLoggerLogPageId);
    }
    else {
        if(($key = array_search($this->wire('input')->post->requestLoggerLogPageId, $configData['requestLoggerPages'])) !== false) {
            unset($configData['requestLoggerPages'][$key]);
        }
        $data = $this->wire('cache')->get("tracyRequestLogger_id_*_page_".$this->wire('input')->post->requestLoggerLogPageId);
        if(count($data) > 0) {
            foreach($data as $id => $datum) {
                $this->wire('cache')->delete($id);
            }
        }
    }
    $this->wire('modules')->saveModuleConfigData($this, $configData);
    $this->wire('session')->redirect($this->httpReferer);
}
