<?php namespace ProcessWire;

if(TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser) {

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
        $fileData['backupExists'] = file_exists($this->wire('config')->paths->cache . 'TracyDebugger/' . $relPath) ? true : false;
        $templateFilename = str_replace('\\', '/', (string) $p->template->filename);
        $fileData['isTemplateFile'] = $isWindows ? (strcasecmp($resolvedPath, $templateFilename) === 0) : ($resolvedPath === $templateFilename);
        $fileData['contents'] = file_get_contents($resolvedPath);
        echo json_encode($fileData);
    }
}
exit;
