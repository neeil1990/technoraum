<?php

header('Content-Type: application/json; charset=utf-8');
$field = '';
// $fieldpost = '';
function login($name,$phone,$form, $fieldpost){
$name = htmlspecialchars($name, ENT_NOQUOTES, 'UTF-8');
$phone = htmlspecialchars($phone, ENT_NOQUOTES, 'UTF-8');
$form = $form;  
$fieldpost = $fieldpost;
}

if($form == "form_credit"){
$fieldpost = array(
'shop' => $shop,
'price' => $price,
'namep' => $namep,
'sku' => $sku,
'location' => $location
);
$name = $firstName;
}elseif(isset($count)){
 $phone = htmlspecialchars($tel, ENT_NOQUOTES, 'UTF-8');   
 $fieldpost = array(
'price' => $price,
'count' => $count,
'namep' => $product_name,
'location' => $link
);
}elseif($form_id == "8"){ 
 $name = htmlspecialchars($name, ENT_NOQUOTES, 'UTF-8'); 
 $phone = htmlspecialchars($tel, ENT_NOQUOTES, 'UTF-8'); 
 $email = htmlspecialchars($email, ENT_NOQUOTES, 'UTF-8'); 
 $text = htmlspecialchars($msg, ENT_NOQUOTES, 'UTF-8'); 
 $name_page = htmlspecialchars($name_page, ENT_NOQUOTES, 'UTF-8');
 $form = "form_price";
$fieldpost = array(
'text' => $text,
'location' => $name_page
);
}elseif(isset($form_id)){ 
 $phone = htmlspecialchars($tel, ENT_NOQUOTES, 'UTF-8');   
 $fieldpost = array();
}else{
  $phone = htmlspecialchars($tel, ENT_NOQUOTES, 'UTF-8');   
 $fieldpost = array();   
}


// $name = htmlspecialchars('тест', ENT_NOQUOTES, 'UTF-8');
// $phone = htmlspecialchars('+79186223502', ENT_NOQUOTES, 'UTF-8');
// $form = 'form_credit';  
// $fieldpost = '347344637'; 
$subdomain = 'arsenal93'; //Наш аккаунт - поддомен
$user = array(
    'USER_LOGIN' => 'vasyukova_e_v@mail.ru', //Ваш логин (электронная почта)
    'USER_HASH' => '28581ae7a0c87f9a9c9a3056bda76e7266603f49' //Хэш для доступа к API (смотрите в профиле пользователя)
);

$dealName = $phone; //Название создаваемой сделки
$dealStatusID = '28357366';
$pipID = '28357366'; //ID статуса сделки
//Поля
$phoneFieldId = '180669'; //ID поля "Телефон" в amocrm
$emailFieldId = '180671'; //ID поля "Email" в amocrm
$responsibleId = '1578811'; //ID Ответственного сотрудника в amocrm
$namepdfield = '475769';
$pricepdfiled = '475771';
$quantpdfield = '475773';
$linkfiled = '475551';
$ship = '476975';
$skufiled = '476505';
$field = array();
if($form == "form_credit"){
$shopsfield = '462021';
$shop_value_field = array('krp'=>'926177','ural'=>'926179','kal'=>'926181','slav'=>'926183','ust'=>'926185','rostov'=>'926187');
$shop_value_site_field = array('krp'=>'г. Краснодар, Красных Партизан ул, 371','kal'=>'г. Краснодар, Калинина ул, 15/2','ural'=>'г. Краснодар, Уральская ул 87','slav'=>'г. Славянск-на-Кубани, Батарейная ул, 262','ust'=>'г. Усть-Лабинск, Красная ул, 110','rostov'=>'г. Ростов-на-Дону, проспект Шолохова, 62');
$field[] = array(
        'shopsfield'=>$shopsfield,
        'shop_value_field'=>$shop_value_field,
        'shop_value_site_field'=>$shop_value_site_field
        );
$field[] = array(
        'namepdfield'=>$namepdfield,
          );
$field[] = array(
        'pricepdfiled'=>$pricepdfiled,
          );
$field[] = array(
        'linkfiled'=>$linkfiled,
          );
$field[] = array(
        'skufiled'=>$skufiled,
          );
}elseif((int)$form_id == "8"){
$field[] = array(
        'text'=>$ship,
          );
$field[] = array(
        'namepdfield'=>$namepdfield,
          );

}elseif(isset($count)){
$field[] = array(
        'pricepdfiled'=>$pricepdfiled,
          );
$field[] = array(
        'quantpdfield'=>$quantpdfield,
          );
$field[] = array(
        'namepdfield'=>$namepdfield,
          );
$field[] = array(
        'linkfiled'=>$linkfiled,
          );
}

