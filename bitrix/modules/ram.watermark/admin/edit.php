<?
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Bitrix\User;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

$module_id = 'ram.watermark';

$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($MODULE_RIGHT === 'D')
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

if (CACHED_b_file)
{
	$notifyList = CAdminNotify::GetList(Array('ID' => 'DESC'), Array('MODULE_ID' => 'ram.watermark', 'TAG' => 'FILE_CACHE'));
	if (!$notify = $notifyList->Fetch())
	{
		CAdminNotify::Add
		(
			Array
			(
				'MESSAGE' => Loc::getMessage('ram.watermark_FILE_CACHE_NOTIFY'),
				'MODULE_ID' => 'ram.watermark',
				'TAG' => 'FILE_CACHE',
			)
		);
	}
}

if (intval(CACHED_b_file_bucket_size) != 1)
{
	$notifyList = CAdminNotify::GetList(Array('ID' => 'DESC'), Array('MODULE_ID' => 'ram.watermark', 'TAG' => 'FILE_BUCKET_SIZE'));
	if (!$notify = $notifyList->Fetch())
	{
		CAdminNotify::Add
		(
			Array
			(
				'MESSAGE' => Loc::getMessage('ram.watermark_FILE_BUCKET_SIZE_NOTIFY'),
				'MODULE_ID' => 'ram.watermark',
				'TAG' => 'FILE_BUCKET_SIZE',
			)
		);
	}
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	
Loader::includeModule($module_id);

Loc::loadMessages(__FILE__);

$version1 = \Bitrix\Main\Application::getConnection()->query('SHOW COLUMNS FROM ram_watermark_image LIKE "STATUS"')->fetch();
$version1 = !empty($version1);
if ($version1)
{
	$APPLICATION->IncludeAdminFile(Loc::getMessage("ram.watermark_V1_CONVERT"), $DOCUMENT_ROOT."/bitrix/modules/ram.watermark/v1/convert.php");
	die();
}
	
$context = Application::getInstance()->getContext();
$request = $context->getRequest();

$ID = intVal($ID);
$copy = $request['action'] === 'copy' ? true : false;
$delete = $request['action'] === 'delete' ? true : false;

$uploadDir = \COption::GetOptionString("main", "upload_dir", "upload");

$cacheManager = Application::getInstance()->getTaggedCache();
$cacheObjects = Array();
	
if ($request->isPost() && $MODULE_RIGHT === 'W')
{
	$watermarkData = $_POST;
	$redirect = true;
	$filtersDeleted = false;
	$filtersAdded = Array();
	$paramsChanged = false;
	
	if (!strlen($watermarkData['NAME'])) $errors[] = Loc::getMessage("ram.watermark_ERROR_EMPTY")." \"".Loc::getMessage("ram.watermark_NAME")."\"";
	
	if (!count($errors))
	{
		if ($ID && !$copy)
		{
			$savedData = \Ram\Watermark\MarkTable::getRowById($ID);
			
			$paramsChanged = count(array_diff_assoc($watermarkData['PARAMS'], $savedData['PARAMS'])) > 0 ? true : false;
			
			$result = \Ram\Watermark\MarkTable::update($ID, Array('NAME' => $watermarkData['NAME'], 'ACTIVE' => $watermarkData['ACTIVE'], 'PARAMS' => $watermarkData['PARAMS']));
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
			}
			else
			{
				if (!$paramsChanged)
				{
					if ($watermarkData['ACTIVE'] != $savedData['ACTIVE']) $paramsChanged = true;
				}
				if ($paramsChanged)
				{
					\CRamWatermark::clearCache($ID);
					\CRamWatermark::createCache($ID, $watermarkData['PARAMS']);
					\Ram\Watermark\MarkTable::getEntity()->cleanCache();
				}
			}
		}
		else if (($ID && $copy) || !$ID)
		{
			$result = \Ram\Watermark\MarkTable::add(Array('NAME' => $watermarkData['NAME'], 'ACTIVE' => $watermarkData['ACTIVE'], 'PARAMS' => $watermarkData['PARAMS']));
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
			}
			else
			{
				$NEWID = $result->getID();
				\CRamWatermark::createCache($NEWID, $watermarkData['PARAMS']);
			}
		}
	}
	
	if (!$errors)
	{
		$postFilters = Array();
		
		if (!empty($watermarkData['FILTERS']))
		{
			foreach ($watermarkData['FILTERS'] as $filterObject => $filterFields)
			{
				foreach ($filterFields as $filterField)
				{
					$arrFilterObject = explode('_', $filterObject);
					
					$filterObjectCount = count($arrFilterObject);
					
					$filter = Array
					(
						'TYPE' => $arrFilterObject[0],
						'MODULE' => $arrFilterObject[1],
						'FIELD' => $filterField,
						'ENTITY' => null,
						'GROUP' => null,
						'OBJECT' => null,
					);
					
					if ($filterObjectCount == 3)
					{
						$filter['ENTITY'] = $arrFilterObject[2];
					}
					else if ($filterObjectCount == 4)
					{
						$filter['ENTITY'] = $arrFilterObject[2];
						$filter['OBJECT'] = $arrFilterObject[3];
					}
					else if ($filterObjectCount == 5)
					{
						$filter['ENTITY'] = $arrFilterObject[2];
						$filter['GROUP'] = $arrFilterObject[3];
						$filter['OBJECT'] = $arrFilterObject[4];
					}
					
					ksort($filter);
					
					if ($filter['MODULE'] === 'iblock')
					{
						$cacheObjects[$filter['ENTITY']] = $filter['ENTITY'];
					}
					
					$postFilters[md5(serialize($filter))] = $filter;
				}
			}
		}
		
		if ($ID && !$copy)
		{
			$DBFilters = array();
			$filtersList = \Ram\Watermark\FilterTable::getList(array('filter' => array('WMID' => $ID)));
			while ($filter = $filtersList->fetch())
			{
				$_filter = $filter;
				unset($_filter['ID']);
				unset($_filter['WMID']);
				ksort($_filter);
				$DBFilters[md5(serialize($_filter))] = $filter;
			}
			
			foreach ($postFilters as $md5code => $postFilter)
			{
				$postFilter['WMID'] = $ID;
				
				if (!empty($DBFilters))
				{
					if (!isset($DBFilters[$md5code]))
					{
						$res = \Ram\Watermark\FilterTable::add($postFilter);
						
						if ($res->isSuccess())
						{
							//
						}
					}
				}
				else
				{
					$res = \Ram\Watermark\FilterTable::add($postFilter);
					
					if ($res->isSuccess())
					{
						//
					}
				}
			}
			
			if (!empty($DBFilters))
			{
				foreach ($DBFilters as $md5code => $dbFilter)
				{
					if (!empty($postFilters))
					{
						if (!isset($postFilters[$md5code]))
						{
							if ($dbFilter['MODULE'] === 'iblock')
							{
								$cacheObjects[$dbFilter['ENTITY']] = $dbFilter['ENTITY'];
							}
							
							\Ram\Watermark\FilterTable::delete($dbFilter['ID']);
						}
						else
						{
							if ($dbFilter['MODULE'] === 'iblock' && !$paramsChanged)
							{
								unset($cacheObjects[$dbFilter['ENTITY']]);
							}
						}
					}
					else
					{
						if ($dbFilter['MODULE'] === 'iblock')
						{
							$cacheObjects[$dbFilter['ENTITY']] = $dbFilter['ENTITY'];
						}
						
						\Ram\Watermark\FilterTable::delete($dbFilter['ID']);
					}
				}
			}
		}
		else
		{
			if (!empty($postFilters))
			{
				foreach ($postFilters as $md5code => $postFilter)
				{
					$postFilter['WMID'] = $NEWID;
					
					if ($postFilter['MODULE'] === 'iblock')
					{
						$cacheObjects[$postFilter['ENTITY']] = $postFilter['ENTITY'];
					}
					
					$res = \Ram\Watermark\FilterTable::add($postFilter);
					
					if ($res->isSuccess())
					{
						//
					}
				}
			}
		}
		
		if (!empty($cacheObjects))
		{					
			$cacheObjects = array_unique($cacheObjects);
			
			if (isset($cacheObjects['all']))
			{
				$iblocks = \Bitrix\Iblock\IblockTable::getList(Array('select' => Array('ID')))->fetchAll();
				foreach ($iblocks as $iblock)
				{
					$cacheManager->clearByTag('iblock_id_'.$iblock['ID']);
				}
			}
			else
			{
				foreach ($cacheObjects as $cacheObject)
				{
					$cacheManager->clearByTag('iblock_id_'.$cacheObject);
				}
			}
		}
		
		\Ram\Watermark\FilterTable::getEntity()->cleanCache();
	}
	
	if (!$errors)
	{
		$cache = Bitrix\Main\Application::getInstance()->getManagedCache();
		$cache->cleanDir('b_file');
		
		LocalRedirect("ram.watermark_list.php?lang=".LANGUAGE_ID);
	}
	else
	{
		CAdminMessage::ShowMessage(implode("<br/>", $errors));
	}
}
else
{
	if ($ID && $delete && $MODULE_RIGHT === 'W')
	{
		if (\Ram\Watermark\MarkTable::getRowById($ID))
		{
			$filtersList = \Ram\Watermark\FilterTable::getList(array('filter' => array('WMID' => $ID)));
			while ($filter = $filtersList->fetch())
			{
				if ($filter['MODULE'] === 'iblock')
				{
					if (is_numeric($filter['ENTITY']))
					{
						$cacheManager->clearByTag('iblock_id_'.$filter['ENTITY']);
					}
					else if ($filter['ENTITY'] === 'all')
					{
						$iblocks = \Bitrix\Iblock\IblockTable::getList(Array('select' => Array('ID')))->fetchAll();
						foreach ($iblocks as $iblock)
						{
							$cacheManager->clearByTag('iblock_id_'.$iblock['ID']);
						}
					}
				}
				
				\Ram\Watermark\FilterTable::delete($filter['ID']);
			}
			
			\Ram\Watermark\MarkTable::delete($ID);
			
			\CRamWatermark::clearCache($ID);
		}
		
		LocalRedirect("ram.watermark_list.php?lang=".LANGUAGE_ID);
	}
	else if ($ID && $copy && $MODULE_RIGHT === 'W')
	{
		$watermarkData = \Ram\Watermark\MarkTable::getRowById($ID);
		$APPLICATION->SetTitle(Loc::getMessage("ram.watermark_COPY"));
	}
	else if ($ID)
	{
		$watermarkData = \Ram\Watermark\MarkTable::getRowById($ID);
		$APPLICATION->SetTitle(Loc::getMessage("ram.watermark_EDIT"));
	}
	else
	{
		$watermarkData = Array
		(
			'ACTIVE' => 'Y',
			'PARAMS' => Array
			(
				'POSITION' => 'mc',
				'TRANSPARENT' => 50,
				'ROTATE' => 0,
				'SCALE' => 80,
				'MARGIN_TOP' => 10,
				'MARGIN_RIGHT' => 10,
				'MARGIN_BOTTOM' => 10,
				'MARGIN_LEFT' => 10,
				'TYPE' => 'image',
				'TEXT' => Loc::getMessage("ram.watermark_PARAMS_TEXT_DEFAULT"),
				'TEXT_ALIGN' => 'center',
				'TEXT_COLOR' => '#000000',
				'TEXT_SIZE' => 20,
				'TEXT_LEADING' => 1.3,
				'IMAGE' => 'example.png',
				'JPEG_QUALITY' => 100,
				'REDUCE_SIZE' => 'Y',
				'MAX_WIDTH' => 800,
				'MAX_HEIGHT' => 600,
				'LIMIT_TYPE' => 'N',
				'LIMIT_JPG' => 'Y',
				'LIMIT_PNG' => 'Y',
				'LIMIT_GIF' => 'Y',
				'LIMIT_BMP' => 'Y',
				'LIMIT_WEBP' => 'Y',
				'LIMIT_SIZES' => 'N',
				'LIMIT_MIN_WIDTH' => 0,
				'LIMIT_MAX_WIDTH' => 5000,
				'LIMIT_MIN_HEIGHT' => 0,
				'LIMIT_MAX_HEIGHT' => 5000,
				'LIMIT_DATE' => 'N',
				'LIMIT_DATE_CREATION' => '',
			),
		);
		$APPLICATION->SetTitle(Loc::getMessage("ram.watermark_ADD"));
	}
}

