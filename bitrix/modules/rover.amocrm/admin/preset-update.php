<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 17.09.2017
 * Time: 13:08
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin.php");?>
<?
use \Rover\AmoCRM\Entity\Source;
use Bitrix\Main\Application;

$request = Application::getInstance()->getContext()->getRequest();

$APPLICATION->IncludeComponent(
    "rover:amocrm.preset.update",
    ".default",
    array(
        'PRESET_ID'     => $request->get('preset_id'),
        'SOURCE_TYPE'   => $request->get(Source::INPUT__TYPE),
    ),
    false
);?>
<?require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_admin.php");?>