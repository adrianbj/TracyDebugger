<?php

/**
 * Processwire module for running the Tracy debugger from Nette.
 * by Adrian Jones
 *
 * Copyright (C) 2021 by Adrian Jones
 * Licensed under GNU/GPL v2, see LICENSE.TXT
 *
 * A big thanks to Roland Toth (https://github.com/rolandtoth/) for the idea for this module
 * and for significant feedback, testing, and feature suggestions.
 *
 */

use Tracy\Debugger;
use Tracy\Helpers;
use Tracy\Dumper;

class TracyDebugger extends WireData implements Module, ConfigurableModule {

    /**
     * Basic information about module
     */
    public static function getModuleInfo() {
        return array(
            'title' => __('Tracy Debugger', __FILE__),
            'summary' => __('Tracy debugger from Nette with many PW specific custom tools.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/forum/58-tracy-debugger/',
            'version' => '4.22.1',
            'autoload' => 100000, // in PW 3.0.114+ higher numbers are loaded first - we want Tracy first
            'singular' => true,
            'requires'  => 'ProcessWire>=2.7.2, PHP>=5.4.4',
            'installs' => array('ProcessTracyAdminer'),
            'icon' => 'bug',
        );
    }


    const COOKIE_SECRET = 'tracy-debug';

    const COLOR_LIGHTGREY = '#999999';
    const COLOR_GREEN = '#009900';
    const COLOR_NORMAL = '#354B60';
    const COLOR_WARN = '#ff8309';
    const COLOR_ALERT = '#cd1818';

    protected $data = array();
    protected $httpReferer;
    protected $tracyEnabled = false;
    protected $earlyExit = false;
    protected $tracyCacheDir;
    protected $modulesDbBackupFilename;
    protected $serverStyleInfo;
    protected static $useOnlineEditor;
    protected static $onlineEditor;
    protected static $onlineFileEditorDirPath;
    public static $inAdmin;
    public static $isLocal = false;
    public static $allowedSuperuser = false;
    public static $allowedTracyUser = false;
    public static $validSwitchedUser = false;
    public static $validLocalUser = false;
    public static $dumpItems = array();
    public static $autocompleteArr = array();
    public static $allApiData = array();
    public static $allApiClassesArr = array();
    public static $apiChanges = array();
    public static $pageFinderQueries = array();
    public static $templateVars = array();
    public static $templateConsts = array();
    public static $initialFuncs = array();
    public static $initialConsts = array();
    public static $templateFuncs = array();
    public static $includedFiles = array();
    public static $fromConsole = false;
    public static $oncePanels;
    public static $stickyPanels;
    public static $showPanels;
    public static $disabableModules = array();
    public static $restrictedUserDisabledPanels = array();
    public static $disabledModules = array();
    public static $templatePath;
    public static $pageVersion;
    public static $templatePathOnce;
    public static $templatePathSticky;
    public static $templatePathPermission;
    public static $tempTemplateFilename;
    public static $panelGenerationTime = array();
    public static $hideInAdmin = array('validator', 'templateResources', 'templatePath');
    public static $superUserOnlyPanels = array('console', 'fileEditor', 'adminer', 'terminal', 'adminTools');
    public static $pageHtml;
    public static $processWireInfoSections = array(
        'configData' => 'Config Data',
        'versionsList' => 'Versions List',
        'adminLinks' => 'Admin Links',
        'documentationLinks' => 'Documentation Links',
        'gotoId' => 'Goto Page By ID',
        'processWireWebsiteSearch' => 'ProcessWire Website Search'
    );
    public static $requestInfoSections = array(
        'moduleSettings' => 'Module Settings',
        'templateSettings' => 'Template Settings',
        'fieldSettings' => 'Field Settings',
        'inputFieldSettings' => 'Inputfield Settings',
        'fieldCode' => 'Field Code',
        'fieldExportCode' => 'Field Export Code',
        'pageInfo' => 'Page Info',
        'pagePermissions' => 'Page Permissions',
        'languageInfo' => 'Language Info',
        'templateInfo' => 'Template Info',
        'templateCode' => 'Template Code',
        'templateExportCode' => 'Template Export Code',
        'fieldsListValues' => 'Field List & Values',
        'serverRequest' => 'Server Request',
        'inputGet' => 'Input GET',
        'inputPost' => 'Input POST',
        'inputCookie' => 'Input COOKIE',
        'session' => 'SESSION',
        'pageObject' => 'Page Object',
        'templateObject' => 'Template Object',
        'fieldsObject' => 'Fields Object',
        'editLinks' => 'Page/Template Edit Links'
    );
    public static $debugModeSections = array(
        'pagesLoaded' => 'Pages Loaded',
        'modulesLoaded' => 'Modules Loaded',
        'hooks' => 'Hooks Triggered',
        'databaseQueries' => 'Database Queries',
        'selectorQueries' => 'Selector Queries',
        'timers' => 'Timers',
        'user' => 'User',
        'cache' => 'Cache',
        'autoload' => 'Autoload'
    );
    public static $diagnosticsSections = array(
        'filesystemFolders' => 'Filesystem Folders',
        'filesystemFiles' => 'Filesystem Files',
        'mysqlInfo' => 'MySQL Info'
    );
    public static $dumpPanelTabs = array(
        'debugInfo' => 'Debug Info',
        'iterator' => 'Iterator',
        'fullObject' => 'Full Object'
    );
    public static $externalPanels = array();
    public static $allPanels = array(
        'adminTools' => 'Admin Tools',
        'adminer' => 'Adminer',
        'apiExplorer' => 'API Explorer',
        'captainHook' => 'Captain Hook',
        'console' => 'Console',
        'customPhp' => 'Custom PHP',
        'debugMode' => 'Debug Mode',
        'diagnostics' => 'Diagnostics',
        'dumpsRecorder' => 'Dumps Recorder',
        'eventInterceptor' => 'Event Interceptor',
        'fileEditor' => 'File Editor',
        'gitInfo' => 'Git Info',
        'helloWorld' => 'Hello World',
        'links' => 'Links',
        'mailInterceptor' => 'Mail Interceptor',
        'methodsInfo' => 'Methods Info',
        'moduleDisabler' => 'Module Disabler',
        'outputMode' => 'Output Mode',
        'pageFiles' => 'Page Files',
        'pageRecorder' => 'Page Recorder',
        'panelSelector' => 'Panel Selector',
        'performance' => 'Performance',
        'phpInfo' => 'PHP Info',
        'processwireInfo' => 'ProcessWire Info',
        'processwireLogs' => 'ProcessWire Logs',
        'processwireVersion' => 'ProcessWire Version',
        'requestInfo' => 'Request Info',
        'requestLogger' => 'Request Logger',
        'terminal' => 'Terminal',
        'templatePath' => 'Template Path',
        'templateResources' => 'Template Resources',
        'todo' => 'ToDo',
        'tracyToggler' => 'Tracy Toggler',
        'tracyLogs' => 'Tracy Logs',
        'userSwitcher' => 'User Switcher',
        'users' => 'Users',
        'validator' => 'Validator',
        'viewports' => 'Viewports'
    );
    public static $userBarFeatures = array(
        'admin' => 'Admin',
        'editPage' => 'Edit Page',
        'pageVersions' => 'Page Versions'
    );

   /**
     * Default configuration for module
     *
     */
    static public function getDefaultData() {
        return array(
            "enabled" => 1,
            "superuserForceDevelopment" => null,
            "guestForceDevelopmentLocal" => null,
            "ipAddress" => null,
            "restrictSuperusers" => null,
            "strictMode" => null,
            "strictModeAjax" => null,
            "forceScream" => null,
            "outputMode" => 'detect',
            "showLocation" => array('Tracy\Dumper::LOCATION_SOURCE', 'Tracy\Dumper::LOCATION_LINK', 'Tracy\Dumper::LOCATION_CLASS'),
            "logSeverity" => array(),
            "numLogEntries" => 10,
            "collapse" => 14,
            "collapse_count" => 7,
            "maxDepth" => 3,
            "maxLength" => 150,
            "maxAjaxRows" => 3,
            "showDebugBar" => array('frontend', 'backend'),
            "hideDebugBar" => null,
            "hideDebugBarFrontendTemplates" => array(),
            "hideDebugBarBackendTemplates" => array(),
            "hideDebugBarModals" => array(),
            "frontendPanels" => array('processwireInfo', 'requestInfo', 'processwireLogs', 'tracyLogs', 'methodsInfo', 'debugMode', 'console', 'panelSelector', 'tracyToggler'),
            "backendPanels" => array('processwireInfo', 'requestInfo', 'processwireLogs', 'tracyLogs', 'methodsInfo', 'debugMode', 'console', 'panelSelector', 'tracyToggler'),
            "restrictedUserDisabledPanels" => array(),
            "nonToggleablePanels" => array(),
            "panelSelectorTracyTogglerButton" => 1,
            "showUserBar" => null,
            "showUserBarTracyUsers" => null,
            "userBarFeatures" => array('admin', 'editPage'),
            "userBarCustomFeatures" => '',
            "userBarBackgroundColor" => '',
            "userBarBackgroundOpacity" => 1,
            "userBarIconColor" => '#666666',
            "userBarTopBottom" => 'bottom',
            "userBarLeftRight" => 'left',
            "showPanelLabels" => null,
            "barPosition" => 'bottom-right',
            "panelZindex" => 100,
            "styleWhere" => array('backend', 'frontend'),
            "styleAdminElements" => "body::before {\n\tcontent: \"[type]\";\n\tbackground: [color];\n\tposition: fixed;\n\tleft: 0;\n\tbottom: 100%;\n\tcolor: #ffffff;\n\twidth: 100vh;\n\tpadding: 0;\n\ttext-align: center;\n\tfont-weight: 600;\n\ttext-transform: uppercase;\n\ttransform: rotate(90deg);\n\ttransform-origin: bottom left;\n\tz-index: 999999;\n\tfont-family: sans-serif;\n\tfont-size: 11px;\n\theight: 13px;\n\tline-height: 13px;\npointer-events: none;\n}\n",
            "styleAdminColors" => "\nlocal|#FF9933\n*.local|#FF9933\ndev.*|#FF9933\n*.test|#FF9933\nstaging.*|#8b0066\n*.com|#009900",
            "styleAdminType" => array('favicon'),
            "showPWInfoPanelIconLabels" => 1,
            "linksNewTab" => null,
            "pWInfoPanelLinksNewTab" => null,
            "customPWInfoPanelLinks" => array(11, 16, 22, 21, 29, 30, 31, 304),
            "captainHookShowDescription" => 1,
            "captainHookToggleDocComment" => null,
            "apiExplorerShowDescription" => 1,
            "apiExplorerToggleDocComment" => null,
            "apiExplorerModuleClasses" => array(),
            "requestInfoPanelSections" => array('moduleSettings', 'templateSettings', 'fieldSettings', 'pageInfo', 'pagePermissions', 'languageInfo', 'templateInfo', 'fieldsListValues', 'serverRequest', 'inputGet', 'inputPost', 'inputCookie', 'session', 'editLinks'),
            "processwireInfoPanelSections" => array('versionsList', 'adminLinks', 'documentationLinks', 'gotoId', 'processWireWebsiteSearch'),
            "debugModePanelSections" => array('pagesLoaded', 'modulesLoaded', 'hooks', 'databaseQueries', 'selectorQueries', 'timers', 'user', 'cache', 'autoload'),
            "diagnosticsPanelSections" => array('filesystemFolders'),
            "dumpPanelTabs" => array('debugInfo', 'fullObject'),
            "validatorUrl" => 'https://html5.validator.nu/',
            "requestMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'PATCH'),
            "requestLoggerMaxLogs" => 10,
            "requestLoggerReturnType" => 'array',
            "imagesInFieldListValues" => 0,
            "snippetsPath" => 'templates',
            "consoleBackupLimit" => 25,
            "consoleCodePrefix" => '',
            "userSwitcherSelector" => '',
            "userSwitcherRestricted" => null,
            "userSwitcherIncluded" => null,
            "todoIgnoreDirs" => 'git, svn, images, img, errors, sass-cache, node_modules',
            "todoScanModules" => null,
            "todoScanAssets" => null,
            "todoAllowedExtensions" => 'php, module, inc, txt, latte, html, htm, md, css, scss, less, js',
            "variablesShowPwObjects" => null,
            "alwaysShowDebugTools" => 1,
            "respectConfigDebugTools" => null,
            "userDevTemplate" => null,
            "userDevTemplateSuffix" => 'dev',
            "customPhpCode" => '',
            "linksCode" => '',
            "fromEmail" => '',
            "email" => '',
            "clearEmailSent" => null,
            "showFireLogger" => 1,
            "reservedMemorySize" => 500000,
            "referencePageEdited" => 1,
            "debugInfo" => 1,
            "editor" => 'vscode://file/%file:%line',
            "useOnlineEditor" => array(),
            "onlineEditor" => 'tracy',
            "forceEditorLinksToTracy" => 1,
            "localRootPath" => '',
            "aceTheme" => 'tomorrow_night_bright',
            "codeFontSize" => 14,
            "codeLineHeight" => 24,
            "codeShowInvisibles" => true,
            "codeTabSize" => 4,
            "codeUseSoftTabs" => true,
            "codeShowDescription" => 1,
            "customSnippetsUrl" => '',
            "pwAutocompletions" => 1,
            "fileEditorAllowedExtensions" => 'php, module, js, css, txt, log, htaccess',
            "fileEditorExcludedDirs" => 'site/assets',
            "fileEditorBaseDirectory" => 'templates',
            "enableShortcutMethods" => 1,
            "enabledShortcutMethods" => array('addBreakpoint', 'bp', 'barDump', 'bd', 'barDumpBig', 'bdb', 'barEcho', 'be', 'debugAll', 'da', 'dump', 'd', 'dumpBig', 'db', 'fireLog', 'fl', 'l', 'templateVars', 'tv', 'timer', 't')
        );
    }


    /**
     * Populate the default config data
     *
     */
    public static $_data;

    public function __construct() {
        foreach(self::getDefaultData() as $key => $value) {
            $this->$key = $value;
        }
    }


    /**
     * Initialize the module
     */
    public function init() {

        // load Tracy files and our helper files
        if(version_compare(PHP_VERSION, '7.2.0', '>=')) {
            $tracyVersion = '2.8.x';
        }
        elseif(version_compare(PHP_VERSION, '7.1.0', '>=')) {
            $tracyVersion = '2.7.x';
        }
        else {
            $tracyVersion = '2.5.x';
        }
        require_once __DIR__ . '/tracy-'.$tracyVersion.'/src/tracy.php';
        require_once __DIR__ . '/includes/TD.php';
        if($this->data['enableShortcutMethods']) {
            require_once __DIR__ . '/includes/ShortcutMethods.php';
        }

        // load base panel class
        require_once __DIR__ . '/includes/BasePanel.php';

        $externalPanelPaths = glob($this->wire('config')->paths->root.'/site/modules/*/TracyPanels/*.php');
        foreach($externalPanelPaths as $panelPath) {
            $path_parts = pathinfo($panelPath);
            $panelName = lcfirst($path_parts['filename']);
            static::$externalPanels[$panelName] = $panelPath;
            static::$allPanels[$panelName] = implode(' ', preg_split('/(?=[A-Z])/', $path_parts['filename']));
            ksort(static::$allPanels);
        }

        // merge in settings from config.php file
        if(isset($this->wire('config')->tracy) && is_array($this->wire('config')->tracy)) {
            $this->data = array_merge($this->data, $this->wire('config')->tracy);
        }
        //populate for later static access to data
        self::$_data = $this;

        // determine if server is local dev or live
        static::$isLocal = static::isLocal();

        // url for $session->redirects
        $this->httpReferer = isset($_SERVER['HTTP_REFERER']) ? $this->wire('sanitizer')->text($_SERVER['HTTP_REFERER']) : self::inputHttpUrl(true);

        // determine if we are in the admin / backend
        static::$inAdmin = $this->inAdmin();

        // check if user is superuser and has tracy-debugger permission if required
        if($this->data['restrictSuperusers'] && $this->wire('user')->isSuperuser()) {
            static::$allowedSuperuser = self::userHasPermission('tracy-debugger');
        }
        else {
            static::$allowedSuperuser = $this->wire('user')->isSuperuser();
        }

        // determine whether user is allowed to use Tracy and whether DEV or PRODUCTION
        static::$allowedTracyUser = static::allowedTracyUser();

        $this->tracyCacheDir = $this->wire('config')->paths->cache . 'TracyDebugger/';

        if(!is_dir($this->tracyCacheDir)) {
            if(!wireMkdir($this->tracyCacheDir)) {
                throw new WireException("Unable to create cache path: " . $this->tracyCacheDir);
            }
        }


        // REQUEST LOGGER
        // add getRequestData() method to the $page object
        // before Tracy enabled check in case this method is used in a template file
        $this->wire()->addHook('Page::getRequestData', $this, 'getRequestData');


        // EARLY EXITS
        // modals
        if(in_array('regularModal', $this->data['hideDebugBarModals']) && $this->wire('input')->get->modal == '1') $this->earlyExit = true;
        if(in_array('inlineModal', $this->data['hideDebugBarModals']) && $this->wire('input')->get->modal == 'inline') $this->earlyExit = true;
        if(in_array('overlayPanels', $this->data['hideDebugBarModals']) && $this->wire('input')->get->modal == 'panel') $this->earlyExit = true;

        // formbuilder iframe
        if(in_array('formBuilderIframe', $this->data['hideDebugBarModals']) &&
            !static::$inAdmin &&
            strpos(self::inputUrl(true), DIRECTORY_SEPARATOR.'form-builder'.DIRECTORY_SEPARATOR) !== false) {
                $this->earlyExit = true;
            }

        // adminer iframe
        if(strpos(self::inputUrl(true), 'adminer') !== false) $this->earlyExit = true;
        // terminal iframe
        if(strpos(self::inputUrl(true), 'terminal') !== false) $this->earlyExit = true;

        // don't init Tracy for @soma's PageEditSoftLock polling on Page Edit
        if(strpos(self::inputUrl(), 'checkpagelock') !== false) $this->earlyExit = true;

        if(isset($_SERVER['REQUEST_URI'])) {
            $info = parse_url($_SERVER['REQUEST_URI']);
            $queryString = isset($info['query']) ? $info['query'] : '';
            // don't init Tracy for the PW Notifications module polling
            if(strpos($queryString, 'Notifications=update') !== false) $this->earlyExit = true;

            // don't init Tracy for sidenav iframes in admin themes that support this
            if(strpos($queryString, 'layout=sidenav-side') !== false ||
                strpos($queryString, 'layout=sidenav-tree') !== false ||
                strpos($queryString, 'layout=sidenav-init') !== false) $this->earlyExit = true;

            // don't init Tracy for Setup > Logs view polling
            if(strpos($_SERVER['REQUEST_URI'], DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR) !== false &&
                strpos($queryString, 'q=') !== false) $this->earlyExit = true;

        }

        // if "enabled" not checked or tracyDisabled via config or other early exit
        if(!$this->data['enabled'] || $this->wire('config')->tracyDisabled || $this->earlyExit) {
            return;
        }

        // include the Console panel's codeProcessor after ready so it has access to any properties/methods added by other modules
        $this->wire()->addHookAfter('ProcessWire::ready', function($event) {
            // if it's an ajax request from the Tracy Console panel for code execution, then process and return
            if($this->wire('config')->ajax && $this->wire('input')->post->tracyConsole == 1) {
                require_once(__DIR__ . '/includes/CodeProcessor.php');
                return;
            }
        }, array('priority' => 9999));


        // log requests for Request Logger
        $this->wire()->addHookAfter('ProcessWire::ready', function($event) {
            if(!method_exists($event->page, 'render')) {
                $event->page->addHookAfter('render', $this, 'logRequests');
            }
        });
        $this->wire()->addHookAfter('Page::logRequests', $this, 'logRequests');


        // clear session & cookies option in Processwire Info panel
        // not inside static::$allowedTracyUser === 'development' check because it won't validate during forceLogin()
        if($this->wire('input')->get->tracyClearSession) {
            $userName = $this->wire('user')->name;
            $this->wire('session')->logout();

            if (isset($_SERVER['HTTP_COOKIE'])) {
                $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
                foreach($cookies as $cookie) {
                    $parts = explode('=', $cookie);
                    $name = trim($parts[0]);
                    setcookie($name, '', time()-1000);
                    setcookie($name, '', time()-1000, '/');
                }
            }

            $this->wire('session')->forceLogin($userName);
            $this->wire('session')->redirect(substr(str_replace('tracyClearSession=1', '', self::inputUrl(true)), 0, -1));
        }


        // USER BAR
        if(
            $this->wire('user')->isLoggedin() &&
            !static::$inAdmin &&
            $this->data['showUserBar'] &&
            count($this->data['userBarFeatures'])>0 &&
            !$this->wire('config')->ajax &&
            (static::$allowedTracyUser !== 'development' || $this->data['showUserBarTracyUsers']) &&
            (strpos(self::inputUrl(true), DIRECTORY_SEPARATOR.'form-builder'.DIRECTORY_SEPARATOR) === false)
        ) {
            $this->wire()->addHookAfter('ProcessWire::ready', function($event) {
                if(!method_exists($event->page, 'render')) {
                    $event->page->addHookAfter('render', $this, 'addUserBar', array('priority'=>1000));
                }
            });
        }


        // Various features that can be run before loading Tracy core files
        if(static::$allowedTracyUser === 'development') {

            // PANELS TO DISPLAY
            $configEnabledPanels = static::$inAdmin ? $this->data['backendPanels'] : $this->data['frontendPanels'];

            // need to set $stickyPanels here so it is alway set if available, rather than in the elseif below
            // because a "once" cookie would prevent if from being set
            if($this->wire('input')->cookie->tracyPanelsSticky) {
                static::$stickyPanels = array_filter(explode(',', $this->wire('input')->cookie->tracyPanelsSticky));
            }

            if($this->wire('input')->cookie->tracyPanelsOnce) {
                static::$oncePanels = array_filter(explode(',', $this->wire('input')->cookie->tracyPanelsOnce));
                static::$showPanels = static::$oncePanels;
                unset($this->wire('input')->cookie->tracyPanelsOnce);
                setcookie("tracyPanelsOnce", "", time()-3600, '/');
            }
            elseif($this->wire('input')->cookie->tracyPanelsSticky) {
                static::$showPanels = static::$stickyPanels;
            }
            else {
                static::$showPanels = $configEnabledPanels;
            }

            if(in_array('debugMode', static::$showPanels)) {
                // Selectors for Debug Mode panel
                $this->wire()->addHookBefore('PageFinder::getQuery', null, function($event) {
                    $this->timerkey = Debug::timer();
                });
                $this->wire()->addHookAfter('PageFinder::getQuery', null, function($event) {
                    $event->setArgument(2, Debug::timer($this->timerkey));
                    static::$pageFinderQueries[] = $event;
                });
            }

            // sort panels based on order defined in config settings
            $showPanelsOrdered = array();
            $i=0;
            // add default panels in the defined order
            foreach($configEnabledPanels as $panelName) {
                if(in_array($panelName, static::$showPanels)) $showPanelsOrdered[$i] = $panelName;
                $i++;
            }
            // add once/sticky panels to the end because there is no specified order for these in config settings
            foreach(static::$allPanels as $panelName => $panelTitle) {
                if(in_array($panelName, static::$showPanels) && !in_array($panelName, $showPanelsOrdered)) $showPanelsOrdered[$i] = $panelName;
                // define disabled panels for restricted users
                if((self::$validSwitchedUser || self::userHasPermission("tracy-restricted-panels") || $this->wire('user')->hasRole("tracy-restricted-panels")) && in_array($panelName, $this->data['restrictedUserDisabledPanels'])) {
                    static::$restrictedUserDisabledPanels[] = $panelName;
                }
                $i++;
            }
            // move Panel Selector to the end so it has access to the generation time values for all other panels
            static::$showPanels = $showPanelsOrdered;
            if(($key = array_search('panelSelector', static::$showPanels)) !== false) {
                unset(static::$showPanels[$key]);
                static::$showPanels[] = 'panelSelector';
            }

            // unhide and unlock all fields
            if($this->wire('input')->cookie->tracyUnhideUnlockFields == 1) {
                $this->wire()->addHookAfter('Field::getInputfield', function(HookEvent $event) {
                    if($this->page->process !== 'ProcessPageEdit' && $this->page->process !== 'ProcessUser') return;
                    $inputfield = $event->return;
                    if($inputfield->collapsed > 0) $inputfield->label .= ' (Unhidden / Uncollapsed / Unlocked by Tracy Debugger)';
                    $inputfield->collapsed = Inputfield::collapsedNo;
                });
            }


            // ProcessWire Info panel early redirects
            // logout
            if($this->wire('input')->get->tracyLogout) {
                $this->wire('session')->logout();
                $this->wire('session')->redirect(rtrim(substr(str_replace(array('tracyLogout=1', 'login=1'), '', self::inputUrl(true)), 0, -1), '?'));
            }
            // login
            if($this->wire('input')->get->tracyLogin) {
                $this->wire('session')->tracyLoginUrl = $this->httpReferer;
            }
            if($this->wire('session')->tracyLoginUrl) {
                $this->wire()->addHookAfter('Session::loginSuccess', function(HookEvent $event) {
                    $this->wire('session')->redirect($this->wire('session')->tracyLoginUrl);
                });
            }

            // refresh modules
            if($this->wire('input')->get->tracyModulesRefresh) {
                $this->wire('session')->tracyLastUrl = substr(str_replace('tracyModulesRefresh=1', '', self::inputUrl(true)), 0, -1);
                $this->wire('session')->redirect($this->wire('config')->urls->admin.'module/?reset=1');
            }
            if($this->wire('input')->get->reset == 2 && $this->wire('session')->tracyLastUrl) {
                $tracyLastUrl = $this->wire('session')->tracyLastUrl;
                $this->wire('session')->remove('tracyLastUrl');
                $this->wire('session')->redirect($tracyLastUrl);
            }


            // PW VERSION SWITCHER
            // if PW version changed, reload to initialize new version
            // don't add in_array(static::$showPanels) check because that won't be true if enabled ONCE due to multiple redirects waiting for $this->wire('config')->version to update
            if(($this->wire('input')->post->tracyPwVersion && $this->wire('input')->post->tracyPwVersion != $this->wire('config')->version) || $this->wire('session')->tracyPwVersion) {
                $this->wire('session')->tracyPwVersion = $this->wire('session')->tracyPwVersion ?: $this->wire('input')->post->tracyPwVersion;
                while($this->wire('session')->tracyPwVersion != $this->wire('config')->version) {
                    sleep(1);
                    $this->wire('session')->redirect($this->httpReferer);
                }
                $this->wire('session')->remove('tracyPwVersion');
            }


            // REQUEST LOGGER
            // enable/disable page logging
            if($this->wire('input')->post->tracyRequestLoggerEnableLogging || $this->wire('input')->post->tracyRequestLoggerDisableLogging) {
                $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                if($this->wire('input')->post->tracyRequestLoggerEnableLogging) {
                    if(!isset($configData['requestLoggerPages'])) $configData['requestLoggerPages'] = array();
                    array_push($configData['requestLoggerPages'], $this->wire('input')->post->requestLoggerLogPageId);
                }
                else {
                    if(($key = array_search($this->wire('input')->post->requestLoggerLogPageId, $configData['requestLoggerPages'])) !== false) {
                        unset($configData['requestLoggerPages'][$key]);
                    }
                    $data = $this->wire('cache')->get("tracyRequestLogger_id_*_page_".$this->wire('input')->post->requestLoggerLogPageId);
                    if(count($data) > 0) {
                        foreach($data as $id => $datum) {
                            $this->wire('cache')->delete($id);
                        }
                    }
                }
                $this->wire('modules')->saveModuleConfigData($this, $configData);
                $this->wire('session')->redirect($this->httpReferer);
            }


            // MODULES DISABLER
            //set up backup directory/file - outside conditional so they are available for cleanup when panel is disabled
            $this->modulesDbBackupFilename = 'modulesBackup.sql';
            if(in_array('moduleDisabler', static::$showPanels) && $this->wire('config')->debug && $this->wire('config')->advanced) {
                // if modules DB was just restored, clear the cookie
                if($this->wire('input')->cookie->modulesRestored == 1) {
                    unset($this->wire('input')->cookie->modulesRestored);
                    setcookie("modulesRestored", "", time()-3600, "/");
                    unset($this->wire('input')->cookie->tracyModulesDisabled);
                    setcookie("tracyModulesDisabled", "", time()-3600, "/");
                    $this->wire('session')->message("Modules successfully restored");
                }

                // get array of disabable modules
                foreach($this->wire('modules') as $name => $label) {
                    $flags = $this->wire('modules')->getFlags($name);
                    $info = $this->wire('modules')->getModuleInfoVerbose($name);
                    if($info['core']) continue;
                    if($name == 'TracyDebugger') continue;
                    if(($flags & Modules::flagsAutoload) || ($flags & Modules::flagsDisabled)) {
                        static::$disabableModules[] = $name;
                    }
                }

                // if modules have been checked to disable
                if($this->wire('input')->cookie->tracyModulesDisabled) {

                    // if it doesn't already exist, backup existing modules database
                    if(!file_exists($this->tracyCacheDir . $this->modulesDbBackupFilename)) {
                        $backup = new WireDatabaseBackup($this->tracyCacheDir);
                        $backup->setDatabase($this->wire('database'));
                        $backup->setDatabaseConfig($this->wire('config'));
                        $file = $backup->backup(array('tables' => array('modules'), 'filename' => $this->modulesDbBackupFilename));

                        $restoreModulesCode =
                        "<?php\n" .
                        "if(file_exists('".$this->tracyCacheDir.$this->modulesDbBackupFilename."')) {\n" .
                            "\t\$db = new PDO('mysql:host={$this->wire('config')->dbHost};dbname={$this->wire('config')->dbName}', '{$this->wire('config')->dbUser}', '{$this->wire('config')->dbPass}');\n" .
                            "\t\$sql = file_get_contents('" . $this->tracyCacheDir . $this->modulesDbBackupFilename . "');\n" .
                            "\t\$qr = \$db->query(\$sql);\n" .
                        "}\n" .
                        "if(isset(\$qr) && \$qr) {\n" .
                            "\tsetcookie('modulesRestored', 1, time() + (24 * 60 * 60));\n" .
                            "\theader('Location: ".self::inputHttpUrl(true)."');\n" .
                        "}\n" .
                        "else {\n" .
                        "\techo 'Sorry, there was a problem and the database could not be restored.';\n" .
                        "}";

                        if(!file_put_contents($this->tracyCacheDir . 'restoremodules.php', $restoreModulesCode, LOCK_EX)) throw new WireException("Unable to write file: " . $this->tracyCacheDir . 'restoremodules.php');
                    }

                    // get array of modules that have been checked to disable
                    static::$disabledModules = array_filter(explode(',', $this->wire('input')->cookie->tracyModulesDisabled));
                }
                else {
                    $this->deleteFile($this->tracyCacheDir . $this->modulesDbBackupFilename);
                    $this->deleteFile($this->tracyCacheDir . 'restoremodules.php');
                }

                // add disabled flag to requested modules
                $i=0;
                foreach(static::$disabableModules as $name) {
                    $flags = $this->wire('modules')->getFlags($name);
                    if(in_array($name, static::$disabledModules)) {
                        if(!($flags & Modules::flagsDisabled)) {
                            $this->wire('modules')->setFlag($name, Modules::flagsDisabled, true);
                            $i++;
                        }
                    }
                    elseif($flags & Modules::flagsDisabled) {
                        $this->wire('modules')->setFlag($name, Modules::flagsDisabled, false);
                        $i++;
                    }
                }
                if($i > 0) $this->wire('session')->redirect($this->httpReferer);
            }
            else {
                $this->deleteFile($this->tracyCacheDir . $this->modulesDbBackupFilename);
                $this->deleteFile($this->tracyCacheDir . 'restoremodules.php');
            }
            // try to delete modules restore file from root
            $rootRestoreFile = $this->wire('config')->paths->root . 'restoremodules.php';
            if(file_exists($rootRestoreFile)) {
                @unlink($rootRestoreFile);
                if(is_file($rootRestoreFile)) {
                   $this->wire('session')->error('Please delete ' . $rootRestoreFile . ' from your system.');
                }
            }


            // PAGE FILES
            // delete orphaned files if requested
            if($this->wire('input')->post->deleteOrphanFiles && $this->wire('input')->post->orphanPaths) {
                foreach(explode('|', $this->wire('input')->post->orphanPaths) as $filePath) {
                    if(file_exists($filePath)) unlink($filePath);
                }
                $this->wire('session')->redirect($this->httpReferer);
            }
            // delete missing pagefiles if requested
            if($this->wire('input')->post->deleteMissingFiles && $this->wire('input')->post->missingPaths) {
                foreach(json_decode(urldecode($this->wire('input')->post->missingPaths), true) as $pid => $files) {
                    $p = $this->wire('pages')->get($pid);
                    foreach($files as $file) {
                        $pagefile = $p->{$file['field']}->get(pathinfo($file['filename'], PATHINFO_BASENAME));
                        $p->{$file['field']}->delete($pagefile);
                        $p->save($file['field']);
                    }
                }
                $this->wire('session')->redirect($this->httpReferer);
            }


            // PAGE RECORDER
            // trash / clear recorded pages if requested
            if($this->wire('input')->post->trashRecordedPages || $this->wire('input')->post->clearRecordedPages) {
                if($this->wire('input')->post->trashRecordedPages) {
                    foreach($this->data['recordedPages'] as $pid) {
                        $this->wire('pages')->trash($this->wire('pages')->get($pid));
                    }
                }
                unset($this->data['recordedPages']);
                $this->wire('modules')->saveModuleConfigData($this, $this->data);
                $this->wire('session')->redirect($this->httpReferer);
            }


            // ADMIN TOOLS
            if(static::$allowedSuperuser) {
                // delete children
                if($this->wire('input')->post->deleteChildren) {
                    foreach($this->wire('pages')->get((int)$this->wire('input')->post->adminToolsId)->children("include=all") as $child) {
                        $child->delete(true);
                    }
                }
                // delete template
                if($this->wire('input')->post->deleteTemplate) {
                    foreach($this->wire('pages')->find("template=".(int)$this->wire('input')->post->adminToolsId.", include=all") as $p) {
                        $p->delete();
                    }
                    $template = $this->wire('templates')->get((int)$this->wire('input')->post->adminToolsId);
                    $this->wire('templates')->delete($template);
                    $templateName = $template->name;
                    $fieldgroup = $this->wire('fieldgroups')->get($templateName);
                    $this->wire('fieldgroups')->delete($fieldgroup);
                    $this->wire('session')->redirect($this->wire('config')->urls->admin);
                }
                // delete field
                if($this->wire('input')->post->deleteField) {
                    $field = $this->wire('fields')->get((int)$this->wire('input')->post->adminToolsId);
                    foreach($this->wire('templates') as $template) {
                        if(!$template->hasField($field)) continue;
                        $template->fields->remove($field);
                        $template->fields->save();
                    }
                    $this->wire('fields')->delete($field);
                    $this->wire('session')->redirect($this->wire('config')->urls->admin.'setup/field');
                }
                // change field type
                if($this->wire('input')->post->changeFieldType) {
                    $field = $this->wire('fields')->get((int)$this->wire('input')->post->adminToolsId);
                    $field->type = $this->wire('input')->post->changeFieldType;
                    $field->save();
                }
                // uninstall module
                if($this->wire('input')->post->uninstallModule) {
                    $moduleName = $this->wire('input')->post->adminToolsName;
                    $reason = $this->wire('modules')->isUninstallable($moduleName, true);
                    $class = $this->wire('modules')->getModuleClass($moduleName);
                    if($reason !== true) {
                        if(strpos($reason, 'Fieldtype') !== false) {
                            foreach($this->wire('fields') as $field) {
                                $fieldtype = wireClassName($field->type, false);
                                if($fieldtype == $class) {
                                    foreach($this->wire('templates') as $template) {
                                        if(!$template->hasField($field)) continue;
                                        $template->fields->remove($field);
                                        $template->fields->save();
                                    }
                                    $this->wire('fields')->delete($field);
                                }
                            }
                        }
                        elseif(strpos($reason, 'required') !== false) {
                            $dependents = $this->wire('modules')->getRequiresForUninstall($class);
                            foreach($dependents as $dependent) {
                                $this->wire('modules')->uninstall($dependent);
                            }
                        }
                    }
                    $this->wire('modules')->uninstall($moduleName);
                    $this->wire('session')->redirect($this->wire('config')->urls->admin.'module');
                }
            }

            // notify user about email sent flag and provide option to clear it
            $emailSentPath = $this->wire('config')->paths->logs.'tracy/email-sent';
            if($this->wire('input')->post->clearEmailSent || $this->wire('input')->get->clearEmailSent) {
                if(file_exists($emailSentPath)) {
                    $removed = unlink($emailSentPath);
                }
                if (!isset($removed) || !$removed) {
                    $this->wire()->error( __('No file to remove'));
                }
                else {
                    $this->wire()->message(__("email-sent file deleted successfully"));
                    $this->wire('session')->redirect(str_replace(array('?clearEmailSent=1', '&clearEmailSent=1'), '', $this->wire('input')->url(true)));
                }
            }

            if(file_exists($emailSentPath)) {
                $this->wire()->warning('Tracy Debugger "Email Sent" flag has been set. <a href="'.$this->wire('input')->url(true).($this->wire('input')->queryString() ? '&' : '?').'clearEmailSent=1">Clear it</a> to continue receiving further emails', Notice::allowMarkup);
            }

            // CONSOLE PANEL CODE INJECTION
            $this->insertCode('init');
            $this->wire()->addHookBefore('ProcessWire::finished', function($event) {
                $this->insertCode('finished');
            });

        }


        // START ENABLING TRACY
        // now that required classes above have been loaded, we can now exit if user is not allowed
        if(!static::$allowedTracyUser) return;

        // override default PW core behavior that converts exceptions to string
        $this->wire()->addHookAfter('Wire::trackException', function($event) {
            $exception = $event->arguments(0);
            if($this->wire('config')->ajax && ($exception instanceof WireException || $exception instanceof \ProcessWire\WireException)) {
                // intentionally blank
            }
            else {
                throw $exception;
            }
        });


        // SET TRACY AS ENBALED
        // if we get this far, Tracy is fully enabled, so set this for checking in ready()
        $this->tracyEnabled = true;


        // PROCESSWIRE LOGS
        // delete ProcessWire logs if requested
        if($this->wire('input')->post->deleteProcessWireLogs) {
            $files = glob($this->wire('config')->paths->logs.'*');
            foreach($files as $file) {
                if(is_file($file)) {
                    unlink($file);
                }
            }
        }


        // TRACY LOGS
        // Tracy log folder path
        $logFolder = $this->wire('config')->paths->logs.'tracy';

        // delete Tracy logs if requested
        if($this->wire('input')->post->deleteTracyLogs) {
            wireRmdir($logFolder, true);
        }

        // if Tracy log folder doesn't exist, create it now
        if(!is_dir($logFolder)) wireMkdir($logFolder);


        // TRACY MODE
        if($this->data['outputMode'] == 'development' || static::$allowedTracyUser === 'development') {
            $outputMode = Debugger::DEVELOPMENT;
        }
        elseif($this->data['outputMode'] == 'production' || static::$allowedTracyUser === 'production') {
            $outputMode = Debugger::PRODUCTION;
        }
        else {
            $outputMode = Debugger::DETECT;
        }

        // back-end
        if(static::$inAdmin) {
            if(in_array('backend', $this->data['showDebugBar']) && $outputMode != Debugger::PRODUCTION) {
                Debugger::$showBar = true;
            }
            else {
                Debugger::$showBar = false;
            }
        }
        // front-end
        else {
            if(in_array('frontend', $this->data['showDebugBar']) && $outputMode != Debugger::PRODUCTION) {
                Debugger::$showBar = true;
            }
            else {
                Debugger::$showBar = false;
            }
        }

        Debugger::$showFireLogger = $this->data['showFireLogger'];
        if(isset(Debugger::$reservedMemorySize)) Debugger::$reservedMemorySize = $this->data['reservedMemorySize'];

        if(static::$allowedTracyUser === 'development') {

            // TRACY TOGGLER
            // if Tracy has been toggled "disabled" add enable button and then exit
            if($this->wire('input')->cookie->tracyDisabled == 1) {
                if(Debugger::$showBar && !$this->wire('config')->ajax) {
                    $this->wire()->addHook('ProcessWire::ready', function($event) {
                        if(!method_exists($event->page, 'render')) {
                            $event->page->addHookAfter('render', $this, 'addEnableButton', array('priority'=>1000));
                        }
                    });
                }
                // Tracy not enabled so exit now
                $this->tracyEnabled = false;
                return;
            }


            if(Debugger::$showBar) {

                // EDITOR PROTOCOL HANDLER
                // build up array of replacements to pass to Debugger::$editorMapping
                // they have to be completely separate replacements because Tracy uses strtr()
                // which won't replace the same substring more than once
                $mappingReplacements = array();
                $compilerCachePath = isset($this->wire('config')->fileCompilerOptions['cachePath']) && $this->wire('config')->fileCompilerOptions['cachePath'] != '' ? $this->wire('config')->fileCompilerOptions['cachePath'] : $this->wire('config')->paths->cache . 'FileCompiler/';
                $compilerCachePath = str_replace('/', DIRECTORY_SEPARATOR, $compilerCachePath);

                static::$useOnlineEditor = (static::$allowedSuperuser || self::$validLocalUser || self::$validSwitchedUser) &&
                    (static::$isLocal && in_array('local', $this->data['useOnlineEditor'])) ||
                    (!static::$isLocal && in_array('live', $this->data['useOnlineEditor']) ||
                    (in_array('fileEditor', static::$showPanels) && $this->data['forceEditorLinksToTracy'])
                );

                if(static::$useOnlineEditor) {
                    if($this->data['onlineEditor'] == 'processFileEdit' &&
                        $this->wire('modules')->isInstalled('ProcessFileEdit') &&
                        $this->wire('user')->hasPermission('file-edit'))
                    {
                        static::$onlineEditor = 'processFileEdit';
                        Debugger::$editor = $this->wire('config')->urls->admin . 'setup/file-editor/?f=%file&l=%line';
                        $processFileEditSettings = $this->wire('modules')->getModuleConfigData('ProcessFileEdit');
                        static::$onlineFileEditorDirPath = $processFileEditSettings['dirPath'];
                    }
                    else {
                        static::$onlineEditor = 'tracy';
                        Debugger::$editor = 'tracy://?f=%file&l=%line';
                        static::$onlineFileEditorDirPath = $this->wire('config')->paths->root;
                    }
                    $mappingReplacements[$compilerCachePath . str_replace($this->wire('config')->paths->root,'' , static::$onlineFileEditorDirPath)] = '';
                    $mappingReplacements[static::$onlineFileEditorDirPath] = '';
                }
                else {
                    Debugger::$editor = $this->data['editor'];

                    if(!static::$isLocal && $this->data['localRootPath'] != '') {
                        $mappingReplacements[$compilerCachePath] = $this->data['localRootPath'];
                        $mappingReplacements[$this->wire('config')->paths->root] = $this->data['localRootPath'];
                    }
                    else {
                        $mappingReplacements[$compilerCachePath] = $this->wire('config')->paths->root;
                    }
                }

                Debugger::$editorMapping = $mappingReplacements;


                //CUSTOM CSS & JS
                Debugger::$customCssFiles = array(
                    $this->wire('config')->paths->TracyDebugger.'styles/styles.css'
                );

                Debugger::$customJsFiles[] = $this->wire('config')->paths->TracyDebugger.'scripts/main.js';
                Debugger::$customJsFiles[] = $this->wire('config')->paths->TracyDebugger.'scripts/tinycon.min.js';
                Debugger::$customJsFiles[] = $this->wire('config')->paths->TracyDebugger.'scripts/js-loader.js';

                if(in_array('fileEditor', static::$showPanels)) {
                    // this needs to be loaded here (not just in File Editor panel) so that File Editor links
                    // will work even if File Editor panel hasn't been opened yet
                    Debugger::$customJsFiles[] = $this->wire('config')->paths->TracyDebugger.'scripts/file-editor.js';
                }

                if($this->showServerTypeIndicator()) {
                    $this->serverStyleInfo = $this->getServerAdminStyles();
                    Debugger::$customCssStr .= $this->setServerAdminStyleColor();
                    if(in_array('favicon', $this->data['styleAdminType'])) Debugger::$customJsStr .= $this->setFaviconBadge();
                }

                if(!static::$inAdmin && (in_array('fileEditor', static::$showPanels) || (in_array('processwireInfo', static::$showPanels) && count($this->data['customPWInfoPanelLinks'])))) {
                    Debugger::$customCssStr .= '<link id="fontAwesomeStyles" type="text/css" href="'.$this->wire('config')->urls->root . 'wire/templates-admin/styles/font-awesome/css/font-awesome.min.css" rel="stylesheet" />';
                }

                Debugger::$customCssStr .= '
                <style>
                    #tracy-debug-bar {
                        left:'.($this->data['barPosition'] == 'bottom-left' ? '0' : 'auto').' !important;
                        right:'.($this->data['barPosition'] == 'bottom-left' ? 'auto' : '0').' !important;
                    }
                    #tracy-show-button {
                        '.($this->data['barPosition'] == 'bottom-left' ? 'left:0' : 'right:0').' !important;
                    }
                </style>
                ';

                // override Tracy core default zIndex for panels
                // add settings link on double-click to "TRACY" icon
                // replace "close" icon link with "hide" and "unhide" links
                // add esc key event handler to close all panels
                $this->wire()->addHookAfter('ProcessWire::ready', function($event) {
                    if(!method_exists($event->page, 'render')) {
                        $event->page->addHookAfter('render', function($event) {
                            $tracyErrors = Debugger::getBar()->getPanel('Tracy:errors');
                            if(!is_array($tracyErrors->data) || count($tracyErrors->data) === 0) {
                                if(($this->data['hideDebugBar'] && !$this->wire('input')->cookie->tracyShow) || $this->wire('input')->cookie->tracyHidden == 1) {
                                    $hideBar = '
                                        <script>
                                            function hideDebugBar() {
                                                if(!document.getElementById("tracy-debug-bar")) {
                                                    window.requestAnimationFrame(hideDebugBar);
                                                } else {
                                                    document.getElementById("tracy-debug").style.display = "none";
                                                    document.getElementById("tracy-show-button").style.display = "block";
                                                }
                                            }
                                            hideDebugBar();
                                        </script>
                                    ';
                                    $event->return = str_replace("</body>", "\n<!-- Tracy Hide Bar -->\n" . static::minify($hideBar)."\n</body>", $event->return);
                                }
                            }
                            if(in_array('titlePrefix', $this->data['styleAdminType'])) {
                                $serverTypeMatch = $this->serverStyleInfo['serverTypeMatch'];
                                $stylesArr = $this->serverStyleInfo['styles'];
                                $type = $this->serverStyleInfo['type'];
                                if($serverTypeMatch && isset($stylesArr[$type])) {
                                    $event->return = str_replace('<title>', '<title>'.strtoupper(str_replace('*', '', $type)).' - ', $event->return);
                                }
                            }
                        });
                    }
                });

                Debugger::$customBodyStr .= '
                    <div id="tracy-show-button" title="Show Tracy" onclick="unhideBar()">&#8689;</div>
                ';

                Debugger::$customJsStr .= '
                    function addHideLink() {
                        if(!document.getElementById("tracy-debug-bar")) {
                            window.requestAnimationFrame(addHideLink);
                        } else {
                            var debugBar = document.getElementById("tracy-debug-bar");
                            var barul = debugBar.getElementsByTagName("ul")[0];
                            var toggleli = document.createElement("li");
                            toggleli.appendChild(document.createTextNode("⇲"));
                            toggleli.setAttribute("id", "hide-button");
                            toggleli.setAttribute("title", "Hide Tracy");
                            toggleli.addEventListener("click", function() {
                                document.body.classList.remove("has-tracy-debugbar");
                                document.getElementById("tracy-debug").style.display = "none";
                                document.getElementById("tracy-show-button").style.display = "block";
                                document.cookie = "tracyHidden=1; path=/";
                                document.cookie = "tracyShow=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                            });
                            barul.replaceChild(toggleli, barul.lastChild.previousElementSibling);
                            window.Tracy.Debug.bar.restorePosition();
                        }
                    }
                    addHideLink();

                    function unhideBar() {
                        document.getElementById("tracy-debug").style.display = "block";
                        document.getElementById("tracy-show-button").style.display = "none";
                        window.Tracy.Debug.bar.restorePosition();
                        document.body.classList.add("has-tracy-debugbar");
                        document.cookie = "tracyHidden=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                        document.cookie = "tracyShow=1; path=/";
                    }

                    function modifyTracyLogo() {
                        if(!document.getElementById("tracy-debug-bar")) {
                            window.requestAnimationFrame(modifyTracyLogo);
                        } else {
                            var tracyLogo = document.getElementById("tracy-debug-logo");
                            tracyLogo.setAttribute("title", tracyLogo.getAttribute("title") + "\n\nTracy Debugger for PW '.$this->getModuleInfo()['version'].'\nClick to scroll to page top\nDouble-click to visit module settings");
                            tracyLogo.addEventListener("click", function() {
                                window.scrollTo(0,0);
                            }, false);
                            tracyLogo.addEventListener("dblclick", function() {
                                window.open("'.$this->wire('config')->urls->admin.'module/edit?name=TracyDebugger","_blank");
                            }, false);
                        }
                    }
                    modifyTracyLogo();

                    function bsZIndex() {
                        if(!document.getElementById("tracy-bs")) {
                            window.requestAnimationFrame(bsZIndex);
                        } else {
                            if(document.getElementById("tracy-bs"))
                                document.getElementById("tracy-bs").style.zIndex = ' . $this->data['panelZindex'] .';
                        }
                    }
                    bsZIndex();

                    window.Tracy.panelZIndex = ' . ($this->data['panelZindex'] + 1) . ';

                    document.addEventListener("keydown", function(e) {
                        if((e.keyCode==27||e.charCode==27)) {
                            var panels = document.getElementsByClassName("tracy-panel");
                            if(panels.length > 0) {
                                for (var i = 0; i < panels.length; i++) {
                                    window.Tracy.Debug.panels[panels[i].id].toPeek();
                                }
                            }
                        }
                    });

                    window.TracyMaxAjaxRows = '.$this->data['maxAjaxRows'].';

                ';

                if($this->data['hideDebugBar'] && $this->showServerTypeIndicator() && in_array('custom', $this->data['styleAdminType']) && !$this->wire('input')->cookie->tracyShow) {
                    // hide server type indicator bar if debug bar is hidden by default
                    Debugger::$customJsStr .= 'document.body.classList.add("tracyHidden");';
                    Debugger::$customJsStr .= 'document.body.classList.remove("has-tracy-debugbar");';
                }

                // remove localStorage items to prevent the panel from staying open
                Debugger::$customJsStr .= '
                    localStorage.removeItem("tracy-debug-panel-PanelSelectorPanel");
                    if(localStorage.getItem("remove-tracy-debug-panel-ProcesswireInfoPanel")) {
                        localStorage.removeItem("tracy-debug-panel-ProcesswireInfoPanel");
                        localStorage.removeItem("remove-tracy-debug-panel-ProcesswireInfoPanel")
                    }
                    if(localStorage.getItem("remove-tracy-debug-panel-RequestInfoPanel")) {
                        localStorage.removeItem("tracy-debug-panel-RequestInfoPanel");
                        localStorage.removeItem("remove-tracy-debug-panel-RequestInfoPanel")
                    }
                ';

                if(is_null($this->wire('session')->tracyDumpsRecorderItems)) {
                    // remove localStorage items to prevent the panel from staying open
                    Debugger::$customJsStr .= '
                        localStorage.removeItem("tracy-debug-panel-DumpsRecorderPanel");
                    ';
                }

                if(!$this->wire('input')->cookie->tracyHidden) {
                    Debugger::$customJsStr .= 'document.body.classList.add("has-tracy-debugbar");';
                }

            }


            // UPDATE CACHED API DATA on core version change
            $this->wire()->addHookBefore('SystemUpdater::coreVersionChange', null, function($event) {
                foreach(array('variables', 'core', 'coreModules') as $type) {
                    self::getApiData($type);
                }
            });


            // MAIL INTERCEPTOR
            // if mail panel items were cleared, clear them from the session variable
            if($this->wire('input')->cookie->tracyClearMailItems || (!in_array('mailInterceptor', static::$showPanels) && $this->wire('session')->tracyMailItems)) {
                $this->wire('session')->tracyMailItems = null;
                unset($this->wire('input')->cookie->tracyClearMailItems);
                setcookie("tracyClearMailItems", "", time()-3600, "/");
            }
            // mail panel intercept hook
            if(in_array('mailInterceptor', static::$showPanels)) {
                $this->wire()->addHookBefore('WireMail::send', $this, 'interceptEmails');
            }

            // TRACY CACHES
            // delete caches on module changes so they will be updated
            $this->wire()->addHookAfter('Modules::install', $this, 'deleteTracyCaches');
            $this->wire()->addHookAfter('Modules::uninstall', $this, 'deleteTracyCaches');
            $this->wire()->addHookAfter('Modules::moduleVersionChanged', $this, 'deleteTracyCaches');

            if(!static::$inAdmin) {
                // VALIDATOR
                if(in_array('validator', static::$showPanels)) {
                    $this->wire()->addHookAfter('ProcessWire::ready', function($event) {
                        if(!method_exists($event->page, 'render')) {
                            $event->page->addHookAfter('render', $this, 'getPageHtml', array('priority'=>1000));
                        }
                    });
                }

                // TEMPLATE RESOURCES
                if(!$this->wire('config')->ajax && (in_array('console', static::$showPanels) || in_array('templateResources', static::$showPanels))) {
                    $functions = get_defined_functions();
                    self::$initialFuncs = $functions['user'];
                    self::$initialConsts = get_defined_constants();
                    $this->wire()->addHookBefore('TemplateFile::render', function($event) {
                        // exclude template rendering from Hanna code because it breaks gathering of all template resources
                        if(!$event->object->hanna) {
                            $event->object->setAppendFilename(__DIR__ . '/includes/GetTemplateResources.php');
                        }
                    });
                }
            }


            // EVENT INTERCEPTOR
            // if event interceptor panel items were cleared, clear them from the session variable and remove cookie
            if($this->wire('input')->cookie->tracyClearEventItems || (!in_array('eventInterceptor', static::$showPanels) && $this->wire('session')->tracyEventItems)) {
                $this->wire('session')->tracyEventItems = null;
                unset($this->wire('input')->cookie->tracyClearEventItems);
                setcookie("tracyClearEventItems", "", time()-3600, "/");
            }
            // event interceptor hook
            if(in_array('eventInterceptor', static::$showPanels) && $this->wire('input')->cookie->eventInterceptorHook) {
                $this->hookSettings = json_decode($this->wire('input')->cookie->eventInterceptorHook, true);
                if($this->hookSettings['when'] == 'before') {
                    $this->wire()->addHookBefore($this->hookSettings['hook'], $this, 'interceptEvent');
                }
                else {
                    $this->wire()->addHookAfter($this->hookSettings['hook'], $this, 'interceptEvent');
                }
            }


            // DUMPS RECORDER
            // if dump recorder panel items were cleared, clear them from the session variable and remove cookie
            if($this->wire('input')->cookie->tracyClearDumpsRecorderItems) {
                $dumpsFile = $this->tracyCacheDir.'dumps.json';
                if(file_exists($dumpsFile)) unlink($dumpsFile);
                unset($this->wire('input')->cookie->tracyClearDumpsRecorderItems);
                setcookie("tracyClearDumpsRecorderItems", "", time()-3600, "/");
            }


            // PAGE RECORDER
            // page recorder hook
            if(in_array('pageRecorder', static::$showPanels)) {
                $this->wire()->addHookAfter('Pages::added', $this, 'recordPage');
            }


            // TRACY SETTINGS
            //convert checked location strings to constants and array_reduce to bitwise OR (|) line
            $locations = array_map('constant', $this->data['showLocation']);
            Debugger::$showLocation = array_reduce($locations, function($a, $b) { return $a | $b; }, 0);

            //convert checked log severity strings to constants and array_reduce to bitwise OR (|) line
            $severityOptions = array_map('constant', $this->data['logSeverity']);
            Debugger::$logSeverity = array_reduce($severityOptions, function($a, $b) { return $a | $b; }, 0);

            Debugger::$maxDepth = $this->data['maxDepth'];
            if(property_exists('Debugger', 'maxLength')) {
                Debugger::$maxLength = $this->data['maxLength'];
            }
            else {
                // backwards compatibility with older versions of Tracy core before
                // https://github.com/nette/tracy/commit/12d5cafa9264f2dfc3dfccb302a0eea404dcc24e
                if(isset(Debugger::$maxLen)) Debugger::$maxLen = $this->data['maxLength'];
            }
            Debugger::getFireLogger()->maxDepth = $this->data['maxDepth'];
            Debugger::getFireLogger()->maxLength = $this->data['maxLength'];
            Debugger::getBlueScreen()->maxDepth = $this->data['maxDepth'];
            Debugger::getBlueScreen()->maxLength = $this->data['maxLength'];

            Debugger::$strictMode = $this->data['strictMode'] || ($this->data['strictModeAjax'] && $this->wire('config')->ajax) || $this->wire('input')->cookie->tracyStrictMode ? TRUE : FALSE;
            Debugger::$scream = $this->data['forceScream'] ? TRUE : FALSE;

        }


        // ENABLE TRACY
        if($this->tracyEnabled) {
            Debugger::enable($outputMode, $logFolder, $this->data['fromEmail'] != '' && $this->data['email'] != '' ? $this->data['email'] : null);

            // don't email custom (writeError()) logged errors for Console panel via CodeProcessor.php
            if($this->wire('config')->ajax && $this->wire('input')->post->tracyConsole == 1) {
                Debugger::getLogger()->mailer = null;
            }
            else {
                Debugger::getLogger()->mailer = function($message) {
                    $m = $this->wire('mail')->new();
                    $m->from($this->data['fromEmail']);
                    $m->to($this->data['email']);
                    $m->subject('Error on server: ' . $this->wire('config')->urls->httpRoot);
                    $message = nl2br(\Tracy\Logger::formatMessage($message)) . "<br /><br />Remember to <a href='".$this->wire('config')->urls->httpAdmin."?clearEmailSent=1'>clear email sent flag</a> to receive future emails.";
                    $m->bodyHTML($message);
                    $m->send();
                };
            }
        }

        // fixes for when SessionHandlerDB module is installed
        if($this->wire('modules')->isInstalled('SessionHandlerDB') && Debugger::$showBar) {

            // ensure Tracy can show AJAX bars when SessionHandlerDB module is installed and debugbar is showing
            $this->wire()->addHookAfter('ProcessWire::finished', function() {
                if(ob_get_level() == 0) ob_start();
                Debugger::getBar()->render();
                Debugger::$showBar = false;
            }, array('priority' => 9999));

            // ensure Tracy can show Redirect bars when SessionHandlerDB module is installed and debugbar is showing
            if(!$this->wire('config')->disableTracySHDBRedirectFix) {
                $this->wire()->addHookBefore('Session::redirect', function($event) {
                    $url = $event->arguments[0];
                    $http301 = isset($event->arguments[1]) ? $event->arguments[1] : true;
                    if($http301) header("HTTP/1.1 301 Moved Permanently");
                    header("Location: $url");
                });
            }
        }


    }


    /**
     * Called when ProcessWire's API is ready
     */
    public function ready() {

        // USER BAR PAGE VERSIONS
        if(!static::$inAdmin && !$this->wire('config')->ajax && $this->wire('user')->isLoggedin() && $this->wire('user')->hasPermission('tracy-page-versions') && $this->data['showUserBar'] && in_array('pageVersions', $this->data['userBarFeatures'])) {
            static::$pageVersion = $this->findPageTemplateInCookie($this->wire('input')->cookie->tracyPageVersion);
            if(static::$pageVersion && static::$pageVersion != pathinfo($this->wire('page')->template->filename, PATHINFO_BASENAME)) {
                static::$templatePath = static::$pageVersion;
            }
            elseif(static::$pageVersion != '') {
                // don't change as already set by dev template permission functionality
            }
            else {
                static::$templatePath = '';
            }

            if(static::$templatePath != '') {
                // template file
                $this->wire('page')->template->filename = static::$templatePath;
                // auto appended/prepended files
                $this->replaceAutoAppendedPreprendedTemplateFiles($this->getFileSuffix(static::$templatePath));
            }
            unset($this->wire('input')->cookie->tracyPageVersion);
            setcookie("tracyPageVersion", "", time()-3600, "/");
        }


        // USER DEV TEMPLATES
        // if user dev template enabled and required permission exists in system,
        // check if current user has a matching permission for the current page's template, or the "tracy-all-suffix" permission
        if($this->data['userDevTemplate'] && ($this->wire('permissions')->get("tracy-".$this->wire('page')->template->name."-".$this->data['userDevTemplateSuffix'])->id || $this->wire('permissions')->get("tracy-all-".$this->data['userDevTemplateSuffix'])->id)) {
            if(self::userHasPermission("tracy-".$this->wire('page')->template->name."-".$this->data['userDevTemplateSuffix']) ||
                self::userHasPermission("tracy-all-".$this->data['userDevTemplateSuffix'])
            ) {
                // template file
                $devTemplateFilename = $this->appendSuffixToFile($this->wire('page')->template->filename, $this->data['userDevTemplateSuffix']);
                if(file_exists($devTemplateFilename)) $this->wire('page')->template->filename = static::$templatePath = static::$templatePathPermission = $devTemplateFilename;
                // auto appended/prepended files
                $this->replaceAutoAppendedPreprendedTemplateFiles($this->data['userDevTemplateSuffix']);
            }
        }

        // Exit now if "enabled" not true from init()
        if(!$this->tracyEnabled) return;

        if(static::$allowedTracyUser === 'development') {

            // if it's an ajax request for the File Editor feature
            if($this->wire('config')->ajax && isset($this->wire('input')->post->filePath)) {
                require_once(__DIR__ . '/includes/GetFileDetails.php');
                return;
            }

            // if it's an ajax request from the ProcessWire Info GoTo Page ID feature
            if($this->wire('config')->ajax && isset($this->wire('input')->post->goToPage)) {
                require_once(__DIR__ . '/includes/GetPageById.php');
                return;
            }

            // CONSOLE PANEL CODE INJECTION
            $this->insertCode('ready');


            // FILE/TEMPLATE EDITOR
            if(static::$allowedSuperuser || self::$validLocalUser || self::$validSwitchedUser) {
                if($this->wire('input')->post->fileEditorFilePath) {
                    $rawCode = base64_decode($this->wire('input')->post->tracyFileEditorRawCode);
                    if(static::$inAdmin &&
                        $this->data['referencePageEdited'] &&
                        $this->wire('input')->get('id') &&
                        $this->wire('pages')->get($this->wire('input')->get('id'))->template->filename === $this->wire('config')->paths->root . $this->wire('input')->post->fileEditorFilePath
                    ) {
                        $p = $this->wire('pages')->get($this->wire('input')->get('id'));
                    }
                    else {
                        $p = $this->wire('page');
                    }

                    $templateExt = pathinfo($p->template->filename, PATHINFO_EXTENSION);
                    $this->tempTemplateFilename = str_replace('.'.$templateExt, '-tracytemp.'.$templateExt, $p->template->filename);
                    // if changes to the template of the current page are submitted
                    // test
                    if($this->wire('input')->post->tracyTestTemplateCode) {
                        if(!file_put_contents($this->tempTemplateFilename, $rawCode, LOCK_EX)) throw new WireException("Unable to write file: " . $this->tempTemplateFilename);
                        if($this->wire('config')->chmodFile) chmod($this->tempTemplateFilename, octdec($this->wire('config')->chmodFile));
                        $p->template->filename = $this->tempTemplateFilename;
                    }

                    // if changes to any other file are submitted
                    if($this->wire('input')->post->tracyTestFileCode || $this->wire('input')->post->tracySaveFileCode || $this->wire('input')->post->tracyChangeTemplateCode) {
                        if($this->wire('input')->post->fileEditorFilePath != '' && strpos($this->wire('input')->post->fileEditorFilePath, '..') === false) {
                            $filePath = $this->wire('config')->paths->root . $this->wire('input')->post->fileEditorFilePath;
                            $rawCode = base64_decode($this->wire('input')->post->tracyFileEditorRawCode);

                            // backup old version to Tracy cache directory
                            $cachePath = $this->tracyCacheDir . $this->wire('input')->post->fileEditorFilePath;
                            if(!is_dir($cachePath)) if(!wireMkdir(pathinfo($cachePath, PATHINFO_DIRNAME), true)) {
                                throw new WireException("Unable to create cache path: $cachePath");
                            }
                            copy($filePath, $cachePath);

                            if(!file_put_contents($filePath, $rawCode, LOCK_EX)) throw new WireException("Unable to write file: " . $filePath);
                            if($this->wire('config')->chmodFile) chmod($filePath, octdec($this->wire('config')->chmodFile));

                            if($this->wire('input')->post->tracyTestFileCode) setcookie('tracyTestFileEditor', $this->wire('input')->post->fileEditorFilePath, time() + (10 * 365 * 24 * 60 * 60), '/');
                        }
                        $this->wire('session')->redirect($this->httpReferer);
                    }
                }

                // if file editor restore
                if($this->wire('input')->post->tracyRestoreFileEditorBackup) {
                    $this->filePath = $this->wire('config')->paths->root . ($this->wire('input')->post->fileEditorFilePath ?: $this->wire('input')->cookie->tracyTestFileEditor);
                    $this->cachePath = $this->tracyCacheDir . ($this->wire('input')->post->fileEditorFilePath ?: $this->wire('input')->cookie->tracyTestFileEditor);
                    copy($this->cachePath, $this->filePath);
                    unlink($this->cachePath);
                    $this->wire('session')->redirect($this->httpReferer);
                }
            }


            // DEBUG BAR & PANELS

            // hide debug bar from specified templates
            if(static::$inAdmin && count($this->data['hideDebugBarBackendTemplates']) > 0) {
                if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessPageEdit') {
                    $p = $this->wire('pages')->get($this->wire('input')->get('id'));
                    if($p->id && in_array($p->template->id, $this->data['hideDebugBarBackendTemplates'])) Debugger::$showBar = false;
                }
            }
            if(!static::$inAdmin && count($this->data['hideDebugBarFrontendTemplates']) > 0) {
                $p = $this->wire('page');
                if($p->id && in_array($p->template->id, $this->data['hideDebugBarFrontendTemplates'])) Debugger::$showBar = false;
            }

        }

        // exit now if not showing debug bar or user doesn't have 'development' access
        if(Debugger::$showBar == false || static::$allowedTracyUser !== 'development') return;


        // check for any "bd_" wire variables and barDump them
        $pwVars = function_exists('wire') ? $this->fuel : \ProcessWire\wire('all');
        foreach($pwVars->getArray() as $key => $val) {
            if(strpos($key, 'bd_') !== false) \TD::barDump($val, '$'.$key);
        }


        // load File Editor panel if Tracy online editor is selected
        if(static::$useOnlineEditor && static::$onlineEditor == 'tracy' && !in_array('fileEditor', static::$showPanels)) {
            array_push(static::$showPanels, 'fileEditor');
        }


        // LOAD SPECIFIED PANELS
        foreach(static::$showPanels as $panel) {
            if(!array_key_exists($panel, static::$allPanels)) continue;
            if(static::$inAdmin && in_array($panel, static::$hideInAdmin)) continue;
            if((in_array($panel, self::$superUserOnlyPanels)) && !static::$allowedSuperuser && !self::$validLocalUser && !self::$validSwitchedUser) continue;
            // special additional check for adminer
            if($panel == 'adminer' && !static::$allowedSuperuser) continue;
            if($panel == 'userSwitcher') {
                if(isset($this->data['userSwitchSession'])) $userSwitchSession = $this->data['userSwitchSession'];
                if(!static::$allowedSuperuser && (!$this->wire('session')->tracyUserSwitcherId || (isset($userSwitchSession[$this->wire('session')->tracyUserSwitcherId]) && $userSwitchSession[$this->wire('session')->tracyUserSwitcherId] <= time()))) continue;
            }
            // ignore disabled panels for restricted users
            if(!empty(static::$restrictedUserDisabledPanels) && in_array($panel, static::$restrictedUserDisabledPanels)) continue;

            $panelName = ucfirst($panel).'Panel';
            if(file_exists(__DIR__ . '/panels/'.$panelName.'.php')) {
                require_once __DIR__ . '/panels/'.$panelName.'.php';
            }
            else {
                // external panels
                include_once static::$externalPanels[$panel];
            }
            switch($panel) {
                case 'performance':
                    break;
                case 'templatePath':
                    static::$templatePathOnce = $this->findPageTemplateInCookie($this->wire('input')->cookie->tracyTemplatePathOnce);
                    static::$templatePathSticky = $this->findPageTemplateInCookie($this->wire('input')->cookie->tracyTemplatePathSticky);
                    if(static::$templatePathOnce) {
                        static::$templatePath = static::$templatePathOnce;
                        unset($this->wire('input')->cookie->tracyTemplatePathOnce);
                        setcookie("tracyTemplatePathOnce", "", time()-3600, "/");
                    }
                    elseif(static::$templatePathSticky) {
                        static::$templatePath = static::$templatePathSticky;
                    }
                    elseif(static::$templatePath != '') {
                        // don't change as already set by dev template permission functionality
                    }
                    else {
                        static::$templatePath = '';
                    }

                    if(static::$templatePath != '') {
                        // template file
                        $this->wire('page')->template->filename = static::$templatePath;
                        // auto appended/prepended files
                        $this->replaceAutoAppendedPreprendedTemplateFiles($this->getFileSuffix(static::$templatePath));
                    }
                    Debugger::getBar()->addPanel(new $panelName);
                    break;
                default:
                    Debugger::getBar()->addPanel(new $panelName);
                    break;
            }
        }
        // load custom replacement Dumps panel - this is not optional/configurable
        // at the end so it has access to bd() calls in any of the other panels - helpful for debugging these panels
        $panelName = 'DumpsPanel';
        require_once __DIR__ . '/panels/'.$panelName.'.php';
        Debugger::getBar()->addPanel(new $panelName);


        // USER SWITCHER
        // process userSwitcher if panel open and switch initiated
        if(in_array('userSwitcher', static::$showPanels) && $this->wire('input')->post->userSwitcher) {
            // if user is superuser and session length is set, save to config settings
            if(static::$allowedSuperuser && $this->wire('input')->post->userSwitchSessionLength && $this->wire('session')->CSRF->validate()) {
                // cleanup expired sessions
                if(isset($this->data['userSwitchSession'])) {
                    foreach($this->data['userSwitchSession'] as $id => $expireTime) {
                        if($expireTime < time()) unset($this->data['userSwitchSession'][$id]);
                    }
                }
                // if no existing session ID, start a new session
                if(!$this->wire('session')->tracyUserSwitcherId) {
                    $pass = new Password();
                    $challenge = $pass->randomBase64String(32);
                    $this->wire('session')->tracyUserSwitcherId = $challenge;
                }
                // save session ID and expiry time in module config settings
                $this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId] = time() + ($this->wire('input')->post->userSwitchSessionLength * 60);
                $this->wire('modules')->saveModuleConfigData($this, $this->data);
            }
            // if logout button clicked
            if($this->wire('input')->post->logoutUserSwitcher && $this->wire('session')->CSRF->validate()) {
                if($this->wire('session')->tracyUserSwitcherId) {
                    // if session variable exists, grab it and add to the new session after logging out
                    $tracyUserSwitcherId = $this->wire('session')->tracyUserSwitcherId;
                    $this->wire('session')->logout();
                    $this->wire('session')->tracyUserSwitcherId = $tracyUserSwitcherId;
                }
                else {
                    $this->wire('session')->logout();
                }
                $this->wire('session')->redirect($this->httpReferer);
            }
            // if end session clicked, remove session variable and config settings entry
            elseif($this->wire('input')->post->endSessionUserSwitcher && $this->wire('session')->CSRF->validate()) {
                $this->wire('session')->remove("tracyUserSwitcherId");
                unset($this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId]);
                $this->wire('modules')->saveModuleConfigData($this, $this->data);
                $this->wire('session')->redirect($this->httpReferer);
            }
            // if session not expired, switch to requested user
            elseif($this->wire('input')->post->userSwitcher && $this->wire('session')->CSRF->validate()) {
                if(isset($this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId]) && $this->data['userSwitchSession'][$this->wire('session')->tracyUserSwitcherId] > time() && $this->wire('session')->tracyUserSwitcherId) {
                    // if session variable exists, grab it and add to the new session after logging out
                    // and forceLogin the new switched user
                    $tracyUserSwitcherId = $this->wire('session')->tracyUserSwitcherId;
                    if($this->wire('user')->isLoggedin()) $this->wire('session')->logout();
                    $user = $this->wire('session')->forceLogin($this->wire('input')->post->userSwitcher);
                    $this->wire('session')->tracyUserSwitcherId = $tracyUserSwitcherId;
                }
                if($this->wire('pages')->get(self::inputUrl())->viewable) {
                    $this->wire('session')->redirect($this->httpReferer);
                }
                else {
                    $this->wire('session')->redirect(static::$inAdmin ? $this->wire('config')->urls->admin : $this->wire('config')->urls->root);
                }
            }
        }


        // if it's an ajax request from the Tracy Console panel snippets, then process and return
        if($this->wire('config')->ajax && $this->wire('input')->post->tracysnippets == 1) {
            require_once(__DIR__ . '/includes/ConsoleSnippets.php');
            return;
        }

    }


    /**
     * FUNCTIONS CALLED FROM HOOKS
     */


    /**
     * Hook after Page::render()
     *
     * Add the User bar
     *
     * @param HookEvent $event
     *
     */
    protected function addUserBar($event) {
        $userBar = '';
        require_once __DIR__ . '/includes/user-bar/UserBar.php';
        $return = $event->return;
        $return = str_replace("</head>", "\n<!-- Tracy User Bar -->\n" . static::minify($userBarStyles)."\n</head>", $return);
        $return = str_replace("</body>", "\n<!-- Tracy User Bar -->\n" . static::minify($userBar)."\n</body>", $return);
        $event->return = $return;
    }


    /**
     * Hook after Page::render()
     *
     * Adds Enable button to page if the Tracy Toggler has currently disabled it
     *
     * @param HookEvent $event
     *
     */
    protected function addEnableButton($event) {

        // DON'T add comments to injected code below because it breaks my simple minify() function
        // if Tracy temporarily toggled disabled, add enable icon link
        $enableButton = '
        <style>
            div#TracyEnableButton {
                bottom: 10px !important;
                '.($this->data['barPosition'] == 'bottom-left' ? 'left' : 'right').':10px !important;
                z-index: 99999 !important;
                position: fixed !important;
                width: 16px !important;
                height: 16px !important;
                margin: 0px !important;
                padding: 0px !important;
                cursor:pointer !important;
            }
            div#TracyEnableButton svg {
                width: 16px !important;
                height: 16px !important;
            }
        </style>
        <script>
            function enableTracy() {
                document.cookie = "tracyDisabled=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                location.reload();
            }
        </script>
        <div id="TracyEnableButton" title="Enable Tracy" onclick="enableTracy()">
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="16.6px" viewBox="199.6 129.9 16 16.6" enable-background="new 199.6 129.9 16 16.6" xml:space="preserve">
            <path fill="'.self::COLOR_NORMAL.'" d="M215.4,139.4c-0.1-0.1-0.3-0.2-0.4-0.2h-1v0c0-0.4-0.1-0.8-0.1-1.2c-0.1-0.7-0.4-1.4-0.8-2l1.5-1.5
                c0.1-0.1,0.2-0.3,0.2-0.5s-0.1-0.3-0.2-0.4c-0.1-0.1-0.3-0.2-0.4-0.2s-0.3,0.1-0.4,0.2l-1.4,1.4c-0.3-0.3-0.7-0.6-1-0.9
                c-0.1-0.1-0.3-0.2-0.4-0.3c0,0,0,0,0.1,0v0c0-0.7-0.3-1.2-0.7-1.7l0.8-1.2c0.1-0.1,0.1-0.3,0.1-0.5c0-0.2-0.1-0.3-0.3-0.4
                s-0.3-0.1-0.5-0.1c-0.2,0-0.3,0.1-0.4,0.3l-0.8,1.1c-0.5-0.3-1-0.4-1.5-0.4c-0.5,0-1,0.1-1.4,0.3l-0.7-1.1c-0.1-0.1-0.2-0.2-0.4-0.3
                s-0.3,0-0.5,0.1c-0.1,0.1-0.2,0.2-0.3,0.4s0,0.3,0.1,0.5l0.8,1.1c-0.4,0.5-0.7,1-0.7,1.7h0c0,0,0,0,0,0c-0.4,0.2-0.9,0.5-1.2,0.9
                c-0.1,0.1-0.2,0.2-0.3,0.3c0,0,0,0,0,0l-1.2-1.2c-0.1-0.1-0.3-0.2-0.4-0.2s-0.3,0.1-0.5,0.2c-0.1,0.1-0.2,0.3-0.2,0.4
                c0,0.2,0.1,0.3,0.2,0.5l1.3,1.3c-0.3,0.5-0.6,1.1-0.7,1.6c-0.2,0.6-0.2,1.2-0.2,1.8h-0.9c-0.2,0-0.3,0.1-0.4,0.2
                c-0.1,0.1-0.2,0.3-0.2,0.4c0,0.2,0.1,0.3,0.2,0.4c0.1,0.1,0.3,0.2,0.4,0.2h1.1c0.1,0.6,0.4,1.1,0.4,1.1c0.1,0.2,0.2,0.2,0.2,0.3
                c0.2,0.1,0.7,0,1-0.3c0,0,0-0.1,0-0.1c-0.1-0.3-0.1-0.6-0.1-0.8c-0.1-0.4-0.1-1-0.1-1.7c0-0.3,0.1-0.7,0.2-1
                c0.2-0.7,0.7-1.5,1.4-2.1c0.8-0.7,1.7-1.1,2.7-1.2c0.3,0,0.9-0.1,1.7,0.1c0.2,0,0.8,0.2,1.6,0.7c0.5,0.4,1,0.8,1.3,1.3
                c0.3,0.4,0.6,1.1,0.7,1.7c0.1,0.6,0.1,1.2,0,1.8c-0.1,0.6-0.3,1.2-0.7,1.7c-0.2,0.4-0.7,0.9-1.3,1.4c-0.5,0.4-1.1,0.6-1.7,0.8
                c-0.3,0.1-0.6,0.1-0.9,0.1c-0.3,0-0.7,0-0.9,0c-0.4-0.1-0.5-0.2-0.6-0.3c0,0-0.1-0.1-0.1-0.4c0-2.3,0-1.7,0-2.9c0-0.3,0-0.7,0-0.9
                c0-0.5,0.1-0.8,0.4-1.1c0.2-0.3,0.6-0.4,0.9-0.4c0.1,0,0.5,0,0.8,0.3c0.4,0.3,0.4,0.7,0.4,0.8c0.1,0.7-0.3,1.1-0.5,1.3
                c-0.2,0.1-0.4,0.2-0.5,0.3c-0.3,0.1-0.6,0.1-0.8,0.1c0,0-0.1,0-0.1,0.1l-0.1,0.5c-0.1,0.4,0.1,0.5,0.2,0.5c0.4,0.1,0.7,0.1,1,0.1
                c0.6,0,1.1-0.3,1.6-0.7c0.4-0.4,0.7-0.9,0.7-1.4c0.1-0.6,0-1.2-0.3-1.8c-0.3-0.6-0.8-1.1-1.5-1.4s-1.2-0.3-1.9-0.1l0,0
                c-0.5,0.1-0.9,0.4-1.3,0.8c-0.3,0.3-0.5,0.7-0.7,1c-0.1,0.4-0.2,0.7-0.2,1.2c0,0.3,0,0.7,0,1v2c0,0.6,0,0.7,0,1.1
                c0,0.2,0,0.5,0.1,0.7c0.1,0.3,0.3,0.6,0.4,0.7c0.2,0.2,0.4,0.4,0.7,0.5c0.6,0.2,1.3,0.3,1.9,0.3c0.4,0,0.8-0.1,1.2-0.2
                c0.8-0.2,1.6-0.5,2.3-1c0,0,0.1,0,0.1-0.1l1.9,1.9c0.1,0.1,0.3,0.2,0.4,0.2c0.2,0,0.3-0.1,0.4-0.2c0.1-0.1,0.2-0.3,0.2-0.4
                c0-0.2-0.1-0.3-0.2-0.4l-1.8-1.8c0.3-0.3,0.5-0.6,0.7-0.9c0.4-0.7,0.7-1.5,0.9-2.3h1.1c0.2,0,0.3-0.1,0.4-0.2
                c0.1-0.1,0.2-0.3,0.2-0.4C215.6,139.6,215.6,139.5,215.4,139.4L215.4,139.4z"/>
            </svg>
        </div>';

        $event->return =  str_replace("</body>", "\n<!-- Tracy Enable Button -->\n" . static::minify($enableButton)."\n</body>", $event->return);

    }


    /**
     * Hook after Page::render()
     *
     * Gets the HTML of the page for the Validator Panel
     *
     * @param HookEvent $event
     *
     */
    protected function getPageHtml($event) {
        static::$pageHtml = $event->return;
    }


    /**
     * Hook before WireMail::send
     *
     * Intercepts outgoing emails for the Mail Interceptor Panel
     *
     * @param HookEvent $event
     *
     */
    protected function interceptEmails($event) {
        $mailItem = array();
        $mailItems = $this->wire('session')->tracyMailItems ? $this->wire('session')->tracyMailItems : array();
        foreach(array('subject', 'from', 'fromName', 'to', 'toName', 'cc', 'ccName', 'bcc', 'body', 'bodyHTML', 'attachments', 'header', 'priority', 'dispositionNotification', 'addSignature', 'sendSingle', 'sendBulk', 'useSentLog', 'wrapText', 'default_charset', 'sender_signature', 'sender_signature_html') as $param) {
            $mailItem[$param] = $event->object->$param;
        }
        $mailItem['timestamp'] = time();
        array_push($mailItems, $mailItem);
        $this->wire('session')->tracyMailItems = $mailItems;

        if($this->wire('input')->cookie->tracyTestEmail) {
            $event->object->to();
            $event->object->cc();
            $event->object->bcc();
            $event->object->to($this->wire('input')->cookie->tracyTestEmail);
        }
        else {
            $event->replace = true;
        }
        $event->return = true;
    }


    /**
     * Hook after Module::install, uninstall, or version change
     *
     * Delete existing Tracy caches
     *
     * @param HookEvent $event
     *
     */
    protected function deleteTracyCaches($event) {
        // don't delete caches if it's a core module update because if you're in the admin this deletes them
        // and the system update will trigger generation of new caches anyway. Without this check, the "New Since" in the
        // API Explorer panel no longer had access to the cached API data.
        if($event->method == 'moduleVersionChanged') {
            $info = $this->modules->getModuleInfoVerbose($event->arguments[0]);
            if($info['core']) return;
        }
        $this->deleteCache('TracyApiData.*');
    }

    private function deleteCache($cache) {
        $this->wire('cache')->delete($cache);
    }

    /**
     * Hook before various
     *
     * Intercepts calls to defined hook and dumps result to Dumps Panel
     *
     * @param HookEvent $event
     *
     */
    // for the event interceptor panel
    protected function interceptEvent($event) {
        $eventItem = array();
        $eventItems = $this->wire('session')->tracyEventItems ? $this->wire('session')->tracyEventItems : array();
        $eventItem['timestamp'] = time();
        $options = array();
        $options[Dumper::COLLAPSE] = true;
        $options[Dumper::DEPTH] = isset($options['maxDepth']) ? $options['maxDepth'] : $this->data['maxDepth'];
        $options[Dumper::TRUNCATE] = isset($options['maxLength']) ? $options['maxLength'] : $this->data['maxLength'];
        $eventItem['object'] = Dumper::toHtml($event->object, $options);
        $eventItem['arguments'] = Dumper::toHtml($event->arguments, $options);
        array_push($eventItems, $eventItem);
        $this->wire('session')->tracyEventItems = $eventItems;
        if($this->hookSettings['return'] == 'false') {
            $event->replace = true;
            $event->return = false;
        }
    }


    /**
     * Hook after Pages::added
     *
     * Record the id of all added pages for the Page Recorder Panel
     *
     * @param HookEvent $event
     *
     */
    // for the page recorder panel
    protected function recordPage($event) {
        $p = $event->arguments(0);
        if($p->is("has_parent=".$this->wire('config')->adminRootPageID)) return;
        $this->data['recordedPages'][] = $p->id;
        $this->wire('modules')->saveModuleConfigData($this, $this->data);
    }


    /**
     * LOCAL HELPER FUNCTIONS
     */


    /**
     * Get file suffix
     *
     * @param string $path Full path to file
     * @return string $suffix File's suffix
     *
     */
    private function getFileSuffix($path) {
        $path = pathinfo($path, PATHINFO_FILENAME);
        return substr($path, strrpos($path, '-') + 1);
    }


    /**
     * Append suffix to file path
     *
     * @param string $path Full path to file
     * @return string $path Full path to file with new appended extension
     *
     */
    private function appendSuffixToFile($path, $suffix) {
        $templateExt = pathinfo($path, PATHINFO_EXTENSION);
        return str_replace('.'.$templateExt, '-'.$suffix.'.'.$templateExt, $path);
    }


    /**
     * Replace auto appended / prepended Template files (_main.php, _init.php etc) with "dev" versions
     *
     * @param string $suffix Suffix to add to filename
     *
     */
    private function replaceAutoAppendedPreprendedTemplateFiles($suffix) {
        // appendeded template file
        if($this->wire('config')->appendTemplateFile) {
            $devAppendedTemplateFilename = $this->appendSuffixToFile($this->wire('config')->appendTemplateFile, $suffix);
            if(file_exists($this->wire('config')->paths->templates . $devAppendedTemplateFilename)) $this->wire('config')->appendTemplateFile = $devAppendedTemplateFilename;
        }

        // prependeded template file
        if($this->wire('config')->prependTemplateFile) {
            $devPrependedTemplateFilename = $this->appendSuffixToFile($this->wire('config')->prependTemplateFile, $suffix);
            if(file_exists($this->wire('config')->paths->templates . $devPrependedTemplateFilename)) $this->wire('config')->prependTemplateFile = $devPrependedTemplateFilename;
        }
    }


    /**
     * Delete File
     *
     * Check if file exists before unlink
     *
     * @param string $path Full path to file
     *
     */
    private function deleteFile($path) {
        if(file_exists($path)) unlink($path);
    }


    /**
     * Minify
     *
     * Remove line breaks and unnecessary whitespace
     *
     * @param string $str String to minify
     *
     */
    public static function minify($str) {
        return preg_replace(array('#^\s*//.+$#m', '/\/\*.*?\*\//s', '/ {2,}/','/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'),array('', '', ' ', ''), $str);
    }


    /**
     * Superuser permission check
     *
     * Actually checks if a user really has the permission
     * This works for superusers as well whereas normally they return true for any permission
     *
     * @param string|int|User Maybe User name, object, or ID. Optional user to check
     * @param string $permission Permission name
     * @return bool
     *
     */
    private static function userHasPermission($permission) {
        foreach(wire('user')->roles as $role) {
            foreach($role->permissions as $perm) {
                if($perm->name == $permission) return true;
            }
        }
        return false;
    }


    /**
     * Are we in the Admin (or frontend)
     *
     * Checks based on URL, so it works before ready()
     *
     * @return bool
     *
     */
    private function inAdmin() {
        if(strpos(self::inputUrl(), $this->wire('config')->urls->admin) === 0 || ($this->wire('config')->ajax && strpos($this->httpReferer, $this->wire('config')->urls->admin) !== false)) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * Find current page template name in cookie
     *
     * Actually checks if a user really has the permission
     * This works for superusers as well whereas normally they return true for any permission
     *
     * @param string $cookie Cookie content
     * @return string|bool Name of Template or false
     *
     */
    private function findPageTemplateInCookie($cookie) {
        $templateEntries = explode(',', $cookie);
        foreach($templateEntries as $templateEntry) {
            $arr = explode("|", $templateEntry, 2);
            if($arr[0] == $this->wire('page')->template->name) {
                return $arr[1];
                break;
            }
        }
        return false;
    }


    /**
     * Determine if server type indicator should be shown
     *
     * @return bool
     *
     */
    private function showServerTypeIndicator() {
        if(
            count($this->data['styleWhere']) > 0 &&
            ((static::$inAdmin && in_array('backend', $this->data['styleWhere'])) ||
                (!static::$inAdmin && in_array('frontend', $this->data['styleWhere'])))
        ) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * Determine server type and return styled color
     *
     * Local or Live based on IP address and dev or staging based on subdomain
     *
     * @return array
     *
     */
    private function getServerAdminStyles() {
        $serverTypeMatch = false;
        $stylesArr = array();

        foreach(explode("\n", $this->data['styleAdminColors']) as $serverType) {
            preg_match_all("/([^\|]+)\|([^\|]+)/", $serverType, $p);
            if(isset($p[1][0]) && isset($p[2][0])) $stylesArr[$p[1][0]] = $p[2][0];
        }

        foreach($stylesArr as $type => $color) {
            if(strpos($this->wire('config')->urls->httpRoot, str_replace('*', '', $type)) !== false) {
                $serverTypeMatch = true;
                break;
            }
        }

        if(!$serverTypeMatch && static::$isLocal) {
            $serverTypeMatch = true;
            $type = 'local';
        }

        return array(
            'serverTypeMatch' => $serverTypeMatch,
            'styles' => $stylesArr,
            'type' => $type
        );
    }


    /**
     * Set the favicon server type badge
     *
     * Local or Live based on IP address and dev or staging based on subdomain
     *
     * @return string Javacript code
     *
     */
    private function setFaviconBadge() {

        $serverTypeMatch = $this->serverStyleInfo['serverTypeMatch'];
        $stylesArr = $this->serverStyleInfo['styles'];
        $type = $this->serverStyleInfo['type'];

        if($serverTypeMatch && isset($stylesArr[$type])) {
            return '
                Tinycon.setOptions({
                    background: "'.$stylesArr[$type].'",
                    fallback: true
                });
                Tinycon.setBubble("'.substr(trim(strtoupper(str_replace('*', '', $type)), '.'), 0, 2).'");
            ';
        }
        else {
            return;
        }

    }


    /**
     * Output debug bar badge or custom (sidebar by default) server type indicator

     *
     * @return string style definition
     *
     */
    private function setServerAdminStyleColor() {

        $serverTypeMatch = $this->serverStyleInfo['serverTypeMatch'];
        $stylesArr = $this->serverStyleInfo['styles'];
        $type = $this->serverStyleInfo['type'];

        if($serverTypeMatch && isset($stylesArr[$type])) {
            $out = '';
            if(in_array('custom', $this->data['styleAdminType'])) {
                $out .= '
                    <style>
                        '. str_replace(array('[color]', '[type]'), array($stylesArr[$type], str_replace('*', '', $type)), $this->data['styleAdminElements']) . '
                    </style>
                ';
            }
            if(in_array('default', $this->data['styleAdminType'])) {
                $out .= '
                    <style>
                        #tracy-debug-logo::before {
                            content: "'.str_replace('*', '', $type).'";
                            background: '.$stylesArr[$type].';
                            color: #ffffff;
                            padding: 4px 8px;
                            text-align: center;
                            font-family: sans-serif;
                            font-weight: 600;
                            text-transform: uppercase;
                            z-index: 999999;
                            font-size: 12px;
                            height: 13px;
                            line-height: 13px;
                            pointer-events: none;
                        }
                    </style>
                ';
            }

            return $out;

        }
        else {
            return;
        }

    }

    /**
     * Insert console code at runtime into init, ready, or finished
     *
     * @param string $when | init, ready, or finished
     *
     */
    private function insertCode($when) {
        if((!static::$allowedSuperuser && !self::$validLocalUser && !self::$validSwitchedUser) ||
            $this->wire('config')->ajax ||
            $this->wire('input')->cookie->tracyCodeError ||
            !$this->wire('input')->cookie->tracyIncludeCode)
                return;

        $options = json_decode($this->wire('input')->cookie->tracyIncludeCode, true);
        if($options['when'] !== $when) return;

        // populate API variables, eg so $page equals $this->wire('page')
        $pwVars = function_exists('wire') ? $this->fuel : \ProcessWire\wire('all');
        foreach($pwVars->getArray() as $key => $value) {
            $$key = $value;
        }
        $page = $pages->get((int)$options['pid']);
        $consoleCodeFile = $this->tracyCacheDir . 'consoleCode.php';
        if(file_exists($consoleCodeFile)) {
            require_once($consoleCodeFile);
        }
        else {
            unset($_COOKIE['tracyIncludeCode']);
            $this->wire('input')->cookie->remove('tracyIncludeCode');
        }
    }


    /**
     * attach logRequests via hook
     *
     * @param HookEvent $event
     * @return void
     */
    public function logRequests(HookEvent $event) {

        $page = $event->object;

        // exits
        if(!isset($this->data['requestLoggerPages']) || !in_array($page->id, $this->data['requestLoggerPages'])) return;
        if(!in_array($_SERVER['REQUEST_METHOD'], $this->data['requestMethods'])) return;

        // log this request
        $data = $page->getRequestData();
        $data = json_decode(json_encode($data), true);
        if(!empty($data)) {
            $data['html'] = $event->return;
            $this->wire('cache')->save("tracyRequestLogger_id_{$data['id']}_page_{$page->id}", $data);

            // do the cleanup to limit entries to max logs setting
            $allData = $this->wire('cache')->get("tracyRequestLogger_id_*_page_{$page->id}");
            if(count($allData) > $this->data['requestLoggerMaxLogs']) {
                reset($allData);
                $this->wire('cache')->delete(key($allData));
            }
        }
    }

    /**
     * get a standardized array or object of the current request
     *
     * @param HookEvent $event
     * @return array | object
     */
    public function getRequestData($event) {
        $page = $event->object;
        if(isset($event->arguments[0]) && $event->arguments[0] !== false) {
            // get all logged requests for $page
            if($event->arguments[0] === 'all') {
                $data = $this->_getRequestLoggerData($page->id, true);
            }
            // get logged request by ID
            elseif(!is_bool($event->arguments[0])) {
                $data = $this->_getRequestLoggerData($event->arguments[0]);
            }
            // get last logged request for $page
            elseif($event->arguments[0] === true) {
                $data = $this->_getRequestLoggerData($page->id);
            }

            // remove logged entries for PW 404 page if they don't have the same URL as current page
            if(!empty($data)) {
                foreach($data as $id => $datum) {
                    if($page->id === $this->wire('config')->http404PageID && !self::$inAdmin && $datum['httpUrl'] !== ($this->wire('config')->https ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) {
                        unset($data[$id]);
                    }
                }
            }
        }
        // get current request for $page
        else {
            $input = file_get_contents('php://input');
            $data = array(
                'id' => uniqid(),
                'requestMethod' => $_SERVER['REQUEST_METHOD'],
                'remoteHost' => isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null,
                'remoteAddress' => $_SERVER['REMOTE_ADDR'],
                'time' => time(),
                'url' => $this->wire('input')->url(true),
                // get url via server variables
                // the pw built in httpUrl method replaces the actual host with one of the hosts in $config
                'httpUrl' => ($this->wire('config')->https ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                'page' => $page->id,
                'html' => null,
                'headers' => $this->getallheaders(),
                'post' => $_POST,
                'get' => $_GET,
                'input' => $input,
                'inputParsed' => self::isJson($input) ? json_decode($input, true) : null
            );
        }

        if(isset($event->arguments[1]) && $event->arguments[1] === true) {
            // intentionally blank - don't modify $data from being array
        }
        elseif($this->data['requestLoggerReturnType'] == 'object') {
            $data = json_decode(json_encode($data), false);
        }

        $event->return = $data;
    }


    /**
     * get logged request data
     *
     * @param int $pid
     * @param bool $all
     * @return $data
     */
    private function _getRequestLoggerData($id, $all = false) {
        // $id is an integer, so it's a page ID
        if(is_int($id)) {
            $data = $this->wire('cache')->get("tracyRequestLogger_id_*_page_$id");
        }
        // it's a logged request ID
        else {
            $data = wire('cache')->get("tracyRequestLogger_id_$id*");

        }
        if(!empty($data)) {
            if(!$all) {
                $data = end($data);
            }
            return $data;
        }
        return array();
    }


    /**
     * get logged request data
     *
     * Use this instead of PHP's getallheaders() because it is not available with PHP-FPM and nginx
     * Even when it is available, the case of keys is inconsistent so use this modified from: https://github.com/ralouphie/getallheaders
     * as it standardizes the case of returned keys
     *
     * @return $headers
     */
    private function getallheaders() {
        $headers = array();
        $addHeaders = array('CONTENT_LENGTH', 'CONTENT_TYPE', 'CONTENT_MD5');
        $removeHeaders = array('HTTP_MOD_REWRITE');
        foreach($_SERVER as $name => $value) {
            if(!in_array($name, $removeHeaders) && (substr($name, 0, 5) == 'HTTP_' || in_array($name, $addHeaders))) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', str_replace('HTTP_', '', $name)))));
                $headers[$name] = $value;
            }
        }
        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }
        return $headers;
    }


    /**
     * PUBLIC STATIC HELPER FUNCTIONS
     */


    /**
     * is the provided string a valid json string?
     *
     * @param string $string
     * @return boolean
     */
    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


    /**
     * Remove ProcessWire variables from get_defined_vars() call
     *
     * @param array $vars Variables
     * @return array Variables with PW vars removed
     *
     */
    public static function templateVars($vars) {

        $pwVars = array('fuel','options');
        foreach(wire('config')->version >= 2.8 ? wire('fuel') : wire()->fuel as $key => $value) {
            if(!is_object($value)) continue;
            $pwVars[] = $key;
        }

        $nonPwVars = $vars;
        foreach($vars as $key => $var) {
            if(is_object($var) || is_array($var)) {
                if(in_array($key, $pwVars)) unset($nonPwVars[$key]);
            }
        }

        unset($nonPwVars['templateVars']);
        unset($nonPwVars['pwVars']);
        unset($nonPwVars['key']);
        unset($nonPwVars['value']);
        unset($nonPwVars['p']);
        unset($nonPwVars['ps']);
        unset($nonPwVars['_filename']);
        unset($nonPwVars['functions']);

        return $nonPwVars;

    }


    /**
     * Format human friendly file size and color based on thresholds
     *
     * @param string $bytes File size in bytes
     * @return string Formatted size with units and colored font
     *
     */
    public static function human_filesize($bytes, $color = true) {
        if($color === true) {
            if($bytes >= 500000) {
                $color = self::COLOR_ALERT;
            }
            elseif($bytes < 500000 && $bytes > 100000) {
                $color = self::COLOR_WARN;
            }
            else {
                $color = self::COLOR_LIGHTGREY;
            }
        }
        else {
            $color = self::COLOR_LIGHTGREY;
        }

        if($bytes == 0) {
            return('<span style="color:'.self::COLOR_ALERT.'">0 Bytes</span>');
        }
        else {
            $filesizename = array("&nbsp;Bytes", "&nbsp;KB", "&nbsp;MB", "&nbsp;GB", "&nbsp;TB", "&nbsp;PB", "&nbsp;EB", "&nbsp;ZB", "&nbsp;YB");
            return '<span style="color:'.$color.'">' . round($bytes/pow(1024, ($i = floor(log($bytes, 1024)))), 1) . $filesizename[$i] . '</span>';
        }
    }


    /**
     * Format human friendly time and color based on thresholds
     *
     * @param string $seconds Time in seconds
     * @return string Formatted time in milliseconds with units and colored font
     *
     */
    public static function formatTime($seconds, $color = true) {
        if($color === true) {
            if($seconds >= 1) {
                $color = self::COLOR_ALERT;
            }
            elseif($seconds < 1 && $seconds > 0.1) {
                $color = self::COLOR_WARN;
            }
            else {
                $color = self::COLOR_LIGHTGREY;
            }
        }
        else {
            $color = self::COLOR_LIGHTGREY;
        }

        return '<span style="color:'.$color.'">'.round(($seconds*1000), 2) . ' ms</span>';
    }


    /**
    *
    * Replace all backslashes with forward slashes
    *
    * @param string $path
    * @return string
    *
    */
    public static function forwardSlashPath($path) {
        if(DIRECTORY_SEPARATOR != '/') $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        return $path;
    }


    /**
     * Insert generated time and size for a panel
     *
     * @param string $panel Panel name
     * @param $seconds Time in seconds
     * @param $bytes Size in bytes
     * @return string Formatted panel dom size and generation time with units and colored font
     *
     */
    public static function generateTimeSize($panel, $seconds, $bytes) {
        self::$panelGenerationTime[$panel]['time'] = $seconds;
        self::$panelGenerationTime[$panel]['size'] = $bytes;
        return '<span class="tracy-time-size">'.static::formatTime($seconds).'<span style="color: '.self::COLOR_NORMAL.';">,</span> '.static::human_filesize($bytes).'</span>';
    }


    /**
     * Insert link to panel's module settings section
     *
     * @param string $hashLink Hash link to panel
     * @return string Formatted link to panel settings
     *
     */
    public static function generatePanelSettingsLink($hashLink) {
        return '
        <span class="tracy-panel-settings-link">
            <a title="Panel Settings" href="'.wire('config')->urls->admin.'module/edit?name=TracyDebugger#'.$hashLink.'">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 369.793 369.792" style="enable-background:new 0 0 369.793 369.792;" xml:space="preserve">
                    <path d="M320.83,140.434l-1.759-0.627l-6.87-16.399l0.745-1.685c20.812-47.201,19.377-48.609,15.925-52.031L301.11,42.61     c-1.135-1.126-3.128-1.918-4.846-1.918c-1.562,0-6.293,0-47.294,18.57L247.326,60l-16.916-6.812l-0.679-1.684     C210.45,3.762,208.475,3.762,203.677,3.762h-39.205c-4.78,0-6.957,0-24.836,47.825l-0.673,1.741l-16.828,6.86l-1.609-0.669     C92.774,47.819,76.57,41.886,72.346,41.886c-1.714,0-3.714,0.769-4.854,1.892l-27.787,27.16     c-3.525,3.477-4.987,4.933,16.915,51.149l0.805,1.714l-6.881,16.381l-1.684,0.651C0,159.715,0,161.556,0,166.474v38.418     c0,4.931,0,6.979,48.957,24.524l1.75,0.618l6.882,16.333l-0.739,1.669c-20.812,47.223-19.492,48.501-15.949,52.025L68.62,327.18     c1.162,1.117,3.173,1.915,4.888,1.915c1.552,0,6.272,0,47.3-18.561l1.643-0.769l16.927,6.846l0.658,1.693     c19.293,47.726,21.275,47.726,26.076,47.726h39.217c4.924,0,6.966,0,24.859-47.857l0.667-1.742l16.855-6.814l1.604,0.654     c27.729,11.733,43.925,17.654,48.122,17.654c1.699,0,3.717-0.745,4.876-1.893l27.832-27.219     c3.501-3.495,4.96-4.924-16.981-51.096l-0.816-1.734l6.869-16.31l1.64-0.643c48.938-18.981,48.938-20.831,48.938-25.755v-38.395     C369.793,159.95,369.793,157.914,320.83,140.434z M184.896,247.203c-35.038,0-63.542-27.959-63.542-62.3     c0-34.342,28.505-62.264,63.542-62.264c35.023,0,63.522,27.928,63.522,62.264C248.419,219.238,219.92,247.203,184.896,247.203z" fill="'.self::COLOR_LIGHTGREY.'"/>
                </svg>
            </a>
        </span>';
    }


    /**
     * Generate Panel footer
     *
     * @param string $panel Panel name
     * @param $seconds Time in seconds
     * @param $bytes Size in bytes
     * @param string $settingsHashLink Hash link to panel
     * @return string Formatted footer
     *
     */
    public static function generatePanelFooter($panel, $seconds, $bytes, $settingsHashLink = null) {
        $out = '<div class="tracy-panel-footer">';
        if($settingsHashLink) $out .= self::generatePanelSettingsLink($settingsHashLink);
        $out .= self::generateTimeSize($panel, $seconds, $bytes);
        $out .= '</div>';
        return $out;
    }

    /**
     * Create editor path
     *
     * Adjust to consider localRootPath and onlineEditor settings
     *
     * @param string $path Full path to remote version of file
     * @return string Full path to local version of file
     *
     */
    public static function createEditorLink($file, $line, $linkText, $title = null) {
        if(strpos($file, '..') !== false) return;
        $file = str_replace("%file", $file, str_replace("%line", $line, Debugger::$editor));
        $file = static::forwardSlashPath($file);
        if(static::$useOnlineEditor) $file = str_replace(static::$onlineFileEditorDirPath, '', $file);
        elseif(!static::$isLocal && self::getDataValue('localRootPath') != '') $file = str_replace(wire('config')->paths->root, self::getDataValue('localRootPath'), $file);
        return '<a '.($title ? 'title="'.$title.'"' : '').' href="'.$file.'">'.$linkText.'</a>';
    }


    public static function removeCompilerFromPath($path) {
        $compilerCachePath = isset(wire('config')->fileCompilerOptions['cachePath']) && wire('config')->fileCompilerOptions['cachePath'] != '' ? wire('config')->fileCompilerOptions['cachePath'] : wire('config')->paths->cache . 'FileCompiler/';
        return str_replace($compilerCachePath, wire('config')->paths->root, $path);
    }


    /**
     * Getter function to get a $data index value from module settings
     *
     * @param string $property Property name
     * @return mixed Value of property
     *
     */
    public static function getDataValue($property) {
        if(is_array(self::$_data->$property) || is_int(self::$_data->$property) || $property == 'consoleCodePrefix') {
            return self::$_data->$property;
        }
        else {
            return trim(self::$_data->$property);
        }
    }


    /**
     * Is the current debug bar standard or one of the additional (AJAX, redirect) ones
     *
     * Determine if Tracy is loading an additional panel - via AJAX, or a redirect panel
     * In this case we don't want to add most of our custom panels to the new bar because they don't change
     *
     * @return bool
     *
     */
    public static function isAdditionalBar() {
        $isRedirect = preg_match('#^Location:#im', implode("\n", headers_list()));
        if(Helpers::isAjax() || $isRedirect) {
            return Helpers::isAjax() ? 'ajax' : 'redirect';
        }
        else {
            return false;
        }
    }

    /**
     * Check IP Address again allowed address / pattern
     *
     * @return array Single key/value with module settings defined IP address and whether the user's address is allowed
     *
     */
    public static function checkIpAddress() {

        $ipAddress = static::getDataValue('ipAddress');
        $ipAddressAllowed = null;

        if($ipAddress != '') {
            if(strpos($ipAddress, '/') === 0) $ipAddressAllowed = (bool) @preg_match($ipAddress, $_SERVER['REMOTE_ADDR']); // regex IPs
                else $ipAddressAllowed = $ipAddress === $_SERVER['REMOTE_ADDR']; // exact IP match
        }
        return array('ipAddress' => $ipAddress, 'ipAddressAllowed' => $ipAddressAllowed);
    }


    /**
     * Is User allowed and what access do they have
     *
     * This considers the Tracy output mode, the user's "tracy-debugger" permissions,
     * superuser status, and IP address if relevant
     *
     * @return string|bool String is 'development' or 'production'. Bool is only for false
     *
     */
    public static function allowedTracyUser() {

        $outputMode = static::getDataValue('outputMode');
        if($outputMode == 'detect') {
            $outputMode = static::$isLocal ? 'development' : 'production';
        }

        if(static::getDataValue('userSwitchSession') !== null) $userSwitchSession = static::getDataValue('userSwitchSession');

        if(static::$allowedSuperuser && static::getDataValue('superuserForceDevelopment') == 1 ||
            (static::$isLocal && static::getDataValue('guestForceDevelopmentLocal') == 1)) {
                self::$validLocalUser = true;
                return 'development';
        }
        elseif(wire('session')->tracyUserSwitcherId && (isset($userSwitchSession[wire('session')->tracyUserSwitcherId]) && $userSwitchSession[wire('session')->tracyUserSwitcherId] > time())) {
            self::$validSwitchedUser = true;
            return 'development';
        }
        elseif($outputMode == 'production') {
            return 'production';
        }
        else {
            $checkIpAddress = static::checkIpAddress();
            if($checkIpAddress['ipAddress'] != '' && self::userHasPermission('tracy-debugger')) {
                return $checkIpAddress['ipAddressAllowed'] ? 'development' : false;
            }
            elseif(self::userHasPermission('tracy-debugger') || static::$allowedSuperuser) {
                if(static::$isLocal) self::$validLocalUser = true;
                return 'development';
            }
            else {
                return false;
            }
        }
    }


    /**
     * Detects if local by IP address.
     * @param  string|array  IP addresses or computer names whitelist detection
     * @return bool
     */
    public static function isLocal($list = null) {
        return Debugger::detectDebugMode($list = null);
    }


    /*
    * This is a replacement for the url method from WireInput
    * There is this issue (https://github.com/processwire/processwire/pull/46)
    * but also the problem that $input->queryString() fails in init() because WireInput's $getVars isn't populated yet
    *
    * @param  bool Return with queryString or not
    * @return string URL of page with or without query string
    *
    */
    public static function inputUrl($withQueryString = false) {

        $url = '';
        /** @var Page $page */
        $page = wire('page');
        $config = wire('config');
        $sanitizer = wire('sanitizer');
        $input = wire('input');

        if($page && $page->id) {
            // pull URL from page
            $url = $page->url();
            $segmentStr = $input->urlSegmentStr();
            $pageNum = $input->pageNum();
            if(strlen($segmentStr) || $pageNum > 1) {
                if($segmentStr) $url = rtrim($url, '/') . '/' . $segmentStr;
                if($pageNum > 1) $url = rtrim($url, '/') . '/' . $config->pageNumUrlPrefix . $pageNum;
                if(isset($_SERVER['REQUEST_URI'])) {
                    $info = parse_url($_SERVER['REQUEST_URI']);
                    if(!empty($info['path']) && substr($info['path'], -1) == '/') $url .= '/'; // trailing slash
                }
                if($pageNum > 1) {
                    if($page->template->slashPageNum == 1) {
                        if(substr($url, -1) != '/') $url .= '/';
                    } else if($page->template->slashPageNum == -1) {
                        if(substr($url, -1) == '/') $url = rtrim($url, '/');
                    }
                } else if(strlen($segmentStr)) {
                    if($page->template->slashUrlSegments == 1) {
                        if(substr($url, -1) != '/') $url .= '/';
                    } else if($page->template->slashUrlSegments == -1) {
                        if(substr($url, -1) == '/') $url = rtrim($url, '/');
                    }
                }
            }

        } else if(isset($_SERVER['REQUEST_URI'])) {
            // page not yet available, attempt to pull URL from request uri
            $info = parse_url($_SERVER['REQUEST_URI']);
            $parts = explode('/', $info['path']);
            $charset = $config->pageNameCharset;
            $i = 0;
            foreach($parts as $i => $part) {
                if($i > 0) $url .= "/";
                $url .= ($charset === 'UTF8' ? $sanitizer->pageNameUTF8($part) : $sanitizer->pageName($part, false));
            }
            if(!empty($info['path']) && substr($info['path'], -1) == '/') {
                $url = rtrim($url, '/') . '/'; // trailing slash
            }
        }

        if($withQueryString && isset($_SERVER['REQUEST_URI'])) {
            $info = parse_url($_SERVER['REQUEST_URI']);
            $queryString = isset($info['query']) ? $info['query'] : '';

            if(strlen($queryString)) {
                $url .= "?$queryString";
            }
        }

        return $url;
    }

    /*
    * This is a replacement for the httpUrl method from WireInput
    */
    public static function inputHttpUrl($withQueryString = false) {
        return wire('input')->scheme() . '://' . wire('config')->httpHost . self::inputUrl($withQueryString);
    }


    /**
     * Get API data by type
     *
     * @param string type: variables | core | coreModules | siteModules | hooks
     * @return array api data
     *
     */
    public static function getApiData($type) {
        if(!isset(self::$allApiData[$type])) {
            require_once __DIR__ . '/includes/PwApiData.php';
            $tracyPwApiData = new TracyPwApiData();
            self::$allApiData[$type] = $tracyPwApiData->getApiData($type);
        }
        return self::$allApiData[$type];
    }


    public static function arrayDiffAssocMultidimensional(array $array1, array $array2) {
        $difference = [];
        foreach($array1 as $key => $value) {
            if(is_array($value)) {
                if(!array_key_exists($key, $array2)) {
                    $difference[$key] = $value;
                }
                elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                }
                else {
                    $multidimensionalDiff = self::arrayDiffAssocMultidimensional($value, $array2[$key]);
                    if (count($multidimensionalDiff) > 0) {
                        $difference[$key] = $multidimensionalDiff;
                    }
                }
            }
            else {
                if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                    $difference[$key] = $value;
                }
            }
        }
        return $difference;
    }


    /**
     * Renames files and folders appending -$n as required for the Processwire Versions panel.
     *
     * @param string Old Path
     * @param string New Path
     *
     */
    public function renamePwVersions($oldPath, $newPath, $addN = false) {
        if(file_exists($newPath) && $addN) {
            $n = 0;
            do { $newPath2 = $newPath . "-" . (++$n); } while(file_exists($newPath2));
            rename($newPath, $newPath2);
        }
        rename($oldPath, $newPath);
    }


    public function __destruct() {

        // if using Test mode in File Editor on regular files, then immediately replace loaded file with backed up version
        // this is here instead of ProcessWire::finished because this works if test version has fatal error
        if(isset($_COOKIE['tracyTestFileEditor'])) {
            $this->filePath = $this->wire('config')->paths->root . ($this->wire('input')->post->fileEditorFilePath ?: $this->wire('input')->cookie->tracyTestFileEditor);
            $this->cachePath = $this->tracyCacheDir . ($this->wire('input')->post->fileEditorFilePath ?: $this->wire('input')->cookie->tracyTestFileEditor);
            if(file_exists($this->cachePath)) {
                copy($this->cachePath, $this->filePath);
                unlink($this->cachePath);
            }
        }

        // delete temporary template file after it's been rendered
        // this is from the File Editor panel
        if(isset($this->tempTemplateFilename) && file_exists($this->tempTemplateFilename)) unlink($this->tempTemplateFilename);

        // modify paths to /wire/, index.php, and .htaccess when using PW Version Switcher
        if($this->wire('input')->post->tracyPwVersion && $this->wire('input')->post->tracyPwVersion != $this->wire('config')->version) {

            $rootPath = $this->wire('config')->paths->root;

            // rename wire
            $this->renamePwVersions($rootPath.'wire', $rootPath.'.wire-'.$this->wire('config')->version, true);
            $this->renamePwVersions($rootPath.'.wire-'.$this->wire('input')->post->tracyPwVersion, $rootPath.'wire');

            // rename .htaccess if previously replaced
            if(file_exists($rootPath.'.htaccess-'.$this->wire('input')->post->tracyPwVersion)) {
                $this->renamePwVersions($rootPath.'.htaccess', $rootPath.'.htaccess-'.$this->wire('config')->version, true);
                $this->renamePwVersions($rootPath.'.htaccess-'.$this->wire('input')->post->tracyPwVersion, $rootPath.'.htaccess');
            }
            // rename index.php if previously replaced
            if(file_exists($rootPath.'.index-'.$this->wire('input')->post->tracyPwVersion.'.php')) {
                $this->renamePwVersions($rootPath.'index.php', $rootPath.'.index-'.$this->wire('config')->version.'.php', true);
                $this->renamePwVersions($rootPath.'.index-'.$this->wire('input')->post->tracyPwVersion.'.php', $rootPath.'index.php');
            }
        }
    }


    /**
     * Return an InputfieldWrapper of Inputfields used to configure the class
     *
     * @param array $data Array of config values indexed by field name
     * @return InputfieldsWrapper
     *
     */
    public function getModuleConfigInputfields(array $data) {

        // if customPHP code was changed then session var was set to tell us to redirect to url with hash
        if($this->wire('session')->scrolltoCustomPhp === 1) {
            $this->wire('session')->remove('scrolltoCustomPhp');
            $this->wire('session')->redirect($this->inputUrl(true).'#wrap_Inputfield_customPhpCode');
        }

        // load JS & CSS files for config settings
        $this->wire('config')->styles->append($this->wire('config')->urls->TracyDebugger . "styles/config.css");
        $this->wire('config')->scripts->append($this->wire('config')->urls->TracyDebugger . "scripts/ace-editor/ace.js");
        $this->wire('config')->scripts->append($this->wire('config')->urls->TracyDebugger . "scripts/config.js");

        // convert PW Info panel custom links from path back to ID
        if(isset($data['customPWInfoPanelLinks'])) {
            $customPWInfoPanelLinkIds = array();
            $customPWInfoPanelLinks = $data['customPWInfoPanelLinks'];
            if(method_exists($this->wire('pages'), 'getByPath')) {
                foreach($customPWInfoPanelLinks as $pagePath) {
                    array_push($customPWInfoPanelLinkIds, $this->wire('pages')->getByPath($pagePath, array('getID' => true, 'useHistory' => true)));
                }
            }
            // fallback for PW < 3.0.6 when getByPath method did not exist
            else {
                foreach($customPWInfoPanelLinks as $pagePath) {
                    array_push($customPWInfoPanelLinkIds, $this->wire('pages')->get($pagePath)->id);
                }
            }
            $data['customPWInfoPanelLinks'] = $customPWInfoPanelLinkIds;
        }


        if($this->wire('input')->post->submit_save_module) {
            unset($this->wire('input')->cookie->tracyModulesDisabled);
            setcookie("tracyModulesDisabled", "", time()-3600, '/');
            unset($this->wire('input')->cookie->tracyDisabled);
            setcookie("tracyDisabled", "", time()-3600, '/');
        }

        $data = array_merge(self::getDefaultData(), $data);

        $wrapper = new InputfieldWrapper();

        // Various Links
        $fieldset = $this->wire('modules')->get("InputfieldMarkup");
        $fieldset->label = __(' ', __FILE__);
        $fieldset->value = '
        <p><strong><img src="https://adrianbj.github.io/TracyDebugger/img/icon.svg" style="display:inline; vertical-align: middle; margin:0 15px 0 0; width: 50px" />Tracy Debugger for ProcessWire v'.$this->getModuleInfo()['version'].'</strong></p>
        <p style="margin-left:55px"><i class="fa fa-fw fa-lg fa-book"></i> <a href="https://adrianbj.github.io/TracyDebugger">TracyDebugger for ProcessWire Docs</a> and <a href="https://tracy.nette.org/">Nette Tracy Docs</a><p>
        <p style="margin-left:55px"><i class="fa fa-fw fa-lg fa-github"></i> <a href="https://github.com/adrianbj/TracyDebugger">Star on Github</a></p>
        <p style="margin-left:55px"><img class="fa fa-fw" style="display:inline; vertical-align: middle; margin:0 4px" src="https://adrianbj.github.io/TracyDebugger/icons/processwire-info.svg"> <a href="http://modules.processwire.com/modules/tracy-debugger/">Recommend in the Modules Directory</a></strong></p>
        <p style="margin-left:55px"><i class="fa fa-fw fa-lg fa-life-ring"></i> <a href="https://processwire.com/talk/forum/58-tracy-debugger/">Forum Support Thread</a></p>
        <p style="margin-left:55px">
            <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=BJPJ5LGQHMCVE&source=url">
                <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" />
            </a>
        </p>
        ';
        if(isset($this->wire('config')->tracy) && is_array($this->wire('config')->tracy)) $fieldset->value .= '<p style="margin-top:35px"><i class="fa fa-fw fa-lg fa-exclamation-triangle"></i> You have specified various Tracy settings in <code>$config->tracy</code> that override settings here.</p>';
        $wrapper->add($fieldset);

        // Quick links
        $f =
        $this->wire('modules')->get('InputfieldMarkup');
        $f->id = 'tracy-quick-links';
        $f->label = __('Quick links', __FILE__);
        $f->value = '<ul></ul>';
        $wrapper->add($f);

        // Main Setup
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'mainSetup');
        $fieldset->label = __('Main setup', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'enabled');
        $f->label = __('Enable Tracy Debugger', __FILE__);
        $f->description = __('Uncheck to completely disable all Tracy Debugger features.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['enabled'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldSelect");
        $f->attr('name', 'outputMode');
        $f->label = 'Output mode';
        $f->description = __('The DETECT option automatically switches from DEVELOPMENT to PRODUCTION mode based on whether the IP of the site is publicly accessible or not.', __FILE__);
        $f->notes = __('In PRODUCTION mode, all errors and dumps etc are logged to file. Nothing is displayed in the browser. In DEVELOPMENT mode, the debug bar is displayed for authorized users - superusers and those with the `tracy-debugger` permission. All other users will be forced into PRODUCTION mode, regardless of this setting.', __FILE__);
        $f->columnWidth = 50;
        $f->required = true;
        $f->addOption('detect', 'DETECT');
        $f->addOption('development', 'DEVELOPMENT');
        $f->addOption('production', 'PRODUCTION');
        if($data['outputMode']) $f->attr('value', $data['outputMode']);
        $fieldset->add($f);

        // Access Permissions
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'accessPermission');
        $fieldset->label = __('Access permission', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'superuserForceDevelopment');
        $f->label = __('Force superusers into DEVELOPMENT mode', __FILE__);
        $f->description = __('Check to force DEVELOPMENT mode for superusers even on live sites.', __FILE__);
        $f->notes = __('By default, the Output Mode setting\'s DETECT option will force a site into PRODUCTION mode when it is live, which hides the DebugBar and sends errors and dumps to log files. However, with this checked, superusers will always be in DEVELOPMENT mode.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['superuserForceDevelopment'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'guestForceDevelopmentLocal');
        $f->label = __('Force guest users into DEVELOPMENT mode on localhost', __FILE__);
        $f->description = __('Check to force DEVELOPMENT mode for guests when server detected as localhost.', __FILE__);
        $f->notes = __('By default, guest users will always be in PRODUCTION mode (no debug bar). However, with this checked, they will always be in DEVELOPMENT mode on localhost.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['guestForceDevelopmentLocal'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'ipAddress');
        $f->label = __('Restrict non-superusers', __FILE__);
        $f->description = __('IP Address that non-superusers need to use TracyDebugger. Enter IP address or a PCRE regular expression to match IP address of user, eg. /^123\.456\.789\./ would match all IP addresses that started with 123.456.789.', __FILE__);
        $f->columnWidth = 50;
        $f->notes = __('Non-superusers are already blocked unless they have the `tracy-debugger` permission. But once a user has been given the permission, this option restricts access to the listed IP address. Highly recommended for debugging live sites that you have manually set into DEVELOPMENT mode.', __FILE__);
        if($data['ipAddress']) $f->attr('value', $data['ipAddress']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'restrictSuperusers');
        $f->label = __('Restrict superusers', __FILE__);
        $f->description = __('If checked, only superusers with the `tracy-debugger` permission will have access to Tracy.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['restrictSuperusers'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // Miscellaneous Settings
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'miscellaneous');
        $fieldset->label = __('Miscellaneous', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'strictMode');
        $f->label = __('Strict mode', __FILE__);
        $f->description = __('Check to enable strict mode which displays notices and warnings like errors.', __FILE__);
        $f->columnWidth = 33;
        $f->attr('checked', $data['strictMode'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'strictModeAjax');
        $f->label = __('Strict mode AJAX only', __FILE__);
        $f->description = __('Check to enable strict mode only for AJAX calls.', __FILE__);
        $f->notes = __('Because Tracy intercepts notices and warnings, these will no longer be returned with the AJAX response which may result in a "success" response, rather than "failure". Notices and warnings from an AJAX call will be displayed in the AJAX bar\'s Errors panel, but you still might prefer this option as it provides a more prominent indication of failure.', __FILE__);
        $f->showIf="strictMode!='1'";
        $f->columnWidth = 34;
        $f->attr('checked', $data['strictModeAjax'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'forceScream');
        $f->label = __('Force scream', __FILE__);
        $f->description = __('Check to force "scream" of mode which disables the @ (silence/shut-up) operator so that notices and warnings are no longer hidden.', __FILE__);
        $f->notes = __('This is disabled when Strict Mode is enabled because of a bug? [https://forum.nette.org/en/25569-strict-and-scream-modes-together](https://forum.nette.org/en/25569-strict-and-scream-modes-together) in the core Tracy package.', __FILE__);
        $f->showIf="strictMode!='1', strictModeAjax!='1'";
        $f->columnWidth = 33;
        $f->attr('checked', $data['forceScream'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'showLocation');
        $f->label = 'Show location';
        $f->description = __('Shows the location of dump() and barDump() calls.', __FILE__);
        $f->notes = __('LOCATION_SOURCE adds tooltip with path to the file, where the function was called.'."\n".'LOCATION_LINK adds a link to the file which can be opened directly.'."\n".'LOCATION_CLASS adds a tooltip to every dumped object containing path to the file, in which the object\'s class is defined.');
        $f->addOption('Tracy\Dumper::LOCATION_SOURCE', 'LOCATION_SOURCE');
        $f->addOption('Tracy\Dumper::LOCATION_LINK', 'LOCATION_LINK');
        $f->addOption('Tracy\Dumper::LOCATION_CLASS', 'LOCATION_CLASS');
        $f->columnWidth = 50;
        if($data['showLocation']) $f->attr('value', $data['showLocation']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'debugInfo');
        $f->label = __('Use debugInfo() magic method', __FILE__);
        $f->description = __('If a `__debugInfo()` method has been defined, it will be used instead of dumping the full object.', __FILE__);
        $f->notes = __('This results in a smaller, cleaner dump, but you may miss some information. You can override this with the `debugInfo => true/false` option when calling `d()`, `bd()`, `fl()`, etc. Note that this also affects the output in the Request Info panel\'s Field List & Values section.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['debugInfo'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'maxDepth');
        $f->label = __('Maximum nesting depth', __FILE__);
        $f->description = __('Set the maximum nesting depth of dumped arrays and objects.', __FILE__);
        $f->notes = __('Default: 3. Warning: making this too large can slow your page load down or even crash your browser.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('value', $data['maxDepth']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'maxLength');
        $f->label = __('Maximum string length', __FILE__);
        $f->description = __('Set the maximum displayed strings length.', __FILE__);
        $f->notes = __('Default: 150', __FILE__);
        $f->columnWidth = 50;
        $f->attr('value', $data['maxLength']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'collapse');
        $f->label = __('Collapse top array/object', __FILE__);
        $f->description = __('Set how big are collapsed', __FILE__);
        $f->notes = __('Default: 14', __FILE__);
        $f->columnWidth = 50;
        $f->attr('value', $data['collapse']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'collapse_count');
        $f->label = __('Collapse array/object', __FILE__);
        $f->description = __('Set how big are collapsed', __FILE__);
        $f->notes = __('Default: 7', __FILE__);
        $f->columnWidth = 50;
        $f->attr('value', $data['collapse_count']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'maxAjaxRows');
        $f->label = __('Maximum number of AJAX rows in debug bar', __FILE__);
        $f->description = __('After number is exceeded, the first one will be recycled.', __FILE__);
        $f->notes = __('Default: 3. Note that you will need to do a hard browser reload for this setting to take effect.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('value', $data['maxAjaxRows']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'reservedMemorySize');
        $f->label = __('Reserved memory size', __FILE__);
        $f->description = __('If you are getting memory exhaustion errors on Tracy\'s bluescreen, try increasing this value.', __FILE__);
        $f->notes = __('Default: 500000', __FILE__);
        $f->columnWidth = 50;
        $f->attr('value', $data['reservedMemorySize']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'showFireLogger');
        $f->label = __('Send data to FireLogger', __FILE__);
        $f->description = __('When checked, certain errors and `fl()` calls will be sent to FireLogger in the browser console.', __FILE__);
        $f->notes = __('If you are running on nginx and don\'t have access to adjust `fastcgi_buffers` and `fastcgi_buffer_size` settings, you may want to uncheck this to avoid 502 bad gateway errors because of `upstream sent too big header while reading response header from upstream` issues.', __FILE__);
        $f->attr('checked', $data['showFireLogger'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'referencePageEdited');
        $f->label = __('Reference page being edited', __FILE__);
        $f->description = __('When editing a page in the admin, the Request Info Panel will show details of the page being edited, and the Consol Panel will assign the $page variable to the page being edited.', __FILE__);
        $f->notes = __('Highly recommended unless you have a reason not to do this.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['referencePageEdited'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'linksNewTab');
        $f->label = __('Open links in new tab', __FILE__);
        $f->description = __('Makes links open in a new browser tab.', __FILE__);
        $f->notes = __('This is used by links such as those in the Captain Hook and API Explorer panels.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['linksNewTab'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // Error Logging Settings
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'errorLogging');
        $fieldset->label = __('Error logging', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'logSeverity');
        $f->label = 'Log severity';
        $f->description = __('If you want Tracy to log PHP errors like E_NOTICE or E_WARNING with detailed information (HTML report), set them here.', __FILE__);
        $f->notes = __('These only affect log file content, not onscreen debug info.');
        $f->columnWidth = 25;
        $f->addOption('E_ERROR', 'E_ERROR');
        $f->addOption('E_WARNING', 'E_WARNING');
        $f->addOption('E_PARSE', 'E_PARSE');
        $f->addOption('E_NOTICE', 'E_NOTICE');
        $f->addOption('E_CORE_ERROR', 'E_CORE_ERROR');
        $f->addOption('E_CORE_WARNING', 'E_CORE_WARNING');
        $f->addOption('E_COMPILE_ERROR', 'E_COMPILE_ERROR');
        $f->addOption('E_COMPILE_WARNING', 'E_COMPILE_WARNING');
        $f->addOption('E_USER_ERROR', 'E_USER_ERROR');
        $f->addOption('E_USER_WARNING', 'E_USER_WARNING');
        $f->addOption('E_USER_NOTICE', 'E_USER_NOTICE');
        $f->addOption('E_STRICT', 'E_STRICT');
        $f->addOption('E_RECOVERABLE_ERROR', 'E_RECOVERABLE_ERROR');
        $f->addOption('E_DEPRECATED', 'E_DEPRECATED');
        $f->addOption('E_USER_DEPRECATED', 'E_USER_DEPRECATED');
        $f->addOption('E_ALL', 'E_ALL');
        if($data['logSeverity']) $f->attr('value', $data['logSeverity']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldEmail");
        $f->attr('name', 'fromEmail');
        $f->label = __('Email errors "From"', __FILE__);
        $f->description = __('Receive emails from this address when an error occurs.', __FILE__);
        $f->columnWidth = 25;
        if($data['fromEmail']) $f->attr('value', $data['fromEmail']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldEmail");
        $f->attr('name', 'email');
        $f->label = __('Email errors "To"', __FILE__);
        $f->description = __('Receive emails at this address when an error occurs.', __FILE__);
        $f->columnWidth = 25;
        if($data['email']) $f->attr('value', $data['email']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'clearEmailSent');
        $f->label = __('Clear "email sent" flag', __FILE__);
        $f->description = __('Check and save settings to remove the "email-sent" file so that you will start receiving new error emails.', __FILE__);
        $f->columnWidth = 25;
        $fieldset->add($f);

        // Debug Bar and Panel Settings
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'debugBarAndPanels');
        $fieldset->label = __('Debug bar and panels', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'showDebugBar');
        $f->label = __('Show debug bar', __FILE__);
        $f->description = __('Show the debug bar.', __FILE__);
        $f->columnWidth = 50;
        $f->addOption('frontend', 'Frontend');
        $f->addOption('backend', 'Backend');
        if($data['showDebugBar']) $f->attr('value', $data['showDebugBar']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'hideDebugBarModals');
        $f->label = __('No debug bar in ...', __FILE__);
        $f->columnWidth = 50;
        $f->addOption('regularModal', 'Regular Modal');
        $f->addOption('inlineModal', 'Inline Modal');
        $f->addOption('overlayPanels', 'Overlay Panels');
        $f->addOption('formBuilderIframe', 'Form Builder iframe');
        if($data['hideDebugBarModals']) $f->attr('value', $data['hideDebugBarModals']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldAsmSelect");
        $f->attr('name', 'hideDebugBarFrontendTemplates');
        $f->label = __('No debug bar in selected frontend templates', __FILE__);
        $f->description = __('Disable the debug bar on pages with the selected templates.', __FILE__);
        $f->columnWidth = 50;
        foreach($this->wire('templates') as $t) {
            $f->addOption($t->id, $t->name);
        }
        if($data['hideDebugBarFrontendTemplates']) $f->attr('value', $data['hideDebugBarFrontendTemplates']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldAsmSelect");
        $f->attr('name', 'hideDebugBarBackendTemplates');
        $f->label = __('No debug bar in selected backend templates', __FILE__);
        $f->description = __('Disable the debug bar when editing pages with the selected templates.', __FILE__);
        $f->columnWidth = 50;
        foreach($this->wire('templates') as $t) {
            $f->addOption($t->id, $t->name);
        }
        if($data['hideDebugBarBackendTemplates']) $f->attr('value', $data['hideDebugBarBackendTemplates']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'hideDebugBar');
        $f->label = __('Hide debug bar by default', __FILE__);
        $f->description = __('Hide the debug bar by default on page load.', __FILE__);
        $f->notes = __('This results in the bar being hidden (unless an error is reported), and replaced with a small "show bar" &#8689; icon.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['hideDebugBar'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'showPanelLabels');
        $f->label = __('Show panel labels', __FILE__);
        $f->description = __('Show the labels next to each panel.', __FILE__);
        $f->notes = __('Unchecking this will make the debugger bar much more compact.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['showPanelLabels'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldRadios");
        $f->attr('name', 'barPosition');
        $f->label = __('Bar Position', __FILE__);
        $f->notes = __('You will need to do a hard reload in your browser for these changes to take effect.', __FILE__);
        $f->columnWidth = 50;
        $f->addOption('bottom-right', 'Bottom Right');
        $f->addOption('bottom-left', 'Bottom Left');
        if($data['barPosition']) $f->attr('value', $data['barPosition']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'panelZindex');
        $f->label = __('Starting z-index for panels', __FILE__);
        $f->description = __('Adjust if you find panels are below/above elements that you don\'t want.', __FILE__);
        $f->notes = __('Default: 100', __FILE__);
        $f->columnWidth = 50;
        if($data['panelZindex']) $f->attr('value', $data['panelZindex']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldAsmSelect");
        $f->attr('name', 'frontendPanels');
        $f->label = __('Frontend panels', __FILE__);
        $f->description = __('Determines which panels are shown in the Debug Bar on the frontend. Sort to match order of panels in Debugger Bar.', __FILE__);
        $f->columnWidth = 50;
        foreach(static::$allPanels as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['frontendPanels']) $f->attr('value', $data['frontendPanels']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldAsmSelect");
        $f->attr('name', 'backendPanels');
        $f->label = __('Backend panels', __FILE__);
        $f->description = __('Determines which panels are shown in the Debug Bar on the backend. Sort to match order of panels in Debugger Bar.', __FILE__);
        $f->notes = __('Note that '.implode(', ', static::$hideInAdmin).' are intentionally missing from this list because they have no use in the backend.', __FILE__);
        $f->columnWidth = 50;
        foreach(static::$allPanels as $name => $label) {
            if(!in_array($name, static::$hideInAdmin)) {
                $f->addOption($name, $label);
            }
        }
        if($data['backendPanels']) $f->attr('value', $data['backendPanels']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'restrictedUserDisabledPanels');
        $f->label = __('Disabled panels for restricted users', __FILE__);
        $f->description = __('Check the panels that should NOT be shown to users with the `tracy-restricted-panels` role or permission.', __FILE__);
        $f->notes = __('Unchecked panels will still only be shown to restricted users if they are selected in the Front/Back-end options above.', __FILE__);
        $f->optionColumns = 3;
        foreach(static::$allPanels as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['restrictedUserDisabledPanels']) $f->attr('value', $data['restrictedUserDisabledPanels']);
        $fieldset->add($f);


        // Panel Selector Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'panelSelectorPanel');
        $fieldset->label = __('Panel Selector panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'nonToggleablePanels');
        $f->label = __('Non-toggleable panels', __FILE__);
        $f->description = __('Selected panels will NOT be toggleable in the Panel Selector.', __FILE__);
        $f->optionColumns = 3;
        foreach(static::$allPanels as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['nonToggleablePanels']) $f->attr('value', $data['nonToggleablePanels']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'panelSelectorTracyTogglerButton');
        $f->label = __('Add Disable/Enable (Toggler) Tracy button to Panel Selector.', __FILE__);
        $f->description = __('This allows you to disable/enable Tracy from the Panel Selector.', __FILE__);
        $f->attr('checked', $data['panelSelectorTracyTogglerButton'] == '1' ? 'checked' : '');
        $fieldset->add($f);


        // Editor Protocol Handler
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'editorLinks');
        $fieldset->label = __('Editor links', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'editor');
        $f->label = __('Editor protocol handler', __FILE__);
        $f->description = __('Sets the Tracy `Debugger::$editor` variable. Enter the appropriate address to open your code editor of choice.'."\n".'This approach only works for OSX. For more instructions on Windows and Linux alternatives, [read here](https://pla.nette.org/en/how-open-files-in-ide-from-debugger).'."\n\n**Protocol handler helpers**\n[VSCode](https://github.com/shengyou/vscode-handler)\n[Sublime Text](https://github.com/saetia/sublime-url-protocol-mac)\n[PHP Storm](https://github.com/aik099/PhpStormProtocol)\n".' For other editors/IDEs, Google "protocol handler editorname".', __FILE__);
        $f->notes = __("`vscode://file/%file:%line`\n`subl://open/?url=file://%file&line=%line`\n`phpstorm://open?file=%file&line=%line`\n Initially configured for VSCode - change to work with your favorite editor.", __FILE__);
        $f->columnWidth = 50;
        if($data['editor']) $f->attr('value', $data['editor']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'localRootPath');
        $f->label = __('Local root path', __FILE__);
        $f->description = __('Maps editor links from live site to local dev files. Only used if you are viewing a live site, otherwise it is ignored.', __FILE__);
        $f->notes = __('An example path on MacOS might be: /Users/myname/Sites/sitefolder/', __FILE__);
        $f->columnWidth = 50;
        if($data['localRootPath']) $f->attr('value', $data['localRootPath']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'useOnlineEditor');
        $f->label = __('Use online editor for links', __FILE__);
        $f->description = __('This will open links in an online editor (Tracy File Editor or ProcessFileEdit) instead of your code editor.', __FILE__);
        $f->notes = __('You can choose which editor after selecting at least one of these.', __FILE__);
        $f->columnWidth = 50;
        $f->addOption('live', 'Live');
        $f->addOption('local', 'Local');
        if($data['useOnlineEditor']) $f->attr('value', $data['useOnlineEditor']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldRadios");
        $f->attr('name', 'onlineEditor');
        $f->label = __('Online editor', __FILE__);
        $f->description = __('Which online editor to use: Tracy File Editor or [ProcessFileEdit (Files Editor)](http://modules.processwire.com/modules/process-file-edit/).', __FILE__);
        if($this->wire('modules')->isInstalled('ProcessFileEdit')) {
            $f->notes = __('Process File Edit is installed, but you may want to adjust its [settings]('.$this->wire('config')->urls->admin.'module/edit?name=ProcessFileEdit)', __FILE__);
        }
        else {
            $f->notes = __('Process File Edit is NOT installed - you need to install it for the option to appear.', __FILE__);
        }
        $f->columnWidth = 50;
        $f->showIf = "useOnlineEditor.count>0";
        $f->requiredIf = "useOnlineEditor.count>0";
        $f->addOption('tracy', 'Tracy File Editor');
        if($this->wire('modules')->isInstalled('ProcessFileEdit')) $f->addOption('processFileEdit', 'ProcessFileEdit');
        if($data['onlineEditor']) $f->attr('value', $data['onlineEditor']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'forceEditorLinksToTracy');
        $f->label = __('Force editor links to use Tracy File Editor', __FILE__);
        $f->description = __('Even if neither of the "Use Online Editor for Links" options are checked, if the File Editor Panel is enabled, all links will be sent to it.', __FILE__);
        $f->notes = __("RECOMMENDED: This is a handy option if you generally want links to use your code editor, but want the option to use the File Editor occasionally.\nYou can enable it (once or sticky) from the Panel selector on the debug bar without the need to change the above settings.", __FILE__);
        $f->attr('checked', $data['forceEditorLinksToTracy'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // Console Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'consolePanel');
        $fieldset->label = __('Console panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldRadios");
        $f->attr('name', 'snippetsPath');
        $f->label = __('Snippets path', __FILE__);
        $f->description = __('Directory where snippets will be saved to / loaded from', __FILE__);
        $f->notes = __('Default: templates', __FILE__);
        $f->addOption('templates', $this->wire('config')->urls->templates.'TracyDebugger/snippets/');
        $f->addOption('assets', $this->wire('config')->urls->assets.'TracyDebugger/snippets/');
        if($data['snippetsPath']) $f->attr('value', $data['snippetsPath']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'consoleBackupLimit');
        $f->label = __('Maximum number automatically named backups', __FILE__);
        $f->description = __('The maximum number of automatically named backups that will be retained before pruning the oldest.', __FILE__);
        $f->notes = __('Default: 25.', __FILE__);
        $f->attr('value', $data['consoleBackupLimit']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldTextarea");
        $f->attr('name', 'consoleCodePrefix');
        $f->label = __('Code prefix', __FILE__);
        $f->description = __('Code block that should be added to each snippet stored on disk.', __FILE__);
        $f->noTrim = true;
        if($data['consoleCodePrefix']) $f->attr('value', $data['consoleCodePrefix']);
        $fieldset->add($f);

        // File Editor Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'fileEditorPanel');
        $fieldset->label = __('File Editor panel', __FILE__);
        $fieldset->description = __("These settings don't affect links opened via the Editor Protocol Handler. They only affect browsing directly from the File Editor folder/file selector sidebar.");
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldMarkup");
        $f->attr('name', 'fileEditorNote');
        $f->showIf = "useOnlineEditor.count>0, onlineEditor!=processFileEdit";
        $f->value = __('Because you are using Tracy File Editor for the editor links, the File Editor Panel will be enabled, even if you don\'t have it selected in the Frontend/Backend Panel selections above.', __FILE__);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldSelect");
        $f->attr('name', 'fileEditorBaseDirectory');
        $f->label = __('Base directory', __FILE__);
        $f->description = __('A more specific selection results in better performance in the File Editor Panel.', __FILE__);
        $f->columnWidth = 50;
        $f->addOption('root', 'Root');
        $f->addOption('site', 'Site');
        $f->addOption('templates', 'Templates');
        $f->required = true;
        if($data['fileEditorBaseDirectory']) $f->attr('value', $data['fileEditorBaseDirectory']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'fileEditorAllowedExtensions');
        $f->label = __('Allowed extensions', __FILE__);
        $f->description = __('Comma separated list of extensions that can be opened in the editor. Fewer extensions results in better performance in the File Editor Panel.', __FILE__);
        $f->notes = __('Initially configured for: php, module, js, css, txt, log, htaccess', __FILE__);
        $f->columnWidth = 50;
        if($data['fileEditorAllowedExtensions']) $f->attr('value', $data['fileEditorAllowedExtensions']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'fileEditorExcludedDirs');
        $f->label = __('Excluded directories', __FILE__);
        $f->description = __('Comma separated list of directories that will be excluded from the tree.', __FILE__);
        $f->notes = __('Initially configured for: site/assets', __FILE__);
        if($data['fileEditorExcludedDirs']) $f->attr('value', $data['fileEditorExcludedDirs']);
        $fieldset->add($f);

        // Code Editor settings
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'codeEditorSettings');
        $fieldset->label = __('Code editor settings', __FILE__);
        $fieldset->description = __("These settings apply to the Console panel and the File Editor panel", __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldSelect");
        $f->attr('name', 'aceTheme');
        $f->label = __('Theme', __FILE__);
        $f->columnWidth = 33;
        $themes = array();
        foreach (new \DirectoryIterator(wire('config')->paths->$this.'scripts/ace-editor/') as $file) {
            if($file->isFile()) {
                $baseName = $file->getBasename('.js');
                if(strpos($baseName, 'theme-') !== false) {
                    array_push($themes, str_replace('theme-', '', $baseName));
                }
            }
        }
        asort($themes);
        foreach($themes as $theme) {
            $f->addOption($theme);
        }
        $f->required = true;
        if($data['aceTheme']) $f->attr('value', $data['aceTheme']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'codeFontSize');
        $f->label = __('Font size', __FILE__);
        $f->description = __('In pixels.', __FILE__);
        $f->notes = __('If you change the font size you must also set the "Line height" to an appropriate value relative to the font size.', __FILE__);
        $f->columnWidth = 34;
        $f->required = true;
        if($data['codeFontSize']) $f->attr('value', $data['codeFontSize']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'codeLineHeight');
        $f->label = __('Line height', __FILE__);
        $f->description = __('In pixels.', __FILE__);
        $f->notes = __('You must set this to an appropriate value relative to the font size.', __FILE__);
        $f->columnWidth = 33;
        $f->required = true;
        if($data['codeLineHeight']) $f->attr('value', $data['codeLineHeight']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldSelect");
        $f->attr('name', 'codeShowInvisibles');
        $f->label = __('Show Invisibles', __FILE__);
        $f->description = __('Show invisible characters like spaces, CR, and LF.', __FILE__);
        $f->columnWidth = 33;
        $f->addOption(1, 'True');
        $f->addOption(0, 'False');
        $f->required = true;
        if($data['codeShowInvisibles']) $f->attr('value', $data['codeShowInvisibles']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'codeTabSize');
        $f->label = __('Tab size', __FILE__);
        $f->description = __('Number of spaces (or equivalent tab width).', __FILE__);
        $f->columnWidth = 34;
        $f->required = true;
        if($data['codeTabSize']) $f->attr('value', $data['codeTabSize']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldSelect");
        $f->attr('name', 'codeUseSoftTabs');
        $f->label = __('Use Soft Tabs', __FILE__);
        $f->description = __('Uses spaces rather than actual (hard) tabs.', __FILE__);
        $f->columnWidth = 33;
        $f->addOption(1, 'True');
        $f->addOption(0, 'False');
        $f->required = true;
        if($data['codeUseSoftTabs']) $f->attr('value', $data['codeUseSoftTabs']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'pwAutocompletions');
        $f->label = __('ProcessWire autocompletions', __FILE__);
        $f->description = __('Adds PW methods, properties, and page fields to the editor autocompletions.', __FILE__);
        $f->notes = __('This add approximately 200KB to the payload of the Console and File Editor panels.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['pwAutocompletions'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'codeShowDescription');
        $f->label = __('Show description', __FILE__);
        $f->description = __('This will show the first line from the doc comment as a note connected to autocomplete matches.', __FILE__);
        $f->notes = __('Unchecking this reduces the autocomplete payload to approximately 100KB.', __FILE__);
        $f->columnWidth = 50;
        $f->showIf = "pwAutocompletions=1";
        $f->attr('checked', $data['codeShowDescription'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldURL");
        $f->attr('name', 'customSnippetsUrl');
        $f->label = __('Custom snippets URL', __FILE__);
        $f->description = __('Link to snippets file for use in Console and File Editor panels. Can be local or remote, eg. a Github Gist file.', __FILE__);
        $f->notes = __('If Github gist, you must use a service such as [https://rawgit.com/](https://rawgit.com/)', __FILE__);
        if($data['customSnippetsUrl']) $f->attr('value', $data['customSnippetsUrl']);
        $fieldset->add($f);

        // ProcessWire Info Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'processwireInfoPanel');
        $fieldset->label = __('ProcessWire Info panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'processwireInfoPanelSections');
        $f->label = __('Panel sections', __FILE__);
        $f->description = __('Which sections to include in the ProcessWire Info panel.', __FILE__);
        $f->columnWidth = 50;
        foreach(static::$processWireInfoSections as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['processwireInfoPanelSections']) $f->attr('value', $data['processwireInfoPanelSections']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldPageListSelectMultiple");
        $f->attr('name', 'customPWInfoPanelLinks');
        $f->label = __('Custom links', __FILE__);
        $f->description = __('Choose pages you would like links to.', __FILE__);
        $f->notes = __('To provide links to items under the Setup menu, navigate to Admin > Setup >', __FILE__);
        $f->columnWidth = 50;
        if($data['customPWInfoPanelLinks']) $f->attr('value', $data['customPWInfoPanelLinks']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'showPWInfoPanelIconLabels');
        $f->label = __('Show icon labels', __FILE__);
        $f->description = __('Shows labels next to each icon for the two "Links" sections.', __FILE__);
        $f->notes = __('Nice for clarity, but takes up more space.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['showPWInfoPanelIconLabels'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'pWInfoPanelLinksNewTab');
        $f->label = __('Open links in new tab', __FILE__);
        $f->description = __('Makes links open in a new browser tab.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['pWInfoPanelLinksNewTab'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // Adminer Panel
        if($this->wire('modules')->isInstalled('ProcessTracyAdminer')) {
            $fieldset = $this->wire('modules')->get("InputfieldFieldset");
            $fieldset->attr('name+id', 'adminerPanel');
            $fieldset->label = __('Adminer panel', __FILE__);
            $wrapper->add($fieldset);

            $f = $this->wire('modules')->get("InputfieldMarkup");
            $f->attr('name', 'adminerSettings');
            $f->value = 'Adminer settings are available <a href="'.$this->wire('config')->urls->admin.'module/edit?name=ProcessTracyAdminer">here</a>.';
            $fieldset->add($f);
        }

        // API Explorer Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'apiExplorerPanel');
        $fieldset->label = __('API Explorer panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'apiExplorerShowDescription');
        $f->label = __('Show description', __FILE__);
        $f->description = __('This will show the first line from the doc comment in its own column.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['apiExplorerShowDescription'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'apiExplorerToggleDocComment');
        $f->label = __('Toggle method doc comment', __FILE__);
        $f->description = __('This will toggle the entire doc comment block if you click on the method column.', __FILE__);
        $f->notes = __('This significantly increases the size of this panel.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['apiExplorerToggleDocComment'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'apiExplorerModuleClasses');
        $f->label = __('Module classes', __FILE__);
        $f->description = __('Select module classes that you also want diplayed.', __FILE__);
        $f->notes = __('These options will significantly increase the size of this panel.', __FILE__);
        $f->addOption('coreModules', 'Core modules');
        $f->addOption('siteModules', 'Site modules');
        if($data['apiExplorerModuleClasses']) $f->attr('value', $data['apiExplorerModuleClasses']);
        $fieldset->add($f);

        // Captain Hook Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'captainHookPanel');
        $fieldset->label = __('Captain Hook panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'captainHookShowDescription');
        $f->label = __('Show description', __FILE__);
        $f->description = __('This will show the first line from the doc comment in its own column.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['captainHookShowDescription'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'captainHookToggleDocComment');
        $f->label = __('Toggle method doc comment', __FILE__);
        $f->description = __('This will toggle the entire doc comment block if you click on the method column.', __FILE__);
        $f->notes = __('This significantly increases the size of this panel.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['captainHookToggleDocComment'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // Request Info Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'requestInfoPanel');
        $fieldset->label = __('Request Info panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'requestInfoPanelSections');
        $f->label = __('Panel sections', __FILE__);
        $f->description = __('Which sections to include in the Request Info panel.', __FILE__);
        $f->columnWidth = 50;
        $f->notes = __('The three "Object" options will significantly increase the size of this panel.', __FILE__);
        foreach(static::$requestInfoSections as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['requestInfoPanelSections']) $f->attr('value', $data['requestInfoPanelSections']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'imagesInFieldListValues');
        $f->label = __('Show image thumbnails in Field List & Values section', __FILE__);
        $f->description = __('This will load all image thumbnails for the page, along with the dimensions & size details.', __FILE__);
        $f->notes = __('This can significantly increase the size of this panel and rendering time if the page has lots of images.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['imagesInFieldListValues'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // Debug Mode Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'debugModePanel');
        $fieldset->label = __('Debug Mode panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'debugModePanelSections');
        $f->label = __('Panel sections', __FILE__);
        $f->description = __('Which sections to include in the Debug Mode panel.', __FILE__);
        $f->columnWidth = 50;
        foreach(static::$debugModeSections as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['debugModePanelSections']) $f->attr('value', $data['debugModePanelSections']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'alwaysShowDebugTools');
        $f->label = __('Show debug mode tools even if $config->debug = false.', __FILE__);
        $f->description = __('If checked, the debug tools will be displayed regardless of whether debug mode is enabled.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['alwaysShowDebugTools'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // Diagnostics Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'diagnosticsPanel');
        $fieldset->label = __('Diagnostics panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'diagnosticsPanelSections');
        $f->label = __('Panel sections', __FILE__);
        $f->description = __('Which sections to include in the Diagnostics panel.', __FILE__);
        $f->notes = __('The "Filesystem Files" option may significantly increase the generation time for this panel.', __FILE__);
        foreach(static::$diagnosticsSections as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['diagnosticsPanelSections']) $f->attr('value', $data['diagnosticsPanelSections']);
        $fieldset->add($f);

        // Dumps Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'dumpsPanel');
        $fieldset->label = __('Dumps panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldAsmSelect");
        $f->attr('name', 'dumpPanelTabs');
        $f->label = __('Dump Tabs', __FILE__);
        $f->description = __('Select and order the tabs you want when dumping ProcessWire objects via dump/barDump methods.', __FILE__);
        $f->notes = __('The first item will be open by default.', __FILE__);
        foreach(static::$dumpPanelTabs as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['dumpPanelTabs']) $f->attr('value', $data['dumpPanelTabs']);
        $fieldset->add($f);

        // Validator Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'validatorPanel');
        $fieldset->label = __('Validator panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'validatorUrl');
        $f->label = __('Validator URL', __FILE__);
        $f->description = __('Enter the URL for the validation service.', __FILE__);
        $f->notes = __('Default: https://html5.validator.nu/', __FILE__);
        if($data['validatorUrl']) $f->attr('value', $data['validatorUrl']);
        $fieldset->add($f);

        // ToDo Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'todoPanel');
        $fieldset->label = __('TODO panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldTextarea");
        $f->attr('name', 'todoIgnoreDirs');
        $f->label = __('Ignore directories', __FILE__);
        $f->description = __('Comma separated list of terms used to match folders to be ignored when scanning for ToDo items.', __FILE__);
        $f->notes = __('Default: git, svn, images, img, errors, sass-cache, node_modules', __FILE__);
        $f->columnWidth = 50;
        if($data['todoIgnoreDirs']) $f->attr('value', $data['todoIgnoreDirs']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'todoAllowedExtensions');
        $f->label = __('Allowed extensions', __FILE__);
        $f->description = __('Comma separated list file extensions to be scanned for ToDo items.', __FILE__);
        $f->notes = __('Default: php, module, inc, txt, latte, html, htm, md, css, scss, less, js', __FILE__);
        $f->columnWidth = 50;
        if($data['todoAllowedExtensions']) $f->attr('value', $data['todoAllowedExtensions']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'todoScanModules');
        $f->label = __('Scan site modules', __FILE__);
        $f->description = __('Check to allow the ToDo to scan the /site/modules directory. Otherwise it will only scan /site/templates.', __FILE__);
        $f->notes = __('Not recommended unless you are a regular module developer.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['todoScanModules'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'todoScanAssets');
        $f->label = __('Scan site assets', __FILE__);
        $f->description = __('Check to allow the ToDo to scan the /site/assets directory. Otherwise it will only scan /site/templates.', __FILE__);
        $f->notes = __('If you check this, you should add files, logs, cache, sessions and other relevant terms to the `Ignore Directories` field.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['todoScanAssets'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // ProcessWire and Tracy Log Panels
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'processwireAndTracyLogsPanels');
        $fieldset->label = __('ProcessWire and Tracy Log panels', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'numLogEntries');
        $f->label = __('Number of log entries', __FILE__);
        $f->description = __('Set the number of log entries to be displayed for the Tracy and ProcessWire log viewer panels.', __FILE__);
        $f->notes = __('Default: 10', __FILE__);
        $f->attr('value', $data['numLogEntries']);
        $fieldset->add($f);

        // Template Resources Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'templateResourcesPanel');
        $fieldset->label = __('Template Resources panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'variablesShowPwObjects');
        $f->label = __('Show content of ProcessWire objects', __FILE__);
        $f->description = __('Shows the full ProcessWire object contents, rather than arrays of values. Only recommended for specific debugging purposes.', __FILE__);
        $f->notes = __('Checking this will significantly increase the size of this panel if you have any variables set to ProcessWire objects.', __FILE__);
        $f->attr('checked', $data['variablesShowPwObjects'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        // Links Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'linksPanel');
        $fieldset->label = __('Links panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldTextarea");
        $f->attr('name', 'linksCode');
        $f->label = __('Links code', __FILE__);
        $f->description = __('One link per line. Optionally add label for link.', __FILE__);
        $f->notes = __('eg. https://www.google.com | Google Search', __FILE__);
        if($data['linksCode']) $f->attr('value', $data['linksCode']);
        $fieldset->add($f);

        // Custom PHP Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'customPhpPanel');
        $fieldset->label = __('Custom PHP panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldTextarea");
        $f->attr('name', 'customPhpCode');
        $f->label = __('Custom PHP code', __FILE__);
        $f->description = __('Use this PHP code block to return any output you want.', __FILE__);
        $f->notes = __('eg. return \'<a href="https://developers.google.com/speed/pagespeed/insights/?url=\'.$page->httpUrl.\'">Google PageSpeed</a>\';', __FILE__);
        if($data['customPhpCode']) $f->attr('value', $data['customPhpCode']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldMarkup");
        $f->attr('name', 'customPhpCodeSave');
        $f->value = '<span class="pw-button-dropdown-wrap"><button id="Inputfield_submit_save_module_copy" class="ui-button ui-widget ui-corner-all pw-button-dropdown-main ui-state-default" name="submit_save_module" value="Submit" type="submit" data-from_id="Inputfield_submit_save_module"><span class="ui-button-text">Submit</span></button><button type="button" id="pw-dropdown-toggle-Inputfield_submit_save_module_copy" class="ui-button ui-widget ui-corner-all ui-button-text-only pw-dropdown-toggle-click pw-dropdown-toggle pw-button-dropdown-toggle ui-state-default" role="button" aria-disabled="false" data-pw-dropdown=".pw-button-dropdown-1" data-from_id="pw-dropdown-toggle-Inputfield_submit_save_module"><span class="ui-button-text"><i class="fa fa-angle-down"></i></span></button></span><br />';
        $f->notes = __('Note: this is just an extra Submit button to make iterations of the customPHP code quicker to make. It works exactly the same as the main ones on this page.', __FILE__);
        $fieldset->add($f);

        // User Switcher Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'userSwitcherPanel');
        $fieldset->label = __('User Switcher panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldMarkup");
        $f->label = __(' ', __FILE__);
        $f->value = '<p>These options can be useful if you use the User system to store frontend "members" and the system has a lot of users.<br />Do not use more than one of the following three options for limiting the list of users.</p>';
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'userSwitcherSelector');
        $f->label = __('Selector', __FILE__);
        $f->description = __('Use this to determine which users will be available.', __FILE__);
        if($data['userSwitcherSelector']) $f->attr('value', $data['userSwitcherSelector']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldAsmSelect");
        $f->attr('name', 'userSwitcherRestricted');
        $f->label = __('Excluded Roles', __FILE__);
        $f->description = __('Users with selected roles will not be available from the list of users to switch to.', __FILE__);
        $f->columnWidth = 50;
        $f->setAsmSelectOption('sortable', false);
        foreach($this->wire('roles') as $role) {
            $f->addOption($role->id, $role->name);
        }
        if($data['userSwitcherRestricted']) $f->attr('value', $data['userSwitcherRestricted']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldAsmSelect");
        $f->attr('name', 'userSwitcherIncluded');
        $f->label = __('Included Roles', __FILE__);
        $f->description = __('Only users with these selected roles will be available from the list of users to switch to. If none selected, then all will be available unless the excluded roles is populated.', __FILE__);
        $f->columnWidth = 50;
        $f->setAsmSelectOption('sortable', false);
        foreach($this->wire('roles') as $role) {
            $f->addOption($role->id, $role->name);
        }
        if($data['userSwitcherIncluded']) $f->attr('value', $data['userSwitcherIncluded']);
        $fieldset->add($f);

        // requestLogger Panel
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'requestLoggerPanel');
        $fieldset->label = __('Request Logger panel', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'requestMethods');
        $f->label = __('Request methods', __FILE__);
        $f->description = __('Which request methods to log.', __FILE__);
        $f->notes = __('It may be useful to disable GET so that normal page visits are ignored.', __FILE__);
        $f->addOption('GET');
        $f->addOption('POST');
        $f->addOption('PUT');
        $f->addOption('DELETE');
        $f->addOption('PATCH');
        $f->columnWidth = 33;
        if($data['requestMethods']) $f->attr('value', $data['requestMethods']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldInteger");
        $f->attr('name', 'requestLoggerMaxLogs');
        $f->label = __('Maximum number of logged requests', __FILE__);
        $f->description = __('Number of requests to be kept for each page.', __FILE__);
        $f->columnWidth = 34;
        $f->attr('value', $data['requestLoggerMaxLogs']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldRadios");
        $f->attr('name', 'requestLoggerReturnType');
        $f->label = __('Output type', __FILE__);
        $f->description = __('This determines whether the logged data is returned as an object or an array.', __FILE__);
        $f->addOption('array', 'Array');
        $f->addOption('object', 'Object');
        $f->columnWidth = 33;
        if($data['requestLoggerReturnType']) $f->attr('value', $data['requestLoggerReturnType']);
        $fieldset->add($f);

        // Server Type Indicator
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'serverTypeIndicator');
        $fieldset->label = __('Server type indicator', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'styleWhere');
        $f->label = __('Where', __FILE__);
        $f->description = __('Add indicator based on server IP address and/or subdomain.', __FILE__);
        $f->columnWidth = 30;
        $f->addOption('backend', 'Backend');
        $f->addOption('frontend', 'Frontend');
        if($data['styleWhere']) $f->attr('value', $data['styleWhere']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldTextarea");
        $f->attr('name', 'styleAdminColors');
        $f->label = __('Indicator colors', __FILE__);
        $f->showIf = "styleWhere.count>0";
        $f->description = __('Use "type|#color" entries to define indicator styling for each server type. "local" is determined by IP address. Other types are detected based on their existence in subdomain or TLD (eg. dev.mysite.com or mysite.dev), dependant on whether you include the period after (dev.﹡) or before (﹡.dev).', __FILE__);
        $f->notes = __("**DEFAULT**\nlocal|#FF9933\n*.local|#FF9933\ndev.*|#FF9933\n*.test|#FF9933\nstaging.*|#8b0066\n*.com|#009900", __FILE__);
        $f->columnWidth = 70;
        if($data['styleAdminColors']) $f->attr('value', $data['styleAdminColors']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'styleAdminType');
        $f->label = __('Indicator type', __FILE__);
        $f->showIf = "styleWhere.count>0";
        $f->description = __('Choose custom if you want complete control', __FILE__);
        $f->notes = __('You will need to do a hard reload in your browser for these changes to take effect.', __FILE__);
        $f->addOption('default', 'Indicator on debug bar');
        $f->addOption('custom', 'Custom - control with CSS');
        $f->addOption('titlePrefix', 'Add prefix page title');
        $f->addOption('favicon', 'Favicon badge');
        $f->columnWidth = 30;
        if($data['styleAdminType']) $f->attr('value', $data['styleAdminType']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldTextarea");
        $f->attr('name', 'styleAdminElements');
        $f->label = __('Custom indicator CSS', __FILE__);
        $f->showIf = "styleWhere.count>0, styleAdminType='custom'";
        $f->description = __('Use [color] and [type] shortcodes to add indicator based on server type.', __FILE__);
        $f->notes = __("**DEFAULT**\nbody::before {\n&nbsp;&nbsp;&nbsp;&nbsp;content: \"[type]\";\n&nbsp;&nbsp;&nbsp;&nbsp;background: [color];\n&nbsp;&nbsp;&nbsp;&nbsp;position: fixed;\n&nbsp;&nbsp;&nbsp;&nbsp;left: 0;\n&nbsp;&nbsp;&nbsp;&nbsp;bottom: 100%;\n&nbsp;&nbsp;&nbsp;&nbsp;color: #ffffff;\n&nbsp;&nbsp;&nbsp;&nbsp;width: 100vh;\n&nbsp;&nbsp;&nbsp;&nbsp;padding: 0;\n&nbsp;&nbsp;&nbsp;&nbsp;text-align: center;\n&nbsp;&nbsp;&nbsp;&nbsp;font-family: sans-serif;\n&nbsp;&nbsp;&nbsp;&nbsp;font-weight: 600;\n&nbsp;&nbsp;&nbsp;&nbsp;text-transform: uppercase;\n&nbsp;&nbsp;&nbsp;&nbsp;transform: rotate(90deg);\n&nbsp;&nbsp;&nbsp;&nbsp;transform-origin: bottom left;\n&nbsp;&nbsp;&nbsp;&nbsp;z-index: 999999;\n&nbsp;&nbsp;&nbsp;&nbsp;font-family: sans-serif;\n&nbsp;&nbsp;&nbsp;&nbsp;font-size: 11px;\n&nbsp;&nbsp;&nbsp;&nbsp;height: 13px;\n&nbsp;&nbsp;&nbsp;&nbsp;line-height: 13px;\n&nbsp;&nbsp;&nbsp;&nbsp;pointer-events: none;\n}\n", __FILE__);
        $f->columnWidth = 70;
        if($data['styleAdminElements']) $f->attr('value', $data['styleAdminElements']);
        $fieldset->add($f);



        // User DEV Template
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'userDevTemplate');
        $fieldset->label = __('User dev template', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'userDevTemplate');
        $f->label = __('Enable user dev template', __FILE__);
        $f->description = __('If user has a permission named to match the page template with the set suffix, the page will be rendered with that template file rather than the default.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['userDevTemplate'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'userDevTemplateSuffix');
        $f->label = __('User dev template suffix', __FILE__);
        $f->description = __('Template file suffix. eg "dev" will render the homepage with the "home-dev.php" template file if the user has a matching permission (prefixed with "tracy"), eg. `tracy-home-dev`. You can also use `tracy-all-dev` to enable for all templates if the files exist.', __FILE__);
        $f->columnWidth = 50;
        if($data['userDevTemplateSuffix']) $f->attr('value', $data['userDevTemplateSuffix']);
        $fieldset->add($f);

        // User Bar
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'userBar');
        $fieldset->label = __('User Bar', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'showUserBar');
        $f->label = __('Show user bar', __FILE__);
        $f->description = __('This bar is shown to logged in users without permission for the Tracy debug bar (typically all non-superusers).', __FILE__);
        $f->columnWidth = 33;
        $f->attr('checked', $data['showUserBar'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'showUserBarTracyUsers');
        $f->label = __('Show user bar for Tracy users', __FILE__);
        $f->showIf = "showUserBar=1";
        $f->description = __('Also show the bar to users with Tracy debug bar permission.', __FILE__);
        $f->notes = __('Be sure to position this bar somewhere other than bottom right so it doesn\'t conflict with the debug bar.', __FILE__);
        $f->columnWidth = 34;
        $f->showIf = "showUserBar=1";
        $f->attr('checked', $data['showUserBarTracyUsers'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldAsmSelect");
        $f->attr('name', 'userBarFeatures');
        $f->label = __('Features', __FILE__);
        $f->showIf = "showUserBar=1";
        $f->description = __('Determines which features are shown on the User Bar.', __FILE__);
        $f->notes = __('The Page Versions function requires that the user has the `tracy-page-versions` permission.', __FILE__);
        $f->columnWidth = 33;
        foreach(static::$userBarFeatures as $name => $label) {
            $f->addOption($name, $label);
        }
        if($data['userBarFeatures']) $f->attr('value', $data['userBarFeatures']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldTextarea");
        $f->attr('name', 'userBarCustomFeatures');
        $f->label = __('Custom features', __FILE__);
        $f->showIf = "showUserBar=1";
        $f->description = __('Use this PHP code block to return any output you want.', __FILE__);
        $f->notes = __('eg. return \'<a href="https://developers.google.com/speed/pagespeed/insights/?url=\'.$page->httpUrl.\'" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 452.555 452.555" style="enable-background:new 0 0 452.555 452.555;" xml:space="preserve" width="16px" height="16px"><path d="M404.927,209.431h47.175c-3.581-49.699-23.038-94.933-53.539-130.611l-33.715,33.715l-23.275-23.296l33.758-33.78     C339.826,24.353,294.527,4.206,244.591,0.194v48.923h-32.917V0C161.22,3.236,115.296,22.8,79.165,53.668l35.57,35.549     l-23.296,23.296L55.804,76.878C24.332,112.858,4.12,158.804,0.475,209.452h50.864v32.917H0.453     C8.93,359.801,106.646,452.555,226.256,452.555s217.347-92.754,225.846-210.186h-47.197L404.927,209.431L404.927,209.431z      M228.133,362.217c-24.116,0-43.659-19.522-43.659-43.681c0-17.839,10.742-33.176,26.144-39.928l16.394-151.707l4.034,0.043     l14.927,151.729c15.229,6.881,25.863,22.045,25.863,39.863C271.857,342.695,252.27,362.217,228.133,362.217z" fill="\'.$iconColor.\'"/></svg></a>\';', __FILE__);
        if($data['userBarCustomFeatures']) $f->attr('value', $data['userBarCustomFeatures']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldSelect");
        $f->attr('name', 'userBarTopBottom');
        $f->label = __('Top / Bottom', __FILE__);
        $f->showIf = "showUserBar=1";
        $f->columnWidth = 50;
        $f->addOption('top', 'Top');
        $f->addOption('bottom', 'Bottom');
        if($data['userBarTopBottom']) $f->attr('value', $data['userBarTopBottom']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldSelect");
        $f->attr('name', 'userBarLeftRight');
        $f->label = __('Left / Right', __FILE__);
        $f->showIf = "showUserBar=1";
        $f->columnWidth = 50;
        $f->addOption('left', 'Left');
        $f->addOption('right', 'Right');
        if($data['userBarLeftRight']) $f->attr('value', $data['userBarLeftRight']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'userBarBackgroundColor');
        $f->label = __('Background color', __FILE__);
        $f->showIf = "showUserBar=1";
        $f->description = __('Leave blank for transparent/none', __FILE__);
        $f->notes = __('eg. #FFFFFF', __FILE__);
        $f->columnWidth = 33;
        if($data['userBarBackgroundColor']) $f->attr('value', $data['userBarBackgroundColor']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'userBarBackgroundOpacity');
        $f->label = __('Background opacity', __FILE__);
        $f->showIf = "showUserBar=1";
        $f->notes = __('0 to 1', __FILE__);
        $f->columnWidth = 34;
        if($data['userBarBackgroundOpacity']) $f->attr('value', $data['userBarBackgroundOpacity']);
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'userBarIconColor');
        $f->label = __('Icon color', __FILE__);
        $f->showIf = "showUserBar=1";
        $f->notes = __('eg. #666666', __FILE__);
        $f->columnWidth = 33;
        if($data['userBarIconColor']) $f->attr('value', $data['userBarIconColor']);
        $fieldset->add($f);

        // Method Shortcuts
        $fieldset = $this->wire('modules')->get("InputfieldFieldset");
        $fieldset->attr('name+id', 'methodShortcuts');
        $fieldset->label = __('Method shortcuts', __FILE__);
        $wrapper->add($fieldset);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'enableShortcutMethods');
        $f->label = __('Enable shortcut methods', __FILE__);
        $f->description = __('Uncheck to not define any of the shortcut methods. If you are not going to use these in your templates, unchecking means that they will not be defined which may reduce possible future name clashes. If in doubt, uncheck and use the full methods:'."\n".'TD::addBreakpoint()'."\n".'TD::barDump()'."\n".'TD::barDumpBig()'."\n".'TD::debugAll()'."\n".'TD::dump()'."\n".'TD::dumpBig()'."\n".'TD::fireLog()'."\n".'TD::log()'."\n".'TD::templateVars()'."\n".'TD::timer()', __FILE__);
        $f->notes = __('If this, or one of the shortcut methods is not enabled, but is called in your templates, all users will get a "call to undefined function" fatal error, so please be aware when using the shortcut methods in your templates if they are not enabled here.', __FILE__);
        $f->columnWidth = 50;
        $f->attr('checked', $data['enableShortcutMethods'] == '1' ? 'checked' : '');
        $fieldset->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckboxes");
        $f->attr('name', 'enabledShortcutMethods');
        $f->label = __('Enabled shortcuts', __FILE__);
        $f->description = __('Uncheck any shortcuts/aliases to methods that you do not want available.', __FILE__);
        $f->notes = __('Useful if any of these functions/methods are defined elswhere in your site and you are getting a "previously declared" fatal error.', __FILE__);
        $f->showIf = "enableShortcutMethods=1";
        $f->columnWidth = 50;
        $f->addOption('addBreakpoint', 'addBreakpoint() for TD::addBreakpoint()');
        $f->addOption('bp', 'bp() for TD::addBreakpoint()');
        $f->addOption('barDump', 'barDump() for TD::barDump()');
        $f->addOption('bd', 'bd() for TD::barDump()');
        $f->addOption('barEcho', 'barEcho() for TD::barEcho()');
        $f->addOption('be', 'be() for TD::barEcho()');
        $f->addOption('barDumpBig', 'barDumpBig() for TD::barDumpBig()');
        $f->addOption('bdb', 'bdb() for TD::barDumpBig()');
        $f->addOption('debugAll', 'debugAll() for TD::debugAll()');
        $f->addOption('da', 'da() for TD::debugAll()');
        $f->addOption('dump', 'dump() for TD::dump()');
        $f->addOption('d', 'd() for TD::dump()');
        $f->addOption('dumpBig', 'dumpBig() for TD::dumpBig()');
        $f->addOption('db', 'db() for TD::dumpBig()');
        $f->addOption('fireLog', 'fireLog() for TD::fireLog()');
        $f->addOption('fl', 'fl() for TD::fireLog()');
        $f->addOption('l', 'l() for TD::log()');
        $f->addOption('templateVars', 'templateVars() for TD::templateVars()');
        $f->addOption('tv', 'tv() for TD::templateVars()');
        $f->addOption('timer', 'timer() for TD::timer()');
        $f->addOption('t', 't() for TD::timer()');
        if($data['enabledShortcutMethods']) $f->attr('value', $data['enabledShortcutMethods']);
        $fieldset->add($f);


        $this->wire('modules')->addHookBefore('saveModuleConfigData', null, function($event) {

            if($event->arguments[0] !== 'TracyDebugger') return;
            if(!$this->wire('input')->post->customPWInfoPanelLinks[0] && $this->wire('input')->post->linksCode == '') return;

            $data = $event->arguments[1];

            // convert PW Info panel custom links page IDs into paths so we can export settings to another PW site
            $customPWInfoPanelLinkPaths = array();
            $customPWInfoPanelLinks = $this->wire('input')->post->customPWInfoPanelLinks;
            if(method_exists($this->wire('pages'), 'getPath') && version_compare($this->wire('config')->version, '3.0.73', '>=')) {
                foreach(explode(',',$customPWInfoPanelLinks[0]) as $pid) {
                    array_push($customPWInfoPanelLinkPaths, $this->wire('pages')->getPath($pid));
                }
            }
            // fallback for PW < 3.0.6 when getPath method did not exist
            else {
                foreach(explode(',',$customPWInfoPanelLinks[0]) as $pid) {
                    array_push($customPWInfoPanelLinkPaths, $this->wire('pages')->get($pid)->path);
                }
            }
            $data['customPWInfoPanelLinks'] = $customPWInfoPanelLinkPaths;

            // make URLs in links panel root relative and get titles if not supplied
            $allLinks = array();
            foreach(explode("\n", $this->wire('input')->post->linksCode) as $link) {
                $link_parts = explode('|', $link);
                $url = trim($link_parts[0]);
                $title = isset($link_parts[1]) ? trim($link_parts[1]) : '';
                $url = str_replace($this->wire('config')->urls->httpRoot, '/', $url);
                if($title == '') {
                    $http = new WireHttp();
                    $fullUrl = strpos($url, 'http') === false ? $this->wire('config')->urls->httpRoot . $url : $url;
                    $html = $http->get($fullUrl);
                    libxml_use_internal_errors(true);
                    $dom = new \DOMDocument();
                    $dom->loadHTML($html);
                    $list = $dom->getElementsByTagName('title');
                    libxml_use_internal_errors(false);
                    $title = $list->length ? str_replace('|', ':', $list->item(0)->textContent) : $url;
                }
                $finalLink = $url . ' | ' . $title;
                $allLinks[] = $finalLink;
            }
            $data['linksCode'] = implode("\n", $allLinks);

            $event->arguments(1, $data);
        });

        $this->wire('modules')->addHookAfter('saveModuleConfigData', null, function($event) {
            if($event->arguments[0] !== 'TracyDebugger') return;
            $data = $event->arguments[1];

            // if custom php panel code was changed, then scroll down to that setting field after saving
            if($data['customPhpCode'] != $this->data['customPhpCode']) $this->wire('session')->scrolltoCustomPhp = 1;

            // if certain settings are changed we need to delete the cached copy
            $changedSettings = array(
                'TracyApiData.*' => array(
                    'codeShowDescription',
                    'apiExplorerShowDescription',
                    'apiExplorerToggleDocComment',
                    'apiExplorerModuleClasses',
                    'captainHookShowDescription',
                    'captainHookToggleDocComment'
                )
            );
            foreach($changedSettings as $cache => $settings) {
                $deleteCache = false;
                foreach($settings as $setting) {
                    if($data[$setting] != $this->data[$setting]) {
                        $deleteCache = true;
                        break;
                    }
                }
                if($deleteCache) $this->deleteCache($cache);
            }

        });

        foreach(static::$externalPanels as $name => $path) {
            $className = ucfirst($name) . 'Panel';
            if(!class_exists($className)) {
                include_once $path;
            }
            $externalPanel = new $className;
            if(method_exists($externalPanel, 'addSettings')) {
                $externalPanelSettings = $externalPanel->addSettings();
                $wrapper->add($externalPanelSettings);
            }
        }

        return $wrapper;

    }


    public function ___upgrade($fromVersion, $toVersion) {

        // move old snippets from Tracy module settings DB table to filesystem
        if(isset($this->data['snippets'])) {
            $snippetsPath = $this->wire('config')->paths->site.$this->data['snippetsPath'].'/TracyDebugger/snippets/';
            if(!file_exists($snippetsPath)) wireMkdir($snippetsPath, true);
            foreach(json_decode($this->data['snippets']) as $snippet) {
                file_put_contents($snippetsPath.$snippet->name.'.php', urldecode($snippet->code));
                touch($snippetsPath.$snippet->name.'.php', substr($snippet->modified, 0, -3));
            }
            $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
            unset($configData['snippets']);
            $this->wire('modules')->saveModuleConfigData($this, $configData);
        }
    }

}