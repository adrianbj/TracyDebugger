<?php

use Tracy\Dumper;

class ProcesswireInfoPanel extends BasePanel {

    protected $icon;
    protected $apiBaseUrl;
    protected $newTab;

    public function __construct() {
        if(wire('modules')->isInstalled('ProcessWireAPI')) {
            $ApiModuleId = wire('modules')->getModuleID("ProcessWireAPI");
            $this->apiBaseUrl = wire('pages')->get("process=$ApiModuleId")->url.'methods/';
        }
        else {
            $this->apiBaseUrl = 'https://processwire.com/api/ref/';
        }
        $this->newTab = \TracyDebugger::getDataValue('pWInfoPanelLinksNewTab') ? 'target="_blank"' : '';
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

        $out = '<script>' . file_get_contents($this->wire("config")->paths->TracyDebugger . 'scripts/js-loader.js') . '</script>';

        $out .= '
        <script>
            function closePanel() {
                localStorage.setItem("remove-tracy-debug-panel-ProcesswireInfoPanel", 1);
            }
        </script>
        ';

        if(in_array('gotoId', $panelSections)) {
            $out .= '
            <script>

                function tracyClearGoToPageID(matchStatus) {
                    document.getElementById("idGoToView").href = "javascript:void(0)";
                    document.getElementById("idGoToEdit").href = "javascript:void(0)";
                    document.getElementById("pageDetails").innerHTML = matchStatus;
                }

                document.getElementById(\'pageId\').addEventListener(\'keyup\', function() {
                    tracyClearGoToPageID("");
                    if(this.value) {
                        tracyClearGoToPageID("<i class=\'fa fa-spinner fa-spin\'></i>");
                        var pid = this.value;
                        if(this.t) clearTimeout(this.t);
                        this.t = setTimeout(function() {
                            var xmlhttp;
                            xmlhttp = new XMLHttpRequest();
                            xmlhttp.onreadystatechange = function() {
                                if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                                    if(xmlhttp.status == 200 && xmlhttp.responseText !== "[]") {
                                        var pageDetails = JSON.parse(xmlhttp.responseText);
                                        document.getElementById("pageDetails").innerHTML = "<span style=\'font-weight:bold\'>" + pageDetails.title + "</span>&nbsp;&nbsp;<a href=\''.$this->wire('config')->urls->admin.'setup/template/edit?id="  + pageDetails.template_id + "\' style=\'color:#888\'>" + pageDetails.template_name + "</a>";
                                        document.getElementById("idGoToEdit").href = "'.$this->wire('config')->urls->admin.'page/edit/?id=" + pageDetails.id;
                                        document.getElementById("idGoToView").href = pageDetails.url;
                                    }
                                    else {
                                        tracyClearGoToPageID("No match");
                                    }
                                    xmlhttp.getAllResponseHeaders();
                                }
                            }

                            xmlhttp.open("POST", "./", true);
                            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                            xmlhttp.send("goToPage="+pid);

                        }
                        , 500);
                    }
                });
            </script>
            ';
        }

        if(in_array('processWireWebsiteSearch', $panelSections)) {
            $out .= '
            <script>
                function searchPw(form) {
                    if(form.section.value == "github") {
                        window.open("https://github.com/processwire/processwire/search?utf8=✓&q="+form.pwquery.value);
                    }
                    else if(form.section.value == "modules") {
                        window.open("https://www.google.com/search?q=site:modules.processwire.com "+form.pwquery.value);
                    }
                    else {
                        window.open("https://www.google.com/search?q=site:processwire.com"+form.section.value+" "+form.pwquery.value);
                    }
                    return false;
                }
            </script>
            ';
        }

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


        // Config Data
        if(in_array('configData', $panelSections)) {
            $configData = $this->sectionHeader(array('Key', 'Value'));
            $config = $this->wire('config')->getArray();
            ksort($config);
            foreach($config as $key => $value) {
                if(is_object($value)) {
                    $outValue = method_exists($value,'getIterator') ? $value->getIterator() : $value;
                    $value = (array)$outValue;
                    ksort($value);
                    if($key == 'paths') $value = array_map(array($this, 'addRoot'), $value);
                }
                $value = \Tracy\Dumper::toHtml($value, array(Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => true));
                $configData .= "<tr><td>".$this->wire('sanitizer')->entities($key)."</td><td>" . $value . "</td></tr>";
            }
            $configData .= $sectionEnd;
        }


        // Versions Info
        if(in_array('versionsList', $panelSections)) {
            $versionsList = '
            <script>
                tracyJSLoader.load("'.$this->wire('config')->urls->TracyDebugger.'scripts/clipboardjs/clipboard.min.js", function() {
                    tracyJSLoader.load("'.$this->wire('config')->urls->TracyDebugger.'scripts/clipboardjs/tooltips.js", function() {
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
            $githubVersionsList = '<details><summary><strong>Server Details</strong></summary>' . $serverInfo . '</details><details><summary><strong>Server Settings</strong></summary> ' . $serverSettings . '</details><details><summary><strong>Module Details</strong></summary> ' . $moduleInfo . '</details>';
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
        $out .= '
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
            $linkTitle = 'ProcessWire Admin';
            $out .= '
            <ul class="pw-info-links">
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'" '.$this->newTab.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                        ' . $this->icon . ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>';

                if($this->wire('user')->isLoggedIn()) {
                    $linkTitle = 'Logout ('.$this->wire('user')->name.')';
                    $out .= '
                    <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                        <a onclick="closePanel()" href="'.\TracyDebugger::inputUrl(true) . (strpos(\TracyDebugger::inputUrl(true), '?') !== false ? '&' : '?') . 'tracyLogout=1"'.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
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
                        ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                    '</li>';
                }
                else {
                    $linkTitle = 'Login';
                    $out .= '
                    <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                        <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin . '?tracyLogin=1"'.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
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
                            ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                    '</li>';
                }

                $linkTitle = 'Clear Session & Cookies';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.\TracyDebugger::inputUrl(true) . (strpos(\TracyDebugger::inputUrl(true), '?') !== false ? '&' : '?') . 'tracyClearSession=1"'.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve">
                            <path fill="#EE2363" d="M15.747201,6.8416004h-1.9904003C13.2032013,3.5680001,10.3552008,1.072,6.9280005,1.072
                            C3.1040001,1.072,0.0032,4.1760001,0.0032,8s3.1008,6.9280005,6.9248004,6.9280005
                            c1.7440004,0,3.3375998-0.6464005,4.5535994-1.7087994l-1.4335995-1.8207998
                            c-0.8224001,0.7551994-1.9167995,1.2192001-3.1167998,1.2192001c-2.5472002,0-4.6176004-2.0703993-4.6176004-4.6176004
                            s2.0704-4.6176004,4.6176004-4.6176004c2.1472001,0,3.9487996,1.4720001,4.4640002,3.4592004H9.3792009
                            c-0.2528,0-0.3296003,0.1632004-0.1695995,0.3583999l3.0656004,3.7728004c0.1599998,0.1984005,0.4191999,0.1984005,0.5824003,0
                            l3.0655985-3.7728009C16.0768013,7.0048003,16,6.8416004,15.747201,6.8416004z"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>';

                $linkTitle = 'Modules Refresh';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.\TracyDebugger::inputUrl(true) . (strpos(\TracyDebugger::inputUrl(true), '?') !== false ? '&' : '?') . 'tracyModulesRefresh=1"'.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px" viewBox="888 888 16 16" enable-background="new 888 888 16 16" xml:space="preserve">
                            <path fill="#EE1D62" d="M903.7,897.7C903.7,897.7,903.7,897.7,903.7,897.7c-0.5,1.9-1.4,3.4-2.8,4.6s-3.1,1.7-5,1.7
                                c-1,0-2-0.2-2.9-0.6c-0.9-0.4-1.8-0.9-2.5-1.6l-1.3,1.3c-0.1,0.1-0.3,0.2-0.5,0.2c-0.2,0-0.3-0.1-0.5-0.2c-0.1-0.1-0.2-0.3-0.2-0.5
                                V898c0-0.2,0.1-0.3,0.2-0.5c0.1-0.1,0.3-0.2,0.5-0.2h4.7c0.2,0,0.3,0.1,0.5,0.2c0.1,0.1,0.2,0.3,0.2,0.5s-0.1,0.3-0.2,0.5l-1.4,1.4
                                c0.5,0.5,1.1,0.8,1.7,1.1s1.3,0.4,1.9,0.4c0.9,0,1.8-0.2,2.6-0.7c0.8-0.5,1.5-1.1,1.9-1.9c0.1-0.1,0.3-0.5,0.6-1.2
                                c0.1-0.2,0.2-0.2,0.3-0.2h2c0.1,0,0.2,0,0.2,0.1S903.7,897.6,903.7,897.7z M904,889.3v4.7c0,0.2-0.1,0.3-0.2,0.5
                                c-0.1,0.1-0.3,0.2-0.5,0.2h-4.7c-0.2,0-0.3-0.1-0.5-0.2c-0.1-0.1-0.2-0.3-0.2-0.5s0.1-0.3,0.2-0.5l1.4-1.4c-1-1-2.2-1.4-3.6-1.4
                                c-0.9,0-1.8,0.2-2.6,0.7c-0.8,0.5-1.5,1.1-1.9,1.9c-0.1,0.1-0.3,0.5-0.6,1.2c-0.1,0.2-0.2,0.2-0.3,0.2h-2.1c-0.1,0-0.2,0-0.2-0.1
                                s-0.1-0.1-0.1-0.2v-0.1c0.5-1.9,1.4-3.4,2.8-4.5s3.1-1.7,5-1.7c1,0,2,0.2,3,0.6c1,0.4,1.8,0.9,2.6,1.6l1.4-1.3
                                c0.1-0.1,0.3-0.2,0.5-0.2c0.2,0,0.3,0.1,0.5,0.2C903.9,889,904,889.2,904,889.3z"/>
                            </svg>'.
                        ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>';

                $linkTitle = 'Tracy Debugger Settings';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'module/edit?name=TracyDebugger" '.$this->newTab.' '.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 456.828 456.828" style="enable-background:new 0 0 456.828 456.828;" xml:space="preserve">
                            <g>
                                <path d="M451.383,247.54c-3.606-3.617-7.898-5.427-12.847-5.427h-63.953v-83.939l49.396-49.394    c3.614-3.615,5.428-7.898,5.428-12.85c0-4.947-1.813-9.229-5.428-12.847c-3.614-3.616-7.898-5.424-12.847-5.424    s-9.233,1.809-12.847,5.424l-49.396,49.394H107.923L58.529,83.083c-3.617-3.616-7.898-5.424-12.847-5.424    c-4.952,0-9.233,1.809-12.85,5.424c-3.617,3.617-5.424,7.9-5.424,12.847c0,4.952,1.807,9.235,5.424,12.85l49.394,49.394v83.939    H18.273c-4.949,0-9.231,1.81-12.847,5.427C1.809,251.154,0,255.442,0,260.387c0,4.949,1.809,9.237,5.426,12.848    c3.616,3.617,7.898,5.431,12.847,5.431h63.953c0,30.447,5.522,56.53,16.56,78.224l-57.67,64.809    c-3.237,3.81-4.712,8.234-4.425,13.275c0.284,5.037,2.235,9.273,5.852,12.703c3.617,3.045,7.707,4.571,12.275,4.571    c5.33,0,9.897-1.991,13.706-5.995l52.246-59.102l4.285,4.004c2.664,2.479,6.801,5.564,12.419,9.274    c5.617,3.71,11.897,7.423,18.842,11.143c6.95,3.71,15.23,6.852,24.84,9.418c9.614,2.573,19.273,3.86,28.98,3.86V169.034h36.547    V424.85c9.134,0,18.363-1.239,27.688-3.717c9.328-2.471,17.135-5.232,23.418-8.278c6.275-3.049,12.47-6.519,18.555-10.42    c6.092-3.901,10.089-6.612,11.991-8.138c1.909-1.526,3.333-2.762,4.284-3.71l56.534,56.243c3.433,3.617,7.707,5.424,12.847,5.424    c5.141,0,9.422-1.807,12.854-5.424c3.607-3.617,5.421-7.902,5.421-12.851s-1.813-9.232-5.421-12.847l-59.388-59.669    c12.755-22.651,19.13-50.251,19.13-82.796h63.953c4.949,0,9.236-1.81,12.847-5.427c3.614-3.614,5.432-7.898,5.432-12.847    C456.828,255.445,455.011,251.158,451.383,247.54z" fill="#ee1d62"></path>
                                <path d="M293.081,31.27c-17.795-17.795-39.352-26.696-64.667-26.696c-25.319,0-46.87,8.901-64.668,26.696    c-17.795,17.797-26.691,39.353-26.691,64.667h182.716C319.771,70.627,310.876,49.067,293.081,31.27z" fill="#ee1d62"></path>
                            </g>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>
            </ul>';
        }

        if(count(\TracyDebugger::getDataValue('customPWInfoPanelLinks'))) {

            // make sure Font Awesome is loaded
            $out .= '
            <script>
                function loadFAIfNotAlreadyLoaded() {
                    if(!document.getElementById("fontAwesome")) {
                        var link = document.createElement("link");
                        link.rel = "stylesheet";
                        link.href = "'.$this->wire('config')->urls->root . 'wire/templates-admin/styles/font-awesome/css/font-awesome.min.css";
                        document.getElementsByTagName("head")[0].appendChild(link);
                    }
                }
                loadFAIfNotAlreadyLoaded();
            </script>
            ';

            $out .= '<ul class="pw-info-links">';
            foreach(\TracyDebugger::getDataValue('customPWInfoPanelLinks') as $path) {
                if(is_integer($path)) {
                    $cp = $this->wire('pages')->get($path);
                }
                elseif(method_exists($this->wire('pages'), 'getByPath')) {
                    $cp = $this->wire('pages')->getByPath($path, array('useHistory' => true));
                }
                // fallback for PW < 3.0.6 when getByPath method did not exist
                else {
                    $cp = $this->wire('pages')->get($path);
                }
                if(!$cp->id || $cp->parent->id === $this->wire('config')->trashPageID) continue;

                $icon = $cp->getIcon();
                if(!$icon) {
                    if($cp->path == $this->wire('config')->urls->admin . 'setup/') {
                        $icon = 'wrench';
                    }
                    elseif($cp->path == $this->wire('config')->urls->admin . 'module/') {
                        $icon = 'plug';
                    }
                    elseif($cp->path == $this->wire('config')->urls->admin . 'access/') {
                        $icon = 'unlock';
                    }
                    elseif($cp->path == $this->wire('config')->urls->admin . 'profile/') {
                        $icon = 'user';
                    }
                    else {
                        $icon = 'file-text';
                    }
                }

                $out .=
                '<li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" '.$this->newTab.' href="'.$cp->url.'"'. (!$withLabels ? ' title="'.$cp->title.'"' : '') . '>
                        <span style="color:#ee1d62; font-size: 15px; margin-right: 2px" class="fa fa-fw fa-'.$icon.'"></span>'
                        . ($withLabels ? '&nbsp;'.$cp->title.'</a>' : '</a>&nbsp;') .
                    '</a>
                </li>';
            }
            $out .= '
            </ul>';
        }


        if(in_array('documentationLinks', $panelSections)) {
            $out .= '
            <ul class="pw-info-links">';
            $linkTitle = 'Github Repository';
            $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                <a onclick="closePanel()" href="https://github.com/processwire/processwire" '.$this->newTab.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 438.549 438.549" style="enable-background:new 0 0 438.549 438.549;" xml:space="preserve">
                        <path d="M409.132,114.573c-19.608-33.596-46.205-60.194-79.798-79.8C295.736,15.166,259.057,5.365,219.271,5.365   c-39.781,0-76.472,9.804-110.063,29.408c-33.596,19.605-60.192,46.204-79.8,79.8C9.803,148.168,0,184.854,0,224.63   c0,47.78,13.94,90.745,41.827,128.906c27.884,38.164,63.906,64.572,108.063,79.227c5.14,0.954,8.945,0.283,11.419-1.996   c2.475-2.282,3.711-5.14,3.711-8.562c0-0.571-0.049-5.708-0.144-15.417c-0.098-9.709-0.144-18.179-0.144-25.406l-6.567,1.136   c-4.187,0.767-9.469,1.092-15.846,1c-6.374-0.089-12.991-0.757-19.842-1.999c-6.854-1.231-13.229-4.086-19.13-8.559   c-5.898-4.473-10.085-10.328-12.56-17.556l-2.855-6.57c-1.903-4.374-4.899-9.233-8.992-14.559   c-4.093-5.331-8.232-8.945-12.419-10.848l-1.999-1.431c-1.332-0.951-2.568-2.098-3.711-3.429c-1.142-1.331-1.997-2.663-2.568-3.997   c-0.572-1.335-0.098-2.43,1.427-3.289c1.525-0.859,4.281-1.276,8.28-1.276l5.708,0.853c3.807,0.763,8.516,3.042,14.133,6.851   c5.614,3.806,10.229,8.754,13.846,14.842c4.38,7.806,9.657,13.754,15.846,17.847c6.184,4.093,12.419,6.136,18.699,6.136   c6.28,0,11.704-0.476,16.274-1.423c4.565-0.952,8.848-2.383,12.847-4.285c1.713-12.758,6.377-22.559,13.988-29.41   c-10.848-1.14-20.601-2.857-29.264-5.14c-8.658-2.286-17.605-5.996-26.835-11.14c-9.235-5.137-16.896-11.516-22.985-19.126   c-6.09-7.614-11.088-17.61-14.987-29.979c-3.901-12.374-5.852-26.648-5.852-42.826c0-23.035,7.52-42.637,22.557-58.817   c-7.044-17.318-6.379-36.732,1.997-58.24c5.52-1.715,13.706-0.428,24.554,3.853c10.85,4.283,18.794,7.952,23.84,10.994   c5.046,3.041,9.089,5.618,12.135,7.708c17.705-4.947,35.976-7.421,54.818-7.421s37.117,2.474,54.823,7.421l10.849-6.849   c7.419-4.57,16.18-8.758,26.262-12.565c10.088-3.805,17.802-4.853,23.134-3.138c8.562,21.509,9.325,40.922,2.279,58.24   c15.036,16.18,22.559,35.787,22.559,58.817c0,16.178-1.958,30.497-5.853,42.966c-3.9,12.471-8.941,22.457-15.125,29.979   c-6.191,7.521-13.901,13.85-23.131,18.986c-9.232,5.14-18.182,8.85-26.84,11.136c-8.662,2.286-18.415,4.004-29.263,5.146   c9.894,8.562,14.842,22.077,14.842,40.539v60.237c0,3.422,1.19,6.279,3.572,8.562c2.379,2.279,6.136,2.95,11.276,1.995   c44.163-14.653,80.185-41.062,108.068-79.226c27.88-38.161,41.825-81.126,41.825-128.906   C438.536,184.851,428.728,148.168,409.132,114.573z" fill="#ee1d62"/>
                    </svg>'.
                    ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $linkTitle = 'Support Forum';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="https://www.processwire.com/talk/" '.$this->newTab.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 317.452 317.452" style="enable-background:new 0 0 317.452 317.452;" xml:space="preserve" width="16px" height="16px">
                            <path d="M158.726,0C71.064,0,0,71.064,0,158.726s71.064,158.726,158.726,158.726s158.726-71.064,158.726-158.726     S246.388,0,158.726,0z M226.751,158.726c0,37.573-30.453,68.025-68.025,68.025s-68.025-30.453-68.025-68.025     s30.453-68.025,68.025-68.025S226.751,121.153,226.751,158.726z M158.726,22.675c29.364,0,56.212,9.728,78.433,25.555     l-32.743,32.743c-13.424-8.05-28.888-12.948-45.69-12.948s-32.267,4.898-45.69,12.948L80.293,48.23     C102.514,32.403,129.362,22.675,158.726,22.675z M22.675,158.726c0-29.364,9.728-56.212,25.555-78.433l32.72,32.72     c-8.027,13.446-12.925,28.911-12.925,45.713s4.898,32.267,12.948,45.69l-32.72,32.72     C32.403,214.938,22.675,188.09,22.675,158.726z M158.726,294.777c-29.364,0-56.212-9.728-78.433-25.555l32.72-32.72     c13.446,8.027,28.911,12.925,45.713,12.925s32.267-4.898,45.69-12.925l32.72,32.72     C214.938,285.049,188.09,294.777,158.726,294.777z M269.222,237.159l-32.72-32.743c8.027-13.424,12.925-28.888,12.925-45.69     s-4.898-32.267-12.925-45.69l32.72-32.72c15.827,22.199,25.555,49.046,25.555,78.411S285.049,214.938,269.222,237.159z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $linkTitle = 'Documentation';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="https://www.processwire.com/docs/" '.$this->newTab.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 459.319 459.319" style="enable-background:new 0 0 459.319 459.319;" xml:space="preserve">
                            <path d="M94.924,366.674h312.874c0.958,0,1.886-0.136,2.778-0.349c0.071,0,0.13,0.012,0.201,0.012   c6.679,0,12.105-5.42,12.105-12.104V12.105C422.883,5.423,417.456,0,410.777,0h-2.955H114.284H94.941   c-32.22,0-58.428,26.214-58.428,58.425c0,0.432,0.085,0.842,0.127,1.259c-0.042,29.755-0.411,303.166-0.042,339.109   c-0.023,0.703-0.109,1.389-0.109,2.099c0,30.973,24.252,56.329,54.757,58.245c0.612,0.094,1.212,0.183,1.847,0.183h317.683   c6.679,0,12.105-5.42,12.105-12.105v-45.565c0-6.68-5.427-12.105-12.105-12.105s-12.105,5.426-12.105,12.105v33.461H94.924   c-18.395,0-33.411-14.605-34.149-32.817c0.018-0.325,0.077-0.632,0.071-0.963c-0.012-0.532-0.03-1.359-0.042-2.459   C61.862,380.948,76.739,366.674,94.924,366.674z M103.178,58.425c0-6.682,5.423-12.105,12.105-12.105s12.105,5.423,12.105,12.105   V304.31c0,6.679-5.423,12.105-12.105,12.105s-12.105-5.427-12.105-12.105V58.425z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $linkTitle = 'API Reference';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="https://processwire.com/api/ref/" '.$this->newTab.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 502.664 502.664" style="enable-background:new 0 0 502.664 502.664;" xml:space="preserve" width="16px" height="16px">
                            <g>
                                <path d="M153.821,358.226L0,274.337v-46.463l153.821-83.414v54.574L46.636,250.523l107.185,53.431    C153.821,303.954,153.821,358.226,153.821,358.226z" fill="#ee1d62"/>
                                <path d="M180.094,387.584L282.103,115.08h32.227L212.084,387.584H180.094z" fill="#ee1d62"/>
                                <path d="M348.843,358.226v-54.272l107.164-52.999l-107.164-52.59v-53.927l153.821,83.522v46.183    L348.843,358.226z" fill="#ee1d62"/>
                            </g>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>';
                $linkTitle = 'API Cheatsheet';
                $out .= '
                <li ' . ($withLabels ? ' class="with-labels"' : '') . '>
                    <a onclick="closePanel()" href="http://cheatsheet.processwire.com/" '.$this->newTab.(!$withLabels ? ' title="'.$linkTitle.'"' : '').'>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 303.969 303.969" style="enable-background:new 0 0 303.969 303.969;" xml:space="preserve" width="16px" height="16px">
                            <path d="M201.604,0H57.038c-8.313,0-15.054,6.74-15.054,15.053v273.862c0,8.313,6.74,15.053,15.054,15.053h189.893  c8.314,0,15.054-6.74,15.054-15.053V60.381L201.604,0z M127.617,169.085c2.992,2.483,3.404,6.921,0.92,9.914  c-1.392,1.676-3.398,2.541-5.418,2.541c-1.588,0-3.18-0.53-4.494-1.621l-27.129-22.518c-1.613-1.34-2.545-3.322-2.545-5.418  c0-2.093,0.932-4.078,2.545-5.416l27.129-22.517c2.991-2.483,7.428-2.07,9.912,0.921c2.484,2.991,2.072,7.431-0.92,9.913  l-20.603,17.099L127.617,169.085z M171.678,109.281l-25.861,89.324c-0.895,3.081-3.705,5.082-6.76,5.082  c-0.648,0-1.307-0.09-1.961-0.279c-3.734-1.081-5.885-4.982-4.803-8.72l25.859-89.323c1.082-3.734,4.984-5.884,8.721-4.806  C170.607,101.643,172.758,105.545,171.678,109.281z M212.472,157.401l-27.127,22.518c-1.316,1.09-2.91,1.621-4.493,1.621  c-2.019,0-4.027-0.865-5.42-2.541c-2.483-2.993-2.072-7.431,0.92-9.914l20.602-17.102l-20.602-17.099  c-2.992-2.482-3.403-6.922-0.92-9.913c2.483-2.991,6.922-3.404,9.913-0.921l27.127,22.517c1.614,1.338,2.546,3.323,2.546,5.416  C215.018,154.079,214.086,156.061,212.472,157.401z M195.97,69.871c-2.65,0-4.798-2.146-4.798-4.797V19.006l50.881,50.865H195.97z" fill="#ee1d62"/>
                        </svg>'.
                        ($withLabels ? '&nbsp;'.$linkTitle.'</a>' : '</a>&nbsp;') .
                '</li>
            </ul>';

            if(in_array('gotoId', $panelSections)) {
                $out .= '
                <form onsubmit="return false;" style="border-top: 1px solid #CCCCCC; margin:10px 0 0 0 ; padding: 10px 0 0 0;">
                    <input id="pageId" name="pageId" placeholder="Goto Page ID" type="text" autocomplete="off" />
                    <a onclick="closePanel()" href="javascript:void(0)" class="tracyLinkBtn" id="idGoToView" />View</a>
                    <a onclick="closePanel()" href="javascript:void(0)" class="tracyLinkBtn" id="idGoToEdit" />Edit</a>
                    <div id="pageDetails" style="height:15px; margin-top:6px"></div>
                </form>
                ';
            }

            if(in_array('processWireWebsiteSearch', $panelSections)) {
                $out .= '
                <form onsubmit="searchPw(this); return false;" style="border-top: 1px solid #CCCCCC; margin:10px 0 0 0 ; padding: 10px 0 0 0;">
                    <input id="pwquery" name="pwquery" placeholder="Search ProcessWire" type="text" style="width:205px !important" />
                    <input type="submit" name="pwsearch" value="Search" />
                    <div style="padding: 12px 0 0 0; font-size: 13px">
                        <label><input type="radio" name="section" value="/api/ref/"> API</label>&nbsp;&nbsp;
                        <label><input type="radio" name="section" value="github"> Github</label>&nbsp;&nbsp;
                        <label><input type="radio" name="section" value="/talk/"> Forums</label>&nbsp;&nbsp;
                        <label><input type="radio" name="section" value="/blog/"> Blog</label>&nbsp;&nbsp;
                        <label><input type="radio" name="section" value="modules"> Modules</label>&nbsp;&nbsp;
                        <label><input type="radio" name="section" value="/" checked> All PW</label>
                    </div>
                </form>
                ';
            }

        }

        $out .= \TracyDebugger::generatedTimeSize('processwireInfo', \Tracy\Debugger::timer('processwireInfo'), strlen($out));
        $out .= '</div>';

        return parent::loadResources() . $out;
    }


    private function addRoot($value) {
        return wire('config')->paths->root . $value;
    }

}
