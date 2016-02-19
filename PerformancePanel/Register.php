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

	const DEFAULT_BP_NAME = 'BP_';

	public static $data = array();
	public static $breakpointCounter = 1;

	/**
	 * Add breakpoint
	 * @param string|null $name
	 * @param string|null $enforceParent unsupported yet
	 */
	public static function addBreakpoint($name = null, $enforceParent = null)
	{
		$safeName = self::getName($name);
		self::$data[$safeName] = array(
			self::NAME => $safeName,
			self::MEMORY => memory_get_usage(),
			self::MEMORY_PEAK => memory_get_peak_usage(),
			self::TIME => microtime(true),
		);
		self::$breakpointCounter++;
	}

	/**
	 * Add breakpoint - addBreakpoint alias
	 * @param string|null $name
	 * @param string|null $enforceParent unsupported yet
	 */
	public static function add($name = null, $enforceParent = null)
	{
		self::addBreakpoint($name, $enforceParent);
	}

	/**
	 *
	 * @param string|null $name
	 * @return string
	 */
	public static function getName($name = null)
	{
		if ($name === null) {
			$name = self::DEFAULT_BP_NAME . self::$breakpointCounter;
		} else {
			if (self::isNameUsed($name)) {
				$name = self::getName($name . '_' . self::$breakpointCounter);
			}
		}
		return $name;
	}

	public static function isNameUsed($name)
	{
		return isset(self::$data[$name]);
	}

	public static function getNames()
	{
		return array_keys(self::$data);
	}

	public static function getData()
	{
		return self::$data;
	}

}
