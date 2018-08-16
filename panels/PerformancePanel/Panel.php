<?php

namespace Zarganwar\PerformancePanel;

use Tracy\IBarPanel;

/**
 * Description of Panel
 *
 * @author Martin Jirasek
 */
class Panel implements IBarPanel
{

	/**
	 *
	 * @return string
	 */
	protected function getHeaderString()
	{
		return "<tr>"
			. "<th><b>Breakpoint</b></th>"
			. "<th><b>Previous breakpoint</b></th>"
			. "<th>Current memory [MB]</th>"
			. "<th>Peak memory from previous [MB]</th>"
			. "<th>Memory from previous [MB]</th>"
			. "<th>Time from previous [ms]</th>"
			. "</tr>";
	}

	/**
	 *
	 * @return string
	 */
	protected function getBaseRowString()
	{
		return "<tr title='%s'>"
			. "<td><b>%s</b></td>"
			. "<td><b>%s</b></td>"
			. "<td>%s</td><td>%s</td>"
			. "<td>%s</td><td>%s</td>"
			. "</tr>";
	}

	/**
	 *
	 * @return string
	 * @todo Draw time vs. memory graph
	 */
	public function getPanel()
	{
		return ''
			. '<h1>Performance between breakpoints</h1>'
			. '<div class="tracy-inner">'
			. '<p>'
			. '<table>' . $this->getRowsString() . '<tr><th><b>Total breakpoints</b></th><th colspan="4">' . $this->countBreakpoints() . '</th></tr></table>'
			. '</p>'
			. '</div>';
	}

	/**
	 *
	 * @return string
	 */
	public function getTab()
	{
		return ''
				. '<span id="performance-panel" title="Performance Tracy Panel">'
					. '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
							 width="16px" height="16px" viewBox="40.6 40.6 16 16" enable-background="new 40.6 40.6 16 16" xml:space="preserve">
							<g>
								<path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M48.6,40.6c-4.4,0-8,3.6-8,8s3.6,8,8,8s8-3.6,8-8S53,40.6,48.6,40.6z M48.6,54.9c-3.5,0-6.3-2.8-6.3-6.3
									s2.8-6.3,6.3-6.3s6.3,2.8,6.3,6.3S52.1,54.9,48.6,54.9z"/>
								<path fill="'.\TracyDebugger::COLOR_NORMAL.'" d="M52.7,48.3h-3.6V44c0-0.4-0.3-0.7-0.7-0.7c-0.4,0-0.7,0.3-0.7,0.7v5c0,0.4,0.3,0.7,0.7,0.7h4.3
									c0.4,0,0.7-0.3,0.7-0.7C53.4,48.6,53.1,48.3,52.7,48.3z"/>
							</g>
						</svg>'
				. '</span>'
		;
	}

	/**
	 *
	 * @return int
	 */
	protected function countBreakpoints()
	{
		return count(Register::getNames());
	}

	/**
	 *
	 * @return string
	 */
	protected function getRowsString()
	{
		$return = $this->getHeaderString();
		$namePrevious = null;
		$data = Register::getData();
		foreach ($data as $name => $current) {
			$memory = $time = $peakMemory = null;

			$possibleParent = Register::getParent($name);
			if ($possibleParent) {
				$namePrevious = $possibleParent;
			}

			if ($namePrevious !== null) {
				$previous = $data[$namePrevious];
				$memory = $current[Register::MEMORY] - $previous[Register::MEMORY];
				$peakMemory = $current[Register::MEMORY_PEAK] - $previous[Register::MEMORY_PEAK];
				$time = $current[Register::TIME] - $previous[Register::TIME];
			}
			$row = $this->getBaseRowString();
			$backtrace = $current[Register::BACKTRACE][0];
			$return .= sprintf(
				$row,
				$backtrace['file'] . ':' . $backtrace['line'],
				$name,
				$namePrevious,
				$this->memoryToNumber($current[Register::MEMORY]),
				$this->memoryToNumber($peakMemory),
				$this->memoryToNumber($memory),
				$this->timeToNumber($time)
			);
			$namePrevious = $name;
		}
		return $return;
	}

	/**
	 *
	 * @param int $value
	 * @return string
	 */
	protected function memoryToNumber($value)
	{
		return number_format($value / 1000000, 3, '.', ' ');
	}

	/**
	 *
	 * @param int $value
	 * @return string
	 */
	protected function timeToNumber($value)
	{
		return number_format($value * 1000, 0, '.', ' ');
	}

}
