<?php

class TracyPwApiData extends WireData {

    private $n = 0;

    public function __construct() {
        if($this->wire('modules')->isInstalled('ProcessWireAPI')) {
            $apiModuleInstalled = true;
            $apiModuleId = $this->wire('modules')->getModuleID("ProcessWireAPI");
            $this->apiBaseUrl = $this->wire('pages')->get("process=$apiModuleId")->url.'methods/';
        }
        else {
            $apiModuleInstalled = false;
            $this->apiBaseUrl = 'https://processwire.com/api/ref/';
        }
        $this->newTab = \TracyDebugger::getDataValue('linksNewTab') ? 'target="_blank"' : '';
    }

    public function getVariables() {
        $apiVars = array();
        $apiVariables = array();
        $pwVars = $this->wire('config')->version >= 2.8 ? $this->wire('all') : $this->wire()->fuel;
        if(is_object($pwVars)) {
            foreach($pwVars as $key => $value) {
                if(!is_object($value)) continue;
                $apiVars[$key] = $value;
            }
            ksort($apiVars);
            foreach($apiVars as $key => $value) {
                $r = new \ReflectionObject($this->wire()->$key);
                $apiVariables += $this->buildItemsArray($r, $key, 'variables');
            }
        }
        return $apiVariables;
    }


