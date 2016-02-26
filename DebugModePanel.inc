<?php

use Tracy\IBarPanel;

/**
 * Custom PW panel
 */

class DebugModePanel implements IBarPanel {

    protected static $iconColor;
    protected static $panelIconColor;

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

    public function getTab() {

        $debugMode = wire('config')->debug;

        $debugColor = $debugMode ? '#CD1818' : '#009900';

        if(\TracyDebugger::getDataValue('showPanelLabels')) {
            static::$iconColor = '#FFFFFF';
            $background = $debugColor;
            $label = '&nbsp;PW Debug Mode ' . ($debugMode ? 'ON' : 'OFF');
            $tabPadding = '5px';
        }
        else {
            static::$iconColor = $debugColor;
            $background = 'none';
            $label = '';
            $tabPadding = '0';
        }
        static::$panelIconColor = $debugColor;

        return "
        <style>
            .tracy-panel {
                max-height: 90% !important;
                max-width: 95% !important;
                overflow-y: scroll !important;
            }
            .tracy-toggle {
                font-size: 1rem !important;
            }
        </style>
        <span>
            <span style='display:inline-block;background:".$background."; color:#FFFFFF; padding: 0 ".$tabPadding."';>
                <svg xmlns=http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' x='0px' y='0px' width='16px' height='16px' viewBox='0 0 456.828 456.828' style='enable-background:new 0 0 456.828 456.828;' xml:space='preserve'>
                    <g>
                        <path d='M451.383,247.54c-3.606-3.617-7.898-5.427-12.847-5.427h-63.953v-83.939l49.396-49.394    c3.614-3.615,5.428-7.898,5.428-12.85c0-4.947-1.813-9.229-5.428-12.847c-3.614-3.616-7.898-5.424-12.847-5.424    s-9.233,1.809-12.847,5.424l-49.396,49.394H107.923L58.529,83.083c-3.617-3.616-7.898-5.424-12.847-5.424    c-4.952,0-9.233,1.809-12.85,5.424c-3.617,3.617-5.424,7.9-5.424,12.847c0,4.952,1.807,9.235,5.424,12.85l49.394,49.394v83.939    H18.273c-4.949,0-9.231,1.81-12.847,5.427C1.809,251.154,0,255.442,0,260.387c0,4.949,1.809,9.237,5.426,12.848    c3.616,3.617,7.898,5.431,12.847,5.431h63.953c0,30.447,5.522,56.53,16.56,78.224l-57.67,64.809    c-3.237,3.81-4.712,8.234-4.425,13.275c0.284,5.037,2.235,9.273,5.852,12.703c3.617,3.045,7.707,4.571,12.275,4.571    c5.33,0,9.897-1.991,13.706-5.995l52.246-59.102l4.285,4.004c2.664,2.479,6.801,5.564,12.419,9.274    c5.617,3.71,11.897,7.423,18.842,11.143c6.95,3.71,15.23,6.852,24.84,9.418c9.614,2.573,19.273,3.86,28.98,3.86V169.034h36.547    V424.85c9.134,0,18.363-1.239,27.688-3.717c9.328-2.471,17.135-5.232,23.418-8.278c6.275-3.049,12.47-6.519,18.555-10.42    c6.092-3.901,10.089-6.612,11.991-8.138c1.909-1.526,3.333-2.762,4.284-3.71l56.534,56.243c3.433,3.617,7.707,5.424,12.847,5.424    c5.141,0,9.422-1.807,12.854-5.424c3.607-3.617,5.421-7.902,5.421-12.851s-1.813-9.232-5.421-12.847l-59.388-59.669    c12.755-22.651,19.13-50.251,19.13-82.796h63.953c4.949,0,9.236-1.81,12.847-5.427c3.614-3.614,5.432-7.898,5.432-12.847    C456.828,255.445,455.011,251.158,451.383,247.54z' fill='".static::$iconColor."'/>
                        <path d='M293.081,31.27c-17.795-17.795-39.352-26.696-64.667-26.696c-25.319,0-46.87,8.901-64.668,26.696    c-17.795,17.797-26.691,39.353-26.691,64.667h182.716C319.771,70.627,310.876,49.067,293.081,31.27z' fill='".static::$iconColor."'/>
                    </g>
                </svg>" . $label . "
            </span>
        </span>
        ";
    }



