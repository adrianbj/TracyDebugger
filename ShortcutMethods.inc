<?php

use Tracy\Debugger;

/**
 * short alias methods for lazy typists :)
 */


/**
 * TD::barDump() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('barDump') && in_array('barDump', $this->data['enabledShortcutMethods'])) {
    function barDump($var, $title = NULL, array $options = NULL) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::barDump($var, $title, $options);
    }
}

/**
 * TD::dump() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('dump') && in_array('dump', $this->data['enabledShortcutMethods'])) {
    function dump($var, $return = FALSE) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::dump($var, $return);
    }
}

/**
 * TD::timer() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('timer') && in_array('timer', $this->data['enabledShortcutMethods'])) {
    function timer($name = NULL) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::timer($name);
    }
}

/**
 * TD::fireLog() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('fireLog') && in_array('fireLog', $this->data['enabledShortcutMethods'])) {
    function fireLog($message = NULL) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::fireLog($message);
    }
}



/**
 * really short alias methods for really lazy typists :)
 */

/**
 * TD::barDump() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('bd') && in_array('bd', $this->data['enabledShortcutMethods'])) {
    function bd($var, $title = NULL, array $options = NULL) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::barDump($var, $title, $options);
    }
}

/**
 * TD::dump() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('d') && in_array('d', $this->data['enabledShortcutMethods'])) {
    function d($var, $return = FALSE) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::dump($var, $return);
    }
}

/**
 * TD::log() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('l') && in_array('l', $this->data['enabledShortcutMethods'])) {
    function l($message, $priority = Debugger::INFO) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::log($message, $priority);
    }
}

/**
 * TD::timer() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('t') && in_array('t', $this->data['enabledShortcutMethods'])) {
    function t($name = NULL) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::timer($name);
    }
}

/**
 * TD::fireLog() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('fl') && in_array('fl', $this->data['enabledShortcutMethods'])) {
    function fl($message = NULL) {
        if(!wire('user')->isSuperuser() || !class_exists('TD')) return false;
        return TD::fireLog($message);
    }
}
