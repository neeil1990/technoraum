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
$APPLICATION->IncludeComponent(
    "rover:amocrm.preset.elements",
    ".default",
    array(
        'ID' => $_REQUEST['preset_id'],
    ),
    false
);?>
<?require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_admin.php");?>