<?php

$functions = get_defined_functions();
$userFuncKeys = array_keys($functions['user']);
\TracyDebugger::$lastInitialFunc = array_pop($userFuncKeys);

$constants = get_defined_constants();
$constKeys = array_keys($constants);
\TracyDebugger::$lastInitialConst = array_pop($constKeys);
