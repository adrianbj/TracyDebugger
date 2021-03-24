<?php

use Tracy\Dumper;

class RequestInfoPanel extends BasePanel {

    protected $icon;

    public function getTab() {

        \Tracy\Debugger::timer('requestInfo');

            $this->icon = '
            <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="16px" viewBox="212.8 211.3 16 16" enable-background="new 212.8 211.3 16 16" xml:space="preserve">
            <g>
                <path d="M225.6,216c-0.1-0.3-0.3-0.6-0.5-0.8l-3.2-3.3c-0.2-0.2-0.4-0.4-0.8-0.5c-0.3-0.1-0.6-0.2-0.9-0.2h-6.5
                    c-0.3,0-0.5,0.1-0.7,0.3c-0.2,0.2-0.3,0.4-0.3,0.7v14c0,0.3,0.1,0.5,0.3,0.7c0.2,0.2,0.4,0.3,0.7,0.3h11.1c0.3,0,0.5-0.1,0.7-0.3
                    c0.2-0.2,0.3-0.4,0.3-0.7v-9.3C225.8,216.7,225.7,216.4,225.6,216z M220.6,212.7c0.2,0.1,0.3,0.1,0.4,0.2l3.2,3.3
                    c0.1,0.1,0.2,0.2,0.2,0.4h-3.8V212.7z M224.5,225.9h-10.4v-13.3h5.2v4.3c0,0.3,0.1,0.5,0.3,0.7c0.2,0.2,0.4,0.3,0.7,0.3h4.2V225.9z" fill="'.\TracyDebugger::COLOR_NORMAL.'"/>
                <path d="M222.8,221.9h-7.2c-0.1,0-0.2,0-0.2,0.1c-0.1,0.1-0.1,0.1-0.1,0.2v0.7c0,0.1,0,0.2,0.1,0.2c0.1,0.1,0.1,0.1,0.2,0.1h7.2
                    c0.1,0,0.2,0,0.2-0.1c0.1-0.1,0.1-0.1,0.1-0.2v-0.7c0-0.1,0-0.2-0.1-0.2C223,222,222.9,221.9,222.8,221.9z" fill="'.\TracyDebugger::COLOR_NORMAL.'"/>
                <path d="M215.5,219.4c-0.1,0.1-0.1,0.1-0.1,0.2v0.7c0,0.1,0,0.2,0.1,0.2c0.1,0.1,0.1,0.1,0.2,0.1h7.2c0.1,0,0.2,0,0.2-0.1
                    c0.1-0.1,0.1-0.1,0.1-0.2v-0.7c0-0.1,0-0.2-0.1-0.2c-0.1-0.1-0.1-0.1-0.2-0.1h-7.2C215.6,219.3,215.5,219.3,215.5,219.4z" fill="'.\TracyDebugger::COLOR_NORMAL.'"/>
            </g>
            </svg>';

            return '
            <span title="Request Info">' .
                $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Request' : '') . '
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

        if($this->wire('modules')->isInstalled("ProcessTracyAdminer")) {
            $adminerModuleId = $this->wire('modules')->getModuleID("ProcessTracyAdminer");
            $adminerUrl = $this->wire('pages')->get("process=$adminerModuleId")->url;
            $adminerIcon = '
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="304.4 284.4 11.7 16">
                <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M304.4 294.8v2.3c.3 1.3 2.7 2.3 5.8 2.3s5.7-1 5.9-2.3v-2.3c-1 .8-3.1 1.4-6 1.4-2.8 0-4.8-.6-5.7-1.4zM310.7 291.9h-1.2c-1.7-.1-3.1-.3-4-.7-.4-.2-.9-.4-1.1-.6v2.4c.7.8 2.9 1.5 5.8 1.5 3 0 5.1-.7 5.8-1.5v-2.4c-.3.2-.7.5-1.1.6-1.1.4-2.5.6-4.2.7zM310.1 285.6c-3.5 0-5.5 1.1-5.8 2.3v.7c.7.8 2.9 1.5 5.8 1.5s5.1-.7 5.8-1.5v-.6c-.3-1.3-2.3-2.4-5.8-2.4z"/>
            </svg>
            ';
        }

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

        if(is_null($p)) {
            $p = $this->wire('page');
        }

        // check if request is to a PW page - otherwise it's maybe an AJAX request to an external script
        $isPwPage = $_SERVER['PHP_SELF'] == $this->wire('config')->urls->root . 'index.php' ? true : false;

        $panelSections = \TracyDebugger::getDataValue('requestInfoPanelSections');

        // end for each section
        $sectionEnd = '
                    </tbody>
                </table>
            </div>';

        $userLang = $this->wire('user')->language;


        /**
         * Panel sections
         */

        // Field Settings
        if(in_array('fieldSettings', $panelSections) && $isPwPage) {
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
                $fieldSettings = '';
                $field = $this->wire('fields')->get((int)$this->wire('input')->get('id'));
                if($field) {
                    if(isset($adminerUrl)) {
                        $fieldSettings .= '<a title="Edit in Adminer" style="padding-bottom:5px" href="'.$adminerUrl.'?edit=fields&where%5Bid%5D='.$field->id.'">'.$adminerIcon.'</a>';
                    }
                    $fieldSettings .= '<table>';
                    $fieldSettings .= '
                        <tr>
                            <td>label</td>
                            <td>'.$field->label.'</td>
                        </tr>
                        <tr>
                            <td>name</td>
                            <td>'.$field->name.'</td>
                        </tr>
                        <tr>
                            <td>id</td>
                            <td>'.$field->id.'</td>
                        </tr>
                        <tr>
                            <td>type</td>
                            <td>'.$field->type.'</td>
                        </tr>
                        <tr>
                            <td>flags</td>
                            <td>'.$field->flags.'</td>
                        </tr>
                        ';
                    foreach($field->getArray() as $k => $v) {
                        $fieldSettings .= '
                            <tr>
                                <td>'.$k.'</td>
                                <td>'.Dumper::toHtml($v, array(Dumper::TRUNCATE => 999)).'</td>
                            </tr>
                        ';
                    }
                    $fieldSettings .= '</table>
                    ';
                }
            }
        }

