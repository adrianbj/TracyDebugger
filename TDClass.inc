<?php

use Tracy\Debugger;

class TD {

	/**
	 * These are here so that they are available even when non-superuser or module not enabled so we
	 * don't get a undefined function error when calling these from a template file.
	 */

    public static function barDump($var, $title = NULL, array $options = NULL) {
        if(class_exists('\Tracy\Debugger')) return Debugger::barDump($var, $title, $options);
    }

    public static function dump($var, $return = FALSE) {
        if(class_exists('\Tracy\Debugger')) return Debugger::dump($var, $return);
    }

    public static function log($message, $priority = Debugger::INFO) {
        if(class_exists('\Tracy\Debugger')) return Debugger::log($message, $priority);
    }

    public static function timer($name = NULL) {
        if(class_exists('\Tracy\Debugger')) {
            $roundedTime = round(Debugger::timer($name),4);
            if($name) {
                return $name.' : '.$roundedTime;
            }
            else{
                return $roundedTime;
            }
        }
    }

    public static function fireLog($message = NULL) {
        if(class_exists('\Tracy\Debugger')) return Debugger::fireLog($message);
    }

}

