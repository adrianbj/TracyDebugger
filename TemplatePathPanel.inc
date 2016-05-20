<?php

use Tracy\IBarPanel;
use Tracy\Debugger;

/**
 * Panel Selector panel
 */

class TemplatePathPanel implements IBarPanel {

    protected $icon;

    public function getTab() {
        if(\TracyDebugger::additionalBar()) return;
        \Tracy\Debugger::timer('templatePath');

        $iconColor = \TracyDebugger::$templatePath ? '#D51616' : '#009900';

        $this->icon = '
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 86.02 86.02" style="enable-background:new 0 0 86.02 86.02;" xml:space="preserve"  style="height:16px">
                <path d="M0.354,48.874l0.118,25.351c0.001,0.326,0.181,0.624,0.467,0.779l20.249,10.602c0.132,0.071,0.276,0.106,0.421,0.106   c0.001,0,0.001,0,0.002,0c0.061,0.068,0.129,0.133,0.211,0.182c0.14,0.084,0.297,0.126,0.455,0.126   c0.146,0,0.291-0.035,0.423-0.106l19.992-10.842c0.183-0.099,0.315-0.261,0.392-0.445c0.081,0.155,0.203,0.292,0.364,0.379   l20.248,10.602c0.132,0.071,0.277,0.106,0.422,0.106c0.001,0,0.001,0,0.002,0c0.062,0.068,0.129,0.133,0.21,0.182   c0.142,0.084,0.299,0.126,0.456,0.126c0.146,0,0.29-0.035,0.422-0.106L85.2,75.071c0.287-0.154,0.467-0.456,0.467-0.783V47.911   c0-0.008-0.004-0.016-0.004-0.022c0-0.006,0.002-0.013,0.002-0.021c-0.001-0.023-0.01-0.049-0.014-0.072   c-0.007-0.05-0.014-0.098-0.027-0.146c-0.011-0.031-0.023-0.062-0.038-0.093c-0.019-0.042-0.037-0.082-0.062-0.12   c-0.019-0.03-0.04-0.058-0.062-0.084c-0.028-0.034-0.059-0.066-0.092-0.097c-0.025-0.023-0.054-0.045-0.083-0.066   c-0.02-0.012-0.034-0.03-0.056-0.043c-0.02-0.011-0.041-0.017-0.062-0.025c-0.019-0.01-0.03-0.022-0.049-0.029l-20.603-9.978   c-0.082-0.034-0.17-0.038-0.257-0.047V10.865c0-0.007-0.002-0.015-0.002-0.022c-0.001-0.007,0.001-0.013,0.001-0.02   c-0.001-0.025-0.012-0.049-0.015-0.073c-0.007-0.049-0.014-0.098-0.027-0.145c-0.01-0.032-0.024-0.063-0.038-0.093   c-0.02-0.042-0.036-0.083-0.062-0.12c-0.02-0.03-0.041-0.057-0.062-0.084c-0.028-0.034-0.058-0.067-0.091-0.097   c-0.025-0.023-0.055-0.045-0.083-0.065c-0.021-0.014-0.035-0.032-0.056-0.045c-0.021-0.011-0.042-0.016-0.062-0.026   c-0.019-0.009-0.031-0.021-0.048-0.027L43.118,0.07c-0.24-0.102-0.512-0.093-0.746,0.025L22.009,10.71   c-0.299,0.151-0.487,0.456-0.489,0.79c0,0.006,0.002,0.011,0.002,0.016c-0.037,0.099-0.063,0.202-0.063,0.312l0.118,25.233   c-0.106,0.011-0.213,0.03-0.311,0.079L0.903,47.755c-0.298,0.15-0.487,0.456-0.489,0.791c0,0.005,0.003,0.009,0.003,0.015   C0.379,48.659,0.353,48.764,0.354,48.874z M61.321,10.964L43.372,21l-19.005-9.485l18.438-9.646L61.321,10.964z M62.486,37.008   l-18.214,9.586V22.535l18.214-10.18V37.008z M65.674,59.58l18.214-10.179v24.355l-18.214,9.883V59.58z M45.77,48.559l18.438-9.646   l18.515,9.099L64.775,58.045L45.77,48.559z M23.165,59.58L41.38,49.402v24.355l-18.215,9.882V59.58z M3.262,48.559L21.7,38.913   l18.515,9.099L22.266,58.045L3.262,48.559z" fill="'.$iconColor.'"/>
            </svg>';


        return '
            <span title="Template Path">
                ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Template Path' : '') . '
            </span>
        ';
    }


