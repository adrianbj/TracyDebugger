<?php

class TracyPwApiData extends WireData {

    private $n = 0;
    private $pwVars = array();

    public function getApiData($type) {
        $cacheName = 'TracyApiData.'.$type;
        $apiData = $this->wire('cache')->get($cacheName);

        if(!$apiData || \TracyDebugger::getDataValue('apiDataVersion') === null || $this->wire('config')->version != \TracyDebugger::getDataValue('apiDataVersion')) {
            $cachedData = json_decode(ltrim($apiData, '~'), true);
            $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
            $configData['apiDataVersion'] = $this->wire('config')->version;
            $this->wire('modules')->saveModuleConfigData($this->wire('modules')->get("TracyDebugger"), $configData);
            if($type == 'variables') {
                $apiData = $this->getVariables();
            }
            else {
                $typeDir = $type == 'coreModules' ? 'modules' : $type;
                $apiData = $this->getClasses($type, $this->wire('config')->paths->$typeDir);
            }

            // if PW core version has changed, populate the "TracyApiChanges" data cache
            if($cachedData && \TracyDebugger::getDataValue('apiDataVersion') !== null && $this->wire('config')->version != \TracyDebugger::getDataValue('apiDataVersion')) {
                \TracyDebugger::$apiChanges['cachedVersion'] = \TracyDebugger::getDataValue('apiDataVersion');
                foreach($apiData as $class => $methods) {
                    $i=0;
                    foreach($methods as $method => $params) {
                        if(!isset($cachedData[$class]) ||!array_key_exists($method, $cachedData[$class])) {
                            \TracyDebugger::$apiChanges[$type][$class][$i] = $method;
                            $i++;
                        }
                    }
                }
                $this->wire('cache')->save('TracyApiChanges', '~'.json_encode(\TracyDebugger::$apiChanges), WireCache::expireNever);
            }

            // tilde hack for this: https://github.com/processwire/processwire-issues/issues/775
            $apiData = '~'.json_encode($apiData);
            $this->wire('cache')->save($cacheName, $apiData, WireCache::expireNever);
        }
        return json_decode(ltrim($apiData, '~'), true);
    }


