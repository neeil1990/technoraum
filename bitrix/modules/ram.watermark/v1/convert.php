<?
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
CJSCore::Init(array("jquery"));
$APPLICATION->AddHeadScript('/bitrix/panel/ram.watermark/admin.js');
$steps = Array('set_structure_v1', 'remove_old_events', 'set_clear_status', 'clear_files', 'get_data_v1', 'convert_data', 'clear_iblock_cache', 'add_new_events');
?>
<script type='text/javascript'>
var ramwmadmin_error = "<?=GetMessage("ram.watermark_AJAX_ERROR")?>";
var ramwmadmin_v1_finish = "<?=GetMessage("ram.watermark_AJAX_FINISH")?>";
</script>
<p><?=Loc::getMessage("ram.watermark_V1_CONVERT_DESCRIPTION")?></p>
<a href="#" onclick='RamWmAdminV1Ajax(<?=json_encode($steps)?>); $(this).remove(); return false;' class="adm-btn adm-btn-save"><?=Loc::getMessage("ram.watermark_V1_CONVERT_BTN")?></a>
<div class='ramwmadmin-v1-ajax'></div>