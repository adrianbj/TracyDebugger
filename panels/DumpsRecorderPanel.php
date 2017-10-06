<?php

class DumpsRecorderPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $dumpCount;

    public function getTab() {

        \Tracy\Debugger::timer('dumpsRecorder');

        $items = $this->wire('session')->tracyDumpItems;
        $this->dumpCount = count($items);
        $this->entries .= '<div><span style="display:inline-block;float:left"><label><input type="checkbox" onchange="preserveDumpsToggle(this)" id="preserveDumps" ' . ($this->wire('input')->cookie->tracyPreserveDumpItems ? 'checked="checked"' : '') . ' /> Preserve Dumps<label></span>'.($this->dumpCount > 0 ? '<span id="clearDumpsButton" style="display:inline-block;float:right"><input type="submit" onclick="clearDumps()" value="Clear Dumps" /></span>' : '') . '</div><div style="clear:both; margin-bottom:5px"></div>';
        if ($this->dumpCount > 0) {
            $this->iconColor = '#CD1818';
            $this->entries .= '
            <div class="dump-items">';
            foreach ($items as $item) {
                if ($item['title'] != '') {
                    $this->entries .= '<h2>' . $item['title'] . '</h2>';
                }
                $this->entries .= $item['dump'];
            }
            $this->entries .= '</div>';
        }
        else {
            $this->iconColor = '#009900';
            $this->entries .= 'No Dumps Recorded';
        }

        $this->icon = '
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve" width="16px" height="16px">
        <path class="dumpIconPath" d="M16,9.2h-0.8l0.5-6.3l-8.8,0v0.9h0.8l0,5.4H6.8V7.9V4.8c0-0.2-0.2-0.4-0.4-0.4H1.8c-0.2,0-0.3,0.1-0.4,0.3
            L0.2,7.5c0,0.1,0,0.1,0,0.2v0.2v1.3H0V11h0.6c0,0,0,0,0,0H1c0,0.2-0.1,0.3-0.1,0.5c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8
            c0-0.2,0-0.3-0.1-0.5h2c0,0,0,0,0,0h4.6c0,0.2-0.1,0.3-0.1,0.5c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8c0-0.2,0-0.3-0.1-0.5H16
            L16,9.2L16,9.2z M2.7,12.3c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9C3.5,11.9,3.1,12.3,2.7,12.3z
             M5.9,7.5H1.2l0.9-2.2h3.7L5.9,7.5L5.9,7.5z M12.6,12.3c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9
            C13.5,12,13.1,12.3,12.6,12.3z" fill="' . $this->iconColor . '"/>
        </svg>
        ';

        return '
        <span title="Dumps Recorder">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Dumps Recorder' : '') . ' ' . ($this->dumpCount > 0 ? '<span class="dumpCount">' . $this->dumpCount . '</span>' : '') . '
        </span>

        <script>
            preserveDumps = ' . ($this->wire('input')->cookie->tracyPreserveDumpItems ? 'true' : 'false') . ';

            window.addEventListener("beforeunload", function () {
                alterCookies();
            });

            function alterCookies() {
                if(!preserveDumps) {
                    document.cookie = "tracyClearDumpItems=true;expires=0;path=/";
                    document.cookie = "tracyPreserveDumpItems=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/";
                }
                else {
                    document.cookie = "tracyPreserveDumpItems=true;expires=0;path=/";
                }
            }
        </script>
        ';
    }


    public function getPanel() {
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = '
        <h1>' . $this->icon . ' Dumps Recorder' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1>

        <script>
            function preserveDumpsToggle(element) {
                preserveDumps = element.checked;
                alterCookies();
            }

            function clearDumps() {
                document.cookie = "tracyClearDumpItems=true;expires=0;path=/";
                document.getElementById("clearDumpsButton").innerHTML="";
                var elements = document.getElementsByClassName("dump-items");
                while(elements.length > 0) {
                    elements[0].parentNode.removeChild(elements[0]);
                }

                var icons = document.getElementsByClassName("dumpIconPath");
                i=0;
                while(i < icons.length) {
                    icons[i].style.fill="#009900";
                    i++;
                }

                var iconCounts = document.getElementsByClassName("dumpCount");
                i=0;
                while(i < iconCounts.length) {
                    iconCounts[i].innerHTML="";
                    i++;
                }
            }
        </script>

        <div class="tracy-inner tracy-DumpPanel">

            <div id="tracyDumpEntries">' . $this->entries . '</div>';

            $out .= \TracyDebugger::generatedTimeSize('dumpsRecorder', \Tracy\Debugger::timer('dumpsRecorder'), strlen($out));

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}