<?
use Bitcall\Client\Core\BitcallClientFactory;
use Bitcall\Client\Models\Requests\TextCallRequest;
if(CIWebSMS::checkPhpVer53()) {
	include 'Autoloader.php';
}
#debmes2(IWEB_APP_PATH);

// ������� ����������� ���������� ������ � ������
if (!function_exists("CIWebObjToArray"))
{
	function CIWebObjToArray(&$obj) {
		if(is_object($obj)) {
			if(count($obj)>0) {
				$obj = (array) $obj;
				array_walk_recursive($obj,'CIWebObjToArray');
			} else {
				$obj = '';
			}
		}
	}
}
/*
 * class CIWebSMS
 */
IncludeModuleLangFile(__FILE__);
class CIWebSMS  {
	
	/*
	 * __construct()
	 * @param $arg
	 */
	public $error = '';
	public $return_mess = '';
	
	function __construct() {
		
	}
	function checkPhpVer53() {
		
		if(defined('PHP_VERSION_ID') && intval(substr(PHP_VERSION_ID,0,3)) >= 503) {
			return true;
		} else {
			return false;
		}
	}
	/*
	* ����� �������� ������ �������� ��������
	*/
	public function CheckPhoneNumber($phone) {
		$result = true;
		if(!preg_match("/^[0-9]{11,14}+$/", $phone)) {
			if(isset($this)) $this->error = GetMessage("IMAGINWEB_SMS_TELEFON_ZADAN_V_NEVE").$phone;
			$result = false;
		}
		return $result;
	}
	
	public function MakePhoneNumber($phone) {
		$result = preg_match_all('/\d/',$phone,$found);
		$res = implode('',$found[0]);
		if(($found[0][0] == '7' || $found[0][0] == '8') && strlen($res) >= '11' && $found[0][1] != 0) {
			$phone = '7'.substr($res,1,10);
		} elseif(($found[0][0].$found[0][1] == '80') && strlen($res) >= '11') {
			$phone = '38'.substr($res,1,10);
		} elseif(($found[0][0].$found[0][1].$found[0][2] == '380') && strlen($res) >= '12') {
			$phone = '380'.substr($res,3,9);
		}  elseif(($found[0][0].$found[0][1].$found[0][2] == '375') && strlen($res) >= '12') {
			$phone = '375'.substr($res,3,9);
		} elseif(strlen($res) == '10' && $res{0} == 0) {
			$phone = '38'.$res;
		} elseif(strlen($res) == '9') {
			$phone = '375'.$res;
		} elseif(strlen($res) == '10') {
			$phone = '7'.$res;
		} elseif(strlen($res) == '14') {
			$phone = $res;
		} else {
			$phone = '';
		}
		return $phone;
	}
	
	public function CompatibilityCheck($arParams = array()) {
		
		$arResult = array('CHECK'=>false);
		
		if(isset($arParams['GATE']) && $arParams['GATE']=='turbosms.ua') {
			$arResult['CHECK'] = (class_exists('SoapClient'))?true:false;
			$arResult['MESSAGE'] = ($arParams['TEXT'])?GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php Soap!':'<span style="color: red;">'.GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php Soap!</span>';
		}
		
		if(isset($arParams['GATE']) && (
				$arParams['GATE']=='targetsms.ru'
		   	 ||	$arParams['GATE']=='axtele.com'
			 || $arParams['GATE']=='qtelecom.ru'
			 || $arParams['GATE']=='redsms.ru'
			 || $arParams['GATE']=='redsms.ru2.0'
			  || $arParams['GATE']=='redsms.ru3.0'
			 || $arParams['GATE']=='epochtasms'
			 || $arParams['GATE']=='mobilmoney.ru'
			 || $arParams['GATE']=='giper.mobi'
			 || $arParams['GATE']=='kompeito.ru'
			 || $arParams['GATE']=='mainsms.ru'
			 || $arParams['GATE']=='am4u.ru'
			 || $arParams['GATE']=='sms-sending.ru'
			 || $arParams['GATE']=='nssms.ru'
			 || $arParams['GATE']=='nssms.ru2'
			 || $arParams['GATE']=='p1sms.ru'
			 || $arParams['GATE']=='devinotele.com'
			 || $arParams['GATE']=='instam.ru'
			 || $arParams['GATE']=='smsdirect.ru'
		       || $arParams['GATE']=='intel-tele.com')
		) {
			
			$arResult['CHECK'] = (function_exists('curl_init'))?true:false;
			$arResult['MESSAGE'] = ($arParams['TEXT'])?GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php cURL!':'<span style="color: red;">'.GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php cURL!</span>';
		}
		
		if(isset($arParams['GATE']) && $arParams['GATE']=='infosmska.ru') {
			$arResult['CHECK'] = (extension_loaded('sockets'))?true:false;
			$arResult['MESSAGE'] = ($arParams['TEXT'])?GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php sockets!':'<span style="color: red;">'.GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php sockets!</span>';
		}
		//if(isset($arParams['GATE']) && $arParams['GATE']=='smsdirect.ru') {
		//	$arResult['CHECK'] = (extension_loaded('sockets'))?true:false;
		//	$arResult['MESSAGE'] = ($arParams['TEXT'])?GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php sockets!':'<span style="color: red;">'.GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php sockets!</span>';
		//}
		
		if(isset($arParams['GATE']) && $arParams['GATE']=='alfa-sms.ru') {
			$arResult['CHECK'] = (extension_loaded('sockets'))?true:false;
			$arResult['MESSAGE'] = ($arParams['TEXT'])?GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php sockets!':'<span style="color: red;">'.GetMessage("IMAGINWEB_SMS_DLA_RABOTY_NE_OBHODI").' php sockets!</span>';
		}
		
		if(isset($arParams['GATE']) && $arParams['GATE']=='easy-sms.ru') {
			$arResult['CHECK'] = (ini_get('allow_url_fopen'))?true:false;
			$arResult['MESSAGE'] = ($arParams['TEXT'])?GetMessage("IMAGINWEB_SMS_NEOBHODIMO_USTANOVIT").' php.ini allow_url_open = on':'<span style="color: red;">'.GetMessage("IMAGINWEB_SMS_NEOBHODIMO_USTANOVIT").' php.ini  allow_url_fopen = on</span>';
		}
		if(isset($arParams['GATE']) && $arParams['GATE']=='imobis') {
			$arResult['CHECK'] = (ini_get('allow_url_fopen'))?true:false;
			$arResult['MESSAGE'] = ($arParams['TEXT'])?GetMessage("IMAGINWEB_SMS_NEOBHODIMO_USTANOVIT").' php.ini allow_url_open = on':'<span style="color: red;">'.GetMessage("IMAGINWEB_SMS_NEOBHODIMO_USTANOVIT").' php.ini  allow_url_fopen = on</span>';
		}
		if(isset($arParams['GATE']) && $arParams['GATE']=='bytehand.com') {
			$arResult['CHECK'] = (ini_get('allow_url_fopen'))?true:false;
			$arResult['MESSAGE'] = ($arParams['TEXT'])?GetMessage("IMAGINWEB_SMS_NEOBHODIMO_USTANOVIT").' php.ini allow_url_open = on':'<span style="color: red;">'.GetMessage("IMAGINWEB_SMS_NEOBHODIMO_USTANOVIT").' php.ini  allow_url_fopen = on</span>';
		}
		return $arResult;
	}
	
