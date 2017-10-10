<?php

use Tracy\Dumper;

/**
 * Custom PW panel
 */

class ProcesswireInfoPanel extends BasePanel {

    protected $icon;
    protected $apiBaseUrl;

    public function __construct() {
        if(wire('modules')->isInstalled('ProcessWireAPI')) {
            $ApiModuleId = wire('modules')->getModuleID("ProcessWireAPI");
            $this->apiBaseUrl = wire('pages')->get("process=$ApiModuleId")->url.'methods/';
        }
        else {
            $this->apiBaseUrl = 'https://processwire.com/api/ref/';
        }
    }

    public function getTab() {
        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('processwireInfo');

            $this->icon = '
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="16.1px" viewBox="80 80.1 16 16.1" enable-background="new 80 80.1 16 16.1" xml:space="preserve">
            <path fill="#EB1D61" d="M94.6,83.7c-0.5-0.7-1.3-1.6-2.1-2.1c-1.7-1.2-3.6-1.6-5.4-1.4c-1.8,0.2-3.3,0.9-4.6,2
                c-1.2,1.1-1.9,2.3-2.3,3.6C80,87,80,88.1,80.1,89c0.1,0.9,0.6,2,0.6,2c0.1,0.2,0.2,0.3,0.3,0.3c0.3,0.2,0.8,0,1.2-0.4
                c0,0,0-0.1,0-0.1c-0.1-0.4-0.1-0.8-0.2-1c-0.1-0.5-0.1-1.3-0.1-2.1c0-0.4,0.1-0.9,0.2-1.3c0.3-0.9,0.8-1.9,1.7-2.7
                c1-0.9,2.2-1.4,3.4-1.5c0.4,0,1.2-0.1,2.1,0.1c0.2,0,1.1,0.3,2,0.9c0.7,0.5,1.2,1,1.6,1.6c0.4,0.5,0.8,1.4,0.9,2.1
                c0.2,0.8,0.2,1.6,0,2.3c-0.1,0.8-0.4,1.5-0.8,2.2c-0.3,0.5-0.9,1.2-1.6,1.7c-0.6,0.5-1.4,0.8-2.1,1c-0.4,0.1-0.8,0.1-1.1,0.2
                c-0.3,0-0.8,0-1.1-0.1c-0.5-0.1-0.6-0.2-0.7-0.4c0,0-0.1-0.1-0.1-0.4c0-3,0-2.2,0-3.7c0-0.4,0-0.8,0-1.2c0-0.6,0.1-1,0.5-1.4
                c0.3-0.3,0.7-0.5,1.2-0.5c0.1,0,0.6,0,1.1,0.4c0.5,0.4,0.5,0.9,0.6,1.1c0.1,0.8-0.4,1.4-0.6,1.6c-0.2,0.1-0.4,0.3-0.6,0.3
                C88,90,87.6,90,87.3,90c-0.1,0-0.1,0-0.1,0.1l-0.1,0.6c-0.1,0.4,0.1,0.6,0.3,0.7c0.4,0.1,0.8,0.2,1.3,0.2c0.7-0.1,1.4-0.3,2-0.9
                c0.5-0.5,0.8-1.1,0.9-1.8c0.1-0.8,0-1.6-0.4-2.3c-0.4-0.8-1.1-1.4-1.9-1.7c-0.9-0.3-1.5-0.4-2.4-0.1c0,0,0,0,0,0
                c-0.6,0.2-1.1,0.4-1.6,1C85,86,84.7,86.5,84.5,87c-0.2,0.5-0.2,0.9-0.2,1.5c0,0.4,0,0.8,0,1.2v2.5c0,0.8,0,0.9,0,1.3
                c0,0.3,0.1,0.6,0.2,0.9c0.1,0.4,0.4,0.7,0.6,0.9c0.2,0.3,0.6,0.5,0.9,0.6c0.7,0.3,1.7,0.4,2.4,0.3c0.5,0,1-0.1,1.5-0.2
                c1-0.2,2-0.7,2.8-1.3c0.9-0.6,1.7-1.5,2.1-2.3c0.6-0.9,0.9-1.9,1.1-2.9c0.2-1,0.2-2.1-0.1-3.1C95.7,85.5,95.2,84.5,94.6,83.7
                L94.6,83.7z"/>
            </svg>';

            return '
            <span title="ProcessWire Info & Links">' .
                $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;ProcessWire' : '') . '
            </span>';
    }

    protected function sectionHeader($columnNames = array()) {
        $out = '
        <div>
            <table>
                <thead>
                    <tr>';
        foreach($columnNames as $columnName) {
            $out .= '<th>'.$columnName.'</th>';
        }

        $out .= '
                    </tr>
                </thead>
            <tbody>
        ';
        return $out;
    }

