<?php namespace ProcessWire;

if(TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser) {

    // validate CSRF token
    $csrfToken = isset($_POST['csrfToken']) ? $_POST['csrfToken'] : '';
    if(!$csrfToken || !hash_equals((string)$this->wire('session')->tracyFileEditorToken, $csrfToken)) {
        http_response_code(403);
        echo json_encode(array('error' => 'CSRF token validation failed'));
        exit;
    }

    $rootPath = $this->wire('config')->paths->root;
    $filePath = $_POST['filePath'];
    $resolvedPath = str_replace('\\', '/', realpath($rootPath . $filePath));
    if($resolvedPath === false || $resolvedPath === '') {
        $resolvedPath = str_replace('\\', '/', realpath($filePath));
    }

    if($resolvedPath !== false && strpos($resolvedPath, $rootPath) === 0) {

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
        $relPath = str_replace($rootPath, '', $resolvedPath);
        $fileData['backupExists'] = file_exists($this->wire('config')->paths->cache . 'TracyDebugger/' . $relPath) ? true : false;
        $fileData['isTemplateFile'] = $resolvedPath === $p->template->filename;
        $fileData['contents'] = file_get_contents($resolvedPath);
        echo json_encode($fileData);
    }
}
exit;
