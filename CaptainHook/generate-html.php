<?php

require_once "classes/CaptainHookSearch.php";

$excludedFilenames = array("AdminTheme.php");
$this->hooks = CaptainHookSearch::getHooks(wire('config')->paths->root.'wire/', $excludedFilenames);