    public function getPanel() {

        $PwVersion = $this->wire('config')->version;

        $panelSections = \TracyDebugger::getDataValue('processwireInfoPanelSections');

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';

        $userLang = $this->wire('user')->language;

        /**
         * Panel sections
         */

        // API Variables
        if(in_array('apiVariables', $panelSections)) {
            $apiVariables = '';
            $apiVariables = $this->sectionHeader(array('Name', 'Class'));
            $pwVars = $PwVersion >= 2.8 ? $this->wire('all') : $this->wire()->fuel;
            if(is_object($pwVars)) {
                $apiVars = array();
                foreach($pwVars as $key => $value) {
                    if(!is_object($value)) continue;
                    $apiVars[$key] = $value;
                }
                ksort($apiVars);
                foreach($apiVars as $key => $value) {
                    $apiVariables .= "\n<tr><td><a href='".$this->apiBaseUrl.strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $key))."/'>\$$key</a></td>" .
                        "<td>" . get_class($value) . "</td></tr>";
                }
            }
            $apiVariables .= $sectionEnd;
        }


        // Core Classes
        if(in_array('coreClasses', $panelSections)) {
            $coreClasses = $this->sectionHeader(array('Type', 'Name',));

            $classTypes = array(
                'Primary' => array(
                    'Wire',
                    'WireData',
                    'WireArray',
                ),
                'Pages' => array(
                    'Page',
                    'NullPage',
                    'User',
                    'Role',
                    'Permission',
                ),
                'Arrays' => array(
                    'WireArray',
                    'PageArray',
                    'PaginatedArray',
                ),
                'Modules' => array(
                    'Module',
                    'Fieldtype',
                    'Inputfield',
                    'Process',
                    'Textformatter',
                ),

                'Files & Images' => array(
                    'Pagefile',
                    'Pagefiles',
                    'Pageimage',
                    'Pageimages',
                    'PagefilesManager',
                ),
                'Fields & Templates' => array(
                    'Field',
                    'Fieldgroup',
                    'Template',
                ),
                'Additional' => array(
                    'HookEvent',
                    'InputfieldWrapper',
                    'WireHttp',
                    'WireMail',
                    'SessionCSRF',
                    'ProcessWire',
                    'PagesType',
                    'Selector',
                    'Selectors',
                    'WireDatabaseBackup',
                    'MarkupPagerNav',
                )
            );
            $currentType = '';
            foreach($classTypes as $type => $classes) {
                foreach($classes as $class) {
                    $coreClasses .= "\n<tr><td>".($currentType !== $type ? $type : '')."</td><td><a href='".$this->apiBaseUrl.strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class))."/'>".$class."</a></td></tr>";
                    $currentType = $type;
                }
            }
            $coreClasses .= $sectionEnd;
        }


        // Config Settings
        if(in_array('versionsList', $panelSections)) {
            $configData = $this->sectionHeader(array('Key', 'Value'));
            foreach($this->wire('config') as $key => $value) {
                if(is_object($value)) {
                    $outValue = method_exists($value,'getIterator') ? $value->getIterator() : $value;
                    $value = (array)$outValue;
                    ksort($value);
                    if($key == 'paths') $value = array_map(array($this, 'addRoot'), $value);
                }
                $value = \Tracy\Dumper::toHtml($value, array(Dumper::LIVE => true, Dumper::DEPTH => 10, Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => (count($value) !== count($value, COUNT_RECURSIVE) || is_object($value) ? true : false)));
                $configData .= "<tr><td>".$this->wire('sanitizer')->entities($key)."</td><td>" . $value . "</td></tr>";
            }
            $configData .= $sectionEnd;
        }


        // Versions Info
        if(in_array('versionsList', $panelSections)) {
            $versionsList = '
            <script>
                function closePanel() {
                    localStorage.setItem("remove-tracy-debug-panel-ProcesswireInfoPanel", 1);
                }

                // javascript dynamic loader from https://gist.github.com/hagenburger/500716
                // using dynamic loading because an exception error or "exit" in template file
                // was preventing these scripts from being loaded which broke the editor
                // if this has any problems, there is an alternate version to try here:
                // https://www.nczonline.net/blog/2009/07/28/the-best-way-to-load-external-javascript/
                var JavaScript = {
                    load: function(src, callback) {
                        var script = document.createElement("script"),
                                loaded;
                        script.setAttribute("src", src);
                        if (callback) {
                            script.onreadystatechange = script.onload = function() {
                                if (!loaded) {
                                    callback();
                                }
                                loaded = true;
                            };
                        }
                        document.getElementsByTagName("head")[0].appendChild(script);
                    }
                };
                JavaScript.load("'.$this->wire('config')->urls->TracyDebugger.'clipboardjs/clipboard.min.js", function() {
                    JavaScript.load("'.$this->wire('config')->urls->TracyDebugger.'clipboardjs/tooltips.js", function() {
                        var versionsClipboard=new Clipboard(".tracyCopyBtn");
                        versionsClipboard.on("success",function(e){e.clearSelection();showTooltip(e.trigger,"Copied!");});versionsClipboard.on("error",function(e){showTooltip(e.trigger,fallbackMessage(e.action));});
                    });
                });
            </script>
            ';
            $eol = " <br />\n";
            $serverInfo = "ProcessWire: " . $this->wire('config')->version . $eol;
            $serverInfo .= "PHP: " . phpversion() . $eol;
            if(isset($_SERVER['SERVER_SOFTWARE'])) $serverInfo .= "Webserver: " . current(explode("PHP", $_SERVER['SERVER_SOFTWARE'])) . $eol;
            $serverInfo .= "MySQL: " . $this->wire('database')->query('select version()')->fetchColumn() . $eol . $eol;

            $serverSettings = "";
            //php settings
            foreach(array('allow_url_fopen', 'max_execution_time', 'max_input_nesting_level', 'max_input_time', 'max_input_vars', 'memory_limit', 'post_max_size', 'upload_max_filesize', 'xdebug', 'xdebug.max_nesting_level') as $setting) {
                if($setting == 'max_execution_time') {
                    $max_execution_time = trim(ini_get('max_execution_time'));
                    $can_change = set_time_limit($max_execution_time);
                }
                $serverSettings .= $setting . ": " . ini_get($setting);
                if($setting == 'max_execution_time') {
                    $serverSettings .= isset($can_change) ? ' (changeable)' : ' (not changeable)';
                }
                $serverSettings .= $eol;
            }
            $serverSettings .= $eol;

            // apache modules
            if(function_exists('apache_get_modules')) $apacheModules = apache_get_modules();
            foreach(array('mod_rewrite', 'mod_security') as $apacheModule) {
                if(isset($apacheModules)) {
                    $serverSettings .= $apacheModule . ": " . (in_array($apacheModule, $apacheModules) ? '1' : false . ($apacheModule == 'mod_security' ? '*confirmed off' : '')) . $eol;
                }
                // fallback if apache_get_modules() is not available
                else {
                    // this is a more reliable fallback for mod_rewrite
                    if($apacheModule == 'mod_rewrite' && isset($_SERVER["HTTP_MOD_REWRITE"])) {
                        $serverSettings .= $apacheModule . ": " . ($_SERVER["HTTP_MOD_REWRITE"] ? '1' : false) . $eol;
                    }
                    // this is for mod_security and any others specified, although it's still not very reliable for mod_security
                    else {
                        ob_start();
                        phpinfo(INFO_MODULES);
                        $contents = ob_get_clean();
                        $serverSettings .= $apacheModule . ": " . (strpos($contents, $apacheModule) ? '1' : false) . $eol;
                    }
                }
            }
            $serverSettings .= $eol;
            // image settings
            if(function_exists('gd_info')) {
                $gd  = gd_info();
                $serverSettings .= "GD: " . (isset($gd['GD Version']) ? $gd['GD Version'] : $this->_('Version-Info not available')) . $eol;
                $serverSettings .= "GIF: " . (isset($gd['GIF Read Support']) && isset($gd['GIF Create Support']) ? $gd['GIF Create Support'] : false) . $eol;
                $serverSettings .= "JPG: " . (isset($gd['JPEG Support']) ? $gd['JPEG Support'] : false) . $eol;
                $serverSettings .= "PNG: " . (isset($gd['PNG Support']) ? $gd['PNG Support'] : false) . $eol;

            }
            $serverSettings .= $eol;
            $serverSettings .= "EXIF Support: " . (function_exists('exif_read_data') ? '1' : false) . $eol;
            $serverSettings .= "FreeType: " . (isset($gd['FreeType Support']) ? $gd['FreeType Support'] : false) . $eol;
            $serverSettings .= "Imagick Extension: " . (class_exists('Imagick') ? '1' : false) . $eol;

            $serverSettings .= $eol;

            $moduleInfo = '';
            foreach($this->wire('modules')->sort("className") as $name => $label) {
                $flags = $this->wire('modules')->getFlags($name);
                $info = $this->wire('modules')->getModuleInfoVerbose($name);
                if($info['core']) continue;
                $moduleInfo .= $name . ": " . $this->wire('modules')->formatVersion($info['version']) . $eol;
            }
            $githubVersionsList = '<details><summary><strong>Server Details</strong></summary>' . $serverInfo . '<details><summary><strong>Server Settings</strong></summary> ' . $serverSettings . '</details><details><summary><strong>Module Details</strong></summary> ' . $moduleInfo . '</details></details>';
            $versionsList .= '
            <p>
                <button class="tracyCopyBtn" data-clipboard-text="'.$githubVersionsList.'">
                    Copy for Github
                </button>
                <button class="tracyCopyBtn" data-clipboard-target="#versionsListTextarea">
                    Copy plain text
                </button>
            </p>
            <p><textarea id="versionsListTextarea" rows="5" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" style="font-size:12px; width:100%; resize:vertical; padding:3px !important">'.str_replace(" <br />", "", $serverInfo . $serverSettings . $moduleInfo).'</textarea></p>';
        }


        // Load all the panel sections
        $out = '
        <h1>' . $this->icon . ' ProcessWire Info</h1>
        <div class="tracy-inner">
        ';

        // all the "non" icon links sections
        foreach(\TracyDebugger::$processWireInfoSections as $name => $label) {
            // get all sections excluding those that are admin "links"
            if(strpos($name, 'Links') === false && in_array($name, $panelSections)) {
                if(isset(${$name}) && ${$name} !== '') {
                    if($label == 'Module Settings') $label = $label . ' (' . $moduleName . ')';
                    if($label == 'Template Settings') $label = $label . ' (' . $template->name . ')';
                    if($label == 'Field Settings') $label = $label . ' (' . $field->name . ')';
                    $out .= '
                    <a href="#" rel="'.$name.'" class="tracy-toggle tracy-collapsed">'.$label.'</a>
                    <div id="'.$name.'" class="tracy-collapsed">'.${$name}.'</div><br />';
                }
            }
        }

        // all the icon links sections
        $withLabels = \TracyDebugger::getDataValue('showPWInfoPanelIconLabels');

        if(in_array('adminLinks', $panelSections)) {
            $panelTitle = 'ProcessWire Admin';
            $out .= '
            <ul class="pw-info-links" style="'.($withLabels ? '' :'text-align: center; ').'">
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'" title="'.$panelTitle.'">
                        ' . $this->icon . ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';

                if($this->wire('user')->isLoggedIn()) {
                    $panelTitle = 'Logout ('.$this->wire('user')->name.')';
                    $out .= '
                    <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                        <a onclick="closePanel()" href="'.\TracyDebugger::inputUrl(true) . (strpos(\TracyDebugger::inputUrl(true), '?') !== false ? '&' : '?') . 'tracyLogout=1" title="'.$panelTitle.'">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                 width="16px" height="16px" viewBox="2.5 0 16 16" enable-background="new 2.5 0 16 16" xml:space="preserve">
                                <g>
                                    <path d="M7.6,5.8c0.4-0.4,0.4-1,0-1.4C7.4,4.2,7.2,4.1,6.9,4.1c-0.3,0-0.5,0.1-0.7,0.3L3,7.3c0,0,0,0-0.1,0.1
                                        c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0c0,0,0,0.1,0,0.1c0,0,0,0,0,0
                                        c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0.1,0,0.1c0,0,0,0.1,0,0.1c0,0,0,0,0,0.1c0,0,0,0,0,0c0,0,0,0.1,0,0.1
                                        c0,0,0,0,0,0c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0,0,0.1,0,0.1c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0
                                        c0,0,0,0,0.1,0.1l3.2,2.9c0.4,0.4,1.1,0.4,1.5,0c0.4-0.4,0.4-1,0-1.4L6.3,9h7.9c0.6,0,1.1-0.4,1.1-1s-0.5-1-1.1-1H6.3L7.6,5.8z" fill="#ee1d62" />
                                    <path d="M4.8,9h9.3c0.6,0,1.1-0.4,1.1-1s-0.5-1-1.1-1H4.8" fill="#ee1d62" />
                                    <path d="M9.9,0C7,0,4.3,1.3,2.7,3.5C2.3,4,2.5,4.6,3,4.9c0.5,0.3,1.1,0.2,1.5-0.3C5.6,3,7.7,2,9.9,2
                                        c3.6,0,6.5,2.7,6.5,6s-2.9,6-6.5,6c-2.2,0-4.2-1-5.4-2.7c-0.3-0.4-1-0.6-1.5-0.3c-0.5,0.3-0.6,0.9-0.3,1.4C4.3,14.7,7,16,9.9,16
                                        c4.8,0,8.6-3.6,8.6-8S14.6,0,9.9,0z" fill="#ee1d62" />
                                </g>
                            </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                    '</li>';
                }
                else {
                    $panelTitle = 'Login';
                    $out .= '
                    <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                        <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin . (strpos(\TracyDebugger::inputUrl(true), '?') !== false ? '&' : '?') . 'tracyLogin=1" title="'.$panelTitle.'">
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                 width="16px" height="16px" viewBox="1.9 0 16 16" enable-background="new 1.9 0 16 16" xml:space="preserve">
                                <g>
                                    <path d="M8.9,10.3c-0.4,0.4-0.4,0.9,0,1.4c0.1,0.1,0.4,0.3,0.7,0.3c0.3,0,0.5-0.1,0.7-0.3l3-3c0,0,0,0,0-0.1l0,0l0,0
                                        l0,0l0,0l0,0c0,0,0,0,0-0.1l0,0c0,0,0,0,0-0.1l0,0c0,0,0,0,0-0.1l0,0c0,0,0,0,0-0.1c0,0,0,0,0-0.1c0,0,0,0,0-0.1c0,0,0,0,0-0.1l0,0
                                        c0,0,0,0,0-0.1l0,0c0,0,0,0,0-0.1l0,0c0,0,0,0,0-0.1l0,0l0,0l0,0l0,0l0,0c0,0,0,0,0-0.1l-3-3C10,3.9,9.3,3.9,8.9,4.3
                                        c-0.4,0.4-0.4,0.9,0,1.4l1.2,1.2H2.9C2.4,7.1,1.9,7.5,1.9,8c0,0.5,0.4,0.9,0.9,0.9h7.3L8.9,10.3z" fill="#ee1d62" />
                                    <path d="M11.6,7.1H2.9C2.4,7.1,1.9,7.5,1.9,8c0,0.5,0.4,0.9,0.9,0.9h8.6" fill="#ee1d62" />
                                    <path d="M10.2,0C7.5,0,5,1.4,3.6,3.5C3.2,3.9,3.3,4.6,3.7,4.9C4.1,5.2,4.8,5,5,4.6C6.1,3,8,1.9,10,1.9
                                        c3.4,0,6.1,2.7,6.1,6.1s-2.7,6.1-6.1,6.1c-2,0-3.9-0.9-5-2.7c-0.3-0.4-0.9-0.5-1.3-0.3c-0.4,0.3-0.5,0.9-0.3,1.4
                                        c1.5,2.2,4,3.5,6.6,3.5c4.4,0,7.9-3.7,7.9-8S14.5,0,10.2,0z" fill="#ee1d62" />
                                </g>
                            </svg>'.
                            ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                    '</li>';
                }
                $panelTitle = 'Clear Session & Cookies';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.\TracyDebugger::inputUrl(true) . (strpos(\TracyDebugger::inputUrl(true), '?') !== false ? '&' : '?') . 'tracyClearSession=1" title="'.$panelTitle.'">
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve">
                            <path fill="#EE2363" d="M15.747201,6.8416004h-1.9904003C13.2032013,3.5680001,10.3552008,1.072,6.9280005,1.072
                            C3.1040001,1.072,0.0032,4.1760001,0.0032,8s3.1008,6.9280005,6.9248004,6.9280005
                            c1.7440004,0,3.3375998-0.6464005,4.5535994-1.7087994l-1.4335995-1.8207998
                            c-0.8224001,0.7551994-1.9167995,1.2192001-3.1167998,1.2192001c-2.5472002,0-4.6176004-2.0703993-4.6176004-4.6176004
                            s2.0704-4.6176004,4.6176004-4.6176004c2.1472001,0,3.9487996,1.4720001,4.4640002,3.4592004H9.3792009
                            c-0.2528,0-0.3296003,0.1632004-0.1695995,0.3583999l3.0656004,3.7728004c0.1599998,0.1984005,0.4191999,0.1984005,0.5824003,0
                            l3.0655985-3.7728009C16.0768013,7.0048003,16,6.8416004,15.747201,6.8416004z"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Templates';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'setup/template/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 86.02 86.02" style="enable-background:new 0 0 86.02 86.02;" xml:space="preserve"  style="height:16px">
                            <path d="M0.354,48.874l0.118,25.351c0.001,0.326,0.181,0.624,0.467,0.779l20.249,10.602c0.132,0.071,0.276,0.106,0.421,0.106   c0.001,0,0.001,0,0.002,0c0.061,0.068,0.129,0.133,0.211,0.182c0.14,0.084,0.297,0.126,0.455,0.126   c0.146,0,0.291-0.035,0.423-0.106l19.992-10.842c0.183-0.099,0.315-0.261,0.392-0.445c0.081,0.155,0.203,0.292,0.364,0.379   l20.248,10.602c0.132,0.071,0.277,0.106,0.422,0.106c0.001,0,0.001,0,0.002,0c0.062,0.068,0.129,0.133,0.21,0.182   c0.142,0.084,0.299,0.126,0.456,0.126c0.146,0,0.29-0.035,0.422-0.106L85.2,75.071c0.287-0.154,0.467-0.456,0.467-0.783V47.911   c0-0.008-0.004-0.016-0.004-0.022c0-0.006,0.002-0.013,0.002-0.021c-0.001-0.023-0.01-0.049-0.014-0.072   c-0.007-0.05-0.014-0.098-0.027-0.146c-0.011-0.031-0.023-0.062-0.038-0.093c-0.019-0.042-0.037-0.082-0.062-0.12   c-0.019-0.03-0.04-0.058-0.062-0.084c-0.028-0.034-0.059-0.066-0.092-0.097c-0.025-0.023-0.054-0.045-0.083-0.066   c-0.02-0.012-0.034-0.03-0.056-0.043c-0.02-0.011-0.041-0.017-0.062-0.025c-0.019-0.01-0.03-0.022-0.049-0.029l-20.603-9.978   c-0.082-0.034-0.17-0.038-0.257-0.047V10.865c0-0.007-0.002-0.015-0.002-0.022c-0.001-0.007,0.001-0.013,0.001-0.02   c-0.001-0.025-0.012-0.049-0.015-0.073c-0.007-0.049-0.014-0.098-0.027-0.145c-0.01-0.032-0.024-0.063-0.038-0.093   c-0.02-0.042-0.036-0.083-0.062-0.12c-0.02-0.03-0.041-0.057-0.062-0.084c-0.028-0.034-0.058-0.067-0.091-0.097   c-0.025-0.023-0.055-0.045-0.083-0.065c-0.021-0.014-0.035-0.032-0.056-0.045c-0.021-0.011-0.042-0.016-0.062-0.026   c-0.019-0.009-0.031-0.021-0.048-0.027L43.118,0.07c-0.24-0.102-0.512-0.093-0.746,0.025L22.009,10.71   c-0.299,0.151-0.487,0.456-0.489,0.79c0,0.006,0.002,0.011,0.002,0.016c-0.037,0.099-0.063,0.202-0.063,0.312l0.118,25.233   c-0.106,0.011-0.213,0.03-0.311,0.079L0.903,47.755c-0.298,0.15-0.487,0.456-0.489,0.791c0,0.005,0.003,0.009,0.003,0.015   C0.379,48.659,0.353,48.764,0.354,48.874z M61.321,10.964L43.372,21l-19.005-9.485l18.438-9.646L61.321,10.964z M62.486,37.008   l-18.214,9.586V22.535l18.214-10.18V37.008z M65.674,59.58l18.214-10.179v24.355l-18.214,9.883V59.58z M45.77,48.559l18.438-9.646   l18.515,9.099L64.775,58.045L45.77,48.559z M23.165,59.58L41.38,49.402v24.355l-18.215,9.882V59.58z M3.262,48.559L21.7,38.913   l18.515,9.099L22.266,58.045L3.262,48.559z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Fields';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'setup/field/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 82.626 82.627" style="enable-background:new 0 0 82.626 82.627;" xml:space="preserve"  style="height:16px">
                            <path d="M5.206,19.956l0.199,42.771c0.003,0.55,0.306,1.054,0.789,1.314l34.161,17.887c0.223,0.119,0.467,0.179,0.711,0.179   c0.001,0,0.002,0,0.003,0c0.103,0.117,0.218,0.227,0.355,0.309c0.236,0.141,0.502,0.212,0.769,0.212   c0.246,0,0.49-0.061,0.712-0.181l33.729-18.292c0.484-0.263,0.787-0.77,0.787-1.319v-44.5c0-0.013-0.005-0.025-0.005-0.039   c-0.001-0.011,0.003-0.021,0.003-0.033c-0.002-0.043-0.019-0.082-0.022-0.124c-0.013-0.082-0.022-0.164-0.047-0.243   c-0.018-0.055-0.041-0.104-0.064-0.157c-0.031-0.07-0.062-0.139-0.104-0.203c-0.031-0.05-0.068-0.095-0.105-0.141   c-0.047-0.058-0.096-0.112-0.152-0.163c-0.044-0.04-0.091-0.076-0.141-0.111c-0.032-0.022-0.059-0.053-0.094-0.073   c-0.032-0.02-0.069-0.028-0.104-0.045c-0.029-0.015-0.052-0.036-0.081-0.049L41.747,0.118c-0.405-0.171-0.864-0.155-1.258,0.042   L6.131,18.071c-0.504,0.254-0.822,0.77-0.825,1.333c0,0.009,0.004,0.017,0.004,0.025C5.249,19.596,5.205,19.772,5.206,19.956z    M72.456,18.501l-30.28,16.93L10.111,19.425L41.218,3.151L72.456,18.501z M43.692,78.61V38.021l30.729-17.173v41.09L43.692,78.61z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Logs';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'setup/logs/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 433.494 433.494" style="enable-background:new 0 0 433.494 433.494;" xml:space="preserve" style="height:16px">
                            <polygon points="353.763,379.942 279.854,234.57 322.024,250.717 253.857,116.637 276.286,127.997 216.747,0 157.209,127.997     179.64,116.636 111.471,250.717 153.642,234.569 79.731,379.942 200.872,337.52 200.872,433.494 232.624,433.494 232.624,337.518       " fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Modules';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'module/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 99.012 99.012" style="enable-background:new 0 0 99.012 99.012;" xml:space="preserve">
                            <g>
                                <path d="M25.08,15.648c-0.478-0.478-1.135-0.742-1.805-0.723c-0.675,0.017-1.314,0.309-1.768,0.808    c-14.829,16.325-6.762,51.623-5.916,55.115L0.723,85.717C0.26,86.18,0,86.808,0,87.463c0,0.654,0.26,1.283,0.723,1.746    l8.958,8.957c0.482,0.48,1.114,0.723,1.746,0.723c0.631,0,1.264-0.24,1.745-0.723l14.865-14.864    c7.237,1.859,16.289,2.968,24.28,2.968c9.599,0,22.739-1.543,30.836-8.886c0.5-0.454,0.793-1.093,0.809-1.769    c0.018-0.676-0.245-1.328-0.723-1.805L25.08,15.648z" fill="#ee1d62"/>
                                <path d="M46.557,30.345c0.482,0.482,1.114,0.723,1.746,0.723c0.632,0,1.264-0.241,1.746-0.724l18.428-18.428    c1.305-1.305,2.023-3.04,2.023-4.885c0-1.846-0.719-3.582-2.023-4.886c-1.305-1.305-3.039-2.022-4.885-2.022    c-1.845,0-3.581,0.718-4.887,2.022L40.277,20.574c-0.964,0.964-0.964,2.527,0,3.492L46.557,30.345z" fill="#ee1d62"/>
                                <path d="M96.99,30.661c-1.305-1.305-3.039-2.024-4.885-2.024c-1.847,0-3.582,0.718-4.886,2.023L68.79,49.089    c-0.464,0.463-0.724,1.091-0.724,1.746s0.26,1.282,0.724,1.746l6.28,6.278c0.481,0.482,1.113,0.724,1.746,0.724    c0.631,0,1.264-0.241,1.746-0.724l18.43-18.429C99.686,37.735,99.686,33.353,96.99,30.661z" fill="#ee1d62"/>
                            </g>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Users';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'access/users/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 80.13 80.13" style="enable-background:new 0 0 80.13 80.13;" xml:space="preserve" style="height:16px">
                            <path d="M48.355,17.922c3.705,2.323,6.303,6.254,6.776,10.817c1.511,0.706,3.188,1.112,4.966,1.112   c6.491,0,11.752-5.261,11.752-11.751c0-6.491-5.261-11.752-11.752-11.752C53.668,6.35,48.453,11.517,48.355,17.922z M40.656,41.984   c6.491,0,11.752-5.262,11.752-11.752s-5.262-11.751-11.752-11.751c-6.49,0-11.754,5.262-11.754,11.752S34.166,41.984,40.656,41.984   z M45.641,42.785h-9.972c-8.297,0-15.047,6.751-15.047,15.048v12.195l0.031,0.191l0.84,0.263   c7.918,2.474,14.797,3.299,20.459,3.299c11.059,0,17.469-3.153,17.864-3.354l0.785-0.397h0.084V57.833   C60.688,49.536,53.938,42.785,45.641,42.785z M65.084,30.653h-9.895c-0.107,3.959-1.797,7.524-4.47,10.088   c7.375,2.193,12.771,9.032,12.771,17.11v3.758c9.77-0.358,15.4-3.127,15.771-3.313l0.785-0.398h0.084V45.699   C80.13,37.403,73.38,30.653,65.084,30.653z M20.035,29.853c2.299,0,4.438-0.671,6.25-1.814c0.576-3.757,2.59-7.04,5.467-9.276   c0.012-0.22,0.033-0.438,0.033-0.66c0-6.491-5.262-11.752-11.75-11.752c-6.492,0-11.752,5.261-11.752,11.752   C8.283,24.591,13.543,29.853,20.035,29.853z M30.589,40.741c-2.66-2.551-4.344-6.097-4.467-10.032   c-0.367-0.027-0.73-0.056-1.104-0.056h-9.971C6.75,30.653,0,37.403,0,45.699v12.197l0.031,0.188l0.84,0.265   c6.352,1.983,12.021,2.897,16.945,3.185v-3.683C17.818,49.773,23.212,42.936,30.589,40.741z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Roles';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'access/roles/" title="Roles">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 548.172 548.172" style="enable-background:new 0 0 548.172 548.172;" xml:space="preserve" style="height:16px">
                            <g>
                                <path d="M333.186,376.438c0-1.902-0.668-3.806-1.999-5.708c-10.66-12.758-19.223-23.702-25.697-32.832    c3.997-7.803,7.043-15.037,9.131-21.693l44.255-6.852c1.718-0.194,3.241-1.19,4.572-2.994c1.331-1.816,1.991-3.668,1.991-5.571    v-52.822c0-2.091-0.66-3.949-1.991-5.564s-2.95-2.618-4.853-2.993l-43.4-6.567c-2.098-6.473-5.331-14.281-9.708-23.413    c2.851-4.19,7.139-9.902,12.85-17.131c5.709-7.234,9.713-12.371,11.991-15.417c1.335-1.903,1.999-3.713,1.999-5.424    c0-5.14-13.706-20.367-41.107-45.683c-1.902-1.52-3.901-2.281-6.002-2.281c-2.279,0-4.182,0.659-5.712,1.997L245.815,150.9    c-7.801-3.996-14.939-6.945-21.411-8.854l-6.567-43.68c-0.187-1.903-1.14-3.571-2.853-4.997c-1.714-1.427-3.617-2.142-5.713-2.142    h-53.1c-4.377,0-7.232,2.284-8.564,6.851c-2.286,8.757-4.473,23.416-6.567,43.968c-8.183,2.664-15.511,5.71-21.982,9.136    l-32.832-25.693c-1.903-1.335-3.901-1.997-5.996-1.997c-3.621,0-11.138,5.614-22.557,16.846    c-11.421,11.228-19.229,19.698-23.413,25.409c-1.334,1.525-1.997,3.428-1.997,5.712c0,1.711,0.662,3.614,1.997,5.708    c10.657,12.756,19.221,23.7,25.694,32.832c-3.996,7.808-7.04,15.037-9.132,21.698l-44.255,6.848    c-1.715,0.19-3.236,1.188-4.57,2.993C0.666,243.35,0,245.203,0,247.105v52.819c0,2.095,0.666,3.949,1.997,5.564    c1.334,1.622,2.95,2.525,4.857,2.714l43.396,6.852c2.284,7.23,5.618,15.037,9.995,23.411c-3.046,4.191-7.517,9.999-13.418,17.418    c-5.905,7.427-9.805,12.471-11.707,15.133c-1.332,1.903-1.999,3.717-1.999,5.421c0,5.147,13.706,20.369,41.114,45.687    c1.903,1.519,3.899,2.275,5.996,2.275c2.474,0,4.377-0.66,5.708-1.995l33.689-25.406c7.801,3.997,14.939,6.943,21.413,8.847    l6.567,43.684c0.188,1.902,1.142,3.572,2.853,4.996c1.713,1.427,3.616,2.139,5.711,2.139h53.1c4.38,0,7.233-2.282,8.566-6.851    c2.284-8.949,4.471-23.698,6.567-44.256c7.611-2.275,14.938-5.235,21.982-8.846l32.833,25.693    c1.903,1.335,3.901,1.995,5.996,1.995c3.617,0,11.091-5.66,22.415-16.991c11.32-11.317,19.175-19.842,23.555-25.55    C332.518,380.53,333.186,378.724,333.186,376.438z M234.397,325.626c-14.272,14.27-31.499,21.408-51.673,21.408    c-20.179,0-37.406-7.139-51.678-21.408c-14.274-14.277-21.412-31.505-21.412-51.68c0-20.174,7.138-37.401,21.412-51.675    c14.272-14.275,31.5-21.411,51.678-21.411c20.174,0,37.401,7.135,51.673,21.411c14.277,14.274,21.413,31.501,21.413,51.675    C255.81,294.121,248.675,311.349,234.397,325.626z" fill="#ee1d62"/>
                                <path d="M505.628,391.29c-2.471-5.517-5.329-10.465-8.562-14.846c9.709-21.512,14.558-34.646,14.558-39.402    c0-0.753-0.373-1.424-1.14-1.995c-22.846-13.322-34.643-19.985-35.405-19.985l-1.711,0.574    c-7.803,7.807-16.563,18.463-26.266,31.977c-3.805-0.379-6.656-0.574-8.559-0.574c-1.909,0-4.76,0.195-8.569,0.574    c-2.655-4-7.61-10.427-14.842-19.273c-7.23-8.846-11.611-13.277-13.134-13.277c-0.38,0-3.234,1.522-8.566,4.575    c-5.328,3.046-10.943,6.276-16.844,9.709c-5.906,3.433-9.229,5.328-9.992,5.711c-0.767,0.568-1.144,1.239-1.144,1.992    c0,4.764,4.853,17.888,14.559,39.402c-3.23,4.381-6.089,9.329-8.562,14.842c-28.363,2.851-42.544,5.805-42.544,8.85v39.968    c0,3.046,14.181,5.996,42.544,8.85c2.279,5.141,5.137,10.089,8.562,14.839c-9.706,21.512-14.559,34.646-14.559,39.402    c0,0.76,0.377,1.431,1.144,1.999c23.216,13.514,35.022,20.27,35.402,20.27c1.522,0,5.903-4.473,13.134-13.419    c7.231-8.948,12.18-15.413,14.842-19.41c3.806,0.373,6.66,0.564,8.569,0.564c1.902,0,4.754-0.191,8.559-0.564    c2.659,3.997,7.611,10.462,14.842,19.41c7.231,8.946,11.608,13.419,13.135,13.419c0.38,0,12.187-6.759,35.405-20.27    c0.767-0.568,1.14-1.235,1.14-1.999c0-4.757-4.855-17.891-14.558-39.402c3.426-4.75,6.279-9.698,8.562-14.839    c28.362-2.854,42.544-5.804,42.544-8.85v-39.968C548.172,397.098,533.99,394.144,505.628,391.29z M464.37,445.962    c-7.128,7.139-15.745,10.715-25.834,10.715c-10.092,0-18.705-3.576-25.837-10.715c-7.139-7.139-10.712-15.748-10.712-25.837    c0-9.894,3.621-18.466,10.855-25.693c7.23-7.231,15.797-10.849,25.693-10.849c9.894,0,18.466,3.614,25.7,10.849    c7.228,7.228,10.849,15.8,10.849,25.693C475.078,430.214,471.512,438.823,464.37,445.962z" fill="#ee1d62"/>
                                <path d="M505.628,98.931c-2.471-5.52-5.329-10.468-8.562-14.849c9.709-21.505,14.558-34.639,14.558-39.397    c0-0.758-0.373-1.427-1.14-1.999c-22.846-13.323-34.643-19.984-35.405-19.984l-1.711,0.57    c-7.803,7.808-16.563,18.464-26.266,31.977c-3.805-0.378-6.656-0.57-8.559-0.57c-1.909,0-4.76,0.192-8.569,0.57    c-2.655-3.997-7.61-10.42-14.842-19.27c-7.23-8.852-11.611-13.276-13.134-13.276c-0.38,0-3.234,1.521-8.566,4.569    c-5.328,3.049-10.943,6.283-16.844,9.71c-5.906,3.428-9.229,5.33-9.992,5.708c-0.767,0.571-1.144,1.237-1.144,1.999    c0,4.758,4.853,17.893,14.559,39.399c-3.23,4.38-6.089,9.327-8.562,14.847c-28.363,2.853-42.544,5.802-42.544,8.848v39.971    c0,3.044,14.181,5.996,42.544,8.848c2.279,5.137,5.137,10.088,8.562,14.847c-9.706,21.51-14.559,34.639-14.559,39.399    c0,0.757,0.377,1.426,1.144,1.997c23.216,13.513,35.022,20.27,35.402,20.27c1.522,0,5.903-4.471,13.134-13.418    c7.231-8.947,12.18-15.415,14.842-19.414c3.806,0.378,6.66,0.571,8.569,0.571c1.902,0,4.754-0.193,8.559-0.571    c2.659,3.999,7.611,10.466,14.842,19.414c7.231,8.947,11.608,13.418,13.135,13.418c0.38,0,12.187-6.757,35.405-20.27    c0.767-0.571,1.14-1.237,1.14-1.997c0-4.76-4.855-17.889-14.558-39.399c3.426-4.759,6.279-9.707,8.562-14.847    c28.362-2.853,42.544-5.804,42.544-8.848v-39.971C548.172,104.737,533.99,101.787,505.628,98.931z M464.37,153.605    c-7.128,7.139-15.745,10.708-25.834,10.708c-10.092,0-18.705-3.569-25.837-10.708c-7.139-7.135-10.712-15.749-10.712-25.837    c0-9.897,3.621-18.464,10.855-25.697c7.23-7.233,15.797-10.85,25.693-10.85c9.894,0,18.466,3.621,25.7,10.85    c7.228,7.232,10.849,15.8,10.849,25.697C475.078,137.856,471.512,146.47,464.37,153.605z" fill="#ee1d62"/>
                            </g>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Permissions';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'access/permissions/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 268.765 268.765" style="enable-background:new 0 0 268.765 268.765;" xml:space="preserve" width="16px" height="16px" style="height:16px">
                            <path style="fill-rule:evenodd;clip-rule:evenodd;" d="M267.92,119.461c-0.425-3.778-4.83-6.617-8.639-6.617    c-12.315,0-23.243-7.231-27.826-18.414c-4.682-11.454-1.663-24.812,7.515-33.231c2.889-2.641,3.24-7.062,0.817-10.133    c-6.303-8.004-13.467-15.234-21.289-21.5c-3.063-2.458-7.557-2.116-10.213,0.825c-8.01,8.871-22.398,12.168-33.516,7.529    c-11.57-4.867-18.866-16.591-18.152-29.176c0.235-3.953-2.654-7.39-6.595-7.849c-10.038-1.161-20.164-1.197-30.232-0.08    c-3.896,0.43-6.785,3.786-6.654,7.689c0.438,12.461-6.946,23.98-18.401,28.672c-10.985,4.487-25.272,1.218-33.266-7.574    c-2.642-2.896-7.063-3.252-10.141-0.853c-8.054,6.319-15.379,13.555-21.74,21.493c-2.481,3.086-2.116,7.559,0.802,10.214    c9.353,8.47,12.373,21.944,7.514,33.53c-4.639,11.046-16.109,18.165-29.24,18.165c-4.261-0.137-7.296,2.723-7.762,6.597    c-1.182,10.096-1.196,20.383-0.058,30.561c0.422,3.794,4.961,6.608,8.812,6.608c11.702-0.299,22.937,6.946,27.65,18.415    c4.698,11.454,1.678,24.804-7.514,33.23c-2.875,2.641-3.24,7.055-0.817,10.126c6.244,7.953,13.409,15.19,21.259,21.508    c3.079,2.481,7.559,2.131,10.228-0.81c8.04-8.893,22.427-12.184,33.501-7.536c11.599,4.852,18.895,16.575,18.181,29.167    c-0.233,3.955,2.67,7.398,6.595,7.85c5.135,0.599,10.301,0.898,15.481,0.898c4.917,0,9.835-0.27,14.752-0.817    c3.897-0.43,6.784-3.786,6.653-7.696c-0.451-12.454,6.946-23.973,18.386-28.657c11.059-4.517,25.286-1.211,33.281,7.572    c2.657,2.89,7.047,3.239,10.142,0.848c8.039-6.304,15.349-13.534,21.74-21.494c2.48-3.079,2.13-7.559-0.803-10.213    c-9.353-8.47-12.388-21.946-7.529-33.524c4.568-10.899,15.612-18.217,27.491-18.217l1.662,0.043    c3.853,0.313,7.398-2.655,7.865-6.588C269.044,139.917,269.058,129.639,267.92,119.461z M134.595,179.491    c-24.718,0-44.824-20.106-44.824-44.824c0-24.717,20.106-44.824,44.824-44.824c24.717,0,44.823,20.107,44.823,44.824    C179.418,159.385,159.312,179.491,134.595,179.491z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Tracy Debugger Settings';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'module/edit?name=TracyDebugger" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 456.828 456.828" style="enable-background:new 0 0 456.828 456.828;" xml:space="preserve">
                            <g>
                                <path d="M451.383,247.54c-3.606-3.617-7.898-5.427-12.847-5.427h-63.953v-83.939l49.396-49.394    c3.614-3.615,5.428-7.898,5.428-12.85c0-4.947-1.813-9.229-5.428-12.847c-3.614-3.616-7.898-5.424-12.847-5.424    s-9.233,1.809-12.847,5.424l-49.396,49.394H107.923L58.529,83.083c-3.617-3.616-7.898-5.424-12.847-5.424    c-4.952,0-9.233,1.809-12.85,5.424c-3.617,3.617-5.424,7.9-5.424,12.847c0,4.952,1.807,9.235,5.424,12.85l49.394,49.394v83.939    H18.273c-4.949,0-9.231,1.81-12.847,5.427C1.809,251.154,0,255.442,0,260.387c0,4.949,1.809,9.237,5.426,12.848    c3.616,3.617,7.898,5.431,12.847,5.431h63.953c0,30.447,5.522,56.53,16.56,78.224l-57.67,64.809    c-3.237,3.81-4.712,8.234-4.425,13.275c0.284,5.037,2.235,9.273,5.852,12.703c3.617,3.045,7.707,4.571,12.275,4.571    c5.33,0,9.897-1.991,13.706-5.995l52.246-59.102l4.285,4.004c2.664,2.479,6.801,5.564,12.419,9.274    c5.617,3.71,11.897,7.423,18.842,11.143c6.95,3.71,15.23,6.852,24.84,9.418c9.614,2.573,19.273,3.86,28.98,3.86V169.034h36.547    V424.85c9.134,0,18.363-1.239,27.688-3.717c9.328-2.471,17.135-5.232,23.418-8.278c6.275-3.049,12.47-6.519,18.555-10.42    c6.092-3.901,10.089-6.612,11.991-8.138c1.909-1.526,3.333-2.762,4.284-3.71l56.534,56.243c3.433,3.617,7.707,5.424,12.847,5.424    c5.141,0,9.422-1.807,12.854-5.424c3.607-3.617,5.421-7.902,5.421-12.851s-1.813-9.232-5.421-12.847l-59.388-59.669    c12.755-22.651,19.13-50.251,19.13-82.796h63.953c4.949,0,9.236-1.81,12.847-5.427c3.614-3.614,5.432-7.898,5.432-12.847    C456.828,255.445,455.011,251.158,451.383,247.54z" fill="#ee1d62"></path>
                                <path d="M293.081,31.27c-17.795-17.795-39.352-26.696-64.667-26.696c-25.319,0-46.87,8.901-64.668,26.696    c-17.795,17.797-26.691,39.353-26.691,64.667h182.716C319.771,70.627,310.876,49.067,293.081,31.27z" fill="#ee1d62"></path>
                            </g>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>
            </ul>';
        }

        if(in_array('documentationLinks', $panelSections)) {
            $out .= '
            <ul class="pw-info-links" style="'.($withLabels ? '' :'text-align: center; ').'">';
            $panelTitle = 'Github Repository';
            $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                <a href="https://github.com/processwire/processwire" title="'.$panelTitle.'">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 438.549 438.549" style="enable-background:new 0 0 438.549 438.549;" xml:space="preserve">
                        <path d="M409.132,114.573c-19.608-33.596-46.205-60.194-79.798-79.8C295.736,15.166,259.057,5.365,219.271,5.365   c-39.781,0-76.472,9.804-110.063,29.408c-33.596,19.605-60.192,46.204-79.8,79.8C9.803,148.168,0,184.854,0,224.63   c0,47.78,13.94,90.745,41.827,128.906c27.884,38.164,63.906,64.572,108.063,79.227c5.14,0.954,8.945,0.283,11.419-1.996   c2.475-2.282,3.711-5.14,3.711-8.562c0-0.571-0.049-5.708-0.144-15.417c-0.098-9.709-0.144-18.179-0.144-25.406l-6.567,1.136   c-4.187,0.767-9.469,1.092-15.846,1c-6.374-0.089-12.991-0.757-19.842-1.999c-6.854-1.231-13.229-4.086-19.13-8.559   c-5.898-4.473-10.085-10.328-12.56-17.556l-2.855-6.57c-1.903-4.374-4.899-9.233-8.992-14.559   c-4.093-5.331-8.232-8.945-12.419-10.848l-1.999-1.431c-1.332-0.951-2.568-2.098-3.711-3.429c-1.142-1.331-1.997-2.663-2.568-3.997   c-0.572-1.335-0.098-2.43,1.427-3.289c1.525-0.859,4.281-1.276,8.28-1.276l5.708,0.853c3.807,0.763,8.516,3.042,14.133,6.851   c5.614,3.806,10.229,8.754,13.846,14.842c4.38,7.806,9.657,13.754,15.846,17.847c6.184,4.093,12.419,6.136,18.699,6.136   c6.28,0,11.704-0.476,16.274-1.423c4.565-0.952,8.848-2.383,12.847-4.285c1.713-12.758,6.377-22.559,13.988-29.41   c-10.848-1.14-20.601-2.857-29.264-5.14c-8.658-2.286-17.605-5.996-26.835-11.14c-9.235-5.137-16.896-11.516-22.985-19.126   c-6.09-7.614-11.088-17.61-14.987-29.979c-3.901-12.374-5.852-26.648-5.852-42.826c0-23.035,7.52-42.637,22.557-58.817   c-7.044-17.318-6.379-36.732,1.997-58.24c5.52-1.715,13.706-0.428,24.554,3.853c10.85,4.283,18.794,7.952,23.84,10.994   c5.046,3.041,9.089,5.618,12.135,7.708c17.705-4.947,35.976-7.421,54.818-7.421s37.117,2.474,54.823,7.421l10.849-6.849   c7.419-4.57,16.18-8.758,26.262-12.565c10.088-3.805,17.802-4.853,23.134-3.138c8.562,21.509,9.325,40.922,2.279,58.24   c15.036,16.18,22.559,35.787,22.559,58.817c0,16.178-1.958,30.497-5.853,42.966c-3.9,12.471-8.941,22.457-15.125,29.979   c-6.191,7.521-13.901,13.85-23.131,18.986c-9.232,5.14-18.182,8.85-26.84,11.136c-8.662,2.286-18.415,4.004-29.263,5.146   c9.894,8.562,14.842,22.077,14.842,40.539v60.237c0,3.422,1.19,6.279,3.572,8.562c2.379,2.279,6.136,2.95,11.276,1.995   c44.163-14.653,80.185-41.062,108.068-79.226c27.88-38.161,41.825-81.126,41.825-128.906   C438.536,184.851,428.728,148.168,409.132,114.573z" fill="#ee1d62"/>
                    </svg>'.
                    ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Support Forum';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a href="https://www.processwire.com/talk/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 317.452 317.452" style="enable-background:new 0 0 317.452 317.452;" xml:space="preserve" width="16px" height="16px">
                            <path d="M158.726,0C71.064,0,0,71.064,0,158.726s71.064,158.726,158.726,158.726s158.726-71.064,158.726-158.726     S246.388,0,158.726,0z M226.751,158.726c0,37.573-30.453,68.025-68.025,68.025s-68.025-30.453-68.025-68.025     s30.453-68.025,68.025-68.025S226.751,121.153,226.751,158.726z M158.726,22.675c29.364,0,56.212,9.728,78.433,25.555     l-32.743,32.743c-13.424-8.05-28.888-12.948-45.69-12.948s-32.267,4.898-45.69,12.948L80.293,48.23     C102.514,32.403,129.362,22.675,158.726,22.675z M22.675,158.726c0-29.364,9.728-56.212,25.555-78.433l32.72,32.72     c-8.027,13.446-12.925,28.911-12.925,45.713s4.898,32.267,12.948,45.69l-32.72,32.72     C32.403,214.938,22.675,188.09,22.675,158.726z M158.726,294.777c-29.364,0-56.212-9.728-78.433-25.555l32.72-32.72     c13.446,8.027,28.911,12.925,45.713,12.925s32.267-4.898,45.69-12.925l32.72,32.72     C214.938,285.049,188.09,294.777,158.726,294.777z M269.222,237.159l-32.72-32.743c8.027-13.424,12.925-28.888,12.925-45.69     s-4.898-32.267-12.925-45.69l32.72-32.72c15.827,22.199,25.555,49.046,25.555,78.411S285.049,214.938,269.222,237.159z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'Documentation';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a href="https://www.processwire.com/docs/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 459.319 459.319" style="enable-background:new 0 0 459.319 459.319;" xml:space="preserve">
                            <path d="M94.924,366.674h312.874c0.958,0,1.886-0.136,2.778-0.349c0.071,0,0.13,0.012,0.201,0.012   c6.679,0,12.105-5.42,12.105-12.104V12.105C422.883,5.423,417.456,0,410.777,0h-2.955H114.284H94.941   c-32.22,0-58.428,26.214-58.428,58.425c0,0.432,0.085,0.842,0.127,1.259c-0.042,29.755-0.411,303.166-0.042,339.109   c-0.023,0.703-0.109,1.389-0.109,2.099c0,30.973,24.252,56.329,54.757,58.245c0.612,0.094,1.212,0.183,1.847,0.183h317.683   c6.679,0,12.105-5.42,12.105-12.105v-45.565c0-6.68-5.427-12.105-12.105-12.105s-12.105,5.426-12.105,12.105v33.461H94.924   c-18.395,0-33.411-14.605-34.149-32.817c0.018-0.325,0.077-0.632,0.071-0.963c-0.012-0.532-0.03-1.359-0.042-2.459   C61.862,380.948,76.739,366.674,94.924,366.674z M103.178,58.425c0-6.682,5.423-12.105,12.105-12.105s12.105,5.423,12.105,12.105   V304.31c0,6.679-5.423,12.105-12.105,12.105s-12.105-5.427-12.105-12.105V58.425z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'API Reference';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a href="https://processwire.com/api/ref/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 502.664 502.664" style="enable-background:new 0 0 502.664 502.664;" xml:space="preserve" width="16px" height="16px">
                            <g>
                                <path d="M153.821,358.226L0,274.337v-46.463l153.821-83.414v54.574L46.636,250.523l107.185,53.431    C153.821,303.954,153.821,358.226,153.821,358.226z" fill="#ee1d62"/>
                                <path d="M180.094,387.584L282.103,115.08h32.227L212.084,387.584H180.094z" fill="#ee1d62"/>
                                <path d="M348.843,358.226v-54.272l107.164-52.999l-107.164-52.59v-53.927l153.821,83.522v46.183    L348.843,358.226z" fill="#ee1d62"/>
                            </g>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $panelTitle = 'API Cheatsheet';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a href="http://cheatsheet.processwire.com/" title="'.$panelTitle.'">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 303.969 303.969" style="enable-background:new 0 0 303.969 303.969;" xml:space="preserve" width="16px" height="16px">
                            <path d="M201.604,0H57.038c-8.313,0-15.054,6.74-15.054,15.053v273.862c0,8.313,6.74,15.053,15.054,15.053h189.893  c8.314,0,15.054-6.74,15.054-15.053V60.381L201.604,0z M127.617,169.085c2.992,2.483,3.404,6.921,0.92,9.914  c-1.392,1.676-3.398,2.541-5.418,2.541c-1.588,0-3.18-0.53-4.494-1.621l-27.129-22.518c-1.613-1.34-2.545-3.322-2.545-5.418  c0-2.093,0.932-4.078,2.545-5.416l27.129-22.517c2.991-2.483,7.428-2.07,9.912,0.921c2.484,2.991,2.072,7.431-0.92,9.913  l-20.603,17.099L127.617,169.085z M171.678,109.281l-25.861,89.324c-0.895,3.081-3.705,5.082-6.76,5.082  c-0.648,0-1.307-0.09-1.961-0.279c-3.734-1.081-5.885-4.982-4.803-8.72l25.859-89.323c1.082-3.734,4.984-5.884,8.721-4.806  C170.607,101.643,172.758,105.545,171.678,109.281z M212.472,157.401l-27.127,22.518c-1.316,1.09-2.91,1.621-4.493,1.621  c-2.019,0-4.027-0.865-5.42-2.541c-2.483-2.993-2.072-7.431,0.92-9.914l20.602-17.102l-20.602-17.099  c-2.992-2.482-3.403-6.922-0.92-9.913c2.483-2.991,6.922-3.404,9.913-0.921l27.127,22.517c1.614,1.338,2.546,3.323,2.546,5.416  C215.018,154.079,214.086,156.061,212.472,157.401z M195.97,69.871c-2.65,0-4.798-2.146-4.798-4.797V19.006l50.881,50.865H195.97z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$panelTitle.'</a>' : '</a>&nbsp;') .
                '</li>
            </ul>';
        }

        $out .= \TracyDebugger::generatedTimeSize('processwireInfo', \Tracy\Debugger::timer('processwireInfo'), strlen($out));
        $out .= '</div>';

        return parent::loadResources() . $out;
    }


    private function addRoot($value) {
        return wire('config')->paths->root . $value;
    }

}
