<?
/**
 * Company developer: ALTASIB
 * Developer: adumnov
 * Site: http://www.altasib.ru
 * E-mail: dev@altasib.ru
 * @copyright (c) 2006-2016 ALTASIB
 */

Class CAltasibGeoBaseIPTools extends CAltasibGeoBaseIP
{
	function ParseXML($text)
	{
		if(strlen($text) > 0)
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");
			$objXML = new CDataXML();
			$res = $objXML->LoadString($text);
			if($res !== false)
				$arRes = $objXML->GetArray();
		}
		$arRes = current($arRes);
		$arRes = $arRes["#"];
		$arRes = current($arRes);

		$ar = Array();

		foreach($arRes as $key => $arVal)
		{
			foreach($arVal["#"] as $title => $Tval)
			{
				$ar[$key][$title] = $Tval["0"]["#"];
			}
		}
		return $ar[0];
	}

	function GetGeoData($ip)
	{
		if(defined("NO_GEOBASE") && NO_GEOBASE === true)
			return false;

		$arData = CAltasibGeoBaseIP::GetGeoDataIpgeobase_ru($ip);
		if(!$arData)
		{
			if(!$arData = CAltasibGeoBaseIP::GetGeoDataGeoip_Elib_ru($ip))
				return false;
		}
		return $arData;
	}

	function GetRowsCount($sTable = "")
	{
		global $DB;
		$arRes = array();
		$arTables = array(
			"altasib_geobase_cities",
			"altasib_geobase_kladr_cities",
			"altasib_geobase_kladr_districts",
			"altasib_geobase_kladr_region",
			"altasib_geobase_mm_city",
			"altasib_geobase_mm_country",
			"altasib_geobase_mm_region",
			"altasib_geobase_selected",
		);
		if(!empty($sTable) && in_array($sTable, $arTables) && $DB->TableExists($sTable)){
			$sRequest = 'SELECT COUNT(*) FROM '.$sTable;
			$data = $DB->Query($sRequest);
			$arData = $data->Fetch();
			$arRes[$sTable] = $arData["COUNT(*)"];
		}
		else {
			foreach($arTables as $table){
				if($DB->TableExists($table)){
					$sReq = 'SELECT COUNT(*) FROM '.$table;
					$data = $DB->Query($sReq);
					$arData = $data->Fetch();
					$arRes[$table] = $arData["COUNT(*)"];
				}
				else{
					$arRes[$table] = 0;
				}
			}
		}
		return $arRes;
	}
}
