<?
// header('Content-Type: application/json; charset=utf-8');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// $field = '';
// $phone = "77000000002";
// $name = "77000000002";

// // $name = htmlspecialchars($name, ENT_NOQUOTES, 'UTF-8');
// // $phone = htmlspecialchars($phone, ENT_NOQUOTES, 'UTF-8');
// $form = "order_site";  
//  $fieldpost = '';
// // $fieldpost = array(
// //     'email' => $email,
// //     'phone' => $phone,
// // 'price' => $PRICE,
// // 'count' => $sum,
// // 'sku' => $jsostr,
// // 'delivery_name' => $delivery_name." ".$DELIVERY_PRICE
// // ); 

// $subdomain = 'arsenal93'; //Наш аккаунт - поддомен
// $user = array(
//     'USER_LOGIN' => 'vasyukova_e_v@mail.ru', //Ваш логин (электронная почта)
//     'USER_HASH' => '28581ae7a0c87f9a9c9a3056bda76e7266603f49' //Хэш для доступа к API (смотрите в профиле пользователя)
// );

// $dealName = $phone; //Название создаваемой сделки
// $dealStatusID = '28357366';
// $pipID = '28357366'; //ID статуса сделки
// //Поля
// $phoneFieldId = '180669'; //ID поля "Телефон" в amocrm
// $emailFieldId = '180675'; //ID поля "Email" в amocrm
// $responsibleId = '1578811'; //ID Ответственного сотрудника в amocrm
// $namepdfield = '475769';
// $pricepdfiled = '475771';
// $quantpdfield = '475773';
// $linkfiled = '475551';
// $skufiled = '476505';
// $fieldsa = array();
// $fieldsa[] = array(
//         'emailFieldId'=>$emailFieldId,
//           );
// $fieldsa[] = array(
//         'phoneFieldId'=>$phoneFieldId,
//           );
// $fieldsa[] = array(
//         'pricepdfiled'=>$pricepdfiled,
//           );
// $fieldsa[] = array(
//         'quantpdfield'=>$quantpdfield,
//           );
// $fieldsa[] = array(
//         'skufiled'=>$skufiled,
//           );
// $fieldsa[] = array(
//         'namepdfield'=>$namepdfield,
//           );

// if (authorize($user, $link) > 0) {

//     $contactInfo = findContact($subdomain, $phone);
//     $idContact = $contactInfo['idContact'];
//     if ($idContact != null) {

//         // addDeal($dealName,$idContact, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$fieldsa,$fieldpost);
 
//     } else {
//         addContact($name,$responsibleId,$phoneFieldId,$phone, $subdomain);
//     }


// }


// /**Функуция авторизации скрипта на amocrm.
//  * @param $user {array} - массив с логином пользователя и hash api ключем
//  * @param $subdomain {string} - поддомен, по которому имеем доступ к amocrm
//  * @return int - Если авторизовались = 1, если нет = -1.
//  */
// function authorize($user, $subdomain)
// {
//     $link = 'https://arsenal93.amocrm.ru/private/api/auth.php?type=json';
//     $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
//     curl_setopt($curl, CURLOPT_URL, $link);
//     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
//     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user));
//     curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//     curl_setopt($curl, CURLOPT_HEADER, false);
//     curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
//     curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
//     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
//     $out = curl_exec($curl);
//     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//     curl_close($curl);
//     $code = (int)$code;
//     $errors = array(
//         301 => 'Moved permanently',
//         400 => 'Bad request',
//         401 => 'Unauthorized',
//         403 => 'Forbidden',
//         404 => 'Not found',
//         500 => 'Internal server error',
//         502 => 'Bad gateway',
//         503 => 'Service unavailable'
//     );
//     try {
//         if ($code != 200 && $code != 204)
//             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
//     } catch (Exception $E) {
//         die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
//     }
//     $Response = json_decode($out, true);
//     $Response = $Response['response'];
//     // print_r($Response);
//     if (isset($Response['auth']))
//     {
//         return 1;
//     }
//     return -1;
// }

// /**
//  * Функция поиска существующего контакта
//  * @param $subdomain {string} - поддомен для доступа к amocrm
//  * @param $email {string} - email пользователя
//  * @return array - массив с id пользователя и со списком сделок, к которым он привязан
//  */
// function findContact($subdomain, $phone)
// {
//     $link = 'https://arsenal93.amocrm.ru/api/v2/contacts/?query=' . $phone;
//     $curl = curl_init();
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
//     curl_setopt($curl, CURLOPT_URL, $link);
//     curl_setopt($curl, CURLOPT_HEADER, false);
//     curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
//     curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
//     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
//     $out = curl_exec($curl);
//     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//     curl_close($curl);
//     $code = (int)$code;
//     $errors = array(
//         301 => 'Moved permanently',
//         400 => 'Bad request',
//         401 => 'Unauthorized',
//         403 => 'Forbidden',
//         404 => 'Not found',
//         500 => 'Internal server error',
//         502 => 'Bad gateway',
//         503 => 'Service unavailable'
//     );
//     try {
//         if ($code != 200 && $code != 204) {
//             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
//         }
//     } catch (Exception $E) {
//         die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
//     }

