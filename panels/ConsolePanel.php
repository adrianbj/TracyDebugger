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

        $this->tracyIncludeCode = json_decode($this->wire('input')->cookie->tracyIncludeCode, true);
        if($this->tracyIncludeCode && $this->tracyIncludeCode['when'] !== 'off') {
            $this->iconColor = $this->wire('input')->cookie->tracyCodeError ? \TracyDebugger::COLOR_ALERT : \TracyDebugger::COLOR_WARN;
        }
        else {
            $this->iconColor = \TracyDebugger::COLOR_NORMAL;
        }

        $this->icon = '
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
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

        $pwRoot = $this->wire('config')->urls->root;
        $rootPath = $this->wire('config')->paths->root;
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
        '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
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
                maxHistoryItems: 25,
                historyItem: localStorage.getItem("tracyConsoleHistoryItem") ? localStorage.getItem("tracyConsoleHistoryItem") : 1,
                historyCount: localStorage.getItem("tracyConsoleHistoryCount"),
                diskSnippetCode: null,
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

                saveToLocalStorage: function(name) {
                    var tracyHistoryItem = {code: this.tce.getValue(), selections: this.tce.selection.toJSON(), scrollTop: this.tce.session.getScrollTop(), scrollLeft: this.tce.session.getScrollLeft()};
                    localStorage.setItem(name, JSON.stringify(tracyHistoryItem));
                },

                setEditorState: function(data) {
                    this.tce.setValue(data.code);
                    this.tce.selection.fromJSON(data.selections);
                    this.tce.session.setScrollTop(data.scrollTop);
                    this.tce.session.setScrollLeft(data.scrollLeft);
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
                    var backupFilename = document.getElementById("backupFilename").value;
                    var accessTemplateVars = !this.inAdmin ? document.getElementById("accessTemplateVars").checked : "false";

                    xmlhttp.open("POST", "./", true);
                    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xmlhttp.send("tracyConsole=1&codeReturn=codeReturn&dbBackup="+dbBackup+"&backupFilename="+backupFilename+"&accessTemplateVars="+accessTemplateVars+"&pid={$pid}&fid={$fid}&tid={$tid}&mid={$mid}&code="+encodeURIComponent(code));
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
                                tracyConsole.diskSnippetCode = xmlhttp.responseText;
                                localStorage.setItem("diskSnippetCode", tracyConsole.diskSnippetCode);
                                tracyConsole.tce.setValue(xmlhttp.responseText);
                                tracyConsole.tce.gotoLine(0, 0);
                                if(process) tracyConsole.processTracyCode();

                                // set mode appropriately
                                tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/ace-editor/ext-modelist.js", function() {
                                    tracyConsole.modelist = ace.require("ace/ext/modelist");
                                    var mode = tracyConsole.modelist.getModeForPath(tracyConsole.rootPath+tracyConsole.snippetsPath+name).mode;
                                    if(tracyConsole.diskSnippetCode.indexOf('<?php') !== -1) {
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
                    xmlhttp.open("POST", "./", true);
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
                        snippetList += "<li title='Load in console' id='"+this.makeIdFromTitle(obj.name)+"' data-modified='"+obj.modified+"'><span class='consoleSnippetIcon consoleEditIcon iconFlip'>" + this.externalEditorLink.replace('ExternalEditorDummyFile', obj.name) + "</span><span class='consoleSnippetIcon' title='Delete snippet' onclick='tracyConsole.modifyConsoleSnippets(\""+obj.name+"\", null, true)'>&#10006;</span><span style='color: #125EAE; cursor: pointer; width:200px; word-break: break-all;' onclick='tracyConsole.loadSnippet(\""+obj.name+"\");'>" + obj.name + "</span></li>";
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
                    xmlhttp.open("POST", "./", true);
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
                        localStorage.setItem("diskSnippetCode", this.tce.getValue());
                        this.disableButton("saveSnippet");
                        this.tce.focus();
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

                loadHistory: function(direction) {
                    var noItem = false;

                    if(direction === 'back' && tracyConsole.historyItem > 1) {
                        var id = --tracyConsole.historyItem;
                    }
                    else if(direction === 'forward' && tracyConsole.historyCount > tracyConsole.historyItem) {
                        var id = ++tracyConsole.historyItem;
                    }
                    else {
                        var id = tracyConsole.historyItem;
                        noItem = true;
                    }

                    if(id == 1) {
                        this.disableButton("historyBack");
                    }
                    else {
                        this.enableButton("historyBack");
                    }
                    if(tracyConsole.historyCount == id) {
                        this.disableButton("historyForward");
                    }
                    else {
                        this.enableButton("historyForward");
                    }

                    document.getElementById("tracySnippetName").value = '';

                    if(noItem) return;
                    localStorage.setItem("tracyConsoleHistoryItem", tracyConsole.historyItem);
                    this.setEditorState(this.getHistoryItem(id));
                    this.tce.focus();
                },

                newSnippet: function() {
                    tracyConsole.tce.setValue('');
                    tracyConsole.tce.session.setMode({path:"ace/mode/php", inline:true});
                    localStorage.removeItem("tracyConsoleSelectedSnippet");
                    if(document.querySelector(".activeSnippet")) {
                        document.querySelector(".activeSnippet").classList.remove("activeSnippet");
                    }
                    document.getElementById("tracySnippetName").value = '';
                    this.disableButton("reloadSnippet");
                    this.resizeAce();
                },

                reloadSnippet: function(process = false) {
                    let snippetName = localStorage.getItem("tracyConsoleSelectedSnippet");
                    this.loadSnippet(snippetName, process);
                },

                loadSnippet: function(name, process = false) {
                    this.getSnippet(name, process);
                    this.setActiveSnippet(name);
                    localStorage.setItem("tracyConsoleSelectedSnippet", name);
                    document.getElementById("tracySnippetName").value = name;
                    this.enableButton("reloadSnippet");
                    this.disableButton("saveSnippet");
                    ++tracyConsole.historyItem;
                    this.resizeAce();
                },

                toggleSaveButton: function() {
                    if(localStorage.getItem("diskSnippetCode") != tracyConsole.tce.getValue()) {
                        tracyConsole.enableButton("saveSnippet");
                    }
                    else {
                        tracyConsole.disableButton("saveSnippet");
                    }
                },

                saveHistory: function() {
                    var code = this.tce.getValue();
                    var selections = this.tce.selection.toJSON();
                    if(code) {
                        var existingItems = JSON.parse(localStorage.getItem("tracyConsoleHistory"));
                        var tracyConsoleHistory = [];
                        var count = 0;
                        var id = 1;
                        // add existing items with revised "id"
                        for(var key in existingItems) {
                            if(!existingItems.hasOwnProperty(key)) continue;
                            ++count;
                            if(existingItems.length === tracyConsole.maxHistoryItems && count === 1) continue;
                            tracyConsoleHistory.push({id: id, code: existingItems[key].code, selections: existingItems[key].selections, scrollTop: existingItems[key].scrollTop, scrollLeft: existingItems[key].scrollLeft});
                            ++id;
                        }
                        // add new item
                        tracyConsoleHistory.push({id: id, code: code, selections: selections, scrollTop: this.tce.session.getScrollTop(), scrollLeft: this.tce.session.getScrollLeft()});

                        localStorage.setItem("tracyConsoleHistoryCount", tracyConsole.historyCount = id);
                        localStorage.setItem("tracyConsoleHistoryItem", tracyConsole.historyItem = id);
                        localStorage.setItem("tracyConsoleHistory", JSON.stringify(tracyConsoleHistory));

                        this.disableButton("historyForward");
                        if(tracyConsole.historyCount > 1) this.enableButton("historyBack");
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

                    tracyConsole.tce.on("beforeEndOperation", function() {
                        tracyConsole.saveToLocalStorage('tracyConsole');
                        if(tracyConsole.tce.getValue().indexOf('<?php') !== -1) {
                            tracyConsole.tce.session.setMode('ace/mode/php');
                        }
                        tracyConsole.toggleSaveButton();
                        // focus set to false to prevent breaking the Ace search box
                        tracyConsole.resizeAce(false);
                    });

                    tracyConsole.tce.session.on("changeScrollTop", function() {
                        tracyConsole.saveToLocalStorage('tracyConsole');
                    });
                    tracyConsole.tce.session.on("changeScrollLeft", function() {
                        tracyConsole.saveToLocalStorage('tracyConsole');
                    });

                    // set theme
                    tracyConsole.tce.setTheme("ace/theme/" + tracyConsole.aceTheme);

                    // set autocomplete and other options
                    ace.config.loadModule('ace/ext/language_tools', function () {
                        if(!!localStorage.getItem("tracyConsole") && localStorage.getItem("tracyConsole") !== "null" && localStorage.getItem("tracyConsole") !== "undefined") {
                            try {
                                tracyConsole.setEditorState(JSON.parse(localStorage.getItem("tracyConsole")));
                            }
                            catch(e) {
                                // for users upgrading from old version of Console panel that didn't store selection & scroll info
                                tracyConsole.tce.setValue(localStorage.getItem("tracyConsole"));
                            }
                        }
                        else {
                            tracyConsole.tce.setValue($code);
                            count = tracyConsole.tce.session.getLength();
                            tracyConsole.tce.gotoLine(count, tracyConsole.tce.session.getLine(count-1).length);
                        }

                        // set mode to php
                        if(localStorage.getItem("tracyConsole") && JSON.parse(localStorage.getItem("tracyConsole")).code.indexOf('<?php') !== -1) {
                            var mode = 'ace/mode/php';
                        }
                        else {
                            var mode = {path:"ace/mode/php", inline:true}
                        }
                        tracyConsole.tce.session.setMode(mode);

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

                    });

                }
            });

            function loadFAIfNotAlreadyLoaded() {
                if(!document.getElementById("fontAwesome")) {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = "$pwRoot" + "wire/templates-admin/styles/font-awesome/css/font-awesome.min.css";
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

            <div style="position: relative; height: calc(100% - 45px)">

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
                            <input title="New snippet" type="submit" onclick="tracyConsole.newSnippet()" value="➕" />&nbsp;&nbsp;
                            <input id="reloadSnippet" title="Reload current snippet from disk" class="disabledButton" style="font-weight: 600 !important" type="submit" onclick="tracyConsole.reloadSnippet()" value="↻" disabled="true" />&nbsp;&nbsp;
                            <input style="font-family: FontAwesome !important" title="Go back (ALT + PageUp)" id="historyBack" type="submit" onclick="tracyConsole.loadHistory(\'back\')" value="&#xf060;" />&nbsp;
                            <input style="font-family: FontAwesome !important" title="Go forward (ALT + PageDown)" class="iconFlip" id="historyForward" type="submit" onclick="tracyConsole.loadHistory(\'forward\')" value="&#xf060;" />&nbsp;
                            <input title="Clear results" type="button" class="clearResults" onclick="tracyConsole.clearResults()" value="&#10006; Clear results" />
                        </span>

                        <span style="display: inline-block; padding: 0 20px 10px 0">
                            <label title="Backup entire database before executing script.">
                                <input type="checkbox" id="dbBackup" '.($this->wire('input')->cookie->tracyDbBackup ? 'checked="checked"' : '').' onclick="tracyConsole.updateBackupState();" /> Backup DB
                            </label>&nbsp;&nbsp;
                            <input id="backupFilename" type="text" placeholder="Backup name (optional)" '.($this->wire('input')->cookie->tracyDbBackup ? 'style="display:inline-block !important"' : 'style="display:none !important"').' '.($this->wire('input')->cookie->tracyDbBackupFilename ? 'value="'.$this->wire('input')->cookie->tracyDbBackupFilename.'"' : '').' />
                        </span>';

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
