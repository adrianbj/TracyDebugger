<?php namespace ProcessWire;
// Post processor for Page Files panel - delete orphaned and missing page files
// PAGE FILES
// both delete files, so like the File Editor they're limited to superusers (and local / switched users), not
// every tracy-debugger user, and to page files: orphan paths must be inside site/assets/files/ and missing
// pagefiles are only removed from pages the user can edit
$tracyPageFilesAllowed = TracyDebugger::$allowedSuperuser || TracyDebugger::$validLocalUser || TracyDebugger::$validSwitchedUser;
// delete orphaned files if requested
if($tracyPageFilesAllowed && $this->wire('input')->post->deleteOrphanFiles && $this->wire('input')->post->orphanPaths && $this->wire('session')->CSRF->validate()) {
    $filesPath = rtrim(str_replace('\\', '/', (string) realpath($this->wire('config')->paths->files)), '/') . '/';
    foreach(explode('|', $this->wire('input')->post->orphanPaths) as $filePath) {
        $realPath = realpath($filePath);
        if($realPath === false || $filesPath === '/') continue;
        $realPath = str_replace('\\', '/', $realPath);
        if(strpos($realPath, $filesPath) === 0 && is_file($realPath)) {
            unlink($realPath);
        }
    }
    $this->wire('session')->redirect($this->httpReferer);
}
// delete missing pagefiles if requested
if($tracyPageFilesAllowed && $this->wire('input')->post->deleteMissingFiles && $this->wire('input')->post->missingPaths && $this->wire('session')->CSRF->validate()) {
    $decodedPaths = json_decode(urldecode($this->wire('input')->post->missingPaths), true);
    if(is_array($decodedPaths)) {
        foreach($decodedPaths as $pid => $files) {
            $p = $this->wire('pages')->get((int) $pid);
            if(!$p->id || !$p->editable() || !is_array($files)) continue;
            foreach($files as $file) {
                if(!isset($file['field'], $file['filename']) || !$p->editable($file['field'])) continue;
                $pagefiles = $p->getUnformatted($file['field']);
                if(!$pagefiles instanceof Pagefiles) continue;
                $pagefile = $pagefiles->get(pathinfo($file['filename'], PATHINFO_BASENAME));
                if(!$pagefile) continue;
                $pagefiles->delete($pagefile);
                $p->save($file['field']);
            }
        }
    }
    $this->wire('session')->redirect($this->httpReferer);
}
