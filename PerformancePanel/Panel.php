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
		return "<tr>"
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
			. '<p><i>'
			. 'Add breakpoint: Zarganwar\PerformancePanel\Register::add([name], [parent]);'
			. '</i></p>'
			. '<p>'
			. '<table>' . $this->getRowsString() . '<tr><th><b>Total breakpoints</b></th><th colspan="4">' . $this->countBreakpoints() . '</th></tr></table>'
			. '</p>';
	}

	/**
	 * 
	 * @return string
	 */
	public function getTab()
	{
		return ''
			. '<img '
			. 'style="top: 0px;"'
			. 'title="Performance Tracy panel"'
			. 'src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABkAAAAUCAMAAABPqWaPAAAAOVBMVEUAouh3OzZ8PzmDQz6LSUOSTUeZUkyZ2eqgV1CmWlOqXVbw7ub/npX/pJr/p53/p5//t7H/zcn/7uxI7FS1AAAAYUlEQVQoz8WQyxKAIAhFb77QrKz+/2NLQHPT2rNg5lx0FJD/wPzO2uBDyN2RbiGBO4PHSwChQlCPCCcDepEiQYAvFc5IKycebmOGOxI42EPo76hbmF0w+rfPl4bO033+rh+bKRPEr5Sr9wAAAABJRU5ErkJggg=="/>'
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
			$return .= sprintf(
				$row,
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
