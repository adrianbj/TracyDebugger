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
                        $rootPath = str_replace('\\', '/', $this->wire('config')->paths->root);
                        $editorPath = str_replace('\\', '/', (string) $this->wire('input')->post->fileEditorFilePath);
                        $filePath = str_replace('\\', '/', (string) realpath($rootPath . $editorPath));
                        if($filePath === '') {
                            $filePath = str_replace('\\', '/', (string) realpath($editorPath));
                        }
                        $isWindows = DIRECTORY_SEPARATOR === '\\';
                        $prefixOk = $isWindows ? (stripos($filePath, $rootPath) === 0) : (strpos($filePath, $rootPath) === 0);
                        $isAjaxSave = $this->wire('config')->ajax && ($this->wire('input')->post->tracySaveFileCode || $this->wire('input')->post->tracyChangeTemplateCode);
                        $writeSucceeded = false;
                        $errorMsg = '';
                        if($editorPath != '' && $filePath !== '' && $prefixOk) {
                            $rawCode = base64_decode($this->wire('input')->post->tracyFileEditorRawCode);
                            $relPath = $isWindows ? substr($filePath, strlen($rootPath)) : str_replace($rootPath, '', $filePath);

                            // backup old version to Tracy cache directory
                            $cachePath = $this->tracyCacheDir . $relPath;
                            if(!is_dir($cachePath)) if(!wireMkdir(pathinfo($cachePath, PATHINFO_DIRNAME), true)) {
                                if($isAjaxSave) {
                                    $errorMsg = "Unable to create cache path: $cachePath";
                                }
                                else {
                                    throw new WireException("Unable to create cache path: $cachePath");
                                }
                            }
                            if(!$errorMsg) {
                                copy($filePath, $cachePath);

                                if(!$this->wire('files')->filePutContents($filePath, $rawCode, LOCK_EX)) {
                                    if($isAjaxSave) {
                                        $errorMsg = "Unable to write file: " . $filePath;
                                    }
                                    else {
                                        throw new WireException("Unable to write file: " . $filePath);
                                    }
                                }
                                else {
                                    if($this->wire('config')->chmodFile && PHP_OS_FAMILY !== 'Windows') chmod($filePath, octdec($this->wire('config')->chmodFile));

                                    if($this->wire('input')->post->tracyTestFileCode) {
                                        if(PHP_VERSION_ID >= 70300) {
                                            setcookie('tracyTestFileEditor', $relPath, ['expires' => time() + (10 * 365 * 24 * 60 * 60), 'path' => '/', 'samesite' => 'Strict']);
                                        } else {
                                            setcookie('tracyTestFileEditor', $relPath, time() + (10 * 365 * 24 * 60 * 60), '/');
                                        }
                                    }
                                    $writeSucceeded = true;
                                }
                            }
                        }
                        else if($isAjaxSave) {
                            $errorMsg = 'Invalid file path';
                        }
                        if($isAjaxSave) {
                            header('Content-Type: application/json');
                            echo json_encode(array(
                                'status' => $writeSucceeded ? 'ok' : 'error',
                                'message' => $errorMsg,
                                'filePath' => $editorPath,
                            ));
                            exit;
                        }
                        $this->wire('session')->redirect($this->httpReferer);
                    }
                }

                // if file editor restore
                if($this->wire('input')->post->tracyRestoreFileEditorBackup && $this->wire('session')->CSRF->validate()) {
                    $rootPath = str_replace('\\', '/', $this->wire('config')->paths->root);
                    $editorPath = str_replace('\\', '/', (string) ($this->wire('input')->post->fileEditorFilePath ?: $this->wire('input')->cookie->tracyTestFileEditor));
                    $this->filePath = str_replace('\\', '/', (string) realpath($rootPath . $editorPath));
                    if($this->filePath === '') {
                        $this->filePath = str_replace('\\', '/', (string) realpath($editorPath));
                    }
                    $isWindows = DIRECTORY_SEPARATOR === '\\';
                    $prefixOk = $isWindows ? (stripos($this->filePath, $rootPath) === 0) : (strpos($this->filePath, $rootPath) === 0);
                    $tracyCacheDirNorm = str_replace('\\', '/', $this->tracyCacheDir);
                    if($this->filePath !== '' && $prefixOk) {
                        $relPath = $isWindows ? substr($this->filePath, strlen($rootPath)) : str_replace($rootPath, '', $this->filePath);
                        $this->cachePath = str_replace('\\', '/', (string) realpath($tracyCacheDirNorm . $relPath));
                        $cachePrefixOk = $isWindows ? (stripos($this->cachePath, $tracyCacheDirNorm) === 0) : (strpos($this->cachePath, $tracyCacheDirNorm) === 0);
                        if($this->cachePath !== '' && $cachePrefixOk) {
                            copy($this->cachePath, $this->filePath);
                            unlink($this->cachePath);
                        }
                    }
                    $this->wire('session')->redirect($this->httpReferer);
                }
            }
