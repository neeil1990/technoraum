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

$MESS[EventType::TYPE__AMOCRM_UNAVAILABLE . "_SUBJECT"] = 'Unable to contact amoCRM';
$MESS[EventType::TYPE__AMOCRM_UNAVAILABLE . "_MESSAGE"] =
'Information message from #SITE_NAME#
------------------------------------------

Unable to contact amoCRM.

Reason: #ERROR_MESSAGE#

The message was generated automatically';