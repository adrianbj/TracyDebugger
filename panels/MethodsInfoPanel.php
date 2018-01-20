<?php

use Tracy\IBarPanel;

/**
 * Method Info panel
 */

class MethodsInfoPanel extends BasePanel {
    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('methodsInfo');

        return '
        <span title="Methods Info">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="16px" height="16px" viewBox="0 0 16 16">
                <path fill="#444444" d="M2.1 3.1c0.2 1.3 0.4 1.6 0.4 2.9 0 0.8-1.5 1.5-1.5 1.5v1c0 0 1.5 0.7 1.5 1.5 0 1.3-0.2 1.6-0.4 2.9-0.3 2.1 0.8 3.1 1.8 3.1s2.1 0 2.1 0v-2c0 0-1.8 0.2-1.8-1 0-0.9 0.2-0.9 0.4-2.9 0.1-0.9-0.5-1.6-1.1-2.1 0.6-0.5 1.2-1.1 1.1-2-0.3-2-0.4-2-0.4-2.9 0-1.2 1.8-1.1 1.8-1.1v-2c0 0-1 0-2.1 0s-2.1 1-1.8 3.1z"/>
                <path fill="#444444" d="M13.9 3.1c-0.2 1.3-0.4 1.6-0.4 2.9 0 0.8 1.5 1.5 1.5 1.5v1c0 0-1.5 0.7-1.5 1.5 0 1.3 0.2 1.6 0.4 2.9 0.3 2.1-0.8 3.1-1.8 3.1s-2.1 0-2.1 0v-2c0 0 1.8 0.2 1.8-1 0-0.9-0.2-0.9-0.4-2.9-0.1-0.9 0.5-1.6 1.1-2.1-0.6-0.5-1.2-1.1-1.1-2 0.2-2 0.4-2 0.4-2.9 0-1.2-1.8-1.1-1.8-1.1v-2c0 0 1 0 2.1 0s2.1 1 1.8 3.1z"/>
            </svg>
            ' . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Methods Info' : '') . '
        </span>
        ';
    }



    public function getPanel() {

        $docsUrl = 'https://adrianbj.github.io/TracyDebugger';
        $debugMethodsRootUrl = $docsUrl . '/#/debug-methods?id=';

        // panel title
        $out = '
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="16px" height="16px" viewBox="0 0 16 16">
                <path fill="#444444" d="M2.1 3.1c0.2 1.3 0.4 1.6 0.4 2.9 0 0.8-1.5 1.5-1.5 1.5v1c0 0 1.5 0.7 1.5 1.5 0 1.3-0.2 1.6-0.4 2.9-0.3 2.1 0.8 3.1 1.8 3.1s2.1 0 2.1 0v-2c0 0-1.8 0.2-1.8-1 0-0.9 0.2-0.9 0.4-2.9 0.1-0.9-0.5-1.6-1.1-2.1 0.6-0.5 1.2-1.1 1.1-2-0.3-2-0.4-2-0.4-2.9 0-1.2 1.8-1.1 1.8-1.1v-2c0 0-1 0-2.1 0s-2.1 1-1.8 3.1z"/>
                <path fill="#444444" d="M13.9 3.1c-0.2 1.3-0.4 1.6-0.4 2.9 0 0.8 1.5 1.5 1.5 1.5v1c0 0-1.5 0.7-1.5 1.5 0 1.3 0.2 1.6 0.4 2.9 0.3 2.1-0.8 3.1-1.8 3.1s-2.1 0-2.1 0v-2c0 0 1.8 0.2 1.8-1 0-0.9-0.2-0.9-0.4-2.9-0.1-0.9 0.5-1.6 1.1-2.1-0.6-0.5-1.2-1.1-1.1-2 0.2-2 0.4-2 0.4-2.9 0-1.2-1.8-1.1-1.8-1.1v-2c0 0 1 0 2.1 0s2.1 1 1.8 3.1z"/>
            </svg>
            Methods Info
        </h1>
        <div class="tracy-inner">
            <p><a href="'.$docsUrl.'" target="_blank"><button class="tracyCopyBtn">TracyDebugger Docs</button></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://tracy.nette.org/" target="_blank"><button class="tracyCopyBtn">Tracy Docs</button></a></p>
            <br />

            <p><strong><a href="'.$debugMethodsRootUrl.'additional-debug-methods" target="_blank">addBreakpoint($name = NULL, $enforceParent&nbsp;=&nbsp;NULL)</a></strong></p>
            <p>
            TD::addBreakpoint()<br />
            addBreakpoint()<br />
            bp()<br />
            </p>

            <p><strong><a href="'.$debugMethodsRootUrl.'bardump" target="_blank">barDump($var, $title = NULL, array $options = NULL)</a></strong></p>
            <p>
            TD::barDump()<br />
            barDump()<br />
            bd()<br />
            </p>

            <p><strong><a href="'.$debugMethodsRootUrl.'bardumpbig" target="_blank">barDumpBig($var, $title = NULL)</a></strong></p>
            <p>
            TD::barDumpBig()<br />
            barDumpBig()<br />
            bdb()<br />
            </p>


            <p><strong><a href="'.$debugMethodsRootUrl.'bardumplive" target="_blank">barDumpLive($var, $title = NULL)</a></strong></p>
            <p>
            TD::barDumpLive()<br />
            barDumpLive()<br />
            bdl()<br />
            </p>

            <p><strong><a href="'.$debugMethodsRootUrl.'debugall" target="_blank">debugAll($var, $title = NULL, array $options = NULL)</a></strong></p>
            <p>
            TD::debugAll()<br />
            debugAll()<br />
            da()<br />
            </p>

            <p><strong><a href="'.$debugMethodsRootUrl.'dump" target="_blank">dump($var, $title = NULL, array $options = NULL, $return = FALSE)</a></strong></p>
            <p>
            TD::dump()<br />
            dump()<br />
            d()<br />
            </p>

            <p><strong><a href="'.$debugMethodsRootUrl.'firelog" target="_blank">fireLog($var)</a></strong></p>
            <p>
            TD::fireLog()<br />
            fireLog()<br />
            fl()<br />
            </p>

            <p><strong><a href="'.$debugMethodsRootUrl.'log" target="_blank">log($str, $priority = ILogger::INFO)</a></strong></p>
            @priority: "debug", "info", "warning", "error", "exception", "critical"
            <p>
            TD::log()<br />
            l()<br />
            </p>

            <p><strong><a href="'.$debugMethodsRootUrl.'dump-all-variables-at-various-breakpoints" target="_blank">templateVars(get_defined_vars())</a></strong></p>
            <p>
            TD::templateVars()<br />
            templateVars()<br />
            tv()<br />
            </p>

            <p><strong><a href="'.$debugMethodsRootUrl.'timer" target="_blank">timer($name = NULL)</a></strong></p>
            <p>
            TD::timer()<br />
            timer()<br />
            t()<br />
            </p>
            ';
            $out .= \TracyDebugger::generatedTimeSize('methodsInfo', \Tracy\Debugger::timer('methodsInfo'), strlen($out)) . '
        </div>
        ';

        return parent::loadResources() . $out;
    }

}
