<?php
// @codingStandardsIgnoreStart

use CRoistatUtils as Utils;

$arResponse = array(
    'statuses' => array(),
    'fields'   => array(),
);

try {
    $arStatusesIDs = array();
    $rsStatus      = CSaleStatus::GetList(array(), array('LID' => 'ru'));
    while ($arStatus = $rsStatus->GetNext()) {
        if (in_array($arStatus['ID'], $arStatusesIDs)) {
            continue;
        }
        $arStatusesIDs[] = $arStatus['ID'];
        $arResponse['statuses'][] = array(
            'id'   => $arStatus['ID'],
            'name' => $arStatus['NAME']
        );
    }

    $arFields = require_once __DIR__ . '/system_fields.php';
    $rsProps  = CSaleOrderProps::GetList();
    while ($arProps = $rsProps->Fetch()) {
        $arFields[$arProps['CODE']] = $arProps['NAME'];
    }
    foreach ($arFields as $code => $name) {
        $arResponse['fields'][] = array(
            'id'   => $code,
            'name' => $name,
        );
    }

    $utils = new Utils();
    if (SITE_CHARSET !== 'UTF-8') {
        $arResponse = $utils->convertToUTF8(SITE_CHARSET, $arResponse);
    }
    if ($arResponse === null || $arResponse === false) {
        $arResponse = array('status' => 'error', 'message' => 'Failed to encode non-UTF8 data.');
    }
} catch (Exception $e) {
    $arResponse = array('status' => 'error', 'message' => 'Exception during process import scheme');
}
echo $utils->jsonResponse($arResponse);