	public function GetCreditBalance($arParams = array(), $encoding = LANG_CHARSET) {

		
		$result = "";
		if((isset($arParams['GATE']) && strlen($arParams['GATE'])<=0)
		   || (!is_array($arParams) && strlen($arParams) <= 0)
		   || (is_array($arParams) && !isset($arParams['GATE']))
		   ) $gate = COption::GetOptionString('imaginweb.sms', 'gate');
		if(!is_array($arParams) && strlen($arParams) >= 0) {
			$gate = $arParams;
			$arParams = array();
		}
		if(is_array($arParams) && isset($arParams['GATE']) && strlen($arParams['GATE']) >= 0) $gate = $arParams['GATE'];
		
		if(!is_array($arParams)) $arParams = array();
		$arParams['GATE'] = $gate;
		$arRes = CIWebSMS::CompatibilityCheck($arParams);
		
		if(!$arRes['CHECK']) return $arRes['MESSAGE'];
		
		if($gate == 'alfa-sms.ru') {
			$result = GetMessage("IMAGINWEB_SMS_BALANS_MOJNO_UZNATQ");
		}
		//debmes_tf($gate);die();
		
		if($gate == 'intel-tele.com') {
		    $auth = Array (
		        'login' => COption::GetOptionString('imaginweb.sms', 'username_intel-tele.com'),
		        'password' => COption::GetOptionString('imaginweb.sms', 'password_intel-tele.com')
		        );
		    
		    if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
		    if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
		    
		    $params = array(
		        'username' => $auth['login'],
		        'api_key' => $auth['password'],
		    );
		    $url = 'http://api.sms.intel-tele.com/balance/?'.http_build_query($params);
		    $responce = file_get_contents($url);
		    $arBalance = json_decode($responce, true);
		    $bal = isset($arBalance['balance']) ? $arBalance['balance'] : '';
		    if (strlen($bal) > 0)
		        $bal = number_format($bal, 3);
		    
		    // credit
		    $url = 'http://api.sms.intel-tele.com/credit/?'.http_build_query($params);
		    $responce = file_get_contents($url);
		    $arCredit = json_decode($responce, true);
		    $credit = isset($arCredit['credit']) ? $arCredit['credit'] : '';
		    
		    if (strlen($credit) > 0)
		        $bal .= " (кредит: ".number_format($credit, 3).")";
		    
		    return $bal;
		}
		
		if($gate == 'targetsms.ru') {
			
			$params = array(
				'security' => array('login' => COption::GetOptionString('imaginweb.sms', 'login_targetsms'), 'password' => COption::GetOptionString('imaginweb.sms', 'pass_targetsms')),
				'type' => 'balance'
			);
			
			if (isset($arParams['LOGIN']) && is_array($arParams)) $params['security']['login'] = $arParams['LOGIN'];
			if (isset($arParams['PASSWORD']) && is_array($arParams)) $params['security']['password'] = $arParams['PASSWORD'];
			
			$param_json = json_encode($params, true);
			$href = 'https://sms.targetsms.ru/sendsmsjson.php';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','charset=utf- 8','Expect:'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param_json);
			curl_setopt($ch, CURLOPT_TIMEOUT, 600);
			curl_setopt($ch, CURLOPT_URL, $href);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$res = curl_exec($ch);
			$result = json_decode($res, true);
			curl_close($ch);
			
			if (strlen($result['error']) > 0)
				return $result['error'];
			else
				return $result['money']['value'];
		}
		
		if($gate == 'easy-sms.ru') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_easy-sms.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_easy-sms.ru')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$query = "https://xml.smstec.ru/requests/check_balance.php?login=".$auth['login']."&password=".$auth['password'];
			$result = file_get_contents($query);
		}
		
