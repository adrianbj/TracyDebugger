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
        \Tracy\Debugger::timer('Console');

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
            function processTracyCode(clear) {
                if(clear) {
                    var code = document.getElementById("tracyConsoleCode").value = "";
                }
                else {
                    var code = document.getElementById("tracyConsoleCode").value;
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
                           document.getElementById("tracyConsoleResult").innerHTML = xmlhttp.responseText;
                       }
                       else {
                           alert(xmlhttp.status+": "+xmlhttp.statusText);
                       }
                    }
                };
                xmlhttp.open("GET", "'.wire("config")->urls->admin . 'page/tracy-console/?pid='.wire('page')->id.'&code="+btoa(code).replace(/\+/g, "-").replace(/\//g, "_").replace(/\=+$/, ""), true);
                xmlhttp.send();
            }
        </script>
        <style type="text/css">
            #tracy-debug-panel-ConsolePanel fieldset {
                all: initial;
                font: inherit;
                color: inherit;
            }
            #tracy-debug-panel-ConsolePanel input[type="submit"] {
                background: #FFFFFF !important;
                margin: 3px !important;
                padding: 3px !important;
                border: 1px solid #D2D2D2 !important;
                -webkit-border-radius: 5px !important;
                -moz-border-radius: 5px !important;
                border-radius: 5px !important;
                cursor: pointer !important;
            }
            #tracy-debug-panel-ConsolePanel textarea {
                border: 1px solid #cccccc;
                width:500px;
                height:75px;
                background: #282828;
                font-family: Consolas, Menlo, Monaco, Lucida Console, Liberation Mono, DejaVu Sans Mono, Bitstream Vera Sans Mono, Courier New, monospace, serif;
                font-size: 14px;
                color: #81a2be;
                padding: 3px;
            }
        </style>
';


        $openPHP = '<' . '?php';
        $firstLine = 'if(!defined("PROCESSWIRE")) die("no direct access");';
        $code = file_get_contents(wire('config')->paths->cache . 'TracyDebugger/consoleCode.php');
        $code = str_replace("$openPHP\n$firstLine\n", "", $code);

        $out .= '<h1>'.$this->icon.' Console</h1>
        <div class="tracy-inner">
            <fieldset>
                <legend>Enter PHP code</legend><br />';
                    $out .= '
                    <textarea id="tracyConsoleCode" style="width:500px;height:75px" size="100" id="phpCode">'.$code.'</textarea><br />
                    <input type="submit" onclick="processTracyCode(false)" value="Run Code" />&nbsp;
                    <input type="submit" onclick="processTracyCode(true)" value="Clear Code" />
                    <br /><br />
                    <div id="tracyConsoleResult" style="background:#FDF5CE; border: 1px dotted #999999; padding: 3px;"></div>
            </fieldset>';
            \TracyDebugger::$panelGenerationTime['Console'] = \Tracy\Debugger::timer('Console');
            $out .= \TracyDebugger::generatedTime(\TracyDebugger::$panelGenerationTime['Console']);
        $out .= '</div>';

        return $out;
    }

}
