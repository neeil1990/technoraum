<?
//<title>Yandex</title>
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var int $IBLOCK_ID */
/** @var string $SETUP_SERVER_NAME */
/** @var string $SETUP_FILE_NAME */
/** @var array $V */
/** @var array|string $XML_DATA */
/** @var bool $firstStep */
/** @var int $CUR_ELEMENT_ID */
/** @var bool $finalExport */
/** @var int $intMaxSectionID */

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Currency,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Sale;

// IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/export_yandex.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/arturgolubev.gmerchant/export_google.php');
IncludeModuleLangFile(__FILE__);

$MAX_EXECUTION_TIME = (isset($MAX_EXECUTION_TIME) ? (int)$MAX_EXECUTION_TIME : 0);
if ($MAX_EXECUTION_TIME <= 0)
	$MAX_EXECUTION_TIME = 0;
if (defined('BX_CAT_CRON') && BX_CAT_CRON == true)
{
	$MAX_EXECUTION_TIME = 0;
	$firstStep = true;
}
if (defined("CATALOG_EXPORT_NO_STEP") && CATALOG_EXPORT_NO_STEP == true)
{
	$MAX_EXECUTION_TIME = 0;
	$firstStep = true;
}
if ($MAX_EXECUTION_TIME == 0)
	set_time_limit(0);

$CHECK_PERMISSIONS = (isset($CHECK_PERMISSIONS) && $CHECK_PERMISSIONS == 'Y' ? 'Y' : 'N');
if ($CHECK_PERMISSIONS == 'Y')
	$permissionFilter = array('CHECK_PERMISSIONS' => 'Y', 'MIN_PERMISSION' => 'R', 'PERMISSIONS_BY' => 0);
else
	$permissionFilter = array('CHECK_PERMISSIONS' => 'N');

if (!isset($firstStep))
	$firstStep = true;

$pageSize = 10;
$navParams = array('nTopCount' => $pageSize);

$SETUP_VARS_LIST = 'IBLOCK_ID,SITE_ID,V,XML_DATA,SETUP_SERVER_NAME,COMPANY_NAME,COMPANY_DESCRIPTION,SETUP_FILE_NAME,USE_HTTPS,HIDE_WITHOT_PICTURES,FILTER_AVAILABLE,DISABLE_REFERERS,MAX_EXECUTION_TIME,CHECK_PERMISSIONS';
$INTERNAL_VARS_LIST = 'intMaxSectionID,arSectionIDs,arAvailGroups';

global $USER, $APPLICATION;
$bTmpUserCreated = false;
if (!CCatalog::IsUserExists())
{
	$bTmpUserCreated = true;
	if (isset($USER))
		$USER_TMP = $USER;
	$USER = new CUser();
}

$formatList = array(
	'google' => array(
		'condition', 'product_type', 'additional_image_link', 'brand', 'gtin', 'mpn', 'mobile_link', 'google_product_category', 'color', 'gender', 'material', 'pattern', 'size',
	),
);

if (!function_exists("yandex_replace_special"))
{
	function yandex_replace_special($arg)
	{
		if (in_array($arg[0], array("&quot;", "&amp;", "&lt;", "&gt;")))
			return $arg[0];
		else
			return " ";
	}
}

$arRunErrors = array();

if(!CModule::IncludeModule('arturgolubev.gmerchant'))
{
	$arRunErrors[] = GetMessage('YANDEX_ERR_BAD_MODULE');
}
else
{
	$moduleWorker = new CArturgolubevGmerchant();
}



$saleIncluded = Loader::includeModule('sale');
if ($saleIncluded && $moduleWorker->checkModule('sale', '16.5.0'))
	Sale\DiscountCouponsManager::freezeCouponStorage();
CCatalogDiscountSave::Disable();

if (isset($XML_DATA))
{
	if (is_string($XML_DATA) && CheckSerializedData($XML_DATA))
		$XML_DATA = unserialize(stripslashes($XML_DATA));
}
if (!isset($XML_DATA) || !is_array($XML_DATA))
	$arRunErrors[] = GetMessage('YANDEX_ERR_BAD_XML_DATA');

$yandexFormat = 'google';
if (isset($XML_DATA['TYPE']) && isset($formatList[$XML_DATA['TYPE']]))
	$yandexFormat = $XML_DATA['TYPE'];

$productFormat = ($yandexFormat != 'google' ? ' type="'.htmlspecialcharsbx($yandexFormat).'"' : '');

$fields = array();
$parametricFields = array();
$fieldsExist = !empty($XML_DATA['XML_DATA']) && is_array($XML_DATA['XML_DATA']);
$parametricFieldsExist = false;
if ($fieldsExist)
{
	foreach ($XML_DATA['XML_DATA'] as $key => $value)
	{
		if ($key == 'PARAMS')
			$parametricFieldsExist = (!empty($value) && is_array($value));
		if (is_array($value))
			continue;
		$value = (string)$value;
		if ($value == '')
			continue;
		$fields[$key] = $value;
	}
	unset($key, $value);
	$fieldsExist = !empty($fields);
}

if ($parametricFieldsExist)
{
	$parametricFields = $XML_DATA['XML_DATA']['PARAMS'];
	if (!empty($parametricFields))
	{
		foreach (array_keys($parametricFields) as $index)
		{
			if ((string)$parametricFields[$index] === '')
				unset($parametricFields[$index]);
		}
	}
	$parametricFieldsExist = !empty($parametricFields);
}

$needProperties = $fieldsExist || $parametricFieldsExist;
$yandexNeedPropertyIds = array();
if ($fieldsExist)
{
	foreach ($fields as $id)
		$yandexNeedPropertyIds[$id] = true;
	unset($id);
}
if ($parametricFieldsExist)
{
	foreach ($parametricFields as $id)
		$yandexNeedPropertyIds[$id] = true;
	unset($id);
}

$propertyFields = array(
	'ID', 'PROPERTY_TYPE', 'MULTIPLE', 'USER_TYPE'
);

$IBLOCK_ID = (int)$IBLOCK_ID;
$db_iblock = CIBlock::GetByID($IBLOCK_ID);
if (!($ar_iblock = $db_iblock->Fetch()))
{
	$arRunErrors[] = str_replace('#ID#', $IBLOCK_ID, GetMessage('YANDEX_ERR_NO_IBLOCK_FOUND_EXT'));
}
/*elseif (!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, 'iblock_admin_display'))
{
	$arRunErrors[] = str_replace('#IBLOCK_ID#',$IBLOCK_ID,GetMessage('CET_ERROR_IBLOCK_PERM'));
} */
else
{
	$ar_iblock['PROPERTY'] = array();
	$rsProps = \CIBlockProperty::GetList(
		array('SORT' => 'ASC', 'NAME' => 'ASC'),
		array('IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N')
	);
	while ($arProp = $rsProps->Fetch())
	{
		$arProp['ID'] = (int)$arProp['ID'];
		$arProp['USER_TYPE'] = (string)$arProp['USER_TYPE'];
		$arProp['CODE'] = (string)$arProp['CODE'];
		if ($arProp['CODE'] == '')
			$arProp['CODE'] = $arProp['ID'];
		$arProp['LINK_IBLOCK_ID'] = (int)$arProp['LINK_IBLOCK_ID'];
		$ar_iblock['PROPERTY'][$arProp['ID']] = $arProp;
	}
	unset($arProp, $rsProps);
}

