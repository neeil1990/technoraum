<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.10.2017
 * Time: 11:52
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model\AdditionalParam;

use Bitrix\Main\Application;
use Rover\AmoCRM\Model\AdditionalParam;

/**
 * Class AdvMarks
 *
 * @package Rover\AmoCRM\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Ip extends AdditionalParam
{
    /**
     * @return bool|mixed
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getValue()
    {
        $ips    = array();
        $server = Application::getInstance()
            ->getContext()->getServer();

        if ($server->get('HTTP_X_FORWARDED_FOR'))
            $ips[] = trim(strtok($server->get('HTTP_X_FORWARDED_FOR'), ','));

        if ($server->get('HTTP_CLIENT_IP'))
            $ips[] = $server->get('HTTP_CLIENT_IP');

        if ($server->get('REMOTE_ADDR'))
            $ips[] = $server->get('REMOTE_ADDR');

        if ($server->get('HTTP_X_REAL_IP'))
            $ips[] = $server->get('HTTP_X_REAL_IP');

        foreach($ips as $ip)
            if((bool)preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $ip))
                return $ip;

        return false;
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getName()
    {
        return self::PARAM__IP;
    }
}