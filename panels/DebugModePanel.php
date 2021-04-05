<?php

use \Tracy\Dumper;

class DebugModePanel extends BasePanel {

    protected static $iconColor;
    protected static $panelIconColor;

    public function getTab() {

        \Tracy\Debugger::timer('debugMode');

        $debugMode = $this->wire('config')->debug;

        $this->iconColor = $debugMode && !\TracyDebugger::$isLocal ? \TracyDebugger::COLOR_ALERT : \TracyDebugger::COLOR_NORMAL;

        $tooltip = $label = 'PW Debug Mode ' . ($debugMode ? 'ON' : 'OFF');

        return "
        <span title='".$tooltip."'>
            <svg xmlns=http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' x='0px' y='0px' width='16px' height='16px' viewBox='0 0 456.828 456.828' style='enable-background:new 0 0 456.828 456.828;' xml:space='preserve'>
                <g>
                    <path d='M451.383,247.54c-3.606-3.617-7.898-5.427-12.847-5.427h-63.953v-83.939l49.396-49.394    c3.614-3.615,5.428-7.898,5.428-12.85c0-4.947-1.813-9.229-5.428-12.847c-3.614-3.616-7.898-5.424-12.847-5.424    s-9.233,1.809-12.847,5.424l-49.396,49.394H107.923L58.529,83.083c-3.617-3.616-7.898-5.424-12.847-5.424    c-4.952,0-9.233,1.809-12.85,5.424c-3.617,3.617-5.424,7.9-5.424,12.847c0,4.952,1.807,9.235,5.424,12.85l49.394,49.394v83.939    H18.273c-4.949,0-9.231,1.81-12.847,5.427C1.809,251.154,0,255.442,0,260.387c0,4.949,1.809,9.237,5.426,12.848    c3.616,3.617,7.898,5.431,12.847,5.431h63.953c0,30.447,5.522,56.53,16.56,78.224l-57.67,64.809    c-3.237,3.81-4.712,8.234-4.425,13.275c0.284,5.037,2.235,9.273,5.852,12.703c3.617,3.045,7.707,4.571,12.275,4.571    c5.33,0,9.897-1.991,13.706-5.995l52.246-59.102l4.285,4.004c2.664,2.479,6.801,5.564,12.419,9.274    c5.617,3.71,11.897,7.423,18.842,11.143c6.95,3.71,15.23,6.852,24.84,9.418c9.614,2.573,19.273,3.86,28.98,3.86V169.034h36.547    V424.85c9.134,0,18.363-1.239,27.688-3.717c9.328-2.471,17.135-5.232,23.418-8.278c6.275-3.049,12.47-6.519,18.555-10.42    c6.092-3.901,10.089-6.612,11.991-8.138c1.909-1.526,3.333-2.762,4.284-3.71l56.534,56.243c3.433,3.617,7.707,5.424,12.847,5.424    c5.141,0,9.422-1.807,12.854-5.424c3.607-3.617,5.421-7.902,5.421-12.851s-1.813-9.232-5.421-12.847l-59.388-59.669    c12.755-22.651,19.13-50.251,19.13-82.796h63.953c4.949,0,9.236-1.81,12.847-5.427c3.614-3.614,5.432-7.898,5.432-12.847    C456.828,255.445,455.011,251.158,451.383,247.54z' fill='".$this->iconColor."'/>
                    <path d='M293.081,31.27c-17.795-17.795-39.352-26.696-64.667-26.696c-25.319,0-46.87,8.901-64.668,26.696    c-17.795,17.797-26.691,39.353-26.691,64.667h182.716C319.771,70.627,310.876,49.067,293.081,31.27z' fill='".$this->iconColor."'/>
                </g>
            </svg>&nbsp;" . (\TracyDebugger::getDataValue('showPanelLabels') ? $label : '') . "
        </span>
        ";
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

        if($this->wire('modules')->isInstalled("ProcessTracyAdminer")) {
            $adminerModuleId = $this->wire('modules')->getModuleID("ProcessTracyAdminer");
            $adminerUrl = $this->wire('pages')->get("process=$adminerModuleId")->url;
            $adminerIcon = '
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="304.4 284.4 11.7 16">
                <path fill="#ffffff" d="M304.4 294.8v2.3c.3 1.3 2.7 2.3 5.8 2.3s5.7-1 5.9-2.3v-2.3c-1 .8-3.1 1.4-6 1.4-2.8 0-4.8-.6-5.7-1.4zM310.7 291.9h-1.2c-1.7-.1-3.1-.3-4-.7-.4-.2-.9-.4-1.1-.6v2.4c.7.8 2.9 1.5 5.8 1.5 3 0 5.1-.7 5.8-1.5v-2.4c-.3.2-.7.5-1.1.6-1.1.4-2.5.6-4.2.7zM310.1 285.6c-3.5 0-5.5 1.1-5.8 2.3v.7c.7.8 2.9 1.5 5.8 1.5s5.1-.7 5.8-1.5v-.6c-.3-1.3-2.3-2.4-5.8-2.4z"/>
            </svg>
            ';
        }

        $PwVersion = $this->wire('config')->version;
        $debugMode = $this->wire('config')->debug;

        if(\TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') &&
            ($this->wire('process') == 'ProcessPageEdit' ||
                $this->wire('process') == 'ProcessUser' ||
                $this->wire('process') == 'ProcessRole' ||
                $this->wire('process') == 'ProcessPermission'
            )
        ) {
            $p = $this->wire('process')->getPage();
        }
        else {
            $p = $this->wire('page');
        }

        $panelSections = \TracyDebugger::getDataValue('debugModePanelSections');

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';

        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = "
        <h1>
        <svg xmlns=http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' x='0px' y='0px' width='16px' height='16px' viewBox='0 0 456.828 456.828' style='enable-background:new 0 0 456.828 456.828;' xml:space='preserve'>
            <g>
                <path d='M451.383,247.54c-3.606-3.617-7.898-5.427-12.847-5.427h-63.953v-83.939l49.396-49.394    c3.614-3.615,5.428-7.898,5.428-12.85c0-4.947-1.813-9.229-5.428-12.847c-3.614-3.616-7.898-5.424-12.847-5.424    s-9.233,1.809-12.847,5.424l-49.396,49.394H107.923L58.529,83.083c-3.617-3.616-7.898-5.424-12.847-5.424    c-4.952,0-9.233,1.809-12.85,5.424c-3.617,3.617-5.424,7.9-5.424,12.847c0,4.952,1.807,9.235,5.424,12.85l49.394,49.394v83.939    H18.273c-4.949,0-9.231,1.81-12.847,5.427C1.809,251.154,0,255.442,0,260.387c0,4.949,1.809,9.237,5.426,12.848    c3.616,3.617,7.898,5.431,12.847,5.431h63.953c0,30.447,5.522,56.53,16.56,78.224l-57.67,64.809    c-3.237,3.81-4.712,8.234-4.425,13.275c0.284,5.037,2.235,9.273,5.852,12.703c3.617,3.045,7.707,4.571,12.275,4.571    c5.33,0,9.897-1.991,13.706-5.995l52.246-59.102l4.285,4.004c2.664,2.479,6.801,5.564,12.419,9.274    c5.617,3.71,11.897,7.423,18.842,11.143c6.95,3.71,15.23,6.852,24.84,9.418c9.614,2.573,19.273,3.86,28.98,3.86V169.034h36.547    V424.85c9.134,0,18.363-1.239,27.688-3.717c9.328-2.471,17.135-5.232,23.418-8.278c6.275-3.049,12.47-6.519,18.555-10.42    c6.092-3.901,10.089-6.612,11.991-8.138c1.909-1.526,3.333-2.762,4.284-3.71l56.534,56.243c3.433,3.617,7.707,5.424,12.847,5.424    c5.141,0,9.422-1.807,12.854-5.424c3.607-3.617,5.421-7.902,5.421-12.851s-1.813-9.232-5.421-12.847l-59.388-59.669    c12.755-22.651,19.13-50.251,19.13-82.796h63.953c4.949,0,9.236-1.81,12.847-5.427c3.614-3.614,5.432-7.898,5.432-12.847    C456.828,255.445,455.011,251.158,451.383,247.54z' fill='".$this->iconColor."'/>
                <path d='M293.081,31.27c-17.795-17.795-39.352-26.696-64.667-26.696c-25.319,0-46.87,8.901-64.668,26.696    c-17.795,17.797-26.691,39.353-26.691,64.667h182.716C319.771,70.627,310.876,49.067,293.081,31.27z' fill='".$this->iconColor."'/>
            </g>
        </svg>
        ProcessWire Debug Mode" . ($isAdditionalBar ? " (".$isAdditionalBar.") " : " ") . ($debugMode ? 'ON' : 'OFF') . "</h1>" . '<span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'DebugModePanel'.($isAdditionalBar ? '-'.$isAdditionalBar : '').'\')">+</a></span></span>' . "
        <div class='tracy-inner'>
            <p>";

        if($debugMode) {
            $out .= '
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 483.537 483.537" style="enable-background:new 0 0 483.537 483.537;" xml:space="preserve" width="16px" height="16px">
                <path d="M479.963,425.047L269.051,29.854c-5.259-9.88-15.565-16.081-26.782-16.081h-0.03     c-11.217,0-21.492,6.171-26.782,16.051L3.603,425.016c-5.046,9.485-4.773,20.854,0.699,29.974     c5.502,9.15,15.413,14.774,26.083,14.774H453.12c10.701,0,20.58-5.594,26.083-14.774     C484.705,445.84,484.979,434.471,479.963,425.047z M242.239,408.965c-16.781,0-30.399-13.619-30.399-30.399     c0-16.78,13.619-30.399,30.399-30.399c16.75,0,30.399,13.619,30.399,30.399C272.638,395.346,259.02,408.965,242.239,408.965z      M272.669,287.854c0,16.811-13.649,30.399-30.399,30.399c-16.781,0-30.399-13.589-30.399-30.399V166.256     c0-16.781,13.619-30.399,30.399-30.399c16.75,0,30.399,13.619,30.399,30.399V287.854z" fill="'.\TracyDebugger::COLOR_WARN.'"/>
            </svg>&nbsp;
            <strong>WARNING</strong></p><p>ProcessWire Debug Mode is ON<br />Make sure it is OFF for live sites.';
        }
        else {
            $out .= '<strong>ProcessWire Debug Mode is OFF</strong></p>';
            if(\TracyDebugger::getDataValue('alwaysShowDebugTools')) $out .= '<p>With debug mode off, it is not possible to access the "Database Queries", "Timers", and "Autoload" sections.';
        }

        $out .= '</p>';


        /**
         * PW Debug Mode Tools panel sections
         */

        if($debugMode || (!$debugMode && \TracyDebugger::getDataValue('alwaysShowDebugTools'))) {

            // Pages Loaded
            if(in_array('pagesLoaded', $panelSections)) {
                $pagesLoaded_oc = 0;
                if($PwVersion >= 2.8) {
                    $pagesLoaded = $this->sectionHeader(array('ID', 'Path', 'Type', 'Loader'));
                    foreach($this->wire('pages')->getCache() as $p) {
                        $pagesLoaded_oc++;
                        $parts = explode('/', trim($p->path, '/'));
                        $name = array_pop($parts);
                        $path = implode('/', $parts) . "/$name/";
                        $path = '/' . ltrim($path, '/');
                        $path = str_replace("/$name/", "/<b>$name</b>/", $path);
                        $pagesLoaded .= "\n<tr>" .
                            "<td>$p->id</td>" .
                            "<td>$path</td>" .
                            "<td>" . wireClassName($p) . "</td>" .
                            "<td>$p->_debug_loader</td>" .
                            "</tr>";
                    }
                }
                else {
                    $pagesLoaded = $this->sectionHeader(array('ID', 'Path', 'Title'));
                    foreach($this->wire('pages')->getCache() as $p) {
                        $pagesLoaded_oc++;
                        $pagesLoaded .= "\n<tr><td>$p->id</td><td>$p->path</td><td>$p->title</td></tr>";
                    }
                }
                $pagesLoaded .= $sectionEnd;
            }


            if(in_array('modulesLoaded', $panelSections)) {
                // Modules Loaded
                $modulesNumLoaded = 0;
                $modulesNumSkipped = 0;
                $modulesLoaded = $this->sectionHeader(array('Class', 'Version', 'Title'));
                foreach($this->wire('modules') as $module) {
                    if($module instanceof ModulePlaceholder) {
                        $modulesNumSkipped++;
                        continue;
                    }
                    $modulesNumLoaded++;
                    $info = $this->wire('modules')->getModuleInfo($module, array('verbose' => false));
                    $modulesLoaded .= "<tr>";
                    $modulesLoaded .= "<td><a href='".$this->wire('config')->urls->admin."module/edit?name=".$info['name']."'>".$info['name']."</a></td>";
                    $modulesLoaded .= "<td>".$info['version']."</td>";
                    $modulesLoaded .= "<td>".$info['title']."</td>";
                    $modulesLoaded .= "</tr>";
                }
                $modulesLoaded .= $sectionEnd;
            }

            if(in_array('hooks', $panelSections)) {
                // Hooks
                $hooksCalled_oc = 0;
                $hooksCalled = $this->sectionHeader(array('When', 'Method::object', 'Visited by', 'Type', 'Priority'));
                if($PwVersion >= 2.8) {
                    $hooks = array_merge($this->wire()->getHooks('*'), $this->wire('hooks')->getAllLocalHooks());
                }
                else {
                    $hooks = array_merge($this->wire()->getHooks('*'), Wire::$allLocalHooks);
                }
                $hooksSorted = array();
                foreach($hooks as $hook) {
                    $whenKey = $hook['options']['before'] ? '0' : '1';
                    $sortKey = $hook['options']['fromClass'] . ":$hook[method]:$whenKey:" . $hook['options']['priority'];
                    $hooksSorted[$sortKey] = $hook;
                    $hooksCalled_oc++;
                }
                ksort($hooksSorted);
                foreach($hooksSorted as $key => $hook) {
                    $suffix = $hook['options']['type'] == 'method' ? '()' : '';
                    $toObject = !empty($hook['toObject']) ? $hook['toObject'] : '';
                    $toMethod = $hook['toMethod'];

                    if($toMethod instanceof \Closure) {
                        $rc = new \ReflectionFunction($toMethod);
                    }
                    else {
                        if(method_exists("\ProcessWire\\$toObject", $toMethod)) {
                            $rc = new \ReflectionMethod("\ProcessWire\\$toObject", $toMethod);
                        }
                        elseif(method_exists($toObject, $toMethod)) {
                            $rc = new \ReflectionMethod($toObject, $toMethod);
                        }
                        $ro = new \ReflectionObject($toObject);
                        $toObjectName = str_replace('ProcessWire\\', '', $ro->getName());
                    }
                    if(isset($rc)) {
                        $file = $rc->getFileName();
                        $line = $rc->getStartLine();
                        $toMethodName = str_replace('ProcessWire\\', '', $rc->getName());
                        $visitedByStr = \TracyDebugger::createEditorLink(\TracyDebugger::removeCompilerFromPath($file), $line, ($toObject ? "$toObjectName::$toMethodName" : $toMethodName));
                    }
                    else {
                        $visitedByStr = ($toObject ? "$toObjectName::$toMethod" : $toMethodName);
                    }

                    if(is_callable($toMethod)) $toMethod = 'anonymous function';
                    $hooksCalled .= "<tr>";
                    $hooksCalled .= "<td>" . ($hook['options']['before'] ? 'before ' : '') . ($hook['options']['after'] ? 'after' : '') . "</td>";
                    $hooksCalled .= "<td>" . ($hook['options']['fromClass'] ? $hook['options']['fromClass'] . '::' : '') . "$hook[method]$suffix</td>";
                    $hooksCalled .= "<td>" . $visitedByStr . "()</td>";
                    $hooksCalled .= "<td>" . ($hook['options']['allInstances'] || $hook['options']['fromClass'] ? "class " : "instance ") . $hook['options']['type'] . "</td>";
                    $hooksCalled .= "<td>" . $hook['options']['priority'] . "</td>";
                    $hooksCalled .= "</tr>";
                }
                $hooksCalled .= $sectionEnd;
            }

            if($debugMode && in_array('databaseQueries', $panelSections)) {
                // Database Queries
                $databaseQueries_oc = 0;
                $databaseQueries = $this->sectionHeader(array('Order', 'Query'));
                if($PwVersion >= 2.8) {
                    $queryMethod = $this->wire('database')->queryLog();
                }
                else {
                    $queryMethod = WireDatabasePDO::getQueryLog();
                }
                foreach($queryMethod as $n => $sql) {
                    $databaseQueries_oc++;
                    $sql = $this->wire('sanitizer')->entities1($sql);
                    $databaseQueries .= "\n<tr><td>$n</td><td class='tracy-force-break'>".$sql."</td></tr>";
                }
                $databaseQueries .= $sectionEnd;
            }

            if(in_array('selectorQueries', $panelSections)) {
                // Selectors Queries
                $selectorQueries_oc = 0;
                $selectorQueries = $this->sectionHeader(array('Order', 'Selector', 'Caller', 'SQL Query', 'Settings', 'Time (ms)'));
                foreach(\TracyDebugger::$pageFinderQueries as $n => $query) {
                    $selectorQueries_oc++;
                    $selector = $this->wire('sanitizer')->entities1((string)$query->arguments[0]);
                    if(method_exists($query->return, 'getDebugQuery')) {
                        // 3.0.158 and newer - needed to get bound values
                        $sqlStr = $query->return->getDebugQuery();
                    }
                    else {
                    // 3.0.157 and earlier
                    $sqlStr = $query->return->getQuery();
                    }
                    $selectorQueries .= "\n<tr><td>$n</td><td class='tracy-force-break'>".$selector."</td><td>".(isset($query->arguments[1]['caller']) ? $query->arguments[1]['caller'] : '')."</td><td class='tracy-force-break'>".$sqlStr."</td><td>".(isset($query->arguments[1]) ? \Tracy\Dumper::toHtml($query->arguments[1], array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => false)) : '')."</td><td>".($query->arguments[2]*1000)."</td></tr>";
                }
                $selectorQueries .= $sectionEnd;
            }

            if($debugMode && in_array('timers', $panelSections)) {
                // Timers
                $timers_oc = 0;
                $timers = $this->sectionHeader(array('Timer', 'Seconds'));
                $savedTimers = Debug::getSavedTimers();
                foreach($savedTimers as $name => $timer) {
                    $timers_oc++;
                    $timers .= "\n<tr><td>$name</td><td>$timer</td></tr>";
                }
                $timers .= $sectionEnd;
            }

            if(in_array('user', $panelSections)) {
                // User
                $userDetails = '
                <p><strong>Username:</strong> '.$this->wire('user')->name.'</p>
                <p><strong>User ID:</strong> '.$this->wire('user')->id.'</p>
                <p><strong>Current User Roles</strong><br />';
                foreach($this->wire('user')->roles as $role) $userDetails .= "\n{$role->name}<br />";
                $userDetails .= '
                </p>
                <p><strong>Current User Permissions</strong><br />';
                foreach($this->wire('user')->getPermissions() as $permission) $userDetails .= "\n{$permission->name}<br />";
                $userDetails .= '
                </p>
                <p><strong>Current User Permissions on this page</strong><br />';
                foreach($this->wire('user')->getPermissions($p) as $permission) $userDetails .= "\n{$permission->name}<br />";
                $userDetails .= '
                </p>';
            }


            if(in_array('cache', $panelSections)) {
                // Cache
                $cacheDetails_oc = 0;
                $cacheDetails_oc2 = 0;
                $fileCompilerCacheQty = 0;
                $cacheDetails = '';
                foreach($this->wire('cache')->getInfo() as $info) {
                    $cacheDetails_oc++;
                    if(strpos($info['name'], 'FileCompiler') === 0) {
                        $fileCompilerCacheQty++;
                        continue;
                    }
                    $cacheDetails_oc2++;
                    $cacheName = $this->wire('sanitizer')->entities($info['name']);
                    $cacheDetails .= "<table class=''><thead><tr><th colspan='2'>";
                    $cacheDetails .= $cacheName;
                    if(isset($adminerUrl)) {
                        $cacheDetails .= '<a style="float:right; cursor:pointer" title="Edit in Adminer" style="padding-bottom:5px" href="'.$adminerUrl.'?edit=caches&where%5Bname%5D='.$cacheName.'">'.$adminerIcon.'</a>';
                    }
                    $cacheDetails .= "</th></tr></thead><tbody>";
                    foreach($info as $key => $value) {
                        if($key == 'name') continue;
                        if($key == 'size') $value = wireBytesStr($value);
                        $key = $this->wire('sanitizer')->entities($key);
                        $value = $this->wire('sanitizer')->entities($value);
                        $cacheDetails .= "<tr><td width='30%'>$key</td><td>$value</td></tr>";
                    }
                    $cacheDetails .= "</tbody></table><br />";
                }
            }

            if($debugMode && in_array('autoload', $panelSections)) {
                // Autoload
                $autoload_oc = 0;
                if($PwVersion >= 2.8) {
                    $autoload = $this->sectionHeader(array('Class', 'File/Details'));
                    foreach($this->wire('classLoader')->getDebugLog() as $className => $classFile) {
                        $autoload_oc++;
                        $className = $this->wire('sanitizer')->entities($className);
                        $classFile = $this->wire('sanitizer')->entities($classFile);
                        $autoload .= "<tr><td width='40%'>$className</td><td>$classFile</td></tr>";
                    }
                    $autoload .= $sectionEnd;
                }
            }


            // Load all the panel sections
            $out .= '
            <br />';

            if(in_array('pagesLoaded', $panelSections)) {
                $out .= '
                <a href="#" rel="#pages-loaded" class="tracy-toggle tracy-collapsed">Pages Loaded ('.$pagesLoaded_oc.')</a>
                <div id="pages-loaded" class="tracy-collapsed">'.$pagesLoaded.'</div><br />';
            }

            if(in_array('modulesLoaded', $panelSections)) {
                $out .= '
                <a href="#" rel="#modules-loaded" class="tracy-toggle tracy-collapsed">Modules Loaded ('.$modulesNumLoaded.'/'.$modulesNumSkipped.')</a>
                <div id="modules-loaded" class="tracy-collapsed">'.$modulesLoaded;
                $out .= '<p>' . $modulesNumLoaded . ' modules loaded / ' . $modulesNumSkipped . ' not loaded</p>';
                $out .= '</div><br />';
            }

            if(in_array('hooks', $panelSections)) {
                $out .= '
                <a href="#" rel="#hooks" class="tracy-toggle tracy-collapsed">Hooks Triggered ('.$hooksCalled_oc.')</a>
                <div id="hooks" class="tracy-collapsed">'.$hooksCalled.'</div><br />';
            }

            if($debugMode && in_array('databaseQueries', $panelSections)) {
                $out .= '
                <a href="#" rel="#database-queries" class="tracy-toggle tracy-collapsed">PDO Queries ($database) ('.$databaseQueries_oc.')</a>
                <div id="database-queries" class="tracy-collapsed">'.$databaseQueries.'</div><br />';
            }

            if(in_array('selectorQueries', $panelSections)) {
                $out .= '
                <a href="#" rel="#selector-queries" class="tracy-toggle tracy-collapsed">Selector Queries ('.$selectorQueries_oc.')</a>
                <div id="selector-queries" class="tracy-collapsed">'.$selectorQueries.'</div><br />';
            }

            if($debugMode && in_array('timers', $panelSections)) {
                $out .= '
                <a href="#" rel="#timers" class="tracy-toggle tracy-collapsed">Timers ('.$timers_oc.')</a>
                <div id="timers" class="tracy-collapsed">'.$timers.'</div><br />';
            }

            if(in_array('user', $panelSections)) {
                $out .= '
                <a href="#" rel="#user-details" class="tracy-toggle tracy-collapsed">User ('.$this->wire('user')->name.')</a>
                <div id="user-details" class="tracy-collapsed">'.$userDetails.'</div><br />';
            }

            if(in_array('cache', $panelSections)) {
                $out .= '
                <a href="#" rel="#cache-details" class="tracy-toggle tracy-collapsed">Cache ('.$cacheDetails_oc.'/'.$fileCompilerCacheQty.'/'.$cacheDetails_oc2.')</a>
                <div id="cache-details" class="tracy-collapsed">'.$cacheDetails;
                if($fileCompilerCacheQty) {
                    $out .= "<p>Plus $fileCompilerCacheQty cached items for FileCompiler (not shown).</p>";
                }
                $out .= '</div><br />';
            }

            if($debugMode && in_array('autoload', $panelSections) && $PwVersion >= 2.8) {
                $out .= '
                <a href="#" rel="#autoload" class="tracy-toggle tracy-collapsed">Autoload ('.$autoload_oc.')</a>
                <div id="autoload" class="tracy-collapsed">'.$autoload.'</div><br />
                ';
            }

        }

        $out .= \TracyDebugger::generatePanelFooter('debugMode', \Tracy\Debugger::timer('debugMode'), strlen($out), 'debugModePanel');
        $out .= '<br /></div>';

        return parent::loadResources() . $out;
    }


    private function addRoot($value) {
        return wire('config')->paths->root . $value;
    }

}