CJSCore::Init(array('jquery', 'date'));
$APPLICATION->AddHeadScript('/bitrix/panel/ram.watermark/colorpicker.js');
$APPLICATION->SetAdditionalCSS('/bitrix/panel/ram.watermark/admin.css');
$APPLICATION->AddHeadScript('/bitrix/panel/ram.watermark/admin.js');
	
$aTabs = Array(
	Array("DIV" => "params", "TAB" => Loc::getMessage("ram.watermark_TABS_params"), "TITLE" => Loc::getMessage("ram.watermark_TABS_params_title")),
	Array("DIV" => "filter", "TAB" => Loc::getMessage("ram.watermark_TABS_filter"), "TITLE" => Loc::getMessage("ram.watermark_TABS_filter_title")),
);

$tabControl = new \CAdminTabControl("ramWatermarkTabControl", $aTabs);

?><form id='ram-watermark-form' method="POST" action="<?=$APPLICATION->GetCurPageParam()?>" name="ram_watermark_edit" enctype="multipart/form-data"><?=bitrix_sessid_post()?><?

$tabControl->Begin();

$tabControl->BeginNextTab();

$arFonts = \CRamWatermark::getFonts();

$arImages = \CRamWatermark::getImages();

$arParams = Array
(
	'ID' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_ID_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_ID_HINT"),
		'TYPE' => 'value',
	),
	'ACTIVE' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_ACTIVE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_ACTIVE_HINT"),
		'TYPE' => 'checkbox',
	),
	'NAME' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_NAME_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_NAME_HINT"),
		'TYPE' => 'text',
	),
	'PARAMS[TYPE]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_TYPE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_TYPE_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_TYPE_MEASURE"),
		'TYPE' => 'select',
		'VALUES' => Array
		(
			'text' => Loc::getMessage("ram.watermark_PARAMS_TYPE_text"),
			'image' => Loc::getMessage("ram.watermark_PARAMS_TYPE_image"),
		),
		'EVENT' => 'RamWmAdminTypeChange(this); RamWmAdminUpdateParams(this);',
	),
	'PARAMS[POSITION]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_POSITION_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_POSITION_HINT"),
		'TYPE' => 'select',
		'VALUES' => Array
		(
			'tl' => Loc::getMessage("ram.watermark_PARAMS_POSITION_tl"),
			'tc' => Loc::getMessage("ram.watermark_PARAMS_POSITION_tc"),
			'tr' => Loc::getMessage("ram.watermark_PARAMS_POSITION_tr"),
			'ml' => Loc::getMessage("ram.watermark_PARAMS_POSITION_ml"),
			'mc' => Loc::getMessage("ram.watermark_PARAMS_POSITION_mc"),
			'mr' => Loc::getMessage("ram.watermark_PARAMS_POSITION_mr"),
			'bl' => Loc::getMessage("ram.watermark_PARAMS_POSITION_bl"),
			'bc' => Loc::getMessage("ram.watermark_PARAMS_POSITION_bc"),
			'br' => Loc::getMessage("ram.watermark_PARAMS_POSITION_br"),
			'all' => Loc::getMessage("ram.watermark_PARAMS_POSITION_all"),
			'random' => Loc::getMessage("ram.watermark_PARAMS_POSITION_random"),
		),
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[TRANSPARENT]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_TRANSPARENT_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_TRANSPARENT_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_TRANSPARENT_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 99,
		'STEP' => 1,
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[ROTATE]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_ROTATE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_ROTATE_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_ROTATE_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 359,
		'STEP' => 1,
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[SCALE]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_SCALE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_SCALE_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_SCALE_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 100,
		'STEP' => 1,
		'EVENT' => 'RamWmAdminScaleChange(this); RamWmAdminUpdateParams(this);'
	),
	'PARAMS[TEXT]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_TEXT_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_MEASURE"),
		'TYPE' => 'textarea',
		'GROUP' => Array('text'),
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[TEXT_COLOR]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_COLOR_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_TEXT_COLOR_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_COLOR_MEASURE"),
		'TYPE' => 'color',
		'GROUP' => Array('text'),
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[TEXT_FONT]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_FONT_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_TEXT_FONT_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_FONT_MEASURE"),
		'TYPE' => 'font',
		'VALUES' => $arFonts,
		'VALUE' => key($arFonts),
		'GROUP' => Array('text'),
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[TEXT_SIZE]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_SIZE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_TEXT_SIZE_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_SIZE_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 10,
		'MAX' => 200,
		'STEP' => 1,
		'GROUP' => Array('text', 'scale'),
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[TEXT_ALIGN]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_ALIGN_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_TEXT_ALIGN_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_ALIGN_MEASURE"),
		'TYPE' => 'select',
		'VALUES' => Array
		(
			'left' => Loc::getMessage("ram.watermark_PARAMS_TEXT_ALIGN_left"),
			'center' => Loc::getMessage("ram.watermark_PARAMS_TEXT_ALIGN_center"),
			'right' => Loc::getMessage("ram.watermark_PARAMS_TEXT_ALIGN_right"),
		),
		'GROUP' => Array('text'),
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[TEXT_LEADING]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_LEADING_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_TEXT_LEADING_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_TEXT_LEADING_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 1,
		'MAX' => 5,
		'STEP' => 0.1,
		'GROUP' => Array('text'),
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[IMAGE]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_IMAGE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_IMAGE_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_IMAGE_MEASURE"),
		'TYPE' => 'image',
		'VALUES' => $arImages,
		'VALUE' => key($arImages),
		'GROUP' => Array('image'),
		'EVENT' => 'RamWmAdminUpdateParams(this);',
	),
	'PARAMS[MARGIN_TOP]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_TOP_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_TOP_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_TOP_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 200,
		'STEP' => 1,
		'EVENT' => 'RamWmAdminUpdateParams(this);',
		'OVER' => 'RamWmAdminOnOver("top");',
		'OUT' => 'RamWmAdminOnOut("top");',
	),
	'PARAMS[MARGIN_RIGHT]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_RIGHT_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_RIGHT_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_RIGHT_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 200,
		'STEP' => 1,
		'EVENT' => 'RamWmAdminUpdateParams(this);',
		'OVER' => 'RamWmAdminOnOver("right");',
		'OUT' => 'RamWmAdminOnOut("right");',
	),
	'PARAMS[MARGIN_BOTTOM]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_BOTTOM_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_BOTTOM_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_BOTTOM_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 200,
		'STEP' => 1,
		'EVENT' => 'RamWmAdminUpdateParams(this);',
		'OVER' => 'RamWmAdminOnOver("bottom");',
		'OUT' => 'RamWmAdminOnOut("bottom");',
	),
	'PARAMS[MARGIN_LEFT]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_LEFT_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_LEFT_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_MARGIN_LEFT_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 200,
		'STEP' => 1,
		'EVENT' => 'RamWmAdminUpdateParams(this);',
		'OVER' => 'RamWmAdminOnOver("left");',
		'OUT' => 'RamWmAdminOnOut("left");',
	),
	'PARAMS[JPEG_QUALITY]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_JPEG_QUALITY_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_JPEG_QUALITY_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_JPEG_QUALITY_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 100,
		'STEP' => 1,
	),
	'PARAMS[REDUCE_SIZE]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_REDUCE_SIZE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_REDUCE_SIZE_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_REDUCE_SIZE_MEASURE"),
		'TYPE' => 'checkbox',
		'EVENT' => 'RamWmAdminReduceSizeChange(this);'
	),
	'PARAMS[MAX_WIDTH]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_MAX_WIDTH_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_MAX_WIDTH_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_MAX_WIDTH_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 100,
		'MAX' => 5000,
		'STEP' => 5,
		'GROUP' => Array('reducesize'),
	),
	'PARAMS[MAX_HEIGHT]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_MAX_HEIGHT_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_MAX_HEIGHT_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_MAX_HEIGHT_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 100,
		'MAX' => 5000,
		'STEP' => 5,
		'GROUP' => Array('reducesize'),
	),
);

