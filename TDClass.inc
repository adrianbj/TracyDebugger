<?php

use Tracy\Debugger;

class TD extends TracyDebugger {

	/**
	 * These are here so that they are available even when user is not allowed or module not enabled so we
	 * don't get a undefined function error when calling these from a template file.
	 */

    protected static function tracyUnavailable() {
        if(!\TracyDebugger::getDataValue('enabled') || !\TracyDebugger::allowedTracyUsers() || !class_exists('\Tracy\Debugger')) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Tracy\Debugger::debugAll() shortcut.
     * @tracySkipLocation
     */
    public static function debugAll($var, $title = NULL, array $options = NULL) {
        if(self::tracyUnavailable()) return false;
        Debugger::barDump($var, $title, $options);
        Debugger::dump($var);
        Debugger::fireLog($var);
        Debugger::log($var);
    }

    /**
     * Tracy\Debugger::barDump() shortcut.
     * @tracySkipLocation
     */
    public static function barDump($var, $title = NULL, array $options = NULL) {
        if(self::tracyUnavailable()) return false;
        return Debugger::barDump($var, $title, $options);
    }

    /**
     * Tracy\Debugger::dump() shortcut.
     * @tracySkipLocation
     */
    public static function dump($var, $return = FALSE) {
        if(self::tracyUnavailable()) return false;
        return Debugger::dump($var, $return);
    }

    /**
     * Tracy\Debugger::log() shortcut.
     * @tracySkipLocation
     */
    public static function log($message, $priority = Debugger::INFO) {
        if(self::tracyUnavailable()) return false;
        return Debugger::log($message, $priority);
    }

    /**
     * Tracy\Debugger::timer() shortcut.
     * @tracySkipLocation
     */
    public static function timer($name = NULL) {
        if(self::tracyUnavailable()) return false;
        $roundedTime = round(Debugger::timer($name),4);
        if($name) {
            return $name.' : '.$roundedTime;
        }
        else{
            return $roundedTime;
        }
    }

    /**
     * Tracy\Debugger::fireLog() shortcut.
     * @tracySkipLocation
     */
    public static function fireLog($message = NULL) {
        if(self::tracyUnavailable()) return false;
        return Debugger::fireLog($message);
    }

    /**
     * Zarganwar\PerformancePanel\Register::add() shortcut.
     * @tracySkipLocation
     */
    public static function addBreakpoint($name = null, $enforceParent = null) {
        if(self::tracyUnavailable() || !class_exists('\Zarganwar\PerformancePanel\Register')) return false;
        return Zarganwar\PerformancePanel\Register::add($name, $enforceParent);
    }

    /**
     * Template vars shortcut.
     * @tracySkipLocation
     */
    public static function templateVars($vars) {
        if(self::tracyUnavailable()) return false;
        return TracyDebugger::templateVars((array) $vars);
    }

}