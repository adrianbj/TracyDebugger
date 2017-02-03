<?php

require_once wire('config')->paths->TracyDebugger . 'panels/CaptainHook/CaptainHookSearch.php';

$excludedFilenames = array();

// make sure /wire/ is first so that duplicate addHook instances are excluded from /site/ and not /wire/ files
$wireHooks = CaptainHookSearch::getHooks(wire('config')->paths->root.'wire/', $excludedFilenames);
$siteHooks = CaptainHookSearch::getHooks(wire('config')->paths->siteModules, $excludedFilenames);
$this->hooks = array_merge($wireHooks, $siteHooks);
