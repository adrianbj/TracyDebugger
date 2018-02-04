<?php

\TracyDebugger::$templateVars = \TracyDebugger::templateVars(get_defined_vars());
unset(\TracyDebugger::$templateVars['returnValue']);

\TracyDebugger::$templateConsts = array_diff_assoc(get_defined_constants(), \TracyDebugger::$initialConsts);

$functions = get_defined_functions();
\TracyDebugger::$templateFuncs = array_diff($functions['user'], \TracyDebugger::$initialFuncs);

$includedFiles = array();
foreach(get_included_files() as $includedFile) {
	$includedFile = \TracyDebugger::forwardSlashPath($includedFile);
	if(strpos($includedFile, $this->wire('config')->paths->site) !== false &&
		strpos($includedFile, DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR) === false &&
		strpos($includedFile, 'config.php') === false) {
		if(!in_array($includedFile, $includedFiles)) $includedFiles[] = $includedFile;
	}
}
\TracyDebugger::$includedFiles = $includedFiles;
// store in session for use by console panel
$this->wire('session')->tracyIncludedFiles = \TracyDebugger::$includedFiles;