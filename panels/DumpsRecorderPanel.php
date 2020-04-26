<?php

class DumpsRecorderPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $dumpCount;

    public function getTab() {

        \Tracy\Debugger::timer('dumpsRecorder');

        $dumpsFile = $this->wire('config')->paths->cache . 'TracyDebugger/dumps.json';
        $items = file_exists($dumpsFile) ? json_decode(file_get_contents($dumpsFile), true) : array();
        $this->dumpCount = is_array($items) ? count($items) : 0;
        $this->entries .= '<div>'.($this->dumpCount > 0 ? '<span id="clearDumpsRecorderButton" style="display:inline-block;float:right"><input type="submit" onclick="clearRecorderDumps()" value="Clear Dumps" /></span>' : '') . '</div><div style="clear:both; margin-bottom:5px"></div>';
        if($this->dumpCount > 0) {
            $this->iconColor = \TracyDebugger::COLOR_WARN;
            $this->entries .= '
            <div class="dumpsrecorder-items">';
            foreach($items as $item) {
                if($item['title'] != '') {
                    $this->entries .= '<h2>' . \Tracy\Helpers::escapeHtml($item['title']) . '</h2>';
                }
                $this->entries .= $item['dump'];
            }
            $this->entries .= '</div>';
        }
        else {
            $this->iconColor = \TracyDebugger::COLOR_NORMAL;
            $this->entries .= 'No Dumps Recorded';
        }

        $this->icon = '
        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve" width="16px" height="16px">
        <path class="dumpsRecorderIconPath" d="M16,9.2h-0.8l0.5-6.3l-8.8,0v0.9h0.8l0,5.4H6.8V7.9V4.8c0-0.2-0.2-0.4-0.4-0.4H1.8c-0.2,0-0.3,0.1-0.4,0.3
            L0.2,7.5c0,0.1,0,0.1,0,0.2v0.2v1.3H0V11h0.6c0,0,0,0,0,0H1c0,0.2-0.1,0.3-0.1,0.5c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8
            c0-0.2,0-0.3-0.1-0.5h2c0,0,0,0,0,0h4.6c0,0.2-0.1,0.3-0.1,0.5c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8c0-0.2,0-0.3-0.1-0.5H16
            L16,9.2L16,9.2z M2.7,12.3c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9C3.5,11.9,3.1,12.3,2.7,12.3z
             M5.9,7.5H1.2l0.9-2.2h3.7L5.9,7.5L5.9,7.5z M12.6,12.3c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9
            C13.5,12,13.1,12.3,12.6,12.3z" fill="' . $this->iconColor . '"/>
        </svg>
        ';

        return '
        <span title="Dumps Recorder">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Dumps Recorder' : '') . ' ' . ($this->dumpCount > 0 ? '<span class="dumpsRecorderCount">' . $this->dumpCount . '</span>' : '') . '
        </span>
        ';
    }


    public function getPanel() {
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = '
        <h1>' . $this->icon . ' Dumps Recorder' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'DumpsRecorderPanel'.($isAdditionalBar ? '-'.$isAdditionalBar : '').'\')">+</a></span></span>

        <script>
            function clearRecorderDumps() {
                document.cookie = "tracyClearDumpsRecorderItems=true;expires=0;path=/";

                var elements = document.getElementsByClassName("dumpsrecorder-items");
                while(elements.length > 0) {
                    elements[0].parentNode.removeChild(elements[0]);
                }
                var icons = document.getElementsByClassName("dumpsRecorderIconPath");
                i=0;
                while(i < icons.length) {
                    icons[i].style.fill="'.\TracyDebugger::COLOR_NORMAL.'";
                    i++;
                }

                var dumpsRecorderCounts = document.getElementsByClassName("dumpsRecorderCount");
                i=0;
                while(i < dumpsRecorderCounts.length) {
                    dumpsRecorderCounts[i].innerHTML="";
                    i++;
                }

            }
        </script>

        <div class="tracy-inner tracy-DumpPanel">

            <div id="tracyDumpEntries">' . $this->entries . '</div>';

        $out .= \TracyDebugger::generatePanelFooter('dumpsRecorder', \Tracy\Debugger::timer('dumpsRecorder'), strlen($out), 'dumpsPanel');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}