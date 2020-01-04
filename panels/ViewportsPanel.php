<?php

class ViewportsPanel extends BasePanel {

    protected $icon;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('viewports');

        $this->icon = '
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
        viewBox="0 0 20.5 16" enable-background="new 0 0 20.5 16" xml:space="preserve">
            <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M16.2,5.3c-0.3,0-0.5,0-0.8,0c-1.2,0-2,0.8-2,2c0,2.2,0,4.4,0,6.6c0,1.3,0.7,2,2.1,2c0.5,0,0.9-0.3,0.9-0.7
                c0-0.5-0.3-0.8-0.9-0.8c-0.5,0-0.6-0.1-0.6-0.5c0-2.2,0-4.4,0-6.6c0-0.5,0.1-0.5,0.5-0.5c1,0,1.9,0,2.9,0c0.5,0,0.6,0,0.6,0.6
                c0,1.6,0,3.3,0,4.9c0,0.5,0.3,0.9,0.8,0.9c0.4,0,0.7-0.3,0.7-0.9c0-1.7,0-3.4,0-5c0-1.2-0.8-1.9-2-1.9c-0.3,0-0.5,0-0.8,0
                c0-0.1,0-0.2,0-0.3c0-0.9,0-1.7,0-2.6c0-1.5-0.9-2.4-2.4-2.4C11,0,6.7,0,2.4,0C1,0,0,1,0,2.4c0,2.8,0,5.5,0,8.3C0,12,1,13,2.3,13
                c1.8,0,3.6,0,5.3,0c0.1,0,0.3,0,0.4,0c0,0.5,0,1,0,1.5c-0.6,0-1.2,0-1.8,0c-0.3,0-0.6,0.1-0.7,0.5c-0.2,0.5,0.1,1,0.8,1
                c1.5,0,2.9,0,4.4,0c0.2,0,0.5,0,0.7,0c0.4,0,0.7-0.4,0.7-0.8c0-0.4-0.3-0.7-0.8-0.7c-0.5,0-1,0-1.5,0c-0.1,0-0.2,0-0.3,0
                c0-0.5,0-1,0-1.5c0.5,0,1.1,0,1.6,0c0.5,0,0.8-0.3,0.8-0.7c0-0.4-0.3-0.8-0.8-0.8c-0.1,0-0.1,0-0.2,0c-2.9,0-5.8,0-8.7,0
                c-0.6,0-0.8-0.2-0.8-0.8c0-2.8,0-5.5,0-8.3c0-0.7,0.2-0.9,0.9-0.9c2.9,0,5.7,0,8.6,0c1.5,0,3,0,4.5,0c0.5,0,0.8,0.2,0.8,0.6
                C16.2,3.2,16.2,4.2,16.2,5.3z"/>
            <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M16.2,5.3c0-1.1,0-2.2,0-3.2c0-0.4-0.3-0.6-0.8-0.6c-1.5,0-3,0-4.5,0c-2.9,0-5.7,0-8.6,0c-0.7,0-0.9,0.2-0.9,0.9
                c0,2.8,0,5.5,0,8.3c0,0.6,0.2,0.8,0.8,0.8c2.9,0,5.8,0,8.7,0c0.1,0,0.1,0,0.2,0c0.5,0,0.9,0.3,0.8,0.8c0,0.4-0.3,0.7-0.8,0.7
                c-0.5,0-1,0-1.6,0c0,0.5,0,1,0,1.5c0.1,0,0.2,0,0.3,0c0.5,0,1,0,1.5,0c0.4,0,0.8,0.3,0.8,0.7c0,0.4-0.3,0.7-0.7,0.8
                c-0.2,0-0.5,0-0.7,0c-1.5,0-2.9,0-4.4,0c-0.6,0-1-0.5-0.8-1c0.1-0.3,0.4-0.5,0.7-0.5c0.6,0,1.2,0,1.8,0c0-0.5,0-1,0-1.5
                c-0.1,0-0.3,0-0.4,0c-1.8,0-3.6,0-5.3,0C1,13,0,12,0,10.7c0-2.8,0-5.5,0-8.3C0,1,1,0,2.4,0c4.3,0,8.6,0,13,0c1.5,0,2.4,0.9,2.4,2.4
                c0,0.9,0,1.7,0,2.6c0,0.1,0,0.2,0,0.3c0.3,0,0.5,0,0.8,0c1.2,0,1.9,0.7,2,1.9c0,1.7,0,3.4,0,5c0,0.5-0.3,0.9-0.7,0.9
                c-0.5,0-0.8-0.3-0.8-0.9c0-1.6,0-3.3,0-4.9c0-0.5,0-0.6-0.6-0.6c-1,0-1.9,0-2.9,0C15,6.9,15,6.9,15,7.4c0,2.2,0,4.4,0,6.6
                c0,0.5,0.1,0.5,0.6,0.5c0.6,0,0.9,0.3,0.9,0.8c0,0.5-0.3,0.7-0.9,0.7c-1.3,0-2.1-0.7-2.1-2c0-2.2,0-4.4,0-6.6c0-1.2,0.7-2,2-2
                C15.7,5.3,15.9,5.3,16.2,5.3z"/>
        </svg>
        ';

        return '
        <span title="Viewports">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Viewports' : '') . '
        </span>';
    }


    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' Viewports</h1>';

        $pageUrl = $this->wire('input')->url(true);
        $pageUrl .= strpos($pageUrl,'?') !== false ? '&' : '?';
        $pageUrl .= 'tracyDisabled=1';

        $out .= '
        <div class="tracy-inner" style="padding: 0 !important">';
            $sizes = array(
                '1366x768',
                '1024x768',
                '768x1280',
                '640x960',
                '480x800',
                '320x480'
            );
            foreach($sizes as $size) {
                $size = explode('x', $size);
                $out .= '<div style="margin-bottom: 10px"><div style="width:'.$size[0].'px" class="tracy-section-heading">'.$size[0].' x '.$size[1].'</div><iframe src="'.$pageUrl.'" style="width: '.$size[0].'px; height: '.$size[1].'px; border: 1px solid #afafaf;"></iframe></div>';
            }

        $out .= '
            <div style="padding-left:5px">'.\TracyDebugger::generatePanelFooter('viewports', \Tracy\Debugger::timer('viewports'), strlen($out), 'viewportsPanel').'</div>';
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}
