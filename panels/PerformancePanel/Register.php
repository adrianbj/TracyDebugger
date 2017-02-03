<?php

namespace Zarganwar\PerformancePanel;

/**
 * Description of Register
 *
 * @author Martin Jirasek
 */
class Register
{
	const MEMORY_PEAK = 'memory_peak';
	const MEMORY = 'memory';
	const TIME = 'time';
	const NAME = 'name';
	const PREVIOUS = 'previous';
	const BACKTRACE = 'backtrace';

	const DEFAULT_BP_NAME = 'BP_';

	public static $data = array();

	/**
	 * Add breakpoint
	 * @param string|null $name
	 * @param string|null $enforceParent
	 * @deprecated
	 */
	public static function addBreakpoint($name = null, $enforceParent = null)
	{
		self::add($name, $enforceParent);
	}

	/**
	 * Add breakpoint
	 * @param string|null $name
	 * @param string|null $enforceParent
	 */
	public static function add($name = null, $enforceParent = null)
	{
		$safeName = self::getName($name);
		$previous = $enforceParent !== null && self::isNameUsed($enforceParent) ? $enforceParent : null;
		self::$data[$safeName] = array(
			self::NAME => $safeName,
			self::MEMORY => memory_get_usage(),
			self::MEMORY_PEAK => memory_get_peak_usage(),
			self::TIME => microtime(true),
			self::PREVIOUS => $previous,
			self::BACKTRACE => debug_backtrace(),
		);
	}

	/**
	 * 
	 * @return int
	 */
	private static function countBreakpoints()
	{
		return count(self::$data);
	}


	/**
	 *
	 * @param string|null $name
	 * @return string
	 */
	public static function getName($name = null)
	{
		$countBreakpoints = self::countBreakpoints() + 1;
		if ($name === null) {
			$name = self::DEFAULT_BP_NAME . $countBreakpoints;
		} else {
			if (self::isNameUsed($name)) {
				$name = self::getName($name . '_' . $countBreakpoints);
			}
		}
		return $name;
	}

	/**
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function hasParent($name)
	{
		return self::$data[$name][self::PREVIOUS] !== null;
	}

	/**
	 *
	 * @param string $name
	 * @return string
	 */
	public static function getParent($name)
	{
		$parent = null;
		if (self::hasParent($name)) {
			$parent = self::$data[$name][self::PREVIOUS];
		}
		return $parent;
	}

	/**
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function isNameUsed($name)
	{
		return isset(self::$data[$name]);
	}

	/**
	 *
	 * @return array
	 */
	public static function getNames()
	{
		return array_keys(self::$data);
	}

	/**
	 *
	 * @return array
	 */
	public static function getData()
	{
		return self::$data;
	}

}