$SETUP_SERVER_NAME = (isset($SETUP_SERVER_NAME) ? trim($SETUP_SERVER_NAME) : '');
$COMPANY_NAME = (isset($COMPANY_NAME) ? trim($COMPANY_NAME) : '');
$COMPANY_DESCRIPTION = (isset($COMPANY_DESCRIPTION) ? trim($COMPANY_DESCRIPTION) : '');
$SITE_ID = (isset($SITE_ID) ? (string)$SITE_ID : '');
if ($SITE_ID === '')
	$SITE_ID = $ar_iblock['LID'];
$iterator = Main\SiteTable::getList(array(
	'select' => array('LID', 'SERVER_NAME', 'SITE_NAME', 'DIR'),
	'filter' => array('=LID' => $SITE_ID, '=ACTIVE' => 'Y')
));
$site = $iterator->fetch();
unset($iterator);
if (empty($site))
{
	$arRunErrors[] = GetMessage('BX_CATALOG_EXPORT_YANDEX_ERR_BAD_SITE');
}
else
{
	$site['SITE_NAME'] = (string)$site['SITE_NAME'];
	if ($site['SITE_NAME'] === '')
		$site['SITE_NAME'] = (string)Main\Config\Option::get('main', 'site_name');
	$site['COMPANY_NAME'] = $COMPANY_NAME;
	if ($site['COMPANY_NAME'] === '')
		$site['COMPANY_NAME'] = (string)Main\Config\Option::get('main', 'site_name');
	$site['SERVER_NAME'] = (string)$site['SERVER_NAME'];
	if ($SETUP_SERVER_NAME !== '')
		$site['SERVER_NAME'] = $SETUP_SERVER_NAME;
	if ($site['SERVER_NAME'] === '')
	{
		$site['SERVER_NAME'] = (defined('SITE_SERVER_NAME')
			? SITE_SERVER_NAME
			: (string)Main\Config\Option::get('main', 'server_name')
		);
	}
	if ($site['SERVER_NAME'] === '')
	{
		$arRunErrors[] = GetMessage('BX_CATALOG_EXPORT_YANDEX_ERR_BAD_SERVER_NAME');
	}
}

global $iblockServerName;
$iblockServerName = $site['SERVER_NAME'];

$arProperties = array();
if (isset($ar_iblock['PROPERTY']))
	$arProperties = $ar_iblock['PROPERTY'];

$boolOffers = false;
$arOffers = false;
$arOfferIBlock = false;
$intOfferIBlockID = 0;
$offersCatalog = false;
$arSelectOfferProps = array();
$arSelectedPropTypes = array(
	Iblock\PropertyTable::TYPE_STRING,
	Iblock\PropertyTable::TYPE_NUMBER,
	Iblock\PropertyTable::TYPE_LIST,
	Iblock\PropertyTable::TYPE_ELEMENT,
	Iblock\PropertyTable::TYPE_SECTION
);
$arOffersSelectKeys = array(
	YANDEX_SKU_EXPORT_ALL,
	YANDEX_SKU_EXPORT_MIN_PRICE,
	YANDEX_SKU_EXPORT_PROP,
);
$arCondSelectProp = array(
	'ZERO',
	'NONZERO',
	'EQUAL',
	'NONEQUAL',
);
$arSKUExport = array();

