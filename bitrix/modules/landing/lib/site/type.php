<?php
namespace Bitrix\Landing\Site;

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Manager;

class Type
{
	/**
	 * Current scope class name.
	 * @var string
	 */
	protected static $currentScopeClass = null;

	/**
	 * Scope already init.
	 * @var bool
	 */
	protected static $scopeInit = false;

	/**
	 * Returns scope class, if exist.
	 * @param string $scope Scope code.
	 * @return string|null
	 */
	protected static function getScopeClass($scope)
	{
		$scope = trim($scope);
		$class = __NAMESPACE__ . '\\Scope\\' . $scope;
		if (class_exists($class))
		{
			return $class;
		}

		return null;
	}

	/**
	 * Set global scope.
	 * @param string $scope Scope code.
	 * @param array $params Additional params.
	 * @return void
	 */
	public static function setScope($scope, array $params = [])
	{
		if (self::$scopeInit || !is_string($scope))
		{
			return;
		}
		if (self::$currentScopeClass === null)
		{
			self::$currentScopeClass = self::getScopeClass($scope);
			self::$scopeInit = true;
			if (self::$currentScopeClass)
			{
				self::$currentScopeClass::init($params);
			}
		}
	}

	/**
	 * Clear selected scope.
	 */
	public static function clearScope()
	{
		self::$scopeInit = false;
		self::$currentScopeClass = null;
	}

	/**
	 * Returns publication path string.
	 * @return string|null
	 */
	public static function getPublicationPath()
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getPublicationPath();
		}

		return null;
	}

	/**
	 * Return general key for site path (ID or CODE).
	 * @return string
	 */
	public static function getKeyCode()
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getKeyCode();
		}

		return 'ID';
	}

	/**
	 * Returns domain id for new site.
	 * @return int|string
	 */
	public static function getDomainId()
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getDomainId();
		}
		return '';
	}

	/**
	 * Returns current scope id.
	 * @return string|null
	 */
	public static function getCurrentScopeId()
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getCurrentScopeId();
		}
		return null;
	}

	/**
	 * Returns filter value for 'TYPE' key.
	 * @param bool $strict If strict, returns without default.
	 * @return string|string[]
	 */
	public static function getFilterType($strict = false)
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getFilterType();
		}

		// compatibility, huh
		return $strict ? null : ['PAGE', 'STORE', 'SMN', 'PREVIEW'];
	}

	/**
	 * Returns true, if type is enabled in system.
	 * @param string $code Type code.
	 * @return bool
	 */
	public static function isEnabled($code)
	{
		if (is_string($code))
		{
			$code = strtoupper(trim($code));
			$types = Site::getTypes();
			if (array_key_exists($code, $types))
			{
				return true;
			}
		}

		return false;
	}
}