foreach ($watermarkData['PARAMS'] as $paramCode => $paramValue)
{
	if (isset($arParams['PARAMS['.$paramCode.']']))
	{
		$arParams['PARAMS['.$paramCode.']']['VALUE'] = $paramValue;
	}
}

if ($ID && !$copy)
{
	$arParams['ID']['VALUE'] = $watermarkData['ID'];
}
else
{
	unset($arParams['ID']);
}

$arParams['ACTIVE']['VALUE'] = $watermarkData['ACTIVE'];
$arParams['NAME']['VALUE'] = $watermarkData['NAME'];

?>
<tr>
	<td class='ramwmadmin'>
		<div class='ramwmadmin-preview' data-color='white'>
			<div class='ramwmadmin-preview-settings'>
				<div class='ramwmadmin-scheme' title='<?=Loc::getMessage("ram.watermark_PREVIEW_SETTINGS_SCHEME")?>'></div>
				<div class='ramwmadmin-background' data-color='white' title='<?=Loc::getMessage("ram.watermark_PREVIEW_SETTINGS_WHITE")?>'></div>
				<div class='ramwmadmin-background' data-color='black' title='<?=Loc::getMessage("ram.watermark_PREVIEW_SETTINGS_BLACK")?>'></div>
				<div class='ramwmadmin-background' data-color='red' title='<?=Loc::getMessage("ram.watermark_PREVIEW_SETTINGS_RED")?>'></div>
				<div class='ramwmadmin-background' data-color='green' title='<?=Loc::getMessage("ram.watermark_PREVIEW_SETTINGS_GREEN")?>'></div>
				<div class='ramwmadmin-background' data-color='blue' title='<?=Loc::getMessage("ram.watermark_PREVIEW_SETTINGS_BLUE")?>'></div>
			</div>
		</div>
		<div class='ramwmadmin-params'>
