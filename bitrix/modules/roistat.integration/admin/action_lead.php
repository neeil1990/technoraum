<?php
// @codingStandardsIgnoreStart
use CRoistatUtils as Utils;

/**
 * @return string
 */
function getDefaultSiteId() {
    $dbSites = CSite::GetList($by = 'sort', $order = 'desc', array('DEFAULT' => 'Y'));
    $arSite  = $dbSites->GetNext();
    if ($arSite) {
        return $arSite['LID'];
    }
    return null;
}

/**
 * @param string $name
 * @param string $phone
 * @param string $email
 * @return bool|int
 */
function addUser($name, $phone, $email) {
    if ($email === null || $email === '') {
        $email = time() . '@client-mail.com';
    }
    $randomCode = GetRandomCode();
    $CUser = new CUser();
    return $CUser->Add(array(
        'LOGIN'            => $name . " [{$randomCode}]",
        'NAME'             => $name,
        'EMAIL'            => $email,
        'PERSONAL_MOBILE'  => $phone,
        'PASSWORD'         => $randomCode,
        'CONFIRM_PASSWORD' => $randomCode,
        'ACTIVE'           => 'Y',
    ));
}

/**
 * @param string $siteId
 * @param string $currencyCode
 * @param int $userId
 * @param string $commentText
 * @param array $arFields
 * @return int
 */
function addOrder($siteId, $currencyCode, $userId, $commentText, array $arFields) {
    $personTypeId = 1;
    $arOrderData = array(
        'LID'              => $siteId,
        'PERSON_TYPE_ID'   => $personTypeId,
        'USER_ID'          => $userId,
        'PAYED'            => 'N',
        'CURRENCY'         => $currencyCode,
        'USER_DESCRIPTION' => $commentText,
    );
    $arSystemFields = array('RESPONSIBLE_ID', 'PAY_SYSTEM_ID', 'DELIVERY_ID', 'STATUS_ID', 'COMPANY_ID');
    foreach ($arSystemFields as $field) {
        if (array_key_exists($field, $arFields)) {
            $arOrderData[$field] = $arFields[$field];
            unset($arFields[$field]);
        }
    }
    $CSaleOrder = new CSaleOrder();
    $orderId = $CSaleOrder->Add($arOrderData);
    if (count($arFields) > 0) {
        foreach ($arFields as $code => $value) {
            setOrderProperty($orderId, $personTypeId, $code, $value);
        }
    }
    return $orderId;
}

/**
 * @param int $orderId
 * @param int $personTypeId
 * @param string $code
 * @param string $value
 */
function setOrderProperty($orderId, $personTypeId, $code, $value) {
    $rsProp = CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $personTypeId, 'CODE' => $code));
    if (!$arProp = $rsProp->GetNext()) {
        return;
    }

    $arPropFields = array(
        'ORDER_ID'       => $orderId,
        'ORDER_PROPS_ID' => $arProp['ID'],
        'NAME'           => $arProp['NAME'],
        'CODE'           => $code,
        'VALUE'          => $value,
    );
    $rsPropValue = CSaleOrderPropsValue::GetList(array(), array('ORDER_ID' => $orderId, 'ORDER_PROPS_ID' => $arProp['ID']));
    if ($arPropValue = $rsPropValue->GetNext()) {
        CSaleOrderPropsValue::Update($arPropValue['ID'], $arPropFields);
    } else {
        CSaleOrderPropsValue::Add($arPropFields);
    }
}

/**
 * @param string $phone
 * @param string $email
 * @return string
 */
function getUserIdByContacts($phone, $email) {
    foreach (array('PERSONAL_MOBILE' => $phone, 'EMAIL' => $email) as $fieldCode => $fieldValue) {
        if (strlen($fieldValue) < 4) {
            continue;
        }
        $filter = array(
            $fieldCode => $fieldValue,
        );
        $result = CUser::GetList($by = 'id', $order = 'desc', $filter)->Fetch();
        if (is_array($result) && array_key_exists('ID', $result)) {
            return $result['ID'];
        }
    }
    return null;
}

try {
    $currencyId = COption::GetOptionString('sale', 'default_currency', false);
    $siteId     = getDefaultSiteId();

    if ($siteId === null) {
        exit('DEFAULT SITE IS NOT FOUND');
    }
    if ($currencyId === false) {
        exit('DEFAULT CURRENCY IS NOT FOUND');
    }

    $utils = new Utils();

    $requestData = $_REQUEST;
    $requestData['data'] = json_decode(trim($requestData['data']), 1);
    $requestData = $utils->convertRecursiveToCharset('UTF-8', SITE_CHARSET, $requestData);

    $comment  = "{$requestData['title']}\n{$requestData['text']}";
    $name     = $requestData['name'];
    $phone    = $requestData['phone'];
    $email    = $requestData['email'];
    $arFields = array_merge(array('NAME' => $name, 'PHONE' => $phone, 'EMAIL' => $email), $requestData['data']);
    $userId   = getUserIdByContacts($phone, $email) ?: addUser($name, $phone, $email);
    $orderId  = addOrder($siteId, $currencyId, $userId, $comment, $arFields);

    if (!is_int($orderId)) {
        echo $utils->jsonResponse(array('status' => 'error'));
    } else {
        echo $utils->jsonResponse(array('status' => 'ok', 'order_id' => $orderId));
    }
} catch (Exception $e) {
    echo $utils->jsonResponse(array('status' => 'error', 'message' => 'Exception during process add lead'));
}
// @codingStandardsIgnoreEnd
