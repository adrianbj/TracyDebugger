<?php

class TracyTogglerPanel extends BasePanel {

    protected $icon;

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('tracyToggler');

        $this->icon = '
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 44.816 44.816" style="enable-background:new 0 0 44.816 44.816;" xml:space="preserve">
                <g>
                    <path d="M22.404,21.173c2.126,0,3.895-1.724,3.895-3.85V3.849C26.299,1.724,24.53,0,22.404,0c-2.126,0-3.895,1.724-3.895,3.849    v13.475C18.51,19.449,20.278,21.173,22.404,21.173z" fill="'.\TracyDebugger::COLOR_NORMAL.'"/>
                    <path d="M30.727,3.33c-0.481-0.2-1.03-0.147-1.466,0.142c-0.434,0.289-0.695,0.776-0.695,1.298v5.113    c0,0.56,0.301,1.076,0.784,1.354c4.192,2.407,6.918,6.884,6.918,11.999c0,7.654-6.217,13.882-13.87,13.882    c-7.654,0-13.86-6.228-13.86-13.882c0-5.113,2.813-9.589,6.931-11.997c0.478-0.279,0.773-0.794,0.773-1.348V4.769    c0-0.521-0.261-1.009-0.695-1.298c-0.435-0.29-0.984-0.342-1.466-0.142C6.257,6.593,0.845,14.276,0.845,23.236    c0,11.92,9.653,21.58,21.572,21.58c11.917,0,21.555-9.66,21.555-21.58C43.971,14.276,38.554,6.593,30.727,3.33z" fill="'.\TracyDebugger::COLOR_NORMAL.'"/>
                </g>
            </svg>';


        $out = '
            <script>
                function disableTracy() {
                    document.cookie = "tracyDisabled=1; expires=0; path=/";
                    location.reload();
                }
            </script>
            <span onclick="disableTracy()" style="cursor: pointer;" title="Disable Tracy">
                ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Disable Tracy' : '') . '
            </span>
        ';

        \TracyDebugger::$panelGenerationTime['tracyToggler']['time'] = \Tracy\Debugger::timer('tracyToggler');
        \TracyDebugger::$panelGenerationTime['tracyToggler']['size'] = strlen($out);

        return $out;

    }

    public function getPanel() {
        return false;
    }

}
