<?php

/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 19.08.2016
 * Time: 17:44
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use Rover\AmoCRM\Model\EventType;

$MESS[EventType::TYPE__AMOCRM_UNAVAILABLE . "_SUBJECT"] = 'Не удалось связаться с сервером amoCRM';
$MESS[EventType::TYPE__AMOCRM_UNAVAILABLE . "_MESSAGE"] =
'Информационное сообщение сайта #SITE_NAME#
------------------------------------------

Не удалось связаться с сервером amoCRM.

Причина: #ERROR_MESSAGE#

Сообщение сгенерировано автоматически';