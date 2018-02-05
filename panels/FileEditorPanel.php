<?php

class FileEditorPanel extends BasePanel {

    protected $icon;
    protected $tracyFileEditorFilePath;
    protected $errorMessage = null;
    protected $encoding = 'auto';

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('fileEditor');

        if(\TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') &&
            ($this->wire('process') == 'ProcessPageEdit' ||
                $this->wire('process') == 'ProcessUser' ||
                $this->wire('process') == 'ProcessRole' ||
                $this->wire('process') == 'ProcessPermission'
            )
        ) {
            $this->p = $this->wire('process')->getPage();
        }
        else {
            $this->p = $this->wire('page');
        }

        $this->tracyFileEditorFilePath = $this->wire('input')->cookie->tracyFileEditorFilePath ?: str_replace($this->wire('config')->paths->root, '', $this->p->template->filename);

        if(isset($_POST['tracyTestTemplateCode']) || $this->wire('input')->cookie->tracyTestFileEditor) {
            $iconColor = '#D51616';
        }
        else {
            $iconColor = '#444444';
        }

        $this->icon = '
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="14.2px" height="16px" viewBox="388.9 298 14.2 16" enable-background="new 388.9 298 14.2 16" xml:space="preserve">
                    <path fill="'.$iconColor.'" d="M394.6,307.5c-0.1,0.1-0.1,0.1-0.1,0.2l-1,3.2c0,0.1,0,0.2,0.1,0.3c0.1,0.1,0.1,0.1,0.2,0.1c0,0,0,0,0.1,0
                        c0,0,0,0,0,0l3.3-1.1c0.1,0,0.1-0.1,0.1-0.1l5.9-5.9c0.1-0.1,0.1-0.1,0.1-0.2c0-0.1,0-0.2-0.1-0.2l-2.2-2.2
                        c-0.1-0.1-0.1-0.1-0.2-0.1c-0.1,0-0.2,0-0.2,0.1l-0.2,0.2v-3.4c0-0.1,0-0.2-0.1-0.3c-0.1-0.1-0.2-0.1-0.3-0.1h-6.5l0,0h-0.8
                        c0,0,0,0,0,0h-0.1v0.1l-3.3,3.3c0,0,0,0,0,0.1h0v0.1v0.9v11.2c0,0.1,0,0.2,0.1,0.3c0.1,0.1,0.2,0.1,0.3,0.1h3.1h4.2h3.1
                        c0.1,0,0.2,0,0.3-0.1c0.1-0.1,0.1-0.2,0.1-0.4v-4.2c0-0.1,0-0.1-0.1-0.2c0,0-0.1,0-0.2,0.1l-0.2,0.2c-0.1,0.1-0.2,0.2-0.4,0.3
                        c-0.1,0.1-0.3,0.3-0.6,0.6v2.3h-2.1h-4.2h-2.1v-10.1h2.9c0.1,0,0.2-0.1,0.2-0.2v-2.9h5.4v3.9L394.6,307.5z M394.6,310l0.6-1.8
                        l1.2,1.2L394.6,310z"/>
            </svg>';

