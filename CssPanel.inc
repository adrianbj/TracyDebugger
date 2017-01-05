<?php

use Tracy\IBarPanel;

/**
 * CSS panel
 */
class CssPanel implements IBarPanel {

    public function getTab() {
        return '<style>' . file_get_contents(wire('config')->paths->siteModules.'TracyDebugger/styles.css') . '</style>';
    }

    public function getPanel() {
        return false;
    }
}