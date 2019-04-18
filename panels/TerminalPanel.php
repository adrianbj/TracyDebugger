<?php

class TerminalPanel extends BasePanel {

    protected $icon;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('terminal');

        $this->icon = '
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
        	 width="24.9px" height="16px" viewBox="223.3 227.6 24.9 16" enable-background="new 223.3 227.6 24.9 16" xml:space="preserve">
            <g>
                <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M248,241.9c-0.2-0.2-0.2-0.2-0.3-0.2h-14.4c-0.2,0-0.3,0-0.3,0.2c-0.2,0.2-0.2,0.2-0.2,0.3v0.9c0,0.2,0,0.3,0.2,0.3
                    c0.2,0.2,0.2,0.2,0.3,0.2h14.4c0.2,0,0.3,0,0.3-0.2c0.2-0.2,0.2-0.2,0.2-0.3v-0.8C248.1,242.3,248.1,242.1,248,241.9z"/>
                <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M224.8,227.8c-0.2-0.2-0.2-0.2-0.3-0.2c-0.2,0-0.3,0-0.3,0.2l-0.8,0.8c-0.2,0.2-0.2,0.2-0.2,0.3c0,0.2,0,0.3,0.2,0.3
                    l5.9,5.9l-5.9,5.9c-0.2,0.2-0.2,0.2-0.2,0.3c0,0.2,0,0.3,0.2,0.3l0.8,0.8c0.2,0.2,0.2,0.2,0.3,0.2c0.2,0,0.3,0,0.3-0.2l7-7
                    c0.2-0.2,0.2-0.2,0.2-0.3c0-0.2,0-0.3-0.2-0.3L224.8,227.8z"/>
            </g>
        </svg>';

        return '
        <span title="Terminal">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Terminal' : '') . '
        </span>';
    }


    public function getPanel() {

        $terminalModuleId = $this->wire('modules')->getModuleID("ProcessTerminal");
        $terminalUrl = $this->wire('pages')->get("process=$terminalModuleId")->url;

        $out = '
        <h1>' . $this->icon . ' Terminal</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'TerminalPanel\')">+</a></span></span>';

        if($this->wire('modules')->isInstalled("ProcessTerminal")) {
            $out .= '
            <div class="tracy-inner" style="padding: 0 !important">
                <iframe src="'.$terminalUrl.'" style="width:100%; height:calc(100% - 5px); border: none; padding:0; margin:0;"></iframe>';
        }
        else {
            $out .= '
            <div class="tracy-inner">
                <p>This panel is not available because the <a href="http://modules.processwire.com/modules/process-terminal/">ProcessTerminal module</a> has not been installed.</p>';
        }

        $out .= '<div style="padding-left:5px">'.\TracyDebugger::generatePanelFooter('terminal', \Tracy\Debugger::timer('terminal'), strlen($out)).'</div>';
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}
