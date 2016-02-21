<?php

use Tracy\Debugger;
use Tracy\IBarPanel;
use Tracy\Dumper;

/**
 * Custom PW panel
 */

class ProcessWirePanel implements IBarPanel {
    public function getTab() {
        return '
        <style>
            .tracy-panel {
                max-height: 90% !important;
                max-width: 95% !important;
                overflow-y: scroll !important;
            }
        </style>
        <span title="ProcessWire debug info">
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 viewBox="0 0 39.7 40.1" enable-background="new 0 0 39.7 40.1" xml:space="preserve">
            <path fill="none" stroke="#3A4163" stroke-width="4.2" stroke-miterlimit="10" d="M11.6,19.5"/>
            <path fill="none" stroke="#3A4163" stroke-width="4.2" stroke-miterlimit="10" d="M11.8,22.4"/>
            <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="16.3309" y1="26.4008" x2="22.7901" y2="25.4266">
                <stop  offset="0" style="stop-color:#AE3E7D"/>
                <stop  offset="1" style="stop-color:#EA2262"/>
            </linearGradient>
            <path fill="url(#SVGID_1_)" d="M22.1,23.4c-0.6,0.5-1.2,0.7-1.6,0.8c-0.8,0.3-1.7,0.4-2.5,0.3c-0.1,0-0.2,0.1-0.3,0.2l-0.3,1.4
                c-0.3,1.1,0.3,1.5,0.7,1.6c1.1,0.3,2.1,0.5,3.3,0.4c1.1-0.1,2.2-0.4,3.2-0.9l-1.9-4.5C22.5,23.1,22.3,23.3,22.1,23.4z"/>
            <path fill="#EA2262" d="M39.3,15.8c-0.4-2.3-1.6-4.9-3-7c-1.2-1.8-3.2-3.9-5.2-5.2c-4.2-2.9-9-3.9-13.3-3.5c-4.5,0.5-8.3,2.2-11.4,5
                C3.4,7.7,1.5,10.9,0.7,14c-0.9,3.1-0.7,5.9-0.4,8c0.3,2.2,1.4,4.9,1.4,4.9c0.2,0.5,0.5,0.7,0.7,0.8c0.8,0.4,2.1,0,3.1-1.2
                c-0.3-1.1-0.4-2-0.5-2.6c-0.2-1.2-0.3-3.3-0.2-5.2c0.1-1,0.3-2.1,0.6-3.3c0.7-2.3,2.1-4.7,4.3-6.6c2.4-2.1,5.4-3.4,8.3-3.8
                c1-0.1,3-0.2,5.3,0.3c0.5,0.1,2.6,0.7,4.9,2.2c1.7,1.1,3,2.6,3.9,3.9c1,1.3,2,3.6,2.3,5.2c0.4,1.9,0.4,3.9,0.1,5.8
                c-0.4,1.9-1,3.8-2.1,5.5c-0.7,1.3-2.2,3-4,4.2c-1.6,1.1-3.4,2-5.3,2.4c-0.9,0.2-1.9,0.4-2.8,0.4c-0.8,0-2,0-2.8-0.1
                c-1.2-0.2-1.4-0.5-1.7-0.9c0,0-0.2-0.3-0.2-1.1c0-7.4,0-5.4,0-9.2c0-1.1,0-2.1,0-2.9c0-1.5,0.2-2.5,1.2-3.5c0.7-0.8,1.8-1.3,2.9-1.3
                c0.3,0,1.6,0,2.6,0.9c1.1,1,1.3,2.3,1.4,2.6c0.2,1.4-0.4,2.6-1,3.3c0.2,1.4,0.7,2.7,1.9,4.5c0.6-0.3,1.3-0.8,1.8-1.3
                c1.3-1.2,2-2.7,2.3-4.4c0.3-1.9-0.1-3.9-0.9-5.6c-0.9-1.9-2.5-3.4-4.6-4.3c-2.1-0.8-3.8-0.9-6-0.3c-1.4,0.5-2.7,1.1-3.9,2.4
                c-0.9,0.9-1.6,2-2,3.3c-0.4,1.3-0.5,2.2-0.6,3.7c0,1.1,0,2.1,0,3c0,1,0,2,0,3.1s0,2.1,0,3.1c0,2-0.1,2.3,0,3.3
                c0,0.7,0.1,1.4,0.4,2.3c0.3,0.9,0.9,1.8,1.4,2.3c0.6,0.7,1.4,1.2,2.1,1.5c1.7,0.8,4.1,0.9,6,0.8c1.3,0,2.5-0.2,3.8-0.5
                c2.5-0.6,4.9-1.7,7-3.2c2.3-1.6,4.2-3.8,5.3-5.7c1.4-2.2,2.3-4.7,2.8-7.2C39.9,20.9,39.8,18.3,39.3,15.8z"/>
            </svg> ProcessWire
        </span>
        ';
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

        $PwVersion = wire('config')->version;

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';


        /**
         * Custom PW panel sections
         */

        if(wire('page')->template != 'admin') {

            // Page info
            $pageSummary = '
            <table>
                <tr>
                    <th>Title</th>
                    <td>'.wire('page')->title.'</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><a href="'.wire('config')->urls->admin.'page/edit/?id='.wire('page')->id.'" target="_blank">'.wire('page')->name.'</a></td>
                </tr>
                <tr>
                    <th>ID</th>
                    <td>'.wire('page')->id.'</td>
                </tr>
                <tr>
                <tr>
                    <th>Path</th>
                    <td>'.wire('page')->path.'</td>
                </tr>
                <tr>
                    <th>Template</th>
                    <td><a href="'.wire('config')->urls->admin.'setup/template/edit?id='.wire('page')->template->id.'" target="_blank">'.wire('page')->template->name.'</td>
                </tr>
                <tr>
                    <th>Parent</th>
                    <td><a href="'.wire('config')->urls->admin.'page/edit/?id='.wire('page')->parent->id.'" target="_blank">'.wire('page')->parent->name.'</a></td>
                </tr>';
                if(wire('page')->prev->id && wire('page')->prev->viewable()) {
                    $pageSummary .= '
                    <tr>
                        <th>Prev Sibling</th>
                        <td><a href="'.wire('page')->prev->url.'">&lt; '.wire('page')->prev->name.'</a></td>
                    </tr>';
                }
                if(wire('page')->next->id && wire('page')->next->viewable()) {
                    $pageSummary .= '
                    <tr>
                        <th>Next Sibling</th>
                        <td><a href="'.wire('page')->next->url.'">'.wire('page')->next->name.' &gt;</a></td>
                    </tr>';
                }
                $pageSummary .= '
                <tr>
                    <th>Children</th>
                    <td><a href="'.wire('config')->urls->admin.'page/list/?open='.wire('page')->id.'" target="_blank">'.wire('page')->numChildren().'</a></td>
                </tr>
                <tr>
                    <th>Created</th>
                    <td>'.wire('page')->createdUser->name.' ('.date("Y-m-d H:i:s", wire('page')->created).')</td>
                </tr>
                <tr>
                    <th>Published</th>
                    <td>'.date("Y-m-d H:i:s", wire('page')->published).'</td>
                </tr>
                <tr>
                    <th>Modified</th>
                    <td>'.wire('page')->modifiedUser->name.' ('.date("Y-m-d H:i:s", wire('page')->modified).')</td>
                </tr>
                <tr>
                    <th>Hidden</th>
                    <td>'. (wire('page')->isHidden() ? "true" : "false") .'</td>
                </tr>
                <tr>
                    <th>Unpublished</th>
                    <td>'. (wire('page')->isUnpublished() ? "true" : "false") .'</td>
                </tr>
                <tr>
                    <th>Locked</th>
                    <td>'. (wire('page')->is(Page::statusLocked) ? "true" : "false") .'</td>
                </tr>
            </table>';

            // Page Object
            $pageObject = Dumper::toHtml(wire('page'), array(Dumper::COLLAPSE => false));

            // Template info
            $pageTemplateInfo = '
            <table>
                <tr>
                    <th>Label</th>
                    <td>'.wire('page')->template->label.'</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><a href="'.wire('config')->urls->admin.'setup/template/edit?id='.wire('page')->template->id.'" target="_blank">'.wire('page')->template->name.'</a></td>
                </tr>
                <tr>
                    <th>ID</th>
                    <td>'.wire('page')->template->id.'</td>
                </tr>
                <tr>
                    <th>Filename</th>
                    <td>'.str_replace(wire('config')->paths->root, '/', wire('page')->template->filename).'</td>
                </tr>
                <tr>
                    <th>Fieldgroup</th>
                    <td>'.wire('page')->template->fieldgroup.'</td>
                </tr>
                <tr>
                    <th>Page Numbers</th>
                    <td>'.(wire('page')->template->allowPageNum ? 'Enabled' : 'Disabled').'</td>
                </tr>
                <tr>
                    <th>URL Segments</th>
                    <td>'.(wire('page')->template->urlSegments ? 'Enabled' : 'Disabled').'</td>
                </tr>
                <tr>
                    <th>Children Allowed</th>
                    <td>'.(wire('page')->template->noChildren ? 'No' : 'Yes').'</td>
                </tr>
                <tr>
                    <th>Allow for New Page</th>
                    <td>'.(wire('page')->template->noParents < 0 ? 'Only One' : (wire('page')->template->noParents == 1 ? 'No' : 'Yes')).'</td>
                </tr>
                <tr>
                    <th>Children Sorted By</th>
                    <td>'.wire('page')->template->sortfield.'</td>
                </tr>
                <tr>
                    <th>Cache Time</th>
                    <td>'.wire('page')->template->cache_time.'</td>
                </tr>
            </table>';

            // Template Object
            $pageTemplateObject = Dumper::toHtml(wire('page')->template, array(Dumper::COLLAPSE => false));

            // Page Fields List
            $pageFieldsList = $this->sectionHeader(array('ID', 'Name', 'Label', 'Type'));
            foreach(wire('page')->fields as $f) {
                $pageFieldsList .= "\n<tr>" .
                    "<td>$f->id</td>" .
                    '<td><a href="'.wire('config')->urls->admin.'setup/field/edit?id='.$f->id.'" target="_blank">'.$f->name.'</a></td>' .
                    "<td>$f->label</td>" .
                    "<td>$f->type</td>" .
                    "</tr>";
            }
            $pageFieldsList .= $sectionEnd;

            // Page Fields Object
            $pageFieldsObject = Dumper::toHtml(wire('page')->fields, array(Dumper::COLLAPSE => false));


            // Page Fields and Values
            $pageFieldArray = array();
            foreach(wire('page')->fields as $f) {
                $pageFieldArray[wire('fields')->get($f->name)->name] = wire('page')->$f;
            }
            $pageFieldValues = Dumper::toHtml($pageFieldArray, array(Dumper::COLLAPSE => false));

        }


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
                $apiVariables .= "\n<tr><td><a target='_blank' href='https://processwire.com/api/variables/$key/'>\$$key</a></td>" .
                    "<td>" . get_class($value) . "</td></tr>";
            }
        }
        else {
                foreach(wire() as $key => $value) {
                    if(!is_object($value)) continue;
                    $apiVariables .= "\n<tr><td><a target='_blank' href='http://processwire.com/api/variables/$key/'>\$$key</a></td>" .
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
        $out = '
        <h1>
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 viewBox="0 0 20 20.2" enable-background="new 0 0 20 20.2" xml:space="preserve" style="height:20px">
            <path fill="none" stroke="#3A4163" stroke-width="4.2" stroke-miterlimit="10" d="M5.8,9.8"/>
            <path fill="none" stroke="#3A4163" stroke-width="4.2" stroke-miterlimit="10" d="M5.9,11.3"/>
            <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="8.2196" y1="13.2879" x2="11.4706" y2="12.7976">
                <stop  offset="0" style="stop-color:#AE3E7D"/>
                <stop  offset="1" style="stop-color:#EA2262"/>
            </linearGradient>
            <path fill="url(#SVGID_1_)" d="M11.1,11.8c-0.3,0.2-0.6,0.4-0.8,0.4c-0.4,0.1-0.9,0.2-1.3,0.2c-0.1,0-0.1,0-0.1,0.1l-0.1,0.7
                C8.7,13.7,9,13.9,9.1,14c0.6,0.2,1.1,0.2,1.6,0.2c0.6,0,1.1-0.2,1.6-0.5l-0.9-2.3C11.3,11.6,11.2,11.7,11.1,11.8z"/>
            <path fill="#EA2262" d="M19.8,7.9c-0.2-1.2-0.8-2.5-1.5-3.5c-0.6-0.9-1.6-2-2.6-2.6c-2.1-1.5-4.5-2-6.7-1.7C6.7,0.3,4.8,1.1,3.2,2.6
                C1.7,3.9,0.8,5.5,0.3,7c-0.4,1.5-0.4,3-0.2,4c0.2,1.1,0.7,2.5,0.7,2.5c0.1,0.3,0.3,0.4,0.3,0.4c0.4,0.2,1.1,0,1.6-0.6
                c-0.1-0.6-0.2-1-0.2-1.3c-0.1-0.6-0.2-1.6-0.1-2.6c0-0.5,0.1-1.1,0.3-1.6C3,6.7,3.8,5.5,4.9,4.5c1.2-1.1,2.7-1.7,4.2-1.9
                c0.5-0.1,1.5-0.1,2.7,0.2c0.3,0.1,1.3,0.3,2.5,1.1c0.8,0.6,1.5,1.3,2,2c0.5,0.7,1,1.8,1.1,2.6c0.2,0.9,0.2,1.9,0.1,2.9
                c-0.2,1-0.5,1.9-1.1,2.7c-0.4,0.6-1.1,1.5-2,2.1c-0.8,0.6-1.7,1-2.6,1.2c-0.5,0.1-1,0.2-1.4,0.2c-0.4,0-1,0-1.4-0.1
                c-0.6-0.1-0.7-0.3-0.9-0.5c0,0-0.1-0.2-0.1-0.6c0-3.7,0-2.7,0-4.6c0-0.5,0-1,0-1.5c0-0.7,0.1-1.2,0.6-1.8C8.8,8.3,9.4,8,9.9,8
                c0.2,0,0.8,0,1.3,0.5c0.6,0.5,0.7,1.1,0.7,1.3c0.1,0.7-0.2,1.3-0.5,1.7c0.1,0.7,0.4,1.4,0.9,2.3c0.3-0.2,0.6-0.4,0.9-0.7
                c0.6-0.6,1-1.4,1.1-2.2c0.1-0.9,0-2-0.5-2.8c-0.5-0.9-1.3-1.7-2.3-2.2c-1.1-0.4-1.9-0.4-3-0.2C7.9,6,7.3,6.3,6.6,6.9
                c-0.4,0.5-0.8,1-1,1.6c-0.2,0.6-0.3,1.1-0.3,1.8c0,0.5,0,1,0,1.5c0,0.5,0,1,0,1.5c0,0.5,0,1,0,1.6c0,1,0,1.2,0,1.7
                c0,0.3,0.1,0.7,0.2,1.1C5.7,18.3,6,18.8,6.3,19c0.3,0.3,0.7,0.6,1.1,0.8c0.9,0.4,2.1,0.5,3,0.4c0.6,0,1.3-0.1,1.9-0.3
                c1.3-0.3,2.5-0.8,3.5-1.6c1.1-0.8,2.1-1.9,2.7-2.9c0.7-1.1,1.2-2.4,1.4-3.6C20.1,10.5,20.1,9.2,19.8,7.9z"/>
            </svg>
            ProcessWire Info
        </h1>';

        if(wire('page')->template != 'admin') {
            $out .= '
            <p><strong>CURRENT PAGE INFO</strong></p>
            <a href="#" rel="#page-summary" class="tracy-toggle">Summary</a>
            <div id="page-summary">'.$pageSummary.'</div><br />

            <a href="#" rel="#page-object" class="tracy-toggle tracy-collapsed">Page Object</a>
            <div id="page-object" class="tracy-collapsed">'.$pageObject.'</div><br />

            <a href="#" rel="#page-template-info" class="tracy-toggle tracy-collapsed">Template Info</a>
            <div id="page-template-info" class="tracy-collapsed">'.$pageTemplateInfo.'</div><br />

            <a href="#" rel="#page-template-object" class="tracy-toggle tracy-collapsed">Template Object</a>
            <div id="page-template-object" class="tracy-collapsed">'.$pageTemplateObject.'</div><br />

            <a href="#" rel="#page-fields-list" class="tracy-toggle tracy-collapsed">Fields List</a>
            <div id="page-fields-list" class="tracy-collapsed">'.$pageFieldsList.'</div><br />

            <a href="#" rel="#page-fields-object" class="tracy-toggle tracy-collapsed">Fields Object</a>
            <div id="page-fields-object" class="tracy-collapsed">'.$pageFieldsObject.'</div><br />

            <a href="#" rel="#page-field-values" class="tracy-toggle tracy-collapsed">Field Values</a>
            <div id="page-field-values" class="tracy-collapsed">'.$pageFieldValues.'</div><br />';
        }

        $out .= '
        <br />
        <p><strong>DEBUG INFO</strong></p>
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

        $out .= '<div style="text-align: center; border-top:1px solid #CCCCCC; margin-top:10px; padding-top:10px;">
            <a href="'.wire('config')->urls->admin.'" title="PW Admin">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                     viewBox="0 0 20 20.2" enable-background="new 0 0 20 20.2" xml:space="preserve" style="height:16px">
                <path fill="none" stroke="#3A4163" stroke-width="4.2" stroke-miterlimit="10" d="M5.8,9.8"/>
                <path fill="none" stroke="#3A4163" stroke-width="4.2" stroke-miterlimit="10" d="M5.9,11.3"/>
                <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="8.2196" y1="13.2879" x2="11.4706" y2="12.7976">
                    <stop  offset="0" style="stop-color:#AE3E7D"/>
                    <stop  offset="1" style="stop-color:#EA2262"/>
                </linearGradient>
                <path fill="url(#SVGID_1_)" d="M11.1,11.8c-0.3,0.2-0.6,0.4-0.8,0.4c-0.4,0.1-0.9,0.2-1.3,0.2c-0.1,0-0.1,0-0.1,0.1l-0.1,0.7
                    C8.7,13.7,9,13.9,9.1,14c0.6,0.2,1.1,0.2,1.6,0.2c0.6,0,1.1-0.2,1.6-0.5l-0.9-2.3C11.3,11.6,11.2,11.7,11.1,11.8z"/>
                <path fill="#EA2262" d="M19.8,7.9c-0.2-1.2-0.8-2.5-1.5-3.5c-0.6-0.9-1.6-2-2.6-2.6c-2.1-1.5-4.5-2-6.7-1.7C6.7,0.3,4.8,1.1,3.2,2.6
                    C1.7,3.9,0.8,5.5,0.3,7c-0.4,1.5-0.4,3-0.2,4c0.2,1.1,0.7,2.5,0.7,2.5c0.1,0.3,0.3,0.4,0.3,0.4c0.4,0.2,1.1,0,1.6-0.6
                    c-0.1-0.6-0.2-1-0.2-1.3c-0.1-0.6-0.2-1.6-0.1-2.6c0-0.5,0.1-1.1,0.3-1.6C3,6.7,3.8,5.5,4.9,4.5c1.2-1.1,2.7-1.7,4.2-1.9
                    c0.5-0.1,1.5-0.1,2.7,0.2c0.3,0.1,1.3,0.3,2.5,1.1c0.8,0.6,1.5,1.3,2,2c0.5,0.7,1,1.8,1.1,2.6c0.2,0.9,0.2,1.9,0.1,2.9
                    c-0.2,1-0.5,1.9-1.1,2.7c-0.4,0.6-1.1,1.5-2,2.1c-0.8,0.6-1.7,1-2.6,1.2c-0.5,0.1-1,0.2-1.4,0.2c-0.4,0-1,0-1.4-0.1
                    c-0.6-0.1-0.7-0.3-0.9-0.5c0,0-0.1-0.2-0.1-0.6c0-3.7,0-2.7,0-4.6c0-0.5,0-1,0-1.5c0-0.7,0.1-1.2,0.6-1.8C8.8,8.3,9.4,8,9.9,8
                    c0.2,0,0.8,0,1.3,0.5c0.6,0.5,0.7,1.1,0.7,1.3c0.1,0.7-0.2,1.3-0.5,1.7c0.1,0.7,0.4,1.4,0.9,2.3c0.3-0.2,0.6-0.4,0.9-0.7
                    c0.6-0.6,1-1.4,1.1-2.2c0.1-0.9,0-2-0.5-2.8c-0.5-0.9-1.3-1.7-2.3-2.2c-1.1-0.4-1.9-0.4-3-0.2C7.9,6,7.3,6.3,6.6,6.9
                    c-0.4,0.5-0.8,1-1,1.6c-0.2,0.6-0.3,1.1-0.3,1.8c0,0.5,0,1,0,1.5c0,0.5,0,1,0,1.5c0,0.5,0,1,0,1.6c0,1,0,1.2,0,1.7
                    c0,0.3,0.1,0.7,0.2,1.1C5.7,18.3,6,18.8,6.3,19c0.3,0.3,0.7,0.6,1.1,0.8c0.9,0.4,2.1,0.5,3,0.4c0.6,0,1.3-0.1,1.9-0.3
                    c1.3-0.3,2.5-0.8,3.5-1.6c1.1-0.8,2.1-1.9,2.7-2.9c0.7-1.1,1.2-2.4,1.4-3.6C20.1,10.5,20.1,9.2,19.8,7.9z"/>
                </svg>
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'page/lister/" title="Lister">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 485.666 485.666" style="enable-background:new 0 0 485.666 485.666;" xml:space="preserve" style="height:16px">
                <g>
                    <g>
                        <path d="M485.015,425.339c-0.675-12.079-6.263-23.347-15.46-31.204L344.844,287.641c18.688-29.097,29.531-63.676,29.531-100.743    C374.375,83.839,290.535,0,187.478,0C84.415,0,0.581,83.839,0.581,186.898c0,103.058,83.834,186.897,186.897,186.897    c32.663,0,63.394-8.434,90.132-23.218l118.798,121.731c8.448,8.656,20.064,13.481,32.165,13.355    c12.084-0.125,23.602-5.192,31.868-14.021l12.691-13.574C481.4,449.236,485.694,437.413,485.015,425.339z M56.212,186.898    c0-72.379,58.893-131.266,131.266-131.266c72.378,0,131.26,58.887,131.26,131.266c0,72.377-58.882,131.265-131.26,131.265    C115.104,318.163,56.212,259.275,56.212,186.898z" fill="#ee1d62"/>
                        <path d="M258.34,162.595h-46.232v-46.559c0-13.487-10.952-24.459-24.432-24.459c-13.492,0-24.433,10.973-24.433,24.459v46.559    h-46.627c-13.485,0-24.458,10.957-24.458,24.433c0,13.485,10.973,24.432,24.458,24.432h46.627v46.299    c0,13.481,10.941,24.458,24.433,24.458c13.479,0,24.432-10.977,24.432-24.458V211.46h46.232c13.481,0,24.453-10.947,24.453-24.432    C282.793,173.552,271.821,162.595,258.34,162.595z" fill="#ee1d62"/>
                    </g>
                </g>
                </svg>
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'setup/template/" title="Templates">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 86.02 86.02" style="enable-background:new 0 0 86.02 86.02;" xml:space="preserve"  style="height:16px">
                <g>
                    <path d="M0.354,48.874l0.118,25.351c0.001,0.326,0.181,0.624,0.467,0.779l20.249,10.602c0.132,0.071,0.276,0.106,0.421,0.106   c0.001,0,0.001,0,0.002,0c0.061,0.068,0.129,0.133,0.211,0.182c0.14,0.084,0.297,0.126,0.455,0.126   c0.146,0,0.291-0.035,0.423-0.106l19.992-10.842c0.183-0.099,0.315-0.261,0.392-0.445c0.081,0.155,0.203,0.292,0.364,0.379   l20.248,10.602c0.132,0.071,0.277,0.106,0.422,0.106c0.001,0,0.001,0,0.002,0c0.062,0.068,0.129,0.133,0.21,0.182   c0.142,0.084,0.299,0.126,0.456,0.126c0.146,0,0.29-0.035,0.422-0.106L85.2,75.071c0.287-0.154,0.467-0.456,0.467-0.783V47.911   c0-0.008-0.004-0.016-0.004-0.022c0-0.006,0.002-0.013,0.002-0.021c-0.001-0.023-0.01-0.049-0.014-0.072   c-0.007-0.05-0.014-0.098-0.027-0.146c-0.011-0.031-0.023-0.062-0.038-0.093c-0.019-0.042-0.037-0.082-0.062-0.12   c-0.019-0.03-0.04-0.058-0.062-0.084c-0.028-0.034-0.059-0.066-0.092-0.097c-0.025-0.023-0.054-0.045-0.083-0.066   c-0.02-0.012-0.034-0.03-0.056-0.043c-0.02-0.011-0.041-0.017-0.062-0.025c-0.019-0.01-0.03-0.022-0.049-0.029l-20.603-9.978   c-0.082-0.034-0.17-0.038-0.257-0.047V10.865c0-0.007-0.002-0.015-0.002-0.022c-0.001-0.007,0.001-0.013,0.001-0.02   c-0.001-0.025-0.012-0.049-0.015-0.073c-0.007-0.049-0.014-0.098-0.027-0.145c-0.01-0.032-0.024-0.063-0.038-0.093   c-0.02-0.042-0.036-0.083-0.062-0.12c-0.02-0.03-0.041-0.057-0.062-0.084c-0.028-0.034-0.058-0.067-0.091-0.097   c-0.025-0.023-0.055-0.045-0.083-0.065c-0.021-0.014-0.035-0.032-0.056-0.045c-0.021-0.011-0.042-0.016-0.062-0.026   c-0.019-0.009-0.031-0.021-0.048-0.027L43.118,0.07c-0.24-0.102-0.512-0.093-0.746,0.025L22.009,10.71   c-0.299,0.151-0.487,0.456-0.489,0.79c0,0.006,0.002,0.011,0.002,0.016c-0.037,0.099-0.063,0.202-0.063,0.312l0.118,25.233   c-0.106,0.011-0.213,0.03-0.311,0.079L0.903,47.755c-0.298,0.15-0.487,0.456-0.489,0.791c0,0.005,0.003,0.009,0.003,0.015   C0.379,48.659,0.353,48.764,0.354,48.874z M61.321,10.964L43.372,21l-19.005-9.485l18.438-9.646L61.321,10.964z M62.486,37.008   l-18.214,9.586V22.535l18.214-10.18V37.008z M65.674,59.58l18.214-10.179v24.355l-18.214,9.883V59.58z M45.77,48.559l18.438-9.646   l18.515,9.099L64.775,58.045L45.77,48.559z M23.165,59.58L41.38,49.402v24.355l-18.215,9.882V59.58z M3.262,48.559L21.7,38.913   l18.515,9.099L22.266,58.045L3.262,48.559z" fill="#ee1d62"/>
                </g>
                </svg>
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'setup/field/" title="Fields">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 82.626 82.627" style="enable-background:new 0 0 82.626 82.627;" xml:space="preserve"  style="height:16px">
                <g>
                    <path d="M5.206,19.956l0.199,42.771c0.003,0.55,0.306,1.054,0.789,1.314l34.161,17.887c0.223,0.119,0.467,0.179,0.711,0.179   c0.001,0,0.002,0,0.003,0c0.103,0.117,0.218,0.227,0.355,0.309c0.236,0.141,0.502,0.212,0.769,0.212   c0.246,0,0.49-0.061,0.712-0.181l33.729-18.292c0.484-0.263,0.787-0.77,0.787-1.319v-44.5c0-0.013-0.005-0.025-0.005-0.039   c-0.001-0.011,0.003-0.021,0.003-0.033c-0.002-0.043-0.019-0.082-0.022-0.124c-0.013-0.082-0.022-0.164-0.047-0.243   c-0.018-0.055-0.041-0.104-0.064-0.157c-0.031-0.07-0.062-0.139-0.104-0.203c-0.031-0.05-0.068-0.095-0.105-0.141   c-0.047-0.058-0.096-0.112-0.152-0.163c-0.044-0.04-0.091-0.076-0.141-0.111c-0.032-0.022-0.059-0.053-0.094-0.073   c-0.032-0.02-0.069-0.028-0.104-0.045c-0.029-0.015-0.052-0.036-0.081-0.049L41.747,0.118c-0.405-0.171-0.864-0.155-1.258,0.042   L6.131,18.071c-0.504,0.254-0.822,0.77-0.825,1.333c0,0.009,0.004,0.017,0.004,0.025C5.249,19.596,5.205,19.772,5.206,19.956z    M72.456,18.501l-30.28,16.93L10.111,19.425L41.218,3.151L72.456,18.501z M43.692,78.61V38.021l30.729-17.173v41.09L43.692,78.61z" fill="#ee1d62"/>
                </g>
                </svg>
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'setup/logs/" title="Logs">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 433.494 433.494" style="enable-background:new 0 0 433.494 433.494;" xml:space="preserve" style="height:16px">
                <g>
                    <g id="Layer_2_2_">
                        <polygon points="353.763,379.942 279.854,234.57 322.024,250.717 253.857,116.637 276.286,127.997 216.747,0 157.209,127.997     179.64,116.636 111.471,250.717 153.642,234.569 79.731,379.942 200.872,337.52 200.872,433.494 232.624,433.494 232.624,337.518       " fill="#ee1d62"/>
                    </g>
                </g>
                </svg>
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'access/users/" title="Users">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 80.13 80.13" style="enable-background:new 0 0 80.13 80.13;" xml:space="preserve" style="height:16px">
                <g>
                    <path d="M48.355,17.922c3.705,2.323,6.303,6.254,6.776,10.817c1.511,0.706,3.188,1.112,4.966,1.112   c6.491,0,11.752-5.261,11.752-11.751c0-6.491-5.261-11.752-11.752-11.752C53.668,6.35,48.453,11.517,48.355,17.922z M40.656,41.984   c6.491,0,11.752-5.262,11.752-11.752s-5.262-11.751-11.752-11.751c-6.49,0-11.754,5.262-11.754,11.752S34.166,41.984,40.656,41.984   z M45.641,42.785h-9.972c-8.297,0-15.047,6.751-15.047,15.048v12.195l0.031,0.191l0.84,0.263   c7.918,2.474,14.797,3.299,20.459,3.299c11.059,0,17.469-3.153,17.864-3.354l0.785-0.397h0.084V57.833   C60.688,49.536,53.938,42.785,45.641,42.785z M65.084,30.653h-9.895c-0.107,3.959-1.797,7.524-4.47,10.088   c7.375,2.193,12.771,9.032,12.771,17.11v3.758c9.77-0.358,15.4-3.127,15.771-3.313l0.785-0.398h0.084V45.699   C80.13,37.403,73.38,30.653,65.084,30.653z M20.035,29.853c2.299,0,4.438-0.671,6.25-1.814c0.576-3.757,2.59-7.04,5.467-9.276   c0.012-0.22,0.033-0.438,0.033-0.66c0-6.491-5.262-11.752-11.75-11.752c-6.492,0-11.752,5.261-11.752,11.752   C8.283,24.591,13.543,29.853,20.035,29.853z M30.589,40.741c-2.66-2.551-4.344-6.097-4.467-10.032   c-0.367-0.027-0.73-0.056-1.104-0.056h-9.971C6.75,30.653,0,37.403,0,45.699v12.197l0.031,0.188l0.84,0.265   c6.352,1.983,12.021,2.897,16.945,3.185v-3.683C17.818,49.773,23.212,42.936,30.589,40.741z" fill="#ee1d62"/>
                </g>
                </svg>
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'access/roles/" title="Roles">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 548.172 548.172" style="enable-background:new 0 0 548.172 548.172;" xml:space="preserve" style="height:16px">
                <g>
                    <g>
                        <path d="M333.186,376.438c0-1.902-0.668-3.806-1.999-5.708c-10.66-12.758-19.223-23.702-25.697-32.832    c3.997-7.803,7.043-15.037,9.131-21.693l44.255-6.852c1.718-0.194,3.241-1.19,4.572-2.994c1.331-1.816,1.991-3.668,1.991-5.571    v-52.822c0-2.091-0.66-3.949-1.991-5.564s-2.95-2.618-4.853-2.993l-43.4-6.567c-2.098-6.473-5.331-14.281-9.708-23.413    c2.851-4.19,7.139-9.902,12.85-17.131c5.709-7.234,9.713-12.371,11.991-15.417c1.335-1.903,1.999-3.713,1.999-5.424    c0-5.14-13.706-20.367-41.107-45.683c-1.902-1.52-3.901-2.281-6.002-2.281c-2.279,0-4.182,0.659-5.712,1.997L245.815,150.9    c-7.801-3.996-14.939-6.945-21.411-8.854l-6.567-43.68c-0.187-1.903-1.14-3.571-2.853-4.997c-1.714-1.427-3.617-2.142-5.713-2.142    h-53.1c-4.377,0-7.232,2.284-8.564,6.851c-2.286,8.757-4.473,23.416-6.567,43.968c-8.183,2.664-15.511,5.71-21.982,9.136    l-32.832-25.693c-1.903-1.335-3.901-1.997-5.996-1.997c-3.621,0-11.138,5.614-22.557,16.846    c-11.421,11.228-19.229,19.698-23.413,25.409c-1.334,1.525-1.997,3.428-1.997,5.712c0,1.711,0.662,3.614,1.997,5.708    c10.657,12.756,19.221,23.7,25.694,32.832c-3.996,7.808-7.04,15.037-9.132,21.698l-44.255,6.848    c-1.715,0.19-3.236,1.188-4.57,2.993C0.666,243.35,0,245.203,0,247.105v52.819c0,2.095,0.666,3.949,1.997,5.564    c1.334,1.622,2.95,2.525,4.857,2.714l43.396,6.852c2.284,7.23,5.618,15.037,9.995,23.411c-3.046,4.191-7.517,9.999-13.418,17.418    c-5.905,7.427-9.805,12.471-11.707,15.133c-1.332,1.903-1.999,3.717-1.999,5.421c0,5.147,13.706,20.369,41.114,45.687    c1.903,1.519,3.899,2.275,5.996,2.275c2.474,0,4.377-0.66,5.708-1.995l33.689-25.406c7.801,3.997,14.939,6.943,21.413,8.847    l6.567,43.684c0.188,1.902,1.142,3.572,2.853,4.996c1.713,1.427,3.616,2.139,5.711,2.139h53.1c4.38,0,7.233-2.282,8.566-6.851    c2.284-8.949,4.471-23.698,6.567-44.256c7.611-2.275,14.938-5.235,21.982-8.846l32.833,25.693    c1.903,1.335,3.901,1.995,5.996,1.995c3.617,0,11.091-5.66,22.415-16.991c11.32-11.317,19.175-19.842,23.555-25.55    C332.518,380.53,333.186,378.724,333.186,376.438z M234.397,325.626c-14.272,14.27-31.499,21.408-51.673,21.408    c-20.179,0-37.406-7.139-51.678-21.408c-14.274-14.277-21.412-31.505-21.412-51.68c0-20.174,7.138-37.401,21.412-51.675    c14.272-14.275,31.5-21.411,51.678-21.411c20.174,0,37.401,7.135,51.673,21.411c14.277,14.274,21.413,31.501,21.413,51.675    C255.81,294.121,248.675,311.349,234.397,325.626z" fill="#ee1d62"/>
                        <path d="M505.628,391.29c-2.471-5.517-5.329-10.465-8.562-14.846c9.709-21.512,14.558-34.646,14.558-39.402    c0-0.753-0.373-1.424-1.14-1.995c-22.846-13.322-34.643-19.985-35.405-19.985l-1.711,0.574    c-7.803,7.807-16.563,18.463-26.266,31.977c-3.805-0.379-6.656-0.574-8.559-0.574c-1.909,0-4.76,0.195-8.569,0.574    c-2.655-4-7.61-10.427-14.842-19.273c-7.23-8.846-11.611-13.277-13.134-13.277c-0.38,0-3.234,1.522-8.566,4.575    c-5.328,3.046-10.943,6.276-16.844,9.709c-5.906,3.433-9.229,5.328-9.992,5.711c-0.767,0.568-1.144,1.239-1.144,1.992    c0,4.764,4.853,17.888,14.559,39.402c-3.23,4.381-6.089,9.329-8.562,14.842c-28.363,2.851-42.544,5.805-42.544,8.85v39.968    c0,3.046,14.181,5.996,42.544,8.85c2.279,5.141,5.137,10.089,8.562,14.839c-9.706,21.512-14.559,34.646-14.559,39.402    c0,0.76,0.377,1.431,1.144,1.999c23.216,13.514,35.022,20.27,35.402,20.27c1.522,0,5.903-4.473,13.134-13.419    c7.231-8.948,12.18-15.413,14.842-19.41c3.806,0.373,6.66,0.564,8.569,0.564c1.902,0,4.754-0.191,8.559-0.564    c2.659,3.997,7.611,10.462,14.842,19.41c7.231,8.946,11.608,13.419,13.135,13.419c0.38,0,12.187-6.759,35.405-20.27    c0.767-0.568,1.14-1.235,1.14-1.999c0-4.757-4.855-17.891-14.558-39.402c3.426-4.75,6.279-9.698,8.562-14.839    c28.362-2.854,42.544-5.804,42.544-8.85v-39.968C548.172,397.098,533.99,394.144,505.628,391.29z M464.37,445.962    c-7.128,7.139-15.745,10.715-25.834,10.715c-10.092,0-18.705-3.576-25.837-10.715c-7.139-7.139-10.712-15.748-10.712-25.837    c0-9.894,3.621-18.466,10.855-25.693c7.23-7.231,15.797-10.849,25.693-10.849c9.894,0,18.466,3.614,25.7,10.849    c7.228,7.228,10.849,15.8,10.849,25.693C475.078,430.214,471.512,438.823,464.37,445.962z" fill="#ee1d62"/>
                        <path d="M505.628,98.931c-2.471-5.52-5.329-10.468-8.562-14.849c9.709-21.505,14.558-34.639,14.558-39.397    c0-0.758-0.373-1.427-1.14-1.999c-22.846-13.323-34.643-19.984-35.405-19.984l-1.711,0.57    c-7.803,7.808-16.563,18.464-26.266,31.977c-3.805-0.378-6.656-0.57-8.559-0.57c-1.909,0-4.76,0.192-8.569,0.57    c-2.655-3.997-7.61-10.42-14.842-19.27c-7.23-8.852-11.611-13.276-13.134-13.276c-0.38,0-3.234,1.521-8.566,4.569    c-5.328,3.049-10.943,6.283-16.844,9.71c-5.906,3.428-9.229,5.33-9.992,5.708c-0.767,0.571-1.144,1.237-1.144,1.999    c0,4.758,4.853,17.893,14.559,39.399c-3.23,4.38-6.089,9.327-8.562,14.847c-28.363,2.853-42.544,5.802-42.544,8.848v39.971    c0,3.044,14.181,5.996,42.544,8.848c2.279,5.137,5.137,10.088,8.562,14.847c-9.706,21.51-14.559,34.639-14.559,39.399    c0,0.757,0.377,1.426,1.144,1.997c23.216,13.513,35.022,20.27,35.402,20.27c1.522,0,5.903-4.471,13.134-13.418    c7.231-8.947,12.18-15.415,14.842-19.414c3.806,0.378,6.66,0.571,8.569,0.571c1.902,0,4.754-0.193,8.559-0.571    c2.659,3.999,7.611,10.466,14.842,19.414c7.231,8.947,11.608,13.418,13.135,13.418c0.38,0,12.187-6.757,35.405-20.27    c0.767-0.571,1.14-1.237,1.14-1.997c0-4.76-4.855-17.889-14.558-39.399c3.426-4.759,6.279-9.707,8.562-14.847    c28.362-2.853,42.544-5.804,42.544-8.848v-39.971C548.172,104.737,533.99,101.787,505.628,98.931z M464.37,153.605    c-7.128,7.139-15.745,10.708-25.834,10.708c-10.092,0-18.705-3.569-25.837-10.708c-7.139-7.135-10.712-15.749-10.712-25.837    c0-9.897,3.621-18.464,10.855-25.697c7.23-7.233,15.797-10.85,25.693-10.85c9.894,0,18.466,3.621,25.7,10.85    c7.228,7.232,10.849,15.8,10.849,25.697C475.078,137.856,471.512,146.47,464.37,153.605z" fill="#ee1d62"/>
                    </g>
                </g>
                </svg>
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'access/permissions/" title="Permissions">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 268.765 268.765" style="enable-background:new 0 0 268.765 268.765;" xml:space="preserve" width="16px" height="16px" style="height:16px">
                <g id="Settings">
                    <g>
                        <path style="fill-rule:evenodd;clip-rule:evenodd;" d="M267.92,119.461c-0.425-3.778-4.83-6.617-8.639-6.617    c-12.315,0-23.243-7.231-27.826-18.414c-4.682-11.454-1.663-24.812,7.515-33.231c2.889-2.641,3.24-7.062,0.817-10.133    c-6.303-8.004-13.467-15.234-21.289-21.5c-3.063-2.458-7.557-2.116-10.213,0.825c-8.01,8.871-22.398,12.168-33.516,7.529    c-11.57-4.867-18.866-16.591-18.152-29.176c0.235-3.953-2.654-7.39-6.595-7.849c-10.038-1.161-20.164-1.197-30.232-0.08    c-3.896,0.43-6.785,3.786-6.654,7.689c0.438,12.461-6.946,23.98-18.401,28.672c-10.985,4.487-25.272,1.218-33.266-7.574    c-2.642-2.896-7.063-3.252-10.141-0.853c-8.054,6.319-15.379,13.555-21.74,21.493c-2.481,3.086-2.116,7.559,0.802,10.214    c9.353,8.47,12.373,21.944,7.514,33.53c-4.639,11.046-16.109,18.165-29.24,18.165c-4.261-0.137-7.296,2.723-7.762,6.597    c-1.182,10.096-1.196,20.383-0.058,30.561c0.422,3.794,4.961,6.608,8.812,6.608c11.702-0.299,22.937,6.946,27.65,18.415    c4.698,11.454,1.678,24.804-7.514,33.23c-2.875,2.641-3.24,7.055-0.817,10.126c6.244,7.953,13.409,15.19,21.259,21.508    c3.079,2.481,7.559,2.131,10.228-0.81c8.04-8.893,22.427-12.184,33.501-7.536c11.599,4.852,18.895,16.575,18.181,29.167    c-0.233,3.955,2.67,7.398,6.595,7.85c5.135,0.599,10.301,0.898,15.481,0.898c4.917,0,9.835-0.27,14.752-0.817    c3.897-0.43,6.784-3.786,6.653-7.696c-0.451-12.454,6.946-23.973,18.386-28.657c11.059-4.517,25.286-1.211,33.281,7.572    c2.657,2.89,7.047,3.239,10.142,0.848c8.039-6.304,15.349-13.534,21.74-21.494c2.48-3.079,2.13-7.559-0.803-10.213    c-9.353-8.47-12.388-21.946-7.529-33.524c4.568-10.899,15.612-18.217,27.491-18.217l1.662,0.043    c3.853,0.313,7.398-2.655,7.865-6.588C269.044,139.917,269.058,129.639,267.92,119.461z M134.595,179.491    c-24.718,0-44.824-20.106-44.824-44.824c0-24.717,20.106-44.824,44.824-44.824c24.717,0,44.823,20.107,44.823,44.824    C179.418,159.385,159.312,179.491,134.595,179.491z" fill="#ee1d62"/>
                    </g>
                </g>
                </svg>
            </a>&nbsp;
        </div>
        ';

        return $out;
    }

}
