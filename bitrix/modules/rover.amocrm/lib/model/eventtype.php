<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 19.08.2016
 * Time: 17:28
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model;

use Bitrix\Main\ArgumentNullException;
// @todo use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class EventType
 *
 * @package Rover\EventGroup\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class EventType
{
	const TYPE__AMOCRM_UNAVAILABLE  = 'AMOCRM_UNAVAILABLE';

    /**
     * @param      $eventName
     * @param bool $lid
     * @return bool
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function checkByEventName($eventName, $lid = false)
	{
		return (bool)self::getByEventName($eventName, $lid)->fetch();
	}

    /**
     * @param      $eventName
     * @param bool $lid
     * @return \Bitrix\Main\DB\Result
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getByEventName($eventName, $lid = false)
	{
		if (!strlen($eventName))
			throw new ArgumentNullException('eventName');

        $query = array(
            'filter'    => array(
                '=EVENT_NAME' => $eventName,
                '=LID' => $lid ? : 'ru'
            ),
        );

		return EventTypeTable::getList($query);
	}

    /**
     * @param        $eventName
     * @param string $languageId
     * @return |null
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getIdByEventName($eventName, $languageId = LANGUAGE_ID)
    {
        if ($type = self::getByEventName($eventName, $languageId)->fetch())
            return $type['ID'];

        return null;
    }

	/**
	 * @return bool
	 * @throws SystemException
	 * @throws \Exception
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function create()
	{
        $query = array('select' => array('LID'), 'order' => array('SORT' => 'asc'));
        $langs = LanguageTable::getList($query);

		while($lang = $langs->fetch())
			self::createByTypeLang(self::TYPE__AMOCRM_UNAVAILABLE, $lang["LID"]);

		return true;
	}

    /**
     * @param $type
     * @param $lid
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function createByTypeLang($type, $lid)
    {
        $type = trim($type);
        if (!strlen($type))
            throw new ArgumentNullException('type');

        $lid = trim($lid);
        if (!strlen($lid))
            throw new ArgumentNullException('lid');

        if (self::checkByEventName(self::TYPE__AMOCRM_UNAVAILABLE, $lid))
            return;

        // add messages in all languages
        IncludeModuleLangFile(__FILE__, $lid);

        $data = array(
            "EVENT_NAME"    => $type,
            "NAME"          => Loc::getMessage($type . '_NAME'),
            "LID"           => $lid,
            "DESCRIPTION"   => Loc::getMessage($type . '_DESCR')
        );

        $result = EventTypeTable::add($data);
        if (!$result->isSuccess())
            throw new SystemException(implode('<br>', $result->getErrorMessages()));
    }

	/**
	 * @throws ArgumentNullException
	 * @throws \Exception
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function remove()
	{
		$eventTypes = self::getByEventName(self::TYPE__AMOCRM_UNAVAILABLE);

		while ($eventType = $eventTypes->fetch()) {
			$result = EventTypeTable::delete($eventType['ID']);

			if (!$result->isSuccess())
				throw new SystemException(implode('<br>', $result->getErrorMessages()));
		}
	}

    /**
     * @param $id
     * @return array|null
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getById($id)
    {
        $id = intval($id);
        if (!$id)
            throw new ArgumentNullException('eventTypeId');

        $query = array(
            'filter' => array('=ID' => $id),
            'select' => array('ID', 'DESCRIPTION', 'NAME', 'EVENT_NAME')
        );

        return EventTypeTable::getRow($query);
    }
}