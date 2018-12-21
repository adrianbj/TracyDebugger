<?php

class PageFilesPanel extends BasePanel {

    // settings
    private $icon;
    private $name = 'pageFiles';
    private $label = 'Page Files';
    private $files = array();
    private $filesList;
    private $orphanFiles = array();
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

        $filesDir = $this->p->filesManager()->path;
        $this->files[$this->p->id] = array_slice(scandir($filesDir), 2);

        foreach($this->p->fields as $f) {
            if($f->type instanceof FieldtypeRepeater) {
                foreach($this->p->$f as $subpage) {
                    $filesDir = $subpage->filesManager()->path;
                    $this->files[$subpage->id] = array_slice(scandir($filesDir), 2);
                }
            }
        }

        $numFiles = count($this->files, COUNT_RECURSIVE) - count($this->files);

        foreach($this->files as $pid => $files) {
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
                $pageFile = $this->getPageimageOrPagefileFromPath($file, $p);
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
            $currentPID = $pid;
        }
        $this->filesList .= '</div>';

        // the svg icon shown in the bar and in the panel header
        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" width="20.6" height="17" viewBox="385.7 297.5 20.6 17">
            <path stroke="'.(count($this->orphanFiles) > 0 ? \TracyDebugger::COLOR_WARN : \TracyDebugger::COLOR_NORMAL).'" stroke-miterlimit="10" d="M405 303.2h-1.1v-2c0-.5-.4-.9-.9-.9h-8.1l-1.6-2.3h-6.1c-.5 0-.9.4-.9.9v14.3c0 .2.1.4.2.5.2.2.4.3.7.3h14.6c.4 0 .7-.3.8-.6l3.3-9.2v-.1c-.1-.5-.4-.9-.9-.9zm-18.1-4.3c0-.1.1-.2.2-.2h5.8l1.6 2.3h8.4c.1 0 .2.1.2.2v2h-12.8-.1c-.3.1-.6.3-.7.6l-2.7 7.4v-12.3zm14.9 14.3c0 .1-.1.1-.2.1H387c-.1 0-.1 0-.1-.1 0 0-.1-.1 0-.1l3.3-9.1c0-.1.1-.1.2-.1H405c.1 0 .2.2.2.3l-3.4 9z"/>
        </svg>
        ';

        return "<span title='{$this->label}'>{$this->icon} ".(count($this->orphanFiles) > 0 ? count($this->orphanFiles) .'/' : '').($numFiles > 0 ? $numFiles : '')."</span>";
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
     * Returns Pageimage or Pagefile object from file path
     * getPageimageOrPagefileFromPath('/site/assets/files/1234/file.jpg'); // returns either Pageimage or Pagefile object
     * getPageimageOrPagefileFromPath('/site/assets/files/1234/file.txt'); // returns Pagefile object
     * getPageimageOrPagefileFromPath('/site/assets/files/1234/none.txt'); // returns null
     *
     * @param string $filename full path to the file eg. /site/assets/files/1234/file.jpg
     * @param Page|null $page if null, page will be contructed based on id present in the file path
     * @return Pagefile|Pageimage|null
     *
     */
    private function getPageimageOrPagefileFromPath($filename, $page = null) {

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


}
