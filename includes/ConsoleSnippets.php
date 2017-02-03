<?php

$configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
$configData['snippets'] = $_POST['snippets'];
bdl($configData['snippets']);
$this->modules->saveModuleConfigData($this->wire('modules')->get("TracyDebugger"), $configData);
exit;