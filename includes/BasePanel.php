<?php

use Tracy\IBarPanel;

abstract class BasePanel extends WireData implements IBarPanel {

    function loadResources() {

        // if legacy version we need to check css is loaded in panels because it doesn't have the Debugger::$customCssFiles option
        if (\TracyDebugger::$tracyVersion == 'legacy') {
            $cssUrl = $this->wire('config')->urls->TracyDebugger . 'styles.css';
            return '
            <script>
                function loadCSSIfNotAlreadyLoaded() {
                    if(!document.getElementById("tracyStyles")) {
                        var link = document.createElement("link");
                        link.rel = "stylesheet";
                        link.href = "' . $cssUrl . '";
                        document.getElementsByTagName("head")[0].appendChild(link);
                    }
                }
                loadCSSIfNotAlreadyLoaded();
            </script>
            ';
        } else {
            return '';
        }
    }

}