$arCatalog = CCatalogSku::GetInfoByIBlock($IBLOCK_ID);
if (empty($arCatalog))
{
	$arRunErrors[] = str_replace('#ID#', $IBLOCK_ID, GetMessage('YANDEX_ERR_NO_IBLOCK_IS_CATALOG'));
}
else
{
	$arCatalog['VAT_ID'] = (int)$arCatalog['VAT_ID'];
	$arOffers = CCatalogSku::GetInfoByProductIBlock($IBLOCK_ID);
	if (!empty($arOffers['IBLOCK_ID']))
	{
		$intOfferIBlockID = $arOffers['IBLOCK_ID'];
		$rsOfferIBlocks = CIBlock::GetByID($intOfferIBlockID);
		if (($arOfferIBlock = $rsOfferIBlocks->Fetch()))
		{
			$boolOffers = true;
			$rsProps = \CIBlockProperty::GetList(
				array('SORT' => 'ASC', 'NAME' => 'ASC'),
				array('IBLOCK_ID' => $intOfferIBlockID, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N')
			);
			while ($arProp = $rsProps->Fetch())
			{
				$arProp['ID'] = (int)$arProp['ID'];
				if ($arOffers['SKU_PROPERTY_ID'] != $arProp['ID'])
				{
					$arProp['USER_TYPE'] = (string)$arProp['USER_TYPE'];
					$arProp['CODE'] = (string)$arProp['CODE'];
					if ($arProp['CODE'] == '')
						$arProp['CODE'] = $arProp['ID'];
					$arProp['LINK_IBLOCK_ID'] = (int)$arProp['LINK_IBLOCK_ID'];

					$ar_iblock['OFFERS_PROPERTY'][$arProp['ID']] = $arProp;
					$arProperties[$arProp['ID']] = $arProp;
					if (in_array($arProp['PROPERTY_TYPE'], $arSelectedPropTypes))
						$arSelectOfferProps[] = $arProp['ID'];
				}
			}
			unset($arProp, $rsProps);
			$arOfferIBlock['LID'] = $site['LID'];
		}
		else
		{
			$arRunErrors[] = GetMessage('YANDEX_ERR_BAD_OFFERS_IBLOCK_ID');
		}
		unset($rsOfferIBlocks);
	}
	if ($boolOffers)
	{
		$offersCatalog = \CCatalog::GetByID($intOfferIBlockID);
		$offersCatalog['VAT_ID'] = (int)$offersCatalog['VAT_ID'];
		if (empty($XML_DATA['SKU_EXPORT']))
		{
			$arRunErrors[] = GetMessage('YANDEX_ERR_SKU_SETTINGS_ABSENT');
		}
		else
		{
			$arSKUExport = $XML_DATA['SKU_EXPORT'];;
			if (empty($arSKUExport['SKU_EXPORT_COND']) || !in_array($arSKUExport['SKU_EXPORT_COND'],$arOffersSelectKeys))
			{
				$arRunErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_CONDITION_ABSENT');
			}
			if (YANDEX_SKU_EXPORT_PROP == $arSKUExport['SKU_EXPORT_COND'])
			{
				if (empty($arSKUExport['SKU_PROP_COND']) || !is_array($arSKUExport['SKU_PROP_COND']))
				{
					$arRunErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_ABSENT');
				}
				else
				{
					if (empty($arSKUExport['SKU_PROP_COND']['PROP_ID']) || !in_array($arSKUExport['SKU_PROP_COND']['PROP_ID'],$arSelectOfferProps))
					{
						$arRunErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_ABSENT');
					}
					if (empty($arSKUExport['SKU_PROP_COND']['COND']) || !in_array($arSKUExport['SKU_PROP_COND']['COND'],$arCondSelectProp))
					{
						$arRunErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_COND_ABSENT');
					}
					else
					{
						if ($arSKUExport['SKU_PROP_COND']['COND'] == 'EQUAL' || $arSKUExport['SKU_PROP_COND']['COND'] == 'NONEQUAL')
						{
							if (empty($arSKUExport['SKU_PROP_COND']['VALUES']))
							{
								$arRunErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_VALUES_ABSENT');
							}
						}
					}
				}
			}
		}
	}
}

$propertyIdList = array_keys($arProperties);
if (empty($arRunErrors))
{
	if (
		$arCatalog['CATALOG_TYPE'] == CCatalogSku::TYPE_FULL
		|| $arCatalog['CATALOG_TYPE'] == CCatalogSku::TYPE_PRODUCT
	)
		$propertyIdList[] = $arCatalog['SKU_PROPERTY_ID'];
}

$arUserTypeFormat = array();
foreach($arProperties as $key => $arProperty)
{
	$arUserTypeFormat[$arProperty['ID']] = false;
	if ($arProperty['USER_TYPE'] == '')
		continue;

	$arUserType = \CIBlockProperty::GetUserType($arProperty['USER_TYPE']);
	if (isset($arUserType['GetPublicViewHTML']))
	{
		$arUserTypeFormat[$arProperty['ID']] = $arUserType['GetPublicViewHTML'];
		$arProperties[$key]['PROPERTY_TYPE'] = 'USER_TYPE';
	}
}
unset($arUserType, $key, $arProperty);

$bAllSections = false;
$arSections = array();


if (empty($arRunErrors))
{
	if (is_array($V))
	{
		foreach ($V as $key => $value)
		{
			if (trim($value)=="0")
			{
				$bAllSections = true;
				continue;
			}
			$value = (int)$value;
			if ($value > 0)
			{
				$arSections[] = $value;
			}
		}
	}
	
	if (!$bAllSections && !empty($arSections) && $CHECK_PERMISSIONS == 'Y')
	{
		$clearedValues = array();
		$filter = array(
			'IBLOCK_ID' => $IBLOCK_ID,
			'ID' => $arSections
		);
		$iterator = CIBlockSection::GetList(
			array(),
			array_merge($filter, $permissionFilter),
			false,
			array('ID')
		);
		while ($row = $iterator->Fetch())
			$clearedValues[] = (int)$row['ID'];
		unset($row, $iterator);
		$arSections = $clearedValues;
		unset($clearedValues);
	}

	if (!$bAllSections && empty($arSections))
	{
		$arRunErrors[] = GetMessage('YANDEX_ERR_NO_SECTION_LIST');
	}
}

$selectedPriceType = 0;
if (!empty($XML_DATA['PRICE']))
{
	$XML_DATA['PRICE'] = (int)$XML_DATA['PRICE'];
	if ($XML_DATA['PRICE'] > 0)
	{
		$rsCatalogGroups = CCatalogGroup::GetGroupsList(array('CATALOG_GROUP_ID' => $XML_DATA['PRICE'],'GROUP_ID' => 2));
		if (!($arCatalogGroup = $rsCatalogGroups->Fetch()))
		{
			$arRunErrors[] = GetMessage('YANDEX_ERR_BAD_PRICE_TYPE');
		}
		else
		{
			$selectedPriceType = $XML_DATA['PRICE'];
		}
		unset($arCatalogGroup, $rsCatalogGroups);
	}
	else
	{
		$arRunErrors[] = GetMessage('YANDEX_ERR_BAD_PRICE_TYPE');
	}
}

$usedProtocol = (isset($USE_HTTPS) && $USE_HTTPS == 'Y' ? 'https://' : 'http://');
$filterAvailable = (isset($FILTER_AVAILABLE) && $FILTER_AVAILABLE == 'Y');

$vatExportSettings = array(
	'ENABLE' => 'N',
	'BASE_VAT' => ''
);

$vatRates = array(
	'0%' => 'VAT_0',
	'10%' => 'VAT_10',
	'18%' => 'VAT_18'
);
$vatList = array();

if (!empty($XML_DATA['VAT_EXPORT']) && is_array($XML_DATA['VAT_EXPORT']))
	$vatExportSettings = array_merge($vatExportSettings, $XML_DATA['VAT_EXPORT']);
$vatExport = $vatExportSettings['ENABLE'] == 'Y';
if ($vatExport)
{
	if ($vatExportSettings['BASE_VAT'] == '')
	{
		$vatExport = false;
	}
	else
	{
		if ($vatExportSettings['BASE_VAT'] != '-')
			$vatList[0] = 'NO_VAT';

		$filter = array('=RATE' => array_keys($vatRates));
		if (isset($vatRates[$vatExportSettings['BASE_VAT']]))
			$filter['!=RATE'] = $vatExportSettings['BASE_VAT'];
		$iterator = Catalog\VatTable::getList(array(
			'select' => array('ID', 'RATE'),
			'filter' => $filter,
			'order' => array('ID' => 'ASC')
		));
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$row['RATE'] = (float)$row['RATE'];
			$index = $row['RATE'].'%';
			if (isset($vatRates[$index]))
				$vatList[$row['ID']] = $vatRates[$index];
		}
		unset($index, $row, $iterator);
	}
}

$itemOptions = array(
	'PROTOCOL' => $usedProtocol,
	'SITE_NAME' => $site['SERVER_NAME'],
	'SITE_DIR' => $site['DIR'],
	'MAX_DESCRIPTION_LENGTH' => 3000
);

$sectionFileName = '';
$itemFileName = '';
if (strlen($SETUP_FILE_NAME) <= 0)
{
	$arRunErrors[] = GetMessage("CATI_NO_SAVE_FILE");
}
elseif (preg_match(BX_CATALOG_FILENAME_REG,$SETUP_FILE_NAME))
{
	$arRunErrors[] = GetMessage("CES_ERROR_BAD_EXPORT_FILENAME");
}
else
{
	$SETUP_FILE_NAME = Rel2Abs("/", $SETUP_FILE_NAME);
}
if (empty($arRunErrors))
{
/*	if ($GLOBALS["APPLICATION"]->GetFileAccessPermission($SETUP_FILE_NAME) < "W")
	{
		$arRunErrors[] = str_replace('#FILE#', $SETUP_FILE_NAME,GetMessage('YANDEX_ERR_FILE_ACCESS_DENIED'));
	} */
	$sectionFileName = $SETUP_FILE_NAME.'_sections';
	$itemFileName = $SETUP_FILE_NAME.'_items';
}

$itemsFile = null;

$BASE_CURRENCY = Currency\CurrencyManager::getBaseCurrency();

