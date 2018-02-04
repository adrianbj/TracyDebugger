<?php

ini_set('memory_limit', -1);

class CaptainHookSearch {

    //protected static $hookNames = array();

    public static function getHooks($root, $excludeFilenames = array()) {

        $filenamesArray = self::getPHPFilenames($root, $excludeFilenames);

        $hooks = array();

        foreach($filenamesArray as $filename) {
            if($hooksInFile = self::getHooksInFile($filename)) {
                $hooks[] = $hooksInFile;
            }
        }

        return $hooks;

    }

    public static function getPHPFilenames($root, $excludeFilenames = array()) {

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


    public static function getHooksInFile($file) {
        $lines = file($file);
        $source = implode('', $lines);
        $str = preg_replace('/\s+/', ' ', $source);
        if(strpos($str, 'function ___') === false && strpos($str, 'addHook') === false) return;
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
        $comment = null;

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
                    $comment = $token[1];
                    $lastStringWasComment = true;
                    break;

                case T_FUNCTION:
                    $nextStringIsFunc = true;
                    break;

                case T_STRING:
                    if($nextStringIsClass) {
                        $nextStringIsClass = false;
                        $className = $token[1];
                        //if(strpos($file, '/modules/') !== false && !wire('modules')->isInstalled($className)) return;
                    }
                    if($nextStringIsExtends) {
                        $nextStringIsExtends = false;
                        $extendsClassName = $token[1];
                    }
                    if($nextStringIsFunc) {
                        $nextStringIsFunc = false;
                        if(strpos($token[1], '___') !== false) {
                            $methodName = str_replace('___', '', $token[1]);
                            $name = $className . '::' . $methodName;
                            //if(!in_array($name, self::$hookNames)) {
                                if(!$lastStringWasComment) $comment = '';
                                //self::$hookNames[] = $name;
                                $files['filename'] = $file;
                                $files['classname'] = $className;
                                $files['extends'] = $extendsClassName;
                                $files['hooks'][$name]['rawname'] = $name;
                                if(strpos($comment, '#pw-internal') === false && strpos($file, 'wire') !== false) {
                                    if(wire('modules')->isInstalled('ProcessWireAPI')) {
                                        $ApiModuleId = wire('modules')->getModuleID("ProcessWireAPI");
                                        $files['hooks'][$name]['name'] = "<a href='".wire('pages')->get("process=$ApiModuleId")->url.'methods/'.self::convertNamesToUrls($className)."/".self::convertNamesToUrls($methodName)."/'>" . $name . "</a>";
                                    }
                                    elseif(strpos($file, 'modules') === false) {
                                        $files['hooks'][$name]['name'] = "<a href='https://processwire.com/api/ref/".self::convertNamesToUrls($className)."/".self::convertNamesToUrls($methodName)."/'>" . $name . "</a>";
                                    }
                                    else {
                                        $files['hooks'][$name]['name'] = $name;
                                    }
                                }
                                else {
                                    $files['hooks'][$name]['name'] = $name;
                                }

                                $files['hooks'][$name]['lineNumber'] = $token[2];
                                $files['hooks'][$name]['line'] = "
                                    <div id='ch-comment'>
                                        <label class='".($lastStringWasComment ? 'comment' : '')."' for='".$name."'>".self::getFunctionLine($lines[($token[2]-1)])." </label>
                                        <input type='checkbox' id='".$name."'>
                                        <div class='hide'>".nl2br(htmlentities($comment))."</div>
                                    </div>";
                            //}
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
                        //if(!in_array($name, self::$hookNames)) {
                            //self::$hookNames[] = $name;
                            $files['filename'] = $file;
                            $files['classname'] = $className;
                            $files['extends'] = $extendsClassName;
                            $files['hooks'][$name]['rawname'] = $name;
                            $files['hooks'][$name]['name'] = $name;
                            $files['hooks'][$name]['lineNumber'] = $token[2];
                            $files['hooks'][$name]['line'] = self::strip_comments(trim($lines[($token[2]-1)]));
                        //}
                    }
                    break;
            }

        }
        // sort hooks by class and method within each file
        if(isset($files['hooks']) && is_array($files['hooks'])) {
            asort($files['hooks']);
            // simple asort seems to work fine, using the first key (rawname) from the array, but if problems, then switch to below
            /*usort($files['hooks'], function($a, $b) {
                $a = str_replace('::', '', $a['rawname']);
                $b = str_replace('::', '', $b['rawname']);
                return strnatcmp($a, $b);
            });*/
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

    private static function convertNamesToUrls($str) {
        return trim(strtolower(preg_replace('/([A-Z])/', '-$1', $str)), '-');
    }

}


