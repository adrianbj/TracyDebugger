<?php

use Tracy\IBarPanel;

/**
 * Method Info panel
 */

class MethodsInfoPanel implements IBarPanel {
    public function getTab() {
        return '
        <span title="Methods info">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="16px" height="16px" viewBox="0 0 16 16">
                <path fill="#444444" d="M2.1 3.1c0.2 1.3 0.4 1.6 0.4 2.9 0 0.8-1.5 1.5-1.5 1.5v1c0 0 1.5 0.7 1.5 1.5 0 1.3-0.2 1.6-0.4 2.9-0.3 2.1 0.8 3.1 1.8 3.1s2.1 0 2.1 0v-2c0 0-1.8 0.2-1.8-1 0-0.9 0.2-0.9 0.4-2.9 0.1-0.9-0.5-1.6-1.1-2.1 0.6-0.5 1.2-1.1 1.1-2-0.3-2-0.4-2-0.4-2.9 0-1.2 1.8-1.1 1.8-1.1v-2c0 0-1 0-2.1 0s-2.1 1-1.8 3.1z"/>
                <path fill="#444444" d="M13.9 3.1c-0.2 1.3-0.4 1.6-0.4 2.9 0 0.8 1.5 1.5 1.5 1.5v1c0 0-1.5 0.7-1.5 1.5 0 1.3 0.2 1.6 0.4 2.9 0.3 2.1-0.8 3.1-1.8 3.1s-2.1 0-2.1 0v-2c0 0 1.8 0.2 1.8-1 0-0.9-0.2-0.9-0.4-2.9-0.1-0.9 0.5-1.6 1.1-2.1-0.6-0.5-1.2-1.1-1.1-2 0.2-2 0.4-2 0.4-2.9 0-1.2-1.8-1.1-1.8-1.1v-2c0 0 1 0 2.1 0s2.1 1 1.8 3.1z"/>
            </svg>
            ' . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Methods Info' : '') . '
        </span>
        ';
    }



    public function getPanel() {

        // panel title
        $out = '
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="16px" height="16px" viewBox="0 0 16 16">
                <path fill="#444444" d="M2.1 3.1c0.2 1.3 0.4 1.6 0.4 2.9 0 0.8-1.5 1.5-1.5 1.5v1c0 0 1.5 0.7 1.5 1.5 0 1.3-0.2 1.6-0.4 2.9-0.3 2.1 0.8 3.1 1.8 3.1s2.1 0 2.1 0v-2c0 0-1.8 0.2-1.8-1 0-0.9 0.2-0.9 0.4-2.9 0.1-0.9-0.5-1.6-1.1-2.1 0.6-0.5 1.2-1.1 1.1-2-0.3-2-0.4-2-0.4-2.9 0-1.2 1.8-1.1 1.8-1.1v-2c0 0-1 0-2.1 0s-2.1 1-1.8 3.1z"/>
                <path fill="#444444" d="M13.9 3.1c-0.2 1.3-0.4 1.6-0.4 2.9 0 0.8 1.5 1.5 1.5 1.5v1c0 0-1.5 0.7-1.5 1.5 0 1.3 0.2 1.6 0.4 2.9 0.3 2.1-0.8 3.1-1.8 3.1s-2.1 0-2.1 0v-2c0 0 1.8 0.2 1.8-1 0-0.9-0.2-0.9-0.4-2.9-0.1-0.9 0.5-1.6 1.1-2.1-0.6-0.5-1.2-1.1-1.1-2 0.2-2 0.4-2 0.4-2.9 0-1.2-1.8-1.1-1.8-1.1v-2c0 0 1 0 2.1 0s2.1 1 1.8 3.1z"/>
            </svg>
            Methods Info
        </h1>
        <p style="font-size:16px">Tracy Documentation: <a href="https://tracy.nette.org/">https://tracy.nette.org/</a></p>
        <br />

        <p style="font-size:16px"><strong>dump($var, $return = FALSE)</strong></p>
        <p>
        TD::dump();<br />
        dump();<br />
        d();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>barDump($var, $title = NULL, array $options = NULL)</strong></p>
        <p>
        TD::barDump();<br />
        barDump();<br />
        bd();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>log($message, $priority = ILogger::INFO)</strong></p>
        @priority: "debug", "info", "warning", "error", "exception", "critical"
        <p>
        TD::log();<br />
        l();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>addBreakpoint($name = NULL, $enforceParent = NULL)</strong></p>
        <p>
        TD::addBreakpoint();<br />
        addBreakpoint();<br />
        bp();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>timer($name = NULL)</strong></p>
        <p>
        TD::timer();<br />
        timer();<br />
        t();<br />
        <br />
        </p>

        <p style="font-size:16px"><strong>fireLog($message)</strong></p>
        <p>
        TD::fireLog();<br />
        fireLog();<br />
        fl();<br />
        <br />
        </p>
        <p style="font-size:14px"><strong>For fireLog to work, you need to have installed these browser plugins:</strong></p>
        <p>
            <em>Chrome</em><br />
            <a href="https://github.com/MattSkala/chrome-firelogger">FireLogger</a>
        </p>
        <p>
            <em>Firefox</em><br />
            <a href="http://www.getfirebug.com/">Firebug</a><br />
            <a href="http://firelogger.binaryage.com/">FireLogger</a><br />
        </p>
        ';

        return $out;
    }

}
