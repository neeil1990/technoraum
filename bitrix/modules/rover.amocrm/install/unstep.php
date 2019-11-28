<?php use Bitrix\Main\Localization\Loc; ?>
<form action="<?=$APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="rover.amocrm">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
	<?=\CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN"))?>
	<p><?=Loc::getMessage("MOD_UNINST_SAVE")?></p>
	<p><input type="checkbox" name="saveevent" id="saveevent" value="Y" checked><label for="saveevent"><?=Loc::getMessage("rover-acrm__uninstall-saveevent")?></label></p>
	<p><input type="checkbox" name="savelog" id="savelog" value="Y" checked><label for="savelog"><?=Loc::getMessage("rover-acrm__uninstall-savelog")?></label></p>
	<input type="submit" name="inst" value="<?=Loc::getMessage("MOD_UNINST_DEL")?>">
</form>