<?php

class TodoPanel extends BasePanel {

    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $todoTypes = array('todo', 'fixme', 'pending', 'xxx', 'hack', 'bug');

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('todo');

        $pathReplace = \TracyDebugger::getDataValue('todoScanModules') == 1 || \TracyDebugger::getDataValue('todoScanAssets') == 1 ? $this->wire('config')->paths->root : $this->wire('config')->paths->templates;

        $numEntries = 0;
        $thisPageNumEntries = 0;
        $files = $this->getTodos();
        $this->entries = $this->sectionHeader(array('File', 'Line', 'Type', 'Comment'));
        $currentFile = '';
        foreach($files as $file => $items) {
            $i=0;
            foreach($items as $item) {
                $numEntries++;
                if($item['file'] == $this->wire('page')->template->filename) {
                    $thisPageFile = ' style="font-weight:bold !important" ';
                    $thisPageNumEntries++;
                }
                else {
                    $thisPageFile = '';
                }

                if(isset($item['file'])) {
                    $this->entries .= "
                        \n<tr>";
                            if($currentFile !== $item['file']) {
                                $this->entries .= "<td" . ($i==0 ? " rowspan='" . count($files[$file]) . "'" : "") . $thisPageFile . ">" . str_replace($pathReplace, '', $item['file']) . "</td>";
                            }
                            // if "todo" or other matched tag is at start of comment then remove it
                            // otherwise, underline it wherever it is in the comment

                            // regex in preg_replace is to match whole words, without hyphens only
                            // see containsTodoType() method comments for more notes on this
                            $replacement = stripos($item['comment'], $item['type']) === 0 ? '' : '<span style="text-decoration:underline">$0</span>';
                            $this->entries .=
                            "<td>".$item['line']."</td>" .
                            "<td>".strtoupper($item['type'])."</td>" .
                            "<td>".\TracyDebugger::createEditorLink($item['file'], $item['line'], preg_replace('/(?<!-)\b'.$item['type'].'\b(?!-)/i', $replacement, $item['comment']))."</td>" .
                        "</tr>";
                }
                $currentFile = $item['file'];
                $i++;
            }
        }
        $this->entries .= '</tbody>
                </table>
            </div>';

        if($thisPageNumEntries > 0) {
            $this->iconColor = \TracyDebugger::COLOR_ALERT;
        }
        elseif($numEntries > 0) {
            $this->iconColor = \TracyDebugger::COLOR_WARN;
        }
        else {
            $this->iconColor = \TracyDebugger::COLOR_NORMAL;
        }

