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

	public static $data = [];
	public static $relations = [];
	public static $breakpointCounter = 1;

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
		self::$data[$safeName] = [
			self::NAME => $safeName,
			self::MEMORY => memory_get_usage(),
			self::MEMORY_PEAK => memory_get_peak_usage(),
			self::TIME => microtime(true),
		];
		
		if ($enforceParent !== null) {
			self::createRelation($safeName, $enforceParent);
		}
		
		self::$breakpointCounter++;
	}

	/**
	 * 
	 * @param type $child
	 * @param type $parent
	 * @throws \InvalidArgumentException
	 */
	private static function createRelation($child, $parent)
	{
		if (!self::isNameUsed($child)) {
			throw new \InvalidArgumentException("Unkwnown child breakpoint '$child'");
		}
		
		if (!self::isNameUsed($parent)) {
			throw new \InvalidArgumentException("Unkwnown parent breakpoint '$parent'");
		}
		
		self::$relations[$child] = $parent;
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
	
	/**
	 * 
	 * @param string $name
	 * @return bool
	 */
	public static function hasParent($name)
	{
		return isset(self::$relations[$name]);
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
			$parent = self::$relations[$name];
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
