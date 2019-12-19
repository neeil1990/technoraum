<?
$module_id = "ram.watermark";

$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($MODULE_RIGHT === 'D')
{
	$APPLICATION->AuthForm(GetMessage("ram.watermark_ACCESS_DENIED"));
}

\Bitrix\Main\Loader::includeModule('ram.watermark');

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

IncludeModuleLangFile(__FILE__);

$uploadDir = \COption::GetOptionString("main", "upload_dir", "upload");

$aTabs = array(
	Array("DIV" => "rights", "TAB" => GetMessage("ram.watermark_TAB_RIGHTS"), "TITLE" => GetMessage("ram.watermark_TAB_RIGHTS_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($REQUEST_METHOD == "POST" && strlen($Update.$RestoreDefaults)>0 && check_bitrix_sessid() && $MODULE_RIGHT === 'W')
{
	if (strlen($RestoreDefaults) > 0)
	{
		$z = \CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while ($zr = $z->Fetch())  $APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}
	
	LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
}

$tabControl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&amp;lang=<?echo LANG?>" name="ram.watermark_settings">
<?=bitrix_sessid_post();?>
<?
$tabControl->BeginNextTab();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");

if ($MODULE_RIGHT === 'W')
{
	$tabControl->Buttons();?>

	<script language="JavaScript">
	function confirmRestoreDefaults()
	{
		return confirm('<?=AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>');
	}
	</script>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>">
	<input type="hidden" name="Update" value="Y">
	<input type="reset" name="reset" value="<?=GetMessage("MAIN_RESET")?>">
	<input type="submit" name="RestoreDefaults" title="<?=GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirmRestoreDefaults();" value="<?=GetMessage("MAIN_RESTORE_DEFAULTS")?>">

	<?
}
?>

<?$tabControl->End();?>
</form>