<?php namespace ProcessWire;

use Tracy\Debugger;

class DumpsRecorderPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $dumpCount;

    public function getTab() {

        Debugger::timer('dumpsRecorder');

        $dumpsFile = $this->wire('config')->paths->cache . 'TracyDebugger/dumps.json';
        $items = file_exists($dumpsFile) ? json_decode(file_get_contents($dumpsFile), true) : array();
        $this->dumpCount = is_array($items) ? count($items) : 0;
        if($this->dumpCount > 0) {
            $this->iconColor = TracyDebugger::COLOR_WARN;
            $this->entries .= '
            <div class="dumpsrecorder-items">';
            foreach($items as $item) {
                $meta = '';
                if(!empty($item['user']) || !empty($item['time'])) {
                    $meta = '<span style="color:#888; font-size:11px; font-weight:normal; margin-left:auto">';
                    if(!empty($item['user'])) $meta .= \Tracy\Helpers::escapeHtml($item['user']);
                    if(!empty($item['time'])) $meta .= ' @ ' . \Tracy\Helpers::escapeHtml($item['time']);
                    $meta .= '</span>';
                }
                $title = ($item['title'] != '') ? \Tracy\Helpers::escapeHtml($item['title']) : '&nbsp;';
                if($meta || $title) {
                    $this->entries .= '<h2 style="display:flex; align-items:center">' . '<span>' . $title . '</span>' . $meta . '</h2>';
                }
                $this->entries .= $item['dump'];
            }
            $this->entries .= '</div>';
        }
        else {
            $this->iconColor = TracyDebugger::COLOR_NORMAL;
            $this->entries .= 'No Dumps Recorded';
        }

        $this->icon = '
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve" width="16px" height="16px">
        <path class="dumpsRecorderIconPath" d="M16,9.2h-0.8l0.5-6.3l-8.8,0v0.9h0.8l0,5.4H6.8V7.9V4.8c0-0.2-0.2-0.4-0.4-0.4H1.8c-0.2,0-0.3,0.1-0.4,0.3
            L0.2,7.5c0,0.1,0,0.1,0,0.2v0.2v1.3H0V11h0.6c0,0,0,0,0,0H1c0,0.2-0.1,0.3-0.1,0.5c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8
            c0-0.2,0-0.3-0.1-0.5h2c0,0,0,0,0,0h4.6c0,0.2-0.1,0.3-0.1,0.5c0,1,0.8,1.8,1.8,1.8c1,0,1.8-0.8,1.8-1.8c0-0.2,0-0.3-0.1-0.5H16
            L16,9.2L16,9.2z M2.7,12.3c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9C3.5,11.9,3.1,12.3,2.7,12.3z
             M5.9,7.5H1.2l0.9-2.2h3.7L5.9,7.5L5.9,7.5z M12.6,12.3c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9
            C13.5,12,13.1,12.3,12.6,12.3z" fill="' . $this->iconColor . '"/>
        </svg>
        ';

        return $this->buildTab('Dumps Recorder', null, ' <span class="dumpsRecorderCount">' . ($this->dumpCount > 0 ? $this->dumpCount : '') . '</span>');
    }


    public function getPanel() {
        $out = $this->buildPanelHeader('Dumps Recorder', true, true);

        $out .= '
        <script' . TracyDebugger::getNonceAttr() . '>
            function clearRecorderDumps() {
                document.cookie = "tracyClearDumpsRecorderItems=true;expires=0;path=/";

                var elements = document.getElementsByClassName("dumpsrecorder-items");
                while(elements.length > 0) {
                    elements[0].parentNode.removeChild(elements[0]);
                }
                var icons = document.getElementsByClassName("dumpsRecorderIconPath");
                i=0;
                while(i < icons.length) {
                    icons[i].style.fill="'.TracyDebugger::COLOR_NORMAL.'";
                    i++;
                }

                var dumpsRecorderCounts = document.getElementsByClassName("dumpsRecorderCount");
                i=0;
                while(i < dumpsRecorderCounts.length) {
                    dumpsRecorderCounts[i].innerHTML="";
                    i++;
                }

                location.reload();

            }

            var clearBtn = document.getElementById("clearRecorderDumpsBtn");
            if(clearBtn) clearBtn.addEventListener("click", function() { clearRecorderDumps(); });
        </script>

        ' . $this->openPanel('tracy-DumpPanel') . '

            <div id="tracyDumpsRecorderEntries">' . $this->entries . '</div>' .
            ($this->dumpCount > 0 ? '<div style="margin:10px 0 5px 0; text-align:right"><input type="submit" id="clearRecorderDumpsBtn" value="Clear Dumps" /></div>' : '');

        return $this->closePanel($out, 'dumpsRecorder', 'dumpsPanel');
    }

}
