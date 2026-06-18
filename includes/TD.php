<?php namespace ProcessWire;

use Tracy\Debugger;
use Tracy\Dumper;

class TD extends TracyDebugger {

    private static $phpSupportsKeysToHide;
    private static $tracySupportsLazy;
    private static $recorderPendingItems = array();
    private static $recorderShutdownRegistered = false;

    // bound dumps.json so a long-lived recorder session (or a high-traffic AJAX
    // endpoint) can't grow it without limit and exhaust memory on the next flush
    const RECORDER_MAX_ITEMS = 200;
    // if an existing file is already pathologically large (e.g. from before this
    // cap existed), discard it rather than decoding it into memory and OOMing
    const RECORDER_MAX_BYTES = 5242880;

    private static function phpSupportsKeysToHide() {
        if(self::$phpSupportsKeysToHide === null) {
            self::$phpSupportsKeysToHide = version_compare(PHP_VERSION, '7.2.0', '>=');
        }
        return self::$phpSupportsKeysToHide;
    }

    private static function tracySupportsLazy() {
        if(self::$tracySupportsLazy === null) {
            self::$tracySupportsLazy = version_compare(Debugger::VERSION, '2.6.0', '>=');
        }
        return self::$tracySupportsLazy;
    }

    private static $tracySupportsAgent;
    private static function tracySupportsAgent() {
        if(self::$tracySupportsAgent === null) {
            self::$tracySupportsAgent = method_exists('\Tracy\Helpers', 'isAgent')
                && method_exists('\Tracy\Helpers', 'consoleLog');
        }
        return self::$tracySupportsAgent;
    }

