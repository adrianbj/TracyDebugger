<?php

class CaptainHookSearch {

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

        $i=0;
        $files = array();
        foreach($tokens as $token) {
            switch($token[0]) {
                case T_FUNCTION:
                    $nextStringIsFunc = true;
                    break;

                case T_STRING:
                    if($nextStringIsFunc) {
                        $nextStringIsFunc = false;
                        if(strpos($token[1], '___') !== false) {
    	                    $files['filename'] = $file;
    	                    $files['hooks'][$i]['name'] = str_replace('___', '', $token[1]);
    	                    $files['hooks'][$i]['lineNumber'] = $token[2];
	                        $files['hooks'][$i]['line'] = trim(str_replace('{', '', $lines[($token[2]-1)]));
    	                }
                    }
                    break;
            }
            $i++;
        }

        return $files;
    }

/*
	public static function getHooksInFile($filename) {

		$fileContents = file_get_contents($filename);
		$lines =  preg_split("/(\r\n|\n|\r)/", $fileContents);
		$hookPattern = "/.+function\s*___[a-zA-Z]*\s*\(.*\)/";
		$matches = array();

		$currentLine = 1;

		foreach ($lines as $line) {
			if(preg_match($hookPattern, $line)) {
				// echo $line . "<br>";
				$matches[] = array(
					"lineNumber" => $currentLine,
					"line" => trim($line)
				);
			}
			$currentLine++;
		}

		if(count($matches) > 0) {
			return array(
				"filename" => $filename,
				'hooks' => $matches
			);
		} else {
			return null;
		}

	}
*/

}