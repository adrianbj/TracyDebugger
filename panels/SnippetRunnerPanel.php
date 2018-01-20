<?php

/**
 * Snippet Runner panel
 */
class SnippetRunnerPanel extends BasePanel {

    protected $icon;

    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('snippetRunner');

        $this->icon = <<< HTML
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="16px" viewBox="298 391.7 16 8.7" enable-background="new 298 391.7 16 8.7" xml:space="preserve">
            <g>
                <path fill="#444444" d="M302.9,399.4l-4.9-2.7v-1.5l4.9-2.7v1.7l-3.4,1.6l3.4,1.7C302.9,397.7,302.9,399.4,302.9,399.4z"/>
                <path fill="#444444" d="M303.7,400.3l3.2-8.7h1l-3.3,8.7H303.7z"/>
                <path fill="#444444" d="M309.1,399.4v-1.7l3.4-1.7l-3.4-1.7v-1.7l4.9,2.7v1.5L309.1,399.4z"/>
            </g>
            </svg>
HTML;

        return '
        <span title="Snippet Runner">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Snippet Runner' : '') . '
        </span>';
    }

    public function getPanel() {

        $tracyModuleUrl = $this->wire("config")->urls->TracyDebugger;

        // store various $input properties so they are available to the snippets
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

        $out = '<script>' . file_get_contents($this->wire("config")->paths->TracyDebugger . 'scripts/js-loader.js') . '</script>';
        $out .= '<script>' . file_get_contents($this->wire("config")->paths->TracyDebugger . 'scripts/get-query-variable.js') . '</script>';

        // determine whether 'l' or 'line' is used for line number with current editor
        parse_str(\Tracy\Debugger::$editor, $vars);
        $lineVar = array_key_exists('l', $vars) ? 'l' : 'line';

        $out .= <<< HTML
        <script>

            var tracySnippetRunner = {

                loadedSnippetFile: null,
                desc: false,

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
                    catch (e) {
                        return str;
                    }
                    return false;
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
                    this.sortUnorderedList("runnerSnippetsList", this.desc, type);
                    this.desc = !this.desc;
                    return false;
                },

                selectRunnerSnippet: function(name, filename) {
                    this.loadedSnippetFile = filename;
                    document.getElementById("tracyRunnerSnippetName").innerHTML = '<strong>Selected snippet:</strong> ' + name;
                },

                makeIdFromTitle: function(title) {
                    return title.replace(/^[^a-z]+|[^\w:.-]+/gi, "");
                },

                setActiveRunnerSnippet: function(item) {
                    if(document.querySelector(".activeSnippet")) {
                        document.querySelector(".activeSnippet").classList.remove("activeSnippet");
                    }
                    item.classList.add("activeSnippet");
                },

                clearSnippetRunnerResults: function() {
                    document.getElementById("tracySnippetRunnerResult").innerHTML = "";
                    document.getElementById("tracySnippetRunnerStatus").innerHTML = "";
                },

                processTracySnippetRunnerCode: function() {
                    file = typeof this.loadedSnippetFile === 'undefined' ? '' : this.loadedSnippetFile;
                    document.getElementById("tracySnippetRunnerStatus").innerHTML = "<i class='fa fa-spinner fa-spin'></i> Processing";
                    this.callSnippetRunnerPhp(file);
                    document.getElementById('runSnippetRunnerCode').blur();
                },

                callSnippetRunnerPhp: function(file) {
                    var xmlhttp;
                    xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                            document.getElementById("tracySnippetRunnerStatus").innerHTML = "Completed!";
                            if(xmlhttp.status == 200) {
                                document.getElementById("tracySnippetRunnerResult").innerHTML += tracySnippetRunner.tryParseJSON(xmlhttp.responseText);
                                // scroll to bottom of results
                                var objDiv = document.getElementById("tracySnippetRunnerResult");
                                objDiv.scrollTop = objDiv.scrollHeight;
                            }
                            else {
                                var tracyBsError = new DOMParser().parseFromString(xmlhttp.responseText, "text/html");
                                var tracyBsErrorDiv = tracyBsError.getElementById("tracy-bs-error");
                                var tracyBsErrorType = tracyBsErrorDiv.getElementsByTagName('p')[0].innerHTML;
                                var tracyBsErrorText = tracyBsErrorDiv.getElementsByTagName('h1')[0].getElementsByTagName('span')[0].innerHTML;
                                var tracyBsErrorLineNum = tracyDebugger.getQueryVariable('{$lineVar}', tracyBsError.querySelector('[data-tracy-href]').getAttribute("data-tracy-href")) - 1;
                                var tracyBsErrorStr = "<br />" + tracyBsErrorType + ": " + tracyBsErrorText + " on line: " + tracyBsErrorLineNum + "<br />";
                                document.getElementById("tracySnippetRunnerResult").innerHTML = xmlhttp.status+": " + xmlhttp.statusText + tracyBsErrorStr + "<div style='border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;'></div>";
                            }
                            xmlhttp.getAllResponseHeaders();
                        }
                    }