    public function getPanel() {

        $PwVersion = wire('config')->version;
        $debugMode = wire('config')->debug;

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';

        $out = "
        <h1>
        <svg xmlns=http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' x='0px' y='0px' width='16px' height='16px' viewBox='0 0 456.828 456.828' style='enable-background:new 0 0 456.828 456.828;' xml:space='preserve'>
            <g>
                <path d='M451.383,247.54c-3.606-3.617-7.898-5.427-12.847-5.427h-63.953v-83.939l49.396-49.394    c3.614-3.615,5.428-7.898,5.428-12.85c0-4.947-1.813-9.229-5.428-12.847c-3.614-3.616-7.898-5.424-12.847-5.424    s-9.233,1.809-12.847,5.424l-49.396,49.394H107.923L58.529,83.083c-3.617-3.616-7.898-5.424-12.847-5.424    c-4.952,0-9.233,1.809-12.85,5.424c-3.617,3.617-5.424,7.9-5.424,12.847c0,4.952,1.807,9.235,5.424,12.85l49.394,49.394v83.939    H18.273c-4.949,0-9.231,1.81-12.847,5.427C1.809,251.154,0,255.442,0,260.387c0,4.949,1.809,9.237,5.426,12.848    c3.616,3.617,7.898,5.431,12.847,5.431h63.953c0,30.447,5.522,56.53,16.56,78.224l-57.67,64.809    c-3.237,3.81-4.712,8.234-4.425,13.275c0.284,5.037,2.235,9.273,5.852,12.703c3.617,3.045,7.707,4.571,12.275,4.571    c5.33,0,9.897-1.991,13.706-5.995l52.246-59.102l4.285,4.004c2.664,2.479,6.801,5.564,12.419,9.274    c5.617,3.71,11.897,7.423,18.842,11.143c6.95,3.71,15.23,6.852,24.84,9.418c9.614,2.573,19.273,3.86,28.98,3.86V169.034h36.547    V424.85c9.134,0,18.363-1.239,27.688-3.717c9.328-2.471,17.135-5.232,23.418-8.278c6.275-3.049,12.47-6.519,18.555-10.42    c6.092-3.901,10.089-6.612,11.991-8.138c1.909-1.526,3.333-2.762,4.284-3.71l56.534,56.243c3.433,3.617,7.707,5.424,12.847,5.424    c5.141,0,9.422-1.807,12.854-5.424c3.607-3.617,5.421-7.902,5.421-12.851s-1.813-9.232-5.421-12.847l-59.388-59.669    c12.755-22.651,19.13-50.251,19.13-82.796h63.953c4.949,0,9.236-1.81,12.847-5.427c3.614-3.614,5.432-7.898,5.432-12.847    C456.828,255.445,455.011,251.158,451.383,247.54z' fill='".static::$panelIconColor."'/>
                <path d='M293.081,31.27c-17.795-17.795-39.352-26.696-64.667-26.696c-25.319,0-46.87,8.901-64.668,26.696    c-17.795,17.797-26.691,39.353-26.691,64.667h182.716C319.771,70.627,310.876,49.067,293.081,31.27z' fill='".static::$panelIconColor."'/>
            </g>
        </svg>
        ProcessWire Debug Mode</h1>
        <p style='font-size:15px'>";

        if($debugMode) {
            $out .= '
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 483.537 483.537" style="enable-background:new 0 0 483.537 483.537;" xml:space="preserve" width="16px" height="16px">
                <path d="M479.963,425.047L269.051,29.854c-5.259-9.88-15.565-16.081-26.782-16.081h-0.03     c-11.217,0-21.492,6.171-26.782,16.051L3.603,425.016c-5.046,9.485-4.773,20.854,0.699,29.974     c5.502,9.15,15.413,14.774,26.083,14.774H453.12c10.701,0,20.58-5.594,26.083-14.774     C484.705,445.84,484.979,434.471,479.963,425.047z M242.239,408.965c-16.781,0-30.399-13.619-30.399-30.399     c0-16.78,13.619-30.399,30.399-30.399c16.75,0,30.399,13.619,30.399,30.399C272.638,395.346,259.02,408.965,242.239,408.965z      M272.669,287.854c0,16.811-13.649,30.399-30.399,30.399c-16.781,0-30.399-13.589-30.399-30.399V166.256     c0-16.781,13.619-30.399,30.399-30.399c16.75,0,30.399,13.619,30.399,30.399V287.854z" fill="#ee1d62"/>
            </svg>&nbsp;
            <strong>WARNING</strong><br />ProcessWire Debug Mode is ON<br />Make sure it is OFF for live sites.';
        }
        else {
            $out .= '<p style="font-size:15px"><strong>ProcessWire Debug Mode is OFF</strong></p>
            <p>With debug mode off, it is not possible to access the "Database Queries", "Timers", and "Autload" sections.';
        }

        $out .= '</p>';


        /**
         * PW Debug Mode Tools panel sections
         */


        // Pages Loaded
        if($PwVersion >= 3) {
            $pagesLoaded = $this->sectionHeader(array('ID', 'Path', 'Type', 'Loader'));
            foreach(wire('pages')->getCache() as $p) {
                $parts = explode('/', trim($p->path, '/'));
                $name = array_pop($parts);
                $path = implode('/', $parts) . "/<b>$name</b>/";
                if($path[0] != '/') $path = "/$path";
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
            foreach(wire('pages')->getCache() as $p) {
                $pagesLoaded .= "\n<tr><td>$p->id</td><td>$p->path</td><td>$p->title</td></tr>";
            }
        }
        $pagesLoaded .= $sectionEnd;

        // API Variables
        $apiVariables = $this->sectionHeader(array('Name', 'Class'));
        if($PwVersion >= 3) {
            foreach(wire('fuel') as $key => $value) {
                if(!is_object($value)) continue;
                $apiVariables .= "\n<tr><td><a href='https://processwire.com/api/variables/$key/'>\$$key</a></td>" .
                    "<td>" . get_class($value) . "</td></tr>";
            }
        }
        else {
                foreach(wire() as $key => $value) {
                    if(!is_object($value)) continue;
                    $apiVariables .= "\n<tr><td><a href='http://processwire.com/api/variables/$key/'>\$$key</a></td>" .
                        "<td>" . get_class($value) . "</td></tr>";
                }
        }
        $apiVariables .= $sectionEnd;

        // Session
        $sessionEntries = $this->sectionHeader(array('Key', 'Value'));
        foreach(wire('session') as $key => $value) {
            if(is_object($value)) $value = (string) $value;
            if(is_array($value)) $value = print_r($value, true);
            $sessionEntries .= "<tr><td>$key</td><td><pre>" . wire('sanitizer')->entities($value) . "</pre></td></tr>";
        }
        $sessionEntries .= $sectionEnd;

        // Modules Loaded
        $modulesLoaded = $this->sectionHeader(array('Class', 'Version', 'Title'));
        foreach(wire('modules') as $module) {
            if($module instanceof ModulePlaceholder) continue;
            $info = wire('modules')->getModuleInfo($module, array('verbose' => false));
            $modulesLoaded .= "<tr>";
            $modulesLoaded .= "<td>$info[name]</td>";
            $modulesLoaded .= "<td>$info[version]</td>";
            $modulesLoaded .= "<td>$info[title]</td>";
            $modulesLoaded .= "</tr>";
        }
        $modulesLoaded .= $sectionEnd;

        // Hooks
        $hooksCalled = $this->sectionHeader(array('When', 'Method::object', 'Visited by', 'Type', 'Priority'));
        if($PwVersion >= 3) {
            $hooks = array_merge(wire('pages')->getHooks('*'), wire('hooks')->getAllLocalHooks());
            $hooksSorted = array();
            foreach($hooks as $hook) {
                $whenKey = $hook['options']['before'] ? '0' : '1';
                $sortKey = $hook['options']['fromClass'] . ":$hook[method]:$whenKey:" . $hook['options']['priority'];
                $hooksSorted[$sortKey] = $hook;
            }
            ksort($hooksSorted);
            foreach($hooksSorted as $key => $hook) {
                $suffix = $hook['options']['type'] == 'method' ? '()' : '';
                $toObject = !empty($hook['toObject']) ? $hook['toObject'] : '';
                $toMethod = $hook['toMethod'];
                if(is_callable($toMethod)) $toMethod = 'anonymous function';
                $hooksCalled .= "<tr>";
                $hooksCalled .= "<td>" . ($hook['options']['before'] ? 'before ' : '') . ($hook['options']['after'] ? 'after' : '') . "</td>";
                $hooksCalled .= "<td>" . ($hook['options']['fromClass'] ? $hook['options']['fromClass'] . '::' : '') . "$hook[method]$suffix</td>";
                $hooksCalled .= "<td>" . ($toObject ? "$toObject::$toMethod" : $toMethod) . "()</td>";
                $hooksCalled .= "<td>" . ($hook['options']['allInstances'] || $hook['options']['fromClass'] ? "class " : "instance ") . $hook['options']['type'] . "</td>";
                $hooksCalled .= "<td>" . $hook['options']['priority'] . "</td>";
                $hooksCalled .= "</tr>";
            }
        }
        else {
            $hooks = array_merge(wire()->getHooks('*'), Wire::$allLocalHooks);
            $hooksSorted = array();
            foreach($hooks as $hook) {
                $whenKey = $hook['options']['before'] ? '0' : '1';
                $sortKey = $hook['options']['fromClass'] . ":$hook[method]:$whenKey:" . $hook['options']['priority'];
                $hooksSorted[$sortKey] = $hook;
            }
            ksort($hooksSorted);
            foreach($hooksSorted as $key => $hook) {
                $suffix = $hook['options']['type'] == 'method' ? '()' : '';
                $toObject = !empty($hook['toObject']) ? $hook['toObject'] : '';
                $toMethod = $hook['toMethod'];
                if(is_callable($toMethod)) $toMethod = 'anonymous function';
                $hooksCalled .= "<tr>";
                $hooksCalled .= "<td>" . ($hook['options']['before'] ? 'before ' : '') . ($hook['options']['after'] ? 'after' : '') . "</td>";
                $hooksCalled .= "<td>" . ($hook['options']['fromClass'] ? $hook['options']['fromClass'] . '::' : '') . "$hook[method]$suffix</td>";
                $hooksCalled .= "<td>" . ($toObject ? "$toObject::$toMethod" : $toMethod) . "()</td>";
                $hooksCalled .= "<td>" . ($hook['options']['allInstances'] || $hook['options']['fromClass'] ? "class " : "instance ") . $hook['options']['type'] . "</td>";
                $hooksCalled .= "<td>" . $hook['options']['priority'] . "</td>";
                $hooksCalled .= "</tr>";
            }
        }
        $hooksCalled .= $sectionEnd;

        // Database Queries
        $databaseQueries = $this->sectionHeader(array('Order', 'Query'));
        if($PwVersion >= 3) {
            foreach(wire('database')->queryLog() as $n => $sql) $databaseQueries .= "\n<tr><th>$n</th><td>$sql</td></tr>";
        }
        else {
            foreach(WireDatabasePDO::getQueryLog() as $n => $sql) $databaseQueries .= "\n<tr><th>$n</th><td>$sql</td></tr>";
        }
        $databaseQueries .= $sectionEnd;

        // Timers
        $timers = $this->sectionHeader(array('Timer', 'Seconds'));
        $savedTimers = Debug::getSavedTimers();
        foreach($savedTimers as $name => $timer) $timers .= "\n<tr><th>$name</th><td>$timer</td></tr>";
        $timers .= $sectionEnd;

        // User
        $userDetails = '
        <p><strong>Username:</strong> '.wire('user')->name.'</p>
        <p><strong>User ID:</strong> '.wire('user')->id.'</p>
        <p><strong>User Roles</strong>
        <ol>';
        foreach(wire('user')->roles as $role) $userDetails .= "\n<li>{$role->name}</li>";
        $userDetails .= '
        </ol>
        </p>
        <p><strong>User Permissions</strong>
        <ol>';
        foreach(wire('user')->getPermissions() as $permission) $userDetails .= "\n<li>{$permission->name}</li>";
        $userDetails .= '</ol>
        </p>
        <p><strong>User Permissions on this page</strong>
        <ol>
        ';
        foreach(wire('user')->getPermissions(wire('page')) as $permission) $userDetails .= "\n<li>{$permission->name}</li>";
        $userDetails .= '</ol>
        </p>';

        // GET, POST, & COOKIE
        foreach(array('get', 'post', 'cookie') as $type) {
            $i = wire('input')->$type;
            if(!count($i)) continue;
            $input[$type] = $this->sectionHeader(array('Key', 'Value'));
            foreach($i as $key => $value) {
                if(is_array($value)) $value = print_r($value, true);
                $input[$type] .= "<tr><td>" . wire('sanitizer')->entities($key) . "</td><td><pre>" . wire('sanitizer')->entities($value) . "</pre></td></tr>";
            }
            $input[$type] .= $sectionEnd;
        }


        // Cache
        $cacheDetails = '';
        foreach(wire('cache')->getInfo() as $info) {
            $cacheDetails .= "<table class=''><thead><tr><th colspan='2'>";
            $cacheDetails .= wire('sanitizer')->entities($info['name']) . "</th></tr></thead><tbody>";
            foreach($info as $key => $value) {
                if($key == 'name') continue;
                if($key == 'size') $value = wireBytesStr($value);
                $key = wire('sanitizer')->entities($key);
                $value = wire('sanitizer')->entities($value);
                $cacheDetails .= "<tr><th width='30%'>$key</th><td>$value</td></tr>";
            }
            $cacheDetails .= "</tbody></table><br />";
        }

        // Autoload
        if($PwVersion >= 3) {
            $autoload = $this->sectionHeader(array('Class', 'File/Details'));
            foreach(wire('classLoader')->getDebugLog() as $className => $classFile) {
                $className = wire('sanitizer')->entities($className);
                $classFile = wire('sanitizer')->entities($classFile);
                $autoload .= "<tr><th width='40%'>$className</th><td>$classFile</td></tr>";
            }
            $autoload .= $sectionEnd;
        }

        // Load all the panel sections
        $out .= '
        <br />
        <a href="#" rel="#pages-loaded" class="tracy-toggle tracy-collapsed">Pages Loaded</a>
        <div id="pages-loaded" class="tracy-collapsed">'.$pagesLoaded.'</div><br />

        <a href="#" rel="#api-variables" class="tracy-toggle tracy-collapsed">API Variables</a>
        <div id="api-variables" class="tracy-collapsed">'.$apiVariables.'</div><br />

        <a href="#" rel="#session-entries" class="tracy-toggle tracy-collapsed">Session</a>
        <div id="session-entries" class="tracy-collapsed">'.$sessionEntries.'</div><br />

        <a href="#" rel="#modules-loaded" class="tracy-toggle tracy-collapsed">Modules Loaded</a>
        <div id="modules-loaded" class="tracy-collapsed">'.$modulesLoaded.'</div><br />

        <a href="#" rel="#hooks" class="tracy-toggle tracy-collapsed">Hooks</a>
        <div id="hooks" class="tracy-collapsed">'.$hooksCalled.'</div><br />

        <a href="#" rel="#database-queries" class="tracy-toggle tracy-collapsed">Database Queries</a>
        <div id="database-queries" class="tracy-collapsed">'.$databaseQueries.'</div><br />

        <a href="#" rel="#timers" class="tracy-toggle tracy-collapsed">Timers</a>
        <div id="timers" class="tracy-collapsed">'.$timers.'</div><br />

        <a href="#" rel="#user-details" class="tracy-toggle tracy-collapsed">User</a>
        <div id="user-details" class="tracy-collapsed">'.$userDetails.'</div><br />';

        if(isset($input['get'])) {
            $out .= '
            <a href="#" rel="#input-get" class="tracy-toggle tracy-collapsed">$input->get</a>
            <div id="input-get" class="tracy-collapsed">'.$input['get'].'</div><br />';
        }

        if(isset($input['post'])) {
            $out .= '
            <a href="#" rel="#input-post" class="tracy-toggle tracy-collapsed">$input->post</a>
            <div id="input-post" class="tracy-collapsed">'.$input['post'].'</div><br />';
        }

        if(isset($input['cookie'])) {
            $out .= '
            <a href="#" rel="#input-cookie" class="tracy-toggle tracy-collapsed">$input->cookie</a>
            <div id="input-cookie" class="tracy-collapsed">'.$input['cookie'].'</div><br />';
        }

        $out .= '
        <a href="#" rel="#cache-details" class="tracy-toggle tracy-collapsed">Cache</a>
        <div id="cache-details" class="tracy-collapsed">'.$cacheDetails.'</div><br />';

        if($PwVersion >= 3) {
            $out .= '
            <a href="#" rel="#autoload" class="tracy-toggle tracy-collapsed">Autoload</a>
            <div id="autoload" class="tracy-collapsed">'.$autoload.'</div><br />
            ';
        }

        return $out;
    }


}
