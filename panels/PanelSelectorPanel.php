<?php

class PanelSelectorPanel extends BasePanel {

    protected $icon;

    protected $panelSettingsLinks = array(
        'adminer' => 'adminerPanel',
        'apiExplorer' => 'apiExplorerPanel',
        'captainHook' => 'captainHookPanel',
        'console' => 'consolePanel',
        'customPhp' => 'customPhpPanel',
        'debugMode' => 'debugModePanel',
        'diagnostics' => 'diagnosticsPanel',
        'dumpsRecorder' => 'dumpsPanel',
        'fileEditor' => 'fileEditorPanel',
        'links' => 'linksPanel',
        'methodsInfo' => 'methodShortcuts',
        'panelSelector' => 'panelSelectorPanel',
        'processwireLogs' => 'processwireAndTracyLogsPanels',
        'tracyLogs' => 'processwireAndTracyLogsPanels',
        'processwireInfo' => 'processwireInfoPanel',
        'requestInfo' => 'requestInfoPanel',
        'requestLogger' => 'requestLoggerPanel',
        'templateResources' => 'templateResourcesPanel',
        'todo' => 'todoPanel',
        'userSwitcher' => 'userSwitcherPanel',
        'validator' => 'validatorPanel'
    );

    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('panelSelector');

        $this->icon = '
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 369.793 369.792" style="enable-background:new 0 0 369.793 369.792;" xml:space="preserve">
                <path d="M320.83,140.434l-1.759-0.627l-6.87-16.399l0.745-1.685c20.812-47.201,19.377-48.609,15.925-52.031L301.11,42.61     c-1.135-1.126-3.128-1.918-4.846-1.918c-1.562,0-6.293,0-47.294,18.57L247.326,60l-16.916-6.812l-0.679-1.684     C210.45,3.762,208.475,3.762,203.677,3.762h-39.205c-4.78,0-6.957,0-24.836,47.825l-0.673,1.741l-16.828,6.86l-1.609-0.669     C92.774,47.819,76.57,41.886,72.346,41.886c-1.714,0-3.714,0.769-4.854,1.892l-27.787,27.16     c-3.525,3.477-4.987,4.933,16.915,51.149l0.805,1.714l-6.881,16.381l-1.684,0.651C0,159.715,0,161.556,0,166.474v38.418     c0,4.931,0,6.979,48.957,24.524l1.75,0.618l6.882,16.333l-0.739,1.669c-20.812,47.223-19.492,48.501-15.949,52.025L68.62,327.18     c1.162,1.117,3.173,1.915,4.888,1.915c1.552,0,6.272,0,47.3-18.561l1.643-0.769l16.927,6.846l0.658,1.693     c19.293,47.726,21.275,47.726,26.076,47.726h39.217c4.924,0,6.966,0,24.859-47.857l0.667-1.742l16.855-6.814l1.604,0.654     c27.729,11.733,43.925,17.654,48.122,17.654c1.699,0,3.717-0.745,4.876-1.893l27.832-27.219     c3.501-3.495,4.96-4.924-16.981-51.096l-0.816-1.734l6.869-16.31l1.64-0.643c48.938-18.981,48.938-20.831,48.938-25.755v-38.395     C369.793,159.95,369.793,157.914,320.83,140.434z M184.896,247.203c-35.038,0-63.542-27.959-63.542-62.3     c0-34.342,28.505-62.264,63.542-62.264c35.023,0,63.522,27.928,63.522,62.264C248.419,219.238,219.92,247.203,184.896,247.203z" fill="'.\TracyDebugger::COLOR_NORMAL.'"/>
            </svg>';


