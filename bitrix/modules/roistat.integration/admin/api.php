<?php
// @codingStandardsIgnoreStart

@define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("currency");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");
CModule::IncludeModule("roistat.integration");

IncludeModuleLangFile(__FILE__);

$LOGIN = COption::GetOptionString("roistat.integration", 'LOGIN', '');
$PASSWORD = COption::GetOptionString("roistat.integration", 'PASSWORD', '');

if (strlen($LOGIN . $PASSWORD) == 0) {
    die('SET PASSWORD FIRST');
}
if ($_REQUEST["token"] != md5($LOGIN . $PASSWORD)) {
    die('INCORRECT TOKEN');
}

if (array_key_exists('action', $_REQUEST)) {
    switch ($_REQUEST['action']) {
        case 'import_scheme':
            require_once(__DIR__ . '/action_import_scheme.php');
            break;
        case 'export':
            require_once(__DIR__ . '/action_export.php');
            break;
        case 'lead':
            require_once(__DIR__ . '/action_lead.php');
            break;
        case 'export_clients':
            require_once(__DIR__ . '/action_export_clients.php');
            break;
        case 'versions':
            require_once(__DIR__ . '/action_versions.php');
            break;
    }
}

// @codingStandardsIgnoreEnd