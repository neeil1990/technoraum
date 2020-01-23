<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_59_2-NAME'),
		'section' => array('sidebar', 'other'),
		'type' => ['knowledge', 'group'],
		'subtype' => 'search',
		'subtype_params' => [
			'type' => 'form',
			'resultPage' => 'search-result2'
		],
		'version' => '20.0.0',
	],
	'nodes' => [
		// todo: need for style hover. If not exist nodes elemtent - button will be pointer-events:none
		// '.landing-block-node-button' => [
		// 	'name' => Loc::getMessage('LANDING_BLOCK_59_2-BUTTON'),
		// 	'type' => 'link',
		// ],
	],
	'style' => [
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_2-BUTTON'),
			'type' => ['background-color', 'color'],
		],
	],
	'attrs' => [
		'.landing-block-node-form' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_2-SEARCH_RESULT'),
			'attribute' => 'action',
			'type' => 'url',
			'allowedTypes' => [
				'landing',
			],
			'disableCustomURL' => true,
			'disallowType' => true,
			'disableBlocks' => true
		]
	]
];