        return '
        <span title="File Editor">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;File Editor' : '') . '
        </span>
        ';
    }


    public function getPanel() {

        $tracyModuleUrl = $this->wire("config")->urls->TracyDebugger;

        $filePath = $this->wire('config')->paths->root . $this->tracyFileEditorFilePath;

        $tracyFileEditorFileData = null;
        if(file_exists($filePath)) {
            $tracyFileEditorFileCode = json_encode(file_get_contents(strpos($this->p->template->filename, '-tracytemp') !== false ? $this->p->template->filename : $filePath));
            $tracyFileEditorFileData = array(
                'writeable' => is_writable($filePath),
                'backupExists' => !isset($_POST['tracyTestTemplateCode']) && file_exists($this->wire('config')->paths->cache . 'TracyDebugger/' . $this->tracyFileEditorFilePath) ? true : false,
                'isTemplateFile' => str_replace('-tracytemp', '', $this->p->template->filename) === $filePath,
                'isTemplateTest' => strpos($this->p->template->filename, '-tracytemp')
            );
            $tracyFileEditorFileData = json_encode($tracyFileEditorFileData);
        }
        else {
            $tracyFileEditorFileCode = json_encode($this->tracyFileEditorFilePath . ' does not exist');
        }

        $out = '<script>' . file_get_contents($this->wire("config")->paths->TracyDebugger . 'scripts/js-loader.js') . '</script>';
        $out .= '<script>' . file_get_contents($this->wire("config")->paths->TracyDebugger . 'scripts/file-editor.js') . '</script>';

        $out .= <<< HTML
        <script>

            var tracyFileEditor = {

                tfe: {},
                tracyModuleUrl: "$tracyModuleUrl",
                tracyFileEditorFilePath: "{$this->tracyFileEditorFilePath}",
                errorMessage: "{$this->errorMessage}",

                getRawFileEditorCode: function() {
                    document.cookie = "tracyFileEditorFilePath=" + document.getElementById('fileEditorFilePath').value + "; path=/";

                    // doing btoa because if code contains <script>, browsers are failing with ERR_BLOCKED_BY_XSS_AUDITOR
                    document.getElementById("tracyFileEditorRawCode").innerHTML = btoa(unescape(encodeURIComponent(tracyFileEditor.tfe.getValue())));
                },

                saveToLocalStorage: function(name) {
                    var tracyHistoryItem = {selections: tracyFileEditor.tfe.selection.toJSON(), scrollTop: tracyFileEditor.tfe.session.getScrollTop(), scrollLeft: tracyFileEditor.tfe.session.getScrollLeft()};
                    localStorage.setItem(name, JSON.stringify(tracyHistoryItem));
                },

                resizeAce: function() {
                    var ml = Math.round((document.getElementById("tracy-debug-panel-FileEditorPanel").offsetHeight - 160) / 23);
                    tracyFileEditor.tfe.setOptions({
                        minLines: ml,
                        maxLines: ml
                    });
                }

            };

            tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/ace-editor/ace.js", function() {
                if(typeof ace !== "undefined") {
                    tracyFileEditor.tfe = ace.edit("tracyFileEditorCode");
                    tracyFileEditor.tfe.container.style.lineHeight = 1.8;
                    tracyFileEditor.tfe.setFontSize(13);
                    tracyFileEditor.tfe.setShowPrintMargin(false);
                    tracyFileEditor.tfe.\$blockScrolling = Infinity; //fix deprecation warning

                    tracyFileEditor.tfe.on("beforeEndOperation", function(e) {
                        tracyFileEditor.saveToLocalStorage('tracyFileEditor');
                    });

                    tracyFileEditor.tfe.session.on("changeScrollTop", function() {
                        tracyFileEditor.saveToLocalStorage('tracyFileEditor');
                    });
                    tracyFileEditor.tfe.session.on("changeScrollLeft", function() {
                        tracyFileEditor.saveToLocalStorage('tracyFileEditor');
                    });

                    // set theme
                    tracyFileEditor.tfe.setTheme("ace/theme/tomorrow_night");

                    // set mode appropriately
                    // in ext-modelist.js I have added "inc" to PHP and "latte" to Twig
                    tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/ace-editor/ext-modelist.js", function() {
                        tracyFileEditor.modelist = ace.require("ace/ext/modelist");
                        var mode = tracyFileEditor.modelist.getModeForPath(tracyFileEditor.tracyFileEditorFilePath).mode;
                        tracyFileEditor.tfe.session.setMode(mode);
                    });

                    // set autocomplete and other options
                    ace.config.loadModule('ace/ext/language_tools', function () {
                        document.getElementById("tracyFileEditorCode").style.visibility = "visible";
                        if(tracyFileEditor.tracyFileEditorFilePath) {
                            tracyFileEditor.tfe.setValue($tracyFileEditorFileCode);
                            var tracyFileEditorState = JSON.parse(localStorage.getItem("tracyFileEditor"));
                            if(!!tracyFileEditorState) {
                                tracyFileEditor.tfe.selection.fromJSON(tracyFileEditorState.selections);
                                tracyFileEditor.tfe.session.setScrollTop(tracyFileEditorState.scrollTop);
                                tracyFileEditor.tfe.session.setScrollLeft(tracyFileEditorState.scrollLeft);
                            }
                            else {
                                tracyFileEditor.tfe.gotoLine(1, 0);
                            }
                        }
                        tracyFileEditor.tfe.setOptions({
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true,
                            minLines: 5
                        });

                        tracyFileEditor.tfe.setAutoScrollEditorIntoView(true);
                        tracyFileEditor.resizeAce();

                        // checks for changes to Console panel class which indicates focus so we can focus cursor in editor
                        tracyFileEditor.observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                if(mutation.attributeName === "class") {
                                    tracyFileEditor.tfe.focus();
                                }
                            });
                        });
                        tracyFileEditor.observer.observe(document.getElementById("tracy-debug-panel-FileEditorPanel"), {
                            attributes: true
                        });

                        window.onresize = function(event) {
                            tracyFileEditor.resizeAce();
                        };

                    });
                }
            });

            tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/php-file-tree/php_file_tree.js");
            tracyFileEditorLoader.generateButtons($tracyFileEditorFileData);
            document.cookie = "tracyTestFileEditor=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";

        </script>
