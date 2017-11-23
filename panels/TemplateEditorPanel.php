<?php
/**
 * templateEditor panel
 */

class TemplateEditorPanel extends BasePanel {

    protected $icon;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('templateEditor');

        if(isset($_POST['testTemplateCode'])) {
            $iconColor = '#D51616';
        }
        else {
            $iconColor = '#009900';
        }

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 492.014 492.014" style="enable-background:new 0 0 492.014 492.014;" xml:space="preserve" width="16px" height="16px">
            <g id="XMLID_144_">
                <path id="XMLID_151_" d="M339.277,459.566H34.922V32.446h304.354v105.873l32.446-32.447V16.223C371.723,7.264,364.458,0,355.5,0   H18.699C9.739,0,2.473,7.264,2.473,16.223v459.568c0,8.959,7.265,16.223,16.226,16.223H355.5c8.958,0,16.223-7.264,16.223-16.223   V297.268l-32.446,32.447V459.566z" fill="'.$iconColor.'"></path>
                <path id="XMLID_150_" d="M291.446,71.359H82.751c-6.843,0-12.396,5.553-12.396,12.398c0,6.844,5.553,12.397,12.396,12.397h208.694   c6.845,0,12.397-5.553,12.397-12.397C303.843,76.912,298.29,71.359,291.446,71.359z" fill="'.$iconColor.'"></path>
                <path id="XMLID_149_" d="M303.843,149.876c0-6.844-5.553-12.398-12.397-12.398H82.751c-6.843,0-12.396,5.554-12.396,12.398   c0,6.845,5.553,12.398,12.396,12.398h208.694C298.29,162.274,303.843,156.722,303.843,149.876z" fill="'.$iconColor.'"></path>
                <path id="XMLID_148_" d="M274.004,203.6H82.751c-6.843,0-12.396,5.554-12.396,12.398c0,6.845,5.553,12.397,12.396,12.397h166.457   L274.004,203.6z" fill="'.$iconColor.'"></path>
                <path id="XMLID_147_" d="M204.655,285.79c1.678-5.618,4.076-11.001,6.997-16.07h-128.9c-6.843,0-12.396,5.553-12.396,12.398   c0,6.844,5.553,12.398,12.396,12.398h119.304L204.655,285.79z" fill="'.$iconColor.'"></path>
                <path id="XMLID_146_" d="M82.751,335.842c-6.843,0-12.396,5.553-12.396,12.398c0,6.843,5.553,12.397,12.396,12.397h108.9   c-3.213-7.796-4.044-16.409-1.775-24.795H82.751z" fill="'.$iconColor.'"></path>
                <path id="XMLID_145_" d="M479.403,93.903c-6.496-6.499-15.304-10.146-24.48-10.146c-9.176,0-17.982,3.647-24.471,10.138   L247.036,277.316c-5.005,5.003-8.676,11.162-10.703,17.942l-14.616,48.994c-0.622,2.074-0.057,4.318,1.477,5.852   c1.122,1.123,2.624,1.727,4.164,1.727c0.558,0,1.13-0.08,1.688-0.249l48.991-14.618c6.782-2.026,12.941-5.699,17.943-10.702   l183.422-183.414c6.489-6.49,10.138-15.295,10.138-24.472C489.54,109.197,485.892,100.392,479.403,93.903z" fill="'.$iconColor.'"></path>
            </g>
        </svg>';

