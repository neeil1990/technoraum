<?
class BurSnow {
	function StartSnow() {
		global $APPLICATION;
		global $module_id;
		$module_id = "burbon.snow";
		//echo '<script>alert("'.SITE_ID.'")</script>';
		//echo '<script>alert("'.COption::GetOptionString($module_id, "snowActive_".SITE_ID).'")</script>';
		if(COption::GetOptionString($module_id, "snowActive_".SITE_ID) == 'Y') {
			global $USER;
			if(!$USER->IsAuthorized() || ($USER->IsAdmin() && COption::GetOptionString($module_id, "snowAdminActive_".SITE_ID) != 'Y') || (!$USER->IsAdmin() && $USER->IsAuthorized() && COption::GetOptionString($module_id, "snowAutorizeActive_".SITE_ID) != 'Y')) {
				if((COption::GetOptionString($module_id, "snowAutorizeActive_".SITE_ID) == 'Y' && !$USER->IsAuthorized()) || (COption::GetOptionString($module_id, "snowAutorizeActive_".SITE_ID) != 'Y')) {
					if(COption::GetOptionString($module_id, "snowPath_".SITE_ID) != '') {
						$dir = opendir($_SERVER["DOCUMENT_ROOT"].COption::GetOptionString($module_id, "snowPath_".SITE_ID));
						$CDir = COption::GetOptionString($module_id, "snowPath_".SITE_ID);
					}
					elseif(COption::GetOptionString($module_id, "snowPathUser_".SITE_ID)) {
						$dir = opendir($_SERVER["DOCUMENT_ROOT"].COption::GetOptionString($module_id, "snowPathUser_".SITE_ID));
						$CDir = COption::GetOptionString($module_id, "snowPathUser_".SITE_ID);
					}
					$count = 0;
					while($file = readdir($dir)){
						if($file == '.' || $file == '..' || is_dir(COption::GetOptionString($module_id, "snowPath_".SITE_ID) . $file) || strpos($file, '.png') === false){
							continue;
						}
						$count++;
					}
					
					$JQ_LOOK = '<script>
					BX.ready(function(){
						if(!window.jQuery) {
							var script = document.createElement("script");
							script.type = "text/javascript";
							script.src = "/bitrix/js/burbon.snow/jquery-1.10.0.min.js";
							document.getElementsByTagName("head")[0].appendChild(script);
						}
						if ("jQuery" in window) {
							nowindow = 1;
						} else {
							var t = setInterval(function() {
								if ("jQuery" in window) {
									nowindow = "";
									clearInterval(t); 
								}
							}, 50);
						}
						
					});
					</script>';
					
					$SNOW_INIT = '<script type="text/javascript">
						 imageDir = "'.$CDir.'";
						 sflakesMax = 65;
						 sflakesMaxActive = "'.COption::GetOptionString($module_id, "snowCount_".SITE_ID).'";
						 svMaxX = "'.COption::GetOptionString($module_id, "snowSpeedX_".SITE_ID).'";
						 svMaxY = "'.COption::GetOptionString($module_id, "snowSpeedY_".SITE_ID).'";
						 sflakeTypes = "'.$count.'";
						 ssnowStick = "'.COption::GetOptionString($module_id, "snowStick_".SITE_ID).'";
						 ssnowRotate = "'.COption::GetOptionString($module_id, "snowRotate_".SITE_ID).'";
						 ssnowCollect = 0;
						 sfollowMouse = "'.COption::GetOptionString($module_id, "snowMouseWind_".SITE_ID).'";
						 sflakeBottom = 0;
						 susePNG = 1;
						 sflakeWidth = "'.COption::GetOptionString($module_id, "snowWidth_".SITE_ID).'";
						 sflakeHeight = "'.COption::GetOptionString($module_id, "snowHeight_".SITE_ID).'";
						 srevers = "'.COption::GetOptionString($module_id, "snowRevers_".SITE_ID).'";
					</script>';
					
					$APPLICATION->AddHeadString($JQ_LOOK,true);
					$APPLICATION->AddHeadString($SNOW_INIT,true);
					$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/js/burbon.snow/snow.js"></script>',true);
				}
			}
		}
	}
}
?>