HTML;

        $out .= '<h1>'.$this->icon.' File Editor: <span id="panelTitleFilePath" style="font-size:14px">'.($this->tracyFileEditorFilePath ?: 'no selected file').'</span></h1>
        <div class="tracy-inner">
            <div id="tracyFoldersFiles" style="float: left; margin: 0; padding:0; width: 310px; height: calc(100vh - 150px); overflow: auto">';

                $out .= "<div class='fe-file-tree'>";
                $out .= $this->php_file_tree($this->wire('config')->paths->{\TracyDebugger::getDataValue('fileEditorBaseDirectory')}, $this->toArray(\TracyDebugger::getDataValue('fileEditorAllowedExtensions')));
                $out .= "</div>";

            $out .= '
            </div>
            <div id="tracyFileEditorCodeContainer" style="float: left; width: calc(100vw - 400px) !important;">
                <div id="tracyFileEditorCode" style="position:relative;"></div><br />
                <form id="tracyFileEditorSubmission" method="post" action="'.\TracyDebugger::inputUrl(true).'">
                    <fieldset>
                        <textarea id="tracyFileEditorRawCode" name="tracyFileEditorRawCode" style="display:none"></textarea>
                        <input type="hidden" id="fileEditorFilePath" name="fileEditorFilePath" value="'.$this->tracyFileEditorFilePath.'" />
                        <div id="fileEditorButtons" style="margin-left:15px"></div>
                    </fieldset>
                </form>
            </div>
            ';

            $out .= \TracyDebugger::generatedTimeSize('fileEditor', \Tracy\Debugger::timer('fileEditor'), strlen($out));

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

    /**
     * Generates a valid HTML list of all directories, sub-directories and files
     *
     * @param string $directory starting point, valid path, with or without trailing slash
     * @param array $extensions array of strings with extension types (without dot), default: empty array, show all files
     * @param bool $extFilter to include (false) or exclude (true) files with that extension, default: false
     * @return string html markup
     *
     */
     public function php_file_tree($directory, $extensions = array(), $extFilter = false) {

        if(!function_exists("scandir")) {
            $msg = $this->_('Error: scandir function does not exist.');
            $this->error($msg);
            return;
        }

        $directory = rtrim($directory, '/\\'); // strip both slash and backslash at the end

        $tree  = "<div class='tracy-file-tree'>";
        $tree .= $this->php_file_tree_dir($directory, $extensions, (bool) $extFilter);
        $tree .= "</div>";

        return $tree;
    }

    /**
     * Recursive function to generate the list of directories/files.
     *
     * @param string $directory starting point, full valid path, without trailing slash
     * @param array $extensions array of strings with extension types (without dot), default: empty array
     * @param bool $extFilter to include (false) or exclude (true) files with that extension, default: false (include)
     * @param string $parent relative directory path, for internal use only
     * @return string html markup
     *
     */
    private function php_file_tree_dir($directory, $extensions = array(), $extFilter = false, $parent = "") {

        // Get directories/files
        $filesArray = array_diff(@scandir($directory), array('.', '..')); // array_diff removes . and ..

        // Filter unwanted extensions
        // currently empty extensions array returns all files in folders
        // comment if statement if you want empty extensions array to return no files at all
        if(!empty($extensions)) {
            foreach(array_keys($filesArray) as $key) {

                // exclude dotfiles, but leave files in extensions filter
                // substr($filesArray[$key], 1) removes first char
                if($filesArray[$key][0] == '.' && !in_array(substr($filesArray[$key], 1), $extensions) ) unset($filesArray[$key]);

                if(!@is_dir("$directory/$filesArray[$key]")) {
                    $ext = substr($filesArray[$key], strrpos($filesArray[$key], ".") + 1);
                    if($extFilter == in_array($ext, $extensions)) unset($filesArray[$key]);
                }
            }
        }

        $tree = "";

        if(count($filesArray) > 0) {
            // Sort directories/files
            natcasesort($filesArray);

            // Make directories first, then files
            $fls = $dirs = array();
            foreach($filesArray as $f) {
                if(@is_dir("$directory/$f")) $dirs[] = $f; else $fls[] = $f;
            }
            $filesArray = array_merge($dirs, $fls);

            $tree .= "<ul>";

            foreach($filesArray as $file) {
                $fileName = $this->toUTF8($file, $this->encoding);

                $parentDir = "/" . str_replace($this->wire('config')->paths->root, "", $directory . "/"); // directory is without trailing slash
                $dirPath = $this->toUTF8("$parentDir/$file/", $this->encoding);
                $dirPath = str_replace("//", "/", $dirPath);

                if(@is_dir("$directory/$file")) {
                    $subtree = $this->php_file_tree_dir("$directory/$file", $extensions, $extFilter, "$parent/$file"); // no need to urlencode parent/file
                    $tree .= "<li class='tft-d'><a data-p='".($subtree == '' ? 'no editable files' : '')."'>$fileName</a>";
                    //$tree .= "<li class='tft-d'><a data-p='$dirPath'>$fileName</a>";
                    $tree .= $subtree;
                    $tree .= "</li>";
                } else {
                    // file
                    // $parent = str_replace($this->dirPath, "", $directory);
                    $ext = strtolower(substr($file, strrpos($file, ".") + 1));
                    $link = str_replace("%2F", "/", rawurlencode("$dirPath")); // to overcome bug/feature on apache
                    //bd($link);
                    $link = trim($link, '/');
                    //$link = $this->wire('config')->paths->templates . $link;
                    $link = str_replace('//', '/', $link);
                    $link = str_replace($this->wire('config')->paths->root, '', $link);
                    if(in_array($ext, array("jpg", "png", "gif", "bmp"))) {
                        // images
                        $rootUrl = $this->convertPathToUrl($this->dirPath);
                        $link = rtrim($rootUrl, '/\\') . $link;
                        $tree .= "<li class='tft-f ext-$ext'><a href='$link'>$fileName</a></li>";
                    } else if($directory == $this->templatesPath && $ext == $this->wire('config')->templateExtension) {
                        // template files
                        $a = $this->isTemplateFile($file);
                        if($a !== false) {
                            $tpl = "<span data-href='$a[1]'>$a[0]</span>";
                            $tree .= "<li class='tft-f ext-$ext'><a href='tracy://?f=$link&l=1'>$fileName</a>$tpl</li>";
                        } else {
                            $tree .= "<li class='tft-f ext-$ext'><a href='tracy://?f=$link&l=1'>$fileName</a></li>";
                        }
                    } else {
                        // just plain file
                        $tree .= "<li class='tft-f ext-$ext'><a href='tracy://?f=$link&l=1'>$fileName</a></li>";
                    }
                }
            }

            $tree .= "</ul>";
        }
        return $tree;
    }

    /**
     * Try to convert string to UTF-8, far from bulletproof, requires mbstring and iconv support
     *
     * @param string $str string to convert to UTF-8
     * @param string $encoding auto|ISO-8859-2|Windows-1250|Windows-1252|urldecode
     * @param boolean $c
     * @return string
     *
     */
    private function toUTF8($str, $encoding = 'auto', $c = false) {

        if(PHP_VERSION_ID >= 70100) return $str;

        // http://stackoverflow.com/questions/7979567/php-convert-any-string-to-utf-8-without-knowing-the-original-character-set-or
        if(extension_loaded('mbstring') && function_exists('iconv')) {
            if($encoding == 'auto') {
                if(DIRECTORY_SEPARATOR != '/') {
                    // windows
                    $str = @iconv(mb_detect_encoding($str, mb_detect_order(), true), 'UTF-8', $str);
                } else {
                    // linux
                    $str = @iconv('Windows-1250', 'UTF-8', $str); // wild guess!!! could be ISO-8859-2, UTF-8, ...
                }
            } else {
                if($encoding == 'urldecode') $str = @urldecode($str);
                else if($encoding == 'none') $str = $str;
                else if($encoding != 'UTF-8') $str = @iconv($encoding, 'UTF-8', $str);
            }
        }
        // replacement of % must be first!!!
        if($c) $str = str_replace(array("%", "#", " ", "{", "}", "^", "+"), array("%25", "%23", "%20", "%7B", "%7D", "%5E", "%2B"), $str);
        return $str;
    }

    /**
     * Convert $config->paths->key to $config->urls->key
     *
     * @param string $path eg. $config->paths->templates
     * @param array $pathTypes eg. array('site','templates'), if not specified, array is constructed from $config->paths
     * @return string path converted to url, empty string if path not found
     *
     */
    private function convertPathToUrl($path, $pathTypes = array()) {
        $path = rtrim($path, '/\\') . '/'; // strip both slash and backslash at the end and then re-add separator
        $url = '';

        if(!$pathTypes) {
            $pathTypes = array('root'); // root is missing
            foreach($this->wire('config')->paths as $pathType => $dummy) $pathTypes[] = $pathType;
        }

        foreach($pathTypes as $pathType) {
            if($this->wire('config')->paths->{$pathType} == $path) {
                $url = $this->wire('config')->urls->{$pathType};
                break;
            }
        }
        return $url;
    }

    /**
     * Convert string delimited by delimiter into an array. Removes empty array keys.
     *
     * @param string $extensions string with delimiters
     * @param string $delimiter, default is comma
     * @return array
     *
     */
    private function toArray($extensions, $delimiter = ',') {
        $ext = preg_replace('# +#', '', $extensions); // remove all spaces
        $ext = array_filter(explode($delimiter, $ext), 'strlen'); // convert to array splitting by delimiter
        return $ext;
    }

}
