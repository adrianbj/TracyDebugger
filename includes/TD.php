<?php

use Tracy\Debugger;
use Tracy\Dumper;

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
        static::barDump($var, $title, $options);
        static::dump($var, $title, $options);
        static::fireLog($var);
        static::log($var);
    }

    /**
     * Tracy\Debugger::barDump() shortcut.
     * @tracySkipLocation
     */
    public static function barDump($var, $title = NULL, array $options = NULL) {
        if(self::tracyUnavailable()) return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }
        if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
            $options['maxDepth'] = $options[0];
            if(isset($options[1])) $options['maxLength'] = $options[1];
        }
        $options[Dumper::DEPTH] = isset($options['maxDepth']) ? $options['maxDepth'] : \TracyDebugger::getDataValue('maxDepth');
        $options[Dumper::TRUNCATE] = isset($options['maxLength']) ? $options['maxLength'] : \TracyDebugger::getDataValue('maxLength');
        $options[Dumper::LOCATION] = Debugger::$showLocation;
        static::dumpToBar($var, $title, $options);
    }

    /**
     * Tracy\Debugger::barDumpLive() shortcut with live dumping.
     * @tracySkipLocation
     */
    public static function barDumpLive($var, $title = NULL) {
        if(self::tracyUnavailable()) return false;
        $options[Dumper::DEPTH] = 99;
        $options[Dumper::TRUNCATE] = 999999;
        $options[Dumper::LOCATION] = Debugger::$showLocation;
        $options[Dumper::LIVE] = true;
        static::dumpToBar($var, $title, $options);
    }

    /**
     * Tracy\Debugger::barDumpBig() shortcut dumping with maxDepth = 6 and maxLength = 9999.
     * @tracySkipLocation
     */
    public static function barDumpBig($var, $title = NULL, array $options = NULL) {
        if(self::tracyUnavailable()) return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }
        if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
            $options['maxDepth'] = $options[0];
            if(isset($options[1])) $options['maxLength'] = $options[1];
        }
        $options[Dumper::DEPTH] = 6;
        $options[Dumper::TRUNCATE] = 9999;
        $options[Dumper::LOCATION] = Debugger::$showLocation;
        static::dumpToBar($var, $title, $options);
    }

    /**
     * Tracy\Debugger::dump() shortcut.
     * @tracySkipLocation
     */
    public static function dump($var, $title = NULL, array $options = NULL, $return = FALSE) {
        if(self::tracyUnavailable()) return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }
        if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
            $options['maxDepth'] = $options[0];
            if(isset($options[1])) $options['maxLength'] = $options[1];
        }
        $options[Dumper::DEPTH] = isset($options['maxDepth']) ? $options['maxDepth'] : \TracyDebugger::getDataValue('maxDepth');
        $options[Dumper::TRUNCATE] = isset($options['maxLength']) ? $options['maxLength'] : \TracyDebugger::getDataValue('maxLength');
        $options[Dumper::LOCATION] = \TracyDebugger::$fromConsole ? false : Debugger::$showLocation;
        if($title) echo '<h2>'.$title.'</h2>';
        echo static::generateDump($var, $options);
    }

    /**
     * Tracy\Debugger::dumpBig() shortcut dumping with maxDepth = 6 and maxLength = 9999.
     * @tracySkipLocation
     */
    public static function dumpBig($var, $title = NULL, array $options = NULL, $return = FALSE) {
        if(self::tracyUnavailable()) return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }
        if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
            $options['maxDepth'] = $options[0];
            if(isset($options[1])) $options['maxLength'] = $options[1];
        }
        $options[Dumper::DEPTH] = 6;
        $options[Dumper::TRUNCATE] = 9999;
        $options[Dumper::LOCATION] = \TracyDebugger::$fromConsole ? false : Debugger::$showLocation;
        if($title) echo '<h2>'.$title.'</h2>';
        echo static::generateDump($var, $options);
    }

    /**
     * Send content to dump bar
     * @tracySkipLocation
     */
    private static function dumpToBar($var, $title = NULL, array $options = NULL) {
        $dumpItem = array();
        $dumpItem['title'] = $title;
        $dumpItem['dump'] = static::generateDump($var, $options);
        array_push(\TracyDebugger::$dumpItems, $dumpItem);

        if(isset(\TracyDebugger::$showPanels) && in_array('dumpsRecorder', \TracyDebugger::$showPanels)) {
            $dumpsRecorderItems = wire('session')->tracyDumpsRecorderItems ?: array();
            array_push($dumpsRecorderItems, $dumpItem);
            wire('session')->tracyDumpsRecorderItems = $dumpsRecorderItems;
        }
    }

    /**
     * Generate debugInfo and Full Object tabbed output
     * @tracySkipLocation
     */
    private static function generateDump($var, $options) {

        // standard options for all dump/barDump variations
        $options[Dumper::COLLAPSE] = true;
        $options[Dumper::DEBUGINFO] = isset($options['debugInfo']) ? $options['debugInfo'] : \TracyDebugger::getDataValue('debugInfo');

        $out = '';
        if(count(\TracyDebugger::getDataValue('dumpPanelTabs')) > 0 && !is_string($var)) {
            $classExt = rand();
            $out .= '<ul class="dumpTabs">';
            foreach(\TracyDebugger::getDataValue('dumpPanelTabs') as $i => $panel) {
                $out .= '<li id="'.$panel.'Tab_'.$classExt.'"' . ($i == 0 ? 'class="active"' : '') . '><a href="javascript:void(0)" onclick="toggleDumpType(\''.$panel.'\', '.$classExt.')">'.\TracyDebugger::$dumpPanelTabs[$panel].'</a></li>';
            }
            $out .= '</ul>';
            if(($var instanceof Wire || $var instanceof \ProcessWire\Wire) && $var->id)   {
                if($var instanceof User || $var instanceof \ProcessWire\User) {
                    $type = 'users';
                    $section = 'access';
                }
                elseif($var instanceof Role || $var instanceof \ProcessWire\Role) {
                    $type = 'roles';
                    $section = 'access';
                }
                elseif($var instanceof Permission || $var instanceof \ProcessWire\Permission) {
                    $type = 'permissions';
                    $section = 'access';
                }
                elseif($var instanceof Language || $var instanceof \ProcessWire\Language) {
                    $type = 'languages';
                    $section = 'setup';
                }
                elseif($var instanceof Page || $var instanceof \ProcessWire\Page) {
                    $type = 'page';
                    $section = '';
                }
                elseif($var instanceof Template || $var instanceof \ProcessWire\Template) {
                    $type = 'template';
                    $section = 'setup';
                }
                elseif($var instanceof Field || $var instanceof \ProcessWire\Field) {
                    $type = 'field';
                    $section = 'setup';
                }

                if(isset($type)) $out .= self::generateEditLink($var, $type, $section);
            }

            if($var instanceof WireArray || $var instanceof \ProcessWire\WireArray) {
                $out .= '<span style="float:right; padding: 4px 6px 3px 6px;">n = ' . $var->count() . '</span>';
            }

            $out .= '
            <div style="clear:both">';
                foreach(\TracyDebugger::getDataValue('dumpPanelTabs') as $i => $panel) {
                    if($panel == 'debugInfo') {
                        $options[Dumper::DEBUGINFO] = true;
                    }
                    elseif($panel == 'fullObject') {
                        $options[Dumper::DEBUGINFO] = false;
                    }
                    else {
                        $options[Dumper::DEBUGINFO] = isset($options['debugInfo']) ? $options['debugInfo'] : \TracyDebugger::getDataValue('debugInfo');
                    }
                    $options[Dumper::COLLAPSE] = true;
                    $out .= '<div id="'.$panel.'_'.$classExt.'" class="tracyDumpTabs_'.$classExt.'"' . ($i==0 ? '' : ' style="display:none"') . '>'.Dumper::toHtml($panel == 'iterator' && method_exists($var, 'getIterator') ? $var->getIterator() : $var, $options).'</div>';
                }
            $out .= '</div>';
        }
        else {
            $out .= Dumper::toHtml($var, $options);
        }
        return $out;
    }

    /**
     * Generate edit link for various PW objects.
     * @tracySkipLocation
     */
    private static function generateEditLink($var, $type, $section) {
        return '<span style="float:right"><a href="' . wire('config')->urls->admin . $section . ($section ? '/' : '') . $type . '/edit/?id=' . $var->id . '" title="Edit ' . trim($type, 's') . ': ' . $var->name . '">#' . $var->id . '</a></span>';
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
        return \TracyDebugger::templateVars((array) $vars);
    }

    private static function has_string_keys(array $array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

}