<?

foreach ($arParams as $paramCode => $paramData)
{
	\CRamWatermark::showAdminParam($paramCode, $paramData);
}

?></div></td></tr><?

?>

<script type='text/javascript'>
var ramwmUploadDir = '<?=$uploadDir?>';
var ramwmEmptySelect = '<?=Loc::getMessage("ram.watermark_EMPTY_SELECT")?>';
</script>

<?	
$tabControl->BeginNextTab();

$arParams = Array
(
	'PARAMS[LIMIT_TYPE]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_TYPE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_TYPE_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_TYPE_MEASURE"),
		'TYPE' => 'checkbox',
		'EVENT' => 'RamWmAdminLimitTypeChange(this);'
	),
	'PARAMS[LIMIT_JPG]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_JPG_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_JPG_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_JPG_MEASURE"),
		'TYPE' => 'checkbox',
		'GROUP' => Array('limittype'),
	),
	'PARAMS[LIMIT_PNG]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_PNG_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_PNG_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_PNG_MEASURE"),
		'TYPE' => 'checkbox',
		'GROUP' => Array('limittype'),
	),
	'PARAMS[LIMIT_GIF]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_GIF_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_GIF_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_GIF_MEASURE"),
		'TYPE' => 'checkbox',
		'GROUP' => Array('limittype'),
	),
	'PARAMS[LIMIT_BMP]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_BMP_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_BMP_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_BMP_MEASURE"),
		'TYPE' => 'checkbox',
		'GROUP' => Array('limittype'),
	),
	'PARAMS[LIMIT_WEBP]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_WEBP_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_WEBP_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_WEBP_MEASURE"),
		'TYPE' => 'checkbox',
		'GROUP' => Array('limittype'),
	),
	'PARAMS[LIMIT_SIZES]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_SIZES_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_SIZES_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_SIZES_MEASURE"),
		'TYPE' => 'checkbox',
		'EVENT' => 'RamWmAdminLimitSizesChange(this);'
	),
	'PARAMS[LIMIT_MIN_WIDTH]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MIN_WIDTH_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MIN_WIDTH_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MIN_WIDTH_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 5000,
		'STEP' => 5,
		'GROUP' => Array('limitsizes'),
	),
	'PARAMS[LIMIT_MAX_WIDTH]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MAX_WIDTH_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MAX_WIDTH_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MAX_WIDTH_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 5000,
		'STEP' => 5,
		'GROUP' => Array('limitsizes'),
	),
	'PARAMS[LIMIT_MIN_HEIGHT]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MIN_HEIGHT_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MIN_HEIGHT_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MIN_HEIGHT_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 5000,
		'STEP' => 5,
		'GROUP' => Array('limitsizes'),
	),
	'PARAMS[LIMIT_MAX_HEIGHT]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MAX_HEIGHT_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MAX_HEIGHT_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_MAX_HEIGHT_MEASURE"),
		'TYPE' => 'number',
		'MIN' => 0,
		'MAX' => 5000,
		'STEP' => 5,
		'GROUP' => Array('limitsizes'),
	),
	'PARAMS[LIMIT_DATE]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_MEASURE"),
		'TYPE' => 'checkbox',
		'EVENT' => 'RamWmAdminLimitDateChange(this);'
	),
	'PARAMS[LIMIT_DATE_FROM]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_FROM_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_FROM_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_FROM_MEASURE"),
		'TYPE' => 'date',
		'GROUP' => Array('limitdate'),
	),
	'PARAMS[LIMIT_DATE_TO]' => Array
	(
		'TITLE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_TO_TITLE"),
		'HINT' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_TO_HINT"),
		'MEASURE' => Loc::getMessage("ram.watermark_PARAMS_LIMIT_DATE_TO_MEASURE"),
		'TYPE' => 'date',
		'GROUP' => Array('limitdate'),
	),
);