if (authorizes($user, $link) > 0) {

    $phone = str_replace("+7", "", $phone);
    $phone = preg_replace("/[^0-9]/", '', $phone);

    $contactInfo = findContacts($subdomain, $phone);
    $idContact = $contactInfo['idContact'];
    if ($idContact != null) {
        if($form_id == "8"){
               $form = "form_price";
               addDeals($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        }else{
            if($form == "form_credit"){
               addDeals($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
            }elseif(!empty($count)){
               addDeals($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
            }elseif(isset($form_id)){
               addDeals($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form_id, $subdomain,$field,$fieldpost);
            }elseif($form == "form_consultant"){
               addDeals($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
            }
       }
        // echo "adddeal";
    } else {
        if($form_id == "8"){
        $idc =  addContactsa($name,$responsibleId,$phoneFieldId,$phone,$emailFieldId,$email, $subdomain, $contactTags);
        $form = "form_price";

        addDeals($dealName,$idc, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        }else{
        $idc =  addContacts($name,$responsibleId,$phoneFieldId,$phone, $subdomain, $contactTags);
        
        if($form == "form_credit"){
                $form = "form_credit";
        addDeals($dealName,$idc, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        }elseif(!empty($count)){
            $form == "form_oneclcik";
        addDeals($dealName,$idc, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        }elseif($form == "form_callback"){
             $form == "form_callback";
        addDeals($dealName,$idc, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        }elseif($form = "form_consultant"){
            $form == "form_consultant";
        addDeals($dealName,$idc, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        }
        }
// echo  $form ;
    }

    $json = $form_id; 
    echo json_encode($json); 
}

/**Функуция авторизации скрипта на amocrm.
 * @param $user {array} - массив с логином пользователя и hash api ключем
 * @param $subdomain {string} - поддомен, по которому имеем доступ к amocrm
 * @return int - Если авторизовались = 1, если нет = -1.
 */
function authorizes($user, $subdomain)
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

// /**
//  * Функция поиска существующего контакта
//  * @param $subdomain {string} - поддомен для доступа к amocrm
//  * @param $email {string} - email пользователя
//  * @return array - массив с id пользователя и со списком сделок, к которым он привязан
//  */
function findContacts($subdomain, $phone)
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
    $Response = $Response['_embedded']['items'][0];
    $Response['idContact'] = $Response['id'];
    if(isset($Response['leads']['id'])){
    $Response['idLeads'] = $Response['leads']['id'];
    }else{
    $Response['idLeads'] = '';  
    }
    return $Response;
}


// /**
//  * Функция добавления нового контакта. Привязывается мобильный телефон и рабочий Email.
//  * @param $name {string} - имя пользователя
//  * @param $responsibleId {number} - ID ответственного и создателя
//  * @param $phoneFieldId {number} - ID кастомного поля "Телефон"
//  * @param $phone {string} - телефон пользователя
//  * @param $emailFieldId {number} - ID кастомного поля "Email"
//  * @param $email {string} - email пользователя
//  * @param $subdomain {string} - поддомен для доступа к amocrm
//  * @return int - ID добавленного пользователя
//  */
function addContacts($name,$responsibleId,$phoneFieldId,$phone, $subdomain, $contactTags)
{
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
                ),
            ),
        )
    );
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

//     $dealStatusID = '28357366';
// $pipID = '28357366';
// $dealName = $name;
        //     if($form = "form_credit"){
        //         $form = "form_credit";
        // addDeals($dealName,$Response, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        // }elseif($form = "form_oneclcik"){
        //     $form = "form_oneclcik";
        // addDeals($dealName,$Response, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        // }elseif($form = "form_callback"){
        //      $form = "form_callback";
        // addDeals($dealName,$Response, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        // }elseif($form = "form_consultant"){
        //     $form = "form_consultant";
        // addDeals($dealName,$Response, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost);
        // }
    return $Response;
}

function addContactsa($name,$responsibleId,$phoneFieldId,$phone,$emailFieldId,$email, $subdomain, $contactTags)
{
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
                ),
                array(
                    'id' => $emailFieldId,
                    'values' => array(
                        array(
                            'value' => $email,
                            'enum' => "WORK"
                        )
                    )
                ),
            ),
        )
    );
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

/**Функция создания новой сделки
 * @param $subdomain
 * @param $responsibleId
 * @return mixed
 */
function addDeals($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$field,$fieldpost)
{ 
    if($form == 'form_credit'){
        $keyim = '';
        foreach ($field as $filsite) {
            foreach ($filsite as $value) {
            if(is_array($value)){
             foreach ($value as $key => $val) {
               if($fieldpost['shop'] ==  $val){
                $keyim = $key;  
               }
             }
            }
          } 
        }
        if($keyim){
         $shopvalue = $field[0]['shop_value_field'][$keyim];
        }
        $shopsfield = $field[0]['shopsfield'];

    $leads['add'] = array(
        array(
            'name' => $dealName,
            'created_at' => time(),
            'status_id' => $dealStatusID,
            'pipeline_id' => $pipID,
            'contacts_id' => $idContact,
            'responsible_user_id' => $responsibleId,
            'tags' => $form,
             'custom_fields' => array(
                array(
                    'id' => $shopsfield,
                    'values' => array(
                        array(
                            'value' => $shopvalue
                             )
                                 )
                    ),
                array(
                    'id' => $field[1]['namepdfield'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['namep']
                             )
                                 )
                    ),
                array(
                    'id' => $field[2]['pricepdfiled'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['price']
                             )
                                 )
                    ),
                array(
                    'id' => $field[3]['linkfiled'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['location']
                             )
                                 )
                    ),
                array(
                    'id' => $field[4]['skufiled'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['sku']
                             )
                                 )
                    )
             
                                     ) 
        )
    );
    }elseif(!empty($fieldpost['count'])){
    
            $leads['add'] = array(
        array(
            'name' => $dealName,
            'created_at' => time(),
            'status_id' => $dealStatusID,
            'pipeline_id' => $pipID,
            'contacts_id' => $idContact,
            'responsible_user_id' => $responsibleId,
            'tags' => "shop_one_click",
             'custom_fields' => array(
                array(
                    'id' => $field[2]['namepdfield'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['namep']
                             )
                                 )
                    ),
                array(
                    'id' => $field[0]['pricepdfiled'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['price']
                             )
                                 )
                    ),
                array(
                    'id' => $field[3]['linkfiled'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['location']
                             )
                                 )
                    ),
                array(
                    'id' => $field[1]['quantpdfield'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['count']
                             )
                                 )
                    ),
             
                                     ) 
        )
    );
            // print_r($leads['add']);
    }elseif($form == "form_price"){

            $leads['add'] = array(
        array(
            'name' => $dealName,
            'created_at' => time(),
            'status_id' => $dealStatusID,
            'pipeline_id' => $pipID,
            'contacts_id' => $idContact,
            'responsible_user_id' => $responsibleId,
            'tags' => "form_price",
             'custom_fields' => array(
                array(
                    'id' => $field[0]['text'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['text']
                             )
                                 )
                    ),
                array(
                    'id' => $field[1]['namepdfield'],
                    'values' => array(
                        array(
                            'value' => $fieldpost['location']
                             )
                                 )
                    )
             
                                     ) 
        )
    );
    }elseif(isset($form)){
     $leads['add'] = array(
        array(
            'name' => $dealName,
            'created_at' => time(),
            'status_id' => $dealStatusID,
            'pipeline_id' => $pipID,
            'contacts_id' => $idContact,
            'responsible_user_id' => $responsibleId,
            'tags' => 'form_callback',
        )
    );            
    }else{
    $leads['add'] = array(
        array(
            'name' => $dealName,
            'created_at' => time(),
            'status_id' => $dealStatusID,
            'pipeline_id' => $pipID,
            'contacts_id' => $idContact,
            'responsible_user_id' => $responsibleId,
            'tags' => $form,
        )
    );
    }
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
    // print_r($code);
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
    // $json = 'ok'; 
    // $Response = json_encode($json); 
    return $Response;
}