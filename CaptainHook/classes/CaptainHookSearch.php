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

		$paths = array($root);
		
		foreach ($iter as $path => $dir) {
			if (!$dir->isDir() && preg_match($fileNamePattern, $path) && !in_array(basename($path), $excludeFilenames) ) {
				$paths[] = $path;
			}
		}
		
		return $paths;
	}
	
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
	
	
	
}