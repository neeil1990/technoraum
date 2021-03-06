<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("altasib.geobase");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/admin_notify.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/altasib.geobase/classes/general/archiver.php");

global $DB;

$response = array();
if(!function_exists('gzopen'))
	$response["ERROR"] = 'GZIP module is not installed!';

ob_implicit_flush(true);
set_time_limit(1800);

if (@preg_match('#ru#i',$_SERVER['HTTP_ACCEPT_LANGUAGE']))
	$lang = 'ru';
if ($_REQUEST['lang'])
	$lang = $_REQUEST['lang'];
if (!in_array($lang,array('ru','en')))
	$lang = 'en';

define("LANG", $lang);
define('LOAD_HOST', 'ipgeobase.ru');
define('LOAD_PATH', '/files/db/Main/');
define('LOAD_FILE', 'geo_files.tar.gz');

$_REQUEST['timeout'] = intval($_REQUEST['timeout']);

define('TIMEOUT', ($_REQUEST['timeout'] > 120 ? 120 : $_REQUEST['timeout']));

$strRequestedUrl = 'http://'.LOAD_HOST.LOAD_PATH.LOAD_FILE;
$sUploadDir = $_SERVER["DOCUMENT_ROOT"] . "/upload/altasib/geobase/";
$strFilename = $sUploadDir . basename($strRequestedUrl);

$this_script_name = basename(__FILE__);

umask(0);
if (!defined("AS_DIR_PERMISSIONS"))
	define("AS_DIR_PERMISSIONS", 0777);

if (!defined("AS_FILE_PERMISSIONS"))
	define("AS_FILE_PERMISSIONS", 0777);

### MESSAGES ###
$MESS = array();
### MESSAGES ###

$strAction = $_REQUEST["action"];

