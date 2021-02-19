<?php

class PageFilesPanel extends BasePanel {

    // settings
    private $icon;
    private $name = 'pageFiles';
    private $label = 'Page Files';
    private $p = false;
    private $missingFiles = array();
    private $orphanFiles = array();
    private $tempFiles = array();
    private $numMissingFiles = 0;
    private $filesListStr;

    /**
     * define the tab for the panel in the debug bar
     */
    public function getTab() {

        \Tracy\Debugger::timer($this->name);

        if(\TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') && ($this->wire('process') == 'ProcessPageEdit' || $this->wire('process') == 'ProcessUser')) {
            $this->p = $this->wire('process')->getPage();
        }
        elseif($this->wire('process') == 'ProcessPageView') {
            $this->p = $this->wire('page');
        }

        if(!$this->p) return;

        $diskFiles = $this->getDiskFiles($this->p);
        $pageFiles = $this->getPageFiles($this->p);
        $numDiskFiles = count($diskFiles, COUNT_RECURSIVE) - count($diskFiles);

        if($numDiskFiles == 0 && count($pageFiles) == 0) {
            $this->filesListStr .= '<p>There are no files associated with this page.';
        }
        else {
            foreach($pageFiles as $pid => $files) {
                $pageFilesBasenames[$pid] = array();
                $fileFields[$pid] = array();
                $this->missingFiles[$pid] = array();
                foreach($files as $file) {
                    $basename = pathinfo($file['filename'], PATHINFO_BASENAME);
                    array_push($pageFilesBasenames[$pid], $basename);
                    $fileFields[$pid][$basename] = $file['field'];
                    if(!in_array($basename, $diskFiles[$pid])) {
                        $this->numMissingFiles++;
                        array_push($this->missingFiles[$pid], $file);
                    }
                }
            }

            foreach($diskFiles as $pid => $files) {
                if(empty($files) && !isset($this->missingFiles[$pid])) continue;
                $p = $this->wire('pages')->get($pid);
                $repeaterFieldName = strpos($p->template->name, 'repeater_') !== false ? ' ('.substr($p->template->name, 9).')' : '';
                if(isset($currentPID)) $this->filesListStr .= '</table></div>';
                if(!isset($currentPID) || $pid !== $currentPID) {
                    $this->filesListStr .= '
                        <h2><strong>#'.$pid . '</strong> ' . $repeaterFieldName.'</h2>
                        <div class="tracyPageFilesPage">
                            <table>
                                <th>Filename</th><th>Filesize</th><th>Modified</th><th>Field</th>'.(count($this->tempFiles) > 0 ? '<th>Temp</th>' : '');
                }

                foreach($files as $file) {
                    if(isset($pageFilesBasenames[$pid]) && in_array($file, $pageFilesBasenames[$pid])) {
                        $style = '';
                        $fileField = $fileFields[$pid][$file];
                    }
                    else {
                        $style = 'color: ' . \TracyDebugger::COLOR_WARN;
                        $fileField = '';
                        $this->orphanFiles[] = $p->filesManager()->path . $file;
                    }
                    $this->filesListStr .= '
                    <tr>
                        <td><a style="'.$style.' !important" href="'.$p->filesManager()->url.$file.'">'.$file.'</a></td>
                        <td>'.\TracyDebugger::human_filesize(filesize($p->filesManager()->path . $file)).'</td>
                        <td>'.date('Y-m-d H:i:s', filemtime($p->filesManager()->path . $file)).'</td>
                        <td style="width: 1px">'.$fileField.'</td>' .
                        (count($this->tempFiles) > 0 ? '<td style="text-align: center">'.(in_array($p->filesManager()->path.$file, $this->tempFiles) ? '	✔' : '').'</td>' : '') . '
                    </tr>';
                }

                if(isset($this->missingFiles[$pid])) {
                    foreach($this->missingFiles[$pid] as $missingFile) {
                        $this->filesListStr .= '
                        <tr>
                            <td><span style="color: ' . \TracyDebugger::COLOR_ALERT . ' !important">'.pathinfo($missingFile['filename'], PATHINFO_BASENAME).'</td>
                            <td></td>
                            <td></td>
                            <td>'.$missingFile['field'].'</td>' .
                            (count($this->tempFiles) > 0 ? '<td></td>' : '') . '
                        </tr>';
                    }
                }

                $currentPID = $pid;
            }
            $this->filesListStr .= '
                </table>
            </div>';
        }

        if($this->numMissingFiles > 0) {
            $iconColor = \TracyDebugger::COLOR_ALERT;
        }
        elseif(count($this->orphanFiles) > 0) {
            $iconColor = \TracyDebugger::COLOR_WARN;
        }
        else {
            $iconColor = \TracyDebugger::COLOR_NORMAL;
        }

        // the svg icon shown in the bar and in the panel header
        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" width="23.3" height="16" viewBox="244.4 248 23.3 16">
            <path fill="'.$iconColor.'" d="M267.6 255.2l-3.2 8.8h-17.6l3.3-8.8c.3-.7 1.2-1.4 2-1.4h14.5c.8 0 1.2.6 1 1.4zm-21.8 7.3l3-7.9c.5-1.3 1.9-2.3 3.3-2.3h12.7c0-.8-.7-1.5-1.5-1.5h-10.2l-1.5-2.9h-5.8c-.8 0-1.5.7-1.5 1.5V261c.1.9.7 1.5 1.5 1.5z"/>
        </svg>
        ';

        $orphanMissingCounts = '';
        if($this->numMissingFiles > 0 || count($this->orphanFiles) > 0) {
            $orphanMissingCounts = $this->numMissingFiles . '/' . count($this->orphanFiles) . '/';
        }

        return "<span title='{$this->label}'>{$this->icon}".(\TracyDebugger::getDataValue('showPanelLabels') ? $this->label : '')." ".$orphanMissingCounts.($numDiskFiles > 0 ? $numDiskFiles : '')."</span>";
    }

