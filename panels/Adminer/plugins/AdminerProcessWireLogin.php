<?php namespace Adminer;

use function ProcessWire\wire;

class AdminerProcessWireLogin {

    public function __construct() {

        // autologin based on https://steampixel.de/simply-auto-login-to-your-adminer/
        // a more complex version is available at: https://github.com/jeliebig/Adminer-Autologin/blob/master/login-env-vars.php
        if(!$_GET['username'] || !isset($_COOKIE['adminer_permanent']) || $_COOKIE['adminer_permanent'] == '') {
            $_POST['auth'] = [
                'driver' => 'mysql',
                'server' => wire('config')->dbHost . (wire('config')->dbPort ? ':' . wire('config')->dbPort : ''),
                'db' => wire('config')->dbName,
                'username' => wire('config')->dbUser,
                'password' => wire('config')->dbPass,
                'permanent' => 1
            ];
        }

    }

    function getCredentials() {
        // server, username and password for connecting to database
        return array(wire('config')->dbHost . (wire('config')->dbPort ? ':' . wire('config')->dbPort : ''), wire('config')->dbUser, wire('config')->dbPass);
    }

    function authenticate() {
        return true;
    }


    public function head() {
    ?>
        <script>
            window.HttpAdminUrl = '<?=wire('config')->urls->httpAdmin?>';
        </script>
    <?php
    }

    function databases($flush = true) {
        return [wire('config')->dbName];
    }

    public function selectVal(&$val, $link, $field, $original) {

        if(!$field || !isset($_GET['select']) || in_array($_GET['select'], array('fieldgroups', 'caches'))) {
            // intentionally blank
        }
        elseif ($val === null) {
			$val = "<i>NULL</i>";
		}
        elseif($_GET['select'] == 'modules' && $field['field'] == 'class') {
            $val = '<a href="'.wire('config')->urls->admin.'module/edit/?name='.$val.'" target="_parent">'.$val.'</a>';
        }
        elseif(ctype_digit("$original") || ctype_digit(str_replace(array(',', 'pid'), '', "$original"))) {

            $valid_page_fields = array('pid', 'pages_id', 'parent_id', 'parents_id', 'source_id', 'language_id', 'data');

            if($_GET['select'] == 'hanna_code' && $field['field'] == 'id') {
                $val = '<a href="'.wire('config')->urls->admin.'setup/hanna-code/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif($_GET['select'] == 'templates' && $field['field'] == 'id') {
                $val = '<a href="'.wire('config')->urls->admin.'setup/template/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif($_GET['select'] != 'templates' && $field['field'] == 'templates_id') {
                $name = wire('templates')->get('id='.$val)->get('label|name');
                if($name) {
                    $val = '<a href="'.wire('config')->urls->admin.'setup/template/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                }
            }
            elseif($_GET['select'] == 'fields' && $field['field'] == 'id') {
                $val = '<a href="'.wire('config')->urls->admin.'setup/field/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif(in_array($field['field'], array('field_id', 'fields_id'))) {
                $f = wire('fields')->get('id='.$val);
                if($f) {
                    $name = $f->get('label|name');
                    $val = '<a href="'.wire('config')->urls->admin.'setup/field/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                }
            }
            elseif($_GET['select'] == 'pages' && $field['field'] == 'id') {
                $val = '<a href="'.wire('config')->urls->admin.'page/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif(in_array($field['field'], array('uid', 'user_id', 'created_users_id', 'modified_users_id', 'user_created', 'user_updated'))) {
                if(method_exists(wire('pages'), 'getRaw')) {
                    $name = wire('pages')->getRaw('id='.$val, 'name');
                    if($name) {
                        $val = '<a href="'.wire('config')->urls->admin.'access/users/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                    }
                }
                else {
                    $val = '<a href="'.wire('config')->urls->admin.'access/users/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
                }
            }
            elseif(strpos($_GET['select'], 'field_') !== false || in_array($field['field'], $valid_page_fields)) {

                $f = wire('fields')->get(str_replace('field_', '', $_GET['select']));
                if($f && $f->type instanceof \ProcessWire\FieldtypeTable && strpos($f->type->getColumn($f, $field['field'])['type'], 'page') !== false) {
                    $valid_page_fields[] = $field['field'];
                }
                elseif($f && $f->type instanceof \ProcessWire\FieldtypeCombo && $f->getComboSettings()->getSubfieldType($field['field']) === 'Page') {
                    $valid_page_fields[] = $field['field'];
                }

                if($_GET['select'] == 'field_process' && $field['field'] == 'data') {
                    $name = wire('modules')->getModuleClass($val);
                    if($name) {
                        $val = '<a href="'.wire('config')->urls->admin.'module/edit/?name='.$name.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                    }
                }
                elseif(in_array($field['field'], $valid_page_fields)) {
                    $data_is_page = false;
                    if($field['field'] == 'data') {
                        if(isset($f) && ($f->type instanceof \ProcessWire\FieldtypePage || $f->type instanceof \ProcessWire\FieldtypePageIDs || $f->type instanceof \ProcessWire\FieldtypeRepeater)) {
                            $data_is_page = true;
                        }
                    }
                    if($field['field'] != 'data' || ($field['field'] == 'data' && $data_is_page)) {
                        $label = array('title', 'name');
                        if(wire('modules')->isInstalled('PagePaths')) {
                            $label[] = 'url';
                        }
                        $allids = [];
                        foreach(explode(',', $val) as $v) {
                            if(method_exists(wire('pages'), 'getRaw')) {
                                $name = wire('pages')->getRaw('id='.str_replace('pid', '', $v), $label);
                                if($name) {
                                    $name = (isset($name['title']) ? $name['title'] : $name['name']) . (isset($name['url']) ? ' ('.$name['url'].')' : '');
                                    $allids[] = '<a href="'.wire('config')->urls->admin.'page/edit/?id='.str_replace('pid', '', $v).'" target="_parent" title="'.$name.'">'.$v.'</a>';
                                }
                            }
                            else {
                                $allids[] = '<a href="'.wire('config')->urls->admin.'page/edit/?id='.$v.'" target="_parent">'.$v.'</a>';
                            }
                        }
                        $val = implode(',', $allids);
                    }
                }

            }
        }
    }

    function messageQuery($query, $time, $failed = false) {
        wire('log')->save('adminer_queries', $query);
    }

    function sqlCommandQuery($query) {
        wire('log')->save('adminer_queries', $query);
    }

}
