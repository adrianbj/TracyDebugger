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
            $currentPageObject = Dumper::toHtml(wire('page'), array(Dumper::COLLAPSE => false));

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
            $currentPageFieldsObject = Dumper::toHtml(wire('page')->fields, array(Dumper::COLLAPSE => false));

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
        $out = '<h1><a title="ProcessWire Admin" href="'.wire('config')->urls->admin.'" target="_blank"><img style="height: 20px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAz5JREFUeNrEmL1y00AQgHWOB+hQQ8lE1BQoBTVKRWn7CWwNNQNuaO10dMFPIPsJojwAE6WkiuAFrAwFRQrUx1js2asZcd493UmO2Zkd2Rr79N3+64TTUu6ehwFcPFRVMtD02c8obbq+aADkwqUP2sOrieSgCeglwM4fBBDBPoJ+AHVbGF3CzkC/AGy+F0CAk2CTlmCU+8cAGTcGRKtdgAbOw8kcQXMrQIDzEc4zfFBC3DPdmEyiUwpSaOCualya4+4XuizFtYago5r1SEjBuPVGYzmrILdMsh1ICvBK4xrpxhAWyFrUTQ9Dx+diEtYPSUDM1nOTP+6hwEfodkoGZXYLZWc3jPn3CmcAKV38Qrq6W7nJ1bm4Dg43FyhxK8Mg0YWDXLfy36qU8ToVlQD+rduJJkPPa8pJjHUu02yO8tzm2R38wsXCWAM3woXrap3s10v8PWXFDKuCQ1ixXwL2qFbENXZ8WGQZchEHKUsWWkyVXkdT8Wea8SpqmBcRhoVqxbLo71i/Q/2hEjuUTGq6QYJXTrgytqBudpjMzamgxs0ETDLJDnACurnC9xPGbQEmhmrFlAPkLMEFPCUSKiEeOLBcJzEF5GrXK6ZOpkyGcu4+Ng1aDvCWuU+Fw3eDCVoV39QwHefhxWYK9yhAaodP99RrfcZa18YuZuLHbwv36/idq6mXXBLuPLdbyZ6goVv+kR8v33uP71f91f1qcrReu4LuUDFjbZcDvFQArS349fWniyerDZjzSAhnDepILYqd/s4sMdRlMbcjY/kjhL+G8bJAuEKy7f4s1LxmUrUx6VQmikQpDbkNIAC5G7jtZ4QU1fUGNcMH9Q60qA6sIY5dOU7QuZ2TRQhgw2JrSa9wRLYWTnq0DZ9YM7a5TH+Xv4+7ylw2bZocb799jjUDhlMzPHhMh8oPUah1dXKkeSc5O1QncRoMvbNymur+BzAX3cpN1/I8carWwUPBldO47tRiQBXqNvLGwGJ9PPLwa6aeU3VQtgW8JiZqOSEvsY7eKjOfb9iVSrjU6nyQeYdd7tnzKRbxzOp8sOGZiq2cVRPCZqLWybjmrc1E5nhiUdsYRItSMcHzE1PJsNPMbI7v/gowABRNYFmCWV57AAAAAElFTkSuQmCC" /> ProcessWire Info</a></h1>';
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

        <div style="border-top:1px solid #ccc; margin-top:10px; padding-top:5px">
            <a href="'.wire('config')->urls->admin.'">
                <img style="height: 16px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAz5JREFUeNrEmL1y00AQgHWOB+hQQ8lE1BQoBTVKRWn7CWwNNQNuaO10dMFPIPsJojwAE6WkiuAFrAwFRQrUx1js2asZcd493UmO2Zkd2Rr79N3+64TTUu6ehwFcPFRVMtD02c8obbq+aADkwqUP2sOrieSgCeglwM4fBBDBPoJ+AHVbGF3CzkC/AGy+F0CAk2CTlmCU+8cAGTcGRKtdgAbOw8kcQXMrQIDzEc4zfFBC3DPdmEyiUwpSaOCualya4+4XuizFtYago5r1SEjBuPVGYzmrILdMsh1ICvBK4xrpxhAWyFrUTQ9Dx+diEtYPSUDM1nOTP+6hwEfodkoGZXYLZWc3jPn3CmcAKV38Qrq6W7nJ1bm4Dg43FyhxK8Mg0YWDXLfy36qU8ToVlQD+rduJJkPPa8pJjHUu02yO8tzm2R38wsXCWAM3woXrap3s10v8PWXFDKuCQ1ixXwL2qFbENXZ8WGQZchEHKUsWWkyVXkdT8Wea8SpqmBcRhoVqxbLo71i/Q/2hEjuUTGq6QYJXTrgytqBudpjMzamgxs0ETDLJDnACurnC9xPGbQEmhmrFlAPkLMEFPCUSKiEeOLBcJzEF5GrXK6ZOpkyGcu4+Ng1aDvCWuU+Fw3eDCVoV39QwHefhxWYK9yhAaodP99RrfcZa18YuZuLHbwv36/idq6mXXBLuPLdbyZ6goVv+kR8v33uP71f91f1qcrReu4LuUDFjbZcDvFQArS349fWniyerDZjzSAhnDepILYqd/s4sMdRlMbcjY/kjhL+G8bJAuEKy7f4s1LxmUrUx6VQmikQpDbkNIAC5G7jtZ4QU1fUGNcMH9Q60qA6sIY5dOU7QuZ2TRQhgw2JrSa9wRLYWTnq0DZ9YM7a5TH+Xv4+7ylw2bZocb799jjUDhlMzPHhMh8oPUah1dXKkeSc5O1QncRoMvbNymur+BzAX3cpN1/I8carWwUPBldO47tRiQBXqNvLGwGJ9PPLwa6aeU3VQtgW8JiZqOSEvsY7eKjOfb9iVSrjU6nyQeYdd7tnzKRbxzOp8sOGZiq2cVRPCZqLWybjmrc1E5nhiUdsYRItSMcHzE1PJsNPMbI7v/gowABRNYFmCWV57AAAAAElFTkSuQmCC" />
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'page/lister/">
                <img style="height: 16px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAABaAAAAWgBwI7h9AAAAB3RJTUUH4AIPFTY303+n9QAABHJJREFUSMe1VG1MU2cYPe+lQkkUS2G04r1lVSDqcKk62aLiVmVjoiFMUafFYVtG1AyzBkVBF7Kgxi8kOtmiuGrcXAyCMKNxqIDOjsQxcdmyzU8E7VQk/bCMGml7n/26daFr2JLt/Lp57rnPe+7znPMyDAERUEEc596kybKPMRiI0QqaazQiB0W0TadjR+Bj+6Oj8S39DHVXFxWw12FobJT5Arf8q6qrYzY9iDna5HDgH4JJD263RrN6dWysuIu2PfM0NKAEZYjS64PESvoEP/X20mJw2Pr0KfrZZfwoCJiO47gREYFqfIC4vj58iDzUL1yojLs/3nrXZhtOACf9sVgiLnq2ob7++cFkQ5zNxhVyM1nP1Kmx1fZc6w9qtXKGPceapdWOKPMny26r1ewjLKcZu3ejAF8jNz4ejFzA6dN9VxI9749KTR12BC4NrzWtNxicTkEwmYhcMv68ccBmI3FS3eK8yMggbw//sunL4mKXTrhgmtPePrSPU8e7jZ6SEqmP8wJ/2dhx6tSwE0A+2mluQYFUCBzHG3TMYmHcr0tO1A8OBr1hZX5MmTKFdqAZWenpQxvFXrMrNKOqq9luGGn7zZsIsFOsNTv7j3zVmysSEhLCryCCHWUtOh3OIA2LHI74PPuII0UdHY67QolxjcXiWssfMvbU1eEgbYddr8d4fIdZHCfVXeXCObO6tvbJE43m8ZrRoykRPGrPnZO84V8etSBSP3lyWAGsklKQFxXF3sMk/Ob1Bl/U0jrkzJtHBrYWyMxEK1R4lpgIJe7jc8aC9YtYQlnZ2f5brMf7qUrFDmA+V/m8T0AvZomcTBZ2B84DfI0prbPT2SEsNaUGAn1bNN8UFY0ZE8JLEyaZfrFanc3CetMOvz9sPzNvMXW3tEhecLs1GmPPuHHhJxCBBGgbGzEe7ZjFcREKuudLKi8PYb5ELyC6vx8K1NC7Hk+ImacLK1duzchAGbuC3/V61FAtMjs7FYp79w4ndXWFnYCLkqiAFArXAt5t9Dx4EExDOf/QrN68mUSggmQyx5nkfYbimBjPhLH1K++kpAS/L+cfmtWzZ7ssfJPplUePgikoFTpNun37hktB8CJyyHmLuSo9nZ1kzdR69ixeQz9USiWuUw2WdnczI1w00NwMK17lyrxe8rElhGnTwGMkrBkZkjdCDjiEZBiqqhTr77cJc0pLGQM+ZqIYIkCCe1NSVOF1rZZGBtaI3p07ScvG0djcXLyFnazsb8x0gjbi+2vX2Fd4EX1yOTWyVZg/cWIIrxLJONLUNPj2oMwXn5+vzuo9/8XjgYEQAUPxpJ23mKuUSvE0SrErLU2cyZbTMbk8Yia7Q8m3b0s77r2gLSxMU6kiK33viH1tbWGFVFAbVVy9KnazBrY/J2dYAf8WUopkXmr3bbl4kdbhMNsYeiWzzyDHho6O/1yABMeZscWFe3me9bLtov3SJeSyCXD8JY5NdB1xXV3/mwAJrnKt3KxOSqJoXxPtOXmS7WUHUZqQQDewCyOWLfsTB/72kgF6h8gAAAAldEVYdGRhdGU6Y3JlYXRlADIwMTYtMDItMTVUMjE6NTQ6NTUrMDE6MDBn40JdAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE2LTAyLTE1VDIxOjU0OjU1KzAxOjAwFr764QAAADF0RVh0c3ZnOmJhc2UtdXJpAGZpbGU6Ly8vdG1wL21hZ2ljay0yOTkzNnRtQ0wybVJFQVhVQYXWZzoAAAAASUVORK5CYII=" />
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'setup/template/">
                <img style="height: 16px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAABaAAAAWgBwI7h9AAAAB3RJTUUH4AIPFS842MATfAAABCJJREFUSMedVW1Mk1cUfs6liBE/WjqErX2L4IJxgm4YmWis4kKiEJQEmPDDVVqGcZGYEYGsM0OykkUX3Ry4LATpzHDOIZ+OMWOcbGqEzBkSMYtmirQjwUI/MgSJ0Hv2g4AJ2jF2/t08zznnOfc8930JcwzvOmXPnopNm1CEKlERHT0N5MhQGnE4NNRPp6ijY651A4Z7vv59y7HERO9tpc7yg8nk8xkMeX0xMc8Js0bPt0RGRflWKiF5Z/Ly3EVKY35cSsps9UUggBkAiEQUdXByYyPi0MKjREsWOxwGg8Mxk6+2BR1dUDswIFfjXRSPjtJ1vuqvTkubTYBqNgLfwBCqdDp4MQTY7d4aJcJpPHTIyzCav7fZ+ILcJde6XN6fxyIee5YsGUlE7+ONzc0LTorzi2ukxEv/UwDR5D14PDOAfMyDdfly9qITsNvZGtRJb6akaHscTvu6s2enV2cyKBbz7CsWszI8UGCZXMgLhf7CHbRlbGz2VnMUwAxkZwcFwQsFeYEFBBTWLb+V+1QqZqCMRcA+08CU6Twl+kXm17dv96Xrbi9cnJsLSTdwy2rFZ9gP7eBgoEI+n8Gwb59G49mlb7bsPHiQxsRHGA4P90Xpo50luble1t+0JGRksATKWDW9evIZdSfMxcnJfFJoabPBEHzk6emn5vb2hXWPLn3jcrmmiAMXI1J2Lw0Nnbdz3lDwT3v3wswmaEtL6TI0/HtTE/fR57Ts3j16Im7536it1VAfnSafbyrfy1FsYrUaH/I21cvp6XIjEmThkydC1hDhtt3Od3kjF8fGypVBDbTB7585oapqYn+wSQgqoqWUOjhIdTjPsNv5EH1HIVlZtACFbAsJ8e943BpU8oL8tU+rQwpUKuTybtQIQZFyDR4lJJDnR+U1c5rbjfUYRkRYGL2KcsjhYa7mZHRVVvIWYaSk7m6c8J/hDCnDyvuNSnpLi+8r5YizJDWVc1AFd0vLdKc2xCHT7aYvEAHX8eOcjXZsf/CA7qCH7oyMqL92huu3trV53AblL0tmpoAbUXjvmcn4T5RBLFqErWREktUqtLIJ3ePj2o/7jbU7GhpIAOU0MRHQfWnoQYNWyxdxGVcrKniVfFuce/hQc9oZfuqtCxeIgHKS8pkJPViG7H9x+U16R6rm/gqmox6H/fGB8wVd4k94YHx8zoU3QUebNRrcxwZcezbRfw3K8l/jUrVaYHVwpIhNSqJszkJ2dTV+Qw5WPG8id5uuMP+EXj/1U5IO2UX3795lF8WwKT6eDnMZl9XXB/pwDaW/kmwuXrHCU2gw5PUVFNBC/C0yu7poJtFzVIkwD8TFTZ5sNhznYzjW2yti2Q20tqp/7T9Q++mVK4EmmxS6fj19SavkBzYb/qA4WnP9OlfKc5TT3q5N66+sOdDZOcX/B93AxOwY9Cz+AAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDE2LTAyLTE1VDIxOjQ3OjU2KzAxOjAwfLI8AwAAACV0RVh0ZGF0ZTptb2RpZnkAMjAxNi0wMi0xNVQyMTo0Nzo1NiswMTowMA3vhL8AAAAxdEVYdHN2ZzpiYXNlLXVyaQBmaWxlOi8vL3RtcC9tYWdpY2stMTUwNDRyRUFHdXpCS3NnUjaevVeRAAAAAElFTkSuQmCC" />
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'setup/field/">
                <img style="height: 16px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAABaAAAAWgBwI7h9AAAAB3RJTUUH4AIPFS0dofKluQAAAxVJREFUSMfFlF1IU2EYx//vNtNaODZNLzxn2odBZEIfShde2IdUNOkDR0lusjOdkS1SZBhe2EW4PqwoYYSjWRm0PpQECwsiJClD66KkrMi+Rom1c6yMZs7zdJFzkZBaUs/teZ/n/3v///c5DJMsfynfWJCSlcUW0q3heyYT2rENp2QZBxQfmPXiRa329VqP6upVxgCAaLLzxwpeSbAXHFu+XFzHDQnpdXWiXa+3vLLZSAYqSaUiAipJofB/0PNWwWiUMrjPVrXXK53jB4QZZjMRYDQqlRMWFA/y8UJvSooo8koh6HKJ9dxSyymH4wUlUj5FRY3XHwKS8vn31hvZ2eJJrsQyeObMKNAI+FjhFv6x8LG+/gfAvn291+KzTHFq9d86+CMExkIXkYr404K5uzv0XTF6ksGDE7m5iMRmzCkqmpY27USE2m7vOz/LuOPCzJmTFf54myuxHtbp+jVchuWV04npuMQOaDS0E5EY7OwcCxAqE5qxNTYWQCZWOJ2qb1EDAfXLl37iyUJ794r+ObNthRrNmJvKS5fYCiMiQm9k2Mau0drqasVGFiEvc7t1Ob7ZnscVFXiBm8js7w/1hbN4Tulok2WkMS9m/fRo1qMLDTExTIKeNVRW4uyQIegtLpZ0fJpl85EjlIX7aO/pka73FQerDQa4hxPYc5dLF3gb7VlSW4suPPqdU2EAEXoUjqzN5d90jDhEJjQzVFVhP5oQV1OjXfNmgUdjNrNAOPmJVDgCEXqY/2BvF1Eh7fb5/nTvRwHYVxZ24B9W2AEJehT9b4C8/wlwh4bofE0Nq0AvYgKBqRLwX5l3fJs9Ojq0xqwbTzE/PH8UQPvJ11aXWF4uG+TtCi45mRkpB8baWlyHg5zB4EQFf/0fMP1gXGSfy6XIlruUixobtY43Nz2ryspC59l4AyVKpHxKSsLq4VJly5495GAqPLBakQYvniiVeMYYjpeX01wQ29XTw97RIfqyYQN7T3cp1e3WrvQdratobZ2yzCRDwsOClNRUsZ7zC7lNTeIWngl5HR39i7kSy+CmTZOd9x2OA1roT9Y26wAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxNi0wMi0xNVQyMTo0NToyOSswMTowMITKlc4AAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTYtMDItMTVUMjE6NDU6MjkrMDE6MDD1ly1yAAAAMXRFWHRzdmc6YmFzZS11cmkAZmlsZTovLy90bXAvbWFnaWNrLTE2MTQyZEp2OXNkdGhhaTVklmP6XAAAAABJRU5ErkJggg==" />
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'setup/logs/">
                <img style="height: 16px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAABaAAAAWgBwI7h9AAAAB3RJTUUH4AIPFTQv8iVdIQAAAiRJREFUSMe1lU9Ik2Ecx7/Pw9SFkLaxRuCb7h1oA+nQFrl/Uk1oIkERLeoi2zKC2Ow9yBLbZZciEAIbHqR28Jbs2CEiO4RpjIaBx3KiIl72Thirl+bep0M9Oyix9W5+Lz/48fl+3x/f5/ACGiXnhe8hi8cjz58eDWfcbq05VKsRu+w9pmMxnFLvqojFNOf8r/K+7oHwjs0mZ4Rbod5KRf4muEJLqio/E8yh3f7+I2+A3Kk4VePkJKz4BA+lMGALLwnBT/YE6YmJIzsgx7rZKNPrcR0PyWOL5RAQgQ8boljl6pSuXrD99Y9A+4JOhzW9Q0FfH6QDwBtyHxdttuOGluH9aGvrn6WiNK2BlmJbQJmXJEh4gbzJdAgYwRrSRiNdUm7qF6LRenNJLUDOi5Z7Yx0d+Fw+tr+zvo4BFGE2GP5pyLMszuztURO1t+VEsbNzc3N2tlDQ3AD5+utauWd8nIxgGJdKpZp8L3kKWiyyrHpD6YpEGn4Cco6m9dszM+wD3mEul6vFc477Gj6gkmDPyx8dDpxFAe7BwVo85yoeMOWR3d54A2O4rWbi8ZofPhisYykSTCQ0HyBf6JLC034/zDiJB14vZAgIM0YuI4WN5WUk2RyGslk+q/u/HFtEED1OZzWn3gMYAwBCUCJvmT8QIFfgg3dqiqboF2q0Wk+sbg29WnS5cJXEmX9lhU++5xz38ZxqbrMkrwrng3IyyafWHO1/wybpN2Zk3TSvIZT0AAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDE2LTAyLTE1VDIxOjUyOjQ3KzAxOjAwMcgjrQAAACV0RVh0ZGF0ZTptb2RpZnkAMjAxNi0wMi0xNVQyMTo1Mjo0NyswMTowMECVmxEAAAAxdEVYdHN2ZzpiYXNlLXVyaQBmaWxlOi8vL3RtcC9tYWdpY2stMjMwNzFDOWFqc1J1YnpzcHLnGKzRAAAAAElFTkSuQmCC" />
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'access/users/">
                <img style="height: 16px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAABaAAAAWgBwI7h9AAAAB3RJTUUH4AIPFTkY/zaGYwAAA7BJREFUSMfNU21MW2UYPc9t7+Z+EEsLA2aBfVQ3EpkOUorEqji2ubBFliyYMWIEHKS12dYw9sNUGApxYhGXqc3KQoxxDAx+JEqWATNxsWHxo4ktwlgmBhQZg15GkLBw2z7+0F7N6g3TP3p+Ps/7nHPe5z0v8B+D7izM7bkv+PyDW7eymb6O2mprlYYDweh6t1tv+Lnt3eJgUI1Qmd9HHZFvjx1ThD7mSk2u25342WT22aFAIM7Agj+1oqI5OVmWxW6Uj47ifhioMTFRYb6CBExLkugKH9Gu27w5wT9V422fnVXmc9LOVB9KSpKbtKfCv4yOIh8LSNHrV5oXYv3wgVVzyLJY4oRj+INw2ad5XP41N1cKGZ3P9WZnS89kTFasKygIa7SDy+uzsvAAdHhRfV4eEDPDHoslVlYMCBaej9aMjKAPx/nVcDjurR6GA4YLF6if2uixvXtB1CN8GAjAw/n0lM+HSc4TrCdPwo21fKu5Oc5AF7+BGVkWWulp4frwsGoGJMnorGotK0Mt3eSUEydgZS++02iQAT+XulzIoTLydHaqpup7OGCw2eh9pGL66FFeRh92CgKyMICi+nr98Z+mO1K7upSLMwMA0VyKUa7S2u30JBVzr8OhCMeQQ/Xo0+lWSjUbcZMX16xhC+9HiijGeKgT51B2+LAkpRdWNNtsMV2SXjI6K+vq6uCkHoRaWtSI6Qzvh6Onh7dRNTbdvo087ISzvPwvR0w4GAySHROc1N3N72CZTjU1qTq18WkK1dUJ2I1CFNntqsK7sB3WxUW8Ttnc5PcjgCew1NurZGItd/Bpnw8A+IrHw8kkQzczg0/4KgxjY6oGDsLEvXY7SVJ6emXl7wv525UOcgeNV1VRBGn8hcuFR2k3nt2wQdWwCY2ILizgbZTiA4eD70UeCtva4r5lLAOqDgNIhO/yZdKQkT8qLFxJWDF8HQ0QEhJ4iQkvNDQgn3ehxOtVOy+Qm7/i0v5+ZdUx6LCaB4eG0I0mfPrnv71rlNAWhDZupFlqoTdv3FDqP6AAX0ajygUV59HcnOpDoijtmF0VaTSZtK9EHsF5WY5soU3R4kuXALxGLRkZ/9SHZg8cMJjNEX/EhgOiiMl7HtJ6hof1hrEfve3z87QSgSSlv1VxdXz83xoAh11aTWam3jBV422fmLizrb0LAlFstVppm0YbNlitnIYS3mc2k4iXhR2pqdBREQ/odPwetvNSKES3+HNafe1adITNwjcXLxqKp2q8R+KF/zf4DVFcj8eyUU/cAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDE2LTAyLTE1VDIxOjU3OjI0KzAxOjAwIGb78wAAACV0RVh0ZGF0ZTptb2RpZnkAMjAxNi0wMi0xNVQyMTo1NzoyNCswMTowMFE7Q08AAAAxdEVYdHN2ZzpiYXNlLXVyaQBmaWxlOi8vL3RtcC9tYWdpY2stMzA4OTVqaGhDNVFFZGpvUFFE1KfvAAAAAElFTkSuQmCC" />
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'access/roles/">
                <img style="height: 16px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAABaAAAAWgBwI7h9AAAAB3RJTUUH4AIPFgAjQv9cpAAABRJJREFUSMelVG1Mk1cUPuctH4IoBSk0rm/RqUsM6DbYBzp0QQIsOsmckOEQpYW5KFDCujoELajgIg0qKsKmFtCRBTRIVWBDhBhk8uFUNmaZDl1XKqjQt+BAqPY9+7FQF41x6vPr5tznnvPcc59zAZ4BTiqZKVfFxnKfS9IS8pOTn8V/XuDEwvzJ9EzZdJbFK8xrsD8zE7p5F35FcTHJBTpBeWUlltBKmOPjw9dQE9MaEcH8xnTx/lFRwPJWWH70qMcqU4t2tLPzhQVwNyRB8pasLBKiCQ5nZ//vDCbQwEh5uec8Y4y2YvXqiTARQBYxzN1KUfTdY66uAjcnsbXP33+0Gx2Gznd2ssre3ceO3b/P3LsklslyRSJeD9tRbTQ+ykwnoGloCDIgkFbm5OBs2EqWjAxcAF6QbDLZab/Tu/CLwfD3ap+wOG9v74mwZbdkvrE8Kclxm/P2MWNbG5PN3OOnNjRMlmHylIupqRaLVJqYGBiInI/kgexyVxfp8VXc5+c3kYDJZ+7whaGhwlzDeKlLY+NEfGgNG7JWNGuWLYg2CIL0eojBL0Dk6IirYDZcHR2l23SC6tVqUOIqbN60CebBAC2oqAApuEFRVxdGgC9O12j4bIrF7xQKBu5DCHiePg0H6Dh8ZLVCPWykrx8+dM8xjI+sOXfu8Y67HzE2ld3t6cFe/JUO37xp3xBDJVwvL3dkcBBfb2sDb/CHLqHQY76xQ+qRmurpaWwqySwqIgXlQ/3Zs0wDjNDQjh2McKj3vFSakQE9uBeGDQYIhzzc5OBgaZKkub0RHPy4AEumr3Ni98yZ5ErLsHXGDMihdqgaH7eUMl62AYXiQQEchyh/f1RAA4T193Pp7CXjm7t2mc1siCx3/Xrci0oIDw3lM5kUnJ+SguYtkjS5SqWCNDwOg3l59kqtMAVum804iSJoYUEBfQPvM+2jo1CEvfzOlBQA2Il5UqndzWlwCXQHD3qUGUXawXXrzHmsj7w/JwdvwVswNyyMguAD6g4IgDkkglyNht6DNsa3uho58qW1JBTSen4FU6tUwlLwwb88PCAYaqEjKemp7j8EVtih08E9+hTyW1psH/JbBcU6nYNO0MpPVqloly2IarVarGWyBBecnGy7UWz709192knjV1rNqVOI/84K44EGLEOLxbPYWF2ybMsWpo0p5eOrquw32wjD0FNfj5F0FSsqKh6ZARpwW10dVuOXuM/LS+AqqLUtVatpjObRWFwcygTT4PvGRvoJYmxsfT2W4H4cdHaeKPzEP/Df+Y2OFgjMSrbKXb9kCZpsP9vM3d0PP35wzWUPxzn96Nw8Hhwe7n5oUobrlZoarmps+YhSrQZfTAdbejqGkonOaDRwEZuYpX19VAcKurl5M/xBStIbDEwD+sH5wkKh3jheEltS8rwflx0csdHyyvh4zpPVyuOHhzkn9lQC1tQ8wTstDZE1KhRmM8vK5UTcAXanXBUZaR/3FxVgi7QNQMeFC7SArFDZ3k5byJHKxeKJDtqJdfQO3GBZMAMLCY9a/9ICpp281aTVXLuGl4GHH1xcIAk/g4aAAO5bSeEUfUcH9zYbLxuoq6M0aMa5SiU2Qx5srKqiMupHsdX6VA8891NkSPoSxIsXgwSPUJxQSDGwHwZ1OlBRFKgbGyEQFkLKokUOewn5PX5+U7tNUaWzrl9/2bpP4E6FKHpDpZvbYI3kTEKoXE48QBY5OAzWvJKSWCCRPO3cP5HxR1ngEh2nAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDE2LTAyLTE1VDIyOjAwOjM1KzAxOjAwZPorLAAAACV0RVh0ZGF0ZTptb2RpZnkAMjAxNi0wMi0xNVQyMjowMDozNSswMTowMBWnk5AAAAAwdEVYdHN2ZzpiYXNlLXVyaQBmaWxlOi8vL3RtcC9tYWdpY2stNzA3NTl1U1Ftb2Y2eFJoV8C866cAAAAASUVORK5CYII=" />
            </a>&nbsp;
            <a href="'.wire('config')->urls->admin.'access/permissions/">
                <img style="height: 16px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAABaAAAAWgBwI7h9AAAAB3RJTUUH4AIPFgEozDa0bQAAA6tJREFUSMetU21MW2UUfk5v0bLIgA4mtLdgTZyRyX6o2DiCW7Yfmz82NKVGtkxc+domuNTi/OBLBDVbArhsqwIWtxhdEULMlrEtGhMxWdYQDcuATEeEjSYotYV2ptSUe48/zL0/2jTK3PnzvnnPc57nPDnnBVYZf+wyHbW/YTAsQhy2e0OhRb2p3/5KOBy4YKyvOi6Kq+XTxD8wAzabIISuiI7KTr0+Pi+04Ax/X1TEQTqMnrVreRqt0KSl4SpsstliWW0DFC+81CjOP1B58SJ+pD30bnExN2EnSpxOyoCRtiwvsxFuTnU6AUzji8JClaiaj8Bx4waK0YdrLpd268qTfMvjiV1JeQZoaUE6W2kiLy86Kg1p08rLDX3ztb29kYjagOJYbqNxHpyb47OYRsGaNat1pMYYXsKjkoQiePCzIKjvgxgh3YED+tq5Qrerp0cdQfpmX7fbGQwqju9aWIl4YSeexbabN6lG8/jKqYGBpDtAPryKU5FIAqGHu+CPxWgHtqOksZENXLbymihKLurTag0GbOPr/FFDAzXiN6yLRhPqRV6Pa319mXSLztDSkqoX0ImOl8eNRmEPzqast1jkN2mX/GBrK7Jxiao3bVKBw9wFf0tLZpXP1n++vT2Z8cCMybn/kMNB6fiKol1dan0Jf4ONU1NkwVYqb26WvkR5bMHrJeUbqducJChTc1tym83/OJidTYa780RuT011VlbsW23HiuT3J+XT83HUhsMaBDkDB5n/98yV+JSvxh4i+q9wjazDDyv3FxRoGjhGHVYrZYEwNzGRgHRLQ8Luffv+jTC2OWWcnkvE0WP8K9dPTio6im5Cp4u1oqOys66OP6QhnjxxQk0oSzhKb3FHW5v8Ne+W+PRpuUmzQ+eSZeG6HJV+37uX9GTjDe3t/D5yENDplHJe5jLMVlWtM/q6+79zu9XGlMsM53MF63QZH8gbhF9GR/kgojhaVHSvJkNvcxltrK/P7PF1u50nT6ojUC4Z+dLDwhGrNUE4CBMq78GOhOgclx47tjCQzxWck5PQwJ+H8fyd2eFhyoGLPhsZoacg4LLHQ/3an8hqNtML/Aku1NUp3ynpjIuxwNvtdsUxHcJ9eGd5mbP5MqdMTf3lk16PvBgK3bWRxTFTwf6nS0uDQZPJbmdWzkCzcdR+zmqNx/s78i7V1OTmznWKDpstNTU+r11tA9J7qKAtY2PCeX4EZeEw9LSEj4n4cwxqZrzeeHx20+2dvb3z88n4/gaC8qGqVKNvOAAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxNi0wMi0xNVQyMjowMTo0MCswMTowMNPFZqwAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTYtMDItMTVUMjI6MDE6NDArMDE6MDCimN4QAAAAMHRFWHRzdmc6YmFzZS11cmkAZmlsZTovLy90bXAvbWFnaWNrLTUwNTJWdlZwTzREQ1NKU3Pv5gYXAAAAAElFTkSuQmCC" />
            </a>&nbsp;
        </div>
        ';

        return $out;
    }

}
