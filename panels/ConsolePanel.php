<?php namespace ProcessWire;

use Tracy\Debugger;

class ConsolePanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    private $tracyIncludeCode;

    public function getTab() {
        if(TracyDebugger::isAdditionalBar()) {
            return;
        }

        Debugger::timer('console');

        $this->tracyIncludeCode = json_decode((string)$this->wire('input')->cookie->tracyIncludeCode, true);
        if($this->tracyIncludeCode && $this->tracyIncludeCode['when'] !== 'off') {
            $this->iconColor = $this->wire('input')->cookie->tracyCodeError ? TracyDebugger::COLOR_ALERT : TracyDebugger::COLOR_WARN;
        }
        else {
            $this->iconColor = TracyDebugger::COLOR_NORMAL;
        }

        $this->icon = '
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="13.7px" viewBox="439 504.1 16 13.7" enable-background="new 439 504.1 16 13.7" xml:space="preserve">
            <path class="consoleIconPath" fill="' . $this->iconColor . '" d="M453.9,504.1h-13.7c-0.6,0-1.1,0.5-1.1,1.1v11.4c0,0.6,0.5,1.1,1.1,1.1h13.7c0.6,0,1.1-0.5,1.1-1.1v-11.4
                C455,504.7,454.5,504.1,453.9,504.1z M441.3,512.1l2.3-2.3l-2.3-2.3l1.1-1.1l3.4,3.4l-3.4,3.4L441.3,512.1z M450.4,513.3h-4.6v-1.1
                h4.6V513.3z"/>
            </svg>';

        return $this->buildTab('Console');
    }


    public function getPanel() {

        $rootPath = $this->wire('config')->paths->root;
        $currentUrl = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES, 'UTF-8');
        $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;
        $inAdmin = TracyDebugger::$inAdmin;

        // CSRF token + $input snapshots are written to session in a
        // ProcessWire::finished hook (see TracyDebugger::init) so they
        // persist with SessionHandlerDB, whose register_shutdown_function
        // closes the session before Tracy renders the panel.
        $csrfToken = $this->wire('session')->tracyConsoleToken;

        $p = $this->getReferencePage();

        $pid = $p ? $p->id : 'null';

        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
            $fid = (int) $this->wire('input')->get('id');
        }
        else {
            $fid = null;
        }
        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
            $tid = (int) $this->wire('input')->get('id');
        }
        else {
            $tid = null;
        }
        if($this->wire('input')->get('name') && $this->wire('page')->process == 'ProcessModule') {
            $mid = $this->wire('sanitizer')->name($this->wire('input')->get('name'));
        }
        else {
            $mid = null;
        }

        // get snippets from filesystem
        $snippets = array();
        $snippetsPath = TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/';
        if(file_exists($this->wire('config')->paths->site.$snippetsPath)) {
            $snippetFiles = new \DirectoryIterator($this->wire('config')->paths->site.$snippetsPath);
            $i=0;
            foreach($snippetFiles as $snippetFile) {
                if(!$snippetFile->isDot() && $snippetFile->isFile()) {
                    $snippetFileName = $snippetFile->getPathname();
                    $snippets[$i]['name'] = pathinfo($snippetFileName, PATHINFO_BASENAME);
                    $snippets[$i]['filename'] = $snippetFileName;
                    $snippets[$i]['code'] = str_replace(TracyDebugger::getDataValue('consoleCodePrefix'), '', file_get_contents($snippetFileName));
                    $snippets[$i]['modified'] = filemtime($snippetFileName);
                    $i++;
                }
            }
            $snippets = json_encode($snippets);
        }
        if(!$snippets) $snippets = json_encode(array());

        $out = '<script' . TracyDebugger::getNonceAttr() . '>' . file_get_contents($this->wire('config')->paths->TracyDebugger . 'scripts/get-query-variable.js') . '</script>';

        $maximizeSvg =
        '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             viewBox="282.8 231 16 15.2" enable-background="new 282.8 231 16 15.2" xml:space="preserve">
            <polygon fill="#AEAEAE" points="287.6,233.6 298.8,231 295.4,242 "/>
            <polygon fill="#AEAEAE" points="293.9,243.6 282.8,246.2 286.1,235.3 "/>
        </svg>';

        $codeUseSoftTabs = TracyDebugger::getDataValue('codeUseSoftTabs');
        $codeShowInvisibles = TracyDebugger::getDataValue('codeShowInvisibles');
        $codeTabSize = TracyDebugger::getDataValue('codeTabSize');
        $customSnippetsUrl = TracyDebugger::getDataValue('customSnippetsUrl');

        // Values interpolated into the panel's JS used to be scattered through it; they now all
        // travel on the tracyConsole config object below, because everything else moved to
        // scripts/console-panel.js. json_encode() keeps strings (and the multi-line SVG) valid
        // as JS literals, and the null fallbacks keep the object literal syntactically valid
        // when there is no field/template/module in context.
        $currentUrlJs = json_encode($currentUrl);
        $midJs = json_encode((string) $mid);
        $maximizeSvgJs = json_encode($maximizeSvg);
        $fidJs = $fid === null ? 'null' : (int) $fid;
        $tidJs = $tid === null ? 'null' : (int) $tid;
        $codeTabSizeJs = (int) $codeTabSize;
        $codeUseSoftTabsJs = $codeUseSoftTabs ? 'true' : 'false';
        $codeShowInvisiblesJs = $codeShowInvisibles ? 'true' : 'false';
        $consolePanelJsVersion = TracyDebugger::getModuleInfo()['version'];

        // Only the current page's fields are inlined here. The API variables/methods/functions
        // are the bulk of the list (~200KB of JSON with descriptions enabled) and are the same
        // for every page, so the panel JS fetches those from the tracyAutocomplete endpoint when
        // the editor initialises, rather than carrying them in every page's HTML.
        if(TracyDebugger::getDataValue('pwAutocompletions')) {
            $pwAutocompleteArr = array();
            if($p && !$p instanceof NullPage) {
                foreach($p->fields as $field) {
                    $item = array(
                        'name' => '$page->' . $field,
                        'meta' => 'PW ' . str_replace('Fieldtype', '', $field->type) . ' field',
                    );
                    if(TracyDebugger::getDataValue('codeShowDescription')) $item['docHTML'] = $field->description;
                    $pwAutocompleteArr[] = $item;
                }
            }
            $pwAutocomplete = json_encode($pwAutocompleteArr);
            $pwAutocompleteUrl = $this->wire('config')->urls->httpRoot . '?tracyAutocomplete='
                . TracyDebugger::getConsoleApiAutocompleteKey();
        }
        else {
            $pwAutocomplete = json_encode(array());
            $pwAutocompleteUrl = '';
        }

        $aceTheme = TracyDebugger::getDataValue('aceTheme');
        $codeFontSize = TracyDebugger::getDataValue('codeFontSize');
        $codeLineHeight = TracyDebugger::getDataValue('codeLineHeight');
        $externalEditorLink = str_replace('"', "'", TracyDebugger::createEditorLink($this->wire('config')->paths->site.TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/'.'ExternalEditorDummyFile', 0, '&#xf040;', 'Edit in external editor'));
        $colorNormal = TracyDebugger::COLOR_NORMAL;
        $colorWarn = TracyDebugger::COLOR_WARN;

        $dbRestoreMessageSafe = isset($this->dbRestoreMessage) ? htmlspecialchars($this->dbRestoreMessage, ENT_QUOTES, 'UTF-8') : '';
        $tracyCodeErrorSafe = $this->wire('input')->cookie->tracyCodeError
            ? htmlspecialchars($this->wire('input')->cookie->tracyCodeError, ENT_QUOTES, 'UTF-8')
            : '';

        $nonceAttr = TracyDebugger::getNonceAttr();
        $out .= <<< HTML
        <script{$nonceAttr}>
        var tracyConsole = window.tracyConsole || {};
        Object.assign(tracyConsole, {
            tce: {},
            tracyModuleUrl: "$tracyModuleUrl",
            csrfToken: "$csrfToken",
            tabsContainer: null,
            addTabButton: null,
            currentTabId: null,
            runs: {},
            maxHistoryItems: 25,
            desc: false,
            loadingSnippet: false,
            inAdmin: "$inAdmin",
            customSnippetsUrl: "$customSnippetsUrl",
            snippetsPath: "$snippetsPath",
            rootPath: "$rootPath",
            pwAutocomplete: $pwAutocomplete,
            pwAutocompleteUrl: "$pwAutocompleteUrl",
            pwAutocompleteLoaded: false,
            aceTheme: "$aceTheme",
            codeFontSize: $codeFontSize,
            lineHeight: $codeLineHeight,
            externalEditorLink: "$externalEditorLink",
            colorNormal: "$colorNormal",
            colorWarn: "$colorWarn",
            split: null,
            consoleGutterSize: 8,
            minSize: null,
            scrollSaveTimer: null,
            scrollSaveDelay: 500,
            _rawResultHtml: '',
            pid: $pid,
            fid: $fidJs,
            tid: $tidJs,
            mid: $midJs,
            currentUrl: $currentUrlJs,
            snippets: $snippets,
            maximizeSvg: $maximizeSvgJs,
            codeTabSize: $codeTabSizeJs,
            codeUseSoftTabs: $codeUseSoftTabsJs,
            codeShowInvisibles: $codeShowInvisiblesJs
        });
        window.tracyConsole = tracyConsole;
        if (!window.tracyConsolePanelLoading) {
            window.tracyConsolePanelLoading = true;
            tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/console-panel.js?v=$consolePanelJsVersion");
        }
        </script>

HTML;

        $out .= '
        <h1>' . $this->icon . ' Console
            <span id="tracyConsoleKeyboardShortcuts" title="Keyboard Shortcuts (toggle on/off)" style="display: inline-block; margin-left: 10px; cursor: pointer">⌘</span>
            <span id="tracyConsoleStatus" style="padding-left: 50px"></span>
        </h1>
        <span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" data-tracy-resize="ConsolePanel">⛶</a></span></span>
        ' . $this->openPanel() . '

            <div style="position: relative; height: calc(100% - 80px)">

                <div id="tracyConsoleMainContainer" class="tracy-console-'.TracyDebugger::getDataValue('consoleTabsTheme').'" style="position: absolute; height: 100%; width: '.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? '100%' : 'calc(100% - 290px)').'">

                    <div id="consoleKeyboardShortcuts" class="keyboardShortcuts tracyHidden">';
                        $panel = 'console';
                        include($this->wire('config')->paths->TracyDebugger.'includes/AceKeyboardShortcuts.php');
                        $out .= $aceKeyboardShortcuts . '
                    </div>
                    ';

                    $out .= '
                    <div style="margin-bottom: 7px">
                        <span style="display: inline-block; padding: 0 10px 5px 0">
                            <input id="reloadSnippet" title="Reload current snippet from disk" class="disabledButton" style="font-family: FontAwesome !important; padding: 3px 8px !important;" type="submit" value="&#xf021" disabled="true" />&nbsp;&nbsp;
                            <input style="font-family: FontAwesome !important" title="Go back (ALT + PageUp)" id="historyBack" class="disabledButton" disabled="true" type="submit" value="&#xf060;" />&nbsp;
                            <input style="font-family: FontAwesome !important" title="Go forward (ALT + PageDown)" id="historyForward" class="disabledButton" disabled="true" type="submit" value="&#xf061;" />&nbsp;
                        </span>

                        <span style="display: inline-block; padding: 0 10px 0 0">
                            <label title="Backup entire database before executing script.">
                                <input type="checkbox" id="dbBackup" '.($this->wire('input')->cookie->tracyDbBackup ? 'checked="checked"' : '').' /> Backup DB
                            </label>&nbsp;&nbsp;
                            <input id="backupFilename" type="text" placeholder="Backup name (optional)" '.($this->wire('input')->cookie->tracyDbBackup ? 'style="display:inline-block !important"' : 'style="display:none !important"').' '.($this->wire('input')->cookie->tracyDbBackupFilename ? 'value="'.htmlspecialchars($this->wire('input')->cookie->tracyDbBackupFilename, ENT_QUOTES, 'UTF-8').'"' : '').' />
                        </span>
                        <span style="display: inline-block; padding: 0 20px 5px 0">
                            <label title="Send full stack trace of errors to Tracy bluescreen">
                                <input type="checkbox" id="allowBluescreen" /> Allow bluescreen
                            </label>
                        </span>
                        <span style="display: inline-block; padding: 0 20px 5px 0">
                            <label title="Show d() and db() output live while the script is still running, instead of only when it finishes. Adds a background request about once a second while output is arriving, easing to every three seconds when it is not.">
                                <input type="checkbox" id="streamResults" '.($this->wire('input')->cookie->tracyConsoleStreamResults ? 'checked="checked"' : '').' /> Stream results
                            </label>
                        </span>
                        ';

                        if(!$inAdmin) {
                            $out .= '
                        <span style="display: inline-block; padding: 0 20px 5px 0">
                            <label title="Access custom variables & functions from this page\'s template file & included files."><input type="checkbox" id="accessTemplateVars" /> Template resources</label>
                        </span>';
                        }

                        $out .= '
                        <span style="display:inline-block; padding-right: 5px;">
                            <input id="tracyConsoleClearResults" title="Clear results" type="submit" class="clearResults" style="padding: 3px 5px !important" value="&#10006; Clear results" />
                            <input id="tracyConsoleForceKillAll" title="Force-kill ALL running console scripts and clear console_runs state. Use only when the normal cancel button isn\'t working." type="submit" class="clearResults" style="padding: 3px 5px !important; color: #e22006 !important" value="&#9888; Force-kill all" />
                            <select id="tracyIncludeCodeSelect" name="includeCode" style="height: 25px !important" title="When to execute code" />
                                <option value="off"' . (!$this->tracyIncludeCode || $this->tracyIncludeCode['when'] === 'off' ? ' selected' : '') . '>@ Run</option>
                                <option value="init"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'init' ? ' selected' : '') . '>@ Init</option>
                                <option value="ready"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'ready' ? ' selected' : '') . '>@ Ready</option>
                                <option value="finished"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'finished' ? ' selected' : '') . '>@ Finished</option>
                            </select>
                        </span>
                        <input id="runInjectButton" title="&bull; Run (CTRL/CMD + Enter)&#10;&bull; Clear & Run (ALT/OPT + Enter)&#10;&bull; Reload from Disk, Clear & Run&#10;(CTRL/CMD + ALT/OPT + Enter)" type="submit" value="' . (!$this->tracyIncludeCode || $this->tracyIncludeCode['when'] === 'off' ? 'Run' : 'Inject') . '" />
                        <span id="snippetPaneToggle" title="Toggle snippets pane" style="font-family: FontAwesome !important; position:absolute; top: 0; right: '.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? '0' : '-290').'px; font-weight: bold; cursor: pointer">'.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? '&#xf053;' : '&#xf054;').'</span>
                    </div>

                    <div id="tracyConsoleContainer" data-max-execution-time="' . (int) ini_get('max_execution_time') . '" data-poll-url="' . $this->wire('config')->urls->assets . 'TracyDebugger/console_runs/" class="split" style="height: 100%; min-height: '.$codeLineHeight.'px">
                        <div id="tracyTabsContainer">
                            <div id="tracyTabsWrapper">
                                <div id="tracyTabs"></div>
                            </div>
                            <button id="addTab" title="Add tab" style="font-weight: 600">+</button>
                        </div>
                        <div style="height: calc(100% - 31px)">
                            <div id="tracyConsoleCode" class="split" style="position: relative; background: #FFFFFF;">
                                <div id="tracyConsoleEditor" style="height: 100%; min-height: '.$codeLineHeight.'px"></div>
                            </div>
                            <div id="tracyConsoleResult" class="split" style="position:relative; padding:0 10px; overflow:auto; border:1px solid #D2D2D2;">';

                    if($dbRestoreMessageSafe) {
                        $out .= '<div style="padding: 10px 0">' . $dbRestoreMessageSafe . '</div>' .
                                '<div style="padding: 10px; border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
                    }
                    if($tracyCodeErrorSafe) {
                        $out .= '<div style="padding: 10px 0">' . $tracyCodeErrorSafe . '</div>' .
                                '<div style="padding: 10px; border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
                    }
                    $out .= '
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tracySnippetsContainer" style="position: absolute; right:0; margin: 0 0 0 10px; width: 275px; height: calc(100% - 15px);"'.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? ' class="tracyHidden"' : '').'">
                    <div style="padding-bottom:5px">
                        Sort: <a id="tracySortAlpha" href="#">alphabetical</a>&nbsp;|&nbsp;<a id="tracySortChrono" href="#">chronological</a>
                    </div>
                    <div style="position: relative; width:100% !important;">
                        <input type="text" id="tracySnippetName" placeholder="Enter filename (eg. myscript.php)" />
                        <input id="saveSnippet" type="submit" style="font-family: FontAwesome !important" class="disabledButton" value="&#xf0c7;" title="Save snippet" />
                    </div>
                    <div id="tracySnippets"></div>
                </div>

            </div>
            ';
        $out .= TracyDebugger::generatePanelFooter('console', Debugger::timer('console'), strlen($out), 'consolePanel');
        $out .= '
        </div>';

        return TracyDebugger::minify($out);

    }

}