HTML;

                    if($this->wire('page')->template != "admin") {
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
                    xmlhttp.send("tracySnippetRunner=1&accessTemplateVars="+accessTemplateVars+"&pid={$p->id}&fid={$fid}&tid={$tid}&mid={$mid}&file="+file);
                },

            };

            document.addEventListener("keydown", function(e) {
                if(document.getElementById("tracy-debug-panel-SnippetRunnerPanel").classList.contains("tracy-focused") &&
                    !document.getElementById("tracy-debug-panel-ConsolePanel").classList.contains("tracy-focused") &&
                    !document.activeElement.classList.contains('ace_text-input')
                ) {
                    if(((e.keyCode==10||e.charCode==10)||(e.keyCode==13||e.charCode==13)) && (e.metaKey || e.ctrlKey || e.altKey)) {
                        e.preventDefault();
                        if(e.altKey) tracySnippetRunner.clearSnippetRunnerResults();
                        tracySnippetRunner.processTracySnippetRunnerCode();
                    }
                }
            });

        </script>

HTML;

        $out .= '<h1>' . $this->icon . ' Snippet Runner</h1>
        <div class="tracy-inner">
            <fieldset>
                <legend>Select a snippet, then use CTRL/CMD+Enter to Run, or ALT/OPT+Enter to Clear & Run.</legend>';
        if($this->wire('page')->template != "admin") {
            $out .= '<p><label><input type="checkbox" id="accessTemplateVars" /> Allow access to custom variables and functions defined in this page\'s template file and all other included files.</label></p>';
        }
        $out .= '
                <br />
                <div id="tracySnippetRunnerContainer">
                    <div id="tracyRunnerSnippetName" style="font-size: 13px"></div>
                    <div style="padding:10px 0">
                        <input title="Run code" type="submit" id="runSnippetRunnerCode" onclick="tracySnippetRunner.processTracySnippetRunnerCode()" value="Run" />&nbsp;
                        <input title="Clear results" type="submit" id="clearSnippetRunnerResults" onclick="tracySnippetRunner.clearSnippetRunnerResults()" value="&#10006; Clear results" />
                        <span id="tracySnippetRunnerStatus" style="padding: 10px"></span>
                    </div>
                    <div id="tracySnippetRunnerResult" style="border: 1px solid #D2D2D2; padding: 10px;max-height: 300px; overflow:auto"></div>
                </div>
                <div style="float: left; margin-left: 10px; width: 250px; margin-top: -'.($this->wire('page')->template != "admin" ? '60' : '23').'px;">
                    <div>
                        Sort: <a href="#" onclick="tracySnippetRunner.sortList(\'alphabetical\')">alphabetical</a>&nbsp;|&nbsp;<a href="#" onclick="tracySnippetRunner.sortList(\'chronological\')">chronological</a>
                    </div>
                    <div id="tracyRunnerSnippets" style="margin-top: 5px; padding:8px; min-height: 115px; max-height: 187px; overflow:auto"></div>
                </div>
            </fieldset>
        </div>';

        // get snippets from filesystem
        $snippets = array();
        if(method_exists($this->wire('files'), 'find')) {
            $snippetFiles = $this->wire('files')->find($this->wire('config')->paths->site.\TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/');
        }
        // fallback for older versions of PW without the $files->find() method
        else {
            $snippetFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->wire('config')->paths->site.\TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/'));
            $snippetFiles->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
        }
        $i=0;
        foreach($snippetFiles as $snippetFile) {
            $snippetFileName = method_exists($this->wire('files'), 'find') ? $snippetFile : $snippetFile->getPathname();
            $snippets[$i]['name'] = pathinfo($snippetFileName, PATHINFO_BASENAME);
            $snippets[$i]['filename'] = $snippetFileName;
            $snippets[$i]['modified'] = filemtime($snippetFileName);
            $i++;
        }

        $snippets = json_encode($snippets);
        if(!$snippets) $snippets = json_encode(array());

        $out .= <<< HTML
        <script>

            var snippets = {$snippets};
            var snippetList = "<ul id='runnerSnippetsList'>";
            for(var key in snippets) {
                if(!snippets.hasOwnProperty(key)) continue;
                var obj = snippets[key];
                snippetList += "<li id='"+tracySnippetRunner.makeIdFromTitle(obj.name)+"' data-modified='"+obj.modified+"'><span style='color: #125EAE; cursor: pointer' onclick='tracySnippetRunner.selectRunnerSnippet(\""+obj.name+"\", \""+obj.filename+"\");tracySnippetRunner.setActiveRunnerSnippet(this);'>" + obj.name + "</span></li>";
            }
            snippetList += "</ul>";
            document.getElementById("tracyRunnerSnippets").innerHTML = snippetList;

        </script>
HTML;

        $out .= \TracyDebugger::generatedTimeSize('snippetRunner', \Tracy\Debugger::timer('snippetRunner'), strlen($out));

        return parent::loadResources() . $out;
    }

}
