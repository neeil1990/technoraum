<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 19.08.2016
 * Time: 17:38
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
namespace Rover\AmoCRM\Model;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Mail\Internal\EventMessageSiteTable;
use Bitrix\Main\Mail\StopException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Rover\Params\Main;

Loc::loadMessages(__FILE__);

/**
 * Class EventMessage
 *
 * @package Rover\EventGroup\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class EventMessage
{
	/**
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Exception
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function create()
	{
		$eventsType = EventType::getByEventName(EventType::TYPE__AMOCRM_UNAVAILABLE);
		if (!$eventsType->getSelectedRowsCount())
			return;

		$sitesIds = array_keys(Main::getSites(array('empty' => null)));

		$data = array(
			'ACTIVE'        => 'Y',
			'EVENT_NAME'    => EventType::TYPE__AMOCRM_UNAVAILABLE,
			"EMAIL_FROM"    => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO"      => "#DEFAULT_EMAIL_FROM#",
			"SUBJECT"       => Loc::getMessage(EventType::TYPE__AMOCRM_UNAVAILABLE . "_SUBJECT"),
			"BODY_TYPE"     => 'text',
            'LID'           => implode(',', $sitesIds),
			"MESSAGE"       => Loc::getMessage(EventType::TYPE__AMOCRM_UNAVAILABLE . "_MESSAGE")
        );

		// hardcode!
        $eventType['LID'] = 'ru';

        while ($eventType = $eventsType->fetch())
        {
            IncludeModuleLangFile(__FILE__, $eventType['LID']);

            if (self::checkByEventName(EventType::TYPE__AMOCRM_UNAVAILABLE))
                continue;

            $result = EventMessageTable::add($data);

            if (!$result->isSuccess())
                throw new StopException(implode('<br>', $result->getErrorMessages()));

            foreach ($sitesIds as $siteId)
                EventMessageSiteTable::add(array(
                    'EVENT_MESSAGE_ID'  => $result->getId(),
                    'SITE_ID'           => $siteId
                ));

            break;
        }
	}

    /**
     * @param $eventMessagesIds
     * @return array
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getMessageTypes($eventMessagesIds)
    {
        if (empty($eventMessagesIds))
            return array();

        $connection = Application::getConnection();
        $sqlHelper  = $connection->getSqlHelper();
        $sql = 'SELECT DISTINCT(EVENT_NAME) 
                    FROM ' . $sqlHelper->forSql(EventMessageTable::getTableName()) . ' 
                    WHERE ID IN (' . implode(',', $eventMessagesIds) . ')
                    ORDER BY EVENT_NAME';

        $eventTypes = $connection->query($sql);
        $result     = array();
        while ($item = $eventTypes->fetch())
            $result[] = $item['EVENT_NAME'];

        return $result;
    }

    /**
     * @param      $eventName
     * @param bool $siteId
     * @return bool
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function checkByEventName($eventName, $siteId = false)
	{
		return (bool)self::getByEventName($eventName, $siteId)
			->getSelectedRowsCount();
	}

    /**
     * @param      $eventName
     * @param bool $siteId
     * @return \Bitrix\Main\DB\Result
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getByEventName($eventName, $siteId = false)
	{
		if (!$eventName)
			throw new ArgumentNullException('eventName');

		$query = array('filter' => array('=EVENT_NAME' => $eventName));

		if ($siteId)
			$query['filter']['=EVENT_MESSAGE_SITE.SITE_ID'] = $siteId;

		return EventMessageTable::getList($query);
	}

	/**
	 * @throws ArgumentNullException
	 * @throws \Exception
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function remove()
	{
	    $connection = Application::getConnection();
	    $sqlHelper  = $connection->getSqlHelper();

		$eventMessages = self::getByEventName(EventType::TYPE__AMOCRM_UNAVAILABLE);

        while ($eventMessage = $eventMessages->fetch()){
            $result = EventMessageTable::delete($eventMessage['ID']);

            if (!$result->isSuccess())
                throw new StopException(implode('<br>', $result->getErrorMessages()));

            $sql = 'DELETE FROM ' . $sqlHelper->forSql(EventMessageSiteTable::getTableName())
                . ' WHERE EVENT_MESSAGE_ID=' . $eventMessage['ID'];

            $connection->queryExecute($sql);
        }
	}
}