<?
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
CJSCore::Init(array("jquery"));
$APPLICATION->AddHeadScript('/bitrix/panel/ram.watermark/admin.js');
$steps = Array('set_structure_v1', 'remove_old_events', 'set_clear_status', 'clear_files', 'clear_iblock_cache', 'uninstall');
?>
<script type='text/javascript'>
var ramwmadmin_error = "<?=GetMessage("ram.watermark_AJAX_ERROR")?>";
var ramwmadmin_v1_finish = "<?=GetMessage("ram.watermark_AJAX_FINISH")?>";
RamWmAdminV1Ajax(<?=json_encode($steps)?>);
</script>
<div class='ramwmadmin-v1-ajax'></div>