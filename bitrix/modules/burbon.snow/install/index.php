<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-18);
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class burbon_snow extends CModule {
	var $MODULE_ID = "burbon.snow";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function burbon_snow() {
		$arModuleVersion = array();
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		$this->PARTNER_NAME = GetMessage("BURBON_SNOW_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("BURBON_SNOW_PARTNER_URI");
		$this->MODULE_NAME = GetMessage("BURBON_SNOW_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("BURBON_SNOW_MODULE_DESC");
	}
	
	function InstallFiles() {
		//CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		//CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/{$this->MODULE_ID}/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		return true;
	}
	
	function InstallDB() {
		global $APPLICATION;
		return true;
	}
	
	function InstallSettings() {
		global $APPLICATION;
		$sites_list = array();
		$sites_arr = CSite::GetList($by="def", $order="desc", array("ACTIVE"=>"Y"));
		while ($site = $sites_arr->Fetch())
		{
			$sites_list[] = array('LID'=>$site["LID"], 'NAME'=>$site["NAME"]);
		}
		$SITE_LID = $sites_list[0]['LID'];
		$SITE_NAME = $sites_list[0]['NAME'];
		$DEFSET['snowCount_'.$SITE_LID] = 65;
		$DEFSET['snowWidth_'.$SITE_LID] = 25;
		$DEFSET['snowHeight_'.$SITE_LID] = 25;
		$DEFSET['snowSpeedX_'.$SITE_LID] = 2;
		$DEFSET['snowSpeedY_'.$SITE_LID] = 2;
		$DEFSET['snowPath_'.$SITE_LID] = '/bitrix/js/burbon.snow/precipitation/snow/';
		$DEFSET['snowActive_'.$SITE_LID] = 'Y';
		$DEFSET['snowAutorizeActive_'.$SITE_LID] = '';
		$DEFSET['snowAdminActive_'.$SITE_LID] = '';
		$DEFSET['snowStick_'.$SITE_LID] = '';
		$DEFSET['snowMouseWind_'.$SITE_LID] = 'Y';
		$DEFSET['snowRotate_'.$SITE_LID] = '';
		
		foreach($DEFSET as $set_id=>$set_val) {
			COption::SetOptionString($this->MODULE_ID, $set_id, $set_val);
		}
		
		return true;
	}

	function DoInstall() {
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		RegisterModule($this->MODULE_ID);
		RegisterModuleDependences("main", "OnEpilog", $this->MODULE_ID, "BurSnow", "StartSnow");
		$this->InstallSettings();
		return true;
	}
	
	function UnInstallDB() {
		global $APPLICATION;
		if($DBErrors !== false) {
			$APPLICATION->ThrowException(implode("<br/>", $DBErrors));
		}
		return true;
	}
	
	function UnInstallFiles() {
		DeleteDirFilesEx("/bitrix/js/burbon.snow/");
		return true;
	}

	function DoUninstall() {
		UnRegisterModuleDependences("main", "OnEpilog", $this->MODULE_ID, "BurSnow", "StartSnow");
		UnRegisterModule($this->MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
		COption::RemoveOption($this->MODULE_ID);
		return true;
	}
}
?>