foreach ($watermarkData['PARAMS'] as $paramCode => $paramValue)
{
	if (isset($arParams['PARAMS['.$paramCode.']']))
	{
		$arParams['PARAMS['.$paramCode.']']['VALUE'] = $paramValue;
	}
}

if ($ID)
{
	$arrFilters = Array('include' => Array(), 'exclude' => Array());
	$filtersList = \Ram\Watermark\FilterTable::getList(array('filter' => array('WMID' => $ID)));
	while ($filter = $filtersList->fetch())
	{
		$strFilterField = $filter['FIELD'];
		$strFilterType = $filter['TYPE'];
		
		unset($filter['ID']);
		unset($filter['WMID']);
		unset($filter['FIELD']);
		
		$arrFilter = Array();
		foreach (Array('MODULE', 'ENTITY', 'GROUP', 'OBJECT') as $part)
		{
			if (strlen($filter[$part])) $arrFilter[] = $filter[$part];
		}
		
		$strFitler = implode('_', $arrFilter);
		
		if (!isset($arrFilters[$strFilterType][$strFitler]))
		{
			$arrFilters[$strFilterType][$strFitler] = Array('PATH' => $arrFilter, 'FIELDS' => Array($strFilterField));
		}
		else
		{
			$arrFilters[$strFilterType][$strFitler]['FIELDS'][] = $strFilterField;
		}
	}
}
?>

