<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$mid = "altasib.geobase";

$incMod = CModule::IncludeModuleEx($mid);
if ($incMod == '0' || $incMod == '3')
	return false;

if($arParams["CHECK_DATA"] == "Y")
{
	if(isset($_SESSION["ALTASIB_GEOBASE_CODE"]))
	{
		return;
	}
	elseif(COption::GetOptionString($mid, "set_cookie", "Y") == "Y")
	{
		$sData = $APPLICATION->get_cookie("ALTASIB_GEOBASE_CODE");
		$arDataS = CAltasibGeoBase::deCodeJSON($sData);
		if(!empty($arDataS))
			return;
	}
}

$arResult = CAltasibGeoBase::GetDataKladr();

// ------- begin nearest block --------
if(COption::GetOptionString($mid, "show_nearest_select_city", "N") == "Y"){
	$arNData = CAltasibGeoBaseSelected::GetNearestCityFromSelected("all", false);

	if(!empty($arNData["R_ID"]) && !empty($arNData["C_NAME"])){

		$arRegion = array(
			"ID" => $arNData["R_ID"],
			"FULL_NAME" => $arNData["R_FNAME"],
			"NAME" => $arNData["R_NAME"],
			"SOCR" => $arNData["R_SOCR"],
			"CODE" => $arNData["R_ID"],
		);
		$arNFormat = CAltasibGeoBase::GetFormatKladrData($arRegion, $arNData["C_NAME"]);

		if(!empty($arNFormat)){
			$arReal = $arResult;
			$arResult = $arNFormat;
			$arResult["NEAREST_CITY_ENABLE"] = "Y";
			$arResult["NEAREST_CITY"] = $arNData;
			$arResult["REAL_CITY"] = $arReal;
		}
	}
}
// ------- end nearest block ----------


$arResult["auto"] = CAltasibGeoBase::GetAddres();

$arResult["REGION_DISABLE"] = COption::GetOptionString($mid, 'region_disable', 'N');
$arResult["POPUP_BACK"] = COption::GetOptionString($mid, "popup_back", "Y");
$arResult['MODE_LOCATION'] = strtoupper(COption::GetOptionString($mid, "mode_location", "CITIES"));

////Mobile detect////

$checkType = CAltasibGeoBase::DeviceIdentification();

/////////////////////

if ($checkType == 'mobile' || $checkType == 'pda')
{
	$this->IncludeComponentTemplate("mobile");
}
else
	$this->IncludeComponentTemplate();
?>