    /**
     * Render the variable as plaintext for the agent path: same Tracy::toText
     * pipeline the bar dump uses (including __debugInfo() / KEYS_TO_HIDE) so
     * the agent sees the same hooks/data/Wire metadata as the visible dump,
     * then run AIExport::scrub() to redact patterns Tracy's exact-key
     * KEYS_TO_HIDE doesn't catch (emails, IPs, JWTs, AWS keys, etc.).
     * @tracySkipLocation
     */
    private static function agentText($var) {
        $opts = array();
        $opts[Dumper::DEPTH] = TracyDebugger::getDataValue('maxDepth');
        $opts[Dumper::TRUNCATE] = TracyDebugger::getDataValue('maxLength');
        if(defined('\\Tracy\\Dumper::ITEMS')) {
            $opts[Dumper::ITEMS] = TracyDebugger::getDataValue('maxItems');
        }
        $opts[Dumper::DEBUGINFO] = TracyDebugger::getDataValue('debugInfo');
        if(self::phpSupportsKeysToHide()) {
            $opts[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
        }
        try {
            $text = Dumper::toText($var, $opts);
        } catch(\Throwable $e) {
            return null;
        }
        $header = self::agentCallHeader();
        if($header !== '') $text = $header . $text;
        return AIExport::scrub($text);
    }

    /**
     * Build a "// bd($x) at site/templates/home.php:42" header using Tracy's
     * own caller resolver so the location matches the dump panel's footer.
     * @tracySkipLocation
     */
    private static function agentCallHeader() {
        if(!class_exists('\\Tracy\\Helpers') || !method_exists('\\Tracy\\Helpers', 'findCallerLocation')) {
            return '';
        }
        $location = \Tracy\Helpers::findCallerLocation();
        if(!$location || empty($location['file']) || empty($location['line'])) return '';
        $file = $location['file'];
        $line = $location['line'];
        $code = '';
        $lines = @file($file);
        if($lines && isset($lines[$line - 1])) {
            $sourceLine = trim($lines[$line - 1]);
            if(preg_match('#(?:[\\\\\w]+::)?(?:d|bd|dump|dumpBig|barDump|barDumpBig|debugAll)\s*\(.*\)\s*;?#i', $sourceLine, $m)) {
                $code = $m[0];
            } else {
                $code = $sourceLine;
            }
        }
        if($code === '') return '';
        $rootPath = wire('config')->paths->root;
        if($rootPath && strpos($file, $rootPath) === 0) {
            $file = substr($file, strlen($rootPath));
        }
        return "Call: " . $code . "\nLocation: " . $file . ':' . $line . "\n\n";
    }

    /**
     * Mirror Tracy 2.12's d-to-console-when-agent-detected behavior for our custom dump path.
     * @tracySkipLocation
     */
    private static function agentDumpToConsole($var, $title = null) {
        if(!self::tracySupportsAgent()) return;
        if(!\Tracy\Helpers::isAgent()) return;
        $text = self::agentText($var);
        if($text === null || $text === '') return;
        \Tracy\Helpers::consoleLog(($title ? $title . ":\n" : '') . $text);
    }

    public static function flushRecorderDumps() {
        if(empty(self::$recorderPendingItems)) return;
        $dumpsFile = wire('config')->paths->cache . 'TracyDebugger/dumps.json';
        $existing = (file_exists($dumpsFile) && filesize($dumpsFile) <= self::RECORDER_MAX_BYTES)
            ? json_decode(file_get_contents($dumpsFile), true)
            : array();
        if(!is_array($existing)) $existing = array();
        $nextId = 0;
        foreach($existing as $item) {
            if(isset($item['id']) && $item['id'] > $nextId) $nextId = $item['id'];
        }
        foreach(self::$recorderPendingItems as &$item) {
            $item['id'] = ++$nextId;
        }
        unset($item);
        $merged = array_merge($existing, self::$recorderPendingItems);
        self::$recorderPendingItems = array();
        if(count($merged) > self::RECORDER_MAX_ITEMS) {
            $merged = array_slice($merged, -self::RECORDER_MAX_ITEMS);
        }
        wire('files')->filePutContents($dumpsFile, json_encode($merged), LOCK_EX);
    }

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
        if(!TracyDebugger::getDataValue('enabled') || TracyDebugger::$allowedTracyUser != 'development' || !class_exists('\Tracy\Debugger')) {
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
        static::log($var);
    }

    /**
     * Tracy\Debugger::barEcho() shortcut.
     * @tracySkipLocation
     */
    public static function barEcho($str, $title = null) {
        if(self::tracyUnavailable() && !TracyDebugger::getDataValue('recordGuestDumps')) return false;
        static::dumpToBar($str, $title, null, true);
    }

    /**
     * Build a "Copy MD" button + sibling JSON payload for the rendered dump,
     * mirroring the Exceptions panel's Agent Markdown copy pattern. Click
     * handler lives in scripts/main.js (matches [data-tracy-md-copy]).
     * Absolutely positioned at top-right of a position:relative parent, to the
     * right of the +/- togglers (which are shifted left in styles.css to make room).
     * @tracySkipLocation
     */
    private static function buildAgentCopyButton($var, $title = null) {
        $text = self::agentText($var);
        if($text === null || $text === '') return '';
        if($title !== null && $title !== '') $text = strip_tags($title) . ":\n" . $text;
        $jsonMd = str_replace('</', '<\\/', \Tracy\Helpers::jsonEncode($text, true));
        $btnStyle = 'position:absolute;top:6px;right:8px;padding:0;line-height:0;cursor:pointer;background:transparent;border:0;color:#888;opacity:0.6;z-index:1;';
        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>';
        return '<span class="tracy-dump-copy-wrap">'
             . '<button type="button" data-tracy-md-copy class="tracy-md-copy-btn" style="' . $btnStyle . '" title="Copy this dump as plaintext for an AI agent">' . $icon . '</button>'
             . '<script type="application/json" data-tracy-md-source>' . $jsonMd . '</script>'
             . '</span>';
    }

    /**
     * Build a "Copy all" button that copies every dump in its scope as agent
     * markdown. Click handler lives in scripts/main.js (matches
     * [data-tracy-md-copy-all]); it determines scope from the button's DOM
     * position (the enclosing .tracy-panel, or the page for inline d() dumps).
     * @tracySkipLocation
     */
    public static function buildAgentCopyAllButton() {
        $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>';
        return '<button type="button" data-tracy-md-copy-all class="tracy-md-copy-all-btn" title="Copy all dumps as plaintext for an AI agent">' . $icon . '<span>Copy all</span></button>';
    }

    /**
     * Tracy\Debugger::barDump() shortcut.
     * @tracySkipLocation
     */
    public static function barDump($var, $title = NULL, $options = []) {
        if(self::tracyUnavailable() && !TracyDebugger::getDataValue('recordGuestDumps')) return false;
        if(is_array($title)) {
            $options = $title;
            $title = NULL;
        }
        if(isset($options) && is_array($options) && !static::has_string_keys($options)) {
            if(isset($options[0])) $options['maxDepth'] = $options[0];
            if(isset($options[1])) $options['maxLength'] = $options[1];
            if(isset($options[2])) $options['maxItems'] = $options[2];
        }
        $options[Dumper::DEPTH] = isset($options['maxDepth']) ? $options['maxDepth'] : TracyDebugger::getDataValue('maxDepth');
        $options[Dumper::TRUNCATE] = isset($options['maxLength']) ? $options['maxLength'] : TracyDebugger::getDataValue('maxLength');
        if(defined('\Tracy\Dumper::ITEMS')) $options[Dumper::ITEMS] = isset($options['maxItems']) ? $options['maxItems'] : TracyDebugger::getDataValue('maxItems');
        $options[Dumper::LOCATION] = Debugger::$showLocation;
        if(self::phpSupportsKeysToHide()) {
            $options[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
        }
        if(self::tracySupportsLazy()) $options[Dumper::LAZY] = true;
        static::dumpToBar($var, $title, $options);
    }

    /**
     * Tracy\Debugger::barDumpBig() shortcut dumping with maxDepth = 6, maxLength = 9999, and maxItems = 250.
     * @tracySkipLocation
     */
    public static function barDumpBig($var, $title = NULL, $options = []) {
        if(self::tracyUnavailable() && !TracyDebugger::getDataValue('recordGuestDumps')) return false;
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
        if(self::phpSupportsKeysToHide()) {
            $options[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
        }
        if(self::tracySupportsLazy()) $options[Dumper::LAZY] = true;
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
            $options[Dumper::DEPTH] = isset($options['maxDepth']) ? $options['maxDepth'] : TracyDebugger::getDataValue('maxDepth');
            $options[Dumper::TRUNCATE] = isset($options['maxLength']) ? $options['maxLength'] : TracyDebugger::getDataValue('maxLength');
            if(defined('\Tracy\Dumper::ITEMS')) {
                $options[Dumper::ITEMS] = isset($options['maxItems']) ? $options['maxItems'] : TracyDebugger::getDataValue('maxItems');
            }
            $options[Dumper::LOCATION] = TracyDebugger::$fromConsole ? false : Debugger::$showLocation;
            if(self::phpSupportsKeysToHide()) {
                $options[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
            }
            if(self::tracySupportsLazy()) $options[Dumper::LAZY] = true;
            echo '
            <div class="tracy-inner" style="height:auto !important">
                <div class="tracy-DumpPanel">';
            if($title) echo '<h2>'.$title.'</h2>';
            echo static::safeGenerateDump($var, $options, $title) .
            '   </div>
            </div>';
            try { static::agentDumpToConsole($var, $title); } catch(\Throwable $e) { try { self::log($e); } catch(\Throwable $logErr) {} }
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
            $options[Dumper::LOCATION] = TracyDebugger::$fromConsole ? false : Debugger::$showLocation;
            if(self::phpSupportsKeysToHide()) {
                $options[Dumper::KEYS_TO_HIDE] = Debugger::$keysToHide;
            }
            if(self::tracySupportsLazy()) $options[Dumper::LAZY] = true;
            echo '
            <div class="tracy-inner" style="height:auto !important">
                <div class="tracy-DumpPanel">';
            if($title) echo '<h2>'.$title.'</h2>';
            echo static::safeGenerateDump($var, $options, $title) .
            '   </div>
            </div>';
            try { static::agentDumpToConsole($var, $title); } catch(\Throwable $e) { try { self::log($e); } catch(\Throwable $logErr) {} }
        }
    }

    /**
     * Wrap generateDump() so a throwable during dump rendering doesn't break
     * the surrounding HTML structure (or abort the request) for d()/db().
     * The error is logged and rendered inline ABOVE a best-effort fallback
     * dump so the user sees both the failure and at least a shallow view of
     * the value.
     * @tracySkipLocation
     */
    private static function safeGenerateDump($var, $options, $title = null) {
        // capture non-fatal PHP errors fired during the dump pipeline (e.g. a
        // getimagesize warning inside __debugInfo) so they can be deduped and
        // rendered as styled blocks above the dump instead of echoing raw text
        // at random positions in the active output buffer. Fatal-equivalent
        // severities are delegated back to the previously installed handler.
        $capturedWarnings = [];
        $prevHandler = null;
        $prevHandler = set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$capturedWarnings, &$prevHandler) {
            if(!(error_reporting() & $errno)) return false;
            if(in_array($errno, [E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
                return is_callable($prevHandler) ? call_user_func($prevHandler, $errno, $errstr, $errfile, $errline) : false;
            }
            $key = $errstr . '|' . $errfile . '|' . $errline;
            if(!isset($capturedWarnings[$key])) {
                $capturedWarnings[$key] = ['message' => $errstr, 'file' => $errfile, 'line' => $errline];
            }
            return true;
        });

        try {
            $dumpHtml = static::generateDump($var, $options, $title);
        }
        catch(\Throwable $e) {
            try { self::log($e); } catch(\Throwable $logErr) {}
            $dumpHtml = static::renderDumpError($e) . static::fallbackDump($var);
        }
        finally {
            restore_error_handler();
        }

        // log each unique warning so Tracy's error log still records them
        foreach($capturedWarnings as $w) {
            try { self::log('Warning: ' . $w['message'] . ' on line: ' . $w['line'] . ' in ' . $w['file'], 'error'); } catch(\Throwable $e) {}
        }

        $warningHtml = '';
        foreach($capturedWarnings as $w) {
            $warningHtml .= static::renderInlineError('Warning', $w['message'], $w['file'], $w['line']);
        }
        return $warningHtml . $dumpHtml;
    }

    /**
     * Render a Throwable as a styled red block (in its own grey container,
     * matching the visual treatment Tracy gives the dump that follows).
     * @tracySkipLocation
     */
    private static function renderDumpError(\Throwable $e) {
        return static::renderInlineError(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     * Shared styled-block renderer for inline error/warning messages shown
     * above a dump. Matches the grey-bordered + red-left-accent treatment.
     * @tracySkipLocation
     */
    private static function renderInlineError($type, $message, $file = null, $line = null) {
        $location = ($file !== null && $line !== null)
            ? ' <em style="color:#666">in ' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . ':' . (int)$line . '</em>'
            : '';
        return '<div style="border: 1px solid #d9d9d9; border-left: 3px solid #c00; padding: 6px 10px; background: #fff3f3; color: #c00; font-family: monospace; white-space: normal; margin-bottom: 5px;">'
            . '<strong>' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . ':</strong> '
            . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
            . $location
            . '</div>';
    }

    /**
     * Best-effort dump used when the full generateDump() pipeline throws. Disables
     * __debugInfo (the most common source of the original throw) and falls back to
     * reflection-based traversal at the user's configured depth.
     * @tracySkipLocation
     */
    private static function fallbackDump($var) {
        try {
            $opts = [
                Dumper::DEPTH => TracyDebugger::getDataValue('maxDepth'),
                Dumper::TRUNCATE => TracyDebugger::getDataValue('maxLength'),
                Dumper::COLLAPSE => TracyDebugger::getDataValue('collapse'),
                Dumper::LOCATION => false,
                Dumper::DEBUGINFO => false,
            ];
            if(defined('\Tracy\Dumper::ITEMS')) $opts[Dumper::ITEMS] = TracyDebugger::getDataValue('maxItems');
            if(self::tracySupportsLazy()) $opts[Dumper::LAZY] = true;
            return Dumper::toHtml($var, $opts);
        }
        catch(\Throwable $e) {
            try { self::log($e); } catch(\Throwable $logErr) {}
            return '<div style="color:#666; font-style:italic;">(unable to render fallback dump: '
                . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . ')</div>';
        }
    }

    /**
     * Send content to dump bar
     * @tracySkipLocation
     */
    private static function dumpToBar($var, $title = NULL, $options = [], $echo = false) {
        $dumpItem = array();
        $dumpItem['title'] = $title;
        $dumpItem['dump'] = $echo ? '<div class="tracy-echo">' . $var . '</div>' : static::safeGenerateDump($var, $options, $title);
        $dumpItem['text'] = null;
        if(!$echo && self::tracySupportsAgent() && \Tracy\Helpers::isAgent()) {
            try {
                $text = self::agentText($var);
                if($text !== null && $text !== '' && $title !== null && $title !== '') $text = strip_tags($title) . ":\n" . $text;
                $dumpItem['text'] = $text;
            } catch(\Throwable $e) { try { self::log($e); } catch(\Throwable $logErr) {} }
        }
        TracyDebugger::$dumpItems[] = $dumpItem;
        // only persist to dumps.json when something will actually consume it:
        //  - dev users: the Dumps Recorder panel is active for this request. cross-window
        //    polling shares the sticky panel cookie, so the dev user's AJAX requests see it too.
        //  - guests: recordGuestDumps is a global config flag, so it doesn't depend on a panel
        //    or cookie (guests have neither). enabling it auto-opens the recorder panel for the
        //    dev user who later views the dumps, so there is still a consumer.
        // without one of these there's no panel to update and no reason to write the file.
        if((!self::tracyUnavailable() && is_array(TracyDebugger::$showPanels) && in_array('dumpsRecorder', TracyDebugger::$showPanels))
            || TracyDebugger::getDataValue('recordGuestDumps')) {
            $dumpItem['user'] = wire('user')->name;
            $dumpItem['time'] = date('Y-m-d H:i:s');
            self::$recorderPendingItems[] = $dumpItem;
            if(!self::$recorderShutdownRegistered) {
                self::$recorderShutdownRegistered = true;
                register_shutdown_function(array(__CLASS__, 'flushRecorderDumps'));
            }
        }
    }

    /**
     * Generate debugInfo and Full Object tabbed output
     * @tracySkipLocation
     */
    private static function generateDump($var, $options, $title = null) {
        // standard options for all dump/barDump variations
        $options[Dumper::COLLAPSE] = isset($options['collapse']) ? $options['collapse'] : TracyDebugger::getDataValue('collapse');
        $options[Dumper::COLLAPSE_COUNT] = isset($options['collapse_count']) ? $options['collapse_count'] : TracyDebugger::getDataValue('collapse_count');
        $options[Dumper::DEBUGINFO] = isset($options['debugInfo']) ? $options['debugInfo'] : TracyDebugger::getDataValue('debugInfo');

        $out = '<div style="margin: 0 0 10px 0">';

        $editCountLink = '';
        if(count(TracyDebugger::getDataValue('dumpPanelTabs')) > 0 && !is_string($var)) {
            $classExt = uniqid();
            if($var instanceof Wire) {
                if($var instanceof User) {
                    $type = 'users';
                    $section = 'access';
                }
                elseif($var instanceof Role) {
                    $type = 'roles';
                    $section = 'access';
                }
                elseif($var instanceof Permission) {
                    $type = 'permissions';
                    $section = 'access';
                }
                elseif($var instanceof Language) {
                    $type = 'languages';
                    $section = 'setup';
                }
                elseif($var instanceof Page) {
                    $type = 'page';
                    $section = '';
                }
                elseif($var instanceof Template) {
                    $type = 'template';
                    $section = 'setup';
                }
                elseif($var instanceof Field) {
                    $type = 'field';
                    $section = 'setup';
                }
                elseif($var instanceof Module) {
                    $type = 'module';
                    $section = '';
                }
                if(isset($type)) $editCountLink .= self::generateEditViewLinks($var, $type, $section);
            }
            if($var instanceof WireArray) {
                $editCountLink .= '<li class="tracyEditLinkCount">n = ' . $var->count() . '</li>';
            }
            $tabs = '<ul class="tracyDumpTabs">';
            $tabDivs = '<div style="clear:both; position:relative;">';
            $expandCollapseAll = is_string($var) || is_null($var) ? '' : '<span class="tracyDumpsToggler tracyDumpsExpander" data-dumps-toggle="expand" title="Expand Level">+</span> <span class="tracyDumpsToggler tracyDumpsCollapser" data-dumps-toggle="collapse" title="Collapse All">–</span>';
            $numTabs = 0;
            foreach(TracyDebugger::getDataValue('dumpPanelTabs') as $i => $panel) {
                if($panel == 'debugInfo') {
                    $options[Dumper::DEBUGINFO] = true;
                }
                elseif($panel == 'fullObject') {
                    $options[Dumper::DEBUGINFO] = false;
                }
                else {
                    $options[Dumper::DEBUGINFO] = isset($options['debugInfo']) ? $options['debugInfo'] : TracyDebugger::getDataValue('debugInfo');
                }
                $currentDump = $expandCollapseAll . Dumper::toHtml($panel == 'iterator' && is_object($var) && method_exists($var, 'getIterator') ? self::humanize($var->getIterator()) : $var, $options);
                if(!isset($lastDump) || (isset($lastDump) && $currentDump !== $lastDump)) {
                	$numTabs++;
                	$tabs .= '<li id="'.$panel.'Tab_'.$classExt.'"' . ($i == 0 ? 'class="active"' : '') . '><a href="#" data-dump-type="'.$panel.'" data-dump-class-ext="'.$classExt.'">'.TracyDebugger::$dumpPanelTabs[$panel].'</a></li>';
                	$tabDivs .= '<div id="'.$panel.'_'.$classExt.'" class="tracyDumpTabs_'.$classExt.'"' . ($i==0 ? '' : ' style="display:none"') . '>'.$currentDump.'</div>';
                }
                $lastDump = $currentDump;
            }
            $tabs .= $editCountLink . '</ul>';
            $tabDivs .= '</div>';
            if($numTabs > 1) {
	            $wrapperOpen = '<div style="clear:both; position:relative;">';
	            $tabDivs = $wrapperOpen . self::buildAgentCopyButton($var, $title) . substr($tabDivs, strlen($wrapperOpen));
	            $out .= $tabs . $tabDivs;
	        }
	        else {
            	$out .= '<div style="clear:both; position:relative;">' . self::buildAgentCopyButton($var, $title) . $lastDump . '</div>';
	        }
        }
        else {
            $out .= '<div style="clear:both; position:relative;">' . self::buildAgentCopyButton($var, $title) . Dumper::toHtml($var, $options) . '</div>';
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

    /**
     * check if array has string keys
     *
     * @param array $array
     * @return bool
     */
    private static function has_string_keys(array $array) {
        foreach($array as $k => $_) {
            if(is_string($k)) return true;
        }
        return false;
    }
}
