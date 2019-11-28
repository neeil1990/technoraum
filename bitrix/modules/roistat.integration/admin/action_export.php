<?php
// @codingStandardsIgnoreStart

use CRoistatUtils as Utils;

try {
    $CURRENCY = COption::GetOptionString('sale', 'default_currency', '');
    $IS_APPEND_DELIVERY_SUM_TO_COST = COption::GetOptionString("roistat.integration", 'IS_APPEND_DELIVERY_SUM_TO_COST');
    $APPLICATION->RestartBuffer();

    $arResult             = array();
    $arResult['orders']   = array();
    $iLimit               = array_key_exists('limit', $_REQUEST) ? (int)$_REQUEST['limit'] : 1000;
    $iOffset              = array_key_exists('offset', $_REQUEST) ? (int)$_REQUEST['offset'] : 0;
    $iModifiedDate        = array_key_exists('date', $_REQUEST) ? (int)$_REQUEST['date'] : (time() - 31 * 24 * 60 * 60);

    $arFilter = array(
        '>DATE_UPDATE' => ConvertTimeStamp($iModifiedDate),
    );

    $arSystemFields = require_once __DIR__ . '/system_fields.php';

    $arIndexedPayments = array();
    $rsPayments = CSalePaySystem::GetList();
    while ($arPayments = $rsPayments->Fetch()) {
        $arIndexedPayments[$arPayments['ID']] = $arPayments['NAME'];
    }

    $arIndexedDeliveries = array();
    $rsDelivery = CSaleDelivery::GetList();
    while ($arDelivery = $rsDelivery->Fetch()) {
        $arIndexedDeliveries[$arDelivery['ID']] = $arDelivery['NAME'];
    }

    $arOrderID = array();

    $rsOrder = CSaleOrder::GetList(array('ID' => 'DESC'), $arFilter, false, array(
        'iNumPage'  => $iOffset > 0 ? ($iOffset / $iLimit) + 1 : 1,
        'nPageSize' => $iLimit,
    ));
    while ($arOrder = $rsOrder->GetNext()) {
        $arAdditionalFields = array(
            'PAY_SYSTEM_NAME' => array_key_exists($arOrder['PAY_SYSTEM_ID'], $arIndexedPayments) ? $arIndexedPayments[$arOrder['PAY_SYSTEM_ID']] : null,
            'DELIVERY_NAME'   => array_key_exists($arOrder['DELIVERY_ID'], $arIndexedDeliveries) ? $arIndexedDeliveries[$arOrder['DELIVERY_ID']] : null,
            'paid_date'       => $arOrder['DATE_PAYED'],
        );
        foreach ($arOrder as $key => $value) {
            if (array_key_exists($key, $arSystemFields)) {
                $arAdditionalFields[$key] = $value;
            }
        }

        $arResult['orders'][] = array(
            'id'          => $arOrder['ID'],
            'date_create' => MakeTimeStamp($arOrder['DATE_INSERT'], 'DD.MM.YYYY HH:MI:SS'),
            'date_update' => MakeTimeStamp($arOrder['DATE_UPDATE'], 'DD.MM.YYYY HH:MI:SS'),
            'status'      => $arOrder['STATUS_ID'],
            'price'       => round($arOrder['PRICE']),
            'client_id'   => $arOrder['USER_ID'],
            'fields'      => $arAdditionalFields,
        );
        $arOrderID[] = $arOrder['ID'];
    }

    $arPropValues = array();
    $rsProps = CSaleOrderPropsValue::GetList(
        array(),
        array('ORDER_ID' => $arOrderID),
        false,
        false,
        array('ID', 'CODE', 'VALUE', 'ORDER_PROPS_ID', 'PROP_TYPE', 'ORDER_ID')
    );
    while ($arProps = $rsProps->Fetch()) {
        $arCurOrderPropsTmp = CSaleOrderProps::GetRealValue(
            $arProps['ORDER_PROPS_ID'],
            $arProps['CODE'],
            $arProps['PROP_TYPE'],
            $arProps['VALUE'],
            LANGUAGE_ID
        );
        foreach ($arCurOrderPropsTmp as $key => $value) {
            $arPropValues[$arProps['ORDER_ID']][$key] = $value;
        }
    }

    $arOrderBasket = array();
    $arProductID = array();

    $dbBasket = CSaleBasket::GetList(
        array('NAME' => 'ASC'),
        array('ORDER_ID' => $arOrderID),
        false,
        false,
        array('ID', 'QUANTITY', 'PRODUCT_ID', 'ORDER_ID')
    );
    while ($arBasket = $dbBasket->GetNext()) {
        $arOrderBasket[$arBasket['ORDER_ID']][$arBasket['PRODUCT_ID']] = intval($arBasket['QUANTITY']);
        //economy memory
        if (!in_array($arBasket['PRODUCT_ID'], $arProductID)) {
            $arProductID[] = $arBasket['PRODUCT_ID'];
        }
    }

    $productsCost = array();
    $dbProducts   = CCatalogProduct::GetList(array(), array('ID' => $arProductID), false, false, array('ID', 'PURCHASING_PRICE'));
    while ($arProduct = $dbProducts->Fetch()) {
        $productsCost[$arProduct['ID']] = floatval($arProduct['PURCHASING_PRICE']);
    }

    foreach ($arResult['orders'] as $key => $orderData) {
        foreach ($arPropValues[$orderData['id']] as $prop => $propValue) {
            if ($prop === 'ROISTAT_VISIT') {
                $arResult['orders'][$key]['visit'] = $propValue;
            } elseif (strtolower($prop) === 'cost') {
                $arResult['orders'][$key]['cost'] = $propValue;
            } else {
                $arResult['orders'][$key]['fields'][$prop] = $propValue;
            }
        }
        if (array_key_exists('cost', $arResult['orders'][$key]) && $arResult['orders'][$key]['cost'] !== '') {
            continue;
        }

        $cost = 0;
        $fields = $arResult['orders'][$key]['fields'];
        if ($IS_APPEND_DELIVERY_SUM_TO_COST === 'Y' && array_key_exists('PRICE_DELIVERY', $fields)) {
            $cost += $fields['PRICE_DELIVERY'];
        }

        foreach ($arOrderBasket[$orderData['id']] as $productId => $quantity) {
            if (array_key_exists($productId, $productsCost) !== false) {
                $cost += $productsCost[$productId] * $quantity;
            }
        }
        $arResult['orders'][$key]['cost'] = $cost > 0 ? $cost : null;
    }

    $arResult['pagination'] = array(
        'total_count' => $rsOrder->SelectedRowsCount(),
        'limit'       => $iLimit,
    );

    $utils = new Utils();
    $response = $arResult;
    if (SITE_CHARSET !== 'UTF-8') {
        $response = $utils->convertToUTF8(SITE_CHARSET, $response);
    }
    if ($response === null || $response === false) {
        $response = array('status' => 'error', 'message' => 'Failed to encode non-UTF8 data.');
    }

    echo $utils->jsonResponse($response);
} catch (Exception $e) {
    echo $utils->jsonResponse(array('status' => 'error', 'message' => 'Exception during process add lead'));
}

// @codingStandardsIgnoreEnd