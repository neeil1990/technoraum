<?php
// @codingStandardsIgnoreStart

use CRoistatUtils as Utils;

$utils = new Utils();

$arModuleVersion = array();
$response        = array();

$path = __DIR__;
include("{$path}/version.php");

$moduleVersion = array_key_exists('VERSION', $arModuleVersion) ? $arModuleVersion['VERSION'] : null;

$response[] = array('core_version'   => constant('SM_VERSION'));
$response[] = array('module_version' => $moduleVersion);

echo $utils->jsonResponse($response);