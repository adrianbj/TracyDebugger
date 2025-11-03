<?php

namespace AdminNeo;

/**
 * Adds option to export table data to XML format in structure <database name=""><table name=""><column name="">.
 *
 * Last changed in release: v5.2.0
 *
 * @link https://www.adminneo.org/plugins/#usage
 *
 * @author Jakub Vrana, https://www.vrana.cz/
 * @author Peter Knut
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class XmlDumpPlugin extends Plugin
{
	private $database = false;

	public function getDumpFormats()
	{
		return ['xml' => 'XML'];
	}

	public function sendDumpHeaders($identifier, $multiTable = false)
	{
		if ($_POST["format"] != "xml") {
			return null;
		}

		header("Content-Type: text/xml; charset=utf-8");

		return "xml";
	}

	public function dumpTable($table, $style, $viewType = 0)
	{
		if ($_POST["format"] != "xml") {
			return null;
		}

		return true;
	}

	public function dumpData($table, $style, $query)
	{
		if ($_POST["format"] != "xml") {
			return null;
		}

		if (!$this->database) {
			$this->database = true;
			echo "<database name='" . h(DB) . "'>\n";

			register_shutdown_function(function () {
				echo "</database>\n";
			});
		}

		$result = Connection::get()->query($query, 1);
		if ($result) {
			while ($row = $result->fetchAssoc()) {
				echo "\t<table name='" . h($table) . "'>\n";
				foreach ($row as $key => $val) {
					echo "\t\t<column name='" . h($key) . "'" . (isset($val) ? "" : " null='null'") . ">" . h($val) . "</column>\n";
				}
				echo "\t</table>\n";
			}
		}

		return true;
	}
}
