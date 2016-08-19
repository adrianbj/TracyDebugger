<?php

$functions = get_defined_functions();
\TracyDebugger::$initialFuncs = $functions['user'];

\TracyDebugger::$initialConsts = get_defined_constants();
