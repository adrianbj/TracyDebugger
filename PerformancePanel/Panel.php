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
			. '<p><i>'
			. 'Add breakpoint: Zarganwar\PerformancePanel\Register::add([name], [parent]);'
			. '</i></p>'
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
				. '<style>'
					. '#performance-panel svg
						{
							width: 1.93em !important;
						}'
				. '</style>'
				. '<span id="performance-panel" title="Performance Tracy Panel">'
					. '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
		 					viewBox="0 0 33.1 11.7" enable-background="new 0 0 33.1 11.7" xml:space="preserve">
							<linearGradient id="PerformancePanelGradient" gradientUnits="userSpaceOnUse" x1="5.8319" y1="0.5" x2="5.8319" y2="11.1638">
								<stop  offset="0" style="stop-color:#FFFFFF"/>
								<stop  offset="1" style="stop-color:#F7A69E"/>
							</linearGradient>
							<rect x="0.5" y="0.5" fill="url(#PerformancePanelGradient)" stroke="#773B37" width="10.7" height="10.7"/>
							<rect x="22" y="0.5" fill="url(#PerformancePanelGradient)" stroke="#773B37" width="10.7" height="10.7"/>
							<polygon fill="#9ADAEA" stroke="#28A0DA" points="16.5,8.1 14,8.1 14,10.5 9,5.9 14,1.2 14,3.6 16.5,3.6 "/>
							<polygon fill="#9ADAEA" stroke="#28A0DA" points="16.5,8.1 19,8.1 19,10.5 24.1,5.7 19.1,1.2 19.1,3.6 16.5,3.6 "/>'
					. '</svg>'
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
