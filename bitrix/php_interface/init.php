<?php
define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/upload/log.txt");

AddEventHandler("sale", "OnOrderNewSendEmail", "HamtimAmokitSaleMails"); 
function HamtimAmokitSaleMails($orderID, &$eventName, &$arFields) { 

$arOrder = CSaleOrder::GetByID($orderID); 
$order_props = CSaleOrderPropsValue::GetOrderProps($orderID);
// $_SESSION["arOrder"] = $arOrder;
// $_SESSION["arFields"] = $arFields;
// $_SESSION["order_props"] = $order_props;

$name = $arOrder["USER_NAME"]." ".$arOrder["USER_LAST_NAME"];

$DELIVERY_PRICE = $arFields['DELIVERY_PRICE'];

$PRICE = $arOrder['PRICE'];

$res = CSaleBasket::GetList(array(), array("ORDER_ID" => $orderID)); // ID заказа

$json=array();
while ($arItem = $res->Fetch()) {
 $prop=CIBlockElement::GetByID($arItem['PRODUCT_ID'])->GetNextElement()->GetProperties();
$sum = 0;
foreach ($prop as $valp) {
    if($valp["CODE"] == "ARTICLE"){
      $prop = $valp["VALUE"];  
    }
}
$sum = $sum + $arItem['QUANTITY'];

$json[] = $prop."(".$arItem['QUANTITY'].")";

}

$jsostr = implode(',', $json);

$type=array();
  while ($arProps = $order_props->Fetch()) { 
    $type[] = $arProps;
  }  

    foreach ($type as $value) {
    if($value["CODE"] == "EMAIL"){
        $email = $value['VALUE'];
    }
        if($value['CODE'] == "PHONE"){
        $phone = $value['VALUE'];
    }
}
$arDeliv = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]); 
$delivery_name = ""; 
if ($arDeliv) { 
    $delivery_name = $arDeliv["NAME"];
 }  

$field = '';


$name = $name;
$phone = $phone;
$form = "order_site";  
 
$fieldpost = array(
    'email' => $email,
    'phone' => $phone,
'price' => $PRICE,
'count' => $sum,
'sku' => $jsostr,
'delivery_name' => $delivery_name." ".$DELIVERY_PRICE
); 
$_SESSION["fieldpost"] = $fieldpost;
$subdomain = 'arsenal93'; //Наш аккаунт - поддомен
$user = array(
    'USER_LOGIN' => 'vasyukova_e_v@mail.ru', //Ваш логин (электронная почта)
    'USER_HASH' => '28581ae7a0c87f9a9c9a3056bda76e7266603f49' //Хэш для доступа к API (смотрите в профиле пользователя)
);

$dealName = $name; //Название создаваемой сделки
$dealStatusID = '28357366';
$pipID = '28357366'; //ID статуса сделки
//Поля
$phoneFieldId = '180669'; //ID поля "Телефон" в amocrm
$emailFieldId = '180675'; //ID поля "Email" в amocrm
$responsibleId = '1578811'; //ID Ответственного сотрудника в amocrm
$namepdfield = '476975';
$pricepdfiled = '475771';
$quantpdfield = '475773';
$linkfiled = '475551';
$skufiled = '476505';
$fieldsa = array();
$fieldsa[] = array(
        'emailFieldId'=>$emailFieldId,
          );
$fieldsa[] = array(
        'phoneFieldId'=>$phoneFieldId,
          );
$fieldsa[] = array(
        'pricepdfiled'=>$pricepdfiled,
          );
$fieldsa[] = array(
        'quantpdfield'=>$quantpdfield,
          );
$fieldsa[] = array(
        'skufiled'=>$skufiled,
          );
$fieldsa[] = array(
        'namepdfield'=>$namepdfield,
          );
$_SESSION["fieldsa"] = $fieldsa;

if (authorize($user, $link) > 0) {

    $contactInfo = findContact($subdomain, $phone);
    $idContact = $contactInfo['idContact'];
    // $_SESSION["contactInfo"] = $contactInfo;
    // $_SESSION["phoneInfo"] = $phone;
    if ($idContact != null) {
        // $_SESSION["statttt"] = "addDeal";
        addDeal($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$fieldsa,$fieldpost);
    } else {
        // $_SESSION["statttt"] = "addContact";
        addContacts($name,$responsibleId,$phoneFieldId,$phone, $subdomain);
    }
 
}

}


/**Функуция авторизации скрипта на amocrm.
 * @param $user {array} - массив с логином пользователя и hash api ключем
 * @param $subdomain {string} - поддомен, по которому имеем доступ к amocrm
 * @return int - Если авторизовались = 1, если нет = -1.
 */
function authorize($user, $subdomain)
{
    $link = 'https://arsenal93.amocrm.ru/private/api/auth.php?type=json';
    $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $code = (int)$code;
    $errors = array(
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable'
    );
    try {
        if ($code != 200 && $code != 204)
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
    } catch (Exception $E) {
        die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    }
    $Response = json_decode($out, true);
    $Response = $Response['response'];
    // print_r($Response);
    if (isset($Response['auth']))
    {
        return 1;
    }
    return -1;
}

/**
 * Функция поиска существующего контакта
 * @param $subdomain {string} - поддомен для доступа к amocrm
 * @param $email {string} - email пользователя
 * @return array - массив с id пользователя и со списком сделок, к которым он привязан
 */
