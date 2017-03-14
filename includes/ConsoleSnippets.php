<?php

$configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
$configData['snippets'] = $_POST['snippets'];
$this->modules->saveModuleConfigData($this->wire('modules')->get("TracyDebugger"), $configData);
exit;