        return '
        <span title="Template Editor">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Template Editor' : '') . '
        </span>
        ';
    }


    public function getPanel() {

        $out = '
        <script>
            var confirmed = false;
            var tte; //setup Tracy TemplateEditor Editor

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

            function tracyGetRawCode(type) {
                var tracyTemplateEditor = {
                    "scrollTop": tte.session.getScrollTop(),
                    "scrollLeft": tte.session.getScrollLeft(),
                    "row": Number(tte.selection.getCursor()["row"]) + 1,
                    "column": tte.selection.getCursor()["column"]
                };
                localStorage.setItem("tracyTemplateEditor-'.$this->wire('page')->template->name.'", JSON.stringify(tracyTemplateEditor));

                if(type === "live" && !confirm("Are you sure you want to make these changes live? This can\'t be undone!")) {
                    confirmed = false;
                }
                else {
                    confirmed = true;
                }

                // doing btoa because if template code contains <script>, browser are failing with ERR_BLOCKED_BY_XSS_AUDITOR
                document.getElementById("tracyTemplateEditorRawCode").innerHTML = btoa(tte.getValue());
            }

            document.getElementById("tracyEditorSubmission").onsubmit = function(e){
                if(!confirmed) e.preventDefault();
            }

            document.getElementById("tracyTemplateEditorCode").addEventListener("keydown", function(e) {
                if(((e.keyCode==10||e.charCode==10)||(e.keyCode==13||e.charCode==13)) && (e.metaKey || e.ctrlKey)) {
                    document.getElementById("testTemplateCode").click();
                }
            });
        </script>
        ';

        $out .= '<h1>'.$this->icon.' Template Editor</h1>
        <div class="tracy-inner">
            <form id="tracyEditorSubmission" method="post" action="'.\TracyDebugger::inputUrl(true).'">
                <fieldset>
                    <legend>Enter PHP code, then use CTRL+Enter or CMD+Enter to test.</legend><br />';
                    $out .= '
                    <div id="tracyTemplateEditorCode" style="visibility:hidden; width: calc(100vw - 80px) !important; height:100px"></div><br />
                    <textarea id="tracyTemplateEditorRawCode" name="tracyTemplateEditorRawCode" style="display:none"></textarea>
                    <input type="submit" id="testTemplateCode" name="testTemplateCode" onclick="tracyGetRawCode(\'test\')" value="Test" />&nbsp;
                    <input type="submit" name="changeTemplateCode" onclick="tracyGetRawCode(\'live\')" value="Push Live" />&nbsp;
                    <input type="submit" name="resetTemplateCode" onclick="tracyGetRawCode(\'reset\')" value="Reset" />
                </fieldset>
            </form>
        </div>';

        $out .= '
        <script>
            JavaScript.load("'.$this->wire('config')->urls->TracyDebugger.'ace-editor/ace.js", function() {
                if(typeof ace !== "undefined") {
                    tte = ace.edit("tracyTemplateEditorCode");
                    tte.container.style.lineHeight = 1.8;
                    tte.setFontSize(13);
                    tte.setShowPrintMargin(false);
                    tte.$blockScrolling = Infinity; //fix deprecation warning

                    // set theme
                    JavaScript.load("'.$this->wire('config')->urls->TracyDebugger.'ace-editor/theme-tomorrow_night.js", function() {
                        tte.setTheme("ace/theme/tomorrow_night");
                    });

                    // set mode to php
                    JavaScript.load("'.$this->wire('config')->urls->TracyDebugger.'ace-editor/mode-php.js", function() {
                        tte.session.setMode({path:"ace/mode/php"});
                    });

                    // set autocomplete and other options
                    JavaScript.load("'.$this->wire('config')->urls->TracyDebugger.'ace-editor/ext-language_tools.js", function() {
                        var ml = Math.round(Math.max(document.documentElement.clientHeight, window.innerHeight || 0) / 60);
                        tte.setOptions({
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true,
                            minLines: 5,
                            maxLines: ml
                        });
                        document.getElementById("tracyTemplateEditorCode").style.visibility = "visible";
                        tte.setValue('.json_encode(file_get_contents($this->wire('page')->template->filename)).');
                        tte.focus();

                        // go to last cursor position
                        var tracyTemplateEditor = JSON.parse(localStorage.getItem("tracyTemplateEditor-'.$this->wire('page')->template->name.'"));
                        if(!!tracyTemplateEditor) {
                            tte.gotoLine(tracyTemplateEditor.row, tracyTemplateEditor.column);
                            tte.session.setScrollTop(tracyTemplateEditor.scrollTop);
                            tte.session.setScrollLeft(tracyTemplateEditor.scrollLeft);
                        }
                        else {
                            tte.gotoLine(1, 0);
                        }

                        function resizeAce() {
                            var ml = Math.round(Math.max(document.documentElement.clientHeight, window.innerHeight || 0) / 60);
                            tte.setOptions({
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

        $out .= \TracyDebugger::generatedTimeSize('templateEditor', \Tracy\Debugger::timer('templateEditor'), strlen($out));

        return parent::loadResources() . $out;
    }

}
