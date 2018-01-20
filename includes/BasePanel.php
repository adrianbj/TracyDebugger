<?php

use Tracy\IBarPanel;

abstract class BasePanel extends WireData implements IBarPanel {

    function loadResources() {
        // currently not being used, but keep for possible future application
    }

}