<tr class='heading'>
	<td colspan='2'><b><?=Loc::getMessage("ram.watermark_INCLUDE")?></b></td>
</tr>
<tr>
	<td colspan='2'>
		<?
		if (!empty($arrFilters['include']))
		{
			\CRamWatermark::showAdminFilter($arrFilters['include'], 'include');
		}
		?>
		<input type='button' onclick='RamWmAdminFilterAdd(this, "include");' class='adm-btn-green' value='<?=Loc::getMessage("ram.watermark_ADD_INCLUDE")?>'/>
	</td>
</tr>
<tr class='heading'>
	<td colspan='2'><b><?=Loc::getMessage("ram.watermark_EXCLUDE")?></b></td>
</tr>
<tr>
	<td colspan='2'>
		<?
		if (!empty($arrFilters['exclude']))
		{
			\CRamWatermark::showAdminFilter($arrFilters['exclude'], 'exclude');
		}
		?>
		<input type='button' onclick='RamWmAdminFilterAdd(this, "exclude");' class='adm-btn-green' value='<?=Loc::getMessage("ram.watermark_ADD_EXCLUDE")?>'/>
	</td>
</tr>
<tr class='heading'>
	<td colspan='2'><b><?=Loc::getMessage("ram.watermark_LIMITS")?></b></td>
