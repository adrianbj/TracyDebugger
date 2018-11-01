<?php

class AdminerPanel extends BasePanel {

    protected $icon;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('adminer');

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="388 299.4 13.3 13.6">
          <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M388 308.4V310.7c.3 1.3 3.1 2.3 6.6 2.3 3.5 0 6.3-1 6.6-2.3V308.4c-1.1.8-3.4 1.4-6.7 1.4-3.1 0-5.4-.6-6.5-1.4zM395.4 305.6H394c-2-.1-3.5-.3-4.6-.7-.5-.2-1-.4-1.3-.6v2.4c.8.8 3.3 1.5 6.7 1.5s5.9-.7 6.7-1.5v-2.4c-.4.2-.8.5-1.3.6-1.3.4-2.9.6-4.8.7zM394.7 299.4c-4 0-6.3 1.1-6.6 2.3v.7c.8.8 3.3 1.5 6.7 1.5 3.4 0 5.9-.7 6.7-1.5v-.6-.1c-.5-1.2-2.8-2.3-6.8-2.3z"/>
        </svg>';

        return '
        <span title="Adminer">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Adminer' : '') . '
        </span>';
    }


    public function getPanel() {

        $adminerModuleId = $this->wire('modules')->getModuleID("ProcessTracyAdminer");
        $adminerUrl = $this->wire('pages')->get("process=$adminerModuleId")->url;

        $out = '
        <h1>' . $this->icon . ' Adminer</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'AdminerPanel\')">+</a></span></span>
        <div class="tracy-inner" style="padding: 0 !important">
            <iframe src="'.$adminerUrl.'?db='.$this->wire('config')->dbName.'" style="width:100%; height:calc(100% - 5px); border: none; padding:0; margin:0;"></iframe>';

        $out .= '<div style="padding-left:5px">'.\TracyDebugger::generatePanelFooter('adminer', \Tracy\Debugger::timer('adminer'), strlen($out), 'adminerPanel').'</div>';
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}
