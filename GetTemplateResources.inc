<?php

\TracyDebugger::$templateVars = \TracyDebugger::templateVars(get_defined_vars());
unset(\TracyDebugger::$templateVars['returnValue']);

\TracyDebugger::$templateConsts = array_diff_assoc(get_defined_constants(), \TracyDebugger::$initialConsts);

$functions = get_defined_functions();
\TracyDebugger::$templateFuncs = array_diff($functions['user'], \TracyDebugger::$initialFuncs);

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
