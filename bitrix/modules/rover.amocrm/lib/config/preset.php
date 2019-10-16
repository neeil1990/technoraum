<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 12.02.2016
 * Time: 2:22
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
namespace Rover\AmoCRM\Config;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
/**
 * Class Preset
 *
 * @package Rover\AmoCRM\Config
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Preset
{
	const OPTION_ID = 'form_connects';

    /** @var */
	protected static $cache;

    /**
     * @param $presetId
     * @param $entityId
     * @param $type
     * @return bool
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function add($presetId, $entityId, $type)
	{
		$presets = self::getAll();

		if (self::exists($presets, $entityId, $type))
			return false;

		$presets[$presetId] = array(
			'id'    => $entityId,
			'type'  => $type
        );

		return self::save($presets);
	}

	/**
	 * @param array $presets
	 * @param       $entityId
	 * @param       $type
	 * @return bool
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	protected static function exists(array $presets, $entityId, $type)
	{
		foreach ($presets as $preset)
			if (($preset['id'] == $entityId)
				&& ($preset['type'] == $type))
				return true;

		return false;
	}

    /**
     * @return array|mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getAll()
	{
		if (is_null(self::$cache)) {
			$presets = unserialize(Option::get(Options::MODULE_ID, self::OPTION_ID));
			if (!is_array($presets))
				$presets = array();

			self::$cache = $presets;
		}

		return self::$cache;
	}

    /**
     * @param $id
     * @return mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getById($id)
	{
		$id = intval($id);

		if (!$id)
			throw new ArgumentNullException('id');

		$presets = self::getAll();

		if (isset($presets[$id]))
			return $presets[$id];

		return null;
	}

    /**
     * @param $id
     * @return null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getTypeById($id)
	{
		$preset = self::getById($id);
		if (isset($preset['type']))
			return $preset['type'];

		return null;
	}

    /**
     * @param $entityId
     * @param $type
     * @return int|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getIdByEntityId($entityId, $type)
	{
		$presets = self::getAll();

		foreach($presets as $presetId => $preset)
			if (($preset['id'] == $entityId)
				&& ($preset['type'] == $type))
			{
				return $presetId;
			}

		return null;
	}

    /**
     * @param $id
     * @return bool
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function removeById($id)
	{
		$presets = self::getAll();
		unset($presets[$id]);

		return self::save($presets);
	}

    /**
     * @param $presets
     * @return bool
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function save($presets)
	{
		Option::set(Options::MODULE_ID, self::OPTION_ID, serialize($presets));
		self::$cache = null;

		return true;
	}
}