<?php

$configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
$configData['snippets'] = $_POST['snippets'];
$this->wire('modules')->saveModuleConfigData($this->wire('modules')->get("TracyDebugger"), $configData);
exit;