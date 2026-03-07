<?php namespace ProcessWire;

if(TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser) {

    $rootPath = $this->wire('config')->paths->root;
    $resolvedPath = realpath($rootPath . $_POST['filePath']);

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
        $fileData['backupExists'] = file_exists($this->wire('config')->paths->cache . 'TracyDebugger/' . $_POST['filePath']) ? true : false;
        $fileData['isTemplateFile'] = $resolvedPath === $p->template->filename;
        $fileData['contents'] = file_get_contents($resolvedPath);
        echo json_encode($fileData);
    }
}
exit;
