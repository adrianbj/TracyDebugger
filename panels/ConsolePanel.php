<?php

class ConsolePanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $tracyIncludeCode;

    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('console');

        $this->tracyIncludeCode = json_decode($this->wire('input')->cookie->tracyIncludeCode, true);
        if($this->tracyIncludeCode && $this->tracyIncludeCode['when'] !== 'off') {
            $this->iconColor = $this->wire('input')->cookie->tracyCodeError ? '#CD1818' : '#FF9933';
        }
        else {
            $this->iconColor = '#444444';
        }

        $this->icon = '
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="13.7px" viewBox="439 504.1 16 13.7" enable-background="new 439 504.1 16 13.7" xml:space="preserve">
            <path fill="'.$this->iconColor.'" d="M453.9,504.1h-13.7c-0.6,0-1.1,0.5-1.1,1.1v11.4c0,0.6,0.5,1.1,1.1,1.1h13.7c0.6,0,1.1-0.5,1.1-1.1v-11.4
                C455,504.7,454.5,504.1,453.9,504.1z M441.3,512.1l2.3-2.3l-2.3-2.3l1.1-1.1l3.4,3.4l-3.4,3.4L441.3,512.1z M450.4,513.3h-4.6v-1.1
                h4.6V513.3z"/>
            </svg>';

        return '
        <span title="Console">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Console' : '') . '
        </span>';
    }

    public function getPanel() {

        $tracyModuleUrl = $this->wire("config")->urls->TracyDebugger;
        $inAdmin = \TracyDebugger::$inAdmin;
        $consoleContainerAdjustment = $inAdmin ? 160 : 190;

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

        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
            $fid = (int)$this->wire('input')->get('id');
        }
        else {
            $fid = null;
        }
        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
            $tid = (int)$this->wire('input')->get('id');
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

        // get snippets from DB to populate local storage
        $snippets = \TracyDebugger::getDataValue('snippets');
        if(!$snippets) $snippets = json_encode(array());

        $out = '<script>' . file_get_contents($this->wire("config")->paths->TracyDebugger . 'scripts/js-loader.js') . '</script>';
        $out .= '<script>' . file_get_contents($this->wire("config")->paths->TracyDebugger . 'scripts/get-query-variable.js') . '</script>';

        // determine whether 'l' or 'line' is used for line number with current editor
        parse_str(\Tracy\Debugger::$editor, $vars);
        $lineVar = array_key_exists('l', $vars) ? 'l' : 'line';

        $out .= <<< HTML
        <script>

            var tracyConsole = {

                tce: {},
                tracyModuleUrl: "$tracyModuleUrl",
                maxHistoryItems: 25,
                historyItem: localStorage.getItem("tracyConsoleHistoryItem") ? localStorage.getItem("tracyConsoleHistoryItem") : 1,
                historyCount: localStorage.getItem("tracyConsoleHistoryCount"),
                loadedSnippetCode: null,
                desc: false,
                inAdmin: "$inAdmin",

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
                    document.getElementById("tracyConsoleStatus").innerHTML = "<i class='fa fa-spinner fa-spin'></i> Processing";
                    this.callPhp(code);
                    this.saveHistory();
                    this.tce.focus();
                },

                tracyIncludeCode: function(when) {
                    params = {when: when, pid: "{$p->id}"};
                    if(when === 'off') {
                        document.cookie = "tracyIncludeCode=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    }
                    else {
                        var expires = new Date();
                        expires.setMinutes(expires.getMinutes() + 5);
                        document.cookie = "tracyIncludeCode="+JSON.stringify(params)+";expires="+expires.toGMTString()+";path=/";
                    }
                },

                callPhp: function(code) {
                    var xmlhttp;
                    xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                            document.getElementById("tracyConsoleStatus").innerHTML = "Completed!";
                            if(xmlhttp.status == 200) {
                                document.getElementById("tracyConsoleResult").innerHTML += tracyConsole.tryParseJSON(xmlhttp.responseText);
                                // scroll to bottom of results
                                var objDiv = document.getElementById("tracyConsoleResult");
                                objDiv.scrollTop = objDiv.scrollHeight;
                            }
                            // this may no longer be needed since we now have our own non-Tracy fatal error / shutdown handler
                            // but maybe leave just in case? Maybe still relevant for init, ready, finished injecting?
                            else {
                                var tracyBsError = new DOMParser().parseFromString(xmlhttp.responseText, "text/html");
                                var tracyBsErrorDiv = tracyBsError.getElementById("tracy-bs-error");
                                var tracyBsErrorType = tracyBsErrorDiv.getElementsByTagName('p')[0].innerHTML;
                                var tracyBsErrorText = tracyBsErrorDiv.getElementsByTagName('h1')[0].getElementsByTagName('span')[0].innerHTML;
                                var tracyBsErrorLineNum = tracyDebugger.getQueryVariable('{$lineVar}', tracyBsError.querySelector('[data-tracy-href]').getAttribute("data-tracy-href")) - 1;
                                var tracyBsErrorStr = "<br />" + tracyBsErrorType + ": " + tracyBsErrorText + " on line: " + tracyBsErrorLineNum + "<br />";
                                document.getElementById("tracyConsoleResult").innerHTML = xmlhttp.status+": " + xmlhttp.statusText + tracyBsErrorStr + "<div style='border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;'></div>";

                                var expires = new Date();
                                expires.setMinutes(expires.getMinutes() + (10 * 365 * 24 * 60));
                                document.cookie = "tracyCodeError="+xmlhttp.status+": " + xmlhttp.statusText + tracyBsErrorStr + ";expires="+expires.toGMTString()+";path=/";
                            }
                            xmlhttp.getAllResponseHeaders();
                        }
                    };

                    var accessTemplateVars = !this.inAdmin ? document.getElementById("accessTemplateVars").checked : "false";

                    xmlhttp.open("POST", "./", true);
                    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xmlhttp.send("tracyConsole=1&accessTemplateVars="+accessTemplateVars+"&pid={$p->id}&fid={$fid}&tid={$tid}&mid={$mid}&code="+encodeURIComponent(code));
                },

                resizeContainers: function() {
                    var consolePanel = document.getElementById("tracy-debug-panel-ConsolePanel");
                    // if normal mode (not in window), then we need to make adjustment to make height a little shorter ($consoleContainerAdjustment)
                    if(!consolePanel.classList.contains('tracy-mode-window')) {
                        var consolePanelHeight = consolePanel.offsetHeight;
                        document.getElementById("tracyConsoleContainer").style.height = (consolePanelHeight - {$consoleContainerAdjustment}) + 'px';
                        document.getElementById("tracySnippetsContainer").style.height = (consolePanelHeight - {$consoleContainerAdjustment}) + 'px';
                    }
                },

                resizeAce: function(focus = true) {
                    tracyConsole.resizeContainers();
                    var ml = Math.round(document.getElementById("tracyConsoleCode").offsetHeight / tracyConsole.tce.renderer.lineHeight);
                    tracyConsole.tce.setOptions({
                        maxLines: (ml - 1),
                        minLines: (ml - 1)
                    });
                    tracyConsole.setEditorHeight();
                    if(focus) tracyConsole.tce.focus();
                },

                setEditorHeight: function() {
                    // no idea why it works to have this before changing size of div, but doesn't work if after
                    tracyConsole.tce.resize(true);
                    document.getElementById("tracyConsoleEditor").style.height = document.getElementById("tracyConsoleCode").offsetHeight + 'px';
                },

                resizePanel: function(size) {
                    var currentTop = document.getElementById("tracy-debug-panel-ConsolePanel").offsetTop;
                    var currentHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
                    size = size == 's' ? '50%' : 'calc(100vh - 90px)';
                    document.getElementById("tracy-debug-panel-ConsolePanel").style.height = size;
                    localStorage.setItem('tracyConsolePanelHeight', size);
                    this.resizeAce();
                    if(document.getElementById("tracy-debug-panel-ConsolePanel").offsetTop < 0) {
                        document.getElementById("tracy-debug-panel-ConsolePanel").style.bottom = (currentHeight - document.getElementById("tracy-debug-panel-ConsolePanel").offsetHeight - currentTop) + 'px';
                    }
                },

                getSnippet: function(name) {
                    var tracyConsoleSnippets = this.getAllSnippets();
                    for(var key in tracyConsoleSnippets) {
                        if(!tracyConsoleSnippets.hasOwnProperty(key)) continue;
                        var obj = tracyConsoleSnippets[key];
                        if(obj.name === name) {
                            return obj.code;
                        }
                    }
                },

                getAllSnippets: function() {
                    return JSON.parse(localStorage.getItem("tracyConsoleSnippets"));
                },

                modifyConsoleSnippets: function(tracySnippetName, code) {
                    var tracyConsoleSnippets = [];
                    if(code) {
                        tracyConsoleSnippets.push({name: tracySnippetName, code: code, modified: Date.now()});
                    }
                    else {
                        if(!confirm("Are you sure you want to delete snippet \"" + tracySnippetName + "\"?")) return false;
                    }
                    var existingSnippets = this.getAllSnippets();
                    for(var key in existingSnippets) {
                        if(!existingSnippets.hasOwnProperty(key)) continue;
                        var obj = existingSnippets[key];
                        if(obj.name !== tracySnippetName) tracyConsoleSnippets.push(obj);
                    }
                    var deleteSnippet = !code;
                    this.setAllSnippets(tracySnippetName, tracyConsoleSnippets, deleteSnippet);
                },

                modifySnippetList: function(name, existingSnippets, deleteSnippet) {
                    if(!existingSnippets) var existingSnippets = this.getAllSnippets();
                    var snippetList = "<ul id='snippetsList'>";
                    for(var key in existingSnippets) {
                        if(!existingSnippets.hasOwnProperty(key)) continue;
                        var obj = existingSnippets[key];
                        if(deleteSnippet === true && obj.name === name) continue;
                        snippetList += "<li id='"+this.makeIdFromTitle(obj.name)+"' data-modified='"+obj.modified+"'><span class='trashIcon' title='Delete this snippet' onclick='tracyConsole.modifyConsoleSnippets(\""+obj.name+"\", null)'>&#10006;</span><span style='color: #125EAE; cursor: pointer' onclick='tracyConsole.loadSnippet(\""+obj.name+"\");'>" + obj.name + "</span></li>";
                    }
                    snippetList += "</ul>";
                    document.getElementById("tracySnippets").innerHTML = snippetList;
                },

                setAllSnippets: function(tracySnippetName, tracyConsoleSnippets, deleteSnippet) {
                    // push to local storage for access during current page instance
                    localStorage.setItem("tracyConsoleSnippets", JSON.stringify(tracyConsoleSnippets));

                    // save to DB for access on initial page load
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
                    xmlhttp.send("tracysnippets=1&snippets="+encodeURIComponent(JSON.stringify(tracyConsoleSnippets)));
                },

                makeIdFromTitle: function(title) {
                    return title.replace(/^[^a-z]+|[^\w:.-]+/gi, "");
                },

                saveSnippet: function() {
                    var tracySnippetName = document.getElementById("tracySnippetName").value;
                    if(tracySnippetName != "") {
                        this.modifyConsoleSnippets(tracySnippetName, this.tce.getValue());
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
                    var t1 = a1.innerText,
                        t2 = a2.innerText;
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

                loadSnippet: function(name) {
                    this.loadedSnippetCode = this.getSnippet(name);
                    this.tce.setValue(this.loadedSnippetCode);
                    document.getElementById("tracySnippetName").value = name;
                    this.tce.gotoLine(0,0);
                    ++tracyConsole.historyItem;
                    this.resizeAce();
                },

                toggleSnippetButton: function() {
                    var tracySnippetNameInputValue = document.getElementById("tracySnippetName").value;
                    if(typeof tracyConsole.loadedSnippetCode === 'undefined' || (tracySnippetNameInputValue != '' && tracyConsole.tce.getValue() != tracyConsole.loadedSnippetCode)) {
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
                }

            };

            tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/ace-editor/ace.js", function() {
                if(typeof ace !== "undefined") {
                    tracyConsole.tce = ace.edit("tracyConsoleEditor");
                    tracyConsole.tce.container.style.lineHeight = '23px';
                    tracyConsole.tce.setFontSize(13);
                    tracyConsole.tce.setShowPrintMargin(false);
                    tracyConsole.tce.\$blockScrolling = Infinity;

                    tracyConsole.tce.on("beforeEndOperation", function() {
                        tracyConsole.saveToLocalStorage('tracyConsole');
                        tracyConsole.toggleSnippetButton();
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
                    tracyConsole.tce.setTheme("ace/theme/tomorrow_night");

                    // set mode to php
                    tracyConsole.tce.session.setMode({path:"ace/mode/php", inline:true});

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

                        tracyConsole.tce.setOptions({
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true,
                            minLines: 5
                        });

                        tracyConsole.tce.setAutoScrollEditorIntoView(true);
                        tracyConsole.resizeAce();

                        // splitjs
                        tracyJSLoader.load(tracyConsole.tracyModuleUrl + "/scripts/splitjs/split.min.js", function() {
                            var sizes = localStorage.getItem('tracyConsoleSplitSizes');
                            sizes = sizes ? JSON.parse(sizes) : [40, 60];
                            var split = Split(['#tracyConsoleCode', '#tracyConsoleResult'], {
                                direction: 'vertical',
                                minSize: tracyConsole.tce.renderer.lineHeight,
                                sizes: sizes,
                                gutterSize: 8,
                                snapOffset: 0,
                                cursor: 'row-resize',
                                onDrag: tracyConsole.resizeAce,
                                onDragEnd: function() {
                                    localStorage.setItem('tracyConsoleSplitSizes', JSON.stringify(split.getSizes()));
                                    tracyConsole.tce.focus();
                                }
                            });

                            // hack to remove extra gutter in Tracy window mode
                            var elements = document.getElementsByClassName('gutter');
                            while(elements.length > 1) {
                                elements[0].parentNode.removeChild(elements[0]);
                            }
                            tracyConsole.resizeAce();
                        });

                        // checks for changes to Console panel class which indicates focus so we can focus cursor in editor
                        tracyConsole.observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                if(mutation.attributeName === "class") {
                                    //tracyConsole.tce.focus();
                                    tracyConsole.resizeAce();
                                }
                            });
                        });
                        tracyConsole.observer.observe(document.getElementById("tracy-debug-panel-ConsolePanel"), {
                            attributes: true
                        });

                        window.onresize = function(event) {
                            tracyConsole.resizeAce();
                        };

                        // build snippet list and populate local storage version from database
                        tracyConsole.modifySnippetList(null, $snippets, false);
                        localStorage.setItem("tracyConsoleSnippets", JSON.stringify($snippets));

                        // history buttons
                        if(tracyConsole.historyCount == tracyConsole.historyItem || !tracyConsole.historyItem || !tracyConsole.historyCount) {
                            tracyConsole.disableButton("historyForward");
                        }
                        if(!tracyConsole.historyItem || tracyConsole.historyItem == 1 || tracyConsole.historyCount < 2) {
                            tracyConsole.disableButton("historyBack");
                        }

                        // various keyboard shortcuts
                        document.getElementById("tracyConsoleCode").addEventListener("keydown", function(e) {
                            if(((e.keyCode==10||e.charCode==10)||(e.keyCode==13||e.charCode==13)) && (e.metaKey || e.ctrlKey || e.altKey)) {
                                if(e.altKey) tracyConsole.clearResults();
                                tracyConsole.processTracyCode();
                            }
                            if((e.keyCode==38||e.charCode==38) && e.ctrlKey && e.metaKey) {
                                tracyConsole.loadHistory('back');
                            }
                            if((e.keyCode==40||e.charCode==40) && e.ctrlKey && e.metaKey) {
                                tracyConsole.loadHistory('forward');
                            }
                        });

                        // activate save button when typing new snippet name
                        document.getElementById("tracySnippetName").onkeyup = function() {
                            tracyConsole.toggleSnippetButton();
                        };

                        // set Console panel height
                        var consolePanelHeight = localStorage.getItem('tracyConsolePanelHeight');
                        document.getElementById('tracy-debug-panel-ConsolePanel').style.height = consolePanelHeight ? consolePanelHeight : '50%';

                    });

                }
            });

        </script>
