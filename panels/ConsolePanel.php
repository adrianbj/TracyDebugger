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

        $this->icon = <<< HTML
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="13.7px" viewBox="439 504.1 16 13.7" enable-background="new 439 504.1 16 13.7" xml:space="preserve">
            <path fill="{$this->iconColor}" d="M453.9,504.1h-13.7c-0.6,0-1.1,0.5-1.1,1.1v11.4c0,0.6,0.5,1.1,1.1,1.1h13.7c0.6,0,1.1-0.5,1.1-1.1v-11.4
                C455,504.7,454.5,504.1,453.9,504.1z M441.3,512.1l2.3-2.3l-2.3-2.3l1.1-1.1l3.4,3.4l-3.4,3.4L441.3,512.1z M450.4,513.3h-4.6v-1.1
                h4.6V513.3z"/>
            </svg>
HTML;

        return '
        <span title="Console">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Console' : '') . '
        </span>';
    }

    public function getPanel() {

        $tracyModuleUrl = $this->wire("config")->urls->TracyDebugger;

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

        $out = <<< HTML
        <script>

            var tce; //setup Tracy Console Editor
            var tracyModuleUrl = "$tracyModuleUrl";
            var maxHistoryItems = 25;
            var storedHistoryItem = localStorage.getItem("tracyConsoleHistoryItem");
            var historyItem = storedHistoryItem ? storedHistoryItem : 1;
            var historyCount = localStorage.getItem("tracyConsoleHistoryCount");
            var loadedSnippetCode;
            var desc = false;
            if(historyCount == historyItem || !historyItem || !historyCount) {
                disableButton("historyForward");
            }
            if(!historyItem || historyItem == 1 || historyCount < 2) {
                disableButton("historyBack");
            }

            function disableButton(button) {
                var button = document.getElementById(button);
                button.setAttribute("disabled", true);
                button.classList.add("disabledButton");
            }

            function enableButton(button) {
                var button = document.getElementById(button);
                button.removeAttribute("disabled");
                button.classList.remove("disabledButton");
            }

            // javascript dynamic loader from https://gist.github.com/hagenburger/500716
            // using dynamic loading because an exception error or "exit" in template file
            // was preventing these scripts from being loaded which broke the editor
            // if this has any problems, there is an alternate version to try here:
            // https://www.nczonline.net/blog/2009/07/28/the-best-way-to-load-external-javascript/
            var JavaScript = {
                load: function(src, callback) {
                    var script = document.createElement("script"),
                            loaded;
                    script.setAttribute("src", src);
                    if (callback) {
                        script.onreadystatechange = script.onload = function() {
                            if (!loaded) {
                                callback();
                            }
                            loaded = true;
                        };
                    }
                    document.getElementsByTagName("head")[0].appendChild(script);
                }
            };

            document.getElementById("tracyConsoleCode").addEventListener("keydown", function(e) {
                if(((e.keyCode==10||e.charCode==10)||(e.keyCode==13||e.charCode==13)) && (e.metaKey || e.ctrlKey || e.altKey)) {
                    if(e.altKey) clearResults();
                    processTracyCode();
                }
                if((e.keyCode==38||e.charCode==38) && e.ctrlKey && e.metaKey) {
                    loadHistory('back');
                }
                if((e.keyCode==40||e.charCode==40) && e.ctrlKey && e.metaKey) {
                    loadHistory('forward');
                }
            });

            function tryParseJSON(str){
                if(!isNaN(str)) return str; // JSON.parse on numbers not being parsed and returning false
                try {
                    var o = JSON.parse(str);

                    // Handle non-exception-throwing cases:
                    // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
                    // but... JSON.parse(null) returns "null", and typeof null === "object",
                    // so we must check for that, too.
                    if (o && typeof o === "object" && o !== null) {
                        // when using clear code, getting a "Compiled File" error that we do not care about
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
            };

            function getQueryVariable(variable, url) {
                var vars = url.split('&');
                for (var i = 0; i < vars.length; i++) {
                    var pair = vars[i].split('=');
                    if (decodeURIComponent(pair[0]) == variable) {
                        return decodeURIComponent(pair[1]);
                    }
                }
            }

            function clearResults() {
                document.getElementById("tracyConsoleResult").innerHTML = "";
                document.getElementById("tracyConsoleStatus").innerHTML = "";
                tce.focus();
            }

            function processTracyCode() {
                var code = tce.getSelectedText() || tce.getValue();
                document.getElementById("tracyConsoleStatus").innerHTML = "<i class='fa fa-spinner fa-spin'></i> Processing";
                callPhp(code);
                saveHistory();
                disableButton("historyForward");
                if(historyCount > 1) enableButton("historyBack");
                tce.focus();
            }

            function tracyIncludeCode(when) {
                params = {when: when, pid: "{$p->id}"};
                if(when === 'off') {
                    document.cookie = "tracyIncludeCode=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                }
                else {
                    var expires = new Date();
                    expires.setMinutes(expires.getMinutes() + 5);
                    document.cookie = "tracyIncludeCode="+JSON.stringify(params)+";expires="+expires.toGMTString()+";path=/";
                }
            }

            function callPhp(code) {

                var xmlhttp;

                if (window.XMLHttpRequest) {
                    // code for IE7+, Firefox, Chrome, Opera, Safari
                    xmlhttp = new XMLHttpRequest();
                } else {
                    // code for IE6, IE5
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }

                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
                        document.getElementById("tracyConsoleStatus").innerHTML = "Completed!";
                        if(xmlhttp.status == 200){
                            document.getElementById("tracyConsoleResult").innerHTML += tryParseJSON(xmlhttp.responseText);
                            // scroll to bottom of results
                            var objDiv = document.getElementById("tracyConsoleResult");
                            objDiv.scrollTop = objDiv.scrollHeight;
                        }
                        else {
                            var tracyBsError = new DOMParser().parseFromString(xmlhttp.responseText, "text/html");
                            var tracyBsErrorDiv = tracyBsError.getElementById("tracy-bs-error");
                            var tracyBsErrorType = tracyBsErrorDiv.getElementsByTagName('p')[0].innerHTML;
                            var tracyBsErrorText = tracyBsErrorDiv.getElementsByTagName('h1')[0].getElementsByTagName('span')[0].innerHTML;
                            var tracyBsErrorLineNum = getQueryVariable('line', tracyBsError.querySelector('[data-tracy-href]').getAttribute("data-tracy-href")) - 1;
                            var tracyBsErrorStr = "<br />" + tracyBsErrorType + ": " + tracyBsErrorText + " on line: " + tracyBsErrorLineNum + "<br />";
                            document.getElementById("tracyConsoleResult").innerHTML = xmlhttp.status+": " + xmlhttp.statusText + tracyBsErrorStr + "<div style='border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;'></div>";

                            var expires = new Date();
                            expires.setMinutes(expires.getMinutes() + (10 * 365 * 24 * 60));
                            document.cookie = "tracyCodeError="+xmlhttp.status+": " + xmlhttp.statusText + tracyBsErrorStr + ";expires="+expires.toGMTString()+";path=/";
                        }
                        xmlhttp.getAllResponseHeaders();
                    }
                }

HTML;

                if ($this->wire('page')->template != "admin") {
                    $out .= <<< HTML
                        var accessTemplateVars = document.getElementById("accessTemplateVars").checked;
HTML;
                } else {
                    $out .= <<< HTML
                        var accessTemplateVars = "false";
HTML;
                }

                $out .= <<< HTML
                xmlhttp.open("POST", "./", true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xmlhttp.send("tracyConsole=1&accessTemplateVars="+accessTemplateVars+"&pid={$p->id}&fid={$fid}&tid={$tid}&mid={$mid}&code="+encodeURIComponent(code));
            }
        </script>

HTML;

        $code = '';
        $file = $this->wire('config')->paths->cache . 'TracyDebugger/consoleCode.php';
        if (file_exists($file)) {
            $code = file_get_contents($file);
            $code = implode("\n", array_slice(explode("\n", $code), 1));
            $code = json_encode($code); // json_encode to convert line breaks to \n - needed by setValue()
        }

        $out .= '<h1>' . $this->icon . ' Console</h1>
        <div class="tracy-inner">
            <fieldset>
                <legend>Use CTRL/CMD+Enter to Run, or ALT/OPT+Enter to Clear & Run.</legend>';
        if ($this->wire('page')->template != "admin") {
            $out .= '<p><label><input type="checkbox" id="accessTemplateVars" /> Allow access to custom variables and functions defined in this page\'s template file and all other included files.</label></p>';
        }

        $out .= '
                <br />
                <div id="tracyConsoleContainer">
                    <div id="tracyConsoleCode" style="visibility:hidden; height:100px"></div>
                    <div style="padding:10px 0">
                        <input title="Run code" type="submit" id="runCode" onclick="processTracyCode()" value="Run" />&nbsp;
                        <input title="Go back (CTRL+CMD+&#8593;)" id="historyBack" type="submit" onclick="loadHistory(\'back\')" value="&#11013;" />&nbsp;
                        <input title="Go forward (CTRL+CMD+&#8595;)" class="arrowRight" id="historyForward" type="submit" onclick="loadHistory(\'forward\')" value="&#11013;" />
                        <input title="Clear results" type="submit" id="clearResults" onclick="clearResults()" value="&#10006; Clear results" />
                        <span style="float:right">
                            <label title="Don\'t Run on Page Load" style="display:inline !important"><input type="radio" name="includeCode" onclick="tracyIncludeCode(\'off\')" value="off" '.(!$this->tracyIncludeCode || $this->tracyIncludeCode['when'] === 'off' ? ' checked' : '').' /> off</label>&nbsp;
                            <label title="Run on init" style="display:inline !important"><input type="radio" name="includeCode" onclick="tracyIncludeCode(\'init\')" value="init" '.($this->tracyIncludeCode['when'] === 'init' ? ' checked' : '').' /> init</label>&nbsp;
                            <label title="Run on ready" style="display:inline !important"><input type="radio" name="includeCode" onclick="tracyIncludeCode(\'ready\')" value="ready" '.($this->tracyIncludeCode['when'] === 'ready' ? ' checked' : '').' /> ready</label>&nbsp;
                            <label title="Run on finished" style="display:inline !important"><input type="radio" name="includeCode" onclick="tracyIncludeCode(\'finished\')" value="finished" '.($this->tracyIncludeCode['when'] === 'finished' ? ' checked' : '').' /> finished</label>&nbsp;
                        </span>
                        <span id="tracyConsoleStatus" style="padding: 10px"></span>
                    </div>
                    <div id="tracyConsoleResult" style="border: 1px solid #D2D2D2; padding: 10px;max-height: 300px; overflow:auto">';
                        if($this->wire('input')->cookie->tracyCodeError) {
                            $out .= $this->wire('input')->cookie->tracyCodeError.
                            '<div style="border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
                        }
                    $out .= '
                    </div>
                </div>
                <div style="float: left; margin-left: 10px; width: 280px; margin-top: -'.($this->wire('page')->template != "admin" ? '60' : '23').'px;">
                    <div style="padding-bottom:5px">
                        Sort: <a href="#" onclick="sortList(\'alphabetical\')">alphabetical</a>&nbsp;|&nbsp;<a href="#" onclick="sortList(\'chronological\')">chronological</a>
                    </div>
                     <div style="position: relative; width:290px !important;">
                        <input type="text" id="tracySnippetName" placeholder="Snippet name..." />
                        <input id="saveSnippet" type="submit" onclick="saveSnippet()" value="&#128190;" title="Save snippet" />
                    </div>
                    <div id="tracySnippets"></div>
                </div>
            </fieldset>
        </div>';

        // get snippets from DB to populate local storage
        $snippets = \TracyDebugger::getDataValue('snippets');
        if(!$snippets) $snippets = json_encode(array());

        $out .= <<< HTML
        <script>

            function getSnippet(name) {
                var tracyConsoleSnippets = getAllSnippets();
                for (var key in tracyConsoleSnippets) {
                    if (!tracyConsoleSnippets.hasOwnProperty(key)) continue;
                    var obj = tracyConsoleSnippets[key];
                    if(obj.name === name) {
                        return obj.code;
                    }
                }
            }

            function getAllSnippets() {
                return JSON.parse(localStorage.getItem("tracyConsoleSnippets"));
            }

            function modifyConsoleSnippets(tracySnippetName, code) {
                var tracyConsoleSnippets = [];
                if(code) {
                    tracyConsoleSnippets.push({name: tracySnippetName, code: code, modified: Date.now()});
                }
                else {
                    if(!confirm("Are you sure you want to delete snippet \"" + tracySnippetName + "\"?")) return false;
                }
                var existingSnippets = getAllSnippets();
                for (var key in existingSnippets) {
                    if (!existingSnippets.hasOwnProperty(key)) continue;
                    var obj = existingSnippets[key];
                    if(obj.name !== tracySnippetName) tracyConsoleSnippets.push(obj);
                }
                var deleteSnippet = !code;
                setAllSnippets(tracySnippetName, tracyConsoleSnippets, deleteSnippet);
            }

            function modifySnippetList(name, existingSnippets, deleteSnippet) {
                if(!existingSnippets) var existingSnippets = getAllSnippets();
                var snippetList = "<ul id='snippetsList'>";
                for (var key in existingSnippets) {
                    if (!existingSnippets.hasOwnProperty(key)) continue;
                    var obj = existingSnippets[key];
                    if(deleteSnippet === true && obj.name === name) continue;
                    snippetList += "<li id='"+makeIdFromTitle(obj.name)+"' data-modified='"+obj.modified+"'><span class='trashIcon' title='Delete this snippet' onclick='modifyConsoleSnippets(\""+obj.name+"\", null)'>&#10006;</span><span style='color: #125EAE; cursor: pointer' onclick='loadSnippet(\""+obj.name+"\");setActiveSnippet(this);'>" + obj.name + "</span></li>";
                }
                snippetList += "</ul>";
                document.getElementById("tracySnippets").innerHTML = snippetList;
            }

            function compareAlphabetical(a1, a2) {
                var t1 = a1.innerText,
                    t2 = a2.innerText;
                return t1 > t2 ? 1 : (t1 < t2 ? -1 : 0);
            }

            function compareChronological(a1, a2) {
                var t1 = a1.dataset.modified,
                    t2 = a2.dataset.modified;
                return t1 > t2 ? 1 : (t1 < t2 ? -1 : 0);
            }

            function sortUnorderedList(ul, sortDescending, type) {
                if (typeof ul == "string") {
                    ul = document.getElementById(ul);
                }

                var lis = ul.getElementsByTagName("LI");
                var vals = [];

                for (var i = 0, l = lis.length; i < l; i++) {
                    vals.push(lis[i]);
                }

                if(type === 'alphabetical') {
                    vals.sort(compareAlphabetical);
                }
                else {
                    vals.sort(compareChronological);
                }

                if (sortDescending) {
                    vals.reverse();
                }

                ul.innerHTML = '';
                for (var i = 0, l = vals.length; i < l; i++) {
                    ul.appendChild(vals[i]);
                }
            }

            function sortList(type) {
                sortUnorderedList("snippetsList", desc, type);
                desc = !desc;
                return false;
            }

            function setAllSnippets(tracySnippetName, tracyConsoleSnippets, deleteSnippet) {
                // push to local storage for access during current page instance
                localStorage.setItem("tracyConsoleSnippets", JSON.stringify(tracyConsoleSnippets));

                // save to DB for access on initial page load
                var xmlhttp;

                if (window.XMLHttpRequest) {
                    // code for IE7+, Firefox, Chrome, Opera, Safari
                    xmlhttp = new XMLHttpRequest();
                } else {
                    // code for IE6, IE5
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }

                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
                        if(xmlhttp.status == 200){
                            modifySnippetList(tracySnippetName, tracyConsoleSnippets, deleteSnippet);
                            setActiveSnippet(document.getElementById(makeIdFromTitle(tracySnippetName)));
                        }
                        else {
                            //
                        }
                        xmlhttp.getAllResponseHeaders();
                    }
                }

                xmlhttp.open("POST", "./", true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xmlhttp.send("tracysnippets=1&snippets="+encodeURIComponent(JSON.stringify(tracyConsoleSnippets)));
            }


            function getHistoryItem(id) {
                var tracyConsoleHistory = JSON.parse(localStorage.getItem("tracyConsoleHistory"));
                for (var key in tracyConsoleHistory) {
                    if (!tracyConsoleHistory.hasOwnProperty(key)) continue;
                    var obj = tracyConsoleHistory[key];
                    if(obj.id === id) return obj;
                }
            }

            function loadHistory(direction) {

                var noItem = false;

                if(direction === 'back' && historyItem > 1) {
                    var id = --historyItem;
                }
                else if(direction === 'forward' && historyCount > historyItem) {
                    var id = ++historyItem;
                }
                else {
                    var id = historyItem;
                    noItem = true;
                }

                if(id == 1) {
                    disableButton("historyBack");
                }
                else {
                    enableButton("historyBack");
                }
                if(historyCount == id) {
                    disableButton("historyForward");
                }
                else {
                    enableButton("historyForward");
                }

                if(noItem) return;

                localStorage.setItem("tracyConsoleHistoryItem", historyItem);

                setEditorState(getHistoryItem(id));

                tce.focus();
            }

            function loadSnippet(name) {
                loadedSnippetCode = getSnippet(name);
                tce.setValue(loadedSnippetCode);
                document.getElementById("tracySnippetName").value = name;
                tce.gotoLine(0,0);
                tce.focus();
                ++historyItem;
            }

            function saveHistory() {
                var code = tce.getValue();
                var selections = tce.selection.toJSON();
                if(code) {
                    var existingItems = JSON.parse(localStorage.getItem("tracyConsoleHistory"));
                    var numItems = 0;
                    if(existingItems) {
                        for (var k in existingItems) if (existingItems.hasOwnProperty(k)) ++numItems;
                    }
                    var tracyConsoleHistory = [];
                    var count = 0;
                    var id = 1;
                    // add existing items with revised "id"
                    for (var key in existingItems) {
                        if (!existingItems.hasOwnProperty(key)) continue;
                        ++count;
                        if(numItems === maxHistoryItems && count === 1) continue;
                        tracyConsoleHistory.push({id: id, code: existingItems[key].code, selections: existingItems[key].selections, scrollTop: existingItems[key].scrollTop, scrollLeft: existingItems[key].scrollLeft});
                        ++id;
                    }
                    // add new item
                    tracyConsoleHistory.push({id: id, code: code, selections: selections, scrollTop: tce.session.getScrollTop(), scrollLeft: tce.session.getScrollLeft()});

                    localStorage.setItem("tracyConsoleHistoryCount", historyCount = id);
                    localStorage.setItem("tracyConsoleHistoryItem", historyItem = id);
                    localStorage.setItem("tracyConsoleHistory", JSON.stringify(tracyConsoleHistory));
                }
            }

            function makeIdFromTitle(title) {
                return title.replace(/^[^a-z]+|[^\w:.-]+/gi, "");
            }

            function saveSnippet() {
                var tracySnippetName = document.getElementById("tracySnippetName").value;
                if(tracySnippetName != "") {
                    modifyConsoleSnippets(tracySnippetName, tce.getValue());
                    disableButton("saveSnippet");
                    tce.focus();
                }
                else {
                    alert('You must enter a name to save a snippet!');
                    document.getElementById("tracySnippetName").focus();
                }
            }

            function setActiveSnippet(item) {
	            if(document.querySelector(".activeSnippet")) {
                	document.querySelector(".activeSnippet").classList.remove("activeSnippet");
	            }
                item.classList.add("activeSnippet");
            }

            function saveToLocalStorage(name) {
                var tracyHistoryItem = {code: tce.getValue(), selections: tce.selection.toJSON(), scrollTop: tce.session.getScrollTop(), scrollLeft: tce.session.getScrollLeft()};
                localStorage.setItem(name, JSON.stringify(tracyHistoryItem));
            }

            function setEditorState(data) {
                tce.setValue(data.code);
                tce.selection.fromJSON(data.selections);
                tce.session.setScrollTop(data.scrollTop);
                tce.session.setScrollLeft(data.scrollLeft);
            }

            JavaScript.load(tracyModuleUrl + "ace-editor/ace.js", function() {
                if(typeof ace !== "undefined") {
                    tce = ace.edit("tracyConsoleCode");
                    tce.container.style.lineHeight = 1.8;
                    tce.setFontSize(13);
                    tce.setShowPrintMargin(false);
                    tce.\$blockScrolling = Infinity; //fix deprecation warning

                    tce.on("beforeEndOperation", function(e) {
                        saveToLocalStorage('tracyConsole');
                        var tracySnippetName = document.getElementById("tracySnippetName").value;
                        if(typeof loadedSnippetCode === 'undefined' || (tracySnippetName != '' && tce.getValue() != loadedSnippetCode)) {
                            enableButton("saveSnippet");
                        }
                        else {
                            disableButton("saveSnippet");
                        }
                    });

                    tce.session.on("changeScrollTop", function() {
                        saveToLocalStorage('tracyConsole');
                    });
                    tce.session.on("changeScrollLeft", function() {
                        saveToLocalStorage('tracyConsole');
                    });

                    // set theme
                    JavaScript.load(tracyModuleUrl + "ace-editor/theme-tomorrow_night.js", function() {
                        tce.setTheme("ace/theme/tomorrow_night");
                    });

                    // set mode to php
                    JavaScript.load(tracyModuleUrl + "ace-editor/mode-php.js", function() {
                        tce.session.setMode({path:"ace/mode/php", inline:true});
                    });

                    // set autocomplete and other options
                    JavaScript.load(tracyModuleUrl + "ace-editor/ext-language_tools.js", function() {
                        var ml = Math.round(Math.max(document.documentElement.clientHeight, window.innerHeight || 0) / 60);
                        tce.setOptions({
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true,
                            minLines: 5,
                            maxLines: ml
                        });
                        document.getElementById("tracyConsoleCode").style.visibility = "visible";
                        if(!!localStorage.getItem("tracyConsole") && localStorage.getItem("tracyConsole") !== "null" && localStorage.getItem("tracyConsole") !== "undefined") {
                            try {
                                setEditorState(JSON.parse(localStorage.getItem("tracyConsole")));
                            }
                            catch(e) {
                                // for users upgrading from old version of Console panel that didn't store selection & scroll info
                                tce.setValue(localStorage.getItem("tracyConsole"));
                            }
                        }
                        else {
                            tce.setValue({$code});
                            count = tce.session.getLength();
                            tce.gotoLine(count, tce.session.getLine(count-1).length);
                        }

                        tce.focus();

                        // checks for changes to Console panel class which indicates focus so we can focus cursor in editor
                        var observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                if(mutation.attributeName === "class") {
                                    tce.focus();
                                }
                            });
                        });
                        observer.observe(document.getElementById("tracy-debug-panel-ConsolePanel"), {
                            attributes: true
                        });


                        function resizeAce() {
                            var ml = Math.round(Math.max(document.documentElement.clientHeight, window.innerHeight || 0) / 60);
                            tce.setOptions({
                                enableBasicAutocompletion: true,
                                enableLiveAutocompletion: true,
                                minLines: 5,
                                maxLines: ml
                            });
                        }

                        resizeAce();

                        window.onresize = function(event) {
                            resizeAce();
                            tce.focus();
                        };
                        // build snippet list and populate local storage version from database
                        modifySnippetList(null, $snippets, false);
                        localStorage.setItem("tracyConsoleSnippets", JSON.stringify($snippets));
                    });

                }
            });
        </script>
HTML;

        $out .= \TracyDebugger::generatedTimeSize('console', \Tracy\Debugger::timer('console'), strlen($out));

        return parent::loadResources() . $out;
    }

}
