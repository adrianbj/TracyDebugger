<?php

class PageFilesPanel extends BasePanel {

    // settings
    private $icon;
    private $name = 'pageFiles';
    private $label = 'Page Files';
    private $files = array();
    private $filesList;
    private $orphanFiles = array();
    private $missingFiles = array();
    private $numFiles;
    private $p = false;

    /**
     * define the tab for the panel in the debug bar
     */
    public function getTab() {

        \Tracy\Debugger::timer($this->name);

        if(\TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') && $this->wire('process') == 'ProcessPageEdit') {
            $this->p = $this->wire('process')->getPage();
        }
        elseif($this->wire('process') == 'ProcessPageView') {
            $this->p = $this->wire('page');
        }

        if(!$this->p) return;

        $this->files = $this->getDiskFiles($this->p);
        $this->missingFiles = $this->getMissingFiles($this->p);
        $numFiles = count($this->files, COUNT_RECURSIVE) - count($this->files);

        foreach($this->files as $pid => $files) {
            if(!$files) continue;
            $p = $this->wire('pages')->get($pid);
            $repeaterFieldName = strpos($p->template->name, 'repeater_') !== false ? ' ('.substr($p->template->name, 9).')' : '';
            if(isset($currentPID)) $this->filesList .= '</div>';
            if(!isset($currentPID) || $pid !== $currentPID) {
                $this->filesList .= '
                    <h2>#'.$pid . ' ' . $repeaterFieldName.'</h2>
                    <div class="tracyPageFilesPage">
                ';
            }

            foreach($files as $file) {
                $pageFile = $this->getPagefileFromPath($file, $p);
                if(!$pageFile) {
                    $style = 'color: ' . \TracyDebugger::COLOR_WARN;
                    $fileField = '';
                    $this->orphanFiles[] = $p->filesManager()->path . $file;
                }
                else {
                    $style = '';
                    $fileField = ' ('.$pageFile->field->name.')';
                }
                $this->filesList .= '<a style="'.$style.' !important" href="'.$p->filesManager()->url.$file.'">'.$file.'</a>'.$fileField.'<br />';
            }

            if(isset($this->missingFiles[$pid])) {
                foreach($this->missingFiles[$pid] as $missingFile) {
                    $this->filesList .= '<span style="color: ' . \TracyDebugger::COLOR_ALERT . ' !important">'.pathinfo($missingFile['filename'], PATHINFO_BASENAME).' ('.$missingFile['field'].')<br />';
                }
            }

            $currentPID = $pid;
        }
        $this->filesList .= '</div>';

        if(count($this->missingFiles) > 0) {
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

        return "<span title='{$this->label}'>{$this->icon} ".(count($this->missingFiles) > 0 ? count($this->missingFiles) .'/' : '').(count($this->orphanFiles) > 0 ? count($this->orphanFiles) .'/' : '').($numFiles > 0 ? $numFiles : '')."</span>";
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
            <form style="display:inline" method="post" action="'.\TracyDebugger::inputUrl(true).'" onsubmit="return confirm(\'Do you really want to delete all the highlighted files?\');">
                <input type="hidden" name="orphanPaths" value="'.implode('|', $this->orphanFiles).'" />
                <input type="submit" name="deleteOrphanFiles" value="Delete '.$numOrphanFiles.' Orphan'._n('', 's', $numOrphanFiles).'" />
            </form>
            <br /><br />';
        }

        $out .= '<div id="tracyPageFilesList">'.$this->filesList.'</div>';

        $out .= \TracyDebugger::generatePanelFooter($this->name, \Tracy\Debugger::timer($this->name), strlen($out), 'yourSettingsFieldsetId');
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
        $files = array();
        foreach($p->fields as $f) {

            // this is for nested repeaters
            if($f && $f->type instanceof FieldTypeRepeater) {
                foreach($p->$f as $subpage) {
                    $files += $this->getDiskFiles($subpage);

                }
            }
            else {
                $filesDir = $p->filesManager()->path;
                $files[$p->id] = array_slice(scandir($filesDir), 2);
            }
        }
        return $files;
    }


     /**
     * Returns Pageimage or Pagefile object from file path
     *
     * @param string $filename full path to the file eg. /site/assets/files/1234/file.jpg
     * @param Page|null $page if null, page will be contructed based on id present in the file path
     * @return Pagefile|Pageimage|null
     *
     */
    private function getPagefileFromPath($filename, $page = null) {

        if(is_null($page)) {
            $id = (int) explode('/', str_replace(wire('config')->urls->files, '', $filename))[0];
            $page = wire('pages')->get($id);
        }

        if(!$page->id) return null; // throw new WireException('Invalid page id');

        $basename = basename($filename);

        // get file field types, that includes image file type
        $field = new Field();
        $field->type = wire('modules')->get('FieldtypeFile');
        $fieldtypes = $field->type->getCompatibleFieldtypes($field)->getItems();
        $selector = 'type=' . implode('|', array_keys($fieldtypes));

        foreach($page->fields->find($selector) as $field) {
            $files = $page->getUnformatted($field->name);
            if($files) {
                $file = $files[$basename];
                if($file) return $file; // match found, return Pagefile or Pageimage

                //check for image variations
                foreach($files as $file) {
                    if($file instanceof Pageimage) {
                        $variation = $file->getVariations()->get($basename);
                        if($variation) return $variation; // match found, return Pageimage
                    }
                }
            }
        }

        return null; // no match

    }


    /**
     * Returns all
     */
    private function getMissingFiles($p) {
        $files = array();
        $p_of = $p->of();
        $p->of(false);
        foreach($p as $field => $item) {
            $f = $this->wire('fields')->get($field);
            // this is for nested repeaters
            if($item && $f && $f->type instanceof FieldTypeRepeater) {
                foreach($p->$f as $subpage) {
                    $files += $this->getMissingFiles($subpage);
                }
            }
            elseif($item && $f && $f->type instanceof FieldTypeFile) {
                $i=0;
                foreach($item as $file) {
                    if(!file_exists($file->filename)) {
                        $files[$p->id][$i]['filename'] = $file->filename;
                        $files[$p->id][$i]['field'] = $f->name;
                        $i++;
                    }
                }
            }
        }
        $p->of($p_of);
        return $files;
    }


}
