<?php

namespace AdminNeo;

/**
 * Adds option to export table data to JSON format.
 *
 * @link https://www.adminneo.org/plugins/#usage
 *
 * @author Jakub Vrana, https://www.vrana.cz/
 * @author Peter Knut
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class JsonDumpPlugin extends Plugin
{
	private $database = false;

	public function getDumpFormats()
	{
		return ['json' => 'JSON'];
	}

	public function sendDumpHeaders($identifier, $multiTable = false)
	{
		if ($_POST["format"] != "json") {
			return null;
		}

		header("Content-Type: application/json; charset=utf-8");

		return "json";
	}

	public function dumpTable($table, $style, $viewType = 0)
	{
		if ($_POST["format"] != "json") {
			return null;
		}

		return true;
	}

	public function dumpData($table, $style, $query)
	{
		if ($_POST["format"] != "json") {
			return null;
		}

		if ($this->database) {
			echo ",\n";
		} else {
			$this->database = true;
			echo "{\n";

			register_shutdown_function(function () {
				echo "}\n";
			});
		}

		$result = Connection::get()->query($query, 1);
		if ($result) {
			echo '"' . addcslashes($table, "\r\n\"\\") . "\": [\n";

			$first = true;
			while ($row = $result->fetchAssoc()) {
				echo($first ? "" : ", ");

				foreach ($row as $key => $val) {
					json_row($key, $val);
				}
				json_row("");

				$first = false;
			}

			echo "]";
		}

		return true;
	}
}
