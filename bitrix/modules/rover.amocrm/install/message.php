<?php
use Bitrix\Main\Localization\Loc;

global $APPLICATION, $amoErrors;

if (empty($amoErrors))
    echo \CAdminMessage::ShowNote(Loc::getMessage("MOD_INST_OK"));
else
    echo \CAdminMessage::ShowMessage(
        array(
            "TYPE"      => "ERROR",
            "MESSAGE"   => Loc::getMessage("MOD_INST_ERR"),
            "DETAILS"   => implode("<br/>", $amoErrors),
            "HTML"      => true
        ));

?><form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="" value="<?=Loc::getMessage("MOD_BACK")?>">
<form>