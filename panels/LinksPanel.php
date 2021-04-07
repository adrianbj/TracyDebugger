<?php

class LinksPanel extends BasePanel {

    protected $icon;

    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('links');

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px"
        viewBox="0 0 16 16" style="enable-background:new 0 0 16 16;" xml:space="preserve">
            <path id="path10" d="M14.9,1.1c-1.5-1.5-3.8-1.5-5.3,0L6.5,4.2C5,5.7,5.1,8.1,6.5,9.5C6.7,9.7,7,9.9,7.3,10.1l0.6-0.6
                C8.2,9.2,8,8.7,8,8.4L7.8,8.2c-0.7-0.7-0.7-1.9,0-2.7l3.1-3.1c0.7-0.7,1.9-0.7,2.7,0c0.7,0.7,0.7,1.9,0,2.7l-2.1,2.1
                c0.1,0.3,0.4,1.1,0.2,2.4l3.1-3.1C16.4,4.9,16.4,2.6,14.9,1.1z" fill="'.\TracyDebugger::COLOR_NORMAL.'" />
            <path id="path12" d="M9.8,6.2C9.5,6,9.3,5.8,9,5.6L8.5,6.2C8.1,6.6,8.2,7,8.2,7.4c0.1,0.1,0.2,0.1,0.2,0.2c0.7,0.7,0.7,1.9,0,2.7
                L5,13.7c-0.7,0.7-1.9,0.7-2.7,0c-0.7-0.7-0.7-1.9,0-2.7l2.3-2.3c0.1-0.4-0.3-1.2-0.1-2.5L1.1,9.6c-1.5,1.5-1.5,3.8,0,5.3
                s3.8,1.5,5.3,0l3.4-3.4C11.3,10,11.2,7.6,9.8,6.2z" fill="'.\TracyDebugger::COLOR_NORMAL.'" />
        </svg>
        ';

        return '
        <span title="Links">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Links' : '') . '
        </span>
        ';
    }


    public function getPanel() {

        // panel title
        $out = '
        <h1>' . $this->icon . ' Links</h1>
        <div class="tracy-inner">
        ';

        if(\TracyDebugger::getDataValue('linksCode')) {
            foreach(explode("\n", \TracyDebugger::getDataValue('linksCode')) as $link) {
                $link_parts = explode('|', $link);
                $out .= '<a href="'.trim($link_parts[0]).'">'.trim($link_parts[1]).'</a><br />';
            }
        }
        else {
            $out .= 'No links added yet - visit the module settings to add some.';
        }

        $out .= \TracyDebugger::generatePanelFooter('links', \Tracy\Debugger::timer('links'), strlen($out), 'linksPanel');
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}
