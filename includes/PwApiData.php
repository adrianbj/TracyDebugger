<?php

class TracyPwApiData extends WireData {

    private $n = 0;
    private $pwVars = array();

    public function getApiData($type) {
        $cacheName = 'TracyApiData.'.$type;
        $apiData = $this->wire('cache')->get($cacheName);

        if(!$apiData || \TracyDebugger::getDataValue('apiDataVersion') === null || $this->wire('config')->version != \TracyDebugger::getDataValue('apiDataVersion')) {
            $cachedData = json_decode(ltrim($apiData, '~'), true);
            if($type == 'variables') {
                $apiData = $this->getVariables();
            }
            elseif($type == 'hooks') {
                // make sure /wire/ is first so that duplicate addHook instances are excluded from /site/ and not /wire/ files
                $apiData = array_merge(
                    $this->getApiHooks(wire('config')->paths->root.'wire/'),
                    $this->getApiHooks(wire('config')->paths->siteModules)
                );
            }
            elseif($type == 'proceduralFunctions') {
                $apiData = array('Functions' => $this->getProceduralFunctions('Functions')['pwFunctions']);
                if(file_exists($this->wire('config')->paths->core . 'FunctionsAPI.php')) $apiData += array('FunctionsAPI' => $this->getProceduralFunctions('FunctionsAPI')['pwFunctions']);
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
                        if(!isset($cachedData[$class]) || !array_key_exists($method, $cachedData[$class])) {
                            if($type != 'hooks') \TracyDebugger::$apiChanges[$type][$class][$i] = $method;
                            $i++;
                        }
                    }
                }
                $this->wire('cache')->save('TracyApiChanges', '~'.json_encode(\TracyDebugger::$apiChanges), WireCache::expireNever);

                $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");
                $configData['apiDataVersion'] = $this->wire('config')->version;
                $this->wire('modules')->saveModuleConfigData($this->wire('modules')->get("TracyDebugger"), $configData);

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

    private function getProceduralFunctions($file) {
        $proceduralFunctionsArr = array();
        $proceduralFunctionsArr += $this->getFunctionsInFile($this->wire('config')->paths->core . $file . '.php');
        return $proceduralFunctionsArr;
    }

    private function getApiHooks($root) {
        $filenamesArray = $this->getPHPFilenames($root);
        $hooks = array();

        foreach($filenamesArray as $filename) {
            if($hooksInFile = $this->getFunctionsInFile($filename, true)) {
                $hooks[] = $hooksInFile;
            }
        }

        // sort by filename with Wire Core, Wire Modules, & Site Modules sections
        uasort($hooks, function($a, $b) {
            // this could be replaced by "return $a['filename'] <=> $b['filename'];" for PHP 7+
            if($a['filename'] == $b['filename']) {
                return 0;
            }
            return($a['filename'] < $b['filename']) ? -1 : 1;
        });
        return $hooks;
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
                // this could be replaced by "return $aStripped <=> $bStripped;" for PHP 7+
                if($aStripped == $bStripped) {
                    return 0;
                }
                return($aStripped < $bStripped) ? -1 : 1;
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
                // this could be replaced by "return $aStripped <=> $bStripped;" for PHP 7+
                if($aStripped == $bStripped) {
                    return 0;
                }
                return($aStripped < $bStripped) ? -1 : 1;
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


    private function getPHPFilenames($root, $excludeFilenames = array()) {
        $fileNamePattern = "/\.(php|module)$/";

        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        $paths = array();
        foreach($iter as $path => $dir) {
            // '/.' check is for site module backups - SKIP_DOTS above is not excluding these
            if(!$dir->isDir() && strpos(str_replace(wire('config')->paths->root, '', $path), DIRECTORY_SEPARATOR.'.') === false && preg_match($fileNamePattern, $path) && !in_array(basename($path), $excludeFilenames) ) {
                $paths[] = $path;
            }
        }

        return $paths;
    }


    private function getFunctionsInFile($file, $hooks = false) {
        $newTab = \TracyDebugger::getDataValue('linksNewTab') ? 'target="_blank"' : '';

        $lines = file($file);
        $source = implode('', $lines);
        $str = preg_replace('/\s+/', ' ', $source);
        if($hooks && strpos($str, 'function ___') === false && strpos($str, 'addHook') === false) return;
        $tokens = token_get_all($source);
        $nextStringIsFunc = false;
        $nextStringIsClass = false;
        $nextStringIsExtends = false;
        $lastStringWasThis = false;
        $secondLastStringWasThis = false;
        $lastStringWasObjectOperator = false;
        $lastStringWasAddHook = false;
        $className = null;
        $extendsClassName = null;
        $docComment = null;

        $files = array();
        foreach($tokens as $token) {
            switch($token[0]) {
                case T_CLASS:
                    $nextStringIsClass = true;
                    $lastStringWasComment = false;
                    break;

                case T_EXTENDS:
                    $nextStringIsExtends = true;
                    $lastStringWasComment = false;
                    break;

                case T_DOC_COMMENT:
                    $docComment = $token[1];
                    $lastStringWasComment = true;
                    break;

                case T_FUNCTION:
                    $nextStringIsFunc = true;
                    break;

                case T_STRING:
                    if($nextStringIsClass) {
                        $nextStringIsClass = false;
                        $className = $token[1];
                    }
                    if($nextStringIsExtends) {
                        $nextStringIsExtends = false;
                        $extendsClassName = $token[1];
                    }
                    if($nextStringIsFunc) {
                        $nextStringIsFunc = false;
                        if(!$hooks || ($hooks && strpos($token[1], '___') !== false)) {
                            $methodName = str_replace('___', '', $token[1]);
                            $name = ($hooks ? $className . '::' : '') . $methodName;

                            if(!$lastStringWasComment) $docComment = '';
                            $files['filename'] = $file;
                            $files['classname'] = $className;
                            $files['extends'] = $extendsClassName;
                            $files['pwFunctions'][$name]['rawname'] = $methodName;
                            $files['pwFunctions'][$name]['name'] = $name;
                            $files['pwFunctions'][$name]['lineNumber'] = $token[2];

                            if($className) {
                                if(class_exists("\ProcessWire\\$className")) {
                                    $r = new \ReflectionMethod("\ProcessWire\\$className", '___'.$methodName);
                                }
                                elseif(class_exists($className)) {
                                    $r = new \ReflectionMethod($className, '___'.$methodName);
                                }
                                if(isset($r)) {
                                    $files['pwFunctions'][$name]['params'] = $this->phpdoc_params($r);
                                }
                            }

                            $methodStr = ltrim(self::getFunctionLine($lines[($token[2]-1)]), 'function');
                            if(
                                ($hooks && \TracyDebugger::getDataValue('captainHookToggleDocComment')) ||
                                (!$hooks && \TracyDebugger::getDataValue('apiExplorerToggleDocComment'))
                            ) {
                                $commentStr = "
                                <div id='ch-comment'>
                                    <label class='".($lastStringWasComment ? 'comment' : '')."' for='".$name."'>".$methodStr." </label>
                                    <input type='checkbox' id='".$name."'>
                                    <div class='hide'>".nl2br(htmlentities($docComment))."</div>
                                </div>";
                                $files['pwFunctions'][$name]['comment'] = $commentStr;
                            }
                            else {
                                $files['pwFunctions'][$name]['comment'] = $methodStr;
                            }

                            if(
                                ($hooks && \TracyDebugger::getDataValue('captainHookShowDescription')) ||
                                (!$hooks && (\TracyDebugger::getDataValue('apiExplorerShowDescription') || \TracyDebugger::getDataValue('codeShowDescription')))
                            ) {
                                // get the comment
                                preg_match('#^/\*\*(.*)\*/#s', $docComment, $comment);
                                if(isset($comment[0])) $comment = trim($comment[0]);
                                // get all the lines and strip the * from the first character
                                if(is_string($comment)) {
                                    preg_match_all('#^\s*\*(.*)#m', $comment, $commentLines);
                                    $files['pwFunctions'][$name]['description'] = isset($commentLines[1][0]) && is_string($commentLines[1][0]) ? nl2br(htmlentities(trim($commentLines[1][0]))) : '';
                                }
                                else {
                                    $files['pwFunctions'][$name]['description'] = '';
                                }
                            }

                        }
                    }
                    if($secondLastStringWasThis && $lastStringWasObjectOperator) {
                        $secondLastStringWasThis = false;
                        $lastStringWasObjectOperator = false;
                        if($token[1] == 'addHook') {
                            $lastStringWasAddHook = true;
                        }
                    }
                    break;

                case T_VARIABLE:
                    if(strpos($token[1], '$this') !== false) {
                        $lastStringWasThis = true;
                        $lastStringWasComment = false;
                        $lastString = $token[1];
                    }
                    break;

                case T_OBJECT_OPERATOR:
                    if($lastStringWasThis) {
                        $lastStringWasThis = false;
                        $lastStringWasComment = false;
                        $secondLastStringWasThis = true;
                        $lastStringWasObjectOperator = true;
                    }
                    break;

                case T_CONSTANT_ENCAPSED_STRING:
                    if($lastStringWasAddHook) {
                        $lastStringWasAddHook = false;
                        $lastStringWasComment = false;
                        $name = str_replace(array("'", '"'), "", $token[1]);
                        $files['filename'] = $file;
                        $files['classname'] = $className;
                        $files['extends'] = $extendsClassName;
                        $files['pwFunctions'][$name]['rawname'] = $name;
                        $files['pwFunctions'][$name]['name'] = $name;
                        $files['pwFunctions'][$name]['lineNumber'] = $token[2];
                        $files['pwFunctions'][$name]['comment'] = self::strip_comments(trim($lines[($token[2]-1)]));
                    }
                    break;
            }

        }
        // case insensitive sort functions by class and method within each file
        if(isset($files['pwFunctions']) && is_array($files['pwFunctions'])) {
            uksort($files['pwFunctions'], function($a, $b) {
                $aLower = strtolower($a);
                $bLower = strtolower($b);
                // this could be replaced by "return $aLower <=> $bLower;" for PHP 7+
                if($aLower == $bLower) {
                    return 0;
                }
                return($aLower < $bLower) ? -1 : 1;
            });
        }
        return $files;
    }


    private static function strip_comments($source) {
        $commentTokens = array(T_COMMENT, T_DOC_COMMENT);
        $tokens = token_get_all('<?php ' . $source);
        $newStr = '';
        foreach($tokens as $token) {
            if(is_array($token)) {
                if(in_array($token[0], $commentTokens))
                    continue;
                $token = $token[1];
            }
            $newStr .= $token;
        }
        return $newStr;
    }


    private static function getFunctionLine($str) {
        $functEndChar = null;
        foreach(array('{', ';', '/*', '//') as $char) {
            if(strpos($str, $char) !== false) {
                $functEndChar = $char;
                break;
            }
        }
        if($functEndChar) {
            return trim(substr($str, 0, strpos($str, $functEndChar)));
        }
        else {
            return trim($str);
        }
    }


    private function phpdoc_params(ReflectionMethod $method) {
        // Retrieve the full PhpDoc comment block
        $doc = $method->getDocComment();

        // Trim each line from space and star chars
        $lines = array_map(function($line) {
            return trim(preg_replace('/\t/', '', $line), " *");
        }, explode("\n", $doc));

        // Retain lines that start with an @
        $lines = array_filter($lines, function($line) {
            return strpos($line, "@") === 0;
        });

        $args = [];

        // Push each value in the corresponding @param array
        foreach($lines as $line) {
            list($param, $value) = explode(' ', "$line ", 2);
            if($param == '@param') {
                list($type, $var) = explode(' ', "$value ");
                $args[$var] = $type;
            }
        }

        return $args;
    }


    public static function convertNamesToUrls($str) {
        return trim(strtolower(preg_replace('/([A-Z])/', '-$1', $str)), '-');
    }

}

