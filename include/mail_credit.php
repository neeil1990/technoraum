<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

include($_SERVER["DOCUMENT_ROOT"]."/include/amocrm.php");

$name = iconv("UTF-8","CP1251",trim(strip_tags($_REQUEST["firstName"])));
$phone = iconv("UTF-8","CP1251",trim(strip_tags($_REQUEST["phone"])));
$shop = iconv("UTF-8","CP1251",trim(strip_tags($_REQUEST["shop"])));
$price = iconv("UTF-8","CP1251",trim(strip_tags($_REQUEST["price"])));
$namep = iconv("UTF-8","CP1251",trim(strip_tags($_REQUEST["namep"])));
$sku = iconv("UTF-8","CP1251",trim(strip_tags($_REQUEST["sku"])));
$location = iconv("UTF-8","CP1251",trim(strip_tags($_REQUEST["location"])));
$form = iconv("UTF-8","CP1251",trim(strip_tags($_REQUEST["form"])));

$fieldpost = array(
'shop' => $shop,
'price' => $price,
'namep' => $namep,
'sku' => $sku,
'location' => $location
);

login($name,$phone, $form, $fieldpost);

$arEventFields = array(
    "NAME" => $name,
    "PHONE" => $phone,
    "SHOP" => $shop
);

CEvent::Send("MAIL_CREDIT", SITE_ID, $arEventFields);