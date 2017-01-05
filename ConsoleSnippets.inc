<?php

$configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
$configData['snippets'] = $_POST['snippets'];
$this->modules->saveModuleConfigData($this->modules->get("TracyDebugger"), $configData);
exit;