    private function getVariables() {
        $apiVars = array();
        $apiVariables = array();
        $this->pwVars = $this->wire('config')->version >= 2.8 ? $this->wire('all') : $this->wire()->fuel;
        if(is_object($this->pwVars)) {
            foreach($this->pwVars as $key => $value) {
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


    private function getClasses($type, $folder) {
        $classes = array();
        foreach(preg_grep('/^([^.])/', scandir($folder)) as $file) {
            array_push($classes, pathinfo($file, PATHINFO_FILENAME));
        }
        $classesArr = array();
        foreach($classes as $class) {
            if(!in_array($class, \TracyDebugger::$allApiClassesArr)) {
                if($type == 'coreModules' && strpos($folder, 'modules') === false) continue;
                // for classes with an API object variable, provide an empty methods/properties array
                if(array_key_exists(lcfirst($class), \TracyDebugger::$allApiData['variables'])) {
                    $classesArr += array($class => array());
                }
                else {
                    if(!preg_match("/^[A-Z]/", $class)) continue;

                    if(class_exists("\ProcessWire\\$class")) {
                        $r = new \ReflectionClass("\ProcessWire\\$class");
                    }
                    elseif(class_exists($class)) {
                        $r = new \ReflectionClass($class);
                    }
                    else {
                        continue;
                    }
                    $classesArr += $this->buildItemsArray($r, $class, $type);
                }
                array_push(\TracyDebugger::$allApiClassesArr, $class);
            }
        }
        ksort($classesArr);
        return $classesArr;
    }


    private function buildItemsArray($r, $class, $type) {

        $items = array();

        // methods from reflection
        $methods = $r->getMethods();
        $methodsList = array();
        foreach($methods as $m) {
            $name = $m->name;
            if(!$r->getMethod($name)->isPublic()) continue;

            // if method is inherited from Wire/WireData/WireArray, then don't display
            $declaringClassName = str_replace('ProcessWire\\', '', $m->getDeclaringClass()->getName());
            if(strcasecmp($class, 'Wire') !== 0 && strcasecmp($declaringClassName, 'Wire') == 0) continue;
            if(strcasecmp($class, 'WireData') !== 0 && strcasecmp($declaringClassName, 'WireData') == 0) continue;
            if(strcasecmp($class, 'WireArray') !== 0 && strcasecmp($declaringClassName, 'WireArray') == 0) continue;

            $docComment = $r->getMethod($name)->getDocComment();
            $filename = $r->getMethod($name)->getFilename();
            $methodsList[$name.'()']['name'] = $name;
            $methodsList[$name.'()']['lineNumber'] = $r->getMethod($name)->getStartLine();
            $methodsList[$name.'()']['filename'] = $filename;
            $i=0;
            foreach($r->getMethod($name)->getParameters() as $param) {
                $methodsList[$name.'()']['params'][$i] = ($param->isOptional() ? '<em>' : '') . '$' . $param->getName() . ($param->isOptional() ? '</em>' : '');
                $i++;
            }
            $methodStr = "$" . ($type == 'coreModules' || $type == 'siteModules' ? 'modules->get(\''.$class.'\')' : lcfirst($class)) . '->' . str_replace('___', '', $name) . '(' . (isset($methodsList[$name.'()']['params']) ? implode(', ', $methodsList[$name.'()']['params']) : '') . ')';

            if(\TracyDebugger::getDataValue('apiExplorerToggleDocComment')) {
                $commentStr = "
                <div id='ch-comment'>
                    <label class='".($docComment != '' ? 'comment' : '') . "' for='".$class."_".$name."'>".$methodStr."</label>
                    <input type='checkbox' id='".$class."_".$name."'>
                    <div class='hide'>";
                $methodsList[$name.'()']['comment'] = $commentStr . "\n\n" . nl2br(htmlentities($docComment)) .
                        "</div>
                    </div>";
            }
            else {
                $methodsList[$name.'()']['comment'] = $methodStr;
            }

            if(\TracyDebugger::getDataValue('apiExplorerShowDescription') || \TracyDebugger::getDataValue('codeShowDescription')) {
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
                        $methodsList[$varName[2].'()']['name'] = $varName[2];
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

                $items[$class][$name]['name'] = $info['name'];
                $items[$class][$name]['params'] = isset($info['params']) ? $info['params'] : array();
                $items[$class][$name]['lineNumber'] = isset($info['lineNumber']) ? $info['lineNumber'] : '';
                $items[$class][$name]['filename'] = isset($info['filename']) ? $info['filename'] : '';
                if(isset($info['comment'])) {
                    $methodStr = $info['comment'];
                }
                else {
                    $methodStr = "$" . lcfirst($class) . '->' . str_replace(array('___', '()'), '', $name) . (isset($info['params']) ? implode(', ', $info['params']) : '');
                }
                $items[$class][$name]['comment'] = $methodStr;

                if(\TracyDebugger::getDataValue('apiExplorerShowDescription') || \TracyDebugger::getDataValue('codeShowDescription')) {
                    if(substr($info['description'], 0, 1) === '#') {
                        $items[$class][$name]['description'] = '';
                    }
                    else {
                        $items[$class][$name]['description'] = $info['description'];
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
                    preg_match('/@property(-read|-write|\s)\s*([^\s]+)\s+(\$[_a-zA-Z0-9]+)(.*)$/m', $c, $varName);
                    if(isset($varName[3])) {
                        $propertiesList[str_replace('$', '', $varName[3])]['name'] = str_replace('$', '', $varName[3]);
                        $propertiesList[str_replace('$', '', $varName[3])]['description'] = $varName[4];
                    }
                }
            }
        }

        if(isset($propertiesList)) {

            uksort($propertiesList, function($a, $b) {
                $aStripped = str_replace(array('___','__', '_'), '', strtolower($a));
                $bStripped = str_replace(array('___','__', '_'), '', strtolower($b));
                return $aStripped > $bStripped;
            });

            foreach($propertiesList as $name => $info) {

                $items[$class][$name]['name'] = $name;
                $items[$class][$name]['lineNumber'] = '';
                $items[$class][$name]['filename'] = '';
                $items[$class][$name]['comment'] = "$" . lcfirst($class) . '->' . str_replace('___', '', $name);
                if(\TracyDebugger::getDataValue('apiExplorerShowDescription') || \TracyDebugger::getDataValue('codeShowDescription')) {
                    $desc = preg_replace('/#([^#\s]+)/', '', $info['description']);
                    $items[$class][$name]['description'] = $desc;
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

