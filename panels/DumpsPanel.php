<?php

class DumpsPanel extends BasePanel {

    public $data;
    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $dumpCount;

    public function getTab() {

        \Tracy\Debugger::timer('dumps');

        $this->data = \TracyDebugger::$dumpItems;
        $data = $this->data;

        $this->dumpCount = is_array($data) ? count($data) : 0;
        if($this->dumpCount > 0) {
            $this->iconColor = \TracyDebugger::COLOR_WARN;
            $this->entries .= '
            <div class="dump-items">';
            foreach($data as $item) {
                if($item['title'] != '') {
                    $this->entries .= '<h2>' . \Tracy\Helpers::escapeHtml($item['title']) . '</h2>';
                }
                $this->entries .= $item['dump'];
            }
            $this->entries .= '</div>';
        }
        else {
            return;
        }

        $this->icon = '
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="16px" viewBox="3 4 16 16" enable-background="new 3 4 16 16" xml:space="preserve">
            <path class="'.(\TracyDebugger::isAdditionalBar() == 'ajax' ? 'ajaxDumpIconPath' : 'dumpIconPath').'" d="M7.8,16.8c-0.9,0-1.6,0.7-1.6,1.6c0,0.9,0.7,1.6,1.6,1.6s1.6-0.7,1.6-1.6C9.4,17.5,8.7,16.8,7.8,16.8z M3,4v1.6h1.6l2.9,6.1
                l-1.1,2c-0.1,0.2-0.2,0.5-0.2,0.8c0,0.9,0.7,1.6,1.6,1.6h9.6v-1.6H8.1c-0.1,0-0.2-0.1-0.2-0.2l0-0.1l0.7-1.3h6
                c0.6,0,1.1-0.3,1.4-0.8l2.9-5.2C19,6.7,19,6.5,19,6.4c0-0.4-0.4-0.8-0.8-0.8H6.4L5.6,4C5.6,4,3,4,3,4z M15.8,16.8
                c-0.9,0-1.6,0.7-1.6,1.6c0,0.9,0.7,1.6,1.6,1.6c0.9,0,1.6-0.7,1.6-1.6C17.4,17.5,16.7,16.8,15.8,16.8z" fill="' . $this->iconColor . '" />
            </svg>
        ';

        return '
        <span title="Dumps">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Dumps' : '') . ' ' . ($this->dumpCount > 0 ? '<span id="'.(\TracyDebugger::isAdditionalBar() == 'ajax' ? 'ajaxDumpCount' : 'dumpCount').'">' . $this->dumpCount . '</span>' : '') . '
        </span>
        ';
    }


    public function getPanel() {
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = '
        <h1>' . $this->icon . ' Dumps' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'DumpsPanel'.($isAdditionalBar ? '-'.$isAdditionalBar : '').'\')">+</a></span></span>

        <div class="tracy-inner tracy-DumpPanel">

            <div id="tracyDumpEntries">' . $this->entries . '</div>';

        $out .= \TracyDebugger::generatePanelFooter('dumps', \Tracy\Debugger::timer('dumps'), strlen($out), 'dumpsPanel');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}