HTML;



        $out .= '<h1>' . $this->icon . ' Console </h1><span class="tracy-icons"><span class="resizeIcons"><a href="javascript:void(0)" title="small" rel="min" onclick="tracyConsole.resizePanel(\'s\')">▼</a> <a href="javascript:void(0)" title="large" rel="max" onclick="tracyConsole.resizePanel(\'l\')">▲</a></span></span>
        <div class="tracy-inner">

            <fieldset>
                <legend>CTRL/CMD+Enter to Run&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ALT/OPT+Enter to Clear & Run</legend>';
        if($this->wire('page')->template != "admin") {
            $out .= '<p><label><input type="checkbox" id="accessTemplateVars" /> Allow access to custom variables and functions defined in this page\'s template file and all other included files.</label></p>';
        }

        $out .= '
                <div style="padding:10px 0">
                    <input title="Run code" type="submit" id="runCode" onclick="tracyConsole.processTracyCode()" value="Run" />&nbsp;
                    <input title="Go back (CTRL+CMD+&#8593;)" id="historyBack" type="submit" onclick="tracyConsole.loadHistory(\'back\')" value="&#11013;" />&nbsp;
                    <input title="Go forward (CTRL+CMD+&#8595;)" class="arrowRight" id="historyForward" type="submit" onclick="tracyConsole.loadHistory(\'forward\')" value="&#11013;" />
                    <input title="Clear results" type="submit" id="clearResults" onclick="tracyConsole.clearResults()" value="&#10006; Clear results" />
                    <span style="float:right; position:relative; right: 270px">
                        <label title="Don\'t Run on Page Load" style="display:inline !important"><input type="radio" name="includeCode" onclick="tracyConsole.tracyIncludeCode(\'off\')" value="off" '.(!$this->tracyIncludeCode || $this->tracyIncludeCode['when'] === 'off' ? ' checked' : '').' /> off</label>&nbsp;
                        <label title="Run on init" style="display:inline !important"><input type="radio" name="includeCode" onclick="tracyConsole.tracyIncludeCode(\'init\')" value="init" '.($this->tracyIncludeCode['when'] === 'init' ? ' checked' : '').' /> init</label>&nbsp;
                        <label title="Run on ready" style="display:inline !important"><input type="radio" name="includeCode" onclick="tracyConsole.tracyIncludeCode(\'ready\')" value="ready" '.($this->tracyIncludeCode['when'] === 'ready' ? ' checked' : '').' /> ready</label>&nbsp;
                        <label title="Run on finished" style="display:inline !important"><input type="radio" name="includeCode" onclick="tracyConsole.tracyIncludeCode(\'finished\')" value="finished" '.($this->tracyIncludeCode['when'] === 'finished' ? ' checked' : '').' /> finished</label>&nbsp;
                    </span>
                    <span id="tracyConsoleStatus" style="padding: 10px"></span>
                </div>

                <div id="tracyConsoleContainer" class="split" style="float: left; display: block;">
                    <div id="tracyConsoleCode" class="split" style="min-height:23px; background:#1D1F21">
                        <div id="tracyConsoleEditor"></div>
                    </div>
                    <div id="tracyConsoleResult" class="split" style="overflow:auto; border:1px solid #D2D2D2; padding:10px; min-height:23px">';
                        if($this->wire('input')->cookie->tracyCodeError) {
                            $out .= $this->wire('input')->cookie->tracyCodeError.
                            '<div style="border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
                        }
                    $out .= '
                    </div>
                </div>

                <div id="tracySnippetsContainer" style="float: left; margin: 0 10px; width: 240px; margin-top: -'.($this->wire('page')->template != "admin" ? '60' : '23').'px;">
                    <div style="padding-bottom:5px">
                        Sort: <a href="#" onclick="tracyConsole.sortList(\'alphabetical\')">alphabetical</a>&nbsp;|&nbsp;<a href="#" onclick="tracyConsole.sortList(\'chronological\')">chronological</a>
                    </div>
                    <div style="position: relative; width:295px !important;">
                        <input type="text" id="tracySnippetName" placeholder="Snippet name..." />
                        <input id="saveSnippet" type="submit" onclick="tracyConsole.saveSnippet()" value="&#128190;" title="Save snippet" />
                    </div>
                    <div id="tracySnippets"></div>
                </div>
            </fieldset>

        ';
            $out .= \TracyDebugger::generatedTimeSize('console', \Tracy\Debugger::timer('console'), strlen($out)) .
        '</div>';

        return parent::loadResources() . \TracyDebugger::minify($out);

    }

}
