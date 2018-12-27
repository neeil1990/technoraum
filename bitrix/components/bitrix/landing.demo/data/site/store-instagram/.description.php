<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
$buttons = array_keys($buttons);

return array(
	'code' => 'store-instagram',
	'name' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--DESC"),
	'active' => false,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'store',
	'sort' => 10,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => $buttons[0],
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => '1construction',
			'THEME_CODE_TYPO' => '3corporate',
		),
		'TITLE' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--NAME"),
	),
	'layout' => array(),
	'folders' => array(),
	'syspages' => array(
		'order' => 'store-instagram/checkout',
		'cart' => 'store-instagram/checkout',
		'payment' => 'store-instagram/payment',
	),
	'items' => array(
		0 => 'store-instagram/mainpage',
		1 => 'store-instagram/checkout',
		2 => 'store-instagram/payment',
	),
);