    public function buildItemsArray($r, $key, $type) {
        $items = array();

        // methods from reflection
        $methods = $r->getMethods();
        $methodsList = array();
        foreach($methods as $m) {
            $name = $m->name;
            if(!$r->getMethod($name)->isPublic()) continue;

            // if method is inherited from Wire/WireData/WireArray, then don't display
            $declaringClassName = str_replace('ProcessWire\\', '', $m->getDeclaringClass()->getName());
            if(!\TracyDebugger::getDataValue('apiExplorerIncludeInheritedMethods')) {
                if(strcasecmp($key, 'Wire') !== 0 && strcasecmp($declaringClassName, 'Wire') == 0) continue;
                if(strcasecmp($key, 'WireData') !== 0 && strcasecmp($declaringClassName, 'WireData') == 0) continue;
                if(strcasecmp($key, 'WireArray') !== 0 && strcasecmp($declaringClassName, 'WireArray') == 0) continue;
            }

            $docComment = $r->getMethod($name)->getDocComment();
            $filename = $r->getMethod($name)->getFilename();
            $className = $key;
            $methodName = str_replace(array('___', '__'), '', $name);

            if(strpos($filename, 'wire') !== false) {
                if($this->apiModuleInstalled || strpos($filename, 'modules') === false) {
                    $methodsList[$name.'()']['name'] = "<a ".$this->newTab." href='".$this->apiBaseUrl.$this->convertNamesToUrls($className)."/".$this->convertNamesToUrls($methodName)."/'>" . $name . "</a>";
                }
                else {
                    $methodsList[$name.'()']['name'] = $name;
                }
            }
            else {
                $methodsList[$name.'()']['name'] = $name;
            }

            $methodsList[$name.'()']['lineNumber'] = $r->getMethod($name)->getStartLine();
            $methodsList[$name.'()']['filename'] = $filename;
            $i=0;
            foreach($r->getMethod($name)->getParameters() as $param) {
                $methodsList[$name.'()']['params'][$i] = ($param->isOptional() ? '<em>' : '') . '$' . $param->getName() . ($param->isOptional() ? '</em>' : '');
                $i++;
            }

            $methodStr = "$" . ($type == 'coreModule' || $type == 'siteModule' ? 'modules->get(\''.$key.'\')' : lcfirst($key)) . '->' . str_replace('___', '', $name) . '(' . (isset($methodsList[$name.'()']['params']) ? implode(', ', $methodsList[$name.'()']['params']) : '') . ')';

            if(\TracyDebugger::getDataValue('apiExplorerToggleDocComment')) {
                $commentStr = "
                <div id='ch-comment'>
                    <label class='".($docComment != '' ? 'comment' : '') . "' for='".$key."_".$name."'>".$methodStr."</label>
                    <input type='checkbox' id='".$key."_".$name."'>
                    <div class='hide'>";
                $methodsList[$name.'()']['comment'] = $commentStr . "\n\n" . nl2br(htmlentities($docComment)) .
                        "</div>
                    </div>";
            }
            else {
                $methodsList[$name.'()']['comment'] = $methodStr;
            }

            if(\TracyDebugger::getDataValue('apiExplorerShowDescription') || \TracyDebugger::getDataValue('captainHookShowDescription') || \TracyDebugger::getDataValue('codeShowDescription')) {
                // get the comment
                preg_match('#^/\*\*(.*)\*/#s', $docComment, $comment);
                if(isset($comment[0])) $comment = trim($comment[0]);
                // get all the lines and strip the * from the first character
                if(is_string($comment)) {
                    preg_match_all('#^\s*\*(.*)#m', $comment, $commentLines);
                    if(isset($commentLines[1][0])) $desc = preg_replace('/#([^#\s]+)/', '', $commentLines[1][0]);
                    $methodsList[$name.'()']['description'] = isset($desc) && is_string($desc) ? nl2br(htmlentities(trim($desc))) : '';
                }
                else {
                    $methodsList[$name.'()']['description'] = '';
                }
            }

        }

        // get runtime methods from doc comment
        $classDocComment = $r->getDocComment();
        // get the comment
        preg_match('#^/\*\*(.*)\*/#s', $classDocComment, $comment);
        if(isset($comment[0])) $comment = trim($comment[0]);
        // get all the lines and strip the * from the first character
        if(is_string($comment)) {
            preg_match_all('#^\s*\*(.*)#m', $comment, $commentLines);
            foreach($commentLines[1] as $c) {
                if(strpos($c, '@method') !== false) {
                    preg_match('/(.*)(?:\s)([A-Za-z_]+)(\(.*\)\s*)(?:\s+)(.*)$/U', $c, $varName);
                    if(isset($varName[2]) && !array_key_exists($varName[2].'()', $methodsList) && !array_key_exists('_'.$varName[2].'()', $methodsList) && !array_key_exists('__'.$varName[2].'()', $methodsList) && !array_key_exists('___'.$varName[2].'()', $methodsList)) {
                        if($this->apiModuleInstalled || strpos($filename, 'modules') === false) {
                            $methodsList[$varName[2].'()']['name'] = "<a ".$this->newTab." href='".$this->apiBaseUrl.$this->convertNamesToUrls($className)."/".$this->convertNamesToUrls($methodName)."/'>" . $varName[2] . "</a>";
                        }
                        else {
                            $methodsList[$varName[2].'()']['name'] = $varName[2];
                        }
                        $methodsList[$varName[2].'()']['params'] = explode(', ', $varName[3]);
                        $methodsList[$varName[2].'()']['description'] = preg_replace('/#([^#\s]+)/', '', $varName[4]);
                    }
                }
            }
        }

        if(isset($methodsList)) {

            uksort($methodsList, function($a, $b) {
                $aStripped = str_replace(array('___','__', '_'), '', $a);
                $bStripped = str_replace(array('___','__', '_'), '', $b);
                return $aStripped > $bStripped;
            });

            foreach($methodsList as $name => $info) {

                if($type == 'variables') {
                    \TracyDebugger::$autocompleteArr[$this->n]['name'] = "$$key->" . str_replace('___', '', $name) . (method_exists($this->wire()->$key, $name) ? '()' : '');
                    \TracyDebugger::$autocompleteArr[$this->n]['meta'] = 'PW method';
                }

                $items[$key][$name]['name'] = $info['name'];
                $items[$key][$name]['lineNumber'] = isset($info['lineNumber']) ? $info['lineNumber'] : '';
                $items[$key][$name]['filename'] = isset($info['filename']) ? $info['filename'] : '';
                if(isset($info['comment'])) {
                    $methodStr = $info['comment'];
                }
                else {
                    $methodStr = "$" . lcfirst($key) . '->' . str_replace(array('___', '()'), '', $name) . (isset($info['params']) ? implode(', ', $info['params']) : '');
                }
                $items[$key][$name]['comment'] = $methodStr;

                if(\TracyDebugger::getDataValue('apiExplorerShowDescription') || \TracyDebugger::getDataValue('captainHookShowDescription') || \TracyDebugger::getDataValue('codeShowDescription')) {
                    if(substr($info['description'], 0, 1) === '#') {
                        if(\TracyDebugger::getDataValue('codeShowDescription') && $type == 'variables') {
                            \TracyDebugger::$autocompleteArr[$this->n]['docHTML'] = '';
                        }
                        $items[$key][$name]['description'] = '';
                    }
                    else {
                        if(\TracyDebugger::getDataValue('codeShowDescription') && $type == 'variables') {
                            \TracyDebugger::$autocompleteArr[$this->n]['docHTML'] = isset($desc) && is_string($desc) ? nl2br(trim($info['description'])) . "\n" . (isset($info['params']) ? '('.implode(', ', $info['params']).')' : '') : '';
                        }
                        $items[$key][$name]['description'] = $info['description'];
                    }
                }
                if($type == 'variables') $this->n++;
            }
        }

        // get runtime properties from doc comment
        $classDocComment = $r->getDocComment();
        // get the comment
        preg_match('#^/\*\*(.*)\*/#s', $classDocComment, $comment);
        if(isset($comment[0])) $comment = trim($comment[0]);
        // get all the lines and strip the * from the first character
        if(is_string($comment)) {
            preg_match_all('#^\s*\*(.*)#m', $comment, $commentLines);
            $propertiesList = array();
            foreach($commentLines[1] as $c) {
                if(strpos($c, '@property') !== false) {
                    preg_match('/(?:@property)(?:\s)(?:\S*)(?:\s)(\$*[A-Za-z_]+)(?:\s)([#A-Za-z`\$->\'’()\s{}]+)/', $c, $varName);
                    if(isset($varName[1])) {
                        $propertiesList[str_replace('$', '', $varName[1])]['name'] = str_replace('$', '', $varName[1]);
                        $propertiesList[str_replace('$', '', $varName[1])]['description'] = $varName[2];
                    }
                }
            }
        }

        if(isset($propertiesList)) {

            uksort($propertiesList, function($a, $b) {
                $aStripped = str_replace(array('___','__', '_'), '', $a);
                $bStripped = str_replace(array('___','__', '_'), '', $b);
                return $aStripped > $bStripped;
            });

            foreach($propertiesList as $name => $info) {

                if($type == 'variables') {
                    \TracyDebugger::$autocompleteArr[$this->n]['name'] = "$$key->" . str_replace('___', '', $name);
                    \TracyDebugger::$autocompleteArr[$this->n]['meta'] = 'PW property';
                }

                $items[$key][$name]['name'] = $name;
                $items[$key][$name]['lineNumber'] = '';
                $items[$key][$name]['filename'] = '';
                $items[$key][$name]['comment'] = "$" . lcfirst($key) . '->' . str_replace('___', '', $name);
                if(\TracyDebugger::getDataValue('apiExplorerShowDescription') || \TracyDebugger::getDataValue('captainHookShowDescription') || \TracyDebugger::getDataValue('codeShowDescription')) {
                    $desc = preg_replace('/#([^#\s]+)/', '', $info['description']);
                    if(\TracyDebugger::getDataValue('codeShowDescription') && $type == 'variables') {
                        \TracyDebugger::$autocompleteArr[$this->n]['docHTML'] = isset($desc) && is_string($desc) ? nl2br(htmlentities(trim($desc))) : '';
                    }
                    $items[$key][$name]['description'] = $desc;
                }
                if($type == 'variables') $this->n++;
            }
        }

        return $items;

    }


    public static function convertNamesToUrls($str) {
        return trim(strtolower(preg_replace('/([A-Z])/', '-$1', $str)), '-');
    }

}

