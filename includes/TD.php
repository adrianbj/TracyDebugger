<?php

use Tracy\Debugger;
use Tracy\Dumper;

class TD extends TracyDebugger {

    // helper functions
    public static function sql($selector) {
        return wire('pages')->getPageFinder()->find(new Selectors($selector), array('returnVerbose' => true,'returnQuery' => true))->getDebugQuery();
    }

    public static function editLinks($pp) {
        return $pp->implode('<a href="{editUrl}">{title}</a><br />');
    }

    public static function viewLinks($pp) {
        return $pp->implode('<a href="{url}">{title}</a><br />');
    }


	/**
	 * These are here so that they are available even when user is not allowed or module not enabled so we
	 * don't get a undefined function error when calling these from a template file.
	 */

    protected static function tracyUnavailable() {
        if(!\TracyDebugger::getDataValue('enabled') || \TracyDebugger::$allowedTracyUser != 'development' || !class_exists('\Tracy\Debugger')) {
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
    public static function debugAll($var, $title = NULL, $options = []) {
        if(self::tracyUnavailable()) return false;
        static::barDump($var, $title, $options);
        static::dump($var, $title, $options);
        if(method_exists('Tracy\Debugger', 'getFireLogger')) {
            static::fireLog($var);
        }
        static::log($var);
    }

    /**
     * Tracy\Debugger::barEcho() shortcut.
     * @tracySkipLocation
     */
    public static function barEcho($str, $title = null) {
        if(self::tracyUnavailable() && !\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        static::dumpToBar($str, $title, null, true);
    }

    /**
     * Tracy\Debugger::barDump() shortcut.
     * @tracySkipLocation
     */
    public static function barDump($var, $title = NULL, $options = []) {
        if(self::tracyUnavailable() && !\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }
        if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
            if(isset($options[0])) $options['maxDepth'] = $options[0];
            if(isset($options[1])) $options['maxLength'] = $options[1];
            if(isset($options[2])) $options['maxItems'] = $options[2];
        }

        $options[Dumper::DEPTH] = isset($options['maxDepth']) ? $options['maxDepth'] : \TracyDebugger::getDataValue('maxDepth');
        $options[Dumper::TRUNCATE] = isset($options['maxLength']) ? $options['maxLength'] : \TracyDebugger::getDataValue('maxLength');
        if(defined('\Tracy\Dumper::ITEMS')) $options[Dumper::ITEMS] = isset($options['maxItems']) ? $options['maxItems'] : \TracyDebugger::getDataValue('maxItems');
        $options[Dumper::LOCATION] = Debugger::$showLocation;
        if(version_compare(PHP_VERSION, '7.2.0', '>=')) {
            $options[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
        }
        if(version_compare(Debugger::VERSION, '2.6.0', '>=')) $options[Dumper::LAZY] = true;
        static::dumpToBar($var, $title, $options);
    }


    /**
     * Tracy\Debugger::barDumpBig() shortcut dumping with maxDepth = 6, maxLength = 9999, and maxItems = 250.
     * @tracySkipLocation
     */
    public static function barDumpBig($var, $title = NULL, $options = []) {
        if(self::tracyUnavailable() && !\TracyDebugger::getDataValue('recordGuestDumps')) return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }
        if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
            if(isset($options[0])) $options['maxDepth'] = $options[0];
            if(isset($options[1])) $options['maxLength'] = $options[1];
            if(isset($options[2])) $options['maxItems'] = $options[2];
        }
        $options[Dumper::DEPTH] = 6;
        $options[Dumper::TRUNCATE] = 9999;
        if(defined('\Tracy\Dumper::ITEMS')) $options[Dumper::ITEMS] = 250;
        $options[Dumper::LOCATION] = Debugger::$showLocation;
        if(version_compare(PHP_VERSION, '7.2.0', '>=')) {
            $options[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
        }
        if(version_compare(Debugger::VERSION, '2.6.0', '>=')) $options[Dumper::LAZY] = true;
        static::dumpToBar($var, $title, $options);
    }

    /**
     * Tracy\Debugger::dump() shortcut.
     * @tracySkipLocation
     */
    public static function dump($var, $title = NULL, $options = []) {
        if(self::tracyUnavailable() && PHP_SAPI !== 'cli') return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }

        if(PHP_SAPI === 'cli') {
            echo $title . PHP_EOL;
            if(is_array($var) || is_object($var)) {
                print_r($var);
            }
            else {
                echo $var;
            }
            return false;
        }
        else {

            if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
                if(isset($options[0])) $options['maxDepth'] = $options[0];
                if(isset($options[1])) $options['maxLength'] = $options[1];
                if(isset($options[2])) $options['maxItems'] = $options[2];
            }
            $options[Dumper::DEPTH] = isset($options['maxDepth']) ? $options['maxDepth'] : \TracyDebugger::getDataValue('maxDepth');
            $options[Dumper::TRUNCATE] = isset($options['maxLength']) ? $options['maxLength'] : \TracyDebugger::getDataValue('maxLength');
            if(defined('\Tracy\Dumper::ITEMS')) {
                $options[Dumper::ITEMS] = isset($options['maxItems']) ? $options['maxItems'] : \TracyDebugger::getDataValue('maxItems');
            }
            $options[Dumper::LOCATION] = \TracyDebugger::$fromConsole ? false : Debugger::$showLocation;
            if(version_compare(PHP_VERSION, '7.2.0', '>=')) {
                $options[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
            }
            if(version_compare(Debugger::VERSION, '2.6.0', '>=')) $options[Dumper::LAZY] = false;
            echo '
            <div class="tracy-inner" style="height:auto !important">
                <div class="tracy-DumpPanel">';
            if($title) echo '<h2>'.$title.'</h2>';
            echo static::generateDump($var, $options) .
            '   </div>
            </div>';
        }
    }

    /**
     * Tracy\Debugger::dumpBig() shortcut dumping with maxDepth = 6, maxLength = 9999 and maxItems = 250.
     * @tracySkipLocation
     */
    public static function dumpBig($var, $title = NULL, $options = []) {
        if(self::tracyUnavailable() && PHP_SAPI !== 'cli') return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }

        if(PHP_SAPI === 'cli') {
            echo $title . PHP_EOL;
            if(is_array($var) || is_object($var)) {
                print_r($var);
            }
            else {
                echo $var;
            }
            return false;
        }
        else {

            if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
                if(isset($options[0])) $options['maxDepth'] = $options[0];
                if(isset($options[1])) $options['maxLength'] = $options[1];
                if(isset($options[2])) $options['maxItems'] = $options[2];
            }
            $options[Dumper::DEPTH] = 6;
            $options[Dumper::TRUNCATE] = 9999;
            if(defined('\Tracy\Dumper::ITEMS')) $options[Dumper::ITEMS] = 250;
            $options[Dumper::LOCATION] = \TracyDebugger::$fromConsole ? false : Debugger::$showLocation;
            if(version_compare(PHP_VERSION, '7.2.0', '>=')) {
                $options[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
            }
            if(version_compare(Debugger::VERSION, '2.6.0', '>=')) $options[Dumper::LAZY] = false;
            echo '
            <div class="tracy-inner" style="height:auto !important">
                <div class="tracy-DumpPanel">';
            if($title) echo '<h2>'.$title.'</h2>';
            echo static::generateDump($var, $options) .
            '   </div>
            </div>';
        }
    }

