<?php
// @codingStandardsIgnoreStart

use CRoistatUtils as Utils;

/**
 * @param string $phone
 * @return string
 */
function stripNotNumericSymbols($phone) {
    $normalizedPhone = preg_replace('#[^\d]#', '', $phone);
    return $normalizedPhone ?: null;
}

/**
 * [
 *     'CLIENT_ID' => 'USER_ID',
 * ]
 *
 * @param array $usersIds
 * @return array
 */
function loadShopClientsByUsersIds(array $usersIds) {
    static $result;
    if ($result === null) {
        $result = array();
        $dbClients = CSaleOrderUserProps::GetList(
            array('DATE_UPDATE' => 'DESC'),
            array('USER_ID' => $usersIds)
        );
        while ($arClient = $dbClients->Fetch()) {
            $userId            = $arClient['USER_ID'];
            $clientId          = $arClient['ID'];
            $result[$clientId] = $userId;
        }
    }
    return $result;
}

/**
 * @param array $usersIds
 * @return array
 */
function loadShopContactsDataByUserIds(array $usersIds) {
    $result           = array();
    $userIdByClientId = loadShopClientsByUsersIds($usersIds);
    $clientsIds       = array_keys($userIdByClientId);

    $dbPropertiesValues = CSaleOrderUserPropsValue::GetList(($b='SORT'), ($o='ASC'), Array('USER_PROPS_ID' => $clientsIds));
    while ($arPropertyValues = $dbPropertiesValues->Fetch()) {
        $clientId = $arPropertyValues['USER_PROPS_ID'];
        $value    = $arPropertyValues['VALUE'];
        if (!array_key_exists($clientId, $userIdByClientId)) {
            continue;
        }

        $userId       = $userIdByClientId[$clientId];
        $propertyCode = strtoupper($arPropertyValues['CODE']);

        if (!in_array($propertyCode, array('PHONE', 'EMAIL'))) {
            continue;
        }
        if (!array_key_exists($userId, $result)) {
            $result[$userId] = array();
        }
        if (!array_key_exists($propertyCode, $result[$userId])) {
            $result[$userId][$propertyCode] = array();
        }
        if ($propertyCode === 'PHONE') {
            $value = stripNotNumericSymbols($value);
        }
        $result[$userId][$propertyCode][] = $value;
    }
    return $result;
}

/**
 * @param string $stringWithValues
 * @param string $userId
 * @param string $propertyCode
 * @param array $contactsData
 * @return string
 */
function addContactsValuesFromShopClients($stringWithValues, $userId, $propertyCode, array $contactsData) {
    $result = $stringWithValues;
    if (!array_key_exists($userId, $contactsData) || !array_key_exists($propertyCode, $contactsData[$userId])) {
        return $result;
    }
    foreach ($contactsData[$userId][$propertyCode] as $contactValue) {
        if ($contactValue !== '') {
            if (strpos($result, $contactValue) !== false) {
                continue;
            }
            if ($result !== '' && substr($result, -1) !== ',') {
                $result .= ',';
            }
            $result .= $contactValue;
        }
    }
    return $result;
}

/**
 * @param array $userData
 * @param array $allowedFields
 * @param string $delimiter
 * @return string
 */
function extractContactFields(array $userData, array $allowedFields, $delimiter = ', ') {
    $result = array();
    foreach ($userData as $fieldName => $fieldValue) {
        if (is_string($fieldValue) && $fieldValue !== '' && in_array($fieldName, $allowedFields)) {
            $result[] = $fieldValue;
        }
    }
    return count($result) > 0 ? implode($delimiter, $result) : null;
}

try {
    global $DB;

    $iLimit        = array_key_exists('limit', $_REQUEST) ? (int)$_REQUEST['limit'] : 1000;
    $iOffset       = array_key_exists('offset', $_REQUEST) ? (int)$_REQUEST['offset'] : 0;
    $iModifiedDate = array_key_exists('date', $_REQUEST) ? (int)$_REQUEST['date'] : (time() - 31 * 24 * 60 * 60);

    $sFilters = "TIMESTAMP_X >= FROM_UNIXTIME({$iModifiedDate}) OR DATE_REGISTER >= FROM_UNIXTIME({$iModifiedDate})";

    $dbUsersCount = $DB->Query("SELECT COUNT(*) as count FROM b_user WHERE {$sFilters}", false, 'Roistat: export clients count ' . __LINE__);
    $arUsersCount = $dbUsersCount->Fetch();

    $sql = "SELECT
          ID,
          TIMESTAMP_X,
          NAME,
          LAST_NAME,
          SECOND_NAME,
          EMAIL,
          DATE_REGISTER,
          PERSONAL_PHONE,
          PERSONAL_FAX,
          PERSONAL_MOBILE,
          PERSONAL_BIRTHDAY,
          WORK_PHONE,
          WORK_FAX,
          WORK_COMPANY
        FROM 
          b_user
        WHERE
          {$sFilters}
        LIMIT {$iOffset},{$iLimit}";

    $arUsers  = $DB->Query($sql, false, 'Roistat: export clients ' . __LINE__);
    $usersIds = array();
    $response = array();
    while ($arUser = $arUsers->Fetch()) {
        $phonesFields = array(
            'PERSONAL_PHONE',
            'PERSONAL_FAX',
            'PERSONAL_MOBILE',
            'WORK_PHONE',
            'WORK_FAX',
        );
        $nameFields = array(
            'NAME',
            'LAST_NAME',
            'SECOND_NAME',
        );
        $usersIds[] = $arUser['ID'];
        $userData = array(
            'id'         => $arUser['ID'],
            'name'       => extractContactFields($arUser, $nameFields, ' '),
            'phone'      => extractContactFields($arUser, $phonesFields),
            'email'      => $arUser['EMAIL'],
            'company'    => $arUser['WORK_COMPANY'],
            'birth_date' => $arUser['PERSONAL_BIRTHDAY'],
        );
        $response[] = $userData;
    }

    // @todo :: temporarily
    $shopContactsData = loadShopContactsDataByUserIds($usersIds);
    foreach ($response as $index => $userData) {
        $userId = $userData['id'];
        $phones = $response[$index]['phone'];
        $emails = $response[$index]['email'];
        $response[$index]['phone'] = addContactsValuesFromShopClients($phones, $userId, 'PHONE', $shopContactsData);
        $response[$index]['email'] = addContactsValuesFromShopClients($emails, $userId, 'EMAIL', $shopContactsData);
    }

    $utils = new Utils();
    if (SITE_CHARSET !== 'UTF-8') {
        $response = $utils->convertToUTF8(SITE_CHARSET, $response);
    }
    if ($response === null || $response === false) {
        $response = array('status' => 'error', 'message' => 'Failed to encode non-UTF8 data.');
    } else {
        $response = array(
            'clients' => $response,
            'pagination' => array(
                'total_count' => $arUsersCount['count'],
                'limit'       => $iLimit,
            ),
        );
    }

    echo $utils->jsonResponse($response);
} catch (Exception $e) {
    echo $utils->jsonResponse(array('status' => 'error', 'message' => 'Exception during process add lead'));
}
// @codingStandardsIgnoreEnd