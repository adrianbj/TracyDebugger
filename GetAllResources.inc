<?php

\TracyDebugger::$templateVars = \TracyDebugger::templateVars(get_defined_vars());
unset(\TracyDebugger::$templateVars['returnValue']);

$constants = get_defined_constants();
\TracyDebugger::$templateConsts = array_slice($constants, \TracyDebugger::$lastInitialConst-1);

$functions = get_defined_functions();
\TracyDebugger::$templateFuncs = array_slice($functions['user'], \TracyDebugger::$lastInitialFunc-1);

$includedFiles = get_included_files();
foreach($includedFiles as $includedFile) {
	if(strpos($includedFile, $this->wire('config')->paths->site) !== false &&
		strpos($includedFile, '/site/modules/') === false &&
		strpos($includedFile, 'config.php') === false) {
		\TracyDebugger::$includedFiles[] = $includedFile;
	}
}
// store in session for use by console panel
$this->wire('session')->tracyIncludedFiles = \TracyDebugger::$includedFiles;