function findContact($subdomain, $phone)
{
    $link = 'https://arsenal93.amocrm.ru/api/v2/contacts/?query=' . $phone;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $code = (int)$code;
    $errors = array(
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable'
    );
    try {
        if ($code != 200 && $code != 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        }
    } catch (Exception $E) {
        die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    }

    $Response = json_decode($out, true);
    // $_SESSION["contact"] = $Response;
    $Response = $Response['_embedded']['items'][0];
    $Response['idContact'] = $Response['id'];
    if(isset($Response['leads']['id'])){
    $Response['idLeads'] = $Response['leads']['id'];
    }else{
    $Response['idLeads'] = '';  
    }
    return $Response;
}


// *
//  * Функция добавления нового контакта. Привязывается мобильный телефон и рабочий Email.
//  * @param $name {string} - имя пользователя
//  * @param $responsibleId {number} - ID ответственного и создателя
//  * @param $phoneFieldId {number} - ID кастомного поля "Телефон"
//  * @param $phone {string} - телефон пользователя
//  * @param $emailFieldId {number} - ID кастомного поля "Email"
//  * @param $email {string} - email пользователя
//  * @param $subdomain {string} - поддомен для доступа к amocrm
//  * @return int - ID добавленного пользователя
 
function addContacts($name,$responsibleId,$phoneFieldId,$phone, $subdomain)
{

    $contactTags = '';

    $name = mb_convert_encoding($name, "UTF-8", "windows-1251");

    $contacts['add'] = array(
        array(
            'name' => $name,
            'responsible_user_id' => $responsibleId,
            'created_by' => $responsibleId,
            'created_at' => time(),
            'tags' => $contactTags, //Теги
            'custom_fields' => array(
                array(
                    'id' => $phoneFieldId,
                    'values' => array(
                        array(
                            'value' => $phone,
                            'enum' => "MOB"
                        )
                    )
                )
            ),
        )
    );

    // $_SESSION['contacts'] = $contacts['add'];

    $link = '';
    $link .= 'https://arsenal93.amocrm.ru/api/v2/contacts';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $code = (int)$code;

    // $_SESSION['code'] = $code;

    $errors = array(
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable'
    );
    try {
        if ($code != 200 && $code != 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        }
    } catch (Exception $E) {
        die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    }

    $Response = json_decode($out, true);
    // $_SESSION['contacts'] = $contacts['add'];
    // $_SESSION['contec'] = $Response;
    $Response = $Response['_embedded']['items'][0]['id'];

    $fieldsa = $_SESSION["fieldsa"];
    $fieldpost = $_SESSION["fieldpost"];

    $fieldsa = '';
    $fieldpost = '';
    $form = '';
    $dealStatusID = '28357366';
    $pipID = '28357366'; 
    addDeal($phone,$Response, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$fieldsa,$fieldpost);
     
    return $Response;
}

/**Функция создания новой сделки
 * @param $subdomain
 * @param $responsibleId
 * @return mixed
 */
function addDeal($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$fieldsa,$fieldpost)
{
  
  $fieldsa = $_SESSION["fieldsa"];
  $fieldpost = $_SESSION["fieldpost"];

  $dileviry = mb_convert_encoding($fieldpost['delivery_name'], "UTF-8", "windows-1251");
  $name = mb_convert_encoding($dealName, "UTF-8", "windows-1251");

    $leads['add'] = array(
        array(
            'name' => $name,
            'created_at' => time(),
            'status_id' => $dealStatusID,
            'pipeline_id' => $pipID,
            'contacts_id' => $idContact,
            'responsible_user_id' => $responsibleId,
            'tags' => "order_site",
             'custom_fields' => array(
                array(
                    'id' => $fieldsa[0]['emailFieldId'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['email']
                             )
                                 )
                    ),
                array(
                    'id' => $fieldsa[1]['phoneFieldId'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['phone']
                             )
                                 )
                    ),
                array(
                    'id' => $fieldsa[2]['pricepdfiled'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['price']
                             )
                                 )
                    ),
                array(
                    'id' => $fieldsa[3]['quantpdfield'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['count']
                             )
                                 )
                    ),
                array(
                    'id' => $fieldsa[4]['skufiled'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['sku']
                             )
                                 )
                    ),
                array(
                    'id' => $fieldsa[5]['namepdfield'],
                    'values' => array(
                        array(
                            'value' => $dileviry
                             )
                                 )
                    )
             
                                     ) 
        )
    );      

    // $_SESSION["addDeals"] =  $leads['add'];

    $link = 'https://arsenal93.amocrm.ru/api/v2/leads';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($leads));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $code = (int)$code;
    $errors = array(
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable'
    );
    try {
        if ($code != 200 && $code != 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        }
    } catch (Exception $E) {
        die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
    }
    $Response = json_decode($out, true);
    $Response = $Response['_embedded']['items'][0]['id'];
    return $Response;
}

if(CModule::IncludeModule("altasib.geobase")) {
    session_start();
    if($city = $_SESSION["ALTASIB_GEOBASE_CODE"]["CITY"]["NAME"]){
        $_SESSION['IPOLSDEK_city'] = $city;
    }else
        $_SESSION['IPOLSDEK_city'] = $_SESSION['ALTASIB_GEOBASE']['CITY_NAME'];
}
