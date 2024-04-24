<?php

use function ProcessWire\wire;

class AdminerProcessWireLogin {

    public function __construct($pwAdminUrl, $server = false, $db = false, $name = false, $pass = false) {

        if(strpos($_SERVER['REQUEST_URI'], '&it=') !== false) {
            header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }

        $this->pwAdminUrl = $pwAdminUrl;
        $this->server = $server;
        $this->db = $db;
        $this->name = $name;
        $this->pass = $pass;
    }

    public function head() {
    ?>
        <script nonce="<?=get_nonce()?>" src="../../../site/modules/TracyDebugger/panels/Adminer/scripts/main.js?v=1"></script>
    <?php
    }

    function name() {
        $pwLink = '
        <a class="adminerPwLogo" href="'.$this->pwAdminUrl.'" title="Return to PW admin">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                    width="16px" height="16.1px" viewBox="80 80.1 16 16.1" enable-background="new 80 80.1 16 16.1" xml:space="preserve">
            <path fill="#E41D5F" d="M94.6,83.7c-0.5-0.7-1.3-1.6-2.1-2.1c-1.7-1.2-3.6-1.6-5.4-1.4c-1.8,0.2-3.3,0.9-4.6,2
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
            </svg>
        </a>
        ';
        return $pwLink."<a href='https://www.adminerevo.org/'".target_blank()." id='h1'>AdminerEvo</a>";
    }

    function credentials() {
        // server, username and password for connecting to database
        return array($this->server, $this->name, $this->pass);
    }

    function login() {
        return true;
    }

    // really just here in case the javascript autosubmit in loginForm() doesn't work
    // this hides the form fields so the user won't think they need to fill them out
    function loginFormField($name, $heading, $value) {
        if($name == 'server') {
            return '<input type="hidden" name="'.$name.'" value="'.h($this->server).'" />';
        }
        if($name == 'driver') {
            return '<input type="hidden" name="auth[driver]" value="server" />';
        }
        if($name == 'db') {
            return '<input type="hidden" name="'.$name.'" value="'.h($this->db).'" />';
        }
        if($name == 'username') {
            return '<input type="hidden" name="'.$name.'" value="'.h($this->name).'" />';
        }
        if($name == 'password') {
            return '';
        }
    }

    function loginForm() {
        ?>
        <input type="hidden" name="auth[permanent]" value="1" checked />
        <script nonce="<?=get_nonce()?>">
            addEventListener('DOMContentLoaded', function () {
                document.getElementsByTagName('body')[0].style.display = "none";
                document.forms[0].submit();
            });
        </script>
        <?php
    }

    function databases($flush = true) {
        return [$this->db];
    }

    public function selectVal(&$val, $link, $field, $original) {

        if(!isset($_GET['select']) || in_array($_GET['select'], array('fieldgroups', 'caches'))) {
            // intentionally blank
        }
        elseif($_GET['select'] == 'modules' && $field['field'] == 'class') {
            $val = '<a href="'.$this->pwAdminUrl.'module/edit/?name='.$val.'" target="_parent">'.$val.'</a>';
        }
        elseif(ctype_digit("$original") || ctype_digit(str_replace(array(',', 'pid'), '', "$original"))) {

            $valid_page_fields = array('pid', 'pages_id', 'parent_id', 'parents_id', 'source_id', 'language_id', 'data');

            if($_GET['select'] == 'hanna_code' && $field['field'] == 'id') {
                $val = '<a href="'.$this->pwAdminUrl.'setup/hanna-code/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif($_GET['select'] == 'templates' && $field['field'] == 'id') {
                $val = '<a href="'.$this->pwAdminUrl.'setup/template/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif($_GET['select'] != 'templates' && $field['field'] == 'templates_id') {
                $name = wire('templates')->get('id='.$val)->get('label|name');
                if($name) {
                    $val = '<a href="'.$this->pwAdminUrl.'setup/template/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                }
            }
            elseif($_GET['select'] == 'fields' && $field['field'] == 'id') {
                $val = '<a href="'.$this->pwAdminUrl.'setup/field/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif(in_array($field['field'], array('field_id', 'fields_id'))) {
                $f = wire('fields')->get('id='.$val);
                if($f) {
                    $name = $f->get('label|name');
                    $val = '<a href="'.$this->pwAdminUrl.'setup/field/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                }
            }
            elseif($_GET['select'] == 'pages' && $field['field'] == 'id') {
                $val = '<a href="'.$this->pwAdminUrl.'page/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif(in_array($field['field'], array('uid', 'user_id', 'created_users_id', 'modified_users_id', 'user_created', 'user_updated'))) {
                if(method_exists(wire('pages'), 'getRaw')) {
                    $name = wire('pages')->getRaw('id='.$val, 'name');
                    if($name) {
                        $val = '<a href="'.$this->pwAdminUrl.'access/users/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                    }
                }
                else {
                    $val = '<a href="'.$this->pwAdminUrl.'access/users/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
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
                        $val = '<a href="'.$this->pwAdminUrl.'module/edit/?name='.$name.'" target="_parent" title="'.$name.'">'.$val.'</a>';
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
                                    $allids[] = '<a href="'.$this->pwAdminUrl.'page/edit/?id='.str_replace('pid', '', $v).'" target="_parent" title="'.$name.'">'.$v.'</a>';
                                }
                            }
                            else {
                                $allids[] = '<a href="'.$this->pwAdminUrl.'page/edit/?id='.$v.'" target="_parent">'.$v.'</a>';
                            }
                        }
                        $val = implode(',', $allids);
                    }
                }

            }
        }
    }

}
