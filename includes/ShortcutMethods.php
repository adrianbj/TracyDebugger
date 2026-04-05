<?php

/**
 * short alias methods for lazy typists :)
 *
 * These are defined in the global namespace so they work from both
 * ProcessWire-namespaced and non-namespaced template files and modules.
 * PHP's function resolution falls back from any namespace to global.
 */

function tracyUnavailable() {
    if(!\ProcessWire\TracyDebugger::getDataValue('enabled') || \ProcessWire\TracyDebugger::$allowedTracyUser != 'development' || !class_exists('ProcessWire\TD')) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * TD::debugAll() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('debugAll') && in_array('debugAll', $this->data['enabledShortcutMethods'])) {
    function debugAll($var, $title = NULL, $options = []) {
        if(tracyUnavailable()) return false;
        return \ProcessWire\TD::debugAll($var, $title, $options);
    }
}

/**
 * TD::barDump() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('barDump') && in_array('barDump', $this->data['enabledShortcutMethods'])) {
    function barDump($var, $title = NULL, $options = []) {
        if(tracyUnavailable() && !\ProcessWire\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        return \ProcessWire\TD::barDump($var, $title, $options);
    }
}

/**
 * TD::barDump() shortcut dumping with maxDepth = 6, maxLength = 999, and maxItems = 250.
 * @tracySkipLocation
 */
if(!function_exists('barDumpBig') && in_array('barDumpBig', $this->data['enabledShortcutMethods'])) {
    function barDumpBig($var, $title = NULL) {
        if(tracyUnavailable() && !\ProcessWire\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        return \ProcessWire\TD::barDumpBig($var, $title);
    }
}

/**
 * TD::dump() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('dump') && in_array('dump', $this->data['enabledShortcutMethods'])) {
    function dump($var, $title = NULL, $options = []) {
        if(tracyUnavailable() && PHP_SAPI !== 'cli') return false;
        return \ProcessWire\TD::dump($var, $title, $options);
    }
}

/**
 * TD::dump() shortcut dumping with maxDepth = 6, maxLength = 999, and maxItems = 250.
 * @tracySkipLocation
 */
if(!function_exists('dumpBig') && in_array('dumpBig', $this->data['enabledShortcutMethods'])) {
    function dumpBig($var, $title = NULL, $options = []) {
        if(tracyUnavailable() && PHP_SAPI !== 'cli') return false;
        return \ProcessWire\TD::dumpBig($var, $title, $options);
    }
}

/**
 * TD::barEcho() shortcut echo to bar dumps panel.
 * @tracySkipLocation
 */
if(!function_exists('barEcho') && in_array('barEcho', $this->data['enabledShortcutMethods'])) {
    function barEcho($str, $title = NULL) {
        if(tracyUnavailable() && !\ProcessWire\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        return \ProcessWire\TD::barEcho($str, $title);
    }
}

/**
 * TD::timer() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('timer') && in_array('timer', $this->data['enabledShortcutMethods'])) {
    function timer($name = NULL) {
        if(tracyUnavailable()) return false;
        return \ProcessWire\TD::timer($name);
    }
}

/**
 * TD::addBreakpoint() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('addBreakpoint') && in_array('addBreakpoint', $this->data['enabledShortcutMethods'])) {
    function addBreakpoint($name = null, $enforceParent = null) {
        if(tracyUnavailable() || !class_exists('\Zarganwar\PerformancePanel\Register')) return false;
        return \ProcessWire\TD::addBreakpoint($name, $enforceParent);
    }
}

/**
 * TD::templateVars() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('templateVars') && in_array('templateVars', $this->data['enabledShortcutMethods'])) {
    function templateVars($vars) {
        if(tracyUnavailable()) return false;
        return \ProcessWire\TD::templateVars($vars);
    }
}

/**
 * really short alias methods for really lazy typists :)
 */

/**
 * TD::debugAll() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('da') && in_array('da', $this->data['enabledShortcutMethods'])) {
    function da($var, $title = NULL, $options = []) {
        if(tracyUnavailable()) return false;
        return \ProcessWire\TD::debugAll($var, $title, $options);
    }
}

/**
 * TD::barDump() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('bd') && in_array('bd', $this->data['enabledShortcutMethods'])) {
    function bd($var, $title = NULL, $options = []) {
        if(tracyUnavailable() && !\ProcessWire\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        return \ProcessWire\TD::barDump($var, $title, $options);
    }
}

/**
 * TD::barDump() shortcut dumping with maxDepth = 6, maxLength = 999, and maxItems = 250.
 * @tracySkipLocation
 */
if(!function_exists('bdb') && in_array('bdb', $this->data['enabledShortcutMethods'])) {
    function bdb($var, $title = NULL) {
        if(tracyUnavailable() && !\ProcessWire\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        return \ProcessWire\TD::barDumpBig($var, $title);
    }
}

/**
 * TD::dump() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('d') && in_array('d', $this->data['enabledShortcutMethods'])) {
    function d($var, $title = NULL, $options = []) {
        if(tracyUnavailable() && PHP_SAPI !== 'cli') return false;
        return \ProcessWire\TD::dump($var, $title, $options);
    }
}

/**
 * TD::dump() shortcut dumping with maxDepth = 6, maxLength = 999, and maxItems = 250.
 * @tracySkipLocation
 */
if(!function_exists('db') && in_array('db', $this->data['enabledShortcutMethods'])) {
    function db($var, $title = NULL, $options = []) {
        if(tracyUnavailable() && PHP_SAPI !== 'cli') return false;
        return \ProcessWire\TD::dumpBig($var, $title, $options);
    }
}

/**
 * TD::barEcho() shortcut echo to bar dumps panel.
 * @tracySkipLocation
 */
if(!function_exists('be') && in_array('be', $this->data['enabledShortcutMethods'])) {
    function be($str, $title = NULL) {
        if(tracyUnavailable() && !\ProcessWire\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        return \ProcessWire\TD::barEcho($str, $title);
    }
}

/**
 * TD::log() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('l') && in_array('l', $this->data['enabledShortcutMethods'])) {
    function l($message, $priority = 'info') {
        if(tracyUnavailable()) return false;
        return \ProcessWire\TD::log($message, $priority);
    }
}

/**
 * TD::timer() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('t') && in_array('t', $this->data['enabledShortcutMethods'])) {
    function t($name = NULL) {
        if(tracyUnavailable()) return false;
        return \ProcessWire\TD::timer($name);
    }
}

/**
 * TD::addBreakpoint() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('bp') && in_array('bp', $this->data['enabledShortcutMethods'])) {
    function bp($name = null, $enforceParent = null) {
        if(tracyUnavailable() || !class_exists('\Zarganwar\PerformancePanel\Register')) return false;
        return \ProcessWire\TD::addBreakpoint($name, $enforceParent);
    }
}

/**
 * TD::templateVars() shortcut.
 * @tracySkipLocation
 */
if(!function_exists('tv') && in_array('tv', $this->data['enabledShortcutMethods'])) {
    function tv($vars) {
        if(tracyUnavailable()) return false;
        return \ProcessWire\TD::templateVars($vars);
    }
}