    /**
     * Send content to dump bar
     * @tracySkipLocation
     */
    private static function dumpToBar($var, $title = NULL, $options = [], $echo = false) {
        $dumpItem = array();
        $dumpItem['title'] = $title;
        $dumpItem['dump'] = $echo ? '<div class="tracy-echo">' . $var . '</div>' : static::generateDump($var, $options);
        array_push(\TracyDebugger::$dumpItems, $dumpItem);

        if((self::tracyUnavailable() && \TracyDebugger::getDataValue('recordGuestDumps')) || (isset(\TracyDebugger::$showPanels) && in_array('dumpsRecorder', \TracyDebugger::$showPanels))) {
            $dumpsFile = wire('config')->paths->cache . 'TracyDebugger/dumps.json';
            $dumpsRecorderItems = file_exists($dumpsFile) ? json_decode(file_get_contents($dumpsFile), true) : array();
            if(!$dumpsRecorderItems) $dumpsRecorderItems = array();
            array_push($dumpsRecorderItems, $dumpItem);
            wire('files')->filePutContents($dumpsFile, json_encode($dumpsRecorderItems));
        }
    }

    /**
     * Generate debugInfo and Full Object tabbed output
     * @tracySkipLocation
     */
    private static function generateDump($var, $options) {
        // standard options for all dump/barDump variations
        $options[Dumper::COLLAPSE] = isset($options['collapse']) ? $options['collapse'] : \TracyDebugger::getDataValue('collapse');
        $options[Dumper::COLLAPSE_COUNT] = isset($options['collapse_count']) ? $options['collapse_count'] : \TracyDebugger::getDataValue('collapse_count');
        $options[Dumper::DEBUGINFO] = isset($options['debugInfo']) ? $options['debugInfo'] : \TracyDebugger::getDataValue('debugInfo');

        $out = '<div style="margin: 0 0 10px 0">';

        $editCountLink = '';
        if(count(\TracyDebugger::getDataValue('dumpPanelTabs')) > 0 && !is_string($var)) {
            $classExt = rand();
            if(($var instanceof Wire || $var instanceof \ProcessWire\Wire)) {
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
                elseif($var instanceof Module || $var instanceof \ProcessWire\Module) {
                    $type = 'module';
                    $section = '';
                }

                if(isset($type)) $editCountLink .= self::generateEditViewLinks($var, $type, $section);
            }

            if($var instanceof WireArray || $var instanceof \ProcessWire\WireArray) {
                $editCountLink .= '<li class="tracyEditLinkCount">n = ' . $var->count() . '</li>';
            }

            $tabs = '<ul class="tracyDumpTabs">';
            $tabDivs = '<div style="clear:both; position:relative;">';
            $expandCollapseAll = is_string($var) || is_null($var) ? '' : '<span class="tracyDumpsToggler tracyDumpsExpander" onclick="tracyDumpsToggler(this, true)" title="Expand Level">+</span> <span class="tracyDumpsToggler tracyDumpsCollapser" onclick="tracyDumpsToggler(this, false)" title="Collapse All">–</span>';
            $numTabs = 0;
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
                $currentDump = $expandCollapseAll . Dumper::toHtml($panel == 'iterator' && is_object($var) && method_exists($var, 'getIterator') ? self::humanize($var->getIterator()) : $var, $options);
                if(!isset($lastDump) || (isset($lastDump) && $currentDump !== $lastDump)) {
                	$numTabs++;
                	$tabs .= '<li id="'.$panel.'Tab_'.$classExt.'"' . ($i == 0 ? 'class="active"' : '') . '><a href="javascript:void(0)" onclick="toggleDumpType(this, \''.$panel.'\', '.$classExt.')">'.\TracyDebugger::$dumpPanelTabs[$panel].'</a></li>';
                	$tabDivs .= '<div id="'.$panel.'_'.$classExt.'" class="tracyDumpTabs_'.$classExt.'"' . ($i==0 ? '' : ' style="display:none"') . '>'.$currentDump.'</div>';
                }
                $lastDump = $currentDump;

            }
            $tabs .= $editCountLink . '</ul>';
            $tabDivs .= '</div>';

            if($numTabs > 1) {
	            $out .= $tabs . $tabDivs;
	        }
	        else {
            	$out .= '<div style="clear:both; position:relative;">' . $lastDump . '</div>';
	        }

        }
        else {
            $out .= '<div style="clear:both; position:relative;">' . Dumper::toHtml($var, $options) . '</div>';
        }