		if($gate == 'turbosms.ua') {
			$client = new SoapClient(COption::GetOptionString('imaginweb.sms', 'host2'));
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username2'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password2')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];

			// �������������� �� �������
			$client->Auth($auth);
			// �������� ���������� ��������� ��������
			$resultTMP = $client->GetCreditBalance();
			$result = $resultTMP->GetCreditBalanceResult;
		}
		if($gate == 'infosmska.ru') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username4'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password4')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$host = "api.infosmska.ru";
			$fp = fsockopen($host, 80);
			$response = '';
			fwrite($fp, "GET /interfaces/getbalance.ashx" .
			"?login=" . rawurlencode($auth['login']) .
			"&pwd=" . rawurlencode($auth['password']) .
			" HTTP/1.1\r\nHost: $host\r\nConnection: Close\r\n\r\n");
			fwrite($fp, "Host: " . $host . "\r\n");
			fwrite($fp, "\n");
			while(!feof($fp)) {
				$response .= fread($fp, 1);
			}
			list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
			fclose($fp);
			$responseBody = iconv('utf-8', $encoding, $responseBody);
			return $responseBody;
		}
		if($gate == 'kompeito.ru') {

			require_once("kompeitosms.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username9'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password9')
			);
			$FROM = COption::GetOptionString('imaginweb.sms', 'originator9');
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$smser = new KompeitoSms($auth['login'], $auth['password'], $FROM);
			
			$bal = $smser->getBalance();
			
			if (array_key_exists('error', $bal)) {
				#$bal = "�� ������� �������� ������. ������ Http: ".$bal['error']."\n";
				return GetMessage("IMAGINWEB_SMS_NE_UDALOSQ_POLUCITQ")." Http: ".$bal['error']."\n";
			}
			
			#debmes2($bal,array_key_exists('error', $bal));
			return $bal['money']." ".GetMessage("IMAGINWEB_SMS_RUB").$bal['credits']. " ".GetMessage("IMAGINWEB_SMS_KREDITOV");;
		}
		if($gate == 'mainsms.ru') {

			require_once("mainsms.class.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username10'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password10')
			);
			$FROM = COption::GetOptionString('imaginweb.sms', 'originator10');
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			$api = new MainSMS($auth['login'] , $auth['password'], false, false);
			#debmes2()
			$bal = $api->getBalance();
			if(!$bal) {
				return GetMessage("IMAGINWEB_SMS_OSIBKA_PRI_ZAPROSE_B");
			}
			return $bal;
		}
		#sms-sending.ru alfa-sms.ru
		if($gate == 'sms-sending.ru') {
			require_once("sms-sending_transport.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_sms-sending.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_sms-sending.ru')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			

			
			$api = new smsSendingTransport($auth['login'],$auth['password']);
		
			$bal = $api->balance();
			if(!$bal) {
				return GetMessage("IMAGINWEB_SMS_OSIBKA_PRI_ZAPROSE_B");
			}
			return $bal;
		}
		
		if($gate == 'am4u.ru') {
			require_once("am4u_transport.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username11'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password11')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			
			#define("IWEB_AM4U_HTTPS_LOGIN", $auth['login']); //��� ����� ��� HTTPS-���������
			#define("IWEB_AM4U_HTTPS_PASSWORD", $auth['password']); //��� ������ ��� HTTPS-���������

			
			$api = new am4uTransport($auth['login'],$auth['password']);
		
			$bal = $api->balance();
			if(!$bal) {
				return GetMessage("IMAGINWEB_SMS_OSIBKA_PRI_ZAPROSE_B");
			}
			return $bal;
		}
		
		if($gate == 'qtelecom.ru') {
			require_once("QTSMS.class.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_qtelecom.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_qtelecom.ru')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$sms= new QTSMS($auth['login'],$auth['password']);
			$r_xml=$sms->get_balance();
			$r_xml = simplexml_load_string($r_xml);
			CIWebObjToArray($r_xml);
			
			if(isset($r_xml['balance']['AGT_BALANCE']))
				return $r_xml['balance']['AGT_BALANCE'];
			else
				return GetMessage("IMAGINWEB_SMS_OSIBKA_PRI_ZAPROSE_B");
		}
		//debmes2($gate);die();
		if($gate == 'redsms.ru3.0') {
			
			require_once("RedsmsApiSimple.php");
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_redsms.ru3.0'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_redsms.ru3.0')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$smsApi = new \Redsms\RedsmsApiSimple($auth['login'],$auth['password']);
			//try {
				$info = $smsApi->clientInfo();
				//debmes2($info);
			//}catch (\Exception $e) {
			//    echo "error code: ".$e->getCode()."\n";
			//    echo "error message: ".$e->getMessage()."\n";
			//}
			$bal = $info['info']['balance'];
			return $bal;
		}
		if($gate == 'redsms.ru') {
			require_once("redsms_smsClient.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_redsms.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_redsms.ru')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			#$user = COption::GetOptionString('imaginweb.sms', 'username5'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
			#$pass = COption::GetOptionString('imaginweb.sms', 'password5');   // ��� ������ � ������
			
			$client = new SMSClient($auth['login'],$auth['password']);
			$sessionID = $client->getSessionID();
			$bal = $client->getBalance();
			
			return $bal;
		}
		
		if($gate == 'redsms.ru2.0') {
			require_once("redsms2.0.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_redsms.ru2.0'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_redsms.ru2.0')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$api_key = $auth['password'];
			$time = intval(file_get_contents('https://lk.redsms.ru/get/timestamp.php'));
			$params = array(
				'login'     => $auth['login'],
				'timestamp' => $time,
			);
			$signature = redsms::Signature($params, $api_key);
			$url = 'https://lk.redsms.ru/get/balance.php?'.http_build_query($params).'&signature='.$signature;
			$responce = file_get_contents($url);
			$arBalance = json_decode($responce, true);
			$bal = isset($arBalance['money']) ? $arBalance['money'] : '';
			
			return $bal;
		}
		
		if($gate == 'axtele.com') {
			require_once("DEVINOSMS.Class.v2.1.php");
			$devino = new DEVINOSMS(); // �������� ������� ���� DEVINOSMS(��������� ��� �������� ���)
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username5'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password5')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			
			#$user = COption::GetOptionString('imaginweb.sms', 'username5'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
			#$pass = COption::GetOptionString('imaginweb.sms', 'password5');   // ��� ������ � ������
			
			$result = $devino->GetSessionID($auth['login'],$auth['password']);
			
			$bal = $devino->GetBalance($result['SessionID']);

			return $bal['GetBalanceResult'];
		}
		
		if($gate == 'p1sms.ru') {	
		    $apikey = COption::GetOptionString('imaginweb.sms', 'apikey_p1sms.ru');
			/*$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_p1sms.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_p1sms.ru')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];*/
			
			$res = '';
			/*$src = '
				<?xml  version="1.0" encoding="utf-8" ?>
				<request>
				<security>
				<login value="'.$auth['login'].'" />
				<password value="'.$auth['password'].'" />
				</security>
				</request>
			';*/
			
			$ch = curl_init();
			/*curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CRLF, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $src);
			curl_setopt($ch, CURLOPT_URL, 'http://95.213.129.83/xml/balance.php');*/
			
			curl_setopt($ch, CURLOPT_URL, 'https://admin.p1sms.ru/apiUsers/getUserBalanceInfo?apiKey='.$apikey);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			
			if (false === ($Result = curl_exec($ch))) {
				#throw new Exception('Http request failed');
			} else {
				/*$Xml = simplexml_load_string($Result);
				CIWebObjToArray($Xml);
				$result = $Xml['money'];*/
		
			    $response = json_decode($Result, true);
			    $result = $response["data"];
			}
			
			curl_close($ch);
		}
		
		if($gate == 'devinotele.com') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_devinotele.com'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_devinotele.com')
			);
			include_once("smsClient.php");
			$client = new SMSClient($auth['login'],$auth['password']);
			$sessionID = $client->getSessionID();
			$result = $client->getBalance();
		}
		
		if($gate == 'instam.ru')
		{
			$auth = array(
				'login' => COption::GetOptionString('imaginweb.sms', 'username_instam.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_instam.ru')
			);
			$uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
			
			$data = '<?xml version="1.0" encoding="utf-8" ?>
			<package login="'.$auth['login'].'" password="'.$auth['password'].'">
			  <status>
				<balance/>
			  </status>
			</package>';
			$url = 'https://my.instam.ru/integration/Balance/Index';
			$curl_options = array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HEADER => 0,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_USERAGENT => $uagent,
			);
			$ch = curl_init();
			curl_setopt_array($ch, $curl_options);
			$content = curl_exec($ch);
			curl_close($ch);
			
			if(false === $content)
			{
				#throw new Exception('Http request failed');
			}
			else
			{
				$Xml = simplexml_load_string($content);
				CIWebObjToArray($Xml);
				$result = $Xml['status']['balance'];
			}
		}
		
		if($gate == 'epochtasms') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username3'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password3')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$src = '<?xml version="1.0" encoding="UTF-8"?>
			<SMS>
				<operations>
					<operation>BALANCE</operation>
				</operations>
				<authentification>
					<username>'.$auth['login'].'</username>
					<password>'.$auth['password'].'</password>
				</authentification>
			</SMS>';
			$Curl = curl_init();
			$CurlOptions = array(
				CURLOPT_URL=>COption::GetOptionString('imaginweb.sms', 'host3'),
				CURLOPT_FOLLOWLOCATION=>false,
				CURLOPT_POST=>true,
				CURLOPT_HEADER=>false,
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_CONNECTTIMEOUT=>15,
				CURLOPT_TIMEOUT=>100,
				CURLOPT_POSTFIELDS=>array('XML'=>$src),
			);
			curl_setopt_array($Curl, $CurlOptions);
			if(false === ($Result = curl_exec($Curl))) {
				#throw new Exception('Http request failed');
			} else {
				$Xml = simplexml_load_string($Result);
				CIWebObjToArray($Xml);
				if($Xml['status'] == 0) {
					$test = ($Xml['trialsms'])?' ('.$Xml['trialsms'].' SMS '.GetMessage("IMAGINWEB_SMS_DLA_TESTA"):'';
					$result = $Xml['credits'].$test;
				}
			}
			curl_close($Curl);
		}
		if($gate == 'imobis') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username6'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password6')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$query = "http://gate.sms-manager.ru/_balance.php?user=".$auth['login']."&password=".$auth['password'];
			
			$result = file_get_contents($query);
			
			
		}
		if($gate == 'smsdirect.ru') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_smsdirect.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_smsdirect.ru')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = urlencode($arParams['LOGIN']);
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = urlencode($arParams['PASSWORD']);
			$auth['login'] = urlencode($auth['login']);
			$query = "https://www.smsdirect.ru/get_user_info?login=".$auth['login']."&pass=".$auth['password']."&mode=0";
			//#$query = "https://www.smsdirect.ru/submit_message?login=".$auth['login']."&pass=".$auth['password']."&from=SMSDirect&to=79263224259&text=smsdirect_submit_sm";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_URL, $query);
			#curl_setopt($ch, CURLOPT_REFERER, $query);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$result = curl_exec($ch);
			curl_close($ch);
			
			//#$query = "http://gate.sms-manager.ru/_balance.php?user=".$auth['login']."&password=".$auth['password'];
			//debmes2(htmlspecialchars($result),$query);
			//#$result = file_get_contents($query);
			
			
		}
		
		if($gate == 'bytehand.com') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username8'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password8')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$query = "http://bytehand.com:3800/balance?id=".$auth['login']."&key=".$auth['password'];
			$result = @file_get_contents($query);
			
			$obResult = json_decode($result);
			if(isset($obResult->description))
				$result = $obResult->description;
			else
				$result = GetMessage("IMAGINWEB_SMS_NEOPOZNANNAA_OSIBKA");
		}
		
		if($gate == 'giper.mobi') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username7'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password7')
			);
			
			$uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
			
			$postdata = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
					<info xmlns="http://Giper.mobi/schema/Info">
						<login>'.$auth['login'].'</login>
						<pwd>'.$auth['password'].'</pwd>
					</info>
