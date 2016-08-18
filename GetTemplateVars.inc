<?php

\TracyDebugger::$templateVars = \TracyDebugger::templateVars(get_defined_vars());
unset(\TracyDebugger::$templateVars['returnValue']);
