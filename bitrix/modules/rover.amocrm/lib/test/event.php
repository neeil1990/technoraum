<?php
namespace Rover\AmoCRM\Test;

/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 29.05.2017
 * Time: 10:54
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
/**
 * Class Event
 *
 * @package Rover\AmoCRM\Test
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Event
{
    /**
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function newUser()
    {
        $fields = array(
            'LOGIN'         => 'test',
            'NAME'          => 'meeting.jpg',
            'LAST_NAME'     => 'test',
            'CHECKWORD'     => '4560e4bb8feac82ade8cbfed062ec5ab',
            'EMAIL'         => 'test@test.ru',
            'ACTIVE'        => 'Y',
            'CONFIRM_CODE'  => '',
            'SITE_ID'       => 's1',
            'LANGUAGE_ID'   => 'ru',
            'USER_IP'       => '192.168.14.251',
            'USER_HOST'     => 'host',
            'GROUP_ID'      => array(6),
            'LID'           => 's1',
            'USER_ID'       => 9,
            'PERSONAL_COUNTRY'      => 1,
            'PERSONAL_STATE'        => 'state',
            'PERSONAL_CITY'         => 'city',
            'UF_REG_COUNTRY_NAME'  => 'Россия',
        );

        \RoverAmoCRMEvents::onBeforeEventAdd('NEW_USER', 's1', $fields, null, null, true);
    }

    /**
     * @param array $files
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function feedbackForm($files = array())
    {
        $fields = array(
            'AUTHOR'       => 'test',
            'AUTHOR_EMAIL' => 'test@test.ru',
            'EMAIL_TO'     => 'test@test.ru',
            'TEXT'         => 'vvv mmm'
        );

        \RoverAmoCRMEvents::onBeforeEventAdd('FEEDBACK_FORM', 's1', $fields, null, $files, true);
    }

    /**
     * @param string $orderNumber
     * @param int    $accountNaumber
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function orderPaid($orderNumber = 'J6L84W', $accountNaumber = 49)
    {
        $fields = array(
            'ORDER_ID'      => $orderNumber,
            'ORDER_REAL_ID' => $accountNaumber,
            'ORDER_ACCOUNT_NUMBER_ENCODE' => $orderNumber,
            'ORDER_DATE'    => '25.07.2017 15:02:55',
            'EMAIL'         => 'rover.webdev@gmail.com',
            'SALE_EMAIL'    => 'rover.webdev@gmail.com'
        );

        \RoverAmoCRMEvents::onBeforeEventAdd('SALE_ORDER_PAID', 's1', $fields, null, null, true);
    }
}