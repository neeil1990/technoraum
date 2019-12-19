<?
IncludeModuleLangFile(__FILE__);
?>
<?
\Bitrix\Main\Loader::includeModule('ram.watermark');

$dbImages = Ram\Watermark\ImageTable::getList();
while ($arImage = $dbImages -> Fetch())
{
	Ram\Watermark\ImageTable::update($arImage['ID'], Array('STATUS' => 'C'));
}

$cacheObjects = Array();

$filtersIblockList = \Ram\Watermark\FilterTable::getList(array('select' => Array('OBJECT'), 'filter' => array('MODULE' => 'iblock'), 'group' => array('OBJECT')));
while ($filterIblock = $filtersIblockList->fetch())
{
	$cacheObjects[] = $filterIblock['OBJECT'];
}

if (!empty($cacheObjects))
{
	$cacheManager = \Bitrix\Main\Application::getInstance()->getTaggedCache();
	
	$cacheObjects = array_unique($cacheObjects);
	
	foreach ($cacheObjects as $cacheObject)
	{
		$cacheManager->clearByTag('iblock_id_'.$cacheObject);
	}
}

CJSCore::Init(array("jquery"));

?><script type='text/javascript'>var ram_watermark_message_ajax_error = "<?=GetMessage("ram.watermark_AJAX_ERROR")?>";</script><?

$APPLICATION->AddHeadScript('/bitrix/panel/ram.watermark/admin.js');

\CRamWatermark::ajaxFilters(0, false, null, true);
?>