        $this->icon = '
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
                <g>
                    <polygon points="34.564,40.07 5.762,40.07 5.762,11.276 34.564,11.276 34.564,12.788 40.326,8.04 40.326,5.516 0,5.516 0,45.831     40.326,45.831 40.326,20.854 34.564,27.765   " fill="'.$this->iconColor.'"/>
                    <polygon points="13.255,17.135 11.031,19.56 25.245,35.943 50,6.248 48.049,4.169 25.245,22.932   " fill="'.$this->iconColor.'"/>
                </g>
            </svg>
        ';
        return '
        <span title="ToDo">
            ' . $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? 'Todo' : '') . ' ' . $thisPageNumEntries . '/' . $numEntries . '
        </span>
        ';
    }


    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' ToDo</h1>

        <div class="tracy-inner">';
            $out .= $this->entries;

        $out .= \TracyDebugger::generatePanelFooter('todo', \Tracy\Debugger::timer('todo'), strlen($out), 'todoPanel');

        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }


    protected function strpos_array($haystack, $needles, $offset = 0) {
        if($needles == '') return false;
        if(is_array($needles)) {
            foreach($needles as $needle) {
                $pos = $this->strpos_array($haystack, $needle);
                if($pos !== false) {
                    return $pos;
                }
            }
            return false;
        }
        else {
            return strpos($haystack, $needles, $offset);
        }
    }


    protected function containsTodoType($str) {
        foreach($this->todoTypes as $todoType) {
            // match whole words, without hyphens only so that "debug" won't match the "bug" todoType
            // and "micro-clearfix-hack" won't match "hack"
            // this still allows "//TODO" to match even though no space before word
            preg_match('/(?<!-)(?<![\'"])\b'.$todoType.'\b(?!-)(?![\'"])/i', $str, $match, PREG_OFFSET_CAPTURE);
            if(isset($match[0][1])) return $todoType;
        }
        return false;
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


    private function getTodos() {
        $items = array();
        $items = $this->scanDirectories($this->wire('config')->paths->templates);
        if(\TracyDebugger::getDataValue('todoScanModules') == 1) $moduleItems = $this->scanDirectories($this->wire('config')->paths->siteModules);
        if(isset($moduleItems)) $items = array_merge($items, $moduleItems);
        if(\TracyDebugger::getDataValue('todoScanAssets') == 1) $assetsItems = $this->scanDirectories($this->wire('config')->paths->assets);
        if(isset($assetsItems)) $items = array_merge($items, $assetsItems);
        return $items;
    }

    private function scanDirectories($dir) {
        $todoLinesData = $this->wire('cache')->get('TracyToDoData');
        $items = array();
        $ignoreDirs = array_map('trim', explode(',', \TracyDebugger::getDataValue('todoIgnoreDirs')));
        array_push($ignoreDirs, 'TracyDebugger');
        $allowedExtensions = array_map('trim', explode(',', \TracyDebugger::getDataValue('todoAllowedExtensions')));
        foreach($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $fileinfo) {
            $filePath = $fileinfo->getPathname();
            $fileSize = filesize($filePath);
            if($fileSize > 0 && $fileinfo->isFile() && $this->strpos_array($filePath, $ignoreDirs) === false && in_array($fileinfo->getExtension(), $allowedExtensions) === true) {
                if(!$todoLinesData || !isset($todoLinesData[$filePath]) || filemtime($filePath) > $todoLinesData[$filePath]['time']) {
                    $todoLinesData[$filePath]['time'] = time();
                    $todoLinesData[$filePath]['items'] = $this->parseFile($filePath, $fileSize);
                    $this->wire('cache')->save('TracyToDoData', $todoLinesData, WireCache::expireNever);
                }
                $items[] = $todoLinesData[$filePath]['items'];
            }
        }
        return $items;
    }


    /**
     * Reads file and returns all comments found
     * @returns array
     */
    private function parseFile($file, $fileSize) {

        $filename = $file;
        $stream = fopen($filename, 'r');
        $fileContent = fread($stream, $fileSize);
        fclose($stream);

        // script tags break token_get_all when forcing the file to be php with <?php so remove them
        $fileContent = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $fileContent);
        // remove any existing php tags and add one at the start
        // change html, latte, and "loud" comment tags into /* comment */ so they will be parsed by token_get_all
        $fileContent = '<?php ' .
        strtr($fileContent, array(
            '<?php' => '',
            '<?' => '',
            '?>' => '',
            '<!--' => '/*',
            '-->' => '*/',
            '{*' => '/*',
            '*}' => '*/',
            '/*!' => '/*'
        ));

        $todos = array();
        // @ silence warnings like "Unterminated comment" which I think occur because of converting some
        // html or js comments into PHP rather than actually a problem with the comment
        $tokens = @token_get_all($fileContent);
        foreach($tokens as $token) {
            if($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
                // exclude comments starting with # unless it's a php file
                // because # in js, css, etc are not comments
                if(pathinfo($filename, PATHINFO_EXTENSION) !== 'php' && substr($token[1], 0, 1) === '#') continue;
                // ignore long entries - likely coming from a minified js file due to escaped \//
                if(strlen($token[1]) > 500) continue;
                $containsTodo = $this->containsTodoType($token[1]);
                if($containsTodo !== false) {
                    $todos[] = $this->createTodosArray($token[2], $token[1], $containsTodo, $filename);
                }
            }
        }
        return $todos;
    }


    private function createTodosArray($line, $comment, $type, $filename) {
        return array(
            'file' => $filename,
            'line' => $line,
            'type' => $type,
            'comment' => nl2br(trim(htmlentities(str_replace(array('/*', '//', '*/', '*'), '', $comment))))
        );
    }

}