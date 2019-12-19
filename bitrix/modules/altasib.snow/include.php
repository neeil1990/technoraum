<?
#################################################
#   Company developer: ALTASIB                  #
#   Developer: Serge                            #
#   Site: http://www.altasib.ru                 #
#   E-mail: dev@altasib.ru                      #
#   Copyright (c) 2006-2011 ALTASIB             #
#################################################


//global $DBType;
IncludeModuleLangFile(__FILE__);

Class SNOW_alx
{
	Function SNOWOnBeforeEndBufferContent()
	{
		global $APPLICATION;

		if (IsModuleInstalled("altasib.snow"))
		{
			if(!defined(ADMIN_SECTION) && ADMIN_SECTION!==true)
			{
				$altasib_snow_link = trim(COption::GetOptionString("altasib_snow", "altasib_snow_link", ""));

				if ($altasib_snow_link != ""):

					$dir = $APPLICATION->GetCurDir();

					if (substr($dir, 0, 8) == "/bitrix/")
						return false;

					$altasib_snow_link_arr = explode(",", $altasib_snow_link);

					if(!is_array($altasib_snow_link_arr))
						return false;

					foreach($altasib_snow_link_arr as $vol):
						$vol = trim($vol);
						if ($vol == "") continue;
						if (substr($vol, 0, 1) != "/")
						$vol = "/".$vol;

						$dir_sub = substr($dir, 0, strlen($vol));

						if ($dir_sub == $vol)
						{
							SNOW_alx::addScriptOnSite();
							return true;
						}
					endforeach;
					return false;
				else:
					SNOW_alx::addScriptOnSite();
					return true;
				endif;
			}
		}
	}
	function addScriptOnSite()
	{
		global $APPLICATION;

		$_arr_speed = array(0.3, 0.6, 1, 2);
		$altasib_speed = $_arr_speed[COption::GetOptionString("altasib_snow", "altasib_speed", "1")];
		$altasib_snowletter = trim(COption::GetOptionString("altasib_snow", "altasib_snowletter", "*"));
		$altasib_snowmax = (trim(COption::GetOptionString("altasib_snow", "altasib_snowmax", "2"))+1)*10;

		$altasib_color = "";
		$altasib_color = "'".trim(COption::GetOptionString("altasib_snow", "altasib_snow_color1", "#aaaacc"))."', ";
		$altasib_color.= "'".trim(COption::GetOptionString("altasib_snow", "altasib_snow_color2", "#ddddff"))."', ";
		$altasib_color.= "'".trim(COption::GetOptionString("altasib_snow", "altasib_snow_color3", "#ccccdd"))."', ";
		$altasib_color.= "'".trim(COption::GetOptionString("altasib_snow", "altasib_snow_color4", "#f3f3f3"))."', ";
		$altasib_color.= "'".trim(COption::GetOptionString("altasib_snow", "altasib_snow_color5", "#f0ffff"))."'";

		$en = COption::GetOptionString("altasib_snow", "altasib_enable_snow", "Y");
		if($en == "Y"):
			$en_auth = COption::GetOptionString("altasib_snow", "altasib_enable_snow_auth", "Y");
			global $USER;
			if(($en_auth == "Y" && !$USER->IsAuthorized()) || $en_auth != "Y"):
				$APPLICATION->AddHeadString("<script type='text/javascript'>top.BX['alxSnow'] = true; var sinkspeed=".$altasib_speed."; var snowletter ='".$altasib_snowletter."'; var snowmax = ".$altasib_snowmax."; var snowcolor=new Array(".$altasib_color.")</script>",true);
				$APPLICATION->AddHeadScript("/bitrix/js/altasib/snow/snow.js");
			endif;
		endif;
	}
}
?>