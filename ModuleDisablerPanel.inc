<?php

use Tracy\IBarPanel;
use Tracy\Debugger;

/**
 * Module Selector Panel
 */

class ModuleDisablerPanel implements IBarPanel {

    protected $icon;
    protected $iconColor;
    protected $disabledModules;

    public function getTab() {
        if(\TracyDebugger::additionalBar()) return;
        \Tracy\Debugger::timer('moduleDisabler');

        $this->disabledModules = empty(\TracyDebugger::$disabledModules) ? array() : \TracyDebugger::$disabledModules;

        if(count($this->disabledModules) > 0) {
            $this->iconColor = '#CD1818';
        }
        else {
            $this->iconColor = '#009900';
        }

        $this->icon = '
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 99.012 99.012" style="enable-background:new 0 0 99.012 99.012;" xml:space="preserve">
                <g>
                    <path d="M25.08,15.648c-0.478-0.478-1.135-0.742-1.805-0.723c-0.675,0.017-1.314,0.309-1.768,0.808    c-14.829,16.325-6.762,51.623-5.916,55.115L0.723,85.717C0.26,86.18,0,86.808,0,87.463c0,0.654,0.26,1.283,0.723,1.746    l8.958,8.957c0.482,0.48,1.114,0.723,1.746,0.723c0.631,0,1.264-0.24,1.745-0.723l14.865-14.864    c7.237,1.859,16.289,2.968,24.28,2.968c9.599,0,22.739-1.543,30.836-8.886c0.5-0.454,0.793-1.093,0.809-1.769    c0.018-0.676-0.245-1.328-0.723-1.805L25.08,15.648z" fill="'.$this->iconColor.'"/>
                    <path d="M46.557,30.345c0.482,0.482,1.114,0.723,1.746,0.723c0.632,0,1.264-0.241,1.746-0.724l18.428-18.428    c1.305-1.305,2.023-3.04,2.023-4.885c0-1.846-0.719-3.582-2.023-4.886c-1.305-1.305-3.039-2.022-4.885-2.022    c-1.845,0-3.581,0.718-4.887,2.022L40.277,20.574c-0.964,0.964-0.964,2.527,0,3.492L46.557,30.345z" fill="'.$this->iconColor.'"/>
                    <path d="M96.99,30.661c-1.305-1.305-3.039-2.024-4.885-2.024c-1.847,0-3.582,0.718-4.886,2.023L68.79,49.089    c-0.464,0.463-0.724,1.091-0.724,1.746s0.26,1.282,0.724,1.746l6.28,6.278c0.481,0.482,1.113,0.724,1.746,0.724    c0.631,0,1.264-0.241,1.746-0.724l18.43-18.429C99.686,37.735,99.686,33.353,96.99,30.661z" fill="'.$this->iconColor.'"/>
                </g>
            </svg>';


        return '
            <span title="Module Disabler">
                ' . $this->icon . (\TracyDebugger::getDataValue('showModuleLabels') ? '&nbsp;Module Disabler' : '') . '
            </span>
        ';
    }



    public function getPanel() {

        $out = '
        <script>
            function getSelectedTracyModuleCheckboxes() {
                var selchbox = [];
                var inpfields = document.getElementsByName("selectedModules[]");
                var nr_inpfields = inpfields.length;
                for(var i=0; i<nr_inpfields; i++) {
                    if(inpfields[i].checked == true) selchbox.push(inpfields[i].value);
                }
                return selchbox;
            }

            function disableTracyModules() {
                document.cookie = "tracyModulesDisabled="+getSelectedTracyModuleCheckboxes()+"; expires=0; path=/";
                location.reload();
            }

            function resetTracyModules() {
                document.cookie = "tracyModulesDisabled=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                location.reload();
            }

            function toggleAllModules(ele) {
                var checkboxes = document.getElementsByName("selectedModules[]");
                if (ele.checked) {
                    for (var i = 0; i < checkboxes.length; i++) {
                        if(checkboxes[i].disabled === false) {
                            checkboxes[i].checked = true;
                        }
                    }
                }
                else {
                     for (var i = 0; i < checkboxes.length; i++) {
                        if(checkboxes[i].disabled === false) {
                            checkboxes[i].checked = false;
                        }
                    }
                }
            }

        </script>


        <style type="text/css">
            #tracy-debug-Panel-ModuleselectorPanel input[type="submit"] {
                background: #FFFFFF !important;
                margin: 3px !important;
                padding: 3px !important;
                border: 1px solid #D2D2D2 !important;
                -webkit-border-radius: 5px !important;
                -moz-border-radius: 5px !important;
                border-radius: 5px !important;
                cursor: pointer !important;
            }

            fieldset {
                border: 0 !important;
            }
        </style>';

        $out .= '<h1>'.$this->icon.' Module Disabler</h1>
        <div class="tracy-inner">
            <fieldset>';
                if(count(\TracyDebugger::$disabableModules) > 0 && wire('config')->advanced) {
                    $out .= '<legend>Temporarily disable autoload modules. <strong>Check to disable!</strong></legend><br />';
                    $out .= '<label><input type="checkbox" onchange="toggleAllModules(this)" ' . (count($this->disabledModules) == count(\TracyDebugger::$disabableModules) ? 'checked="checked"' : '') . ' /> Toggle All</label><br />';
                    foreach(\TracyDebugger::$disabableModules as $name) {
                        $flags = wire('modules')->getFlags($name);
                        $out .= '
                            <label style="width: 280px !important; white-space: nowrap;'.(wire('page')->template == 'admin' && in_array($name, \TracyDebugger::$hideInAdmin) ? ' visibility:hidden;position: absolute; left: -999em;' : '').'">
                                <input type="checkbox" name="selectedModules[]" ' . ($name == 'Moduleselector' ? 'disabled="disabled"' : '') . ' value="'.$name.'" ' . (in_array($name, $this->disabledModules) || ($flags & Modules::flagsDisabled) ? 'checked="checked"' : '') . ' />
                                <a href="'.wire('config')->urls->admin.'module/edit?name='.$name.'">' . $name . '</a>
                            </label>';
                    }
                    $out .= '<br />
                    <input type="submit" onclick="disableTracyModules()" value="Disable Modules" />&nbsp;
                    <input type="submit" onclick="resetTracyModules()" value="Reset" />';
                }
                else {
                    if(!wire('config')->advanced) {
                        $out .= '<legend>This panel only works if ProcessWire\'s Advanced Mode is turned on: $config->advanced = true;</legend>';
                    }
                    else {
                        $out .= '<legend>There are no modules installed that can be disabled.</legend>';
                    }
                }
            $out .= '</fieldset>' . \TracyDebugger::generatedTimeSize(\Tracy\Debugger::timer('moduleDisabler'), strlen($out)) . '
        </div>';

        return $out;
    }

}
