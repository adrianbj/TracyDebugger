<?php

class CaptainHookSearch {

    protected static $hookNames = array();

    public static function getHooks($root, $excludeFilenames = array()) {

        $filenamesArray = self::getPHPFilenames($root, $excludeFilenames);

        $hooks = array();

        foreach ($filenamesArray as $filename) {
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

        foreach ($iter as $path => $dir) {
            // '/.' check is for site module backups - SKIP_DOTS above is not excluding these
            if (!$dir->isDir() && strpos($path, '/.') === false && preg_match($fileNamePattern, $path) && !in_array(basename($path), $excludeFilenames) ) {
                $paths[] = $path;
            }
        }

        return $paths;
    }


    public static function getHooksInFile($file) {
        $lines = file($file);
        $source = implode('', $lines);
        $tokens = token_get_all($source);
        $nextStringIsFunc = false;
        $nextStringIsClass = false;
        $lastStringWasThis = false;
        $secondLastStringWasThis = false;
        $lastStringWasObjectOperator = false;
        $lastStringWasAddHook = false;
        $className = null;

        $files = array();
        foreach($tokens as $token) {
            switch($token[0]) {
                case T_CLASS:
                    $nextStringIsClass = true;
                    break;
                case T_FUNCTION:
                    $nextStringIsFunc = true;
                    break;

                case T_STRING:
                    if($nextStringIsClass) {
                        $nextStringIsClass = false;
                        $className = $token[1];
                    }
                    if($nextStringIsFunc) {
                        $nextStringIsFunc = false;
                        if(strpos($token[1], '___') !== false) {
                            $name = $className . '::' . str_replace('___', '', $token[1]);
                            if(!in_array($name, self::$hookNames)) {
                                self::$hookNames[] = $name;
                                $files['filename'] = $file;
                                $files['hooks'][$name]['name'] = $name;
                                $files['hooks'][$name]['lineNumber'] = $token[2];
                                $files['hooks'][$name]['line'] = self::getFunctionLine($lines[($token[2]-1)]);
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
                        $lastString = $token[1];
                    }
                    break;

                case T_OBJECT_OPERATOR:
                    if($lastStringWasThis) {
                        $lastStringWasThis = false;
                        $secondLastStringWasThis = true;
                        $lastStringWasObjectOperator = true;
                    }
                    break;

                case T_CONSTANT_ENCAPSED_STRING:
                    if($lastStringWasAddHook) {
                        $lastStringWasAddHook = false;
                        $name = str_replace(array("'", '"'), "", $token[1]);
                        if(!in_array($name, self::$hookNames)) {
                            self::$hookNames[] = $name;
                            $files['filename'] = $file;
                            $files['hooks'][$name]['name'] = $name;
                            $files['hooks'][$name]['lineNumber'] = $token[2];
                            $files['hooks'][$name]['line'] = self::strip_comments(trim($lines[($token[2]-1)]));
                        }
                    }
            }

        }
        if(isset($files['hooks']) && is_array($files['hooks'])) asort($files['hooks']);
        return $files;
    }


    private static function strip_comments($source) {
        $commentTokens = array(T_COMMENT, T_DOC_COMMENT);
        $tokens = token_get_all('<?php ' . $source);
        $newStr = '';
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], $commentTokens))
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


}


