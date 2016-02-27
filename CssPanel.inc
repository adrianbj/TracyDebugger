<?php

use Tracy\IBarPanel;

/**
 * CSS panel
 */

class CssPanel implements IBarPanel {
    public function getTab() {
        return '
            <style>
                .tracy-panel {
                    max-height: 90% !important;
                    max-width: 95% !important;
                }

                .tracy-panel h2 {
                    font-size: 20px !important;
                }

                .tracy-inner {
                    max-height: 768px !important;
                    max-width: 1024px !important;
                }

                .tracy-toggle {
                    font-size: 15px !important;
                }

                /*#tracy-debug-panel-ProcesswireLogsPanel {
                    bottom: 30px;
                    left: 20px;
                    min-height: 90% !important;
                    min-width: 95% !important;
                }

                #tracy-debug-panel-TracyLogsPanel {
                    bottom: 30px;
                    left: 20px;
                    min-height: 90% !important;
                    min-width: 95% !important;
                }*/

                .pw-admin-links a {
                    transition: transform 0.12s;
                }
                .pw-admin-links a:hover svg {
                    transform: scale(1.4);
                }
                .pw-admin-links a:hover, .pw-admin-links a:active, .pw-admin-links a:focus {
                    background-color: transparent !important;
                }
            </style>
        ';
    }



    public function getPanel() {
        return false;
    }

}