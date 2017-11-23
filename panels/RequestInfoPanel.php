<?php

use Tracy\Dumper;

/**
 * Custom PW panel
 */

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
                    $module = $this->wire('modules')->get($moduleName);
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
                    foreach($module->getArray() as $k => $v) {
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
                $prevPage = $p->prevAll("include=all")->first();
                if($prevPage) {
                    $pageInfo .= '
                    <tr>
                        <td>prev (sibling)</td>
                        <td>' . ($prevPage->viewable() ? '<a title="View Prev Sibling" href="'.$prevPage->url.'">'.$this->getLanguageVersion($prevPage, 'name', $userLang, true).'</a>' : '<span title="Not Viewable">'.$this->getLanguageVersion($prevPage, 'name', $userLang, true).'</span>') . ' (<a title="Edit Prev Sibling" href="'.$prevPage->editUrl().'">'.$prevPage->id.'</a>)</td>
                    </tr>';
                }
                $nextPage = $p->nextAll("include=all")->first();
                if($nextPage) {
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
        // defining $templateFileEditorPath even if templateInfo not a selected panel because it's used to build the template editing button at the bottom of the panel
        if($isPwPage) {
            if(file_exists($p->template->filename)) $templateFilePath = $p->template->filename;
        }
        else {
            $templateFilePath = $_SERVER['SCRIPT_FILENAME'];
        }
        $templateFileEditorPath = isset($templateFilePath) ? str_replace('%file', $templateFilePath, str_replace('%line', '1', \TracyDebugger::getDataValue('editor'))) : '';
        if(\TracyDebugger::getDataValue('localRootPath') != '') $templateFileEditorPath = str_replace($this->wire('config')->paths->root, \TracyDebugger::getDataValue('localRootPath'), $templateFileEditorPath);

        if(in_array('templateInfo', $panelSections) && $isPwPage) {
            // posix_getpwuid doesn't exist on Windows
            if(function_exists('posix_getpwuid')) {
                if(isset($templateFilePath)) {
                    $owner = posix_getpwuid(fileowner($templateFilePath));
                    $group = posix_getgrgid($owner['gid']);
                }
            }
            $permission = !isset($templateFilePath) ? '' : substr(sprintf('%o', fileperms($templateFilePath)), -4);

            $templateInfo = '
            <table>
                <tr>
                    <td>label</td>
                    <td>'.($this->wire('languages') ? $p->template->getLabel($userLang) : $p->template->label).'</td>
                </tr>
                <tr>
                    <td>name</td>
                    <td><a title="Edit Template" href="'.$this->wire('config')->urls->admin.'setup/template/edit?id='.$p->template->id.'">'.$p->template->name.'</a></td>
                </tr>
                <tr>
                    <td>id</td>
                    <td>'.$p->template->id.'</td>
                </tr>
                <tr>
                    <td>modified</td>
                    <td>'.date("Y-m-d H:i:s", $p->template->modified).'</td>
                </tr>
                <tr>
                    <td>fieldgroup</td>
                    <td>'.$p->template->fieldgroup.'</td>
                </tr>
                <tr>
                    <td>filename</td>
                    <td>'.(isset($templateFilePath) ? '<a title="Edit Template File" href="'.$templateFileEditorPath.'">'.str_replace($this->wire('config')->paths->root, '/', $templateFilePath).'</a>' . '<br />
                        modified: ' . date("Y-m-d H:i:s", filemtime($templateFilePath)) . '<br />' .
                        (isset($owner) ? 'user:group: ' . $owner['name'].":".$group['name'] : '') . '<br />
                        permissions: ' . $permission
                         : 'No file').'</td>
                </tr>
                <tr>
                    <td>compile</td>
                    <td>'.($p->template->compile == 0 ? 'No' : ($p->template->compile == 1 ? 'Yes (template file only)' : 'Yes (and included files)')).'</td>
                </tr>
                <tr>
                    <td>contentType</td>
                    <td>'.$p->template->contentType.'</td>
                </tr>
                <tr>
                    <td>allowPageNum</td>
                    <td>'.($p->template->allowPageNum ? 'Enabled' : 'Disabled').'</td>
                </tr>
                <tr>
                    <td>urlSegments</td>
                    <td>'.($p->template->urlSegments ? 'Enabled' : 'Disabled').'</td>
                </tr>
                <tr>
                    <td>noChildren (Children Allowed)</td>
                    <td>'.($p->template->noChildren ? 'No' : 'Yes').'</td>
                </tr>
                <tr>
                    <td>noParents (Allow for New Page)</td>
                    <td>'.($p->template->noParents < 0 ? 'Only One' : ($p->template->noParents == 1 ? 'No' : 'Yes')).'</td>
                </tr>
                <tr>
                    <td>sortfield (Children Sorted By)</td>
                    <td>'.$p->template->sortfield.'</td>
                </tr>
                <tr>
                    <td>cache_time (Cache Time)</td>
                    <td>'.$p->template->cache_time.'</td>
                </tr>
            </table>';
        }


        // Fields List & Values
        if(in_array('fieldsListValues', $panelSections) && $isPwPage) {
            // TODO - this is a mess - very repetitive and needs cleaning up a lot
            $fieldsListValues = $this->sectionHeader(array('id', 'name', 'label', 'type', 'inputfieldType/class', 'returns', 'value', 'settings'));
            foreach($p->fields as $f) {
                if(is_object($p->$f)) {
                    $fieldArray = array();
                    foreach($p->$f as $key => $item) {
                        if(is_object($item)) {
                            foreach($item as $type => $value) {
                                // TODO this is a temp fix for situations where the type is: 0
                                // need to figure out why and deal with properly
                                if($type === 0) break 2;
                                if($type == 'created' || $type == 'modified' || $type == 'published') $value .= ' ('.date("Y-m-d H:i:s", $value).')';
                                if($type == 'created_users_id' || $type == 'modified_users_id') $value .= ' ('.$this->wire('users')->get($value)->name.')';

                                if(is_object($value)) {
                                    $outValue = method_exists($value,'getArray') ? $value->getArray() : $value;
                                    // run getValue() on as many levels as the Max Nesting Depth config setting
                                    for($i=0;$i<=\TracyDebugger::getDataValue('maxDepth');$i++) {
                                        if(is_array($outValue)) {
                                            array_walk_recursive($outValue, function (&$val) {
                                                $val = is_object($val) && method_exists($val,'getArray') ? $val->getArray() : $val;
                                            });
                                        }
                                    }
                                }
                                else {
                                    $outValue = $value;
                                }

                                if(is_array($outValue)) {
                                    $n=0;
                                    foreach($outValue as &$val) {
                                        if(is_array($val)) {
                                            if(isset($val['created'])) $val['created'] .= ' ('.date("Y-m-d H:i:s", $val['created']).')';
                                            if(isset($val['modified'])) $val['modified'] .= ' ('.date("Y-m-d H:i:s", $val['modified']).')';
                                            if($value instanceof PageFiles) {
                                                $val['name'] = $value->eq($n)->name;
                                                $val['filename'] = $value->eq($n)->filename;
                                                $val['ext'] = $value->eq($n)->ext;
                                                $val['url'] = $value->eq($n)->url;
                                                $val['httpUrl'] = $value->eq($n)->httpUrl;
                                                $val['filesize'] = $value->eq($n)->filesize;
                                                $val['filesizeStr'] = $value->eq($n)->filesizeStr;
                                            }
                                            if($value instanceof PageImages) {
                                                $val['width'] = $value->eq($n)->width;
                                                $val['height'] = $value->eq($n)->height;
                                            }
                                            $n++;
                                        }
                                    }
                                }

                                $fieldArray['value'][$key][$type] = $outValue;

                                if($f->type instanceof FieldtypeFile) {
                                    $fieldArray['value'][$key]['name'] = $item->name;
                                    $fieldArray['value'][$key]['filename'] = $item->filename;
                                    $fieldArray['value'][$key]['ext'] = $item->ext;
                                    $fieldArray['value'][$key]['url'] = $item->url;
                                    $fieldArray['value'][$key]['httpUrl'] = $item->httpUrl;
                                    $fieldArray['value'][$key]['filesize'] = $item->filesize;
                                    $fieldArray['value'][$key]['filesizeStr'] = $item->filesizeStr;
                                }
                                if($f->type instanceof FieldtypeImage) {
                                    $fieldArray['value'][$key]['width'] = $item->width;
                                    $fieldArray['value'][$key]['height'] = $item->height;
                                    //just don't think there is any point showing the variations so remove to clean up
                                    unset($fieldArray['value'][$key]['imageVariations']);
                                }
                            }
                        }
                        elseif($f->type instanceof FieldtypeFile || $f->type instanceof FieldtypeImage) {
                            if($f->type instanceof FieldtypeFile) {
                                $fieldArray['value']['basename'] = $p->$f->name;
                                $fieldArray['value']['name'] = $p->$f->name;
                                $fieldArray['value']['filename'] = $p->$f->filename;
                                $fieldArray['value']['ext'] = $p->$f->ext;
                                $fieldArray['value']['url'] = $p->$f->url;
                                $fieldArray['value']['httpUrl'] = $p->$f->httpUrl;
                                $fieldArray['value']['filesize'] = $p->$f->filesize;
                                $fieldArray['value']['filesizeStr'] = $p->$f->filesizeStr;
                            }
                            if($f->type instanceof FieldtypeImage) {
                                $fieldArray['value']['width'] = $p->$f->width;
                                $fieldArray['value']['height'] = $p->$f->height;
                                //just don't think there is any point showing the variations so remove to clean up
                                unset($fieldArray['value']['imageVariations']);
                            }
                            foreach($p->$f->getArray() as $type => $value) {
                                if($type == 'created' || $type == 'modified' || $type == 'published') $value .= ' ('.date("Y-m-d H:i:s", $value).')';
                                if($type == 'created_users_id' || $type == 'modified_users_id') $value .= ' ('.$this->wire('users')->get($value)->name.')';
                                $fieldArray['value'][$type] = $value;
                            }
                        }
                        else {
                            $fieldArray['value'][$key] = $item;
                        }
                    }
                    if(isset($fieldArray['value'])) $value = Dumper::toHtml($fieldArray['value'], array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE_COUNT => 1, Dumper::COLLAPSE => false));
                }
                elseif(is_array($p->$f)) {
                    $value = Dumper::toHtml($p->$f, array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE_COUNT => 1, Dumper::COLLAPSE => false));
                }
                else {
                    $value = $p->$f;
                }
                $fieldArray['settings'] = $f->getArray();
                $settings = Dumper::toHtml($fieldArray['settings'], array(Dumper::LIVE => true, Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => true));

                $fieldsListValues .= "\n<tr>" .
                    "<td>$f->id</td>" .
                    '<td><a title="Edit Field" href="'.$this->wire('config')->urls->admin.'setup/field/edit?id='.$f->id.'">'.$f->name.'</a></td>' .
                    "<td>$f->label</td>" .
                    "<td>".str_replace('Fieldtype', '', $f->type)."</td>" .
                    "<td>".str_replace('Inputfield', '', ($f->inputfield ? $f->inputfield : $f->inputfieldClass))."</td>" .
                    "<td>".gettype($p->$f)."</td>" .
                    "<td>".$value."</td>" .
                    "<td>$settings</td>" .
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
                    $key == 'tracyWhitelistData'
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
        <h1>' . $this->icon . ' Request Info' . ($isAdditionalBar ? ' ('.$isAdditionalBar.')' : '') . '</h1>
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
            if($isPwPage) {
                $out .= '
                <a onclick="closePanel()" href="'.$this->wire('config')->urls->admin.'page/edit/?id='.$p->id.'" title="Edit this page">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 528.899 528.899" style="enable-background:new 0 0 528.899 528.899;" xml:space="preserve">
                        <path d="M328.883,89.125l107.59,107.589l-272.34,272.34L56.604,361.465L328.883,89.125z M518.113,63.177l-47.981-47.981   c-18.543-18.543-48.653-18.543-67.259,0l-45.961,45.961l107.59,107.59l53.611-53.611   C532.495,100.753,532.495,77.559,518.113,63.177z M0.3,512.69c-1.958,8.812,5.998,16.708,14.811,14.565l119.891-29.069   L27.473,390.597L0.3,512.69z" fill="#ee1d62"/>
                    </svg>
                </a>&nbsp;';
            }
            $out .= '
                <a href="'.$templateFileEditorPath.'" title="Edit this' . ($isPwPage ? ' template ' : ' ') . 'file">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 492.014 492.014" style="enable-background:new 0 0 492.014 492.014;" xml:space="preserve" width="16px" height="16px">
                        <g id="XMLID_144_">
                            <path id="XMLID_151_" d="M339.277,459.566H34.922V32.446h304.354v105.873l32.446-32.447V16.223C371.723,7.264,364.458,0,355.5,0   H18.699C9.739,0,2.473,7.264,2.473,16.223v459.568c0,8.959,7.265,16.223,16.226,16.223H355.5c8.958,0,16.223-7.264,16.223-16.223   V297.268l-32.446,32.447V459.566z" fill="#444444"/>
                            <path id="XMLID_150_" d="M291.446,71.359H82.751c-6.843,0-12.396,5.553-12.396,12.398c0,6.844,5.553,12.397,12.396,12.397h208.694   c6.845,0,12.397-5.553,12.397-12.397C303.843,76.912,298.29,71.359,291.446,71.359z" fill="#444444"/>
                            <path id="XMLID_149_" d="M303.843,149.876c0-6.844-5.553-12.398-12.397-12.398H82.751c-6.843,0-12.396,5.554-12.396,12.398   c0,6.845,5.553,12.398,12.396,12.398h208.694C298.29,162.274,303.843,156.722,303.843,149.876z" fill="#444444"/>
                            <path id="XMLID_148_" d="M274.004,203.6H82.751c-6.843,0-12.396,5.554-12.396,12.398c0,6.845,5.553,12.397,12.396,12.397h166.457   L274.004,203.6z" fill="#444444"/>
                            <path id="XMLID_147_" d="M204.655,285.79c1.678-5.618,4.076-11.001,6.997-16.07h-128.9c-6.843,0-12.396,5.553-12.396,12.398   c0,6.844,5.553,12.398,12.396,12.398h119.304L204.655,285.79z" fill="#444444"/>
                            <path id="XMLID_146_" d="M82.751,335.842c-6.843,0-12.396,5.553-12.396,12.398c0,6.843,5.553,12.397,12.396,12.397h108.9   c-3.213-7.796-4.044-16.409-1.775-24.795H82.751z" fill="#444444"/>
                            <path id="XMLID_145_" d="M479.403,93.903c-6.496-6.499-15.304-10.146-24.48-10.146c-9.176,0-17.982,3.647-24.471,10.138   L247.036,277.316c-5.005,5.003-8.676,11.162-10.703,17.942l-14.616,48.994c-0.622,2.074-0.057,4.318,1.477,5.852   c1.122,1.123,2.624,1.727,4.164,1.727c0.558,0,1.13-0.08,1.688-0.249l48.991-14.618c6.782-2.026,12.941-5.699,17.943-10.702   l183.422-183.414c6.489-6.49,10.138-15.295,10.138-24.472C489.54,109.197,485.892,100.392,479.403,93.903z" fill="#444444"/>
                        </g>
                    </svg>
                </a>&nbsp;
            </div>';
        }

        $out .= \TracyDebugger::generatedTimeSize('requestInfo', \Tracy\Debugger::timer('requestInfo'), strlen($out));
        $out .= '</div>';

        return parent::loadResources() . $out;
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
