<?php

class FileEditorPanel extends BasePanel {

    private $icon;
    private $tracyFileEditorFilePath;
    private $errorMessage = null;
    private $encoding = 'auto';
    private $tracyPwApiData;

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
            $this->p = $this->wire('process')->getPage() ?: $this->wire('page');
        }
        else {
            $this->p = $this->wire('page');
        }

        $this->tracyFileEditorFilePath = $this->wire('input')->cookie->tracyFileEditorFilePath ?: str_replace($this->wire('config')->paths->root, '', $this->p->template->filename);

        if(isset($_POST['tracyTestTemplateCode']) || $this->wire('input')->cookie->tracyTestFileEditor) {
            $iconColor = \TracyDebugger::COLOR_ALERT;
        }
        else {
            $iconColor = \TracyDebugger::COLOR_NORMAL;
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

        $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;

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

        $maximizeSvg = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="282.8 231 16 15.2" enable-background="new 282.8 231 16 15.2" xml:space="preserve"><polygon fill="#AEAEAE" points="287.6,233.6 298.8,231 295.4,242 "/><polygon fill="#AEAEAE" points="293.9,243.6 282.8,246.2 286.1,235.3 "/></svg>';

        $codeUseSoftTabs = \TracyDebugger::getDataValue('codeUseSoftTabs');
        $codeShowInvisibles = \TracyDebugger::getDataValue('codeShowInvisibles');
        $codeTabSize = \TracyDebugger::getDataValue('codeTabSize');
        $customSnippetsUrl = \TracyDebugger::getDataValue('customSnippetsUrl');

        $aceTheme = \TracyDebugger::getDataValue('aceTheme');
        $codeFontSize = \TracyDebugger::getDataValue('codeFontSize');
        $codeLineHeight = \TracyDebugger::getDataValue('codeLineHeight');

        if(\TracyDebugger::getDataValue('pwAutocompletions')) {
            $i=0;
            foreach(\TracyDebugger::getApiData('variables') as $key => $vars) {
                foreach($vars as $name => $params) {
                    if(strpos($name, '()') !== false) {
                        $pwAutocompleteArr[$i]['name'] = "$$key->" . str_replace('___', '', $name) . ($this->wire()->$key && method_exists($this->wire()->$key, $name) ? '()' : '');
                        $pwAutocompleteArr[$i]['meta'] = 'PW method';
                    }
                    else {
                        $pwAutocompleteArr[$i]['name'] = "$$key->" . str_replace('___', '', $name);
                        $pwAutocompleteArr[$i]['meta'] = 'PW property';
                    }
                    if(\TracyDebugger::getDataValue('codeShowDescription')) {
                        $pwAutocompleteArr[$i]['docHTML'] = $params['description'] . "\n" . (isset($params['params']) ? '('.implode(', ', $params['params']).')' : '');
                    }
                    $i++;
                }
            }

            $i=0;
            foreach(\TracyDebugger::getApiData('proceduralFunctions') as $key => $vars) {
                foreach($vars as $name => $params) {
                    $pwAutocompleteArr[$i]['name'] = $name . '()';
                    $pwAutocompleteArr[$i]['meta'] = 'PW function';
                    if(\TracyDebugger::getDataValue('codeShowDescription')) {
                        $pwAutocompleteArr[$i]['docHTML'] = $params['description'] . "\n" . (isset($params['params']) && !empty($params['params']) ? '('.implode(', ', $params['params']).')' : '');
                    }
                    $i++;
                }
            }

            // page fields
            $i = count($pwAutocompleteArr);
            foreach($this->p->fields as $field) {
                $pwAutocompleteArr[$i]['name'] = '$page->'.$field;
                $pwAutocompleteArr[$i]['meta'] = 'PW ' . str_replace('Fieldtype', '', $field->type) . ' field';
                if(\TracyDebugger::getDataValue('codeShowDescription')) $pwAutocompleteArr[$i]['docHTML'] = $field->description;
                $i++;
            }

            $pwAutocomplete = json_encode($pwAutocompleteArr);
        }
        else {
            $pwAutocomplete = json_encode(array());
        }

        $out = <<< HTML
        <script>

            var tracyFileEditor = {

                tfe: {},
                tracyModuleUrl: "$tracyModuleUrl",
                tracyFileEditorFilePath: "{$this->tracyFileEditorFilePath}",
                errorMessage: "{$this->errorMessage}",
                customSnippetsUrl: "$customSnippetsUrl",
                pwAutocomplete: $pwAutocomplete,
                aceTheme: "$aceTheme",
                codeFontSize: $codeFontSize,
                codeLineHeight: $codeLineHeight,

                isSafari: function() {
                    if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
                        return true;
                    }
                    else {
                        return false;
                    }
                },

                toggleKeyboardShortcuts: function() {
                    document.getElementById("fileEditorKeyboardShortcuts").classList.toggle('tracyHidden');
                },

                getRawFileEditorCode: function() {
                    document.cookie = "tracyFileEditorFilePath=" + document.getElementById('fileEditorFilePath').value + "; path=/";

                    // doing btoa because if code contains <script>, browsers are failing with ERR_BLOCKED_BY_XSS_AUDITOR
                    document.getElementById("tracyFileEditorRawCode").innerHTML = btoa(unescape(encodeURIComponent(tracyFileEditor.tfe.getValue())));
                },

                saveToLocalStorage: function(name) {
                    var tracyHistoryItem = {selections: tracyFileEditor.tfe.selection.toJSON(), scrollTop: tracyFileEditor.tfe.session.getScrollTop(), scrollLeft: tracyFileEditor.tfe.session.getScrollLeft()};
                    localStorage.setItem(name, JSON.stringify(tracyHistoryItem));
                },

                toggleFullscreen: function() {
                    var tracyFileEditorPanel = document.getElementById('tracy-debug-panel-FileEditorPanel');
                    if(!document.getElementById("tracyFileEditorCodeContainer").classList.contains("maximizedConsole")) {
                        window.Tracy.Debug.panels["tracy-debug-panel-FileEditorPanel"].toFloat();
                        // hack to hide resize handle that was showing through
                        tracyFileEditorPanel.style.resize = 'none';
                        if(this.isSafari()) {
                            // Safari doesn't support position:fixed on elements outside document body
                            // so move File Editor panel to body when in fullscreen mode
                            document.body.appendChild(tracyFileEditorPanel);
                        }
                    }
                    else {
                        tracyFileEditorPanel.style.resize = 'both';
                        if(this.isSafari()) {
                            document.getElementById("tracy-debug").appendChild(tracyFileEditorPanel);
                        }
                    }
                    document.getElementById("tracyFileEditorCodeContainer").classList.toggle("maximizedConsole");
                    document.documentElement.classList.toggle('noscroll');
                    tracyFileEditor.resizeAce();
                },

                resizeAce: function(focus = true) {

                    if(typeof tracyFileEditor.tfe.resize == 'function') tracyFileEditor.tfe.resize(true);
                    if(focus && typeof tracyFileEditor.tfe.focus == 'function') {
                        document.getElementById("tracy-debug-panel-FileEditorPanel").classList.add('tracy-focused');
                        tracyFileEditor.tfe.focus();
                    }
                }

            };

            tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/ace-editor/ace.js", function() {
                if(typeof ace !== "undefined") {
                    tracyFileEditor.tfe = ace.edit("tracyFileEditorCode");
                    tracyFileEditor.lineHeight = tracyFileEditor.codeLineHeight;
                    tracyFileEditor.tfe.container.style.lineHeight = tracyFileEditor.lineHeight + 'px';
                    tracyFileEditor.tfe.setFontSize(tracyFileEditor.codeFontSize);
                    tracyFileEditor.tfe.setShowPrintMargin(false);
                    tracyFileEditor.tfe.setShowInvisibles($codeShowInvisibles);
                    tracyFileEditor.tfe.\$blockScrolling = Infinity; //fix deprecation warning

                    tracyFileEditor.tfe.on("beforeEndOperation", function() {
                        tracyFileEditor.saveToLocalStorage('tracyFileEditor');
                        // focus set to false to prevent breaking the Ace search box
                        tracyFileEditor.resizeAce(false);
                    });

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
                    tracyFileEditor.tfe.setTheme("ace/theme/" + tracyFileEditor.aceTheme);

                    // set mode appropriately
                    tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/ace-editor/ext-modelist.js", function() {
                        tracyFileEditor.modelist = ace.require("ace/ext/modelist");
                        tracyFileEditor.mode = tracyFileEditor.modelist.getModeForPath(tracyFileEditor.tracyFileEditorFilePath).mode;
                        tracyFileEditor.tfe.session.setMode(tracyFileEditor.mode);

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
                                enableSnippets: true,
                                enableLiveAutocompletion: true,
                                tabSize: $codeTabSize,
                                useSoftTabs: $codeUseSoftTabs,
                                minLines: 5
                            });

                            // all PW variable completers
                            if(tracyFileEditor.pwAutocomplete.length > 0) {
                                var staticWordCompleter = {
                                    getCompletions: function(editor, session, pos, prefix, callback) {
                                        callback(null, tracyFileEditor.pwAutocomplete.map(function(word) {
                                            return {
                                                value: word.name,
                                                meta: word.meta,
                                                docHTML: word.docHTML
                                            };
                                        }));
                                    }
                                };
                                tracyFileEditor.tfe.completers.push(staticWordCompleter);
                            }

                            // included PW snippets
                            tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/code-snippets.js", function() {
                                tracyFileEditor.snippetManager = ace.require("ace/snippets").snippetManager;
                                tracyFileEditor.snippetManager.register(getCodeSnippets(), tracyFileEditor.mode.replace('ace/mode/', ''));

                                // custom snippets URL
                                if(tracyFileEditor.customSnippetsUrl !== '') {
                                    tracyJSLoader.load(tracyFileEditor.customSnippetsUrl, function() {
                                        tracyFileEditor.snippetManager.register(getCustomCodeSnippets(), "php");
                                    });
                                }

                            });

                        });

                        tracyFileEditor.tfe.commands.addCommands([
                            {
                                name: "increaseFontSize",
                                bindKey: "Ctrl-=|Ctrl-+",
                                exec: function(editor) {
                                    var size = parseInt(tracyFileEditor.tfe.getFontSize(), 10) || 12;
                                    editor.setFontSize(size + 1);
                                }
                            },
                            {
                                name: "decreaseFontSize",
                                bindKey: "Ctrl+-|Ctrl-_",
                                exec: function(editor) {
                                    var size = parseInt(editor.getFontSize(), 10) || 12;
                                    editor.setFontSize(Math.max(size - 1 || 1));
                                }
                            },
                            {
                                name: "resetFontSize",
                                bindKey: "Ctrl+0|Ctrl-Numpad0",
                                exec: function(editor) {
                                    editor.setFontSize(14);
                                }
                            }
                        ]);


                        tracyFileEditor.tfe.setAutoScrollEditorIntoView(true);
                        tracyFileEditor.resizeAce();

                        // create and append toggle fullscreen/restore buttons
                        var toggleFullscreenButton = document.createElement('div');
                        toggleFullscreenButton.innerHTML = '<span class="fullscreenToggleButton" title="Toggle fullscreen" onclick="tracyFileEditor.toggleFullscreen()">$maximizeSvg</span>';
                        document.getElementById("tracyFileEditorContainer").querySelector('.ace_gutter').prepend(toggleFullscreenButton);

                        // checks for changes to the panel
                        var config = { attributes: true, attributeOldValue: true };
                        tracyFileEditor.observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                // change in class indicates focus so we can focus cursor in editor
                                if(mutation.attributeName == 'class' && mutation.oldValue !== mutation.target.className && mutation.oldValue.indexOf('tracy-focused') === -1 && mutation.target.classList.contains('tracy-focused')) {
                                    tracyFileEditor.resizeAce();
                                }
                                // else if a change in style then resize but don't focus
                                else if(mutation.attributeName == 'style') {
                                    tracyFileEditor.resizeAce(false);
                                }
                            });
                        });
                        tracyFileEditor.observer.observe(document.getElementById("tracy-debug-panel-FileEditorPanel"), config);

                        // this is necessary for Safari, but not Chrome and Firefox
                        // otherwise resizing panel container doesn't resize internal panes
                        if(tracyFileEditor.isSafari()) {
                            document.getElementById("tracy-debug-panel-FileEditorPanel").addEventListener('mousemove', function() {
                                tracyFileEditor.resizeAce();
                            });
                        }

                        document.getElementById("tracyFileEditorCode").querySelector(".ace_text-input").addEventListener("keydown", function(e) {
                            if(document.getElementById("tracy-debug-panel-FileEditorPanel").classList.contains("tracy-focused")) {
                                if(e.ctrlKey && e.shiftKey) {
                                    e.preventDefault();
                                    // enter
                                    if((e.keyCode==10||e.charCode==10)||(e.keyCode==13||e.charCode==13)) {
                                        tracyFileEditor.toggleFullscreen();
                                    }
                                }
                            }
                        });

                        window.onresize = function(event) {
                            if(document.getElementById("tracy-debug-panel-FileEditorPanel").classList.contains("tracy-focused")) {
                                tracyFileEditor.resizeAce();
                            }
                        };

                    });
                }
            });

            tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/php-file-tree/php_file_tree.js", function() {
                tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/file-editor.js", function() {
                    tracyFileEditorLoader.generateButtons($tracyFileEditorFileData);
                });
            });
            tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/filterbox/filterbox.js", function() {
                tracyJSLoader.load(tracyFileEditor.tracyModuleUrl + "scripts/file-editor-search.js");
            });

            document.cookie = "tracyTestFileEditor=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";

        </script>

