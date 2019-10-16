<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 05.10.2017
 * Time: 12:38
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM;

use Bitrix\Main\Config\Option;
use Rover\AmoCRM\Config\Dependence;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Model\StatusTable;

/**
 * Class Agent
 *
 * @package Rover\AmoCRM
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Agent
{
    /**
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function checkNewEvents()
    {
        $dependence = new Dependence();
        if ($dependence->checkBase()->getResult()){
            $limit = Options::getAgentEventsCountStatic();
            $count = Entry::pushByStatus(StatusTable::STATUS__NEW, $limit);

            $errorsLimit = $limit - $count;
            if ($errorsLimit > 0)
                Entry::pushByStatus(StatusTable::STATUS__ERROR, $errorsLimit);
        }


        return self::getNewEventsName();
    }

    /**
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function deleteOldEvents()
    {
        $dependence = new Dependence();
        if ($dependence->checkBase()->getResult()){
            $period = Option::get('rover.amocrm', Tabs::INPUT__EVENT_LOG_LIFETIME, 365);
            StatusTable::deleteOld($period);
        }

        return self::getOldEventsName();
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getNewEventsName()
    {
        return '\\' . get_called_class() . '::checkNewEvents();';
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getOldEventsName()
    {
        return '\\' . get_called_class() . '::deleteOldEvents();';
    }
}