<?
#################################################
#   Company developer: ALTASIB                  #
#   Developer: Serge                            #
#   Site: http://www.altasib.ru                 #
#   E-mail: dev@altasib.ru                      #
#   Copyright (c) 2006-2011 ALTASIB             #
#################################################
?>
<?
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

if(!$USER->IsAdmin()) return;

$module_id = "altasib_snow";
$strWarning = "";

$arAllOptions = array(
        "main" => Array(
                Array("altasib_enable_snow", GetMessage("ALTASIB_ENABLE_SNOW")." ", "Y", array("checkbox")),
                Array("altasib_enable_snow_auth", GetMessage("ALTASIB_ENABLE_SNOW_AUTH")." ", "Y", array("checkbox")),
                Array("altasib_speed", GetMessage("ALTASIB_SPEED")." ", 1, array("selectbox", array(1, 2, 3, 4))),
                Array("altasib_snowletter", GetMessage("ALTASIB_SNOWLETTER")." ", "*", Array("text", 1)),
                Array("altasib_snowmax", GetMessage("ALTASIB_SNOWMAX")." ", 2, array("selectbox", array(10, 20, 30, 40, 50))),

                Array("altasib_snow_color1", GetMessage("ALTASIB_COLOR"), "#aaaacc", Array("text", 30)),
                Array("altasib_snow_color2", GetMessage("ALTASIB_COLOR"), "#ddddff", Array("text", 30)),
                Array("altasib_snow_color3", GetMessage("ALTASIB_COLOR"), "#ccccdd", Array("text", 30)),
                Array("altasib_snow_color4", GetMessage("ALTASIB_COLOR"), "#f3f3f3", Array("text", 30)),
                Array("altasib_snow_color5", GetMessage("ALTASIB_COLOR"), "#f0ffff", Array("text", 30)),
        )
);
$aTabs = array(
        array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
        array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "altasib_comments_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);

//Restore defaults
if ($USER->IsAdmin() && $_SERVER["REQUEST_METHOD"]=="GET" && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
{
        COption::RemoveOption("altasib_snow");
}
$tabControl = new CAdminTabControl("tabControl", $aTabs);

function ShowParamsHTMLByArray($arParams)
{
        foreach($arParams as $Option)
        {
                 __AdmSettingsDrawRow("altasib_snow", $Option);
        }
}

//Save options
if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid())
{
        if(strlen($RestoreDefaults)>0)
        {
                COption::RemoveOption("altasib_snow");
        }
        else
        {
                foreach($arAllOptions as $aOptGroup)
                {
                        foreach($aOptGroup as $option)
                        {
                                __AdmSettingsSaveOption($module_id, $option);
                        }
                }
        }
        if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
                LocalRedirect($_REQUEST["back_url_settings"]);
        else
                LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}
?>

<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&amp;lang=<?echo LANG?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
        ?>
<div style="background-color: #fff; padding: 0; border-top: 1px solid #8E8E8E; border-bottom: 1px solid #8E8E8E;  margin-bottom: 15px;"><div style="background-color: #8E8E8E; height: 30px; padding: 7px; border: 1px solid #fff">
        <a href="http://www.is-market.ru?param=cl" target="_blank"><img src="/bitrix/modules/altasib.snow/images/is-market.gif" style="float: left; margin-right: 15px;" border="0" /></a>
        <div style="margin: 13px 0px 0px 0px">
                <a href="http://www.is-market.ru?param=cl" target="_blank" style="color: #fff; font-size: 10px; text-decoration: none"><?=GetMessage("ALTASIB_IS")?></a>
        </div>
</div></div>

        <?
        ShowParamsHTMLByArray($arAllOptions["main"]);
        ?>
<?
?>


<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
<script type="text/javascript" src="/bitrix/js/jquery/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="/bitrix/modules/altasib.snow/js/iColorPicker.js"></script>


<script language="JavaScript">
$('input[name=altasib_snow_color1]').attr('id', 'altasib_snow_color1');
$('input[name=altasib_snow_color1]').addClass('iColorPicker');

$('input[name=altasib_snow_color2]').attr('id', 'altasib_snow_color2');
$('input[name=altasib_snow_color2]').addClass('iColorPicker');

$('input[name=altasib_snow_color3]').attr('id', 'altasib_snow_color3');
$('input[name=altasib_snow_color3]').addClass('iColorPicker');

$('input[name=altasib_snow_color4]').attr('id', 'altasib_snow_color4');
$('input[name=altasib_snow_color4]').addClass('iColorPicker');

$('input[name=altasib_snow_color5]').attr('id', 'altasib_snow_color5');
$('input[name=altasib_snow_color5]').addClass('iColorPicker');

function RestoreDefaults()
{
        if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)?>&<?=bitrix_sessid_get()?>";
}
</script>
<script language="JavaScript">
function RestoreDefaults()
{
        if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)?>&<?=bitrix_sessid_get()?>";
}
</script>
<div align="left">
        <input type="hidden" name="Update" value="Y">
        <input type="submit" <?if(!$USER->IsAdmin())echo " disabled ";?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
        <input type="reset" <?if(!$USER->IsAdmin())echo " disabled ";?> name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
        <input type="button" <?if(!$USER->IsAdmin())echo " disabled ";?>  type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
</div>
<?$tabControl->End();?>
<?=bitrix_sessid_post();?>
</form>
