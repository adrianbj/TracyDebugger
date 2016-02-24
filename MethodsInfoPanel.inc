<?php

use Tracy\IBarPanel;

/**
 * Method Info panel
 */

class MethodsInfoPanel implements IBarPanel {
    public function getTab() {
        return '
        <style>
            .tracy-panel {
                max-height: 90% !important;
                max-width: 95% !important;
                overflow-y: scroll !important;
            }
        </style>
        <span title="Methods info">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 612 612" style="enable-background:new 0 0 612 612;" xml:space="preserve">
                <path d="M306,153c-21.114,0-38.25,17.136-38.25,38.25S284.886,229.5,306,229.5s38.25-17.117,38.25-38.25S327.133,153,306,153z      M306,267.75c-21.114,0-38.25,17.117-38.25,38.25v114.75c0,21.114,17.136,38.25,38.25,38.25s38.25-17.136,38.25-38.25V306     C344.25,284.886,327.133,267.75,306,267.75z M306,0C137.279,0,0,137.279,0,306s137.279,306,306,306s306-137.279,306-306     S474.721,0,306,0z M306,554.625C168.893,554.625,57.375,443.088,57.375,306S168.893,57.375,306,57.375     c137.088,0,248.625,111.537,248.625,248.625S443.088,554.625,306,554.625z" fill="#444"/>
            </svg>
            ' . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Methods Info' : '') . '
        </span>
        ';
    }



    public function getPanel() {

        // panel title
        $out = '
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 612 612" style="enable-background:new 0 0 612 612;" xml:space="preserve">
                <path d="M306,153c-21.114,0-38.25,17.136-38.25,38.25S284.886,229.5,306,229.5s38.25-17.117,38.25-38.25S327.133,153,306,153z      M306,267.75c-21.114,0-38.25,17.117-38.25,38.25v114.75c0,21.114,17.136,38.25,38.25,38.25s38.25-17.136,38.25-38.25V306     C344.25,284.886,327.133,267.75,306,267.75z M306,0C137.279,0,0,137.279,0,306s137.279,306,306,306s306-137.279,306-306     S474.721,0,306,0z M306,554.625C168.893,554.625,57.375,443.088,57.375,306S168.893,57.375,306,57.375     c137.088,0,248.625,111.537,248.625,248.625S443.088,554.625,306,554.625z" fill="#444"/>
            </svg>
            Methods Info
        </h1>
        <p style="font-size:16px">Tracy Documentation: <a href="https://tracy.nette.org/" target="_blank">https://tracy.nette.org/</a></p>
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
            <a href="https://github.com/MattSkala/chrome-firelogger" target="_blank">FireLogger</a>
        </p>
        <p>
            <em>Firefox</em><br />
            <a href="http://www.getfirebug.com/" target="_blank">Firebug</a><br />
            <a href="http://firelogger.binaryage.com/" target="_blank">FireLogger</a><br />
        </p>
        ';

        return $out;
    }

}