    public function getPanel() {

        $defaultTickIcon = '<span title="Active via Sticky">
                                <svg title="default" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 191.667 191.667" style="enable-background:new 0 0 191.667 191.667;" xml:space="preserve" width="12px" height="12px">
                                    <path d="M95.833,0C42.991,0,0,42.99,0,95.833s42.991,95.834,95.833,95.834s95.833-42.991,95.833-95.834S148.676,0,95.833,0z   M150.862,79.646l-60.207,60.207c-2.56,2.56-5.963,3.969-9.583,3.969c-3.62,0-7.023-1.409-9.583-3.969l-30.685-30.685  c-2.56-2.56-3.97-5.963-3.97-9.583c0-3.621,1.41-7.024,3.97-9.584c2.559-2.56,5.962-3.97,9.583-3.97c3.62,0,7.024,1.41,9.583,3.971  l21.101,21.1l50.623-50.623c2.56-2.56,5.963-3.969,9.583-3.969c3.62,0,7.023,1.409,9.583,3.969  C156.146,65.765,156.146,74.362,150.862,79.646z" fill="#009900" />
                                </svg>
                            </span>';
        $onceIcon = '<span title="Active via Once">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="12px" height="12px" viewBox="0 0 405.07 405.07" style="enable-background:new 0 0 405.07 405.07;" xml:space="preserve">
                            <path d="M202.531,0C90.676,0,0,90.678,0,202.535C0,314.393,90.676,405.07,202.531,405.07c111.859,0,202.539-90.678,202.539-202.535   C405.07,90.678,314.391,0,202.531,0z M243.192,312.198c0,8.284-6.716,15-15,15h-27.629c-8.284,0-15-6.716-15-15v-168.35   l-17.1,9.231c-4.069,2.197-8.924,2.393-13.155,0.536c-4.233-1.858-7.373-5.565-8.51-10.046l-5.459-21.518   c-1.695-6.683,1.383-13.66,7.461-16.913l47.626-25.491c2.177-1.166,4.608-1.775,7.078-1.775h24.688c8.284,0,15,6.716,15,15V312.198   z" fill="#FF9933" />
                        </svg>
                    </span>';

        $notActiveIcon = '<span title="Not Active or File Not Available, using default: '.pathinfo(wire('page')->template->filename, PATHINFO_BASENAME).'">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 27.965 27.965" style="enable-background:new 0 0 27.965 27.965;" xml:space="preserve" width="12px" height="12px">
                                <path d="M13.98,0C6.259,0,0,6.261,0,13.983c0,7.721,6.259,13.982,13.98,13.982c7.725,0,13.985-6.262,13.985-13.982    C27.965,6.261,21.705,0,13.98,0z M19.992,17.769l-2.227,2.224c0,0-3.523-3.78-3.786-3.78c-0.259,0-3.783,3.78-3.783,3.78    l-2.228-2.224c0,0,3.784-3.472,3.784-3.781c0-0.314-3.784-3.787-3.784-3.787l2.228-2.229c0,0,3.553,3.782,3.783,3.782    c0.232,0,3.786-3.782,3.786-3.782l2.227,2.229c0,0-3.785,3.523-3.785,3.787C16.207,14.239,19.992,17.769,19.992,17.769z" fill="#CD1818"/>
                            </svg>
                        </span>';

        $templateIcon = '';
        if(\TracyDebugger::$templatePath && file_exists(\TracyDebugger::$templatePath)) {
            if(isset(wire('input')->cookie->tracyTemplatePathSticky) && wire('input')->cookie->tracyTemplatePathSticky == \TracyDebugger::$templatePath) {
                $templateIcon = $defaultTickIcon;
            }
            else {
                $templateIcon = $onceIcon;
            }
        }
        else {
            $templateIcon = $notActiveIcon;
        }

        $out = '
        <script>
            function changeTemplatePath(type) {
                document.cookie = "tracyTemplatePath"+type+"="+document.getElementById(\'tracyTemplatePath\').innerHTML+"; expires=0; path=/";
                location.reload();
            }

            function resetTemplatePath() {
                document.cookie = "tracyTemplatePathSticky=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                document.cookie = "tracyTemplatePathOnce=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                location.reload();
            }
        </script>


        <style type="text/css">
            #tracy-debug-panel-TemplatePathPanel fieldset {
                all: initial;
                font: inherit;
                color: inherit;
            }
            #tracy-debug-panel-TemplatePathPanel input[type="submit"] {
                background: #FFFFFF !important;
                margin: 3px !important;
                padding: 3px !important;
                border: 1px solid #D2D2D2 !important;
                -webkit-border-radius: 5px !important;
                -moz-border-radius: 5px !important;
                border-radius: 5px !important;
                cursor: pointer !important;
            }
            #tracy-debug-panel-TemplatePathPanel input[type="text"] {
                resize: horizontal;
                width: auto;
            }
            #flex {
                display: flex;
                display: -webkit-flex;
                font-size: 16px !important;
            }
            #tracyTemplatePath {
                min-width: 0px;
                height: 20px;
                padding: 0;
                outline:none;
                font-weight: bold !important;
                background: #EDEDED !important;
            }
        </style>';


        $out .= '<h1>'.$this->icon.' Template Path</h1>
        <div class="tracy-inner">
            <fieldset>
                <legend>Temporarily use template files with this suffix.<br />eg. '.wire('page')->template->name.'-dev.php.<br /><br />Click to change from the "-dev" option.</legend><br />';

                    $out .= '
                    <div id="flex">' .
                        wire('page')->template->name.'-<div id="tracyTemplatePath" contenteditable="true" spellcheck="false">'.(\TracyDebugger::$templatePath ? \TracyDebugger::$templatePath : 'dev').'</div>.php&nbsp;' . $templateIcon . '<br /><br />
                    </div>
                    <input type="submit" onclick="changeTemplatePath(\'Once\')" value="Once" />&nbsp;
                    <input type="submit" onclick="changeTemplatePath(\'Sticky\')" value="Sticky" />&nbsp;
                    <input type="submit" onclick="resetTemplatePath()" value="Reset" />
            </fieldset>';
            \TracyDebugger::$panelGenerationTime['TemplatePath'] = \Tracy\Debugger::timer('TemplatePath');
            $out .= \TracyDebugger::generatedTime(\TracyDebugger::$panelGenerationTime['TemplatePath']);
        $out .= '</div>';

        return $out;
    }

}
