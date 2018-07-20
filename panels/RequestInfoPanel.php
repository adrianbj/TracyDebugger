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
                    c0.1,0.1,0.2,0.2,0.2,0.4h-3.8V212.7z M224.5,225.9h-10.4v-13.3h5.2v4.3c0,0.3,0.1,0.5,0.3,0.7c0.2,0.2,0.4,0.3,0.7,0.3h4.2V225.9z" fill="#444444"/>
                <path d="M222.8,221.9h-7.2c-0.1,0-0.2,0-0.2,0.1c-0.1,0.1-0.1,0.1-0.1,0.2v0.7c0,0.1,0,0.2,0.1,0.2c0.1,0.1,0.1,0.1,0.2,0.1h7.2
                    c0.1,0,0.2,0,0.2-0.1c0.1-0.1,0.1-0.1,0.1-0.2v-0.7c0-0.1,0-0.2-0.1-0.2C223,222,222.9,221.9,222.8,221.9z" fill="#444444"/>
                <path d="M215.5,219.4c-0.1,0.1-0.1,0.1-0.1,0.2v0.7c0,0.1,0,0.2,0.1,0.2c0.1,0.1,0.1,0.1,0.2,0.1h7.2c0.1,0,0.2,0,0.2-0.1
                    c0.1-0.1,0.1-0.1,0.1-0.2v-0.7c0-0.1,0-0.2-0.1-0.2c-0.1-0.1-0.1-0.1-0.2-0.1h-7.2C215.6,219.3,215.5,219.3,215.5,219.4z" fill="#444444"/>
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
            $fieldSettings = '';
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
                $field = $this->wire('fields')->get((int)$this->wire('input')->get('id'));
                $fieldSettings = '
                <table>
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
                    </tr>';
                foreach($field->getArray() as $k => $v) {
                    $fieldSettings .= '
                        <tr>
                            <td>'.$k.'</td>
                            <td>'.Dumper::toHtml($v).'</td>
                        </tr>
                    ';
                }
                $fieldSettings .= '</table>
                ';
            }
        }

        // Template Settings
        if(in_array('templateSettings', $panelSections) && $isPwPage) {
            $templateSettings = '';
            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
                $template = $this->wire('templates')->get((int)$this->wire('input')->get('id'));
                $templateSettings = '
                <table>
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
                    </tr>';
                foreach($template->getArray() as $k => $v) {
                    $templateSettings .= '
                        <tr>
                            <td>'.$k.'</td>
                            <td>'.Dumper::toHtml($v).'</td>
                        </tr>
                    ';
                }
                $templateSettings .= '</table>
                ';
            }
        }

        // Module Settings
        if(in_array('moduleSettings', $panelSections) && $isPwPage) {
            $moduleSettings = '';
            if($this->wire('input')->get('name') && $this->wire('page')->process == 'ProcessModule') {
                $moduleName = $this->wire('sanitizer')->name($this->wire('input')->get('name'));
                if($this->wire('modules')->isInstalled($moduleName)) {
                    $moduleObject = $this->wire('modules')->getModule($moduleName, array('noInit' => true));
                    $moduleSettings = '
                    <table>';
                    foreach($this->wire('modules')->getModuleInfoVerbose($moduleName) as $k => $v) {
                        $moduleSettings .= '
                            <tr>
                                <td>'.$k.'</td>
                                <td>'.Dumper::toHtml($v).'</td>
                            </tr>
                        ';
                    }
                    foreach($moduleObject as $k => $v) {
                        $moduleSettings .= '
                            <tr>
                                <td>'.$k.'</td>
                                <td>'.Dumper::toHtml($v).'</td>
                            </tr>
                        ';
                    }
                    $moduleSettings .= '</table>
                    ';
                }
            }
        }


        // Page info
        if(in_array('pageInfo', $panelSections) && $isPwPage) {
            $pageInfo = '
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
                    <td>'.$this->wire('process').'</td>
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
                    <td><a title="Edit User" href="'.$this->wire('config')->urls->admin.'access/users/edit/?id='.$p->modifiedUser->id.'">'.$p->createdUser->name.'</a></td>
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
                    <td><a title="Edit User" href="'.$this->wire('config')->urls->admin.'access/users/edit/?id='.$p->modifiedUser->id.'">'.$p->modifiedUser->name.'</a></td>
                </tr>
                <tr>
                    <td>modified</td>
                    <td>'.date("Y-m-d H:i:s", $p->modified).'</td>
                </tr>
                <tr>
                    <td>Hidden (status)</td>
                    <td>'. ($p->isHidden() ? "&#10004;" : "&#x2718;") .'</td>
                </tr>
                <tr>
                    <td>Unpublished (status)</td>
                    <td>'. ($p->isUnpublished() ? "&#10004;" : "&#x2718;") .'</td>
                </tr>
                <tr>
                    <td>Locked (status)</td>
                    <td>'. ($p->is(Page::statusLocked) ? "&#10004;" : "&#x2718;") .'</td>
                </tr>
            </table>';
        }

        // Language info
        $languageInfo = '';
        if($this->wire('languages') && in_array('languageInfo', $panelSections) && $isPwPage) {
            $languageInfo .= '<table><tr><th>language</th><th>id</th><th>title</th><th>name</th><th>active</th></tr>';
            foreach($this->wire('languages') as $language) {
                $languageInfo .= '<tr><td>' . $language->title . ' ('.$language->name.')</td><td><a title="Edit Language" href="'.$this->wire('config')->urls->admin.'/setup/languages/edit/?id='.$language->id.'">'.$language->id.'</a></td><td>' . $this->getLanguageVersion($p, 'title', $language) . '</td><td>' . $this->getLanguageVersion($p, 'name', $language) . '</td><td>' . ($language->isDefaultLanguage ? 'default' : ($p->get("status{$language->id}") ? "&#10004;" : "&#x2718;")) . '</td></tr>';
            }
            $languageInfo .= '</table>';
        }

        // Template info
        // defining $templateFilePath even if templateInfo not a selected panel because it's used to build the template editing button at the bottom of the panel
        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
            $templateFilePath = $this->wire('templates')->get((int)$this->wire('input')->get('id'))->filename;
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
                    <path fill="#444444" d="M394.6,307.5c-0.1,0.1-0.1,0.1-0.1,0.2l-1,3.2c0,0.1,0,0.2,0.1,0.3c0.1,0.1,0.1,0.1,0.2,0.1c0,0,0,0,0.1,0
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
            // posix_getpwuid doesn't exist on Windows
            if(function_exists('posix_getpwuid')) {
                if(isset($templateFilePath)) {
                    $owner = posix_getpwuid(fileowner($templateFilePath));
                    $group = posix_getgrgid($owner['gid']);
                }
            }
            $permission = !isset($templateFilePath) ? '' : substr(sprintf('%o', fileperms($templateFilePath)), -4);

            if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
                $template = $this->wire('templates')->get((int)$this->wire('input')->get('id'));
            }
            else {
                $template = $p->template;
            }

            $templateInfo = '
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
                        (isset($owner) ? 'user:group: ' . $owner['name'].":".$group['name'] : '') . '<br />
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


        // Fields List & Values
        if(in_array('fieldsListValues', $panelSections) && $isPwPage) {
            $fieldsListValues = $this->sectionHeader(array('id', 'name', 'label', 'type', 'inputfieldType/class', 'unformatted', 'formatted', 'image details', 'settings'));
            $value = array();
            foreach($p->fields as $f) {
                $value = $this->getFieldArray($p,$f);
                $dumpedValue = Dumper::toHtml($value, array(Dumper::LIVE => true, Dumper::DEBUGINFO => \TracyDebugger::getDataValue('debugInfo'), Dumper::DEPTH => 99, Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE_COUNT => 1, Dumper::COLLAPSE => false));
                $fieldArray['settings'] = $p->template->fieldgroup->getField($f, true)->getArray();
                $settings = Dumper::toHtml($fieldArray['settings'], array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => true));

                $fieldsListValues .= "\n<tr>" .
                    "<td>$f->id</td>" .
                    '<td><a title="Edit Field" href="'.$this->wire('config')->urls->admin.'setup/field/edit?id='.$f->id.'">'.$f->name.'</a></td>' .
                    "<td>$f->label</td>" .
                    "<td>".str_replace('Fieldtype', '', $f->type)."</td>" .
                    "<td>".str_replace('Inputfield', '', ($f->inputfield ? $f->inputfield : $f->inputfieldClass))."</td>" .
                    "<td>".$dumpedValue."</td>" .
                    "<td>".$p->getFormatted($f->name)."</td>" .
                    "<td>".$this->imageDetails($p, $f)."</td>" .
                    "<td>".$settings."</td>" .
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
        <h1>' . $this->icon . ' Request Info' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1><span class="tracy-icons"><span class="resizeIcons"><a href="javascript:void(0)" title="halfscreen" rel="min" onclick="tracyResizePanel(\'RequestInfoPanel\', \'halfscreen\')">▼</a> <a href="javascript:void(0)" title="fullscreen" rel="max" onclick="tracyResizePanel(\'RequestInfoPanel\', \'fullscreen\')">▲</a></span></span>
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
                        <path d="M328.883,89.125l107.59,107.589l-272.34,272.34L56.604,361.465L328.883,89.125z M518.113,63.177l-47.981-47.981   c-18.543-18.543-48.653-18.543-67.259,0l-45.961,45.961l107.59,107.59l53.611-53.611   C532.495,100.753,532.495,77.559,518.113,63.177z M0.3,512.69c-1.958,8.812,5.998,16.708,14.811,14.565l119.891-29.069   L27.473,390.597L0.3,512.69z" fill="#ee1d62"/>
                    </svg>
                </a>&nbsp;';
            }
            if(isset($templateFileEditorLink) && $templateFileEditorLink != '') {
                $out .= $templateFileEditorLink . '&nbsp;
                </div>';
            }
        }

        $out .= '<br />';
        $out .= \TracyDebugger::generatedTimeSize('requestInfo', \Tracy\Debugger::timer('requestInfo'), strlen($out));
        $out .= '</div>';

        return parent::loadResources() . $out;
    }


    private function getFieldArray(Page $p, $f) {
        $of = $p->of();
        $p->of(false);
        $fieldArray = '';
        if($f->type == "FieldtypeFieldsetTabOpen"
           || $f->type == "FieldtypeFieldsetTabClose"
           || $f->type == "FieldtypeFieldsetOpen"
           || $f->type == "FieldtypePassword"
           || $f->type == "FieldtypeFieldsetClose") return false;

        if($f->type instanceof FieldtypeRepeater) {
            if(is_object($p->$f) && count($p->$f)) {
                $fieldArray = array();
                foreach($p->$f as $o) $fieldArray[$o->id] = $o->getIterator();
            }
        }
        elseif($f->type instanceof FieldtypePage) {
            if(is_object($p->$f) && count($p->$f)) {
                $fieldArray = array();
                if($p->$f instanceof PageArray) {
                    foreach($p->$f as $o) $fieldArray[$o->id] = $o->getIterator();
                }
                else {
                    if($p->$f) $fieldArray[$p->$f->id] = $p->$f->getIterator();
                        else $fieldArray = '';
                }
            }
            else {
                $fieldArray = $p->$f;
            }
        }
        elseif($f->type instanceof FieldtypeFile) {
            $fieldArray = array();
            $fieldArray = $p->$f->getIterator();
        }
        else {
            $fieldArray = $p->$f ?: '';
        }
        $p->of($of);
        return $fieldArray;
    }


    private function imageDetails($p, $f) {
        $of = $p->of();
        $p->of(false);
        $imageStr = '';
        $imagePreview = '';
        $inputfield = \TracyDebugger::getDataValue('imagesInFieldListValues') ? $f->getInputfield($p) : null;

        if($f->type instanceof FieldtypeRepeater) {
            if(is_object($p->$f) && count($p->$f)) {
                foreach($p->$f as $subpage) {
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
                foreach($p->$f as $subpage) {
                    $imageStr .= $this->getImages($subpage);
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