HTML;

        $keyboardShortcutIcon = '
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
        viewBox="388 298 16 16" enable-background="new 388 298 16 16" xml:space="preserve" style="width:13px !important; height:13px !important;">
        <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M401.1,308.1h-1.9v-4.3h1.9c1.6,0,2.9-1.3,2.9-2.9c0-1.6-1.3-2.9-2.9-2.9c-1.6,0-2.9,1.3-2.9,2.9v1.9h-4.3v-1.9
            c0-1.6-1.3-2.9-2.9-2.9c-1.6,0-2.9,1.3-2.9,2.9c0,1.6,1.3,2.9,2.9,2.9h1.9v4.3h-1.9c-1.6,0-2.9,1.3-2.9,2.9c0,1.6,1.3,2.9,2.9,2.9
            c1.6,0,2.9-1.3,2.9-2.9v-1.9h4.3v1.9c0,1.6,1.3,2.9,2.9,2.9c1.6,0,2.9-1.3,2.9-2.9C404,309.4,402.7,308.1,401.1,308.1z M399.2,300.9
            c0-1,0.8-1.9,1.9-1.9c1,0,1.9,0.8,1.9,1.9c0,1-0.8,1.9-1.9,1.9h-1.9V300.9z M390.9,302.8c-1,0-1.9-0.8-1.9-1.9c0-1,0.8-1.9,1.9-1.9
            c1,0,1.9,0.8,1.9,1.9v1.9H390.9z M392.8,311.1c0,1-0.8,1.9-1.9,1.9c-1,0-1.9-0.8-1.9-1.9c0-1,0.8-1.9,1.9-1.9h1.9V311.1z
                M393.9,308.1v-4.3h4.3v4.3H393.9z M401.1,312.9c-1,0-1.9-0.8-1.9-1.9v-1.9h1.9c1,0,1.9,0.8,1.9,1.9
            C402.9,312.1,402.1,312.9,401.1,312.9z"/>
        </svg>
        ';

        $out .= '<h1>'.$this->icon.' File Editor <span title="Keyboard Shortcuts" style="display: inline-block; margin-left: 5px; cursor: pointer" onclick="tracyFileEditor.toggleKeyboardShortcuts()">' . $keyboardShortcutIcon . '</span> <span id="panelTitleFilePath" style="font-size:14px">'.($this->tracyFileEditorFilePath ?: 'no selected file').'</span></h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'FileEditorPanel\')">+</a></span></span>
        <div class="tracy-inner">
            <div id="tracyFileEditorContainer" style="height: 100%;">

                <div id="fileEditorKeyboardShortcuts" class="keyboardShortcuts tracyHidden">';
                    $panel = 'fileEditor';
                    include($this->wire('config')->paths->TracyDebugger.'includes/AceKeyboardShortcuts.php');
                    $out .= $aceKeyboardShortcuts . '
                </div>';

                $out .= '
                <div style="float: left; height: calc(100% - 38px);">
                    <select style="width: 17px !important" title="Select recently opened files" onchange="tracyFileEditorLoader.loadFileEditor(this.value)" id="tfe_recently_opened"></select>
                    <div id="tracyFoldersFiles" style="padding: 0 !important; margin:40px 0 0 0 !important; width: 312px !important; height: calc(100% - 40px) !important; overflow-y: auto; overflow-x: hidden; z-index: 1">';
                        $out .= "<div class='fe-file-tree'>";
                        $out .= $this->php_file_tree($this->wire('config')->paths->{\TracyDebugger::getDataValue('fileEditorBaseDirectory')}, $this->toArray(\TracyDebugger::getDataValue('fileEditorAllowedExtensions')));
                        $out .= "</div>";
                    $out .= '
                    </div>
                    <div style="padding: 8px 12px 0 0; float:right">
                        <form id="tracyFileEditorSubmission" style="padding: 0; margin: 0;" method="post" action="'.\TracyDebugger::inputUrl(true).'">
                            <fieldset>
                                <textarea id="tracyFileEditorRawCode" name="tracyFileEditorRawCode" style="display:none"></textarea>
                                <input type="hidden" id="fileEditorFilePath" name="fileEditorFilePath" value="'.$this->tracyFileEditorFilePath.'" />
                                <div id="fileEditorButtons"></div>
                            </fieldset>
                        </form>
                    </div>
                </div>
                <div id="tracyFileEditorCodeContainer" style="float: left; margin: 0; padding:0; width: calc(100% - 322px) !important; height: 100%; overflow: none">
                    <div id="tracyFileEditorCode" style="position:relative; height: 100%"></div>
                </div>
            </div>
            ';

            $out .= \TracyDebugger::generatePanelFooter('fileEditor', \Tracy\Debugger::timer('fileEditor'), strlen($out), 'fileEditorPanel');

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

        if($this->strposa($directory, $this->toArray(\TracyDebugger::getDataValue('fileEditorExcludedDirs'))) !== false) {
            $filesArray = array();
        }
        else {
            // Get directories/files
            $filesArray = array_diff(@scandir($directory), array('.', '..')); // array_diff removes . and ..
        }

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
                    if($subtree != '') $tree .= "<li class='tft-d'><a>$fileName</a>";
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


    private function strposa($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }
        return false;
    }

}