//     $Response = json_decode($out, true);
//     echo "<pre>";
// print_r($Response);
// echo "</pre>";
//     $Response = $Response['_embedded']['items'][0];
//     $Response['idContact'] = $Response['id'];
//     if(isset($Response['leads']['id'])){
//     $Response['idLeads'] = $Response['leads']['id'];
//     }else{
//     $Response['idLeads'] = '';  
//     }
//     return $Response;
// }


// // *
// //  * Функция добавления нового контакта. Привязывается мобильный телефон и рабочий Email.
// //  * @param $name {string} - имя пользователя
// //  * @param $responsibleId {number} - ID ответственного и создателя
// //  * @param $phoneFieldId {number} - ID кастомного поля "Телефон"
// //  * @param $phone {string} - телефон пользователя
// //  * @param $emailFieldId {number} - ID кастомного поля "Email"
// //  * @param $email {string} - email пользователя
// //  * @param $subdomain {string} - поддомен для доступа к amocrm
// //  * @return int - ID добавленного пользователя
 
// function addContact($name,$responsibleId,$phoneFieldId,$phone, $subdomain)
// {
//     $contacts['add'] = array(
//         array(
//             'name' => $name,
//             'responsible_user_id' => $responsibleId,
//             'created_by' => $responsibleId,
//             'created_at' => time(),
//             'custom_fields' => array(
//                 array(
//                     'id' => $phoneFieldId,
//                     'values' => array(
//                         array(
//                             'value' => $phone,
//                             'enum' => "MOB"
//                         )
//                     )
//                 )
//             ),
//         )
//     );
//       echo "<pre>";
// print_r($contacts);
// echo "</pre>";
//     $link = '';
//     $link .= 'https://arsenal93.amocrm.ru/api/v2/contacts';

//     $curl = curl_init();
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
//     curl_setopt($curl, CURLOPT_URL, $link);
//     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
//     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
//     curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//     curl_setopt($curl, CURLOPT_HEADER, false);
//     curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
//     curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
//     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
//     $out = curl_exec($curl);
//     $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

//     $code = (int)$code;
//     $errors = array(
//         301 => 'Moved permanently',
//         400 => 'Bad request',
//         401 => 'Unauthorized',
//         403 => 'Forbidden',
//         404 => 'Not found',
//         500 => 'Internal server error',
//         502 => 'Bad gateway',
//         503 => 'Service unavailable'
//     );
//     try {
//         if ($code != 200 && $code != 204) {
//             throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
//         }
//     } catch (Exception $E) {
//         die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
//     }

//     $Response = json_decode($out, true);
//    echo "<pre>";
// print_r($Response);
// echo "</pre>";
//     $Response = $Response['_embedded']['items'][0]['id'];
 
//     // addDeal($phone,$Response, $dealStatusID, $pipID, $responsibleId, $form, $subdomain,$fieldsa,$fieldpost);
     
//     return $Response;
// }

// unset($_SESSION["responses"]);
// unset($_SESSION["arOrder"]);
// unset($_SESSION["contactss"]);
// unset($_SESSION["contactInfo"]);
// unset($_SESSION["contact"]);
// unset($_SESSION["lead"]);
// unset($_SESSION["fieldsa"]);
// unset($_SESSION["fieldposta"]);
// unset($_SESSION["arFields"]);
// unset($_SESSION["order_props"]);
// unset($_SESSION["jsons"]);
// unset($_SESSION["fieldpost"]);

// echo "<pre>";
// print_r($_SESSION["responses"]);
// $name = $_SESSION["arOrder"]["USER_NAME"]." ".$_SESSION["arOrder"]["USER_LAST_NAME"];
// print_r($name);
// echo "</pre>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
// echo "<pre>";
// print_r($_SESSION["contactInfo"]);
// echo "</pre>";
// echo "<pre>";
// print_r($_SESSION["contact"]);
// echo "</pre>";
echo "<pre>";
print_r($_SESSION["contec"]);
echo "</pre>";
echo "<pre>";
print_r($_SESSION["fieldpost"]);
echo "</pre>";
// echo "<pre>";
print_r($_SESSION["fieldpost"]['delivery_name']);
// print_r($_SESSION["fieldsa"]);
// echo "</pre>";
// echo "<pre>";
// print_r($_SESSION["fieldposta"]);
// echo "</pre>";
echo "<pre>";
print_r($_SESSION["arOrder"]);
echo "</pre>";
echo "<pre>";
print_r($_SESSION["arFields"]);
echo "</pre>";
echo "<pre>";
print_r($_SESSION["order_props"]);
echo "</pre>";
echo "<pre>";
print_r($_SESSION["addDeals"]);
echo "</pre>";
echo "<pre>";
print_r($_SESSION["statttt"]);
echo "</pre>";
?>