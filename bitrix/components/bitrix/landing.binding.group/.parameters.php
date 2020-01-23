<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arComponentParameters = Array(
	'PARAMETERS' => array(
		'GROUP_ID' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_GROUP_ID'),
			'TYPE' => 'STRING'
		),
		'PATH_AFTER_CREATE' => array(
			'NAME' => getMessage('LANDING_CMP_PAR_PATH_AFTER_CREATE'),
			'TYPE' => 'STRING'
		)
	)
);
