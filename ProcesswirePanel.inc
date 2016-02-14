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
            <img style="height: 15px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAz5JREFUeNrEmL1y00AQgHWOB+hQQ8lE1BQoBTVKRWn7CWwNNQNuaO10dMFPIPsJojwAE6WkiuAFrAwFRQrUx1js2asZcd493UmO2Zkd2Rr79N3+64TTUu6ehwFcPFRVMtD02c8obbq+aADkwqUP2sOrieSgCeglwM4fBBDBPoJ+AHVbGF3CzkC/AGy+F0CAk2CTlmCU+8cAGTcGRKtdgAbOw8kcQXMrQIDzEc4zfFBC3DPdmEyiUwpSaOCualya4+4XuizFtYago5r1SEjBuPVGYzmrILdMsh1ICvBK4xrpxhAWyFrUTQ9Dx+diEtYPSUDM1nOTP+6hwEfodkoGZXYLZWc3jPn3CmcAKV38Qrq6W7nJ1bm4Dg43FyhxK8Mg0YWDXLfy36qU8ToVlQD+rduJJkPPa8pJjHUu02yO8tzm2R38wsXCWAM3woXrap3s10v8PWXFDKuCQ1ixXwL2qFbENXZ8WGQZchEHKUsWWkyVXkdT8Wea8SpqmBcRhoVqxbLo71i/Q/2hEjuUTGq6QYJXTrgytqBudpjMzamgxs0ETDLJDnACurnC9xPGbQEmhmrFlAPkLMEFPCUSKiEeOLBcJzEF5GrXK6ZOpkyGcu4+Ng1aDvCWuU+Fw3eDCVoV39QwHefhxWYK9yhAaodP99RrfcZa18YuZuLHbwv36/idq6mXXBLuPLdbyZ6goVv+kR8v33uP71f91f1qcrReu4LuUDFjbZcDvFQArS349fWniyerDZjzSAhnDepILYqd/s4sMdRlMbcjY/kjhL+G8bJAuEKy7f4s1LxmUrUx6VQmikQpDbkNIAC5G7jtZ4QU1fUGNcMH9Q60qA6sIY5dOU7QuZ2TRQhgw2JrSa9wRLYWTnq0DZ9YM7a5TH+Xv4+7ylw2bZocb799jjUDhlMzPHhMh8oPUah1dXKkeSc5O1QncRoMvbNymur+BzAX3cpN1/I8carWwUPBldO47tRiQBXqNvLGwGJ9PPLwa6aeU3VQtgW8JiZqOSEvsY7eKjOfb9iVSrjU6nyQeYdd7tnzKRbxzOp8sOGZiq2cVRPCZqLWybjmrc1E5nhiUdsYRItSMcHzE1PJsNPMbI7v/gowABRNYFmCWV57AAAAAElFTkSuQmCC" />
            ProcessWire
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

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';

        // panel title
        $out = '
        <h1>ProcessWire Debug Info</h1>';


        /**
         * Custom PW panel sections
         */

        if(wire('page') && wire('page')->process != 'ProcessPageEdit' && wire('page')->process != 'ProcessPageList') {

            // Page info
            $currentPageInfo = '
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
                    $currentPageInfo .= '
                    <tr>
                        <th>Prev Sibling</th>
                        <td><a href="'.wire('page')->prev->url.'">&lt; '.wire('page')->prev->name.'</a></td>
                    </tr>';
                }
                if(wire('page')->next->id && wire('page')->next->viewable()) {
                    $currentPageInfo .= '
                    <tr>
                        <th>Next Sibling</th>
                        <td><a href="'.wire('page')->next->url.'">'.wire('page')->next->name.' &gt;</a></td>
                    </tr>';
                }
                $currentPageInfo .= '
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

            // Current Page Object
            $currentPageObject = Dumper::toHtml(wire('page'));

            // Template info
            $currentTemplateInfo = '
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
                    <th>Page Numbers</th>
                    <td>'.(wire('page')->template->allowPageNum ? 'Enabled' : 'Disabled').'</td>
                </tr>
                <tr>
                    <th>URL Segments</th>
                    <td>'.(wire('page')->template->urlSegments ? 'Enabled' : 'Disabled').'</td>
                </tr>
            </table>';

            // Current Page Fields List
            $currentPageFieldsList = $this->sectionHeader(array('ID', 'Name', 'Label', 'Type'));
            foreach(wire('page')->fields as $f) {
                $currentPageFieldsList .= "\n<tr>" .
                    "<td>$f->id</td>" .
                    '<td><a href="'.wire('config')->urls->admin.'setup/field/edit?id='.$f->id.'" target="_blank">'.$f->name.'</a></td>' .
                    "<td>$f->label</td>" .
                    "<td>$f->type</td>" .
                    "</tr>";
            }
            $currentPageFieldsList .= $sectionEnd;

            // Current Page Fields Object
            $currentPageFieldsObject = Dumper::toHtml(wire('page')->fields);

        }

        // Pages Loaded
        $pagesInfo = $this->sectionHeader(array('ID', 'Path', 'Type', 'Loader'));
        foreach(wire('pages')->getCache() as $p) {
            $parts = explode('/', trim($p->path, '/'));
            $name = array_pop($parts);
            $path = implode('/', $parts) . "/<b>$name</b>/";
            if($path[0] != '/') $path = "/$path";
            $pagesInfo .= "\n<tr>" .
                "<td>$p->id</td>" .
                "<td>$path</td>" .
                "<td>" . wireClassName($p) . "</td>" .
                "<td>$p->_debug_loader</td>" .
                "</tr>";
        }
        $pagesInfo .= $sectionEnd;

        // API Variables
        $apiVariables = $this->sectionHeader(array('Name', 'Class'));
        foreach(wire('fuel') as $key => $value) {
            if(!is_object($value)) continue;
            $apiVariables .= "\n<tr><td><a target='_blank' href='https://processwire.com/api/variables/$key/'>\$$key</a></td>" .
                "<td>" . get_class($value) . "</td></tr>";
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
        $hooksCalled .= $sectionEnd;

        // Database Queries
        $databaseQueries = $this->sectionHeader(array('Order', 'Query'));
        foreach(wire('database')->queryLog() as $n => $sql) $databaseQueries .= "\n<tr><th>$n</th><td>$sql</td></tr>";
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
        <p><strong>Current User Roles</strong>
        <ol>';
        foreach(wire('user')->roles as $role) $userDetails .= "\n<li>{$role->name}</li>";
        $userDetails .= '
        </ol>
        </p>
        <p><strong>Current User Permissions</strong>
        <ol>';
        foreach(wire('user')->getPermissions() as $permission) $userDetails .= "\n<li>{$permission->name}</li>";
        $userDetails .= '</ol>
        </p>
        <p><strong>Current User Permissions on this page</strong>
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
        $autoload = $this->sectionHeader(array('Class', 'File/Details'));
        foreach(wire('classLoader')->getDebugLog() as $className => $classFile) {
            $className = wire('sanitizer')->entities($className);
            $classFile = wire('sanitizer')->entities($classFile);
            $autoload .= "<tr><th width='40%'>$className</th><td>$classFile</td></tr>";
        }
        $autoload .= $sectionEnd;

        // Load all the panel sections
        $out = '';
        if(wire('page') && wire('page')->process != 'ProcessPageEdit' && wire('page')->process != 'ProcessPageList') {
            $out .= '
            <a href="#" rel="#current-page-info" class="tracy-toggle">Current Page Info</a>
            <div id="current-page-info">'.$currentPageInfo.'</div><br />

            <a href="#" rel="#current-page-object" class="tracy-toggle tracy-collapsed">Current Page Object</a>
            <div id="current-page-object" class="tracy-collapsed">'.$currentPageObject.'</div><br />

            <a href="#" rel="#current-template-info" class="tracy-toggle tracy-collapsed">Current Template Info</a>
            <div id="current-template-info" class="tracy-collapsed">'.$currentTemplateInfo.'</div><br />

            <a href="#" rel="#current-page-fields-list" class="tracy-toggle tracy-collapsed">Current Page Fields List</a>
            <div id="current-page-fields-list" class="tracy-collapsed">'.$currentPageFieldsList.'</div><br />

            <a href="#" rel="#current-page-fields-object" class="tracy-toggle tracy-collapsed">Current Page Fields Object</a>
            <div id="current-page-fields-object" class="tracy-collapsed">'.$currentPageFieldsObject.'</div><br />';
        }

        $out .= '
        <a href="#" rel="#pages-loaded" class="tracy-toggle tracy-collapsed">Pages Loaded</a>
        <div id="pages-loaded" class="tracy-collapsed">'.$pagesInfo.'</div><br />

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
        <div id="cache-details" class="tracy-collapsed">'.$cacheDetails.'</div><br />

        <a href="#" rel="#autoload" class="tracy-toggle tracy-collapsed">Autoload</a>
        <div id="autoload" class="tracy-collapsed">'.$autoload.'</div><br />
        ';

        return $out;
    }

}
