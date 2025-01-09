<?php

class ConsolePanel extends BasePanel {

    private $icon;
    private $iconColor;
    private $tracyIncludeCode;

    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) {
            return;
        }

        \Tracy\Debugger::timer('console');

        $this->tracyIncludeCode = json_decode((string)$this->wire('input')->cookie->tracyIncludeCode, true);
        if($this->tracyIncludeCode && $this->tracyIncludeCode['when'] !== 'off') {
            $this->iconColor = $this->wire('input')->cookie->tracyCodeError ? \TracyDebugger::COLOR_ALERT : \TracyDebugger::COLOR_WARN;
        }
        else {
            $this->iconColor = \TracyDebugger::COLOR_NORMAL;
        }

        $this->icon = '
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="13.7px" viewBox="439 504.1 16 13.7" enable-background="new 439 504.1 16 13.7" xml:space="preserve">
            <path class="consoleIconPath" fill="' . $this->iconColor . '" d="M453.9,504.1h-13.7c-0.6,0-1.1,0.5-1.1,1.1v11.4c0,0.6,0.5,1.1,1.1,1.1h13.7c0.6,0,1.1-0.5,1.1-1.1v-11.4
                C455,504.7,454.5,504.1,453.9,504.1z M441.3,512.1l2.3-2.3l-2.3-2.3l1.1-1.1l3.4,3.4l-3.4,3.4L441.3,512.1z M450.4,513.3h-4.6v-1.1
                h4.6V513.3z"/>
            </svg>';

        return '
        <span title="Console">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Console' : '') . '
        </span>';
    }


    public function getPanel() {

        $rootPath = $this->wire('config')->paths->root;
        $currentUrl = $_SERVER['REQUEST_URI'];
        $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;
        $inAdmin = \TracyDebugger::$inAdmin;

        // store various $input properties so they are available to the console
        $this->wire('session')->tracyPostData = $this->wire('input')->post->getArray();
        $this->wire('session')->tracyGetData = $this->wire('input')->get->getArray();
        $this->wire('session')->tracyWhitelistData = $this->wire('input')->whitelist->getArray();

        if(\TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') &&
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

        $pid = $p ? $p->id : 'null';

        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
            $fid = (int) $this->wire('input')->get('id');
        }
        else {
            $fid = null;
        }
        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
            $tid = (int) $this->wire('input')->get('id');
        }
        else {
            $tid = null;
        }
        if($this->wire('input')->get('name') && $this->wire('page')->process == 'ProcessModule') {
            $mid = $this->wire('sanitizer')->name($this->wire('input')->get('name'));
        }
        else {
            $mid = null;
        }

        $pageUrl = \TracyDebugger::inputUrl(true);

        $file = $this->wire('config')->paths->cache . 'TracyDebugger/consoleCode.php';
        if(file_exists($file)) {
            $code = file_get_contents($file);
            $code = implode("\n", array_slice(explode("\n", $code), 1));
            $code = json_encode($code); // json_encode to convert line breaks to \n - needed by setValue()
        }
        else {
            $code = '""';
        }

        // get snippets from filesystem
        $snippets = array();
        $snippetsPath = \TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/';
        if(file_exists($this->wire('config')->paths->site.$snippetsPath)) {
            $snippetFiles = new DirectoryIterator($this->wire('config')->paths->site.$snippetsPath);
            $i=0;
            foreach($snippetFiles as $snippetFile) {
                if(!$snippetFile->isDot() && $snippetFile->isFile()) {
                    $snippetFileName = $snippetFile->getPathname();
                    $snippets[$i]['name'] = pathinfo($snippetFileName, PATHINFO_BASENAME);
                    $snippets[$i]['filename'] = $snippetFileName;
                    $snippets[$i]['code'] = file_get_contents($snippetFileName);
                    $snippets[$i]['modified'] = filemtime($snippetFileName);
                    $i++;
                }
            }
            $snippets = json_encode($snippets);
        }
        if(!$snippets) $snippets = json_encode(array());

        $out = '<script>' . file_get_contents($this->wire('config')->paths->TracyDebugger . 'scripts/get-query-variable.js') . '</script>';

        // determine whether 'l' or 'line' is used for line number with current editor
        parse_str(\Tracy\Debugger::$editor, $vars);
        $lineVar = array_key_exists('l', $vars) ? 'l' : 'line';

        $maximizeSvg =
        '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             viewBox="282.8 231 16 15.2" enable-background="new 282.8 231 16 15.2" xml:space="preserve">
            <polygon fill="#AEAEAE" points="287.6,233.6 298.8,231 295.4,242 "/>
            <polygon fill="#AEAEAE" points="293.9,243.6 282.8,246.2 286.1,235.3 "/>
        </svg>';

        $codeUseSoftTabs = \TracyDebugger::getDataValue('codeUseSoftTabs');
        $codeShowInvisibles = \TracyDebugger::getDataValue('codeShowInvisibles');
        $codeTabSize = \TracyDebugger::getDataValue('codeTabSize');
        $customSnippetsUrl = \TracyDebugger::getDataValue('customSnippetsUrl');

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
                        $pwAutocompleteArr[$i]['docHTML'] = $params['description'] . "\n" . (isset($params['params']) && !empty($params['params']) ? '('.implode(', ', $params['params']).')' : '');
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
            if($p) {
                foreach($p->fields as $field) {
                    $pwAutocompleteArr[$i]['name'] = '$page->'.$field;
                    $pwAutocompleteArr[$i]['meta'] = 'PW ' . str_replace('Fieldtype', '', $field->type) . ' field';
                    if(\TracyDebugger::getDataValue('codeShowDescription')) $pwAutocompleteArr[$i]['docHTML'] = $field->description;
                    $i++;
                }
            }
            $pwAutocomplete = json_encode($pwAutocompleteArr);
        }
        else {
            $pwAutocomplete = json_encode(array());
        }

        $aceTheme = \TracyDebugger::getDataValue('aceTheme');
        $codeFontSize = \TracyDebugger::getDataValue('codeFontSize');
        $codeLineHeight = \TracyDebugger::getDataValue('codeLineHeight');
        $externalEditorLink = str_replace('"', "'", \TracyDebugger::createEditorLink($this->wire('config')->paths->site.\TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/'.'ExternalEditorDummyFile', 0, '✎', 'Edit in external editor'));
        $colorNormal = \TracyDebugger::COLOR_NORMAL;
        $colorWarn = \TracyDebugger::COLOR_WARN;

        $out .= <<< HTML
        <script>

            var tracyConsole = {

                tce: {},
                tracyModuleUrl: "$tracyModuleUrl",
                tabs: {},
                tabsContainer: null,
                addTabButton: null,
                currentTabId: null,
                maxHistoryItems: 25,
                historyItem: localStorage.getItem("tracyConsoleHistoryItem") ? localStorage.getItem("tracyConsoleHistoryItem") : 1,
                historyCount: localStorage.getItem("tracyConsoleHistoryCount"),
                desc: false,
                inAdmin: "$inAdmin",
                customSnippetsUrl: "$customSnippetsUrl",
                snippetsPath: "$snippetsPath",
                rootPath: "$rootPath",
                pwAutocomplete: $pwAutocomplete,
                aceTheme: "$aceTheme",
                codeFontSize: $codeFontSize,
                lineHeight: $codeLineHeight,
                externalEditorLink: "$externalEditorLink",
                colorNormal: "$colorNormal",
                colorWarn: "$colorWarn",

                isSafari: function() {
                    if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
                        return true;
                    }
                    else {
                        return false;
                    }
                },

                getCookie: function(name) {
                    var value = "; " + document.cookie;
                    var parts = value.split("; " + name + "=");
                    if (parts.length == 2) return parts.pop().split(";").shift();
                },

                disableButton: function(button) {
                    var button = document.getElementById(button);
                    button.setAttribute("disabled", true);
                    button.classList.add("disabledButton");
                },

                enableButton: function(button) {
                    var button = document.getElementById(button);
                    button.removeAttribute("disabled");
                    button.classList.remove("disabledButton");
                },

                tryParseJSON: function(str) {
                    if(!isNaN(str)) return str;
                    try {
                        var o = JSON.parse(str);
                        if(o && typeof o === "object" && o !== null) {
                            if(o.message.indexOf("Compiled file") > -1) {
                                return "";
                            }
                            else {
                                return "Error: " + o.message;
                            }
                        }
                    }
                    catch(e) {
                        return str;
                    }
                    return false;
                },

                saveToLocalStorage: function() {
                    var code = this.tce.getValue();

                    var selections = this.tce.selection.toJSON();

                    var existingTabs = JSON.parse(localStorage.getItem("tracyConsoleTabs")) || [];
                    var tracyConsoleTabs = [];
                    var updated = false;

                    // Iterate through existing tabs and update if matching id is found
                    for (var key in existingTabs) {
                        if (!existingTabs.hasOwnProperty(key)) continue;

                        var tab = existingTabs[key];
                        if (tab.id == this.currentTabId) {
                            // Update the existing tab
                            tracyConsoleTabs.push({
                                id: this.currentTabId,
                                name: document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .button-label').textContent,
                                code: code,
                                selections: selections,
                                scrollTop: this.tce.session.getScrollTop(),
                                scrollLeft: this.tce.session.getScrollLeft()
                            });
                            updated = true;
                        } else {
                            // Keep the existing tab as is
                            tracyConsoleTabs.push(tab);
                        }
                    }

                    // If no matching id was found, add a new tab
                    if (!updated) {
                        tracyConsoleTabs.push({
                            id: this.currentTabId,
                            name: document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .button-label').textContent,
                            code: '',
                            selections: selections,
                            scrollTop: this.tce.session.getScrollTop(),
                            scrollLeft: this.tce.session.getScrollLeft()
                        });
                    }

                    // update loaded copy of tabs
                    tracyConsole.tabs = tracyConsoleTabs;

                    localStorage.setItem("tracyConsoleSelectedTab", this.currentTabId);
                    localStorage.setItem("tracyConsoleTabs", JSON.stringify(tracyConsoleTabs));

                },

                setEditorState: function(data) {
                    if(data) {
                        this.tce.setValue(data.code);
                        this.tce.selection.fromJSON(data.selections);
                        this.tce.session.setScrollTop(data.scrollTop);
                        this.tce.session.setScrollLeft(data.scrollLeft);
                    }
                    else {
                        this.tce.setValue('');
                    }
                },

                clearResults: function() {
                    document.getElementById("tracyConsoleResult").innerHTML = "";
                    document.getElementById("tracyConsoleStatus").innerHTML = "";
                    this.tce.focus();
                },

                processTracyCode: function() {
                    var code = this.tce.getSelectedText() || this.tce.getValue();
                    document.getElementById("tracyConsoleStatus").innerHTML = "<span style='font-family: FontAwesome !important' class='fa fa-spinner fa-spin'></span> Processing";
                    codeReturn = this.getCookie('tracyIncludeCode') ? false : true;
                    this.callPhp(code, codeReturn);
                    this.saveHistory();
                    this.tce.focus();
                },

                reloadAndRun: function() {
                    if(localStorage.getItem("tracyConsoleSelectedSnippet")) {
                        document.getElementById("tracyConsoleStatus").innerHTML = "<span style='font-family: FontAwesome !important' class='fa fa-spinner fa-spin'></span> Processing";
                        this.reloadSnippet(true);
                    }
                    else {
                        this.processTracyCode();
                    }
                },

                tracyIncludeCode: function(when) {
                    when = when.value;
                    params = {when: when, pid: $pid};
                    var icons = document.getElementsByClassName("consoleIconPath");
                    i=0;
                    if(when === 'off') {
                        document.cookie = "tracyIncludeCode=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                        while(i < icons.length) {
                            icons[i].style.fill = tracyConsole.colorNormal;
                            i++;
                        }
                        document.getElementById("runInjectButton").value = 'Run';
                    }
                    else {
                        var expires = new Date();
                        expires.setMinutes(expires.getMinutes() + 5);
                        document.cookie = "tracyIncludeCode="+JSON.stringify(params)+";expires="+expires.toGMTString()+";path=/";
                        while(i < icons.length) {
                            icons[i].style.fill = tracyConsole.colorWarn;
                            i++;
                        }
                        document.getElementById("runInjectButton").value = 'Inject';
                    }
                    tracyConsole.tce.focus();
                },

                toggleSnippetsPane: function() {
                    if(tracyConsole.getCookie('tracySnippetsPaneCollapsed') == 1) {
                        document.cookie = "tracySnippetsPaneCollapsed=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                        document.getElementById("tracyConsoleMainContainer").style.width = "calc(100% - 290px)";
                        document.getElementById("tracySnippetsContainer").classList.remove('tracyHidden');
                        document.getElementById("snippetPaneToggle").innerHTML = ">";
                    }
                    else {
                        var expires = new Date();
                        expires.setMinutes(expires.getMinutes() + (10 * 365 * 24 * 60));
                        document.cookie = "tracySnippetsPaneCollapsed=1;expires="+expires.toGMTString()+";path=/";
                        document.getElementById("tracyConsoleMainContainer").style.width = "100%";
                        document.getElementById("tracySnippetsContainer").classList.add('tracyHidden');
                        document.getElementById("snippetPaneToggle").innerHTML = "<";
                    }
                },

                toggleKeyboardShortcuts: function() {
                    document.getElementById("consoleKeyboardShortcuts").classList.toggle('tracyHidden');
                },

                toggleFullscreen: function() {
                    var tracyConsolePanel = document.getElementById('tracy-debug-panel-ConsolePanel');
                    if(!document.getElementById("tracyConsoleContainer").classList.contains("maximizedConsole")) {
                        window.Tracy.Debug.panels["tracy-debug-panel-ConsolePanel"].toFloat();
                        // hack to hide resize handle that was showing through
                        tracyConsolePanel.style.resize = 'none';
                        if(this.isSafari()) {
                            // Safari doesn't support position:fixed on elements outside document body
                            // so move Console panel to body when in fullscreen mode
                            document.body.appendChild(tracyConsolePanel);
                        }
                    }
                    else {
                        tracyConsolePanel.style.resize = 'both';
                        if(this.isSafari()) {
                            document.getElementById("tracy-debug").appendChild(tracyConsolePanel);
                        }
                    }
                    document.getElementById("tracyConsoleContainer").classList.toggle("maximizedConsole");
                    document.documentElement.classList.toggle('noscroll');
                    tracyConsole.resizeAce();
                },

                callPhp: function(code, codeReturn = true) {
                    if(!codeReturn) {
                        var expires = new Date();
                        expires.setMinutes(expires.getMinutes() + 5);
                        document.cookie = "tracyCodeReturn=no;expires="+expires.toGMTString()+";path=/";
                    }
                    else {
                        document.cookie = "tracyCodeReturn=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    }

                    var xmlhttp;
                    xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                            document.getElementById("tracyConsoleStatus").innerHTML = "✔ " + (codeReturn ? "Executed" : "Injected @ " + JSON.parse(tracyConsole.getCookie('tracyIncludeCode')).when);
                            var resultsDiv = document.getElementById("tracyConsoleResult");
                            if(xmlhttp.status == 200) {
                                resultId = Date.now();
                                resultsDiv.innerHTML += '<div id="tracyConsoleResult_'+resultId+'" style="padding:10px 0">' + tracyConsole.tryParseJSON(xmlhttp.responseText) + '</div>';

                                // saved resultsDiv.innerHTML to localStorage in an array of values corresponding to the currentTabId
                                var existingResults = JSON.parse(localStorage.getItem("tracyConsoleResults")) || [];
                                var tracyConsoleResults = [];
                                var updated = false;
                                for (var key in existingResults) {
                                    if (!existingResults.hasOwnProperty(key)) continue;
                                    var result = existingResults[key];
                                    if (result.id == tracyConsole.currentTabId) {
                                        tracyConsoleResults.push({
                                            id: tracyConsole.currentTabId,
                                            results: resultsDiv.innerHTML
                                        });
                                        updated = true;
                                    } else {
                                        tracyConsoleResults.push(result);
                                    }
                                }
                                if (!updated) {
                                    tracyConsoleResults.push({
                                        id: tracyConsole.currentTabId,
                                        results: resultsDiv.innerHTML
                                    });
                                }
                                localStorage.setItem("tracyConsoleResults", JSON.stringify(tracyConsoleResults));

                                document.getElementById("tracyConsoleResult_"+resultId).scrollIntoView();
                                if(!document.getElementById("tracy-debug-panel-ConsolePanel").classList.contains("tracy-mode-float")) {
                                    window.Tracy.Debug.panels["tracy-debug-panel-ConsolePanel"].toFloat();
                                }
                            }
                            else {
                                var errorStr = xmlhttp.status + ': ' + xmlhttp.statusText + '<br />' + xmlhttp.responseText;
                                resultsDiv.innerHTML = '<div style="padding: 10px 0">' + errorStr + '</div><div style="position:relative; border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';

                                var expires = new Date();
                                expires.setMinutes(expires.getMinutes() + (10 * 365 * 24 * 60));
                                document.cookie = "tracyCodeError=" + errorStr + ";expires="+expires.toGMTString()+";path=/";
                            }
                            xmlhttp.getAllResponseHeaders();
                        }
                    };

                    var dbBackup = document.getElementById("dbBackup").checked;
                    var allowBluescreen = document.getElementById("allowBluescreen").checked;
                    var backupFilename = document.getElementById("backupFilename").value;
                    var accessTemplateVars = !this.inAdmin ? document.getElementById("accessTemplateVars").checked : "false";

                    xmlhttp.open("POST", "$currentUrl", true);
                    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xmlhttp.send("tracyConsole=1&codeReturn=codeReturn&allowBluescreen="+allowBluescreen+"&dbBackup="+dbBackup+"&backupFilename="+backupFilename+"&accessTemplateVars="+accessTemplateVars+"&pid={$pid}&fid={$fid}&tid={$tid}&mid={$mid}&code="+encodeURIComponent(code));
                },

                resizeAce: function(focus = true) {
                    tracyConsole.tce.resize(true);
                    if(focus) {
                        window.Tracy.Debug.panels["tracy-debug-panel-ConsolePanel"].focus();
                        tracyConsole.tce.focus();
                    }
                },

                getSnippet: function(name, process = false) {
                    var xmlhttp;
                    xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                            if(xmlhttp.status == 200 && xmlhttp.responseText !== "[]") {
                                tracyConsole.tce.setValue(xmlhttp.responseText);
                                tracyConsole.tce.gotoLine(0, 0);
                                if(process) tracyConsole.processTracyCode();

                                // set mode appropriately
                                tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/ace-editor/ext-modelist.js", function() {
                                    tracyConsole.modelist = ace.require("ace/ext/modelist");
                                    var mode = tracyConsole.modelist.getModeForPath(tracyConsole.rootPath+tracyConsole.snippetsPath+name).mode;
                                    if(xmlhttp.responseText.indexOf('<?php') !== -1) {
                                        mode = 'ace/mode/php';
                                    }
                                    else {
                                        mode = mode == 'ace/mode/php' ? {path:"ace/mode/php", inline:true} : mode;
                                    }
                                    tracyConsole.tce.session.setMode(mode);
                                });
                            }
                            xmlhttp.getAllResponseHeaders();
                        }
                    };
                    xmlhttp.open("POST", "$currentUrl", true);
                    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xmlhttp.send("tracysnippets=1&snippetname=" + name);
                },

                getAllSnippets: function() {
                    return JSON.parse(localStorage.getItem("tracyConsoleSnippets"));
                },

                modifyConsoleSnippets: function(tracySnippetName, code, deleteSnippet) {
                    var tracyConsoleSnippets = [];
                    if(deleteSnippet) {
                        if(!confirm("Are you sure you want to delete the \"" + tracySnippetName + "\" snippet?")) return false;
                    }
                    else {
                        tracyConsoleSnippets.push({name: tracySnippetName, code: code, modified: Date.now()});
                    }

                    var existingSnippets = this.getAllSnippets();
                    for(var key in existingSnippets) {
                        if(!existingSnippets.hasOwnProperty(key)) continue;
                        var obj = existingSnippets[key];
                        if(obj.name !== tracySnippetName) tracyConsoleSnippets.push(obj);
                    }
                    this.setAllSnippets(tracySnippetName, tracyConsoleSnippets, deleteSnippet);
                },

                modifySnippetList: function(name, existingSnippets, deleteSnippet) {
                    if(!existingSnippets) var existingSnippets = this.getAllSnippets();
                    var snippetList = "<ul id='snippetsList'>";
                    for(var key in existingSnippets) {
                        if(!existingSnippets.hasOwnProperty(key)) continue;
                        var obj = existingSnippets[key];
                        if(deleteSnippet === true && obj.name === name) continue;
                        snippetList += "<li title='Load in console' id='"+this.makeIdFromTitle(obj.name)+"' data-modified='"+obj.modified+"'><span class='consoleSnippetIcon consoleEditIcon iconFlip'>" + this.externalEditorLink.replace('ExternalEditorDummyFile', obj.name) + "</span><span class='consoleSnippetIcon' style='font-family: FontAwesome !important;' title='Delete snippet' onclick='tracyConsole.modifyConsoleSnippets(\""+obj.name+"\", null, true)'>&#xf1f8;</span><span style='color: #125EAE; cursor: pointer; width:200px; word-break: break-all;' onclick='tracyConsole.loadSnippet(\""+obj.name+"\");'>" + obj.name + "</span></li>";
                    }
                    snippetList += "</ul>";
                    document.getElementById("tracySnippets").innerHTML = snippetList;
                },

                setAllSnippets: function(tracySnippetName, tracyConsoleSnippets, deleteSnippet) {
                    // push to local storage for access during current page instance
                    localStorage.setItem("tracyConsoleSnippets", JSON.stringify(tracyConsoleSnippets));

                    // save to or delete from filesystem
                    var xmlhttp;
                    xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                            if(xmlhttp.status == 200) {
                                tracyConsole.modifySnippetList(tracySnippetName, tracyConsoleSnippets, deleteSnippet);
                                tracyConsole.setActiveSnippet(tracySnippetName);
                            }
                            xmlhttp.getAllResponseHeaders();
                        }
                    };
                    xmlhttp.open("POST", "$currentUrl", true);
                    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    if(deleteSnippet) {
                        document.getElementById("tracySnippetName").value = '';
                        xmlhttp.send("tracysnippets=1&snippetname="+tracySnippetName+"&deletesnippet=1");
                    }
                    else {
                        xmlhttp.send("tracysnippets=1&snippetname="+tracySnippetName+"&snippetcode="+encodeURIComponent(JSON.stringify(this.tce.getValue())));
                    }
                },

                makeIdFromTitle: function(title) {
                    return title.replace(/^[^a-z]+|[^\w:.-]+/gi, "");
                },

                saveSnippet: function() {
                    var tracySnippetName = document.getElementById("tracySnippetName").value;
                    if(tracySnippetName != "") {
                        this.modifyConsoleSnippets(tracySnippetName, encodeURIComponent(this.tce.getValue()));
                        this.disableButton("saveSnippet");
                        this.disableButton("reloadSnippet");
                        this.tce.focus();
                        // change selected tab name to match new snippet name just saved
                        document.querySelector('button[data-tab-id="'+tracyConsole.currentTabId+'"] .button-label').textContent = tracySnippetName;
                        // update the tab name in localStorage
                        tracyConsole.saveToLocalStorage();
                    }
                    else {
                        alert('You must enter a name to save a snippet!');
                        document.getElementById("tracySnippetName").focus();
                    }
                },

                setActiveSnippet: function(name) {
                    var item = document.getElementById(this.makeIdFromTitle(name));
                    if(!item) return;
                    if(document.querySelector(".activeSnippet")) {
                        document.querySelector(".activeSnippet").classList.remove("activeSnippet");
                    }
                    item.classList.add("activeSnippet");
                },

                compareAlphabetical: function(a1, a2) {
                    var t1 = a1.innerText.toLowerCase(),
                        t2 = a2.innerText.toLowerCase();
                    return t1 > t2 ? 1 : (t1 < t2 ? -1 : 0);
                },

                compareChronological: function(a1, a2) {
                    var t1 = a1.dataset.modified,
                        t2 = a2.dataset.modified;
                    return t1 > t2 ? 1 : (t1 < t2 ? -1 : 0);
                },

                sortUnorderedList: function(ul, sortDescending, type) {
                    if(typeof ul == "string") {
                        ul = document.getElementById(ul);
                    }

                    var lis = ul.getElementsByTagName("LI");
                    var vals = [];

                    for(var i = 0, l = lis.length; i < l; i++) {
                        vals.push(lis[i]);
                    }

                    if(type === 'alphabetical') {
                        vals.sort(this.compareAlphabetical);
                    }
                    else {
                        vals.sort(this.compareChronological);
                    }

                    if(sortDescending) {
                        vals.reverse();
                    }

                    ul.innerHTML = '';
                    for(var i = 0, l = vals.length; i < l; i++) {
                        ul.appendChild(vals[i]);
                    }
                },

                sortList: function(type) {
                    this.sortUnorderedList("snippetsList", this.desc, type);
                    this.desc = !this.desc;
                    return false;
                },

                getHistoryItem: function(id) {
                    var tracyConsoleHistory = JSON.parse(localStorage.getItem("tracyConsoleHistory"));
                    for(var key in tracyConsoleHistory) {
                        if(!tracyConsoleHistory.hasOwnProperty(key)) continue;
                        var obj = tracyConsoleHistory[key];
                        if(obj.id === id) return obj;
                    }
                },

                getTabItem: function(id) {
                    var tracyConsoleTabs = JSON.parse(localStorage.getItem("tracyConsoleTabs"));
                    for(var key in tracyConsoleTabs) {
                        if(!tracyConsoleTabs.hasOwnProperty(key)) continue;
                        var obj = tracyConsoleTabs[key];
                        if(obj.id === id) {
                            return obj;
                        }
                    }
                },

                loadHistory: function(direction) {
                    var noItem = false;
                    var historyCounts = JSON.parse(localStorage.getItem("tracyConsoleHistoryCount")) || {};
                    var historyItems = JSON.parse(localStorage.getItem("tracyConsoleHistoryItem")) || {};

                    // initialize count and item for the current tab
                    var historyCount = historyCounts[this.currentTabId] || 0;
                    var historyItem = historyItems[this.currentTabId] || 0;

                    // determine the new history item ID based on direction
                    if (direction === 'back' && historyItem > 1) {
                        historyItem--;
                    }
                    else if (direction === 'forward' && historyItem < historyCount) {
                        historyItem++;
                    }
                    else {
                        noItem = true;
                    }

                    // update buttons based on the new history state
                    if (historyItem <= 1) {
                        this.disableButton("historyBack");
                    } else {
                        this.enableButton("historyBack");
                    }

                    if (historyItem >= historyCount) {
                        this.disableButton("historyForward");
                    }
                    else {
                        this.enableButton("historyForward");
                    }

                    if (noItem) return;

                    // save the updated history item index for the current tab
                    historyItems[this.currentTabId] = historyItem;
                    localStorage.setItem("tracyConsoleHistoryItem", JSON.stringify(historyItems));

                    // load the history item for the current tab
                    var historyData = JSON.parse(localStorage.getItem("tracyConsoleHistory")) || {};

                    var currentTabHistory = historyData[this.currentTabId] || [[]];
                    var currentTabResults = currentTabHistory[0] || [];
                    var historyEntry = currentTabResults.find(item => item.id === historyItem);

                    if (historyEntry) {
                        this.setEditorState(historyEntry);
                        this.tce.focus();
                    }
                },

                reloadSnippet: function(process = false) {
                    let snippetName = localStorage.getItem("tracyConsoleSelectedSnippet");
                    this.loadSnippet(snippetName, process, true, true);
                },

                loadSnippet: function(name, process = false, get = true, reload = false) {
                    let existingTabId = null;
                    if(get) {
                        // check if the snippet is already open
                        for (const tabId in tracyConsole.tabs) {
                            if (tracyConsole.tabs[tabId].name === name) {
                                existingTabId = tracyConsole.tabs[tabId].id;
                                break;
                            }
                        }
                    }

                    if(existingTabId && !reload) {
                        tracyConsole.switchTab(existingTabId);
                    }
                    else {
                        if(get) {
                            this.addNewTab(name);
                            this.getSnippet(name, process);
                        }
                        this.setActiveSnippet(name);
                        localStorage.setItem("tracyConsoleSelectedSnippet", name);
                        document.getElementById("tracySnippetName").value = name;
                        document.querySelector('button[data-tab-id="'+tracyConsole.currentTabId+'"] .button-label').textContent = name;
                        tracyConsole.lockTabName();
                        this.disableButton("reloadSnippet");
                        this.disableButton("saveSnippet");
                        ++tracyConsole.historyItem;
                        this.resizeAce();
                    }
                },

                scrollTabIntoView: function(tabId) {
                    const tabElement = document.querySelector('[data-tab-id="'+tabId+'"]');
                    if(tabElement) {
                        tabElement.scrollIntoView({
                            behavior: "smooth",
                            block: "nearest",
                            inline: "nearest"
                        });
                    }
                },

                lockTabName: function() {
                    var tabButton = document.querySelector('button[data-tab-id="'+tracyConsole.currentTabId+'"] .button-label');
                    tabButton.classList.add("lockedTab");
                },

                toggleSaveButton: function() {
                    // if code in tracyConsoleSnippets is different from the current code in the editor, enable the save button
                    var tracyConsoleSnippets = this.getAllSnippets();
                    var snippet = tracyConsoleSnippets.find(obj => obj.name === document.getElementById("tracySnippetName").value);
                    if(snippet && snippet.code.replace(/\s+/g, ' ').trim() != this.tce.getValue().replace(/\s+/g, ' ').trim()) {
                        this.enableButton("saveSnippet");
                        this.enableButton("reloadSnippet");
                    }
                    else {
                        this.disableButton("saveSnippet");
                        this.disableButton("reloadSnippet");
                    }
                },

                saveHistory: function() {
                    var code = this.tce.getValue();
                    var selections = this.tce.selection.toJSON();

                    if (code) {
                        var historyData = JSON.parse(localStorage.getItem("tracyConsoleHistory")) || {};
                        var historyCounts = JSON.parse(localStorage.getItem("tracyConsoleHistoryCount")) || {};
                        var historyItems = JSON.parse(localStorage.getItem("tracyConsoleHistoryItem")) || {};

                        // initialize history for the current tab as needed
                        var tracyConsoleHistory = historyData[this.currentTabId] || [[]];
                        var currentTabResults = tracyConsoleHistory[0] || [];

                        // ensure the results array does not exceed maxHistoryItems for this tab
                        if (currentTabResults.length >= tracyConsole.maxHistoryItems) {
                            currentTabResults.shift();
                        }

                        // assign unique IDs for the current tab's history
                        currentTabResults.forEach((item, index) => {
                            item.id = index + 1;
                        });
                        var id = currentTabResults.length + 1;

                        // add the new history item
                        currentTabResults.push({
                            id: id,
                            code: code,
                            selections: selections,
                            scrollTop: this.tce.session.getScrollTop(),
                            scrollLeft: this.tce.session.getScrollLeft()
                        });

                        // update tracyConsoleHistory for the current tab
                        tracyConsoleHistory[0] = currentTabResults;
                        historyData[this.currentTabId] = tracyConsoleHistory;

                        // update history counts and items for the current tab
                        historyCounts[this.currentTabId] = currentTabResults.length;
                        historyItems[this.currentTabId] = id;

                        // save back to localStorage
                        localStorage.setItem("tracyConsoleHistory", JSON.stringify(historyData));
                        localStorage.setItem("tracyConsoleHistoryCount", JSON.stringify(historyCounts));
                        localStorage.setItem("tracyConsoleHistoryItem", JSON.stringify(historyItems));

                        // update UI buttons
                        this.disableButton("historyForward");
                        if (historyCounts[this.currentTabId] > 1) {
                            this.enableButton("historyBack");
                        }
                    }
                },

                updateBackupState: function() {
                    if(!document.getElementById("dbBackup").checked) {
                        document.getElementById("backupFilename").value = '';
                        document.getElementById("backupFilename").style.display = "none";
                        document.cookie = "tracyDbBackup=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                        document.cookie = "tracyDbBackupFilename=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    }
                    else {
                        document.getElementById("backupFilename").style.display = "inline-block";
                    }
                    tracyConsole.tce.focus();
                },

                switchTab: function(tabId) {
                    tabId = Number(tabId);

                    if (this.currentTabId) {
                        let currentTabButton = document.querySelector('button[data-tab-id="'+this.currentTabId+'"]');
                        if(currentTabButton) {
                            currentTabButton.classList.remove("active");
                            const currentButton = Array.from(this.tabsContainer.children).find(
                                (button) => button.dataset.tabId == this.currentTabId
                            );
                            if (currentButton) {
                                currentButton.classList.remove("active");
                            }
                        }
                    }

                    this.currentTabId = tabId;
                    const newButton = Array.from(this.tabsContainer.children).find(
                        (button) => button.dataset.tabId == tabId
                    );
                    if (newButton) {
                        newButton.classList.add("active");
                    }

                    this.scrollTabIntoView(tabId);

                    this.setEditorState(this.getTabItem(tabId));
                    this.tce.focus();

                    //populate resultsDiv with saved results
                    var existingResults = JSON.parse(localStorage.getItem("tracyConsoleResults")) || [];
                    for (var key in existingResults) {
                        if (!existingResults.hasOwnProperty(key)) continue;
                        var result = existingResults[key];
                        if (result.id == tabId) {
                            document.getElementById("tracyConsoleResult").innerHTML = result.results;
                            break;
                        }
                        else {
                            document.getElementById("tracyConsoleResult").innerHTML = '';
                        }
                    }

                    localStorage.setItem("tracyConsoleSelectedTab", this.currentTabId);

                    // if tab label matches a snippet name, then select that snippet
                    let tabButton = document.querySelector('button[data-tab-id="'+tabId+'"] .button-label');
                    if(tabButton) {
                        var snippetName = tabButton.textContent;
                        if(snippetName && document.getElementById(this.makeIdFromTitle(snippetName))) {
                            this.loadSnippet(snippetName, false, false);
                        }
                        else {
                            // remove active from all snippets
                            if(document.querySelector(".activeSnippet")) {
                                document.querySelector(".activeSnippet").classList.remove("activeSnippet");
                                document.getElementById("tracySnippetName").value = '';
                            }
                            localStorage.removeItem("tracyConsoleSelectedSnippet");
                            this.disableButton("reloadSnippet");
                        }
                    }
                },

                removeTab: function(tabId) {

                    if(Object.keys(this.tabs).length === 1) {
                        document.querySelector('button[data-tab-id="'+tracyConsole.currentTabId+'"] .button-label').textContent = 'Untitled‑1';
                        tracyConsole.tce.setValue('');
                        localStorage.removeItem('tracyConsoleResults');
                        tracyConsole.saveToLocalStorage();
                        tracyConsole.tce.focus();
                    }
                    else {

                        // remove tab button
                        const tabButton = Array.from(this.tabsContainer.children).find(
                            (button) => button.dataset.tabId == tabId
                        );
                        if (tabButton) this.tabsContainer.removeChild(tabButton);

                        delete tracyConsole.tabs[tabId];
                        tracyConsole.currentTabId = Math.max(...Object.keys(tracyConsole.tabs).map(Number));
                        localStorage.setItem("tracyConsoleSelectedTab", tracyConsole.currentTabId);

                        // remove tab from tracyConsoleTabs
                        const existingItems = JSON.parse(localStorage.getItem("tracyConsoleTabs"));
                        const tracyConsoleTabs = [];
                        for (var key in existingItems) {
                            if (!existingItems.hasOwnProperty(key)) continue;
                            var item = existingItems[key];
                            if (item.id != tabId) {
                                tracyConsoleTabs.push(item);
                            }
                        }
                        localStorage.setItem("tracyConsoleTabs", JSON.stringify(tracyConsoleTabs));

                        // remove results for this tab
                        const existingResults = JSON.parse(localStorage.getItem("tracyConsoleResults"));
                        const tracyConsoleResults = [];
                        for (var key in existingResults) {
                            if (!existingResults.hasOwnProperty(key)) continue;
                            var result = existingResults[key];
                            if (result.id != tabId) {
                                tracyConsoleResults.push(result);
                            }
                        }
                        localStorage.setItem("tracyConsoleResults", JSON.stringify(tracyConsoleResults));

                        this.switchTab(tracyConsole.currentTabId);
                    }

                },

                addNewTab: function() {

                    let tabId;

                    if (Object.keys(tracyConsole.tabs).length == 0) {
                        tabId = 1;
                    }
                    else {
                        tabId = Math.max(...Object.keys(tracyConsole.tabs).map(Number)) + 1;
                    }
                    const tabButton = document.createElement("button");
                    const buttonLabel = document.createElement("span");
                    buttonLabel.classList.add("button-label");
                    buttonLabel.textContent = 'Untitled‑' + tracyConsole.getNextTabNumber();
                    tabButton.appendChild(buttonLabel);

                    tabButton.dataset.tabId = tabId;
                    tabButton.setAttribute("draggable", "true");
                    tabButton.addEventListener("click", () => tracyConsole.switchTab(tabId));
                    tracyConsole.tabsContainer.appendChild(tabButton);

                    const closeButton = document.createElement("span");
                    closeButton.classList.add("close-button");
                    closeButton.textContent = "✖";
                    closeButton.addEventListener("click", function (e) {
                        e.stopPropagation();
                        tracyConsole.removeTab(tabId);
                    });
                    tabButton.appendChild(closeButton);

                    tracyConsole.tabs[tabId] = {};
                    document.getElementById("tracyConsoleResult").innerHTML = '';

                    tracyConsole.switchTab(tabId);

                    tracyConsole.saveToLocalStorage();
                },

                getNextTabNumber: function() {
                    const existingLabels = Array.from(document.querySelectorAll('.button-label')).map(span => span.textContent.trim());

                    const untitledPattern = /^Untitled‑(\d+)$/;
                    let maxNumber = 0;

                    existingLabels.forEach(label => {
                        const match = label.match(untitledPattern);
                        if (match) {
                            const number = parseInt(match[1], 10);
                            if (number > maxNumber) {
                                maxNumber = number;
                            }
                        }
                    });

                    return maxNumber + 1;
                },

                getDragAfterElement: function (container, x) {
                    const draggableElements = [...container.querySelectorAll("button[draggable='true']:not(.dragging)")];
                    return draggableElements.reduce((closest, child) => {
                        const box = child.getBoundingClientRect();
                        const offset = x - box.left - box.width / 2;
                        if (offset < 0 && offset > closest.offset) {
                            return { offset, element: child };
                        }
                        else {
                            return closest;
                        }
                    }, { offset: Number.NEGATIVE_INFINITY }).element;
                },

                updateLocalStorageTabOrder: function() {
                    // Get the new order of tab elements from the DOM
                    const tabElements = document.querySelectorAll('#tracyTabs button[data-tab-id]');
                    const newTabOrder = Array.from(tabElements).map(tab => parseInt(tab.getAttribute('data-tab-id'), 10));

                    // Retrieve the current tabs from localStorage
                    const tracyConsoleTabs = JSON.parse(localStorage.getItem('tracyConsoleTabs')) || [];

                    // Reorder the tabs array based on the newTabOrder array
                    const updatedTabs = newTabOrder
                        .map(tabId => tracyConsoleTabs.find(tab => tab.id === tabId))
                        .filter(Boolean);

                    // Save the reordered tabs back to localStorage
                    localStorage.setItem('tracyConsoleTabs', JSON.stringify(updatedTabs));
                }

            };

            tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/ace-editor/ace.js", function() {
                if(typeof ace !== "undefined") {
                    tracyConsole.tce = ace.edit("tracyConsoleEditor");
                    tracyConsole.tce.container.style.lineHeight = tracyConsole.lineHeight + 'px';
                    tracyConsole.tce.setFontSize(tracyConsole.codeFontSize);
                    tracyConsole.tce.setShowPrintMargin(false);
                    tracyConsole.tce.setShowInvisibles($codeShowInvisibles);
                    tracyConsole.tce.\$blockScrolling = Infinity;

                    tracyConsole.currentTabId = localStorage.getItem("tracyConsoleSelectedTab") || 1;

                    tracyConsole.tce.on("beforeEndOperation", function() {

                        let tabName;
                        let updateName = false;
                        let tabButton = document.querySelector('button[data-tab-id="'+tracyConsole.currentTabId+'"]');
                        let potentialTabName = tracyConsole.tce.session.getLine(0).substring(0, 15);
                        if(potentialTabName.trim().length) {
                            tabName = potentialTabName.trim();
                            updateName = true;
                        }
                        else if(tabButton.querySelector('.button-label').textContent) {
                            updateName = false;
                        }
                        else {
                            tabName = 'Untitled‑' + tracyConsole.getNextTabNumber();
                            updateName = true;
                        }

                        // if tab button doesn't have lockedTab class, update the tab name
                        if(!tabButton.querySelector('.button-label').classList.contains('lockedTab') && updateName) {
                            tabButton.querySelector('.button-label').textContent = tabName;
                        }

                        tracyConsole.saveToLocalStorage();
                        if(tracyConsole.tce.getValue().indexOf('<?php') !== -1) {
                            tracyConsole.tce.session.setMode('ace/mode/php');
                        }
                        else {
                            tracyConsole.tce.session.setMode({path:"ace/mode/php", inline:true});
                        }
                        tracyConsole.toggleSaveButton();
                        // focus set to false to prevent breaking the Ace search box
                        tracyConsole.resizeAce(false);

                    });

                    tracyConsole.tce.session.on("changeScrollTop", function() {
                        tracyConsole.saveToLocalStorage();
                    });
                    tracyConsole.tce.session.on("changeScrollLeft", function() {
                        tracyConsole.saveToLocalStorage();
                    });

                    // set theme
                    tracyConsole.tce.setTheme("ace/theme/" + tracyConsole.aceTheme);

                    // set autocomplete and other options
                    ace.config.loadModule('ace/ext/language_tools', function () {
                        /*if(!!localStorage.getItem("tracyConsoleTabs") && localStorage.getItem("tracyConsoleTabs") !== "null" && localStorage.getItem("tracyConsoleTabs") !== "undefined") {
                            try {
                                tracyConsole.setEditorState(tracyConsole.getTabItem(tabId));
                            }
                            catch(e) {
                                console.log('error');
                                // for users upgrading from old version of Console panel that didn't store selection & scroll info
                                //tracyConsole.tce.setValue(localStorage.getItem("tracyConsoleTabs"));
                            }
                        }
                        else {
                            tracyConsole.tce.setValue($code);
                            count = tracyConsole.tce.session.getLength();
                            tracyConsole.tce.gotoLine(count, tracyConsole.tce.session.getLine(count-1).length);
                        }*/

                        // set mode to php
                        /*if(localStorage.getItem("tracyConsoleTabs") && JSON.parse(localStorage.getItem("tracyConsoleTabs")).code.indexOf('<?php') !== -1) {
                            tracyConsole.tce.session.setMode('ace/mode/php');
                        }
                        else {*/
                            tracyConsole.tce.session.setMode({path:"ace/mode/php", inline:true});
                        //}


                        tracyConsole.tce.setOptions({
                            enableBasicAutocompletion: true,
                            enableSnippets: true,
                            enableLiveAutocompletion: true,
                            tabSize: $codeTabSize,
                            useSoftTabs: $codeUseSoftTabs,
                            minLines: 5
                        });

                        // all PW variable completers
                        if(tracyConsole.pwAutocomplete.length > 0) {
                            var staticWordCompleter = {
                                getCompletions: function(editor, session, pos, prefix, callback) {
                                    callback(null, tracyConsole.pwAutocomplete.map(function(word) {
                                        return {
                                            value: word.name,
                                            meta: word.meta,
                                            docHTML: word.docHTML
                                        };
                                    }));
                                }
                            };
                            tracyConsole.tce.completers.push(staticWordCompleter);
                        }

                        // included PW snippets
                        tracyConsole.snippetManager = ace.require("ace/snippets").snippetManager;
                        tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/code-snippets.js", function() {
                            tracyConsole.snippetManager.register(getCodeSnippets(), "php-inline");

                            // custom snippets URL
                            if(tracyConsole.customSnippetsUrl !== '') {
                                tracyJSLoader.load(tracyConsole.customSnippetsUrl, function() {
                                    tracyConsole.snippetManager.register(getCustomCodeSnippets(), "php-inline");
                                });
                            }

                        });

                        tracyConsole.tce.commands.addCommands([
                            {
                                name: "increaseFontSize",
                                bindKey: "Ctrl-=|Ctrl-+",
                                exec: function(editor) {
                                    var size = parseInt(tracyConsole.tce.getFontSize(), 10) || 12;
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


                        tracyConsole.tce.setAutoScrollEditorIntoView(true);
                        tracyConsole.resizeAce();

                        // create and append toggle fullscreen/restore buttons
                        var toggleFullscreenButton = document.createElement('div');
                        toggleFullscreenButton.innerHTML = '<span class="fullscreenToggleButton" title="Toggle fullscreen" onclick="tracyConsole.toggleFullscreen()">$maximizeSvg</span>';
                        document.getElementById("tracyConsoleContainer").querySelector('.ace_gutter').prepend(toggleFullscreenButton);

                        // splitjs
                        tracyJSLoader.load(tracyConsole.tracyModuleUrl + "/scripts/splitjs/split.min.js", function() {


                            // setup tabs
                            tracyConsole.tabsContainer = document.getElementById("tracyTabs");
                            tracyConsole.addTabButton = document.getElementById("addTab");
                            tracyConsole.addTabButton.addEventListener("click", tracyConsole.addNewTab);

                            let draggedTab = null;

                            tracyConsole.tabsContainer.addEventListener("dragstart", (e) => {
                                if (e.target === tracyConsole.addTabButton) return;
                                draggedTab = e.target;
                                e.dataTransfer.effectAllowed = "move";
                                e.target.classList.add("dragging");
                            });

                            tracyConsole.tabsContainer.addEventListener("dragend", (e) => {
                                e.target.classList.remove("dragging");
                                draggedTab = null;
                                tracyConsole.updateLocalStorageTabOrder();
                            });

                            tracyTabs.addEventListener("dragover", (e) => {
                                e.preventDefault();

                                // Find the element to insert the dragged tab after
                                const afterElement = tracyConsole.getDragAfterElement(tracyTabs, e.clientX);

                                // Ensure `draggedTab` is a valid child of `tracyTabs`
                                if (!tracyTabs.contains(draggedTab)) {
                                    console.error("Dragged tab is not a child of tracyTabs.");
                                    return;
                                }

                                if (afterElement === tracyConsole.addTabButton || afterElement == null) {
                                    // If dragging to the end, append draggedTab at the end of tracyTabs
                                    tracyTabs.appendChild(draggedTab);
                                } else {
                                    // Ensure `afterElement` is a valid child of `tracyTabs`
                                    if (tracyTabs.contains(afterElement)) {
                                        tracyTabs.insertBefore(draggedTab, afterElement);
                                    } else {
                                        console.error("afterElement is not a child of tracyTabs.");
                                    }
                                }
                            });

                            // for users upgrading from old version of Console panel that didn't have tabs
                            // if there are no tabs in localStorage yet, copy content from the old tracyConsole key to item 1 of tracyConsoleTabs
                            if (!localStorage.getItem("tracyConsoleTabs") && localStorage.getItem("tracyConsole")) {
                                var tracyConsoleData = JSON.parse(localStorage.getItem("tracyConsole"));
                                var tracyConsoleTabs = [
                                    {
                                        id: 1,
                                        ...tracyConsoleData /* spread all keys from tracyConsole */
                                    }
                                ];
                                localStorage.setItem("tracyConsoleTabs", JSON.stringify(tracyConsoleTabs));
                                // remove old items that are either no longer needed, or need their structure updated to prevent errors
                                localStorage.removeItem("tracyConsole");
                                localStorage.removeItem("diskSnippetCode");
                                localStorage.removeItem("tracyConsoleHistory");
                                localStorage.removeItem("tracyConsoleHistoryCount");
                                localStorage.removeItem("tracyConsoleHistoryItem");
                                localStorage.removeItem("tracyConsoleResults");
                            }

                            // load all tabs from localStorage
                            const consoleTabs = JSON.parse(localStorage.getItem("tracyConsoleTabs"));
                            if (consoleTabs) {
                                consoleTabs.forEach((consoleTab) => {
                                    const tabId = consoleTab.id;
                                    tracyConsole.tabs[tabId] = consoleTab;
                                    const tabButton = document.createElement("button");
                                    const buttonLabel = document.createElement("span");
                                    buttonLabel.classList.add("button-label");

                                    if(!consoleTab.name || !consoleTab.name.trim().length) {
                                        consoleTab.name = 'Untitled‑' + tracyConsole.getNextTabNumber();
                                    }
                                    buttonLabel.textContent = consoleTab.name;
                                    tabButton.appendChild(buttonLabel);

                                    tabButton.dataset.tabId = tabId;
                                    tabButton.setAttribute("draggable", "true");
                                    tabButton.addEventListener("click", () => tracyConsole.switchTab(tabId));
                                    tracyConsole.tabsContainer.appendChild(tabButton);

                                    // add close button
                                    const closeButton = document.createElement("span");
                                    closeButton.classList.add("close-button");
                                    closeButton.textContent = "✖";
                                    closeButton.addEventListener("click", function (e) {
                                        e.stopPropagation();
                                        tracyConsole.removeTab(tabId);
                                    });
                                    tabButton.appendChild(closeButton);
                                });
                                tracyConsole.switchTab(localStorage.getItem("tracyConsoleSelectedTab"));
                            }
                            else {
                                tracyConsole.addNewTab();
                            }


                            var sizes = localStorage.getItem('tracyConsoleSplitSizes');
                            tracyConsole.consoleGutterSize = 8;
                            tracyConsole.minSize = tracyConsole.lineHeight;
                            sizes = sizes ? JSON.parse(sizes) : [40, 60];
                            tracyConsole.split = Split(['#tracyConsoleCode', '#tracyConsoleResult'], {
                                direction: 'vertical',
                                cursor: 'row-resize',
                                sizes: sizes,
                                minSize: tracyConsole.minSize,
                                expandToMin: true,
                                gutterSize: tracyConsole.consoleGutterSize,
                                snapOffset: 10,
                                dragInterval: tracyConsole.lineHeight,
                                gutterAlign: 'end',
                                onDrag: tracyConsole.resizeAce,
                                onDragEnd: function() {
                                    // save split
                                    localStorage.setItem('tracyConsoleSplitSizes', JSON.stringify(tracyConsole.split.getSizes()));
                                    tracyConsole.tce.focus();
                                }
                            });

                            document.getElementById("tracyConsoleCode").querySelector(".ace_text-input").addEventListener("keydown", function(e) {
                                if(document.getElementById("tracy-debug-panel-ConsolePanel").classList.contains("tracy-focused")) {
                                    // shift enter - expand to fit all code while still adding new line and save
                                    // shift backspace - delete line and row in code pane and save
                                    if(e.shiftKey && ((e.keyCode==13||e.charCode==13) || (e.keyCode==8||e.charCode==8))) {
                                        var numLines = tracyConsole.tce.session.getLength();
                                        if(e.keyCode==13||e.charCode==13) numLines++;
                                        var containerHeight = document.getElementById('tracyConsoleContainer').offsetHeight;
                                        collapsedCodePaneHeightPct = (tracyConsole.lineHeight + (tracyConsole.consoleGutterSize/2)) / containerHeight * 100;
                                        var codeLinesHeight = (numLines * tracyConsole.lineHeight + (tracyConsole.consoleGutterSize/2));
                                        var codeLinesHeightPct = codeLinesHeight / containerHeight * 100;
                                        if(containerHeight - codeLinesHeight < tracyConsole.lineHeight) codeLinesHeightPct = 100 - collapsedCodePaneHeightPct;
                                        tracyConsole.split.setSizes([codeLinesHeightPct, 100 - codeLinesHeightPct]);
                                        localStorage.setItem('tracyConsoleSplitSizes', JSON.stringify(tracyConsole.split.getSizes()));
                                    }

                                    if(e.ctrlKey && e.shiftKey) {
                                        e.preventDefault();
                                        var containerHeight = document.getElementById('tracyConsoleContainer').offsetHeight;
                                        collapsedCodePaneHeightPct = (tracyConsole.lineHeight + (tracyConsole.consoleGutterSize/2)) / containerHeight * 100;

                                        // enter - toggle fullscreen
                                        if((e.keyCode==10||e.charCode==10)||(e.keyCode==13||e.charCode==13)) {
                                            tracyConsole.toggleFullscreen();
                                        }
                                        // down - maximize code pane (collapse results pane)
                                        if(e.keyCode==40||e.charCode==40) {
                                            tracyConsole.split.collapse(1);
                                        }
                                        // up - minimize code pane
                                        if(e.keyCode==38||e.charCode==38) {
                                            tracyConsole.split.collapse(0);
                                        }
                                        // page down - add new row to code pane and save
                                        if(e.keyCode==34||e.charCode==34) {
                                            sizes = tracyConsole.split.getSizes();
                                            if(sizes[1] > collapsedCodePaneHeightPct + tracyConsole.consoleGutterSize) {
                                                var codePaneHeight = (sizes[0] / 100 * containerHeight) - (tracyConsole.consoleGutterSize/2);
                                                codePaneHeight = Math.round(codePaneHeight + tracyConsole.lineHeight);
                                                codePaneHeight = Math.ceil(codePaneHeight / tracyConsole.lineHeight) * tracyConsole.lineHeight;
                                                sizes[0] = codePaneHeight / containerHeight * 100;
                                                sizes[1] = 100 - sizes[0];
                                                tracyConsole.split.setSizes(sizes);
                                            }
                                            else {
                                                tracyConsole.split.collapse(1);
                                            }
                                            localStorage.setItem('tracyConsoleSplitSizes', JSON.stringify(tracyConsole.split.getSizes()));
                                        }
                                        // page up - remove row from code pane and save
                                        if(e.keyCode==33||e.charCode==33) {
                                            var sizes = tracyConsole.split.getSizes();
                                            if(sizes[0] > collapsedCodePaneHeightPct + tracyConsole.consoleGutterSize) {
                                                var codePaneHeight = (sizes[0] / 100 * containerHeight) - (tracyConsole.consoleGutterSize/2);
                                                codePaneHeight = Math.round(codePaneHeight - tracyConsole.lineHeight);
                                                codePaneHeight = Math.ceil(codePaneHeight / tracyConsole.lineHeight) * tracyConsole.lineHeight;
                                                sizes[0] = codePaneHeight / containerHeight * 100;
                                                sizes[1] = 100 - sizes[0];
                                                tracyConsole.split.setSizes(sizes);
                                            }
                                            else {
                                                tracyConsole.split.collapse(0);
                                            }
                                            localStorage.setItem('tracyConsoleSplitSizes', JSON.stringify(tracyConsole.split.getSizes()));
                                        }
                                        // right - expand to fit all code
                                        if(e.keyCode==39||e.charCode==39) {
                                            var codeLinesHeight = (tracyConsole.tce.session.getLength() * tracyConsole.lineHeight + (tracyConsole.consoleGutterSize/2));
                                            var codeLinesHeightPct = codeLinesHeight / containerHeight * 100;
                                            if(containerHeight - codeLinesHeight < tracyConsole.lineHeight) codeLinesHeightPct = 100 - collapsedCodePaneHeightPct;
                                            tracyConsole.split.setSizes([codeLinesHeightPct, 100 - codeLinesHeightPct]);
                                        }
                                        // left - restore last saved pane split position
                                        if(e.keyCode==37||e.charCode==37) {
                                            var sizes = localStorage.getItem('tracyConsoleSplitSizes');
                                            sizes = sizes ? JSON.parse(sizes) : [40, 60];
                                            tracyConsole.split.setSizes(sizes);
                                        }
                                    }
                                }
                                tracyConsole.resizeAce();
                            });

                            // hack to remove extra gutter in Tracy window mode
                            var elements = document.getElementsByClassName('gutter');
                            while(elements.length > 1) {
                                elements[0].parentNode.removeChild(elements[0]);
                            }
                            tracyConsole.resizeAce();
                        });

                        // checks for changes to the panel
                        var config = { attributes: true, attributeOldValue: true };
                        tracyConsole.observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {

                                // if split is less than minSize then collapse it (which will expand it to minSize)
                                // else restore to stored sizes
                                // this is mostly for resizing of the entire panel
                                if(tracyConsole.split) {
                                    var containerHeight = document.getElementById('tracyConsoleContainer').offsetHeight;
                                    var sizes = tracyConsole.split.getSizes();
                                    if(sizes[0] < 0 || sizes[1] < 0) {
                                        sizes = localStorage.getItem('tracyConsoleSplitSizes');
                                        sizes = sizes ? JSON.parse(sizes) : [40, 60];
                                    }
                                    if(sizes[0] * containerHeight / 100 < tracyConsole.minSize - (tracyConsole.consoleGutterSize/2)) {
                                        tracyConsole.split.collapse(0);
                                    }
                                    else if(sizes[1] * containerHeight / 100 < tracyConsole.minSize - (tracyConsole.consoleGutterSize/2)) {
                                        tracyConsole.split.collapse(1);
                                    }
                                    else {
                                        tracyConsole.split.setSizes(sizes);
                                    }
                                }

                                // change in class indicates focus so we can focus cursor in editor
                                if(mutation.attributeName == 'class' && mutation.oldValue !== mutation.target.className && mutation.oldValue.indexOf('tracy-focused') === -1 && mutation.target.classList.contains('tracy-focused')) {
                                    tracyConsole.resizeAce();
                                }
                                // else if a change in style then resize but don't focus
                                else if(mutation.attributeName == 'style') {
                                    tracyConsole.resizeAce(false);
                                }
                            });
                        });
                        tracyConsole.observer.observe(document.getElementById("tracy-debug-panel-ConsolePanel"), config);

                        // this is necessary for Safari, but not Chrome and Firefox
                        // otherwise resizing panel container doesn't resize internal console panes
                        if(tracyConsole.isSafari()) {
                            document.getElementById("tracy-debug-panel-ConsolePanel").addEventListener('mousemove', function() {
                                tracyConsole.resizeAce();
                            });
                        }

                        window.onresize = function(event) {
                            if(document.getElementById("tracy-debug-panel-ConsolePanel").classList.contains("tracy-focused")) {
                                tracyConsole.resizeAce();
                            }
                        };

                        // build snippet list, populate local storage version from database, and show last selected snippet
                        tracyConsole.modifySnippetList(null, $snippets, false);
                        localStorage.setItem("tracyConsoleSnippets", JSON.stringify($snippets));
                        let tracyConsoleSelectedSnippet = localStorage.getItem("tracyConsoleSelectedSnippet");
                        if(tracyConsoleSelectedSnippet) {
                            let tracyConsoleSelectedSnippetEl = document.getElementById(tracyConsole.makeIdFromTitle(localStorage.getItem("tracyConsoleSelectedSnippet")));
                            if(tracyConsoleSelectedSnippetEl) {
                                document.getElementById("tracySnippetName").value = localStorage.getItem("tracyConsoleSelectedSnippet");
                                tracyConsoleSelectedSnippetEl.classList.add("activeSnippet");
                                tracyConsole.enableButton("reloadSnippet");
                            }
                        }

                        tracyConsole.sortList('alphabetical');

                        // history buttons
                        if(tracyConsole.historyCount == tracyConsole.historyItem || !tracyConsole.historyItem || !tracyConsole.historyCount) {
                            tracyConsole.disableButton("historyForward");
                        }
                        if(!tracyConsole.historyItem || tracyConsole.historyItem == 1 || tracyConsole.historyCount < 2) {
                            tracyConsole.disableButton("historyBack");
                        }

                        // various keyboard shortcuts
                        document.getElementById("tracyConsoleCode").querySelector(".ace_text-input").addEventListener("keydown", function(e) {
                            if(document.getElementById("tracy-debug-panel-ConsolePanel").classList.contains("tracy-focused")) {
                                if(((e.keyCode==10||e.charCode==10)||(e.keyCode==13||e.charCode==13)) && (e.metaKey || e.ctrlKey || e.altKey) && !e.shiftKey) {
                                    e.preventDefault();
                                    if(e.altKey) tracyConsole.clearResults();
                                    if((e.metaKey || e.ctrlKey) && e.altKey) {
                                        tracyConsole.reloadAndRun();
                                    }
                                    else {
                                        tracyConsole.processTracyCode();
                                    }
                                }
                                if((e.keyCode==33||e.charCode==33) && e.altKey) {
                                    tracyConsole.loadHistory('back');
                                }
                                if((e.keyCode==34||e.charCode==34) && e.altKey) {
                                    tracyConsole.loadHistory('forward');
                                }
                            }
                        });

                        // activate save button when typing new snippet name
                        document.getElementById("tracySnippetName").onkeyup = function() {
                            tracyConsole.enableButton("saveSnippet");
                        };

                        // add tracy-debug class to all stylesheets so that they won't be removed
                        // by Tracy core when bluescreen is triggered from Console exception
                        // this is no longer needed with Tracy core 2.9.1+,
                        // but keeping while we are still supporting older versions
                        for (let i = 0; i < document.styleSheets.length; i++) {
			                let style = document.styleSheets[i];
                            style.ownerNode.classList.add("tracy-debug");
                        }

                    });

                }
            });

            function loadFAIfNotAlreadyLoaded() {
                if(!document.getElementById("fontAwesome")) {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = "/wire/templates-admin/styles/font-awesome/css/font-awesome.min.css";
                    document.getElementsByTagName("head")[0].appendChild(link);
                }
            }
            loadFAIfNotAlreadyLoaded();

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

        $out .= '
        <h1>' . $this->icon . ' Console
            <span title="Keyboard Shortcuts" style="display: inline-block; margin-left: 5px; cursor: pointer" onclick="tracyConsole.toggleKeyboardShortcuts()">' . $keyboardShortcutIcon . '</span>
            <span id="tracyConsoleStatus" style="padding-left: 50px"></span>
        </h1>
        <span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'ConsolePanel\')">+</a></span></span>
        <div class="tracy-inner">

            <div style="position: relative; height: calc(100% - 80px)">

                <div id="tracyConsoleMainContainer" style="position: absolute; height: 100%; width: '.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? '100%' : 'calc(100% - 290px)').'">

                    <div id="consoleKeyboardShortcuts" class="keyboardShortcuts tracyHidden">';
                        $panel = 'console';
                        include($this->wire('config')->paths->TracyDebugger.'includes/AceKeyboardShortcuts.php');
                        $out .= $aceKeyboardShortcuts . '
                    </div>
                    ';

                    $out .= '
                    <div>
                        <span style="display: inline-block; padding: 0 20px 10px 0">
                            <input id="reloadSnippet" title="Reload current snippet from disk" class="disabledButton" style="font-family: FontAwesome !important; padding: 3px 8px !important" type="submit" onclick="tracyConsole.reloadSnippet()" value="&#xf021" disabled="true" />&nbsp;&nbsp;
                            <input style="font-family: FontAwesome !important" title="Go back (ALT + PageUp)" id="historyBack" type="submit" onclick="tracyConsole.loadHistory(\'back\')" value="&#xf060;" />&nbsp;
                            <input style="font-family: FontAwesome !important" title="Go forward (ALT + PageDown)" id="historyForward" type="submit" onclick="tracyConsole.loadHistory(\'forward\')" value="&#xf061;" />&nbsp;
                            <input title="Clear results" type="button" class="clearResults" onclick="tracyConsole.clearResults()" value="&#10006; Clear results" />
                        </span>

                        <span style="display: inline-block; padding: 0 20px 10px 0">
                            <label title="Backup entire database before executing script.">
                                <input type="checkbox" id="dbBackup" '.($this->wire('input')->cookie->tracyDbBackup ? 'checked="checked"' : '').' onclick="tracyConsole.updateBackupState();" /> Backup DB
                            </label>&nbsp;&nbsp;
                            <input id="backupFilename" type="text" placeholder="Backup name (optional)" '.($this->wire('input')->cookie->tracyDbBackup ? 'style="display:inline-block !important"' : 'style="display:none !important"').' '.($this->wire('input')->cookie->tracyDbBackupFilename ? 'value="'.$this->wire('input')->cookie->tracyDbBackupFilename.'"' : '').' />
                        </span>
                        <span style="display: inline-block; padding: 0 20px 10px 0">
                            <label title="Send full stack trace of errors to Tracy bluescreen">
                                <input type="checkbox" id="allowBluescreen" /> Allow bluescreen
                            </label>
                        </span>
                        ';

                        if(!$inAdmin) {
                            $out .= '
                        <span style="display: inline-block; padding: 0 20px 10px 0">
                            <label title="Access custom variables & functions from this page\'s template file & included files."><input type="checkbox" id="accessTemplateVars" onclick="tracyConsole.tce.focus();" /> Template resources</label>
                        </span>';
                        }

                        $out .= '
                        <span style="display:inline-block; padding-right: 10px;">
                            <select name="includeCode" style="height: 24px !important" title="When to execute code" onchange="tracyConsole.tracyIncludeCode(this)" />
                                <option value="off"' . (!$this->tracyIncludeCode || $this->tracyIncludeCode['when'] === 'off' ? ' selected' : '') . '>@ Run</option>
                                <option value="init"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'init' ? ' selected' : '') . '>@ Init</option>
                                <option value="ready"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'ready' ? ' selected' : '') . '>@ Ready</option>
                                <option value="finished"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'finished' ? ' selected' : '') . '>@ Finished</option>
                            </select>
                        </span>
                        <input id="runInjectButton" title="&bull; Run (CTRL/CMD + Enter)&#10;&bull; Clear & Run (ALT/OPT + Enter)&#10;&bull; Reload from Disk, Clear & Run&#10;(CTRL/CMD + ALT/OPT + Enter)" type="submit" onclick="tracyConsole.processTracyCode()" value="' . (!$this->tracyIncludeCode || $this->tracyIncludeCode['when'] === 'off' ? 'Run' : 'Inject') . '" />
                        <span id="snippetPaneToggle" title="Toggle snippets pane" style="float:right; font-weight: bold; cursor: pointer" onclick="tracyConsole.toggleSnippetsPane()">'.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? '<' : '>').'</span>
                    </div>

                    <div id="tracyConsoleContainer" class="split" style="height: 100%; min-height: '.$codeLineHeight.'px">
                        <div id="tracyTabsContainer">
                            <div id="tracyTabsWrapper">
                                <div id="tracyTabs"></div>
                            </div>
                            <button id="addTab" title="Add Tab">+</button>
                        </div>
                        <div id="tracyConsoleCode" class="split" style="position: relative; background: #FFFFFF;">
                            <div id="tracyConsoleEditor" style="height: 100%; min-height: '.$codeLineHeight.'px"></div>
                        </div>
                        <div id="tracyConsoleResult" class="split" style="position:relative; padding:0 10px; overflow:auto; border:1px solid #D2D2D2;">';

                if($this->dbRestoreMessage) {
                    $out .= '<div style="padding: 10px 0">' . $this->dbRestoreMessage . '</div>' .
                            '<div style="padding: 10px; border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
                }
                if($this->wire('input')->cookie->tracyCodeError) {
                    $out .= '<div style="padding: 10px 0">' . $this->wire('input')->cookie->tracyCodeError . '</div>' .
                            '<div style="padding: 10px; border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
                }
                $out .= '
                        </div>
                    </div>
                </div>

                <div id="tracySnippetsContainer" style="position: absolute; right:0; margin: 0 0 0 10px; width: 275px; height: calc(100% - 15px);"'.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? ' class="tracyHidden"' : '').'">
                    <div style="padding-bottom:5px">
                        Sort: <a href="#" onclick="tracyConsole.sortList(\'alphabetical\')">alphabetical</a>&nbsp;|&nbsp;<a href="#" onclick="tracyConsole.sortList(\'chronological\')">chronological</a>
                    </div>
                    <div style="position: relative; width:100% !important;">
                        <input type="text" id="tracySnippetName" placeholder="Enter filename (eg. myscript.php)" />
                        <input id="saveSnippet" type="submit" class="disabledButton" onclick="tracyConsole.saveSnippet()" value="&#128190;" title="Save snippet" />
                    </div>
                    <div id="tracySnippets"></div>
                </div>

            </div>
            ';
        $out .= \TracyDebugger::generatePanelFooter('console', \Tracy\Debugger::timer('console'), strlen($out), 'consolePanel');
        $out .= '
        </div>';

        return parent::loadResources() . \TracyDebugger::minify($out);

    }

}