if ($strAction == "UPDATE"){
	if (CAltasibGeoBase::GetIsUpdateDataFile(LOAD_HOST, LOAD_PATH, LOAD_FILE, $strFilename, false)){
		CAdminNotify::Add(
			array(
				"MESSAGE"		=> GetMessage("ALTASIB_GEOBASE_THERE_IS"),
				"TAG"			=> "GEOBASE_DB_UPDATE_".date('d.m.Y'),
				"MODULE_ID"		=> "altasib.geobase",
				"ENABLE_CLOSE"	=> "Y"
			)
		);
		$response = array(
			"UPDATE"	=> "Y"
		);
	}else{
		$response["UPDATE"]	= "N";
		if(file_exists($strFilename))
			$response["MTIME"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), filemtime($strFilename));
	}
}
elseif ($strAction == "LOAD"){
	/*******/
	$iTimeOut = TIMEOUT;
	$strUserAgent = "AltasibGeoIPLoader";

	$strLog = '';
	$status = '';
	$res = LoadFile($strRequestedUrl, $strFilename, $iTimeOut);
	if (!$res){
		$response["STATUS"]		= $res;
		$response["PROGRESS"]	= $status;
		$response["NEXT_STEP"]	= false;
		$response["MESSAGE"]	= nl2br($strLog);
	}
	elseif ($res == 3) { // partial downloading
		$response["STATUS"]		= $res;
		$response["PROGRESS"]	= $status;
		$response["NEXT_STEP"]	= "LOAD";
	}
	elseif ($res == 2) {
		$response["STATUS"]		= $res;
		$response["PROGRESS"]	= $status;
		$response["NEXT_STEP"]	= "UNPACK";
		$response["BY_STEP"]	= "Y";
		$response["FILENAME"]	= urlencode(basename($strRequestedUrl));
	}
	/*******/
}
elseif ($strAction == "UNPACK"){
	SetCurrentStatus(LoaderGetMessage("LOADER_UNPACK_ACTION"));
	$oArchiver = new CAltasibGeobaseArchiver($sUploadDir . $_REQUEST["filename"], true);
	$tRes = $oArchiver->extractFiles($sUploadDir);
	SetCurrentProgress($oArchiver->iCurPos, $oArchiver->iArchSize);
	if($tRes) {
		if (!$oArchiver->bFinish){
			$response["STATUS"]		= !$oArchiver->bFinish ? 2 : 1;
			$response["PROGRESS"]	= $status;
			$response["NEXT_STEP"]	= "UNPACK";
			$response["SEEK"]		= $oArchiver->iCurPos;
		} else {
			$response["STATUS"]		= !$oArchiver->bFinish ? 2 : 1;
			$response["PROGRESS"]	= $status;
			$response["NEXT_STEP"]	= "DBUPDATE";
			$response["FILENAME"]	= urlencode(basename('cidr_optim'));
			$response["DROP_T"]		= "Y";

			@unlink($sUploadDir . $_REQUEST["filename"].'.log');
			@unlink($sUploadDir . $_REQUEST["filename"].'.tmp');

			SetCurrentStatus(LoaderGetMessage("LOADER_UNPACK_DELETE"));
		}
	} else {
		SetCurrentStatus(LoaderGetMessage("LOADER_UNPACK_ERRORS"));
		$arErrors = &$oArchiver->GetErrors();
		if (count($arErrors)>0){
			if ($ft = fopen($sUploadDir . $this_script_name.".log", "wb")){
				foreach ($arErrors as $value){
					$str = "[".$value[0]."] ".$value[1]."\n";
					fwrite($ft, $str);
					$response["ERROR"] .= $str.'; ';
					$txt .= $str . '<br>';
				}
				fclose($ft);
			}
		}
	}
	/*******/
}
elseif ($strAction == "DBUPDATE"){
	$iTimeOut = TIMEOUT;
	if ($iTimeOut > 0)
		$start_time = altasib_geobase_getmicrotime ();

	if ($_REQUEST["drop_t"] == 'Y'){
		if ($DB->TableExists('altasib_geobase_codeip'))
			$DB->Query("DROP TABLE `altasib_geobase_codeip`");
		if ($DB->TableExists('altasib_geobase_cities'))
			$DB->Query("DROP TABLE `altasib_geobase_cities`");
	}
	switch($_REQUEST["filename"]){
		case "cidr_optim": {
			$FPath = '/upload/altasib/geobase/cidr_optim.txt';
			$fileSize = filesize($_SERVER["DOCUMENT_ROOT"].$FPath);
			$f = fopen($_SERVER["DOCUMENT_ROOT"].$FPath, 'r');
			$_REQUEST["seek"] ? fseek($f, $_REQUEST["seek"]) : false;
			if(!$DB->TableExists('altasib_geobase_codeip'))
			{
				$altasib_geobase_codeip = "CREATE TABLE `altasib_geobase_codeip`(
						`ID` int( 11 ) NOT NULL AUTO_INCREMENT,
						`BLOCK_BEGIN`	bigint( 14 )	NOT NULL,
						`BLOCK_END`		bigint( 14 )	NOT NULL,
						`BLOCK_ADDR`	varchar( 64 )	NOT NULL,
						`COUNTRY_CODE`	char( 2 )		NOT NULL,
						`CITY_ID`		int( 8 )		NOT NULL,
					PRIMARY KEY ( `ID` ) ,
					UNIQUE KEY (`BLOCK_BEGIN`),
					UNIQUE KEY (`BLOCK_END` ))
					ENGINE = InnoDB";
				$DB->Query($altasib_geobase_codeip, false, "");
			}

			if(COption::GetOptionString("altasib.geobase", "set_sql", "Y") == "Y")
				$DB->Query("SET SQL_BIG_SELECTS=1");

			$bFinished = true;
			$strFields = "`BLOCK_BEGIN`, "
						."`BLOCK_END`, "
						."`BLOCK_ADDR`, "
						."`COUNTRY_CODE`, "
						."`CITY_ID`";
			while (!feof ($f)) {
				if (TIMEOUT > 0 && (altasib_geobase_getmicrotime() - $start_time) > TIMEOUT) {
					$bFinished = False;
					break;
				}
				$strVar = fgets($f);
				if(trim($strVar) !== ''){
					$arValues = explode(',' ,preg_replace("/\t/", ',', $strVar));
					if(!empty($arValues))
					{
							$strValues .= (!!strlen($strValues) ? ', ' : '')
								.'('.$arValues[0].', '
								.$arValues[1].', '
								."'".$DB->ForSql($arValues[2])."', "
								."'".$DB->ForSql($arValues[3])."', "
								.intval($arValues[4]).')';
					}
				}
			}
			$DB->Query('INSERT INTO altasib_geobase_codeip ('.$strFields.')
				VALUES '.$strValues);

			SetCurrentProgress (ftell($f), $fileSize);
			if ($bFinished){
				$response = array(
					"STATUS"	=> 1,
					"PROGRESS"	=> 100,
					"NEXT_STEP"	=> "DBUPDATE",
					"FILENAME"	=> urlencode(basename("cities")),
					"SEEK"		=> 0,
					"DROP_T"	=> "N",
					"MES"		=> iconv("cp1251", "UTF-8", GetMessage('ALTASIB_GEOBASE_TABLE_CODEIP_UPDATED'))
				);
			}else {
				$response = array(
					"STATUS"	=> 1,
					"PROGRESS"	=> $status,
					"NEXT_STEP"	=> "DBUPDATE",
					"SEEK"		=> ftell($f),
					"SIZE"		=> $fileSize,
					"FILENAME"	=> urlencode(basename("cidr_optim")),
					"DROP_T"	=> "N"
				);
			}
			break;
		}
		case "cities": {
			$FPath = '/upload/altasib/geobase/cities.txt';
			if(ToLower(SITE_CHARSET) == "utf-8")
			{
				$strFName = $_SERVER['DOCUMENT_ROOT'].$FPath;
				file_put_contents($strFName, iconv("windows-1251", "UTF-8", file_get_contents($strFName)));
			}
			$fileSize = filesize($_SERVER["DOCUMENT_ROOT"].$FPath);
			$f = fopen($_SERVER["DOCUMENT_ROOT"].$FPath, 'r');
			$_REQUEST["seek"] ? fseek($f, $_REQUEST["seek"]) : false;
			if(!$DB->TableExists('altasib_geobase_cities')){
				$altasib_geobase_cities = "CREATE TABLE `altasib_geobase_cities` (
											`ID`			int( 6 )		NOT NULL,
											`CITY_NAME`		varchar( 128 )	NOT NULL,
											`REGION_NAME`	varchar( 255 )	NOT NULL,
											`COUNTY_NAME`	varchar( 255 )	NOT NULL,
											`BREADTH_CITY`	real			NOT NULL,
											`LONGITUDE_CITY` real			NOT NULL,
										PRIMARY KEY ( `ID` ))
										ENGINE = InnoDB";
				$DB->Query($altasib_geobase_cities, false, "");
			}
			$bFinished = true;
			$strFields =	"ID, "
							."CITY_NAME, "
							."REGION_NAME, "
							."COUNTY_NAME ,"
							."BREADTH_CITY ,"
							."LONGITUDE_CITY";
			while (!feof ($f)) {
				if (TIMEOUT > 0 && (altasib_geobase_getmicrotime() - $start_time) > TIMEOUT) {
					$bFinished = False;
					break;
				}
				$arValues = explode(',' ,preg_replace("/\t/", ',', fgets($f)));
				$strValues .= (!!strlen($strValues) ? ", " : "")
								."(".intval($arValues[0]).", "
								."'".$DB->ForSql($arValues[1]) ."', "
								."'".$DB->ForSql($arValues[2]) ."', "
								."'".$DB->ForSql($arValues[3]) ."', "
									.floatval($arValues[4]) .", "
									.floatval($arValues[5]) .")";
			}
			$DB->Query('INSERT INTO altasib_geobase_cities ('.$strFields.') VALUES '.$strValues);
			SetCurrentProgress (ftell($f), $fileSize);
			if ($bFinished){
				$response = array(
					"STATUS"	=> 0,
					"PROGRESS"	=> 100,
					"MES"		=> iconv("cp1251", "UTF-8", GetMessage('ALTASIB_GEOBASE_TABLE_CODEIP_UPDATED'))
				);
			
			}else {
				$response = array(
					"STATUS"	=> 1,
					"PROGRESS"	=> $status,
					"NEXT_STEP"	=> "DBUPDATE",
					"SEEK"		=> ftell($f),
					"SIZE"		=> $fileSize,
					"FILENAME"	=> urlencode(basename("cities")),
					"DROP_T"	=> "N"
				);
			}
			break;
		}
	}
}

