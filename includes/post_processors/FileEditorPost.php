<?php namespace ProcessWire;
// Post processor for File Editor panel - test, save, restore template/file code

            // FILE/TEMPLATE EDITOR
            if(static::$allowedSuperuser || self::$validLocalUser || self::$validSwitchedUser) {
                if($this->wire('input')->post->fileEditorFilePath && $this->wire('session')->CSRF->validate()) {
                    $rawCode = base64_decode($this->wire('input')->post->tracyFileEditorRawCode);
                    if(static::$inAdmin &&
                        $this->data['referencePageEdited'] &&
                        $this->wire('input')->get('id') &&
                        $this->wire('pages')->get($this->wire('input')->get('id'))->template->filename === $this->wire('config')->paths->root . $this->wire('input')->post->fileEditorFilePath
                    ) {
                        $p = $this->wire('pages')->get($this->wire('input')->get('id'));
                    }
                    else {
                        $p = $this->wire('page');
                    }

                    $templateExt = pathinfo($p->template->filename, PATHINFO_EXTENSION);
                    $this->tempTemplateFilename = str_replace('.'.$templateExt, '-tracytemp.'.$templateExt, $p->template->filename);
                    // if changes to the template of the current page are submitted
                    // test
                    if($this->wire('input')->post->tracyTestTemplateCode) {
                        if(!$this->wire('files')->filePutContents($this->tempTemplateFilename, $rawCode, LOCK_EX)) {
                            throw new WireException("Unable to write file: " . $this->tempTemplateFilename);
                        }
                        $p->template->filename = $this->tempTemplateFilename;
                    }

                    // if changes to any other file are submitted
                    if($this->wire('input')->post->tracyTestFileCode || $this->wire('input')->post->tracySaveFileCode || $this->wire('input')->post->tracyChangeTemplateCode) {
                        $rootPath = $this->wire('config')->paths->root;
                        $filePath = str_replace('\\', '/', realpath($rootPath . $this->wire('input')->post->fileEditorFilePath));
                        if($this->wire('input')->post->fileEditorFilePath != '' && $filePath !== false && strpos($filePath, $rootPath) === 0) {
                            $rawCode = base64_decode($this->wire('input')->post->tracyFileEditorRawCode);

                            // backup old version to Tracy cache directory
                            $cachePath = $this->tracyCacheDir . $this->wire('input')->post->fileEditorFilePath;
                            if(!is_dir($cachePath)) if(!wireMkdir(pathinfo($cachePath, PATHINFO_DIRNAME), true)) {
                                throw new WireException("Unable to create cache path: $cachePath");
                            }
                            copy($filePath, $cachePath);

                            if(!$this->wire('files')->filePutContents($filePath, $rawCode, LOCK_EX)) {
                                throw new WireException("Unable to write file: " . $filePath);
                            }
                            if($this->wire('config')->chmodFile && PHP_OS_FAMILY !== 'Windows') chmod($filePath, octdec($this->wire('config')->chmodFile));

                            if($this->wire('input')->post->tracyTestFileCode) {
                                if(PHP_VERSION_ID >= 70300) {
                                    setcookie('tracyTestFileEditor', $this->wire('input')->post->fileEditorFilePath, ['expires' => time() + (10 * 365 * 24 * 60 * 60), 'path' => '/', 'samesite' => 'Strict']);
                                } else {
                                    setcookie('tracyTestFileEditor', $this->wire('input')->post->fileEditorFilePath, time() + (10 * 365 * 24 * 60 * 60), '/');
                                }
                            }
                        }
                        $this->wire('session')->redirect($this->httpReferer);
                    }
                }

                // if file editor restore
                if($this->wire('input')->post->tracyRestoreFileEditorBackup && $this->wire('session')->CSRF->validate()) {
                    $rootPath = $this->wire('config')->paths->root;
                    $editorPath = $this->wire('input')->post->fileEditorFilePath ?: $this->wire('input')->cookie->tracyTestFileEditor;
                    $this->filePath = str_replace('\\', '/', realpath($rootPath . $editorPath));
                    $this->cachePath = str_replace('\\', '/', realpath($this->tracyCacheDir . $editorPath));
                    if($this->filePath !== false && strpos($this->filePath, $rootPath) === 0 &&
                       $this->cachePath !== false && strpos($this->cachePath, $this->tracyCacheDir) === 0) {
                        copy($this->cachePath, $this->filePath);
                        unlink($this->cachePath);
                    }
                    $this->wire('session')->redirect($this->httpReferer);
                }
            }