if ($firstStep)
{
	if (empty($arRunErrors))
	{
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME);

		if (!$fp = @fopen($_SERVER["DOCUMENT_ROOT"].$sectionFileName, "wb"))
		{
			$arRunErrors[] = str_replace('#FILE#', $sectionFileName, GetMessage('YANDEX_ERR_FILE_OPEN_WRITING'));
		}
		else
		{
			if (!@fwrite($fp, '<?xml version="1.0"?>'."\n"))
			{
				$arRunErrors[] = str_replace('#FILE#', $sectionFileName, GetMessage('YANDEX_ERR_SETUP_FILE_WRITE'));
				@fclose($fp);
			}
		}
	}

	if (empty($arRunErrors))
	{
		/** @noinspection PhpUndefinedVariableInspection */
		/* fwrite($fp, 'header("Content-Type: text/xml; charset=utf-8");?>'."\n"); */
		/* fwrite($fp, 'echo "<"."?xml version=\"1.0\" encoding=\"windows-1251\"?".">"?>'."\n"); */
		fwrite($fp, '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">'."\n");
		
		fwrite($fp, '<channel>'."\n");
		
		fwrite($fp, '<title>'.$APPLICATION->ConvertCharset(htmlspecialcharsbx($COMPANY_NAME), LANG_CHARSET, 'utf-8')."</title>\n");
		fwrite($fp, '<link>'.$usedProtocol.htmlspecialcharsbx($site['SERVER_NAME'])."</link>\n");
		
		if($COMPANY_DESCRIPTION)
			fwrite($fp, '<description>'.$APPLICATION->ConvertCharset(htmlspecialcharsbx($COMPANY_DESCRIPTION), LANG_CHARSET, 'utf-8')."</description>\n");
		
		$arSectionIDs = array();
		if (!$bAllSections)
		{
			for ($i = 0, $intSectionsCount = count($arSections); $i < $intSectionsCount; $i++)
			{
				$sectionIterator = CIBlockSection::GetNavChain($IBLOCK_ID, $arSections[$i], array('ID', 'IBLOCK_SECTION_ID', 'NAME', 'LEFT_MARGIN', 'RIGHT_MARGIN'));
				$curLEFT_MARGIN = 0;
				$curRIGHT_MARGIN = 0;
				while ($section = $sectionIterator->Fetch())
				{
					$section['ID'] = (int)$section['ID'];
					$section['IBLOCK_SECTION_ID'] = (int)$section['IBLOCK_SECTION_ID'];
					if ($arSections[$i] == $section['ID'])
					{
						$curLEFT_MARGIN = (int)$section['LEFT_MARGIN'];
						$curRIGHT_MARGIN = (int)$section['RIGHT_MARGIN'];
						$arSectionIDs[$section['ID']] = $section['ID'];
					}
					$arAvailGroups[$section['ID']] = array(
						'ID' => $section['ID'],
						'IBLOCK_SECTION_ID' => $section['IBLOCK_SECTION_ID'],
						'NAME' => $section['NAME']
					);
					if ($intMaxSectionID < $section['ID'])
						$intMaxSectionID = $section['ID'];
				}
				unset($section, $sectionIterator);

				$filter = array(
					'IBLOCK_ID' => $IBLOCK_ID,
					'>LEFT_MARGIN' => $curLEFT_MARGIN,
					'<RIGHT_MARGIN' => $curRIGHT_MARGIN,
					'GLOBAL_ACTIVE' => 'Y'
				);
				$sectionIterator = CIBlockSection::GetList(
					array('LEFT_MARGIN' => 'ASC'),
					array_merge($filter, $permissionFilter),
					false,
					array('ID', 'IBLOCK_SECTION_ID', 'NAME')
				);
				while ($section = $sectionIterator->Fetch())
				{
					$section['ID'] = (int)$section['ID'];
					$section['IBLOCK_SECTION_ID'] = (int)$section['IBLOCK_SECTION_ID'];
					$arAvailGroups[$section['ID']] = $section;
					if ($intMaxSectionID < $section['ID'])
						$intMaxSectionID = $section['ID'];
				}
				unset($section, $sectionIterator);
			}
		}
		else
		{
			$filter = array(
				'IBLOCK_ID' => $IBLOCK_ID,
				'GLOBAL_ACTIVE' => 'Y'
			);
			$sectionIterator = CIBlockSection::GetList(
				array('LEFT_MARGIN' => 'ASC'),
				array_merge($filter, $permissionFilter),
				false,
				array('ID', 'IBLOCK_SECTION_ID', 'NAME')
			);
			while ($section = $sectionIterator->Fetch())
			{
				$section['ID'] = (int)$section['ID'];
				$section['IBLOCK_SECTION_ID'] = (int)$section['IBLOCK_SECTION_ID'];
				$arAvailGroups[$section['ID']] = $section;
				$arSectionIDs[$section['ID']] = $section['ID'];
				if ($intMaxSectionID < $section['ID'])
					$intMaxSectionID = $section['ID'];
			}
			unset($section, $sectionIterator);
		}
		
		
		fclose($fp);

		$itemsFile = @fopen($_SERVER["DOCUMENT_ROOT"].$itemFileName, 'wb');
		if (!$itemsFile)
		{
			$arRunErrors[] = str_replace('#FILE#', $itemFileName, GetMessage('YANDEX_ERR_FILE_OPEN_WRITING'));
		}
	}
}
else
{
	$itemsFile = @fopen($_SERVER["DOCUMENT_ROOT"].$itemFileName, 'ab');
	if (!$itemsFile)
	{
		$arRunErrors[] = str_replace('#FILE#', $itemFileName, GetMessage('YANDEX_ERR_FILE_OPEN_WRITING'));
	}
}
unset($arSections);

