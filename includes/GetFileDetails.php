<?php
if((\TracyDebugger::$allowedSuperuser || \TracyDebugger::$validLocalUser || \TracyDebugger::$validSwitchedUser) && strpos($_POST['filePath'], '..') === false && file_exists($_POST['filePath'])) {

    if(\TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') &&
        ($this->wire('process') == 'ProcessPageEdit' ||
            $this->wire('process') == 'ProcessUser' ||
            $this->wire('process') == 'ProcessRole' ||
            $this->wire('process') == 'ProcessPermission'
        )
    ) {
        $p = $this->wire('process')->getPage();
    }
    else {
        $p = $this->wire('page');
    }

    $fileData = array();
    $fileData['writeable'] = is_writable($_POST['filePath']);
    $fileData['backupExists'] = file_exists($this->wire('config')->paths->cache . 'TracyDebugger/' . $_POST['filePath']) ? true : false;
    $fileData['isTemplateFile'] = $this->wire('config')->paths->root . $_POST['filePath'] === $p->template->filename;
    $fileData['contents'] = file_get_contents($this->wire('config')->paths->root . $_POST['filePath']);
    echo json_encode($fileData);
}
exit;