### JSON ###
print json_encode_cyr($response);
############


function LoadFile ($strRequestedUrl, $strFilename, $iTimeOut){
		global $strUserAgent;
		$iTimeOut = IntVal($iTimeOut);
		if ($iTimeOut > 0)
			$start_time = altasib_geobase_getmicrotime ();
		$strRealUrl = $strRequestedUrl;
		$iStartSize = 0;

		// Initialize if spool download
		$strRealUrl_tmp = "";
		$iRealSize_tmp = 0;
		if (file_exists ($strFilename . ".tmp") && file_exists ($strFilename . ".log") && filesize ($strFilename . ".log") > 0) {
			$fh = fopen ($strFilename . ".log", "rb");
			$file_contents_tmp = fread ($fh, filesize ($strFilename . ".log"));
			fclose ($fh);

			list($strRealUrl_tmp, $iRealSize_tmp) = preg_split ("/\n/", $file_contents_tmp);
			$strRealUrl_tmp = Trim($strRealUrl_tmp);
			$iRealSize_tmp = Trim($iRealSize_tmp);
		}
		if ($iRealSize_tmp <= 0 || strlen ($strRealUrl_tmp) <= 0) {
			if (file_exists ($strFilename . ".tmp"))
				@unlink ($strFilename . ".tmp");
			if (file_exists ($strFilename . ".log"))
				@unlink ($strFilename . ".log");
		} else {
			$strRealUrl = $strRealUrl_tmp;
			$iStartSize = filesize ($strFilename . ".tmp");
		}
		// END: Initialize if spool download

		// Look for a file and requests INFO
		do {
			$lasturl	= $strRealUrl;
			$parsedUrl	= parse_url ($strRealUrl);
			$host		= $parsedUrl["host"];
			$port		= $parsedUrl["port"];
			$hostName	= $host;
			$port		= $port ? $port : "80";

			$socketHandle = fsockopen ($host, $port, $error_id, $error_msg, 30);
			if (!$socketHandle) {
				return false;
			}else{
				if (!$parsedUrl["path"]) $parsedUrl["path"] = "/";
				$request = "";
				$request .= "HEAD " . $parsedUrl["path"] . ($parsedUrl["query"] ? '?' . $parsedUrl["query"] : '') . " HTTP/1.0\r\n";
				$request .= "Host: $hostName\r\n";
				if ($strUserAgent != "") $request .= "User-Agent: $strUserAgent\r\n";
				$request .= "\r\n";
				fwrite ($socketHandle, $request);
				$replyHeader = "";
				while (($result = fgets ($socketHandle, 4024)) && $result != "\r\n") {
					$replyHeader .= $result;
				}
				fclose ($socketHandle);
				$ar_replyHeader = preg_split ("/\r\n/", $replyHeader);
				$replyCode = 0;
				$replyMsg = "";
				if (preg_match("#([A-Z]{4})/([0-9.]{3}) ([0-9]{3})#", $ar_replyHeader[0], $regs)) {
					$replyCode = IntVal ($regs[3]);
					$replyMsg = substr ($ar_replyHeader[0], strpos ($ar_replyHeader[0], $replyCode) + strlen ($replyCode) + 1, strlen ($ar_replyHeader[0]) - strpos ($ar_replyHeader[0], $replyCode) + 1);
				}
				if ($replyCode != 200 && $replyCode != 302) {
					if ($replyCode == 403) SetCurrentStatus (LoaderGetMessage ("LOADER_LOAD_SERVER_ANSWER1")); else
						SetCurrentStatus (str_replace ("#ANS#", $replyCode . " - " . $replyMsg, LoaderGetMessage ("LOADER_LOAD_SERVER_ANSWER")) . '<br>' . htmlspecialchars ($strRequestedUrl));
					return false;
				}
				$strLocationUrl = "";
				$iNewRealSize = 0;
				$strAcceptRanges = "";
				for ($i = 1; $i < count ($ar_replyHeader); $i++) {
					if (strpos ($ar_replyHeader[$i], "Location") !== false) $strLocationUrl = trim (substr ($ar_replyHeader[$i], strpos ($ar_replyHeader[$i], ":") + 1, strlen ($ar_replyHeader[$i]) - strpos ($ar_replyHeader[$i], ":") + 1)); elseif (strpos ($ar_replyHeader[$i], "Content-Length") !== false) $iNewRealSize = IntVal (Trim (substr ($ar_replyHeader[$i], strpos ($ar_replyHeader[$i], ":") + 1, strlen ($ar_replyHeader[$i]) - strpos ($ar_replyHeader[$i], ":") + 1))); elseif (strpos ($ar_replyHeader[$i], "Accept-Ranges") !== false) $strAcceptRanges = Trim (substr ($ar_replyHeader[$i], strpos ($ar_replyHeader[$i], ":") + 1, strlen ($ar_replyHeader[$i]) - strpos ($ar_replyHeader[$i], ":") + 1));
				}
				if (strlen ($strLocationUrl) > 0) {
					$redirection = $strLocationUrl;
					if ((strpos ($redirection, "http://") === false))
						$strRealUrl = dirname ($lasturl) . "/" . $redirection;
					else
						$strRealUrl = $redirection;
				}
				if (strlen ($strLocationUrl) <= 0)
					break;
			}
		} while (true);
		// END: Look for a file and requests INFO
		
		$bCanContinueDownload = ($strAcceptRanges == "bytes");

		// If it is possible to complete the download
		if ($bCanContinueDownload) {
			$fh = fopen ($strFilename . ".log", "wb");
			if (!$fh) {
				SetCurrentStatus (str_replace ("#FILE#", $strFilename . ".log", LoaderGetMessage ("LOADER_LOAD_NO_WRITE2FILE")));
				return false;
			}
			fwrite ($fh, $strRealUrl . "\n");
			fwrite ($fh, $iNewRealSize . "\n");
			fclose ($fh);
		}
		// END: If it is possible to complete the download

		// download file
		$parsedUrl = parse_url($strRealUrl);
		$host = $parsedUrl["host"];
		$port = $parsedUrl["port"];
		$hostName = $host;
		$port = $port ? $port : "80";

		SetCurrentStatus (str_replace ("#HOST#", $host, LoaderGetMessage ("LOADER_LOAD_CONN2HOST")));
		$socketHandle = fsockopen ($host, $port, $error_id, $error_msg, 30);
		if (!$socketHandle) {
			SetCurrentStatus (str_replace ("#HOST#", $host, LoaderGetMessage ("LOADER_LOAD_NO_CONN2HOST")) . " [" . $error_id . "] " . $error_msg);
			return false;
		} else {
			if (!$parsedUrl["path"]) $parsedUrl["path"] = "/";

			SetCurrentStatus (LoaderGetMessage ("LOADER_LOAD_QUERY_FILE"));

			$request = "";
			$request .= "GET " . $parsedUrl["path"] . ($parsedUrl["query"] ? '?' . $parsedUrl["query"] : '') . " HTTP/1.0\r\n";
			$request .= "Host: $hostName\r\n";

			if ($strUserAgent != "") $request .= "User-Agent: $strUserAgent\r\n";
			if ($bCanContinueDownload && $iStartSize > 0) $request .= "Range: bytes=" . $iStartSize . "-\r\n";

			$request .= "\r\n";

			fwrite ($socketHandle, $request);

			$result = "";
			SetCurrentStatus (LoaderGetMessage ("LOADER_LOAD_WAIT"));
			$replyHeader = "";
			while (($result = fgets ($socketHandle, 4096)) && $result != "\r\n")
				$replyHeader .= $result;
			$ar_replyHeader = preg_split ("/\r\n/", $replyHeader);
			$replyCode = 0;
			$replyMsg = "";
			if (preg_match("#([A-Z]{4})/([0-9.]{3}) ([0-9]{3})#", $ar_replyHeader[0], $regs)) {
				$replyCode = IntVal ($regs[3]);
				$replyMsg = substr ($ar_replyHeader[0], strpos ($ar_replyHeader[0], $replyCode) + strlen ($replyCode) + 1, strlen ($ar_replyHeader[0]) - strpos ($ar_replyHeader[0], $replyCode) + 1);
			}
			if ($replyCode != 200 && $replyCode != 302 && $replyCode != 206) {
				SetCurrentStatus (str_replace ("#ANS#", $replyCode . " - " . $replyMsg, LoaderGetMessage ("LOADER_LOAD_SERVER_ANSWER")));
				return false;
			}
			$strContentRange = "";
			$iContentLength = 0;
			for ($i = 1; $i < count ($ar_replyHeader); $i++) {
				if (strpos ($ar_replyHeader[$i], "Content-Range") !== false) $strContentRange = trim (substr ($ar_replyHeader[$i], strpos ($ar_replyHeader[$i], ":") + 1, strlen ($ar_replyHeader[$i]) - strpos ($ar_replyHeader[$i], ":") + 1)); elseif (strpos ($ar_replyHeader[$i], "Content-Length") !== false) $iContentLength = doubleval (Trim (substr ($ar_replyHeader[$i], strpos ($ar_replyHeader[$i], ":") + 1, strlen ($ar_replyHeader[$i]) - strpos ($ar_replyHeader[$i], ":") + 1))); elseif (strpos ($ar_replyHeader[$i], "Accept-Ranges") !== false) $strAcceptRanges = Trim (substr ($ar_replyHeader[$i], strpos ($ar_replyHeader[$i], ":") + 1, strlen ($ar_replyHeader[$i]) - strpos ($ar_replyHeader[$i], ":") + 1));
			}
			$bReloadFile = True;
			if (strlen ($strContentRange) > 0) {
				if (preg_match("# *bytes +([0-9]*) *- *([0-9]*) */ *([0-9]*)#", $strContentRange, $regs)) {
				$iStartBytes_tmp = doubleval ($regs[1]);
				$iEndBytes_tmp = doubleval ($regs[2]);
				$iSizeBytes_tmp = doubleval ($regs[3]);

				if ($iStartBytes_tmp == $iStartSize && $iEndBytes_tmp == ($iNewRealSize - 1) && $iSizeBytes_tmp == $iNewRealSize) {
					$bReloadFile = False;
				}
			}
		}
		if ($bReloadFile) {
			@unlink ($strFilename . ".tmp");
			$iStartSize = 0;
		}
		if (($iContentLength + $iStartSize) != $iNewRealSize) {
			SetCurrentStatus (LoaderGetMessage ("LOADER_LOAD_ERR_SIZE"));
			return false;
		}
		$fh = fopen ($strFilename . ".tmp", "ab");
		if (!$fh) {
			SetCurrentStatus (str_replace ("#FILE#", $strFilename . ".tmp", LoaderGetMessage ("LOADER_LOAD_CANT_OPEN_WRITE")));
			return false;
		}
		$bFinished = True;
		$downloadsize = (double)$iStartSize;
		SetCurrentStatus (LoaderGetMessage ("LOADER_LOAD_LOADING"));
		while (!feof ($socketHandle)) {
			if ($iTimeOut > 0 && (altasib_geobase_getmicrotime() - $start_time) > $iTimeOut) {
				$bFinished = False;
				break;
			}
			$result = fread ($socketHandle, 256 * 1024);
			$downloadsize += strlen ($result);
			if ($result == "") break;
			fwrite ($fh, $result);
		}
		SetCurrentProgress ($downloadsize, $iNewRealSize);
		fclose ($fh);
		fclose ($socketHandle);
		if ($bFinished) {
			@unlink ($strFilename);
			if (!@rename ($strFilename . ".tmp", $strFilename)) {
				SetCurrentStatus (str_replace ("#FILE2#", $strFilename, str_replace ("#FILE1#", $strFilename . ".tmp", LoaderGetMessage ("LOADER_LOAD_ERR_RENAME"))));
				return false;
			}
			@unlink ($strFilename . ".tmp");
		} else
			return 3;

		SetCurrentStatus (str_replace ("#SIZE#", $downloadsize, str_replace ("#FILE#", $strFilename, LoaderGetMessage ("LOADER_LOAD_FILE_SAVED"))));
		@unlink ($strFilename . ".log");
		return 2;
	}
	// END: download file
}
function LoaderGetMessage($name) {
	global $MESS;
	return $MESS[$name];
}
function SetCurrentStatus($str) {
	global $strLog;
	$strLog .= $str."\n";
}
function SetCurrentProgress($cur, $total = 0) {
	global $status;
	if (!$total){
		$total	= 100;
		$cur	= 0;
	}
	$val = intval($cur/$total*100);
	if ($val > 100){
		$val = 100;
	}

	$status = $val;
}
function altasib_geobase_getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
function json_encode_cyr($str) {
	$arr_replace_utf = array('null', '\u0410', '\u0430','\u0411','\u0431','\u0412','\u0432',
		'\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',
		'\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',
		'\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',
		'\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',
		'\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',
		'\u0448','\u0429','\u0449','\u042a','\u044a','\u042b','\u044b','\u042c','\u044c',
		'\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');

	$arr_replace_cyr = array('false', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�',
		'�', '�', '�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�',
		'�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�',
		'�','�','�','�','�','�','�','�','�','�','�','�','�','�');

	$str1 = json_encode($str, JSON_FORCE_OBJECT);
	$str2 = str_replace($arr_replace_utf,$arr_replace_cyr,$str1);
	return $str2;
}
?>