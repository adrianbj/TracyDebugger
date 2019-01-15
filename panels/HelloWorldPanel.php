<?php
/**
 * Tracy Debugger Hello World Panel
 *
 * To make your panel visible you have to add it to the public static $allPanels array in TracyDebugger.module
 * See also https://tracy.nette.org/en/extensions for docs about tracy panels
 *
 * @author Bernhard Baumrock, 24.11.2018
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class HelloWorldPanel extends BasePanel {

    // settings
    private $name = 'helloWorld';
    private $label = 'Hello World';

    // the svg icon shown in the bar and in the panel header
    private $icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="106.059px" height="106.059px" viewBox="0 0 106.059 106.059" style="enable-background:new 0 0 106.059 106.059;" xml:space="preserve">
        <g>
            <path d="M90.546,15.518C69.858-5.172,36.199-5.172,15.515,15.513C-5.173,36.198-5.171,69.858,15.517,90.547   c20.682,20.684,54.341,20.684,75.027-0.004C111.23,69.858,111.229,36.2,90.546,15.518z M84.757,84.758   c-17.494,17.494-45.96,17.496-63.455,0.002c-17.498-17.497-17.496-45.966,0-63.46C38.796,3.807,67.261,3.805,84.759,21.302   C102.253,38.796,102.251,67.265,84.757,84.758z M33.24,38.671c0-3.424,2.777-6.201,6.201-6.201c3.423,0,6.2,2.776,6.2,6.201   c0,3.426-2.777,6.202-6.2,6.202C36.017,44.873,33.24,42.097,33.24,38.671z M61.357,38.671c0-3.424,2.779-6.201,6.203-6.201   c3.423,0,6.2,2.776,6.2,6.201c0,3.426-2.776,6.202-6.2,6.202S61.357,42.097,61.357,38.671z M76.017,64.068   c-3.843,8.887-12.843,14.629-22.927,14.629c-10.301,0-19.354-5.771-23.064-14.703c-0.636-1.529,0.089-3.285,1.62-3.921   c0.376-0.155,0.766-0.229,1.15-0.229c1.176,0,2.292,0.695,2.771,1.85c2.777,6.686,9.655,11.004,17.523,11.004   c7.69,0,14.528-4.321,17.42-11.011c0.658-1.521,2.424-2.222,3.944-1.563S76.675,62.548,76.017,64.068z"></path>
        </g>
        </svg>';

    /**
     * define the tab for the panel in the debug bar
     */
    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer($this->name);
        return "<span title='{$this->label}'>{$this->icon} ".(\TracyDebugger::getDataValue('showPanelLabels') ? $this->label : '')."</span>";
    }

    /**
     * the panel's HTML code
     */
    public function getPanel() {
        $out = "<h1>{$this->icon} {$this->label}</h1>";

        // example of a maximize button
        $out .= '<span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'' . $this->className . '\')">+</a></span></span>';

        // panel body
        $out .= '<div class="tracy-inner">';
            $out .= $this->label;
            $out .= \TracyDebugger::generatePanelFooter($this->name, \Tracy\Debugger::timer($this->name), strlen($out), 'yourSettingsFieldsetId');
        $out .= '</div>';

        return parent::loadResources() . $out;
    }

}