';
			
			$url = 'http://giper.mobi/api/info';
			$ch = curl_init( $url );
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch, CURLOPT_COOKIEJAR, "c://coo.txt");
			curl_setopt($ch, CURLOPT_COOKIEFILE,"c://coo.txt");
			
			
			$content = curl_exec( $ch );
			$err     = curl_errno( $ch );
			$errmsg  = curl_error( $ch );
			$header  = curl_getinfo( $ch );
			curl_close( $ch );
			
			if(false === $content) {
				#throw new Exception('Http request failed');
			} else {
				$Xml = simplexml_load_string($content);
				CIWebObjToArray($Xml);
				$result = $Xml['account'];
			}
		}
		
		if($gate == 'nssms.ru') {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_nssms.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_nssms.ru')
			);
			include_once("smsClient.php");
			$client = new SMSClient($auth['login'],$auth['password']);
			$sessionID = $client->getSessionID();
			$result = $client->getBalance();
			/*
			$uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

			$postdata = '<?xml version="1.0" encoding="UTF-8"?>
			<request method="check_balance">
				<login>'.$auth['login'].'</login>
				<password>'.$auth['password'].'</password>
			</request>';
			$postdata = 'xml='.$postdata;

			#debmes2(htmlspecialchars($postdata));
			$url = 'http://nssms.ru/gateway/';
			$ch = curl_init( $url );
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			#curl_setopt($ch, CURLOPT_COOKIEJAR, "c://coo.txt");
			#curl_setopt($ch, CURLOPT_COOKIEFILE,"c://coo.txt");
			
			
			$content = curl_exec( $ch );
			$err     = curl_errno( $ch );
			$errmsg  = curl_error( $ch );
			$header  = curl_getinfo( $ch );
			curl_close( $ch );
			#debmes2($content);
			if(false === $content) {
				#throw new Exception('Http request failed');
			} else {
				$Xml = simplexml_load_string($content);
				CIWebObjToArray($Xml);
				
				$result = $Xml['money'];
			}
			*/
		}
		
		
		if($gate == 'nssms.ru2')
		{
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_nssms.ru2'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_nssms.ru2')
			);
			$uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
			
			$data = '<?xml version="1.0" encoding="UTF-8"?>
			<request method="check_balance">
				<login>'.$auth['login'].'</login>
				<password>'.$auth['password'].'</password>
			</request>';
			$data = 'xml='.$data;
			
			$url = 'http://nssms.ru/gateway/';
			$curl_options = array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => 0,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_USERAGENT => $uagent,
			);
			$ch = curl_init();
			curl_setopt_array($ch, $curl_options);
			$content = curl_exec($ch);
			$err = curl_errno($ch);
			$errmsg = curl_error($ch);
			$header = curl_getinfo($ch);
			curl_close($ch);
			
			if(false === $content)
			{
				#throw new Exception('Http request failed');
			}
			else
			{
				$Xml = simplexml_load_string($content);
				CIWebObjToArray($Xml);
				$result = $Xml['money'];
			}
		}
		
		return strip_tags(htmlspecialchars_decode($result));
	}
	public function sendGetRedsms($url, $data = []) {
		
			require_once("RedsmsApiSimple.php");

			
			$user = COption::GetOptionString('imaginweb.sms', 'username_redsms.ru3.0'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
			$pass = COption::GetOptionString('imaginweb.sms', 'password_redsms.ru3.0');   // ��� ������ � ������
			
			$sendType = COption::GetOptionString('imaginweb.sms', 'resend_type_redsms_3');
			
			//debmes2($sendType);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $user = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $pass = $arParams['PASSWORD'];
			
			
			$smsApi = new \Redsms\RedsmsApiSimple($user,$pass);
			try {
				
				
				$sendResult = $smsApi->sendGetAll($url, $data);
			} catch (\Exception $e) {
				echo "error code: ".$e->getCode()."\n";
				echo "error message: ".$e->getMessage()."\n";
			    }
			
			return $sendResult;
		
	}
	
	public function SendCall($phone, $message, $arParams = array(), $encoding = LANG_CHARSET)
	{
		if(strlen(trim($message))<=0) return false;
		
		$phone = CIWebSMS::MakePhoneNumber(trim($phone));
		
		
		if(!CIWebSMS::checkPhpVer53()) return false;
		
		$SITE_ID = (isset($arParams['SITE_ID']))?$arParams['SITE_ID']:SITE_ID;
		$originator = trim(COption::GetOptionString('imaginweb.sms', 'call_sender'.$SITE_ID));
		
		if(is_array($arParams) && isset($arParams['ORIGINATOR']) && strlen($arParams['ORIGINATOR']) >= 0) $originator = $arParams['ORIGINATOR'];
		
		$key = COption::GetOptionString('imaginweb.sms', 'call_key');
		if(CIWebSMS::CheckPhoneNumber($phone) && $key) {
			$message = iconv($encoding, 'utf-8', $message);
			//debmes2($message);
			//debmes2($phone,$originator);
			//debmes2($key);
			
			////������� �����������
			//$callerPhone = '791********';
			////������� ��������
			//$phone = "792********";
			////��������� ����
			//$key = '****';
			//�������������� �������
			$clientFactory = new BitcallClientFactory();
			//������� ������
			$client = $clientFactory->getClient($key);
			//������� ������ �� ���������� ���������� ������ � �������
			$request = new TextCallRequest($message, $originator, $phone);
			//��������� ������
			$response = $client->text($request);
			//���������� �����
			//var_dump($response);

			if(isset($this)) $this->return_mess = $response;
			
			if($response)
				return true;
			else
				return false;
		}
		return false;
	}
	
	public function Send($phone, $message, $arParams = array(), $encoding = LANG_CHARSET)
	{
		if(strlen(trim($message))<=0) return false;
		$makePhp = COption::GetOptionString('imaginweb.sms', 'make_php');
		if($makePhp == 'Y') {
			eval( '$valTMP = '.$message.';');
			$message = $valTMP;
		}
		//debmes2($message);
		//debmes2($makePhp); return;
		
		$phone = CIWebSMS::MakePhoneNumber(trim($phone));
		$SITE_ID = (isset($arParams['SITE_ID']))?$arParams['SITE_ID']:SITE_ID;
		$gate = trim(COption::GetOptionString('imaginweb.sms', 'gate'.$SITE_ID));
		if(isset($arParams['GATE']) && is_array($arParams))
			$gate = $arParams['GATE'];
		/*elseif(isset($arParams['SITE_ID'])) {
			$gate = COption::GetOptionString('imaginweb.sms', 'gate'.$arParams['SITE_ID']);
		}*/ elseif($gate == '') {
			$gate = trim(COption::GetOptionString('imaginweb.sms', 'gate'));
		}
		//debmes2($arParams,$gate);
		if(!is_array($arParams)) $arParams = array();
		$arParams['GATE'] = $gate;
		
		$arRes = CIWebSMS::CompatibilityCheck($arParams);
		if(!$arRes['CHECK']) return false;
		if($arParams['GATE'] == 'OFF') return false;
		
		$allOriginator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'sender'.$SITE_ID);
		if(strlen(trim($allOriginator)) > 0) $originator = $allOriginator;
		#debmes2($allOriginator,'sender'.$SITE_ID);
		if(!is_array($arParams) && strlen($arParams) >= 0) $originator = $arParams;
		if(is_array($arParams) && isset($arParams['ORIGINATOR']) && strlen($arParams['ORIGINATOR']) >= 0) $originator = $arParams['ORIGINATOR'];
		//debmes2($arParams);
		//debmes2($arParams);
		if(CIWebSMS::CheckPhoneNumber($phone) && $gate == 'qtelecom.ru') {
			
			require_once("QTSMS.class.php");
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_qtelecom.ru');
			
			$message = iconv($encoding, 'utf-8', $message);
			$originator = iconv($encoding, 'utf-8', $originator);
			
			$user = COption::GetOptionString('imaginweb.sms', 'username_qtelecom.ru'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
			$pass = COption::GetOptionString('imaginweb.sms', 'password_qtelecom.ru');   // ��� ������ � ������
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $user = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $pass = $arParams['PASSWORD'];
			
			$sms= new QTSMS($user,$pass);
			$result=$sms->post_message($message, $phone, $originator);
			
			if(isset($this)) $this->return_mess = $result;
			
			if($result)
				return true;
			else
				return false;
		}
		
		
		if(CIWebSMS::CheckPhoneNumber($phone) && $gate == 'redsms.ru3.0') {
			
			require_once("RedsmsApiSimple.php");
			//$devino = new DEVINOSMS(); // �������� ������� ���� DEVINOSMS(��������� ��� �������� ���)
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_redsms.ru');
			
			$message = iconv($encoding, 'utf-8', $message);
			$originator = iconv($encoding, 'utf-8', $originator);
			
			$user = COption::GetOptionString('imaginweb.sms', 'username_redsms.ru3.0'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
			$pass = COption::GetOptionString('imaginweb.sms', 'password_redsms.ru3.0');   // ��� ������ � ������
			
			$sendType = COption::GetOptionString('imaginweb.sms', 'resend_type_redsms_3');
			
			$validity = COption::GetOptionString('imaginweb.sms', 'viber_validity_redsms.ru3.0');
			
			if(intval($validity) <= 0) $validity = 86400;
			//debmes2($sendType);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $user = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $pass = $arParams['PASSWORD'];
			
			
			$smsApi = new \Redsms\RedsmsApiSimple($user,$pass);
			try {
				$sendResult = $smsApi->sendSMS($phone, $message, $originator,$sendType,$validity);
			} catch (\Exception $e) {
				echo "error code: ".$e->getCode()."\n";
				echo "error message: ".$e->getMessage()."\n";
			    }
			
			if(isset($this)) $this->return_mess = $sendResult;
			
			if($sendResult)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && $gate == 'redsms.ru') {
			
			require_once("redsms_smsClient.php");
			//$devino = new DEVINOSMS(); // �������� ������� ���� DEVINOSMS(��������� ��� �������� ���)
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_redsms.ru');
			
			$message = iconv($encoding, 'utf-8', $message);
			$originator = iconv($encoding, 'utf-8', $originator);
			
			$user = COption::GetOptionString('imaginweb.sms', 'username_redsms.ru'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
			$pass = COption::GetOptionString('imaginweb.sms', 'password_redsms.ru');   // ��� ������ � ������
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $user = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $pass = $arParams['PASSWORD'];
			
			$client = new SMSClient($user,$pass);
			$sessionID = $client->getSessionID();
			$result = $client->send($originator,$phone,$message);
			
			if(isset($this)) $this->return_mess = $result;
			
			if($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && $gate == 'redsms.ru2.0') {
			
			require_once("redsms2.0.php");
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_redsms.ru2.0');
			
			$message = iconv($encoding, 'utf-8', $message);
			$originator = iconv($encoding, 'utf-8', $originator);
			
			$user = COption::GetOptionString('imaginweb.sms', 'username_redsms.ru2.0'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
			$pass = COption::GetOptionString('imaginweb.sms', 'password_redsms.ru2.0');   // ��� ������ � ������
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $user = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $pass = $arParams['PASSWORD'];
			
			$api_key = $pass;
			$time = intval(file_get_contents('https://lk.redsms.ru/get/timestamp.php'));
			$params = array(
				'login'     => $user,
				'timestamp' => $time,
				'phone' => $phone,
				'text' => $message,
				'sender' => $originator,
			);
			$signature = redsms::Signature($params, $api_key);
			$url = 'https://lk.redsms.ru/get/send.php?'.http_build_query($params).'&signature='.$signature;
			$responce = file_get_contents($url);
			$result = json_decode($responce, true);
			
			if(isset($this)) $this->return_mess = $result;
			
			if($result)
				return true;
			else
				return false;
		}
		
		if (CIWebSMS::CheckPhoneNumber($phone) && $gate == 'targetsms.ru')
		{
			$originator = (strlen(trim($originator)) > 0) ? trim($originator) : COption::GetOptionString('imaginweb.sms', 'originator_targetsms');
			
			$message = iconv($encoding, 'utf-8', $message);
			$originator = iconv($encoding, 'utf-8', $originator);
			
			$login = COption::GetOptionString('imaginweb.sms', 'login_targetsms');
			$pass = COption::GetOptionString('imaginweb.sms', 'pass_targetsms');
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $login = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $pass = $arParams['PASSWORD'];
			
			$params = array(
				'security' => array('login' => $login, 'password' => $pass),
				'type' => 'sms',
				'message' => array(
					array(
						'type' => 'sms',
						'sender' => $originator,
						'text' => $message,
						'abonent' => array(array('phone' => $phone)),
					),
				),
			);
			
			$param_json = json_encode($params, true);
			$href = 'https://sms.targetsms.ru/sendsmsjson.php';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','charset=utf- 8','Expect:'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param_json);
			curl_setopt($ch, CURLOPT_TIMEOUT, 600);
			curl_setopt($ch, CURLOPT_URL, $href);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$res = curl_exec($ch);
			$result = json_decode($res, true);
			curl_close($ch);
			
			if (strlen($result['error']) > 0)
				return false;
			else
				return true;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && $gate == 'intel-tele.com') {
		    
		    $originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_intel-tele.com');
		    
		    $message = iconv($encoding, 'utf-8', $message);
		    $originator = iconv($encoding, 'utf-8', $originator);
		    
		    $user = COption::GetOptionString('imaginweb.sms', 'username_intel-tele.com'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
		    $pass = COption::GetOptionString('imaginweb.sms', 'password_intel-tele.com');   // ��� ������ � ������
		    
		    if(isset($arParams['LOGIN']) && is_array($arParams)) $user = $arParams['LOGIN'];
		    if(isset($arParams['PASSWORD']) && is_array($arParams)) $pass = $arParams['PASSWORD'];
		    
		    $api_key = $pass;
		    
		    $params = array(
		        'username' => $user,
		        'api_key' => $api_key,
		        'from' => $originator,
		        'to' => str_replace("+", "", $phone),
		        'message' => $message,
		    );
		    
		    $url = 'http://api.sms.intel-tele.com/message/send/?'.http_build_query($params);
		    $responce = file_get_contents($url);
		    $result = json_decode($responce, true);
		    
		    if($result["reply"][0]["status"] == "OK")
		        return true;
		    else
		        return false;
		}

		if(CIWebSMS::CheckPhoneNumber($phone) && $gate == 'axtele.com') {
			
			require_once("DEVINOSMS.Class.v2.1.php");
			$devino = new DEVINOSMS(); // �������� ������� ���� DEVINOSMS(��������� ��� �������� ���)
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator5');
			
			
			
			$message = iconv($encoding, 'utf-8', $message);
			$originator = iconv($encoding, 'utf-8', $originator);
			
			$user = COption::GetOptionString('imaginweb.sms', 'username5'); // ��� ����� ��� �����������, ����� ��� �������� System_ID
			$pass = COption::GetOptionString('imaginweb.sms', 'password5');   // ��� ������ � ������
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $user = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $pass = $arParams['PASSWORD'];
			
			$result = $devino->GetSessionID($user,$pass);
			$da = array($phone);
			$countDA = count($da); //���������� �������.
			$sourceAddress = addslashes('<![CDATA['.$originator.']]>'); //��� �����������, ������������ � ����������
			$receiptRequested='true';
			foreach ($da as $s)									//������� ������� � ��� <string>
				$destinationAddresses.='<string>'.$s.'</string>';
			$data = addslashes('<![CDATA['.$message.']]>');  //����� ���, �������� ����� ����������� ��������

			$result += $devino->SendMessage($result[SessionID],$data, $destinationAddresses,$sourceAddress,$receiptRequested,$countDA); //

			$result['CommandStatus'] = iconv('windows-1251',$encoding,$result['CommandStatus']);
			
			if(isset($this)) $this->return_mess = $result;
			
			if($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && $gate == 'smsdirect.ru') {
			
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator5');
			
			
			
			$message = iconv($encoding, 'utf-8', $message);
			$originator = iconv($encoding, 'utf-8', $originator);
			
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_smsdirect.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_smsdirect.ru')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = urlencode($arParams['LOGIN']);
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = urlencode($arParams['PASSWORD']);
			$auth['login'] = urlencode($auth['login']);
			//$query = "https://www.smsdirect.ru/get_user_info?login=".$auth['login']."&pass=".$auth['password']."&mode=0";
			$query = "https://www.smsdirect.ru/submit_message?login=".$auth['login']."&pass=".$auth['password']."&from=".$originator."&to=".$phone."&text=".urlencode($message);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_URL, $query);
			#curl_setopt($ch, CURLOPT_REFERER, $query);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$result = curl_exec($ch);
			curl_close($ch);
			
			if(isset($this)) $this->return_mess = $result;
			if($result)
				return true;
			else
				return false;
			
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == '' || $gate == 'am4u.ru')) {
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator');
			
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username11'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password11')
			);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator11');
			#$FROM = COption::GetOptionString('imaginweb.sms', 'originator9');
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			
			require_once("am4u_transport.php");
			
			#define("IWEB_AM4U_HTTPS_LOGIN", $auth['login']); //��� ����� ��� HTTPS-���������
			#define("IWEB_AM4U_HTTPS_PASSWORD", $auth['password']); //��� ������ ��� HTTPS-���������
			
			$api = new am4uTransport($auth['login'],$auth['password']);
			$message = iconv($encoding, 'utf-8', $message);
			$params = array(
				"text" => $message,
				"source" => $originator
			);
			//debmes2($params);
			$result = $api->send($params,array($phone));
			
			if(isset($this)) $this->return_mess = $result;
			
			if($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == '' || $gate == 'sms-sending.ru')) {
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator');
			
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_sms-sending.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_sms-sending.ru')
			);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_sms-sending.ru');
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			
			require_once("sms-sending_transport.php");
			
			
			$api = new smsSendingTransport($auth['login'],$auth['password']);
			
			$params = array(
				"text" => $message,
				"source" => $originator
			);
			#debmes2($auth);
			#debmes2($params);
			$result = $api->send($params,array($phone));
			
			if(isset($this)) $this->return_mess = $result;
			
			if($result)
				return true;
			else
				return false;
		}
		
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'p1sms.ru')) {
			$message = iconv($encoding, 'utf-8', $message);
			//$message = str_replace(array("\n", "\r", "\n\r"), '0�0�', $message);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_p1sms.ru');
			$apikey = COption::GetOptionString('imaginweb.sms', 'apikey_p1sms.ru');
			
			// ������ �����������
			/*$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_p1sms.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_p1sms.ru')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];*/
			
			$res = '';
			/*$src = '
				<?xml  version="1.0" encoding="utf-8" ?>
				<request>
					<security>
						<login value="'.$auth['login'].'" />
						<password value="'.$auth['password'].'" />
					</security>
					<message type="sms">
						<sender>'.$originator.'</sender>
						<text>'.$message.'</text>
						<abonent phone="'.$phone.'" number_sms="1"/>
					</message>
				</request>
			';*/
			
			$arDataParams = array(
				"apiKey" => $apikey,
				"label" => "1cbitrix_imaginweb",
				"sms" => array(array(
					"channel" => "char",
					"sender" => $originator,
					"text" => $message,
					"phone" => $phone,
				)),
			);
			
			$src = json_encode($arDataParams, true);
			/*$src = '{"apiKey": "'.$apikey.'",
						"label": "1cbitrix_imaginweb",
                        "sms": [{"channel":"char",
                        "sender": "'.$originator.'",
                        "text": "'.$message.'",
                        "phone": "'.$phone.'"}]}';*/
			
			$ch = curl_init();
			/*curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CRLF, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $src);
			curl_setopt($ch, CURLOPT_URL, 'http://95.213.129.83/xml/');*/
			
			curl_setopt($ch, CURLOPT_URL, 'https://admin.p1sms.ru/apiSms/create');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $src);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			
			if(false === ($result = curl_exec($ch))) {
				#throw new Exception('Http request failed');
			} else {
				//$Xml = simplexml_load_string($Result);
				//CIWebObjToArray($Xml);
				////debmes2($Xml);
			    $result = json_decode($result, JSON_UNESCAPED_UNICODE);
			}
			
			if (isset($this)) $this->return_mess = $result["status"];
			
			if ($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'devinotele.com')) {
			$message = iconv($encoding, 'utf-8', $message);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_devinotele.com');
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_devinotele.com'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_devinotele.com')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			include_once("smsClient.php");
			$client = new SMSClient($auth['login'],$auth['password']);
			$sessionID = $client->getSessionID();
			
			try {
				$resultTMP = $client->send($originator,$phone,$message);
				if(isset($this)) $this->return_mess = $resultTMP;
				$result = true;
			} catch( SMSError_Exception $e ) {
				$result = false;
				if(isset($this)) $this->return_mess = $e;
			}
			if($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'instam.ru')) {
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_instam.ru');
			$originator = iconv($encoding, 'utf-8', $originator);
			$message = iconv($encoding, 'utf-8', $message);
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_instam.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_instam.ru')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$url = 'https://service.instam.ru/get.ashx?login='.$auth['login'].'&password='.$auth['password'].'&recipient='.$phone.'&sender='.urlencode($originator).'&text='.urlencode($message).'&type=message';
			$curl_options = array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HEADER => 0,
				CURLOPT_FOLLOWLOCATION => 1,
			);
			$ch = curl_init();
			curl_setopt_array($ch, $curl_options);
			$response = curl_exec($ch);
			curl_close($ch);
			
			if(isset($this)) $this->return_mess = $response;
			
			if($response)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'nssms.ru')) {
			$message = iconv($encoding, 'utf-8', $message);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_nssms.ru');
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_nssms.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_nssms.ru')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			include_once("smsClient.php");
			$client = new SMSClient($auth['login'],$auth['password']);
			$sessionID = $client->getSessionID();
			
			
			try {
				$resultTMP = $client->send($originator,$phone,$message);
				if(isset($this)) $this->return_mess = $resultTMP;
				$result = true;
			} catch( SMSError_Exception $e ) {
				$result = false;
				if(isset($this)) $this->return_mess = $e;
			}
			if($result)
				return true;
			else
				return false;
			/*
 
 			$postdata = '<?xml version="1.0" encoding="UTF-8"?>
<request method="Sendsms">
	<login>'.$auth['login'].'</login>
	<password>'.$auth['password'].'</password>
	<sender>'.$originator.'</sender>
	<phone_to num="0">+'.$phone.'</phone_to>
	<message>'.$message.'</message>
 </request>';
			$postdata = 'xml='.$postdata;

			#debmes2(htmlspecialchars($postdata));
			$url = 'http://nssms.ru/gateway/';
			$ch = curl_init( $url );
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			#curl_setopt($ch, CURLOPT_COOKIEJAR, "c://coo.txt");
			#curl_setopt($ch, CURLOPT_COOKIEFILE,"c://coo.txt");
			
			
			$content = curl_exec( $ch );
			$err     = curl_errno( $ch );
			$errmsg  = curl_error( $ch );
			$header  = curl_getinfo( $ch );
			curl_close( $ch );
			#debmes2($content);
			if(false === $content) {
				#throw new Exception('Http request failed');
			} else {
				#debmes2($content);
				$Xml = simplexml_load_string($content);
				CIWebObjToArray($Xml);
				$Xml[] = htmlspecialchars($postdata);
				if(isset($this)) $this->return_mess = $Xml;
			}
			
			if($Result)
				return true;
			else
				return false;
			*/
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'nssms.ru2'))
		{
			$message = iconv($encoding, 'utf-8', $message);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_nssms.ru2');
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_nssms.ru2'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_nssms.ru2')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
			
			$data = '<?xml version="1.0" encoding="UTF-8"?>
			<request method="Sendsms">
				<login>'.$auth['login'].'</login>
				<password>'.$auth['password'].'</password>
				<sender>'.$originator.'</sender>
				<phone_to num="0">+'.$phone.'</phone_to>
				<message>'.$message.'</message>
			</request>';
			$data = 'xml='.$data;
			
			$url = 'http://nssms.ru/gateway/';
			$curl_options = array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => 0,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_USERAGENT => $uagent,
			);
			$ch = curl_init();
			curl_setopt_array($ch, $curl_options);
			$content = curl_exec($ch);
			$err = curl_errno($ch);
			$errmsg = curl_error($ch);
			$header = curl_getinfo($ch);
			curl_close($ch);
			
			if(false === $content)
			{
				#throw new Exception('Http request failed');
				$result = false;
			}
			else
			{
				$Xml = simplexml_load_string($content);
				CIWebObjToArray($Xml);
				$Xml[] = htmlspecialchars($data);
				if(isset($this)) $this->return_mess = $Xml;
				$result = true;
			}
			
			if($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == '' || $gate == 'alfa-sms.ru')) {
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator');
			
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_alfa-sms.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_alfa-sms.ru')
			);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_alfa-sms.ru');
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			
			require_once("ASSMS.class.php");
			
			$sms= new ASSMS($auth['login'],$auth['password']);
			$result=$sms->post_message($message, $phone, $originator);
			#debmes2($auth);
			#debmes2($message,$phone);
			//$api = new smsSendingTransport($auth['login'],$auth['password']);
			//
			//$params = array(
			//	"text" => $message,
			//	"source" => $originator
			//);

			//$result = $api->send($params,array($phone));
			
			if(isset($this)) $this->return_mess = $result;
			
			if($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == '' || $gate == 'mainsms.ru')) {
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator');
			
			require_once("mainsms.class.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username10'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password10')
			);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator10');
			#$FROM = COption::GetOptionString('imaginweb.sms', 'originator9');
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			$message = iconv($encoding, 'utf-8', $message);
			//debmes2($arParams,$message);
			//debmes2($auth);
			$api = new MainSMS($auth['login'] , $auth['password'], false, false);
			
			
			#debmes2($message,$phone); debmes2($originator);
			$result = $api->sendSMS ( $phone , $message , $originator);
			//debmes2($this);
			if(isset($this)) $this->return_mess = $result;
			
			if($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == '' || $gate == 'kompeito.ru')) {
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator');
			
			require_once("kompeitosms.php");
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username9'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password9')
			);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator9');
			#$FROM = COption::GetOptionString('imaginweb.sms', 'originator9');
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			$message = iconv($encoding, 'utf-8', $message);
			
			$smser = new KompeitoSms($auth['login'], $auth['password'], $originator);
			
			$send_result = $smser->sendSingle($phone, $message);
			$responseBody = $send_result;
			if (array_key_exists('error', $send_result)) {
				#$responseBody = "������ �������� ���������. ������ Http: " + $send_result['error']."\n";
				$result = false;
			} else {
				$result = true;
			}
			if(isset($this)) $this->return_mess = $responseBody;
			
			if($result)
				return true;
			else
				return false;
		}
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == '' || $gate == 'infosmska.ru')) {
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username4'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password4')
			);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator4');
			
			
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			$tf = (COption::GetOptionString('imaginweb.sms', 'tf'))?'&tf=1':'';
			
			$message = iconv($encoding, 'utf-8', $message);
			$host = "api.infosmska.ru";
			$fp = fsockopen($host, 80);
			$query = "GET /interfaces/SendMessages.ashx".
			"?login=".rawurlencode($auth['login']).
			"&pwd=".rawurlencode($auth['password']).
			"&phones=".rawurlencode($phone).
			"&message=".rawurlencode($message).
			"&sender=".rawurlencode($originator).
			$tf.
			" HTTP/1.1\r\nHost: $host\r\nConnection: Close\r\n\r\n";
			fwrite($fp, $query);
			fwrite($fp, "Host: " . $host . "\r\n");
			fwrite($fp, "\n");
			while(!feof($fp)) {
				$response .= fread($fp, 1);
			}
			fclose($fp);
			
			list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
			$responseBody = iconv('utf-8', $encoding, $responseBody);
			if(isset($this)) $this->return_mess = $responseBody;
			
			if($responseBody)
				return true;
			else
				return false;
		}
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == '' || $gate == 'mobilmoney.ru')) {
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator');
			
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password')
			);
			
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$src = '<?xml version="1.0" encoding="'.$encoding.'"?>
			<request method="SendSMS">
				<login>'.$auth['login'].'</login>
				<pwd>'.$auth['password'].'</pwd>
				<originator>'.$originator.'</originator>
				<phone_to>+'.$phone.'</phone_to>
				<message>'.$message.'</message>
				<sync>0</sync>
			</request>';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: text/xml; charset='.$encoding.'"));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $src);
			curl_setopt($ch, CURLOPT_URL, COption::GetOptionString('imaginweb.sms', 'host'));
			
			$result = curl_exec($ch);
			$result = iconv('utf-8', $encoding, $responseBody);
			if(isset($this)) $this->return_mess = $result;
			
			curl_close($ch);
			
			if($result)
				return true;
			else
				return false;
		}

		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'turbosms.ua')) {
			
			$originator = (strlen($originator) <= 0)?COption::GetOptionString('imaginweb.sms', 'originator2'):$originator;
			
			
			chdir(dirname(__FILE__));
			$client = new SoapClient(COption::GetOptionString('imaginweb.sms', 'host2'));
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username2'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password2')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			// �������������� �� �������
			$client->Auth($auth);
			$message = iconv($encoding, 'utf-8', $message);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator2');
			// ������ ��� ��������
			$sms = Array(
				'sender' => $originator,
				'destination' => '+'.$phone,
				'text' => $message
			);

			// ���������� ��������� �� ���� �����. 
			// ������� ����������� ����� ��������� ���������� ����� � �����. ������������ ����� - 11 ��������.
			// ����� ����������� � ������ �������, ������� ���� � ��� ������
			$result = $client->SendSMS($sms);
			
			
			if(isset($this)) $this->return_mess = $result->SendSMSResult->ResultArray;
			
			if($result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'epochtasms')) {
			$message = iconv($encoding, 'utf-8', $message);
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator3');
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username3'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password3')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$src = '<?xml version="1.0" encoding="UTF-8"?>
			<SMS>
				<operations>
					<operation>SEND</operation>
				</operations>
				<authentification>
					<username>'.$auth['login'].'</username>
					<password>'.$auth['password'].'</password>
				</authentification>
				<message>
					<sender>'.$originator.'</sender>
					<text>'.$message.'</text>
				</message>
				<numbers>
					<number>'.$phone.'</number>
				</numbers>
			</SMS>';
			
			
			$Curl = curl_init();
			$CurlOptions = array(
				CURLOPT_URL=>COption::GetOptionString('imaginweb.sms', 'host3'),
				CURLOPT_FOLLOWLOCATION=>false,
				CURLOPT_POST=>true,
				CURLOPT_HEADER=>false,
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_CONNECTTIMEOUT=>15,
				CURLOPT_TIMEOUT=>100,
				CURLOPT_POSTFIELDS=>array('XML'=>$src),
			);
			curl_setopt_array($Curl, $CurlOptions);
			if(false === ($Result = curl_exec($Curl))) {
				throw new Exception('Http request failed');
			} else {
				$Xml = simplexml_load_string($Result);
				CIWebObjToArray($Xml);
				if(isset($this)) $this->return_mess = $Xml;
			}
			curl_close($Curl);
			
			if($Result)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'easy-sms.ru')) {
			$message = iconv($encoding, 'utf-8', $message);
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator_easy-sms.ru');
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username_easy-sms.ru'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password_easy-sms.ru')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$query = "https://xml.smstec.ru/requests/sendsms.php?login=".$auth['login']."&password=".$auth['password']."&originator=".urlencode($originator)."&text=".urlencode($message)."&phone=$phone";
			$response = file_get_contents($query);
			
			if(isset($this)) $this->return_mess = $response;
			
			if($response)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'imobis')) {
			$message = iconv($encoding, 'utf-8', $message);
			$binary = bin2hex( iconv("UTF-8", "UTF-16BE", $message) );
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator6');
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username6'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password6')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$query = "http://gate.sms-manager.ru/_getsmsd.php?user=".$auth['login']."&password=".$auth['password']."&sender=".urlencode($originator)."&SMSText=".urlencode($message)."&binary=".$binary."&GSM=$phone";
			$response = file_get_contents($query);
			
			if(isset($this)) $this->return_mess = $response;
			
			if($response)
				return true;
			else
				return false;
		}
		
		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'bytehand.com')) {
			$message = iconv($encoding, 'utf-8', $message);
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator8');
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username8'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password8')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$query = "http://bytehand.com:3800/send?id=".$auth['login']."&key=".$auth['password']."&to=".$phone."&from=".urlencode($originator)."&text=".urlencode($message);
			$response = @file_get_contents($query);
			
			if(isset($this)) $this->return_mess = $response;
			
			if($response)
				return true;
			else
				return false;
		}

		if(CIWebSMS::CheckPhoneNumber($phone) && ($gate == 'giper.mobi')) {
			$message = iconv($encoding, 'utf-8', $message);
			
			$originator = (strlen(trim($originator)) > 0)?trim($originator):COption::GetOptionString('imaginweb.sms', 'originator7');
			
			// ������ �����������
			$auth = Array (
				'login' => COption::GetOptionString('imaginweb.sms', 'username7'),
				'password' => COption::GetOptionString('imaginweb.sms', 'password7')
			);
			if(isset($arParams['LOGIN']) && is_array($arParams)) $auth['login'] = $arParams['LOGIN'];
			if(isset($arParams['PASSWORD']) && is_array($arParams)) $auth['password'] = $arParams['PASSWORD'];
			
			$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
				"<message>".
					"<login>" . $auth['login'] . "</login>".
					"<pwd>" . $auth['password'] . "</pwd>".
					"<id>" . rand(100000,999999) . "</id>".
					"<sender>" . $originator . "</sender>".
					"<text>" . $message . "</text>".
					"<phones>".
					"<phone>" . $phone . "</phone>".
					"</phones>".
				"</message>";
			
			$uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
			$url = 'http://giper.mobi/api/message';
			$ch = curl_init( $url );
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_COOKIEJAR, "c://coo.txt");
			curl_setopt($ch, CURLOPT_COOKIEFILE,"c://coo.txt");
			
			
			$content = curl_exec( $ch );
			$err     = curl_errno( $ch );
			$errmsg  = curl_error( $ch );
			$header  = curl_getinfo( $ch );
			curl_close( $ch );
			
			if(false === $content) {
				return false;
			} else {
				if(isset($this)) $this->return_mess = $content;
				return true;
			}
		}
		
		return false;
	}
}