if (empty($arRunErrors))
{
	//*****************************************//
	$saleDiscountOnly = false;
	if($moduleWorker->checkModule('sale', '17.5.0'))
	{
		$calculationConfig = [
			'CURRENCY' => $BASE_CURRENCY,
			'USE_DISCOUNTS' => true,
			'RESULT_WITH_VAT' => true,
			'RESULT_MODE' => Catalog\Product\Price\Calculation::RESULT_MODE_COMPONENT
		];
		if ($saleIncluded)
		{
			$saleDiscountOnly = (string)Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y';
			if ($saleDiscountOnly)
				$calculationConfig['PRECISION'] = (int)Main\Config\Option::get('sale', 'value_precision');
		}
		Catalog\Product\Price\Calculation::setConfig($calculationConfig);
		unset($calculationConfig);

		if ($selectedPriceType > 0)
		{
			$priceTypeList = array($selectedPriceType);
		}
		else
		{
			$priceTypeList = array();
			$priceIterator = Catalog\GroupAccessTable::getList(array(
				'select' => array('CATALOG_GROUP_ID'),
				'filter' => array('=GROUP_ID' => 2),
				'order' => array('CATALOG_GROUP_ID' => 'ASC')
			));
			while ($priceType = $priceIterator->fetch())
			{
				$priceTypeId = (int)$priceType['CATALOG_GROUP_ID'];
				$priceTypeList[$priceTypeId] = $priceTypeId;
				unset($priceTypeId);
			}
			unset($priceType, $priceIterator);
		}
	}else{
		$priceTypeList = array();
		$db_res = CCatalogGroup::GetGroupsList(array("GROUP_ID"=>2, "BUY"=>"Y"));
		while ($ar_res = $db_res->Fetch())
		{
			$priceTypeId = (int)$ar_res['CATALOG_GROUP_ID'];
			$priceTypeList[$priceTypeId] = $priceTypeId;
		}
	}

	$needDiscountCache = \CIBlockPriceTools::SetCatalogDiscountCache($priceTypeList, array(2), $site['LID']);

	$itemFields = array(
		'ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME',
		'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_TEXT_TYPE', 'DETAIL_PICTURE', 'DETAIL_PAGE_URL',
		'CATALOG_AVAILABLE', 'CATALOG_TYPE'
	);
	$offerFields = array(
		'ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME',
		'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_TEXT_TYPE', 'DETAIL_PICTURE', 'DETAIL_PAGE_URL'
	);

	$allowedTypes = array();
	switch ($arCatalog['CATALOG_TYPE'])
	{
		case CCatalogSku::TYPE_CATALOG:
			$allowedTypes = array(
				Catalog\ProductTable::TYPE_PRODUCT => true,
				Catalog\ProductTable::TYPE_SET => true
			);
			break;
		case CCatalogSku::TYPE_OFFERS:
			$allowedTypes = array(
				Catalog\ProductTable::TYPE_OFFER => true
			);
			break;
		case CCatalogSku::TYPE_FULL:
			$allowedTypes = array(
				Catalog\ProductTable::TYPE_PRODUCT => true,
				Catalog\ProductTable::TYPE_SET => true,
				Catalog\ProductTable::TYPE_SKU => true
			);
			break;
		case CCatalogSku::TYPE_PRODUCT:
			$allowedTypes = array(
				Catalog\ProductTable::TYPE_SKU => true
			);
			break;
	}

	$filter = array('IBLOCK_ID' => $IBLOCK_ID);
	if (!$bAllSections && !empty($arSectionIDs))
	{
		$filter['INCLUDE_SUBSECTIONS'] = 'Y';
		$filter['SECTION_ID'] = $arSectionIDs;
	}
	$filter['ACTIVE'] = 'Y';
	$filter['ACTIVE_DATE'] = 'Y';
	if ($filterAvailable)
		$filter['CATALOG_AVAILABLE'] = 'Y';
	$filter = array_merge($filter, $permissionFilter);

	$offersFilter = array('ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y');
	if ($filterAvailable)
		$offersFilter['CATALOG_AVAILABLE'] = 'Y';
	$offersFilter = array_merge($offersFilter, $permissionFilter);

	if (isset($allowedTypes[Catalog\ProductTable::TYPE_SKU]))
	{
		if ($arSKUExport['SKU_EXPORT_COND'] == YANDEX_SKU_EXPORT_PROP)
		{
			$strExportKey = '';
			$mxValues = false;
			if ($arSKUExport['SKU_PROP_COND']['COND'] == 'NONZERO' || $arSKUExport['SKU_PROP_COND']['COND'] == 'NONEQUAL')
				$strExportKey = '!';
			$strExportKey .= 'PROPERTY_'.$arSKUExport['SKU_PROP_COND']['PROP_ID'];
			if ($arSKUExport['SKU_PROP_COND']['COND'] == 'EQUAL' || $arSKUExport['SKU_PROP_COND']['COND'] == 'NONEQUAL')
				$mxValues = $arSKUExport['SKU_PROP_COND']['VALUES'];
			$offersFilter[$strExportKey] = $mxValues;
		}
	}

	do
	{
		if (isset($CUR_ELEMENT_ID) && $CUR_ELEMENT_ID > 0)
			$filter['>ID'] = $CUR_ELEMENT_ID;

		$existItems = false;

		$itemIdsList = array();
		$items = array();

		$skuIdsList = array();
		$simpleIdsList = array();
		
		$iterator = CIBlockElement::GetList(
			array('ID' => 'ASC'),
			$filter,
			false,
			$navParams,
			$itemFields
		);
		while ($row = $iterator->Fetch())
		{
			$finalExport = false; // items exist
			$existItems = true;

			$id = (int)$row['ID'];
			$CUR_ELEMENT_ID = $id;

			$row['CATALOG_TYPE'] = (int)$row['CATALOG_TYPE'];
			$elementType = $row['CATALOG_TYPE'];
			if (!isset($allowedTypes[$elementType]))
				continue;

			$row['SECTIONS'] = array();
			if ($needProperties || $needDiscountCache)
				$row['PROPERTIES'] = array();
			$row['PRICES'] = array();

			$items[$id] = $row;
			$itemIdsList[$id] = $id;

			if ($elementType == Catalog\ProductTable::TYPE_SKU)
				$skuIdsList[$id] = $id;
			else
				$simpleIdsList[$id] = $id;
		}
		unset($row, $iterator);

		if (!empty($items))
		{
			$moduleWorker->PrepareProducts($items, array(), $itemOptions);

			/* foreach (array_chunk($itemIdsList, 500) as $pageIds)
			{
				$iterator = Iblock\SectionElementTable::getList(array(
					'select' => array('IBLOCK_ELEMENT_ID', 'IBLOCK_SECTION_ID'),
					'filter' => array('@IBLOCK_ELEMENT_ID' => $pageIds, '==ADDITIONAL_PROPERTY_ID' => null),
					'order' => array('IBLOCK_ELEMENT_ID' => 'ASC')
				));
				while ($row = $iterator->fetch())
				{
					$id = (int)$row['IBLOCK_ELEMENT_ID'];
					$sectionId = (int)$row['IBLOCK_SECTION_ID'];
					$items[$id]['SECTIONS'][$sectionId] = $sectionId;
					unset($sectionId, $id);
				}
				unset($row, $iterator);
			}
			unset($pageIds); */

			if ($needProperties || $needDiscountCache)
			{
				if (!empty($propertyIdList))
				{
					\CIBlockElement::GetPropertyValuesArray(
						$items,
						$IBLOCK_ID,
						array(
							'ID' => $itemIdsList,
							'IBLOCK_ID' => $IBLOCK_ID
						),
						array('ID' => $propertyIdList),
						array('USE_PROPERTY_ID' => 'Y', 'PROPERTY_FIELDS' => $propertyFields)
					);
				}

				if ($needDiscountCache)
				{
					foreach ($itemIdsList as $id)
						\CCatalogDiscount::SetProductPropertiesCache($id, $items[$id]['PROPERTIES']);
					unset($id);
				}

				if (!$needProperties)
				{
					foreach ($itemIdsList as $id)
						$items[$id]['PROPERTIES'] = array();
					unset($id);
				}
				else
				{
					foreach ($itemIdsList as $id)
					{
						if (empty($items[$id]['PROPERTIES']))
							continue;
						foreach (array_keys($items[$id]['PROPERTIES']) as $index)
						{
							$propertyId = $items[$id]['PROPERTIES'][$index]['ID'];
							if (!isset($yandexNeedPropertyIds[$propertyId]))
								unset($items[$id]['PROPERTIES'][$index]);
						}
						unset($propertyId, $index);
					}
					unset($id);
				}
			}

			if ($needDiscountCache)
			{
				\CCatalogDiscount::SetProductSectionsCache($itemIdsList);
				\CCatalogDiscount::SetDiscountProductCache($itemIdsList, array('IBLOCK_ID' => $IBLOCK_ID, 'GET_BY_ID' => 'Y'));
			}

			if (!empty($skuIdsList))
			{
				$offerPropertyFilter = array();
				if ($needProperties || $needDiscountCache)
				{
					if (!empty($propertyIdList))
						$offerPropertyFilter = array('ID' => $propertyIdList);
				}

				$offers = \CCatalogSku::getOffersList(
					$skuIdsList,
					$IBLOCK_ID,
					$offersFilter,
					$offerFields,
					$offerPropertyFilter,
					array('USE_PROPERTY_ID' => 'Y', 'PROPERTY_FIELDS' => $propertyFields)
				);
				unset($offerPropertyFilter);

				if (!empty($offers))
				{
					$offerLinks = array();
					$offerIdsList = array();
					$parentsUrl = array();
					foreach (array_keys($offers) as $productId)
					{
						unset($skuIdsList[$productId]);
						$items[$productId]['OFFERS'] = array();
						$parentsUrl[$productId] = $items[$productId]['DETAIL_PAGE_URL'];
						foreach (array_keys($offers[$productId]) as $offerId)
						{
							$productOffer = $offers[$productId][$offerId];

							$productOffer['PRICES'] = array();
							if ($needDiscountCache)
								\CCatalogDiscount::SetProductPropertiesCache($offerId, $productOffer['PROPERTIES']);
							if (!$needProperties)
							{
								$productOffer['PROPERTIES'] = array();
							}
							else
							{
								if (!empty($productOffer['PROPERTIES']))
								{
									foreach (array_keys($productOffer['PROPERTIES']) as $index)
									{
										$propertyId = $productOffer['PROPERTIES'][$index]['ID'];
										if (!isset($yandexNeedPropertyIds[$propertyId]))
											unset($productOffer['PROPERTIES'][$index]);
									}
									unset($propertyId, $index);
								}
							}
							$items[$productId]['OFFERS'][$offerId] = $productOffer;
							unset($productOffer);

							$offerLinks[$offerId] = &$items[$productId]['OFFERS'][$offerId];
							$offerIdsList[$offerId] = $offerId;
						}
						unset($offerId);
					}
					if (!empty($offerIdsList))
					{
						$moduleWorker->PrepareProducts($offerLinks, $parentsUrl, $itemOptions);

						foreach (array_chunk($offerIdsList, 500) as $pageIds)
						{
							if ($needDiscountCache)
							{
								\CCatalogDiscount::SetProductSectionsCache($pageIds);
								\CCatalogDiscount::SetDiscountProductCache(
									$pageIds,
									array('IBLOCK_ID' => $arCatalog['IBLOCK_ID'], 'GET_BY_ID' => 'Y')
								);
							}

							if (!$filterAvailable)
							{
								$iterator = Catalog\ProductTable::getList(array(
									'select' => ($vatExport ? array('ID', 'AVAILABLE', 'VAT_ID', 'VAT_INCLUDED') : array('ID', 'AVAILABLE')),
									'filter' => array('@ID' => $pageIds)
								));
								while ($row = $iterator->fetch())
								{
									$id = (int)$row['ID'];
									$offerLinks[$id]['CATALOG_AVAILABLE'] = $row['AVAILABLE'];
									if ($vatExport)
									{
										$row['VAT_ID'] = (int)$row['VAT_ID'];
										$offerLinks[$id]['CATALOG_VAT_ID'] = ($row['VAT_ID'] > 0 ? $row['VAT_ID'] : $offersCatalog['VAT_ID']);
										$offerLinks[$id]['CATALOG_VAT_INCLUDED'] = $row['VAT_INCLUDED'];
									}
								}
								unset($id, $row, $iterator);
							}

							// load vat cache
							/* $vatList = CCatalogProduct::GetVATDataByIDList($pageIds);
							unset($vatList); */

							$priceFilter = [
								'@PRODUCT_ID' => $pageIds,
								[
									'LOGIC' => 'OR',
									'<=QUANTITY_FROM' => 1,
									'=QUANTITY_FROM' => null
								],
								[
									'LOGIC' => 'OR',
									'>=QUANTITY_TO' => 1,
									'=QUANTITY_TO' => null
								]
							];
							if ($selectedPriceType > 0)
								$priceFilter['=CATALOG_GROUP_ID'] = $selectedPriceType;
							else
								$priceFilter['@CATALOG_GROUP_ID'] = $priceTypeList;

							$iterator = Catalog\PriceTable::getList([
								'select' => ['ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY'],
								'filter' => $priceFilter
							]);

							while ($price = $iterator->fetch())
							{
								$id = (int)$price['PRODUCT_ID'];
								$priceTypeId = (int)$price['CATALOG_GROUP_ID'];
								$offerLinks[$id]['PRICES'][$priceTypeId] = $price;
								unset($priceTypeId, $id);
							}
							unset($price, $iterator);

							if ($saleDiscountOnly)
							{
								Catalog\Discount\DiscountManager::preloadPriceData(
									$pageIds,
									($selectedPriceType > 0 ? [$selectedPriceType] : $priceTypeList)
								);
							}
						}
						unset($pageIds);
					}
					unset($parentsUrl, $offerIdsList, $offerLinks);
				}
				unset($offers);

				if (!empty($skuIdsList))
				{
					foreach ($skuIdsList as $id)
					{
						unset($items[$id]);
						unset($itemIdsList[$id]);
					}
					unset($id);
				}
			}

			if (!empty($simpleIdsList))
			{
				foreach (array_chunk($simpleIdsList, 500) as $pageIds)
				{
					// load vat cache
					/* $vatList = CCatalogProduct::GetVATDataByIDList($pageIds);
					unset($vatList); */

					$priceFilter = [
						'@PRODUCT_ID' => $pageIds,
						[
							'LOGIC' => 'OR',
							'<=QUANTITY_FROM' => 1,
							'=QUANTITY_FROM' => null
						],
						[
							'LOGIC' => 'OR',
							'>=QUANTITY_TO' => 1,
							'=QUANTITY_TO' => null
						]
					];
					if ($selectedPriceType > 0)
						$priceFilter['=CATALOG_GROUP_ID'] = $selectedPriceType;
					else
						$priceFilter['@CATALOG_GROUP_ID'] = $priceTypeList;

					$iterator = Catalog\PriceTable::getList([
						'select' => ['ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY'],
						'filter' => $priceFilter
					]);

					while ($price = $iterator->fetch())
					{
						$id = (int)$price['PRODUCT_ID'];
						$priceTypeId = (int)$price['CATALOG_GROUP_ID'];
						$items[$id]['PRICES'][$priceTypeId] = $price;
						unset($priceTypeId, $id);
					}
					unset($price, $iterator);

					if ($saleDiscountOnly)
					{
						Catalog\Discount\DiscountManager::preloadPriceData(
							$pageIds,
							($selectedPriceType > 0 ? [$selectedPriceType] : $priceTypeList)
						);
					}
				}
				unset($pageIds);
			}
		}
		
		$itemsContent = '';
		if (!empty($items))
		{
			foreach ($itemIdsList as $id)
			{
				$CUR_ELEMENT_ID = $id;

				$row = $items[$id];

				/* if (!empty($row['SECTIONS']))
				{
					foreach ($row['SECTIONS'] as $sectionId)
					{
						if (!isset($arAvailGroups[$sectionId]))
							continue;
						$row['CATEGORY_ID'] = $sectionId;
					}
					unset($sectionId);
				}
				else
				{
					$row['CATEGORY_ID'] = $intMaxSectionID;
				}
				if (!isset($row['CATEGORY_ID']))
					continue; */

				if ($row['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_SKU && !empty($row['OFFERS']))
				{
					$minOfferId = null;
					$minOfferPrice = null;

					foreach (array_keys($row['OFFERS']) as $offerId)
					{
						if (empty($row['OFFERS'][$offerId]['PRICES']))
						{
							unset($row['OFFERS'][$offerId]);
							continue;
						}

						$fullPrice = 0;
						$minPrice = 0;
						$minPriceCurrency = '';

						$calculatePrice = CCatalogProduct::GetOptimalPrice(
							$row['OFFERS'][$offerId]['ID'],
							1,
							array(2),
							'N',
							$row['OFFERS'][$offerId]['PRICES'],
							$site['LID'],
							array()
						);

						if (!empty($calculatePrice))
						{
							$minPrice = $calculatePrice['RESULT_PRICE']['DISCOUNT_PRICE'];
							$fullPrice = $calculatePrice['RESULT_PRICE']['BASE_PRICE'];
							$minPriceCurrency = $calculatePrice['RESULT_PRICE']['CURRENCY'];
						}
						unset($calculatePrice);
						if ($minPrice <= 0)
						{
							unset($row['OFFERS'][$offerId]);
							continue;
						}
						$row['OFFERS'][$offerId]['RESULT_PRICE'] = array(
							'MIN_PRICE' => $minPrice,
							'FULL_PRICE' => $fullPrice,
							'CURRENCY' => $minPriceCurrency
						);
						if ($minOfferPrice === null || $minOfferPrice > $minPrice)
						{
							$minOfferId = $offerId;
							$minOfferPrice = $minPrice;
						}
					}
					unset($offerId);

					if ($arSKUExport['SKU_EXPORT_COND'] == YANDEX_SKU_EXPORT_MIN_PRICE)
					{
						if ($minOfferId === null)
							$row['OFFERS'] = array();
						else
							$row['OFFERS'] = array($minOfferId => $row['OFFERS'][$minOfferId]);
					}
					if (empty($row['OFFERS']))
						continue;
					
					foreach ($row['OFFERS'] as $offer)
					{
						$offer['DETAIL_TEXT'] = str_replace(array(chr(13), chr(10), chr(9)),' ', $offer['DETAIL_TEXT']);
						$row['DETAIL_TEXT'] = str_replace(array(chr(13), chr(10), chr(9)),' ', $row['DETAIL_TEXT']);
						$picture = (!empty($offer['PICTURE']) ? $offer['PICTURE'] : $row['PICTURE']);
						
						if($HIDE_WITHOT_PICTURES == "Y" && $row['DETAIL_TEXT'] == '' && $offer['DETAIL_TEXT'] == '')
							continue;
						
						if($HIDE_WITHOT_PICTURES == "Y" && empty($picture))
							continue;
						
						$itemsContent .= "<item>\n";
							$itemsContent .= "<title>".$moduleWorker->text2xml($offer['NAME'], true)."</title>\n";
							
							$itemsContent .= "<link>".$usedProtocol.$site['SERVER_NAME'].htmlspecialcharsbx($offer['DETAIL_PAGE_URL'])."</link>\n";
							
							$itemsContent .= "<description><![CDATA[".$APPLICATION->ConvertCharset(htmlspecialcharsbx(($offer['DETAIL_TEXT'] !== '' ? substr(strip_tags($offer['DETAIL_TEXT']), 0, 950) : substr(strip_tags($row['DETAIL_TEXT']), 0, 950))), LANG_CHARSET, 'utf-8')."]]></description>\n";
							
							$itemsContent .= "<g:id>".$offer['ID']."</g:id>\n";
							
							if($row["IBLOCK_SECTION_ID"])
								$itemsContent .= "<g:item_group_id>".$row["IBLOCK_SECTION_ID"]."</g:item_group_id>\n";
							
							
							$minPrice = $offer['RESULT_PRICE']['MIN_PRICE'];
							$fullPrice = $offer['RESULT_PRICE']['FULL_PRICE'];
							if ($minPrice < $fullPrice){
								$itemsContent .= "<g:price>".$fullPrice." ".$offer['RESULT_PRICE']['CURRENCY']."</g:price>\n";
								$itemsContent .= "<g:sale_price>".$minPrice." ".$offer['RESULT_PRICE']['CURRENCY']."</g:sale_price>\n";
							}else{
								$itemsContent .= "<g:price>".$minPrice." ".$offer['RESULT_PRICE']['CURRENCY']."</g:price>\n";
							}
							
							$itemsContent .= "<g:availability>".($offer['CATALOG_AVAILABLE'] == 'Y' ? 'in stock' : 'preorder')."</g:availability>\n";
							
							
							if (!empty($picture))
								$itemsContent .= "<g:image_link>".$picture."</g:image_link>\n";
							unset($picture);
							
							$identifier_exists = 0;
							foreach ($formatList["google"] as $key)
							{
								switch ($key)
								{
									case 'additional_image_link':
										if ($fieldsExist && isset($fields[$key]))
										{
											if($arProperties[$fields[$key]]["PROPERTY_TYPE"] == 'F')
											{
												$value = $moduleWorker->getParam(
													$offer,
													$key,
													$fields[$key],
													$arProperties,
													$arUserTypeFormat,
													$usedProtocol
												);
												if ($value == '')
												{
													$value = $moduleWorker->getParam(
														$row,
														$key,
														$fields[$key],
														$arProperties,
														$arUserTypeFormat,
														$usedProtocol
													);
												}
												if ($value != '')
													$itemsContent .= $value;
												unset($value);
											}
										}
									break;
									case 'condition':
										if ($fieldsExist && isset($fields[$key]))
										{
											$value = $moduleWorker->getParam(
												$offer,
												$key,
												$fields[$key],
												$arProperties,
												$arUserTypeFormat,
												$usedProtocol
											);
											if ($value == '')
											{
												$value = $moduleWorker->getParam(
													$row,
													$key,
													$fields[$key],
													$arProperties,
													$arUserTypeFormat,
													$usedProtocol
												);
											}
												echo '<pre>'; var_dump($value); echo '</pre>';
											if ($value != '')
												$itemsContent .= $value."\n";
											else
												$itemsContent .= "<g:condition>new</g:condition>\n";
											unset($value);
										}
										else
											$itemsContent .= "<g:condition>new</g:condition>\n";
									break;
									default:
									if ($fieldsExist && isset($fields[$key]))
									{
										$value = $moduleWorker->getParam(
											$offer,
											$key,
											$fields[$key],
											$arProperties,
											$arUserTypeFormat,
											$usedProtocol
										);
										if ($value == '')
										{
											$value = $moduleWorker->getParam(
												$row,
												$key,
												$fields[$key],
												$arProperties,
												$arUserTypeFormat,
												$usedProtocol
											);
										}
										if ($value != '')
										{
											if($key == 'brand' || $key == 'gtin' || $key == 'mpn')
												$identifier_exists = 1;
												
											$itemsContent .= $value."\n";
										}
										unset($value);
									}
								}
							}
							
							if(!$identifier_exists)
								$itemsContent .= "<g:identifier_exists>no</g:identifier_exists>\n";
								
							
						$itemsContent .= '</item>'."\n";
					}
					unset($offer);
				}
				elseif (isset($simpleIdsList[$id]) && !empty($row['PRICES']))
				{
					$row['CATALOG_VAT_ID'] = (int)$row['CATALOG_VAT_ID'];
					if ($row['CATALOG_VAT_ID'] == 0)
						$row['CATALOG_VAT_ID'] = $arCatalog['VAT_ID'];

					$fullPrice = 0;
					$minPrice = 0;
					$minPriceCurrency = '';

					$calculatePrice = CCatalogProduct::GetOptimalPrice(
						$row['ID'],
						1,
						array(2),
						'N',
						$row['PRICES'],
						$site['LID'],
						array()
					);

					if (!empty($calculatePrice))
					{
						$minPrice = $calculatePrice['RESULT_PRICE']['DISCOUNT_PRICE'];
						$fullPrice = $calculatePrice['RESULT_PRICE']['BASE_PRICE'];
						$minPriceCurrency = $calculatePrice['RESULT_PRICE']['CURRENCY'];
					}
					unset($calculatePrice);

					if ($minPrice <= 0)
						continue;
					
					$row['DETAIL_TEXT'] = str_replace(array(chr(13), chr(10), chr(9)),' ', $row['DETAIL_TEXT']);
					$picture = (!empty($offer['PICTURE']) ? $offer['PICTURE'] : $row['PICTURE']);
					
					if($HIDE_WITHOT_PICTURES == "Y" && $row['DETAIL_TEXT'] =='')
						continue;
					
					if($HIDE_WITHOT_PICTURES == "Y" && empty($picture))
						continue;
					
					
					$itemsContent .= "<item>\n";
						$itemsContent .= "<title>".$moduleWorker->text2xml($row['NAME'], true)."</title>\n";
						
						$itemsContent .= "<link>".$usedProtocol.$site['SERVER_NAME'].htmlspecialcharsbx($row['DETAIL_PAGE_URL'])."</link>\n";
						
						$itemsContent .= "<description><![CDATA[".$APPLICATION->ConvertCharset(htmlspecialcharsbx(substr(strip_tags($row['DETAIL_TEXT']), 0, 950)), LANG_CHARSET, 'utf-8')."]]></description>\n";
						
						$itemsContent .= "<g:id>".$row['ID']."</g:id>\n";
						
						if($row["IBLOCK_SECTION_ID"])
							$itemsContent .= "<g:item_group_id>".$row["IBLOCK_SECTION_ID"]."</g:item_group_id>\n";
						
						if ($minPrice < $fullPrice){
							$itemsContent .= "<g:price>".$fullPrice." ".$minPriceCurrency."</g:price>\n";
							$itemsContent .= "<g:sale_price>".$minPrice." ".$minPriceCurrency."</g:sale_price>\n";
						}else{
							$itemsContent .= "<g:price>".$minPrice." ".$minPriceCurrency."</g:price>\n";
						}
						
						$itemsContent .= "<g:availability>".($row['CATALOG_AVAILABLE'] == 'Y' ? 'in stock' : 'preorder')."</g:availability>\n";
						
						if (!empty($row['PICTURE']))
							$itemsContent .= "<g:image_link>".$row['PICTURE']."</g:image_link>\n";
						
						$identifier_exists = 0;
						foreach ($formatList["google"] as $key)
						{
							switch ($key)
							{
								case 'additional_image_link':
									if ($fieldsExist && isset($fields[$key]))
									{
										if($arProperties[$fields[$key]]["PROPERTY_TYPE"] == 'F')
										{
											$value = $moduleWorker->getParam(
												$row,
												$key,
												$fields[$key],
												$arProperties,
												$arUserTypeFormat,
												$usedProtocol
											);
											if ($value != '')
												$itemsContent .= $value;
											unset($value);
										}
									}
								break;
								case 'condition':
									if ($fieldsExist && isset($fields[$key]))
									{
										$value = $moduleWorker->getParam(
											$row,
											$key,
											$fields[$key],
											$arProperties,
											$arUserTypeFormat,
											$usedProtocol
										);
										echo '<pre>'; var_dump($value); echo '</pre>';
										
										if ($value != '')
											$itemsContent .= $value."\n";
										else
											$itemsContent .= "<g:condition>new</g:condition>\n";
										unset($value);
									}
									else
										$itemsContent .= "<g:condition>new</g:condition>\n";
								break;
								default:
								if ($fieldsExist && isset($fields[$key]))
								{
									$value = $moduleWorker->getParam(
										$row,
										$key,
										$fields[$key],
										$arProperties,
										$arUserTypeFormat,
										$usedProtocol
									);
									if ($value != '')
									{
										if($key == 'brand' || $key == 'gtin' || $key == 'mpn')
											$identifier_exists = 1;
										$itemsContent .= $value."\n";
									}
									unset($value);
								}
							}
						}
						
						if(!$identifier_exists)
							$itemsContent .= "<g:identifier_exists>no</g:identifier_exists>\n";
						
					$itemsContent .= "</item>\n";
				}

				unset($row);

				if ($MAX_EXECUTION_TIME > 0 && (getmicrotime() - START_EXEC_TIME) >= $MAX_EXECUTION_TIME)
					break;
			}
			unset($id);

			\CCatalogDiscount::ClearDiscountCache(array(
				'PRODUCT' => true,
				'SECTIONS' => true,
				'SECTION_CHAINS' => true,
				'PROPERTIES' => true
			));
			\CCatalogProduct::ClearCache();
		}

		if ($itemsContent !== '')
			fwrite($itemsFile, $itemsContent);
		unset($itemsContent);

		unset($simpleIdsList, $skuIdsList);
		unset($items, $itemIdsList);
	}
	while ($MAX_EXECUTION_TIME == 0 && $existItems);
}

if (empty($arRunErrors))
{
	if (is_resource($itemsFile))
		@fclose($itemsFile);
	unset($itemsFile);
}

if (empty($arRunErrors))
{
	if ($MAX_EXECUTION_TIME == 0)
		$finalExport = true;
	if ($finalExport)
	{
		$process = true;
		$content = '';

		$items = file_get_contents($_SERVER["DOCUMENT_ROOT"].$itemFileName);
		if ($items === false)
		{
			$arRunErrors[] = GetMessage('YANDEX_STEP_ERR_DATA_FILE_NOT_READ');
			$process = false;
		}

		if ($process)
		{
			$content .= $items;
			unset($items);
			$content .= "</channel>\n"."</rss>\n";

			if (file_put_contents($_SERVER["DOCUMENT_ROOT"].$sectionFileName, $content, FILE_APPEND) === false)
			{
				$arRunErrors[] = str_replace('#FILE#', $sectionFileName, GetMessage('YANDEX_ERR_SETUP_FILE_WRITE'));
				$process = false;
			}
		}
		if ($process)
		{
			unlink($_SERVER["DOCUMENT_ROOT"].$itemFileName);

			if (file_exists($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME))
			{
				if (!unlink($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME))
				{
					$arRunErrors[] = str_replace('#FILE#', $SETUP_FILE_NAME, GetMessage('BX_CATALOG_EXPORT_YANDEX_ERR_UNLINK_FILE'));
					$process = false;
				}
			}
		}
		if ($process)
		{
			if (!rename($_SERVER["DOCUMENT_ROOT"].$sectionFileName, $_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME))
			{
				$arRunErrors[] = str_replace('#FILE#', $sectionFileName, GetMessage('BX_CATALOG_EXPORT_YANDEX_ERR_UNLINK_FILE'));
			}
		}
		unset($process);
	}
}

CCatalogDiscountSave::Enable();
if ($saleIncluded && $moduleWorker->checkModule('sale', '16.5.0'))
	Sale\DiscountCouponsManager::unFreezeCouponStorage();

if (!empty($arRunErrors))
	$strExportErrorMessage = implode('<br />',$arRunErrors);

if ($bTmpUserCreated)
{
	if (isset($USER_TMP))
	{
		$USER = $USER_TMP;
		unset($USER_TMP);
	}
}