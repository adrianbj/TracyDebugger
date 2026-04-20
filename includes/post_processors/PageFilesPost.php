<?php namespace ProcessWire;
// Post processor for Page Files panel - delete orphaned and missing page files
// PAGE FILES
// delete orphaned files if requested
if($this->wire('input')->post->deleteOrphanFiles && $this->wire('input')->post->orphanPaths && $this->wire('session')->CSRF->validate()) {
    $rootPath = $this->wire('config')->paths->root;
    foreach(explode('|', $this->wire('input')->post->orphanPaths) as $filePath) {
        $realPath = str_replace('\\', '/', realpath($filePath));
        if($realPath !== false && strpos($realPath, $rootPath) === 0 && file_exists($realPath)) {
            unlink($realPath);
        }
    }
    $this->wire('session')->redirect($this->httpReferer);
}
// delete missing pagefiles if requested
if($this->wire('input')->post->deleteMissingFiles && $this->wire('input')->post->missingPaths && $this->wire('session')->CSRF->validate()) {
    $decodedPaths = json_decode(urldecode($this->wire('input')->post->missingPaths), true);
    if(is_array($decodedPaths)) {
        foreach($decodedPaths as $pid => $files) {
            $p = $this->wire('pages')->get($pid);
            foreach($files as $file) {
                $pagefile = $p->{$file['field']}->get(pathinfo($file['filename'], PATHINFO_BASENAME));
                $p->{$file['field']}->delete($pagefile);
                $p->save($file['field']);
            }
        }
    }
    $this->wire('session')->redirect($this->httpReferer);
}
