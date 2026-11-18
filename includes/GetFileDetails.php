<?php namespace ProcessWire;

// the Tracy Exceptions panel reads its files here too (exceptionFile, see below)
if((TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser) && !TracyDebugger::panelRestricted($this->wire('input')->post->exceptionFile ? 'tracyExceptions' : 'fileEditor')) {

    // validate CSRF token, falling back to a same-origin check so a stale
    // baked-in token (session changed under an open File Editor tab) doesn't fail
    $csrfToken = isset($_POST['csrfToken']) ? $_POST['csrfToken'] : '';
    if(!TracyDebugger::validTracyRequest($this->wire('session')->tracyFileEditorToken, $csrfToken)) {
        http_response_code(403);
        echo json_encode(array('error' => 'CSRF token validation failed'));
        exit;
    }

    $rootPath = str_replace('\\', '/', $this->wire('config')->paths->root);
    $filePath = str_replace('\\', '/', (string) $_POST['filePath']);
    $resolvedPath = str_replace('\\', '/', (string) realpath($rootPath . $filePath));
    if($resolvedPath === '') {
        $resolvedPath = str_replace('\\', '/', (string) realpath($filePath));
    }

    // Windows file paths are case-insensitive and realpath() returns the
    // canonical case, which may differ from paths->root. Use stripos there.
    $isWindows = DIRECTORY_SEPARATOR === '\\';
    $prefixOk = $isWindows ? (stripos($resolvedPath, $rootPath) === 0) : (strpos($resolvedPath, $rootPath) === 0);

    // the Tracy Exceptions panel renders the file it asks for as HTML, so it only gets the exception files
    // that Tracy logged, not any file under the root (which could be one a user uploaded)
    if($prefixOk && $this->wire('input')->post->exceptionFile) {
        $exceptionsPath = str_replace('\\', '/', (string) realpath($this->wire('config')->paths->logs . 'tracy'));
        $inExceptionsPath = $exceptionsPath !== '' && ($isWindows
            ? stripos($resolvedPath, $exceptionsPath . '/') === 0
            : strpos($resolvedPath, $exceptionsPath . '/') === 0);
        if(!$inExceptionsPath || !preg_match('/\.(html|md)$/iD', $resolvedPath)) $prefixOk = false;
    }

    if($resolvedPath !== '' && $prefixOk) {

        if(TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') &&
            ($this->wire('process') == 'ProcessPageEdit' ||
                $this->wire('process') == 'ProcessUser' ||
                $this->wire('process') == 'ProcessRole' ||
                $this->wire('process') == 'ProcessPermission'
            )
        ) {
            $p = $this->wire('process')->getPage();
            if($p instanceof NullPage) {
                $p = $this->wire('pages')->get((int) $this->wire('input')->get('id'));
            }
        }
        else {
            $p = $this->wire('page');
        }

        $fileData = array();
        $fileData['writeable'] = is_writable($resolvedPath);
        $relPath = $isWindows
            ? substr($resolvedPath, strlen($rootPath))
            : str_replace($rootPath, '', $resolvedPath);
        $fileData['backupExists'] = file_exists(TracyDebugger::fileEditorBackupPath() . $relPath) ? true : false;
        $templateFilename = str_replace('\\', '/', (string) $p->template->filename);
        $fileData['isTemplateFile'] = $isWindows ? (strcasecmp($resolvedPath, $templateFilename) === 0) : ($resolvedPath === $templateFilename);
        $fileData['contents'] = file_get_contents($resolvedPath);
        // an exception's Markdown copy is for pasting into an AI tool, so it gets the same scrub as the dumps' copy
        if($this->wire('input')->post->exceptionFile && preg_match('/\.md$/iD', $resolvedPath)) {
            $fileData['contents'] = AIExport::scrub((string) $fileData['contents']);
        }
        echo json_encode($fileData);
    }
}
exit;
