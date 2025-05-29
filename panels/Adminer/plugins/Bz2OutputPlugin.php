<?php

namespace AdminNeo;

/**
 * Adds bzip2 compression of data export.
 *
 * @link https://www.adminneo.org/plugins/#usage
 *
 * @author Jakub Vrana, https://www.vrana.cz/
 * @author Peter Knut
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class Bz2OutputPlugin extends Plugin
{
	/** @var string */
	private $filename;

	/** @var resource */
	private $file;

	public function getDumpOutputs()
	{
		return function_exists('bzopen') ? ['bz2' => 'bzip2'] : [];
	}

	public function sendDumpHeaders($identifier, $multiTable = false)
	{
		if ($_POST["output"] == "bz2") {
			$this->filename = tempnam("", "bz2");
			$this->file = bzopen($this->filename, 'w');

			header("Content-Type: application/x-bzip");

			ob_start([$this, 'compress'], 1e6);
		}

		return null;
	}

	private function compress($string, $state)
	{
		bzwrite($this->file, $string);

		if ($state & PHP_OUTPUT_HANDLER_END) {
			bzclose($this->file);

			$result = file_get_contents($this->filename);
			unlink($this->filename);

			return (string)$result;
		}

		return "";
	}
}