        // Field Inputfield Settings
        if(in_array('inputFieldSettings', $panelSections) && $isPwPage) {
            $inputFieldSettings = '';
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
                $field = $this->wire('fields')->get((int)$this->wire('input')->get('id'));
                if($field) {
                    $inputfield = $field->getInputfield(new NullPage());
                    if($inputfield) {
                        $inputFieldSettings = '
                        <table>
                            <tr>
                                <td>id</td>
                                <td>'.$inputfield->id.'</td>
                            </tr>
                            <tr>
                                <td>type</td>
                                <td>'.$inputfield->type.'</td>
                            </tr>';
                        foreach($inputfield->getArray() as $k => $v) {
                            $inputFieldSettings .= '
                                <tr>
                                    <td>'.$k.'</td>
                                    <td>'.Dumper::toHtml($v, array(Dumper::TRUNCATE => 999)).'</td>
                                </tr>
                            ';
                        }
                        $inputFieldSettings .= '</table>
                        ';
                    }
                }
            }
        }

        // Field Code
        if(in_array('fieldCode', $panelSections) && $isPwPage) {
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
                $fieldCode = '<pre style="margin-bottom: 0">';
                $field = $this->wire('fields')->get((int)$this->wire('input')->get('id'));
                if($field) {
                    $fieldCode .= "[\n";
                    $fieldCode .= "\t'type' => '" . (string)$field->type . "',\n";
                    $fieldCode .= "\t'name' => '$field->name',\n";
                    $fieldCode .= "\t'label' => __('$field->label'),\n";
                    $fieldCode .= "\t'flags' => '$field->flags',\n";
                    $fieldDataArr = $field->getArray();
                    foreach($fieldDataArr as $k => $v) {
                        if(is_array($v)) {
                            $fieldCode .= "\t'$k' => [\n";
                            foreach($v as $key => $val) {
                                $fieldCode .= "\t\t'".$this->wire('sanitizer')->entities($val)."',\n";
                            }
                            $fieldCode .= "\t],\n";
                        }
                        else {
                            $fieldCode .= "\t'$k' => '".$this->wire('sanitizer')->entities($v)."',\n";
                        }
                    }
                    $fieldCode .= "]\n";
                    $fieldCode .= '</pre>';
                }
            }
        }

        // Field Export Code
        if(in_array('fieldExportCode', $panelSections) && $isPwPage) {
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
                $fieldExportCode = '<pre style="margin-bottom: 0">';
                $field = $this->wire('fields')->get((int)$this->wire('input')->get('id'));
                if($field) {
                    if(method_exists($field, 'getExportData')) {
                        $fieldExportData = array();
                        $fieldExportData[$field->name] = $field->getExportData();
                        $fieldExportCode .= $this->wire('sanitizer')->entities(wireEncodeJSON($fieldExportData, true, true));
                    }
                    $fieldExportCode .= '</pre>';
                }
            }
        }

        // Template Settings
        if(in_array('templateSettings', $panelSections) && $isPwPage) {
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
                $templateSettings = '';
                $template = $this->wire('templates')->get((int)$this->wire('input')->get('id'));
                if($template) {
                    if(isset($adminerUrl)) {
                        $templateSettings .= '<a title="Edit in Adminer" style="padding-bottom:5px" href="'.$adminerUrl.'?edit=templates&where%5Bid%5D='.$template->id.'">'.$adminerIcon.'</a>';
                    }
                    $templateSettings .= '<table>';
                    if(method_exists($template, 'getExportData')) {
                        foreach($template->getExportData() as $k => $v) {
                            $templateSettings .= '
                                <tr>
                                    <td>'.$k.'</td>
                                    <td>'.Dumper::toHtml($v, array(Dumper::TRUNCATE => 999)).'</td>
                                </tr>
                            ';
                        }
                    }
                    // older version of PW that doesn't have getExportData() method
                    else {
                        $templateSettings .= '
                            <tr>
                                <td>label</td>
                                <td>'.$template->label.'</td>
                            </tr>
                            <tr>
                                <td>name</td>
                                <td>'.$template->name.'</td>
                            </tr>
                            <tr>
                                <td>id</td>
                                <td>'.$template->id.'</td>
                            </tr>
                            <tr>
                                <td>type</td>
                                <td>'.$template->type.'</td>
                            </tr>
                            <tr>
                                <td>flags</td>
                                <td>'.$template->flags.'</td>
                            </tr>
                            ';
                        foreach($template->getArray() as $k => $v) {
                            $templateSettings .= '
                                <tr>
                                    <td>'.$k.'</td>
                                    <td>'.Dumper::toHtml($v, array(Dumper::TRUNCATE => 999)).'</td>
                                </tr>
                            ';
                        }
                    }
                    $templateSettings .= '</table>
                    ';
                }
            }
        }

        // Template Code
        if(in_array('templateCode', $panelSections) && $isPwPage) {
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
                $templateCode = '<pre style="margin-bottom: 0">';
                $template = $this->wire('templates')->get((int)$this->wire('input')->get('id'));
                if($template) {
                    $templateCode .= "[\n";
                    if(method_exists($template, 'getExportData')) {
                        $templateExportData = $template->getExportData();
                        unset($templateExportData['id']);
                        $templateCode .= $this->wire('sanitizer')->entities(ltrim(rtrim(ltrim(str_replace(':', ' =>', wireEncodeJSON($templateExportData, true, true)), "{"), "}"), "\n"));
                    }
                    // older version of PW that doesn't have getExportData() method
                    else {
                        $templateCode .= "\t'type' => '" . (string)$template->getInputfield(new NullPage()) . "',\n";
                        $templateCode .= "\t'name' => '$template->name',\n";
                        $templateCode .= "\t'label' => __('$template->label'),\n";
                        $templateCode .= "\t'flags' => '$template->flags',\n";
                        $templateDataArr = $template->getArray();
                        foreach($templateDataArr as $k => $v) {
                            if(is_array($v)) {
                                $templateCode .= "\t'$k' => [\n";
                                foreach($v as $key => $val) {
                                    $templateCode .= "\t\t'".$this->wire('sanitizer')->entities($val)."',\n";
                                }
                                $templateCode .= "\t],\n";
                            }
                            else {
                                $templateCode .= "\t'$k' => '".$this->wire('sanitizer')->entities($v)."',\n";
                            }
                        }
                    }
                    $templateCode .= "]\n";
                    $templateCode .= '</pre>';
                }
            }
        }

        // Template Export Code
        if(in_array('templateExportCode', $panelSections) && $isPwPage) {
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
                $templateExportCode = '<pre style="margin-bottom: 0">';
                $template = $this->wire('templates')->get((int)$this->wire('input')->get('id'));
                if($template) {
                    if(method_exists($template, 'getExportData')) {
                        $templateExportData = array();
                        $templateExportData[$template->name] = $template->getExportData();
                        $templateExportCode .= $this->wire('sanitizer')->entities(wireEncodeJSON($templateExportData, true, true));
                    }
                    $templateExportCode .= '</pre>';
                }
            }
        }


        // Module Settings
        if(in_array('moduleSettings', $panelSections) && $isPwPage) {
            if($this->wire('input')->get('name') && $this->wire('page')->process == 'ProcessModule') {
                $moduleSettings = '';
                $moduleName = $this->wire('sanitizer')->name($this->wire('input')->get('name'));
                if($this->wire('modules')->isInstalled($moduleName)) {
                    $moduleInfo = $this->wire('modules')->getModuleInfoVerbose($moduleName);
                    $moduleConfigData = $this->wire('modules')->getModuleConfigData($moduleName) ?: array();
                    $moduleObject = $this->wire('modules')->getModule($moduleName, array('noInit' => true));
                    $moduleObject = method_exists($moduleObject, 'getArray') ? $moduleObject->getArray() : array();
                    ksort($moduleConfigData);
                    ksort($moduleObject);
                    if(isset($adminerUrl)) {
                        $moduleSettings .= '<a title="Edit in Adminer" style="padding-bottom:5px" href="'.$adminerUrl.'?edit=modules&where%5Bclass%5D='.$moduleName.'">'.$adminerIcon.'</a>';
                    }
                    foreach(array(
                        'getModuleInfoVerbose() (' . count($moduleInfo) . ' params)' => $moduleInfo,
                        'getConfig() (' . count($moduleConfigData) . ' params)' => $moduleConfigData,
                        'getModule() (' . count($moduleObject) . ' params)' => $moduleObject
                    ) as $type => $settings) {
                        $moduleSettings .= '
                        <p><table>
                            <th colspan="2">'.$type.'</th>';
                                foreach($settings as $k => $v) {
                                    $moduleSettings .= '
                                        <tr>
                                            <td>'.$k.'</td>
                                            <td>'.Dumper::toHtml($v, array(Dumper::TRUNCATE => 999)).'</td>
                                        </tr>
                                    ';
                                }
                        $moduleSettings .= '
                        </table></p>';
                    }
                }
            }
        }


        // Page info
        if(in_array('pageInfo', $panelSections) && $isPwPage) {
            $pageInfo = '';
            if(isset($adminerUrl)) {
                $pageInfo .= '<a title="Edit in Adminer" style="padding-bottom:5px" href="'.$adminerUrl.'?edit=pages&where%5Bid%5D='.$p->id.'">'.$adminerIcon.'</a>';
            }
            $pageInfo .= '
            <table>
                <tr>
                    <td>title</td>
                    <td>'.$this->getLanguageVersion($p, 'title', $userLang, true).'</td>
                </tr>
                <tr>
                    <td>name</td>
                    <td>'.$this->getLanguageVersion($p, 'name', $userLang, true).'</td>
                </tr>';

            if($this->wire('languages')) {
                $pageInfo .= '
                    <tr>
                        <td>language</td>
                        <td>' . $userLang->title . ' ('.$userLang->name.')</td>
                    </tr>';
            }

            if($this->wire('page')->process == 'ProcessPageEdit') {
                $pageInfo .= '
                <tr>
                    <td>id</td>
                    <td><a title="Edit Page" href="'.$p->editUrl().'">'.$p->id.'</a></td>
                </tr>
                <tr>
                    <td>path</td>
                    <td><a title="View Page" href="'.$p->url.'">'.$p->path.'</a></td>
                </tr>
                ';
            }
            else {
                $pageInfo .= '
                <tr>
                    <td>id</td>
                    <td>'.$p->id.'</td>
                </tr>';
            }

            if($p->template->urlSegments) {
                $i=1;
                while($i <= $this->wire('config')->maxUrlSegments) {
                    if($this->wire('input')->urlSegment($i)) {
                        $pageInfo .= '
                        <tr>
                            <td>urlSegment '.$i.'</td>
                            <td>'.$this->wire('input')->urlSegment($i).'</td>
                        </tr>';
                    }
                    $i++;
                }
            }
            $pageInfo .= '
            <tr>
                <td>template</td>
                <td><a title="Edit Template" href="'.$this->wire('config')->urls->admin.'setup/template/edit?id='.$p->template->id.'">'.$p->template->name.'</a>'.($p->template->label ? ' ('.($this->wire('languages') ? $p->template->getLabel($userLang) : $p->template->label).')' :'').'</td>
            </tr>
            <tr>
                <td>process</td>
                <td>'.$this->wire('page')->process.'</td>
            </tr>';
            if($p->parent->id) {
                $pageInfo .= '
                <tr>
                    <td>parent</td>
                    <td>' . ($p->parent->viewable() ? '<a title="View Parent" href="'.$p->parent->url.'">'.$this->getLanguageVersion($p->parent, 'name', $userLang, true).'</a>' : '<span title="Not Viewable">'.$this->getLanguageVersion($p->parent, 'name', $userLang, true).'</span>') . ' (<a title="Edit Parent" href="'.$p->parent->editUrl().'">'.$p->parent->id.'</a>)</td>
                </tr>';
            }
            $pageInfo .= '
            <tr>
                <td>rootParent</td>
                <td>' . ($p->rootParent->viewable() ? '<a title="View Root Parent" href="'.$p->rootParent->url.'">'.$this->getLanguageVersion($p->rootParent, 'name', $userLang, true).'</a>' : '<span title="Not Viewable">'.$this->getLanguageVersion($p->rootParent, 'name', $userLang, true).'</span>') . ' (<a title="Edit Root Parent" href="'.$p->rootParent->editUrl().'">'.$p->rootParent->id.'</a>)</td>
            </tr>
            ';
            $prevPage = $p->prev("include=all");
            if($prevPage->id) {
                $pageInfo .= '
                <tr>
                    <td>prev (sibling)</td>
                    <td>' . ($prevPage->viewable() ? '<a title="View Prev Sibling" href="'.$prevPage->url.'">'.$this->getLanguageVersion($prevPage, 'name', $userLang, true).'</a>' : '<span title="Not Viewable">'.$this->getLanguageVersion($prevPage, 'name', $userLang, true).'</span>') . ' (<a title="Edit Prev Sibling" href="'.$prevPage->editUrl().'">'.$prevPage->id.'</a>)</td>
                </tr>';
            }
            $nextPage = $p->next("include=all");
            if($nextPage->id) {
                $pageInfo .= '
                <tr>
                    <td>next (sibling)</td>
                    <td>' . ($nextPage->viewable() ? '<a title="View Next Sibling" href="'.$nextPage->url.'">'.$this->getLanguageVersion($nextPage, 'name', $userLang, true).'</a>' : '<span title="Not Viewable">'.$this->getLanguageVersion($nextPage, 'name', $userLang, true).'</span>') . ' (<a title="Edit Next Sibling" href="'.$nextPage->editUrl().'">'.$nextPage->id.'</a>)</td>
                </tr>';
            }
            $pageInfo .= '
            <tr>
                <td>children</td>
                <td>'.$p->numChildren().' <a title="Open Page Tree" href="'.$this->wire('config')->urls->admin.'page/list/?open='.$p->id.'">open tree</a> | <a title="View Children Tab" href="'.$p->editUrl().'#ProcessPageEditChildren">edit</a></td>
            </tr>
            ';
            if($p->numChildren()) {
                $firstChild = $p->child("include=all");
                $pageInfo .= '
                <tr>
                    <td>child</td>
                    <td>' . ($firstChild->viewable() ? '<a title="View First Child" href="'.$firstChild->url.'">'.$this->getLanguageVersion($firstChild, 'name', $userLang, true).'</a>' : '<span title="Not Viewable">'.$this->getLanguageVersion($firstChild, 'name', $userLang, true).'</span>') . ' (<a title="Edit First Child" href="'.$firstChild->editUrl().'">'.$firstChild->id.'</a>)</td>
                </tr>
                ';
            }
            $pageInfo .= '
                <tr>
                    <td>createdUser</td>
                    <td>'.$p->createdUser->name.' (<a title="Edit User" href="'.$this->wire('config')->urls->admin.'access/users/edit/?id='.$p->createdUser->id.'">'.$p->createdUser->id.'</a>)</td>
                </tr>
                <tr>
                    <td>created</td>
                    <td>'.date("Y-m-d H:i:s", $p->created).'</td>
                </tr>
                <tr>
                    <td>published</td>
                    <td>'.date("Y-m-d H:i:s", $p->published).'</td>
                </tr>
                <tr>
                    <td>modifiedUser</td>
                    <td>'.$p->modifiedUser->name.' (<a title="Edit User" href="'.$this->wire('config')->urls->admin.'access/users/edit/?id='.$p->modifiedUser->id.'">'.$p->modifiedUser->id.'</a>)</td>
                </tr>
                <tr>
                    <td>modified</td>
                    <td>'.date("Y-m-d H:i:s", $p->modified).'</td>
                </tr>
                <tr>
                    <td>Hidden (status)</td>
                    <td>'. ($p->isHidden() ? "✔" : "✘") .'</td>
                </tr>
                <tr>
                    <td>Unpublished (status)</td>
                    <td>'. ($p->isUnpublished() ? "✔" : "✘") .'</td>
                </tr>
                <tr>
                    <td>Locked (status)</td>
                    <td>'. ($p->is(Page::statusLocked) ? "✔" : "✘") .'</td>
                </tr>
            </table>';
        }

        // Page permissions
        if(in_array('pageInfo', $panelSections) && $isPwPage) {

            $pagePermissionsLabels = array('', 'view', 'edit', 'add', 'publish', 'list', 'move', 'sort', 'delete', 'trash');
            $pagePermissionsPerms = array('viewable', 'editable', 'addable', 'publishable', 'listable', 'moveable', 'sortable', 'deleteable', 'trashable');
            if(version_compare($this->wire('config')->version, '3.0.107') >= 0) {
                array_push($pagePermissionsLabels, 'restore');
                array_push($pagePermissionsPerms, 'restorable');
            }
            $pagePermissions = $this->sectionHeader($pagePermissionsLabels);

            // current user
            $pagePermissions .= '<tr><td><strong>' . $this->wire('user')->name . '</strong></td>';
            foreach($pagePermissionsPerms as $permission) {
                $pagePermissions .= '<td style="text-align: center;">' . ($p->$permission() ? '✔' : '') . '</td>';
            }

            // all roles
            $currentUser = $this->wire('user');
            foreach($this->wire('roles') as $role) {
                $fakeUser = new User();
                $fakeUser->addRole($role);
                $this->wire('users')->setCurrentUser($fakeUser);

                $pagePermissions .= '<tr><td>' . $role->name . '</td>';
                foreach($pagePermissionsPerms as $permission) {
                    $pagePermissions .= '<td style="text-align: center;">' . ($p->$permission() ? '✔' : '') . '</td>';
                }
                $pagePermissions .= '</tr>';
            }
            $pagePermissions .= $sectionEnd;

            $this->wire('users')->setCurrentUser($currentUser);
        }

        // Language info
        $languageInfo = '';
        if($this->wire('languages') && in_array('languageInfo', $panelSections) && $isPwPage) {
            $languageInfo .= '<table><tr><th>language</th><th>id</th><th>title</th><th>name</th><th>active</th></tr>';
            foreach($this->wire('languages') as $language) {
                $languageInfo .= '<tr><td>' . $language->title . ' ('.$language->name.')</td><td><a title="Edit Language" href="'.$this->wire('config')->urls->admin.'/setup/languages/edit/?id='.$language->id.'">'.$language->id.'</a></td><td>' . $this->getLanguageVersion($p, 'title', $language) . '</td><td>' . $this->getLanguageVersion($p, 'name', $language) . '</td><td>' . ($language->isDefaultLanguage ? 'default' : ($p->get("status{$language->id}") ? "✔" : "✘")) . '</td></tr>';
            }
            $languageInfo .= '</table>';
        }

        // Template info
        // defining $templateFilePath even if templateInfo not a selected panel because it's used to build the template editing button at the bottom of the panel
        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
            if($template) $templateFilePath = $this->wire('templates')->get((int)$this->wire('input')->get('id'))->filename;
        }
        elseif($isPwPage && ($this->wire('process') == 'ProcessPageView' || $this->wire('process') == 'ProcessPageEdit')) {
            if(file_exists($p->template->filename)) $templateFilePath = $p->template->filename;
        }
        elseif($isPwPage && $this->wire('process')) {
            $templateFilePath = $this->wire('modules')->getModuleFile($this->wire('process'));
        }
        else {
            $templateFilePath = $_SERVER['SCRIPT_FILENAME'];
        }
        if(!isset($templateFilePath) || !file_exists($templateFilePath)) $templateFilePath = null;

        $templateFileEditorLinkIcon = '
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="14.2px" height="16px" viewBox="388.9 298 14.2 16" enable-background="new 388.9 298 14.2 16" xml:space="preserve">
                    <path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M394.6,307.5c-0.1,0.1-0.1,0.1-0.1,0.2l-1,3.2c0,0.1,0,0.2,0.1,0.3c0.1,0.1,0.1,0.1,0.2,0.1c0,0,0,0,0.1,0
                        c0,0,0,0,0,0l3.3-1.1c0.1,0,0.1-0.1,0.1-0.1l5.9-5.9c0.1-0.1,0.1-0.1,0.1-0.2c0-0.1,0-0.2-0.1-0.2l-2.2-2.2
                        c-0.1-0.1-0.1-0.1-0.2-0.1c-0.1,0-0.2,0-0.2,0.1l-0.2,0.2v-3.4c0-0.1,0-0.2-0.1-0.3c-0.1-0.1-0.2-0.1-0.3-0.1h-6.5l0,0h-0.8
                        c0,0,0,0,0,0h-0.1v0.1l-3.3,3.3c0,0,0,0,0,0.1h0v0.1v0.9v11.2c0,0.1,0,0.2,0.1,0.3c0.1,0.1,0.2,0.1,0.3,0.1h3.1h4.2h3.1
                        c0.1,0,0.2,0,0.3-0.1c0.1-0.1,0.1-0.2,0.1-0.4v-4.2c0-0.1,0-0.1-0.1-0.2c0,0-0.1,0-0.2,0.1l-0.2,0.2c-0.1,0.1-0.2,0.2-0.4,0.3
                        c-0.1,0.1-0.3,0.3-0.6,0.6v2.3h-2.1h-4.2h-2.1v-10.1h2.9c0.1,0,0.2-0.1,0.2-0.2v-2.9h5.4v3.9L394.6,307.5z M394.6,310l0.6-1.8
                        l1.2,1.2L394.6,310z"/>
            </svg>
        ';

        if(isset($templateFilePath) && $templateFilePath != '') $templateFileEditorLink = \TracyDebugger::createEditorLink($templateFilePath, 1, $templateFileEditorLinkIcon, 'Edit ' . pathinfo($templateFilePath, PATHINFO_BASENAME));

        if(in_array('templateInfo', $panelSections) && $isPwPage) {
            $templateInfo = '';
            // posix_getpwuid doesn't exist on Windows
            if(function_exists('posix_getpwuid')) {
                if(isset($templateFilePath)) {
                    $owner = posix_getpwuid(fileowner($templateFilePath));
                    if(!is_bool($owner)) $group = posix_getgrgid($owner['gid']);
                }
            }
            $permission = !isset($templateFilePath) ? '' : substr(sprintf('%o', fileperms($templateFilePath)), -4);

            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
                $template = $this->wire('templates')->get((int)$this->wire('input')->get('id'));
            }
            else {
                $template = $p->template;
            }

            if($template) {

                if(isset($adminerUrl)) {
                    $templateInfo .= '<a title="Edit in Adminer" style="padding-bottom:5px" href="'.$adminerUrl.'?edit=templates&where%5Bid%5D='.$template->id.'">'.$adminerIcon.'</a>';
                }

                $templateInfo .= '
                <table>
                    <tr>
                        <td>label</td>
                        <td>'.($this->wire('languages') ? $template->getLabel($userLang) : $template->label).'</td>
                    </tr>
                    <tr>
                        <td>name</td>
                        <td><a title="Edit Template" href="'.$this->wire('config')->urls->admin.'setup/template/edit?id='.$template->id.'">'.$template->name.'</a></td>
                    </tr>
                    <tr>
                        <td>id</td>
                        <td>'.$template->id.'</td>
                    </tr>
                    <tr>
                        <td>modified</td>
                        <td>'.date("Y-m-d H:i:s", $template->modified).'</td>
                    </tr>
                    <tr>
                        <td>fieldgroup</td>
                        <td>'.$template->fieldgroup.'</td>
                    </tr>
                    <tr>
                        <td>filename</td>
                        <td>'.(isset($templateFilePath) ? \TracyDebugger::createEditorLink($templateFilePath, 1, str_replace($this->wire('config')->paths->root, '/', $templateFilePath), 'Edit Template File') . '<br />
                            modified: ' . date("Y-m-d H:i:s", filemtime($templateFilePath)) . '<br />' .
                            (isset($owner) && !is_bool($owner) && isset($group) && !is_bool($group) ? 'user/group: ' . $owner['name'].":".$group['name'] .'<br />' : '') . '
                            permissions: ' . $permission
                            : 'No file').'</td>
                    </tr>
                    <tr>
                        <td>compile</td>
                        <td>'.($template->compile === 0 ? 'No' : ($template->compile === 1 ? 'Yes (template file only)' : 'Yes (and included files)')).'</td>
                    </tr>
                    <tr>
                        <td>contentType</td>
                        <td>'.$template->contentType.'</td>
                    </tr>
                    <tr>
                        <td>allowPageNum</td>
                        <td>'.($template->allowPageNum === 1 ? 'Enabled' : 'Disabled').'</td>
                    </tr>
                    <tr>
                        <td>urlSegments</td>
                        <td>'.($template->urlSegments === 1 || is_array($template->urlSegments) ? 'Enabled' : 'Disabled').'</td>
                    </tr>
                    <tr>
                        <td>urlSegmentsList (Segments Allowed)</td>
                        <td>'.(is_array($template->urlSegments) ? Dumper::toHtml($template->urlSegments) : '').'</td>
                    </tr>
                    <tr>
                        <td>noChildren (Children Allowed)</td>
                        <td>'.($template->noChildren === 1 ? 'No' : 'Yes').'</td>
                    </tr>
                    <tr>
                        <td>noParents (Allow for New Page)</td>
                        <td>'.($template->noParents < 0 ? 'Only One' : ($template->noParents === 1 ? 'No' : 'Yes')).'</td>
                    </tr>
                    <tr>
                        <td>sortfield (Children Sorted By)</td>
                        <td>'.$template->sortfield.'</td>
                    </tr>
                    <tr>
                        <td>cache_time (Cache Time)</td>
                        <td>'.$template->cache_time.'</td>
                    </tr>
                </table>';
            }
        }


        // Fields List & Values
        if(in_array('fieldsListValues', $panelSections) && $isPwPage) {

            $fieldsListValuesColumns = array('id', 'name', 'label', 'type', 'inputfieldType/class', 'Adminer', 'unformatted', 'formatted', 'image details', 'settings');

            if(!isset($adminerUrl)) {
                if (($key = array_search('Adminer', $fieldsListValuesColumns)) !== false) {
                    unset($fieldsListValuesColumns[$key]);
                }
            }
            if(!\TracyDebugger::getDataValue('imagesInFieldListValues')) {
                if (($key = array_search('image details', $fieldsListValuesColumns)) !== false) {
                    unset($fieldsListValuesColumns[$key]);
                }
            }

            $fieldsListValues = $this->sectionHeader($fieldsListValuesColumns);

            $value = array();
            foreach($p->fields as $f) {
                $fieldArray['settings'] = $p->template->fieldgroup->getField($f, true)->getArray();
                $settings = Dumper::toHtml($fieldArray['settings'], array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => true));
                $fieldsListValues .= "\n<tr>" .
                    "<td>$f->id</td>" .
                    '<td><a title="Edit Field" href="'.$this->wire('config')->urls->admin.'setup/field/edit?id='.$f->id.'">'.$f->name.'</a></td>' .
                    "<td>$f->label</td>" .
                    "<td>".str_replace('Fieldtype', '', $f->type)."</td>" .
                    "<td>".str_replace('Inputfield', '', ($f->inputfield ? $f->inputfield : $f->inputfieldClass))."</td>";
                    if(isset($adminerUrl)) $fieldsListValues .= "<td><a href='".$adminerUrl."?edit=field_".$f->name."&where%5Bpages_id%5D=".$p->id."'>".$adminerIcon."</a></td>";
                    $fieldsListValues .= "<td>".$this->generateOutput($p, $f, false)."</td>" .
                    "<td>".$this->generateOutput($p, $f, true)."</td>";
                    if(\TracyDebugger::getDataValue('imagesInFieldListValues')) $fieldsListValues .= "<td>".$this->imageDetails($p, $f)."</td>";
                    $fieldsListValues .= "<td>".$settings."</td>" .
                    "</tr>";
            }
            $fieldsListValues .= $sectionEnd;
        }


        // Server Request Info
        if(in_array('serverRequest', $panelSections)) {
            $serverRequest = '
            <table>';
            foreach($_SERVER as $k => $v) {
                $serverRequest .= '
                    <tr>
                        <td>'.$k.'</td>
                        <td>'.Dumper::toHtml($v).'</td>
                    </tr>
                ';
            }
            $serverRequest .= '</table>
            ';
        }


        // Input GET, POST, & COOKIE
        if(in_array('inputGet', $panelSections) || in_array('inputPost', $panelSections) || in_array('inputCookie', $panelSections)) {
            $inputTypes = array();
            if(in_array('inputGet', $panelSections)) {
                $input_oc['get'] = 0;
                $inputTypes[] = 'get';
            }
            if(in_array('inputPost', $panelSections)) {
                $input_oc['post'] = 0;
                $inputTypes[] = 'post';
            }
            if(in_array('inputCookie', $panelSections)) {
                $input_oc['cookie'] = 0;
                $inputTypes[] = 'cookie';
            }
            foreach($inputTypes as $type) {
                $typeuc = ucfirst($type);
                $i = $this->wire('input')->$type;
                if(!count($i)) continue;
                ${"input$typeuc"} = $this->sectionHeader(array('Key', 'Value'));
                foreach($i as $key => $value) {
                    $input_oc[$type]++;
                    if(is_array($value)) $value = print_r($value, true);
                    ${"input$typeuc"} .= "<tr><td>" . $this->wire('sanitizer')->entities($key) . "</td><td><pre>" . $this->wire('sanitizer')->entities($value) . "</pre></td></tr>";
                }
                ${"input$typeuc"} .= $sectionEnd;
            }
        }

        // Session
        if(in_array('session', $panelSections)) {
            $session_oc = 0;
            $session = $this->sectionHeader(array('Key', 'Value'));
            foreach($this->wire('session') as $key => $value) {
                if(
                    $key == 'tracyDumpItemsAjax' ||
                    $key == 'tracyDumpsRecorderItems' ||
                    $key == 'tracyEventItems' ||
                    $key == 'tracyMailItems' ||
                    $key == 'tracyIncludedFiles' ||
                    $key == 'tracyPostData' ||
                    $key == 'tracyGetData' ||
                    $key == 'tracyWhitelistData' ||
                    $key == 'tracyLoginUrl'
                ) continue;
                $session_oc++;
                if(is_object($value)) $value = (string) $value;
                if(is_array($value)) $value = print_r($value, true);
                $session .= "<tr><td>".$this->wire('sanitizer')->entities($key)."</td><td><pre>" . $this->wire('sanitizer')->entities($value) . "</pre></td></tr>";
            }
            $session .= $sectionEnd;
        }


        // Page, Template, and Field Objects
        if($isPwPage) {
            if(in_array('pageObject', $panelSections)) $pageObject = Dumper::toHtml($p, array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => false));
            if(in_array('templateObject', $panelSections)) $templateObject = Dumper::toHtml($p->template, array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => false));
            if(in_array('fieldsObject', $panelSections)) $fieldsObject = Dumper::toHtml($p->fields, array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => false));
        }



        // Load all the panel sections
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = '
        <h1>' . $this->icon . ' Request Info' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'RequestInfoPanel'.($isAdditionalBar ? '-'.$isAdditionalBar : '').'\')">+</a></span></span>
        <div class="tracy-inner">
        ';

        // all the "non" icon links sections
        $i=0;
        foreach(\TracyDebugger::$requestInfoSections as $name => $label) {
            // get all sections excluding those that are admin "links"
            $counter = '';
            if(strpos($name, 'Links') === false && in_array($name, $panelSections)) {
                if(isset(${$name}) && ${$name} !== '') {
                    if($name == 'inputGet') $counter = ' (' . $input_oc['get'] . ')';
                    if($name == 'inputPost') $counter = ' (' . $input_oc['post'] . ')';
                    if($name == 'inputCookie') $counter = ' (' . $input_oc['cookie'] . ')';
                    if($name == 'session') $counter = ' (' . $session_oc . ')';
                    $out .= '
                    <a href="#" rel="'.$name.'" class="tracy-toggle '.($name == 'pageInfo' && $i==0 ? '' : ' tracy-collapsed').'">'.$label.$counter.'</a>
                    <div id="'.$name.'" '.($name == 'pageInfo' && $i==0 ? '' : ' class="tracy-collapsed"').'>'.${$name}.'</div><br />';
                    $i++;
                }
            }
        }


        if(in_array('editLinks', $panelSections)) {
            $out .= '
            <div class="pw-info-links" style="text-align: right; border-top:1px solid #CCCCCC; margin-top:10px; padding-top:10px;">
            ';
            if($isPwPage && !\TracyDebugger::$inAdmin) {
                $out .= '
                <a onclick="tracyClosePanel(\'RequestInfo\')" href="'.$this->wire('config')->urls->admin.'page/edit/?id='.$p->id.'" title="Edit this page">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 528.899 528.899" style="enable-background:new 0 0 528.899 528.899;" xml:space="preserve">
                        <path d="M328.883,89.125l107.59,107.589l-272.34,272.34L56.604,361.465L328.883,89.125z M518.113,63.177l-47.981-47.981   c-18.543-18.543-48.653-18.543-67.259,0l-45.961,45.961l107.59,107.59l53.611-53.611   C532.495,100.753,532.495,77.559,518.113,63.177z M0.3,512.69c-1.958,8.812,5.998,16.708,14.811,14.565l119.891-29.069   L27.473,390.597L0.3,512.69z" fill="'.\TracyDebugger::COLOR_NORMAL.'"/>
                    </svg>
                </a>&nbsp;';
            }
            if(isset($templateFileEditorLink) && $templateFileEditorLink != '') {
                $out .= $templateFileEditorLink . '&nbsp';
            }
            $out .= '</div>';
        }

        $out .= '<br />';
        $out .= \TracyDebugger::generatePanelFooter('requestInfo', \Tracy\Debugger::timer('requestInfo'), strlen($out), 'requestInfoPanel');
        $out .= '</div>';

        return parent::loadResources() . $out;
    }


    private function generateOutput($p, $f, $outputFormatting) {
        $out = '';
        $value = $outputFormatting ? $this->wire('sanitizer')->entities1($p->getFormatted($f->name)) : $p->getUnformatted($f->name);
        if(is_string($value) && $outputFormatting) {
            $out .= substr($value, 0, \TracyDebugger::getDataValue('maxLength')) . (strlen($value) > 99 ? '... ('.strlen($value).')' : '');
        }
        else {
            // trycatch is to prevent panel errors if an image is missing
            // log the error to the Tracy error logs instead
            try {
                $out .= Dumper::toHtml($value, array(Dumper::LIVE => true, Dumper::DEBUGINFO => \TracyDebugger::getDataValue('debugInfo'), Dumper::DEPTH => 99, Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE_COUNT => 1, Dumper::COLLAPSE => false));
            }
            catch(Exception $e) {
                \TD::log($e);
            }
        }
        return $out;
    }


    private function imageDetails($p, $f) {
        $of = $p->of();
        $p->of(false);
        $imageStr = '';
        $imagePreview = '';
        $inputfield = \TracyDebugger::getDataValue('imagesInFieldListValues') ? $f->getInputfield($p) : null;

        if($f->type instanceof FieldtypeRepeater) {
						$repeaterValue = $p->get($f->name);
						if($repeaterValue instanceof Page) $repeaterValue = array($repeaterValue); //support for FieldtypeFieldsetPage
						if($repeaterValue) {
                foreach($repeaterValue as $subpage) {
                    $imageStr .= $this->getImages($subpage);
                }
            }
        }
        elseif($f->type instanceof FieldtypePage) {
            if(is_object($p->$f)) {
                $fieldArray = array();
                if($p->$f instanceof PageArray) {
                    foreach($p->$f as $subpage) {
                        $imageStr .= $this->getImages($subpage);
                    }
                }
                else {
                    $imageStr .= $this->getImages($p->$f);
                }
            }
        }
        elseif($f->type instanceof FieldtypeImage) {
            foreach($p->$f as $image) {
                $imageStr .= $this->imageStr($inputfield, $image);
            }
        }
        $p->of($of);
        return $imageStr;
    }


    private function getImages($p) {
        $p_of = $p->of();
        $p->of(false);
        $imageStr = '';
        foreach($p as $field => $item) {
            $f = $this->wire('fields')->get($field);
            // this is for nested repeaters
            if($item && $f && $f->type instanceof FieldTypeRepeater) {
								$repeaterValue = $p->get($f->name);
								if($repeaterValue instanceof Page) $repeaterValue = array($repeaterValue); //support for FieldtypeFieldsetPage
								if($repeaterValue) {
                		foreach($repeaterValue as $subpage) {
                    		$imageStr .= $this->getImages($subpage);
                		}
                }
            }
            elseif($item && $f && $f->type instanceof FieldTypeImage) {
                $inputfield = \TracyDebugger::getDataValue('imagesInFieldListValues') ? $f->getInputfield($p) : null;
                foreach($item as $image) {
                    $imageStr .= $this->imageStr($inputfield, $image);
                }
            }
        }
        $p->of($p_of);

        return $imageStr;
    }


    private function imageStr($inputfield, $image) {
        $imagePreview = '';
        if(isset($inputfield) && $inputfield) {
            $thumb = $inputfield->getAdminThumb($image);
            $thumb = $thumb['thumb'];
            $imagePreview = '<a class="pw-modal" href="'.$image->url.'"><img style="padding:5px 0" width="125" src="'.$thumb->url.'" /></a><br />';
        }
        return '<p><strong>'.$image->name.'</strong><br />'.$imagePreview.'description: '.$image->description.'<br />tags: '.$image->tags.'<br />dimensions: '.$image->width.'x'.$image->height.'<br />size: '.$image->filesizeStr.'<br />variations: '.$this->variationsStr($image).'</p><br />';
    }


    private function variationsStr($image) {
        $variationsArr = array();
        foreach($image->getVariations() as $var) {
            $variationsArr[] = $var->width . 'x' . $var->height . '&nbsp;(' . str_replace(' ', '&nbsp;', $var->filesizeStr) . ')';
        }
        return implode (', ', $variationsArr);
    }


    private function getLanguageVersion($p, $fieldName, $lang, $showDefault = false) {
        if($this->wire('languages')) {
            $p->of(false);
            $result = '';
            if($fieldName == 'name') {
                if($this->wire('modules')->isInstalled("LanguageSupportPageNames")) {
                    $result = $p->localName($lang);
                }
                elseif($lang->isDefaultLanguage) {
                    $result = $p->$fieldName;
                }
            }
            elseif($fieldName == 'title') {
                if(!$this->wire('modules')->isInstalled("FieldtypePageTitleLanguage") || !$this->wire('fields')->get('title')->type instanceof FieldtypePageTitleLanguage) {
                    $result = $lang->isDefaultLanguage ? $p->$fieldName : '';
                }
                elseif(is_object($p->$fieldName)) {
                    $result = $p->$fieldName->getLanguageValue($lang);
                }
                else {
                    $result = $p->$fieldName;
                }
            }
            return $result == '' && $showDefault ? '<span title="No '.$fieldName.' for '.$lang->title.'" style="color:#000000; font-weight: bold" aria-hidden="true">&#9432; </span>' . $p->$fieldName : $result;
        }
        else {
            return $p->$fieldName;
        }
    }

}