        $out .= '</div>';

        return $out;
    }

    /**
     * Generate human readable datetime and user->id to name str.
     * @tracySkipLocation
     */
    private static function humanize($arrayObject) {
        if($arrayObject instanceof \ArrayObject) {
            if(isset($arrayObject['created'])) $arrayObject['created'] = $arrayObject['created'] . ' (' . date('Y-m-d H:i:s', $arrayObject['created']) . ')';
            if(isset($arrayObject['modified'])) $arrayObject['modified'] = $arrayObject['modified'] . ' (' . date('Y-m-d H:i:s', $arrayObject['modified']) . ')';
            if(isset($arrayObject['published'])) $arrayObject['published'] = $arrayObject['published'] . ' (' . date('Y-m-d H:i:s', $arrayObject['published']) . ')';
            if(isset($arrayObject['created_users_id'])) $arrayObject['created_users_id'] = $arrayObject['created_users_id'] . ' (' . wire('users')->get($arrayObject['created_users_id'])->name . ')';
            if(isset($arrayObject['modified_users_id'])) $arrayObject['modified_users_id'] = $arrayObject['modified_users_id'] . ' (' . wire('users')->get($arrayObject['modified_users_id'])->name . ')';
        }
        return $arrayObject;
    }

    /**
     * Generate edit link for various PW objects.
     * @tracySkipLocation
     */
    private static function generateEditViewLinks($var, $type, $section) {
        if($var->id == '' || $var->id === 0) {
            return;
        }
        else {
            return '<li style="float:right"><a href="' . wire('config')->urls->admin . $section . ($section ? '/' : '') . $type . '/edit/?' . ($type == 'module' ? 'name=' . $var->className : 'id=' . $var->id) . '" title="Edit ' . trim($type, 's') . ': ' . $var->name . '">#' . ($type=='module' ? $var->className : $var->id) . '</a>' . ($type == 'page' && $var->viewable() ? '<a href="' . $var->url . '" title="View ' . trim($type, 's') . ': ' . $var->path . '" class="' . (method_exists($var, 'hasStatus') && $var->hasStatus('unpublished') ? 'pageUnpublished' : '') . (method_exists($var, 'hasStatus') && $var->hasStatus('hidden') ? 'pageHidden' : '') . '">' : '<span class="pageTitle">') . (($var->get('title|name') && strlen($var->get('title|name')) > 20) ? substr($var->get('title|name'),0,19).'…' : $var->get('title|name')) . ($type == 'page' && $var->viewable() ? '</a>' : '</span>') . '</li>';
        }
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
        if(!method_exists('Tracy\Debugger', 'getFireLogger') || self::tracyUnavailable()) return false;
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

    /**
     * check if array has string keys
     *
     * @param array $array
     * @return bool
     */
    private static function has_string_keys(array $array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

}