</tr>
<tr>
	<td colspan='2'>
		<div class='ramwmadmin-additionalparams'>
		<?
		foreach (Array('PARAMS[LIMIT_TYPE]', 'PARAMS[LIMIT_JPG]', 'PARAMS[LIMIT_PNG]', 'PARAMS[LIMIT_GIF]', 'PARAMS[LIMIT_BMP]', 'PARAMS[LIMIT_WEBP]') as $paramCode)
		{
			\CRamWatermark::showAdminParam($paramCode, $arParams[$paramCode]);
		}
		?>
		</div>
	</td>
</tr>
<tr class='ramwmadmin-paramgroup_limittype'>
	<td colspan='2' align='center'>
		<div class='adm-info-message-wrap' align='center'>
			<div class='adm-info-message'><?=Loc::getMessage("ram.watermark_LIMIT_TYPE_DESCRIPTION")?></div>
		</div>
	</td>
</tr>
<tr>
	<td colspan='2'>
		<div class='ramwmadmin-additionalparams'>
		<?
		foreach (Array('PARAMS[LIMIT_SIZES]', 'PARAMS[LIMIT_MIN_WIDTH]', 'PARAMS[LIMIT_MAX_WIDTH]', 'PARAMS[LIMIT_MIN_HEIGHT]', 'PARAMS[LIMIT_MAX_HEIGHT]') as $paramCode)
		{
			\CRamWatermark::showAdminParam($paramCode, $arParams[$paramCode]);
		}
		?>
		</div>
	</td>