    /**
     * the panel's HTML code
     */
    public function getPanel() {
        $isAdditionalBar = \TracyDebugger::isAdditionalBar();
        $out = "<h1>{$this->icon} {$this->label}" . ($isAdditionalBar ? " (".$isAdditionalBar.")" : "") . "</h1>";

        $out .= '<span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'' . $this->className . '\')">+</a></span></span>';

        // panel body
        $out .= '<div class="tracy-inner">';

        $numOrphanFiles = count($this->orphanFiles);

        if($numOrphanFiles > 0) {
            $out .= '
            <form style="display:inline" method="post" action="'.\TracyDebugger::inputUrl(true).'" onsubmit="return confirm(\'Do you really want to delete all the orange highlighted orphan files?\');">
                <input type="hidden" name="orphanPaths" value="'.implode('|', $this->orphanFiles).'" />
                <input type="submit" style="color:'.\TracyDebugger::COLOR_WARN.' !important; color: #FFFFFF" name="deleteOrphanFiles" value="Delete '.$numOrphanFiles.' orphan'._n('', 's', $numOrphanFiles).'" />
            </form>&nbsp&nbsp;';
        }

        if($this->numMissingFiles > 0) {
            $out .= '
            <form style="display:inline" method="post" action="'.\TracyDebugger::inputUrl(true).'" onsubmit="return confirm(\'Do you really want to delete all the red highlighted missing pagefiles?\');">
                <input type="hidden" name="missingPaths" value="'.urlencode(json_encode($this->missingFiles)).'" />
                <input type="submit" style="color:'.\TracyDebugger::COLOR_ALERT.' !important; color: #FFFFFF" name="deleteMissingFiles" value="Delete '.$this->numMissingFiles.' missing pagefile'._n('', 's', $this->numMissingFiles).'" />
            </form>';
        }

        if($numOrphanFiles > 0 || $this->numMissingFiles > 0) {
            $out .= '<br /><br />';
        }

        $out .= '<div id="tracyPageFilesList">'.$this->filesListStr.'</div>';

        $out .= \TracyDebugger::generatePanelFooter($this->name, \Tracy\Debugger::timer($this->name), strlen($out));
        $out .= '</div>';

        return parent::loadResources() . $out;
    }


    /**
     * Return files from page's assets/files folder
     *
     * @param Page
     * @return array $files fullpaths to files
     */
    private function getDiskFiles($p) {
        if(!$p->filesManager()) return array();
        $files = array();
        $p_of = $p->of();
        $p->of(false);
        $filesDir = $p->filesManager()->path;
        foreach($p->fields as $f) {
            // this is for nested repeaters
            if($f && $f->type instanceof FieldTypeRepeater) {
                $repeaterValue = $p->{$f->name};
                if($repeaterValue) {
                    if($repeaterValue instanceof Page) $repeaterValue = array($repeaterValue);
                    foreach($repeaterValue as $subpage) {
                        $files += $this->getDiskFiles($subpage);
                    }
                }
            }
            else {
                $files[$p->id] = array_slice(scandir($filesDir), 2);
            }
        }
        $p->of($p_of);
        return $files;
    }


    /**
     * Returns all pagefiles, including variations
     *
     * @param Page
     * @return array $files Page ID keyed array of paths and field names
     */
    private function getPageFiles($p) {
        $files = array();
        $i=0;
        $p_of = $p->of();
        $p->of(false);
        foreach($p as $field => $item) {
            $f = $this->wire('fields')->get($field);
            // this is for nested repeaters
            if($item && $f && $f->type instanceof FieldTypeRepeater) {
                $repeaterValue = $p->{$f->name};
                if($repeaterValue) {
                    if($repeaterValue instanceof Page) $repeaterValue = array($repeaterValue);
                    foreach($repeaterValue as $subpage) {
                        $files += $this->getPageFiles($subpage);
                    }
                }
            }
            elseif($item && $f && $f->type instanceof FieldTypeFile) {
                foreach($item as $file) {
                    $files[$p->id][$i]['filename'] = $file->filename;
                    $files[$p->id][$i]['field'] = $f->name;
                    if($file->isTemp()) $this->tempFiles[$i] = $file->filename;
                    $i++;
                    if($file instanceof Pageimage) {
                        if($file->webp && file_exists($file->webp->filename)) {
                            $files[$p->id][$i]['filename'] = $file->webp->filename;
                            $files[$p->id][$i]['field'] = $f->name;
                            $i++;
                        }
                        foreach($file->getVariations() as $var) {
                            $files[$p->id][$i]['filename'] = $var->filename;
                            $files[$p->id][$i]['field'] = $f->name;
                            if($var->isTemp()) $this->tempFiles[$i] = $var->filename;
                            $i++;

                            if($var->webp && file_exists($var->webp->filename)) {
                                $files[$p->id][$i]['filename'] = $var->webp->filename;
                                $files[$p->id][$i]['field'] = $f->name;
                                $i++;
                            }
                        }
                    }
                }
            }
        }
        $p->of($p_of);
        return $files;
    }

}
