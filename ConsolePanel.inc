<?php

use Tracy\IBarPanel;
use Tracy\Debugger;

/**
 * Console panel
 */

class ConsolePanel implements IBarPanel {

    protected $icon;

    public function getTab() {
        if(\TracyDebugger::additionalBar()) return;
        \Tracy\Debugger::timer('console');

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 471.362 471.362" style="enable-background:new 0 0 471.362 471.362;" xml:space="preserve">
            <g>
                <path d="M468.794,355.171c-1.707-1.718-3.897-2.57-6.563-2.57H188.145c-2.664,0-4.854,0.853-6.567,2.57    c-1.711,1.711-2.565,3.897-2.565,6.563v18.274c0,2.662,0.854,4.853,2.565,6.563c1.713,1.712,3.903,2.57,6.567,2.57h274.086    c2.666,0,4.856-0.858,6.563-2.57c1.711-1.711,2.567-3.901,2.567-6.563v-18.274C471.365,359.068,470.513,356.882,468.794,355.171z" fill="#444444"/>
                <path d="M30.259,85.075c-1.903-1.903-4.093-2.856-6.567-2.856s-4.661,0.953-6.563,2.856L2.852,99.353    C0.95,101.255,0,103.442,0,105.918c0,2.478,0.95,4.664,2.852,6.567L115.06,224.69L2.852,336.896C0.95,338.799,0,340.989,0,343.46    c0,2.478,0.95,4.665,2.852,6.567l14.276,14.273c1.903,1.906,4.089,2.854,6.563,2.854s4.665-0.951,6.567-2.854l133.048-133.045    c1.903-1.902,2.853-4.096,2.853-6.57c0-2.473-0.95-4.663-2.853-6.565L30.259,85.075z" fill="#444444"/>
            </g>
        </svg>';

        return '
        <span title="Console">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Console' : '') . '
        </span>
        ';
    }


    public function getPanel() {

        $out = '
        <script>

            var tce; //setup Tracy Console Editor

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
                if(((e.keyCode==10||e.charCode==10)||(e.keyCode==13||e.charCode==13)) && (e.metaKey || e.ctrlKey)) {
                    processTracyCode(false);
                }
            });

            function tryParseJSON (str){
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
                catch (e) {
                    return str;
                }

                return false;
            };

            function processTracyCode(clear) {
                if(clear) {
                    var code = "";
                    tce.setValue("");
                }
                else {
                    var code = tce.getValue();
                }
                callPhp(code);
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
                        if(xmlhttp.status == 200){
                            document.getElementById("tracyConsoleResult").innerHTML = tryParseJSON(xmlhttp.responseText);
                        }
                        else {
                            document.getElementById("tracyConsoleResult").innerHTML = xmlhttp.status+": " + xmlhttp.statusText + "<br />See the browser dev console for the actual error";
                        }
                    }
                };
                xmlhttp.open("POST", "'.wire("config")->urls->admin . 'page/tracy-console/", true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xmlhttp.send("pid='.wire('page')->id.'&code="+btoa(code).replace(/\+/g, "-").replace(/\//g, "_").replace(/\=+$/, ""));
            }
        </script>
        <style type="text/css">
            #tracy-debug-panel-ConsolePanel  {
                width: calc(100vw - 40px) !important;
            }
            fieldset {
                border: 0 !important;
            }
        </style>
        ';

        $code = '';
        $openPHP = '<' . '?php';
        $firstLine = 'if(!defined("PROCESSWIRE")) die("no direct access");';
        $file = wire('config')->paths->cache . 'TracyDebugger/consoleCode.php';
        if(file_exists($file)) {
            $code = file_get_contents(wire('config')->paths->cache . 'TracyDebugger/consoleCode.php');
            $code = str_replace("$openPHP\n$firstLine\n", "", $code);
            $code = json_encode($code); // json_encode to convert line breaks to \n - needed by setValue()
        }

        $out .= '<h1>'.$this->icon.' Console</h1>
        <div class="tracy-inner">
            <fieldset>
                <legend>Enter PHP code, then use CTRL+Enter or CMD+Enter to run.</legend><br />';
                    $out .= '
                    <div id="tracyConsoleCode" style="visibility:hidden; width: calc(100vw - 80px) !important; height:100px"></div><br />
                    <input type="submit" onclick="processTracyCode(false)" value="Run Code" />&nbsp;
                    <input type="submit" onclick="processTracyCode(true)" value="Clear Code" />
                    <br /><br />
                    <div id="tracyConsoleResult" style="background:#FDF5CE; border: 1px dotted #999999; padding: 3px;"></div>
            </fieldset>
        </div>';

        $out .= '
        <script>
            JavaScript.load("'.wire('config')->urls->siteModules.'TracyDebugger/ace-editor/ace.js", function() {
                if(typeof ace !== "undefined") {
                    tce = ace.edit("tracyConsoleCode");
                    tce.container.style.lineHeight = 1.8;
                    tce.setFontSize(13);
                    tce.setShowPrintMargin(false);
                    tce.$blockScrolling = Infinity; //fix deprecation warning

                    tce.on("beforeEndOperation", function(e) {
                        localStorage.setItem("tracyConsole", btoa(tce.getValue()));
                    });

                    // set theme
                    JavaScript.load("'.wire('config')->urls->siteModules.'TracyDebugger/ace-editor/theme-tomorrow_night.js", function() {
                        tce.setTheme("ace/theme/tomorrow_night");
                    });

                    // set mode to php
                    JavaScript.load("'.wire('config')->urls->siteModules.'TracyDebugger/ace-editor/mode-php.js", function() {
                        tce.session.setMode({path:"ace/mode/php", inline:true});
                    });

                    // set autocomplete and other options
                    JavaScript.load("'.wire('config')->urls->siteModules.'TracyDebugger/ace-editor/ext-language_tools.js", function() {
                        var ml = Math.round(Math.max(document.documentElement.clientHeight, window.innerHeight || 0) / 60);
                        tce.setOptions({
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true,
                            minLines: 5,
                            maxLines: ml
                        });
                        document.getElementById("tracyConsoleCode").style.visibility = "visible";
                        if(!!localStorage.getItem("tracyConsole") && atob(localStorage.getItem("tracyConsole")) !== "null" && atob(localStorage.getItem("tracyConsole")) !== "undefined") {
                            tce.setValue(atob(localStorage.getItem("tracyConsole")));
                        }
                        else {
                            tce.setValue('.$code.');
                        }

                        tce.focus();

                        // go to end of the last line
                        count = tce.session.getLength();
                        tce.gotoLine(count, tce.session.getLine(count-1).length);

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
                        };

                    });
                }
            });
        </script>
        ';

        \TracyDebugger::$panelGenerationTime['console'] = \Tracy\Debugger::timer('console');
        $out .= \TracyDebugger::generatedTime(\TracyDebugger::$panelGenerationTime['console']);

        return $out;
    }

}