</tr>
<tr class='ramwmadmin-paramgroup_limitsizes'>
	<td colspan='2' align='center'>
		<div class='adm-info-message-wrap' align='center'>
			<div class='adm-info-message'><?=Loc::getMessage("ram.watermark_LIMIT_SIZES_DESCRIPTION")?></div>
		</div>
	</td>
</tr>
<tr>
	<td colspan='2'>
		<div class='ramwmadmin-additionalparams'>
		<?
		foreach (Array('PARAMS[LIMIT_DATE]', 'PARAMS[LIMIT_DATE_FROM]', 'PARAMS[LIMIT_DATE_TO]') as $paramCode)
		{
			\CRamWatermark::showAdminParam($paramCode, $arParams[$paramCode]);
		}
		?>
		</div>
	</td>
</tr>
<tr class='ramwmadmin-paramgroup_limitdate'>
	<td colspan='2' align='center'>
		<div class='adm-info-message-wrap' align='center'>
			<div class='adm-info-message'><?=Loc::getMessage("ram.watermark_LIMIT_DATE_DESCRIPTION")?></div>
		</div>
	</td>
</tr>
<?

if ($MODULE_RIGHT === 'W')
{
	$tabControl->Buttons(Array("btnApply" => false, "back_url"=>"ram.watermark_list.php?lang=".LANGUAGE_ID));
}
else
{
	$tabControl->Buttons(Array("btnSave" => false, "btnApply" => false, "back_url"=>"ram.watermark_list.php?lang=".LANGUAGE_ID));
}

$tabControl->End();

?></form><?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>