<?php

require_once wire('config')->paths->TracyDebugger . 'CaptainHook/classes/CaptainHookSearch.php';

$excludedFilenames = array();
// not sure why this was being excluded
//$excludedFilenames = array("AdminTheme.php");

$wireHooks = CaptainHookSearch::getHooks(wire('config')->paths->root.'wire/', $excludedFilenames);
$siteHooks = CaptainHookSearch::getHooks(wire('config')->paths->siteModules, $excludedFilenames);
$this->hooks = array_merge($wireHooks, $siteHooks);
