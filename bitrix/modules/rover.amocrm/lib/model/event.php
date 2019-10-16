<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 20.06.2017
 * Time: 11:33
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model;

use Bitrix\Main\Mail\Internal\EventTable;
use Bitrix\Main\Type\DateTime;
use Rover\AmoCRM\Config\Options;

/**
 * Class Event
 *
 * @package Rover\AmoCRM\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Event
{
    /**
     * @param $message
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function sendUnavailable($message)
    {
        if (!self::canSendNewMessage())
            return;

        \RoverAmoCRMEvents::disable();

        \CEvent::Send(EventType::TYPE__AMOCRM_UNAVAILABLE,
            Options::getCurSiteId(),
            array('ERROR_MESSAGE' => $message));

        \RoverAmoCRMEvents::enable();
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function canSendNewMessage()
    {
        $query = array(
            'filter' => array(
                '=EVENT_NAME' => EventType::TYPE__AMOCRM_UNAVAILABLE
            ),
            'order' => array('ID' => 'DESC'),
        );

        $event = EventTable::getRow($query);
        if (!$event || (!$event['DATE_INSERT'] instanceof DateTime))
            return true;

        /** @var DateTime $lastDate */
        $lastDate   = $event['DATE_INSERT'];
        $now        = new DateTime();

        return ($now->getTimestamp() - $lastDate->getTimestamp()) > 60 * 60;
    }
}