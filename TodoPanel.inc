<?php

use Tracy\Debugger;
use Tracy\IBarPanel;

class TodoPanel implements IBarPanel {

    protected $icon;
    protected $iconColor;
    protected $entries;
    protected $todoTypes = array('todo', 'fixme', 'pending', 'xxx', 'hack', 'bug');

    public function getTab() {
        if(\TracyDebugger::additionalBar()) return;
        \Tracy\Debugger::timer('todo');

        $numEntries = 0;
        $thisPageNumEntries = 0;
        $files = $this->getTodos();
        $this->entries = $this->sectionHeader(array('File', 'Line', 'Comment'));
        foreach($files as $file => $items) {
            foreach($items as $item) {
                $numEntries++;
                if($item['file'] == wire('page')->template->filename) {
                    $thisPageFile = '* ';
                    $thisPageNumEntries++;
                }
                else {
                    $thisPageFile = '';
                }

                if(isset($item['file'])) {
                    $this->entries .= "
                        \n<tr>
                            <td>".$thisPageFile . str_replace(wire('config')->paths->templates, '', $item['file'])."</td>" .
                            "<td>".$item['line']."</td>" .
                            "<td><a href='".$item['link']."' />".$item['comment']."</td>" .
                        "</tr>";
                }
            }
        }
        $this->entries .= '</tbody>
                </table>
            </div>';

        if($thisPageNumEntries > 0) {
            $this->iconColor = '#CD1818';
        }
        elseif($numEntries > 0) {
            $this->iconColor = '#FF9933';
        }
        else {
            $this->iconColor = '#009900';
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
        <span title="Console">
            ' . $this->icon .   (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Todo' : '') . '(' . $thisPageNumEntries . '/' . $numEntries . ')
        </span>
        ';
    }


    public function getPanel() {

        \TracyDebugger::$panelGenerationTime['toDo'] = \Tracy\Debugger::timer('toDo');

        // Load all the panel sections
        $out = '
        <h1>' . $this->icon . ' ToDo</h1>
        <p>* Lines with an asterisk are in the template file for this page.</p>

        <div class="tracy-inner">';
            \TracyDebugger::$panelGenerationTime['todo'] = \Tracy\Debugger::timer('todo');
            $out .= $this->entries . \TracyDebugger::generatedTime(\TracyDebugger::$panelGenerationTime['todo']) . '
        </div>';

        return $out;
    }


    protected function strpos_array($haystack, $needles, $offset = 0) {
        if (is_array($needles)) {
            foreach ($needles as $needle) {
                $pos = $this->strpos_array($haystack, $needle);
                if ($pos !== false) {
                    return $pos;
                }
            }
            return false;
        }
        else {
            return strpos($haystack, $needles, $offset);
        }
    }


    protected function containsTodoType($str, array $arr) {
        foreach($arr as $a) {
            // match whole words only so that "debug" won't match "bug" todoType
            // but this still allows "//TODO" to match even though no space before word
            $regex = '/\b'.$a.'\b/i';
            preg_match($regex, $str, $match, PREG_OFFSET_CAPTURE);
            if(isset($match[0][1])) return true;
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
        $ignore = array('.git', '.svn', '.jpg', '.gif', '.png', '.svg', 'images', 'img', 'errors');
        $items = array();
        $templatesFolder = function_exists('\ProcessWire\wire') ? \ProcessWire\wire('config')->paths->templates : wire('config')->paths->templates;
        foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($templatesFolder, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $fileinfo){
            $filePath = $fileinfo->getPathname();
            if($this->strpos_array($filePath, $ignore) === false) {
                $items[] = $this->parseFile($filePath);
            }
        }
        return $items;
    }


    private function extractNonPhpComments($comment, $fileContent) {
        $todos = array();
        // this is for all comments that aren't in a PHP code block, eg $token[0] == T_INLINE_HTML
        // this will return all comment types (accept html comments) by adding a <?php to the $comment
        $tokens = token_get_all('<?php'.$comment);
        foreach($tokens as $token) {
            if($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
                if($this->containsTodoType($token[1], $this->todoTypes)) {
                    $todos[] = array($token[1], $token[2]);
                }
            }
        }
        // this is just for html comment tags
        if(strpos($comment, '<!--') !== false) {
            $htmlComments = array();
            $commentLines = array();
            $commentBlock = trim($comment);
            $commentLines = array_merge($commentLines, preg_split('~[\r\n]{1,2}~', $commentBlock));
            foreach ($commentLines as $key => $commentContent) {
                if($this->containsTodoType($commentContent, $this->todoTypes)) {
                    $skip = 0;
                    foreach ($commentLines as $tkey => $line) {
                        if ($tkey >= $key) {
                            break;
                        }
                        if ($line == $commentContent) {
                            $skip++;
                        }
                    }
                    $line = 0;
                    // assign line number
                    foreach (preg_split('~\n~', $fileContent) as $lineNumber => $commentLine) {
                        if ($commentContent != '' && strpos($commentLine, $commentContent) !== FALSE) {
                            if ($skip > 0) {
                                $skip--;
                                continue;
                            }
                            $line = $lineNumber + 1;
                            break;
                        }
                    }
                    $todos[] = array($commentContent, $line);
                }
            }
        }

        return $todos;
    }


    private function createTodosArray($line, $comment, $filename) {
        return array(
            'file' => $filename,
            'line' => $line,
            'comment' => nl2br(htmlentities($comment)),
            'link' => \TracyDebugger::makePathLocal(str_replace("%file", $filename, str_replace("%line", $line, \TracyDebugger::getDataValue("editor"))))
        );
    }

    /**
     * Reads file and returns all comments found
     * @returns array
     */
    private function parseFile($file) {

        $filename = $file;
        $stream = fopen($filename, 'r');
        $fileContent = fread($stream, filesize($filename));
        fclose($stream);

        $todos = array();
        $tokens = token_get_all($fileContent);
        foreach($tokens as $token) {
            if($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT || $token[0] == T_INLINE_HTML) {
                if($this->containsTodoType($token[1], $this->todoTypes)) {
                    if($token[0] == T_INLINE_HTML) {
                        // extract possible multiple comments from T_INLINE_HTML block
                        foreach($this->extractNonPhpComments($token[1], $fileContent) as $htmlComment) {
                            $todos[] = $this->createTodosArray($htmlComment[1], $htmlComment[0], $filename);
                        }
                    }
                    else {
                        $todos[] = $this->createTodosArray($token[2], $token[1], $filename);
                    }
                }
            }
        }

        return $todos;
    }
}