        return '
            <span title="Panel Selector">
                ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Panel Selector' : '') . '
            </span>
        ';
    }


    protected function isOnce($name, $defaultPanels) {

        $masterPanels = empty(\TracyDebugger::$stickyPanels) ? $defaultPanels : \TracyDebugger::$stickyPanels;

        if(empty(\TracyDebugger::$oncePanels)) {
            return false;
        }
        else {
            if((in_array($name, \TracyDebugger::$oncePanels) && !in_array($name, $masterPanels)) ||
            (!in_array($name, \TracyDebugger::$oncePanels) && in_array($name, $masterPanels))) {
                return true;
            }
            else {
                return false;
            }
        }
    }


    public function getPanel() {

        $out = '
        <script>
            function getSelectedTracyPanelCheckboxes() {
                var selchbox = [];
                var inpfields = document.getElementsByName("selectedPanels[]");
                var nr_inpfields = inpfields.length;
                for(var i=0; i<nr_inpfields; i++) {
                    if(inpfields[i].checked == true) selchbox.push(inpfields[i].value);
                }
                return selchbox;
            }

            function changeTracyPanels(type) {
                document.cookie = "tracyPanels"+type+"="+getSelectedTracyPanelCheckboxes()+"; expires=0; path=/";
                location.reload();
            }

            function resetTracyPanels() {
                document.cookie = "tracyPanelsSticky=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                document.cookie = "tracyPanelsOnce=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                location.reload();
            }

            function toggleStrictMode() {
                if(getCookie("tracyStrictMode")) {
                    document.cookie = "tracyStrictMode=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                    location.reload();
                }
                else {
                    document.cookie = "tracyStrictMode=1; expires=0; path=/";
                    location.reload();
                }
            }

            function disableTracy() {
                document.cookie = "tracyDisabled=1; expires=0; path=/";
                location.reload();
            }

            function toggleAllTracyPanels(ele) {
                var checkboxes = document.getElementsByName("selectedPanels[]");
                if(ele.checked) {
                    for(var i = 0; i < checkboxes.length; i++) {
                        if(checkboxes[i].disabled === false) {
                            checkboxes[i].checked = true;
                        }
                    }
                }
                else {
                     for(var i = 0; i < checkboxes.length; i++) {
                        if(checkboxes[i].disabled === false) {
                            checkboxes[i].checked = false;
                        }
                    }
                }
            }

            function getCookie(name) {
                var value = "; " + document.cookie;
                var parts = value.split("; " + name + "=");
                if(parts.length == 2) return parts.pop().split(";").shift();
            }

        </script>
        ';

        $onceIcon = '<span title="Status changed Once">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="12px" height="12px" viewBox="0 0 405.07 405.07" style="enable-background:new 0 0 405.07 405.07; width:12px !important; height:12px !important" xml:space="preserve">
                            <path d="M202.531,0C90.676,0,0,90.678,0,202.535C0,314.393,90.676,405.07,202.531,405.07c111.859,0,202.539-90.678,202.539-202.535   C405.07,90.678,314.391,0,202.531,0z M243.192,312.198c0,8.284-6.716,15-15,15h-27.629c-8.284,0-15-6.716-15-15v-168.35   l-17.1,9.231c-4.069,2.197-8.924,2.393-13.155,0.536c-4.233-1.858-7.373-5.565-8.51-10.046l-5.459-21.518   c-1.695-6.683,1.383-13.66,7.461-16.913l47.626-25.491c2.177-1.166,4.608-1.775,7.078-1.775h24.688c8.284,0,15,6.716,15,15V312.198   z" fill="'.\TracyDebugger::COLOR_WARN.'" />
                        </svg>
                    </span>';

        $out .= '<h1>'.$this->icon.' Panel Selector</h1>
        <div class="tracy-inner">
            <fieldset>
                <legend>*Panels with asterisk are on by default</legend><br />';
                    $defaultPanels = $this->wire('page')->template == "admin" ? \TracyDebugger::getDataValue('backendPanels') : \TracyDebugger::getDataValue('frontendPanels');
                    $showPanels = \TracyDebugger::$showPanels;
                    $out .= '<label><input type="checkbox" onchange="toggleAllTracyPanels(this)" ' . (count($showPanels) == count(\TracyDebugger::$allPanels) ? 'checked="checked"' : '') . ' /> Toggle All</label><br />';
                    $out .= '<div style="-webkit-column-count: 2; -moz-column-count: 2; column-count: 2; -webkit-column-gap: 40px; -moz-column-gap: 40px; column-gap: 40px;">';
                    foreach(\TracyDebugger::$allPanels as $name => $label) {

                        if(in_array($name, \TracyDebugger::$restrictedUserDisabledPanels)) continue;
                        if(in_array($name, \TracyDebugger::$superUserOnlyPanels) && !\TracyDebugger::$allowedSuperuser && !\TracyDebugger::$validLocalUser && !\TracyDebugger::$validSwitchedUser) continue;
                        // special additional check for adminer
                        if($name == 'adminer' && !\TracyDebugger::$allowedSuperuser) continue;
                        if($name == 'userSwitcher') {
                            if(\TracyDebugger::getDataValue('userSwitchSession') != '') $userSwitchSession = \TracyDebugger::getDataValue('userSwitchSession');
                            if(!\TracyDebugger::$allowedSuperuser && (!$this->wire('session')->tracyUserSwitcherId || (isset($userSwitchSession[$this->wire('session')->tracyUserSwitcherId]) && $userSwitchSession[$this->wire('session')->tracyUserSwitcherId] <= time()))) continue;
                        }

                        $seconds = isset(\TracyDebugger::$panelGenerationTime[$name]['time']) ? \TracyDebugger::$panelGenerationTime[$name]['time'] : '';
                        $size = isset(\TracyDebugger::$panelGenerationTime[$name]['size']) ? \TracyDebugger::human_filesize(\TracyDebugger::$panelGenerationTime[$name]['size']) : '';
                        $out .= '
                            <label style="'.($this->wire('page')->template == 'admin' && in_array($name, \TracyDebugger::$hideInAdmin) ? ' visibility:hidden;position: absolute; left: -999em;' : '').'">' .
                                ($this->isOnce($name, $defaultPanels) ? $onceIcon .'&nbsp;' : '<span style="display:inline-block;width:18px">&nbsp;</span>');
                                if(!empty(\TracyDebugger::$externalPanels) || !array_key_exists($name, \TracyDebugger::$externalPanels)) {
                                    if(array_key_exists($name, $this->panelSettingsLinks)) {
                                        $out .= \TracyDebugger::generatePanelSettingsLink($this->panelSettingsLinks[$name]);
                                    }
                                    else {
                                        $out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                    }
                                    $out .= '<span style="font-size:16px; font-weight:600"><a title="Panel Info" href="https://adrianbj.github.io/TracyDebugger/#/debug-bar?id='.str_replace(' ', '-', strtolower($label)).'" target="_blank">ℹ</a></span>';
                                }
                                else {
                                    $out .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                }
                                $out .= '&nbsp;<input type="checkbox" name="selectedPanels[]" ' . ($name == 'panelSelector' || in_array($name, \TracyDebugger::getDataValue('nonToggleablePanels')) ? 'disabled="disabled"' : '') . ' value="'.$name.'" ' . (in_array($name, $showPanels) ? 'checked="checked"' : '') . ' /> '
                                . $label . (in_array($name, $defaultPanels) ? '&nbsp;<strong>*</strong>' : '') . ($seconds ? '<span style="color:#999999; font-size:11px; float:right; margin-left:20px">&nbsp;' . \TracyDebugger::formatTime($seconds) . ($size ? ', '.$size : '') . '</span>' : '') . '
                            </label>';
                    }
                    $out .= '
                    </div>
                    <br />
                    <span style="float:left">
                        <input type="submit" onclick="changeTracyPanels(\'Once\')" value="Once" />&nbsp;
                        <input type="submit" onclick="changeTracyPanels(\'Sticky\')" value="Sticky" />&nbsp;
                        <input type="submit" onclick="resetTracyPanels()" value="Reset" />
                    </span>
                    <span style="float:right">';
                    if(!\TracyDebugger::getDataValue('strictMode')) {
                        $out .= '<input type="submit" onclick="toggleStrictMode()" value="' . ($this->wire('input')->cookie->tracyStrictMode ? 'Disable' : 'Enable') .' Strict Mode" />&nbsp;&nbsp;';
                    }
                    if(\TracyDebugger::getDataValue('panelSelectorTracyTogglerButton')) {
                        $out .= '<input type="submit" onclick="disableTracy()" value="Disable Tracy" />';
                    }
                    $out .= '
                    </span>

            </fieldset>';

                $out .= \TracyDebugger::generatePanelFooter('panelSelector', \Tracy\Debugger::timer('panelSelector'), strlen($out), 'panelSelectorPanel');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

}
