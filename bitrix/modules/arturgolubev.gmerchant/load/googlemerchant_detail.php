<?
// v1.6.1
use Bitrix\Iblock,
	Bitrix\Currency;

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.gmerchant/load/functions_googlemerchant_detail.php");

// IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/export_yandex.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/arturgolubev.gmerchant/export_google.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if (!check_bitrix_sessid())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$APPLICATION->SetTitle(GetMessage('YANDEX_DETAIL_TITLE'));

CModule::IncludeModule('catalog');

if (!$USER->CanDoOperation('catalog_export_edit'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage('YANDEX_ERR_NO_ACCESS_EXPORT'));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if ((!isset($_REQUEST['IBLOCK_ID'])) || (0 == strlen($_REQUEST['IBLOCK_ID'])))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("YANDEX_ERR_NO_IBLOCK_CHOSEN"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
$intIBlockID = $_REQUEST['IBLOCK_ID'];
$intIBlockIDCheck = intval($intIBlockID);
if ($intIBlockIDCheck.'|' != $intIBlockID.'|' || $intIBlockIDCheck <= 0)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("YANDEX_ERR_NO_IBLOCK_CHOSEN"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
else
{
	$intIBlockID = $intIBlockIDCheck;
	unset($intIBlockIDCheck);
}

$strPerm = 'D';
$rsIBlocks = CIBlock::GetByID($intIBlockID);
if (($arIBlock = $rsIBlocks->Fetch()))
{
	$bBadBlock = !CIBlockRights::UserHasRightTo($intIBlockID, $intIBlockID, "iblock_admin_display");
	if ($bBadBlock)
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		ShowError(GetMessage('YANDEX_ERR_NO_ACCESS_IBLOCK'));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}
else
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(str_replace('#ID#',$intIBlockID,GetMessage("YANDEX_ERR_NO_IBLOCK_FOUND_EXT")));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}


$boolOffers = false;
$arOffers = false;
$arOfferIBlock = false;
$intOfferIBlockID = 0;
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

$arOffers = CCatalogSKU::GetInfoByProductIBlock($intIBlockID);
if (!empty($arOffers['IBLOCK_ID']))
{
	$intOfferIBlockID = $arOffers['IBLOCK_ID'];
	$strPerm = 'D';
	$rsOfferIBlocks = CIBlock::GetByID($intOfferIBlockID);
	if ($arOfferIBlock = $rsOfferIBlocks->Fetch())
	{
		$bBadBlock = !CIBlockRights::UserHasRightTo($intOfferIBlockID, $intOfferIBlockID, "iblock_admin_display");
		if ($bBadBlock)
		{
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
			ShowError(GetMessage('YANDEX_ERR_NO_ACCESS_IBLOCK_SKU'));
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
			die();
		}
	}
	else
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		ShowError(str_replace('#ID#',$intIBlockID,GetMessage("YANDEX_ERR_NO_IBLOCK_SKU_FOUND")));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
	$boolOffers = true;

}
$arCondSelectProp = array(
	'ZERO' => GetMessage('YANDEX_SKU_EXPORT_PROP_SELECT_ZERO'),
	'NONZERO' => GetMessage('YANDEX_SKU_EXPORT_PROP_SELECT_NONZERO'),
	'EQUAL' => GetMessage('YANDEX_SKU_EXPORT_PROP_SELECT_EQUAL'),
	'NONEQUAL' => GetMessage('YANDEX_SKU_EXPORT_PROP_SELECT_NONEQUAL'),
);

$arIBlock["TEXT_FIELDS"] = array();
$arIBlock["TEXT_FIELDS"][] = 'TEXT_FIELD';

$arIBlock["SALE_FIELDS"] = array();
$arIBlock["SALE_FIELDS"][] = 'NAME_MAIN';
$arIBlock["SALE_FIELDS"][] = 'IBLOCK_SECTION_ID_MAIN';
$arIBlock["SALE_FIELDS"][] = 'IBLOCK_SECTION_NAME_MAIN';
$arIBlock["SALE_FIELDS"][] = 'PREVIEW_TEXT_MAIN';
$arIBlock["SALE_FIELDS"][] = 'DETAIL_TEXT_MAIN';
$arIBlock["SALE_FIELDS"][] = 'CATALOG_WEIGHT';
$arIBlock["SALE_FIELDS"][] = 'CATALOG_LENGTH';
$arIBlock["SALE_FIELDS"][] = 'CATALOG_HEIGHT';
$arIBlock["SALE_FIELDS"][] = 'CATALOG_WIDTH';



if($boolOffers) $arIBlock["SALE_SKU_FIELDS"] = array();
if($boolOffers) $arIBlock["SALE_SKU_FIELDS"][] = 'NAME_SKU';
if($boolOffers) $arIBlock["SALE_SKU_FIELDS"][] = 'PREVIEW_TEXT_SKU';
if($boolOffers) $arIBlock["SALE_SKU_FIELDS"][] = 'DETAIL_TEXT_SKU';



$googleConfig = array();

$googleConfig[] = 'title';
if($boolOffers) $googleConfig[] = 'title_sku';
$googleConfig[] = 'description';
if($boolOffers) $googleConfig[] = 'description_sku';

$googleConfig[] = 'condition';
$googleConfig[] = 'product_type';
$googleConfig[] = 'additional_image_link';
$googleConfig[] = 'brand';
$googleConfig[] = 'gtin';

if($boolOffers) $googleConfig[] = 'gtin_sku';

$googleConfig[] = 'mpn';
$googleConfig[] = 'mobile_link';
$googleConfig[] = 'google_product_category';
$googleConfig[] = 'color';
$googleConfig[] = 'gender';
$googleConfig[] = 'age_group';
$googleConfig[] = 'material';
$googleConfig[] = 'pattern';
$googleConfig[] = 'size';
$googleConfig[] = 'size_alternative_1';
$googleConfig[] = 'size_alternative_2';
$googleConfig[] = 'size_alternative_3';
$googleConfig[] = 'size_alternative_4';

$googleConfig[] = 'cost_of_goods_sold';
$googleConfig[] = 'unit_pricing_measure';
$googleConfig[] = 'unit_pricing_base_measure';
$googleConfig[] = 'shipping_weight';
$googleConfig[] = 'shipping_length';
$googleConfig[] = 'shipping_width';
$googleConfig[] = 'shipping_height';
$googleConfig[] = 'custom_label_0';
$googleConfig[] = 'custom_label_1';
$googleConfig[] = 'custom_label_2';
$googleConfig[] = 'custom_label_3';
$googleConfig[] = 'custom_label_4';

$googleConfig[] = 'utm_source';
$googleConfig[] = 'utm_medium';
$googleConfig[] = 'utm_campaign';
$googleConfig[] = 'utm_content';
$googleConfig[] = 'utm_term';

$arTypesConfig = array();
$arTypesConfig['google'] = $googleConfig;

$arTypesConfigKeys = array_keys($arTypesConfig);

$vatRates = array(
	'-' => GetMessage('YANDEX_BASE_VAT_EMPTY'),
	'0%' => '0%',
	'10%' => '10%',
	'18%' => '18%'
);

$defaultVatExport = array(
	'ENABLE' => 'N',
	'BASE_VAT' => ''
);

$dbRes = CIBlockProperty::GetList(
	array('SORT' => 'ASC'),
	array('IBLOCK_ID' => $intIBlockID, 'ACTIVE' => 'Y')
);
$arIBlock['PROPERTY'] = array();
$arIBlock['OFFERS_PROPERTY'] = array();
while ($arRes = $dbRes->Fetch())
{
	$arIBlock['PROPERTY'][$arRes['ID']] = $arRes;
}
if ($boolOffers)
{
	$rsProps = CIBlockProperty::GetList(array('SORT' => 'ASC'),array('IBLOCK_ID' => $intOfferIBlockID,'ACTIVE' => 'Y'));
	while ($arProp = $rsProps->Fetch())
	{
		if ($arOffers['SKU_PROPERTY_ID'] != $arProp['ID'])
		{
			if ($arProp['PROPERTY_TYPE'] == 'L')
			{
				$arProp['VALUES'] = array();
				$rsPropEnums = CIBlockProperty::GetPropertyEnum($arProp['ID'],array('sort' => 'asc'),array('IBLOCK_ID' => $intOfferIBlockID));
				while ($arPropEnum = $rsPropEnums->Fetch())
				{
					$arProp['VALUES'][$arPropEnum['ID']] = $arPropEnum['VALUE'];
				}
			}
			$arIBlock['OFFERS_PROPERTY'][$arProp['ID']] = $arProp;
			if (in_array($arProp['PROPERTY_TYPE'],$arSelectedPropTypes))
				$arSelectOfferProps[] = $arProp['ID'];
		}
	}
}


if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (!empty($_REQUEST['save']))
	{
		$arErrors = array();
		$arCurrency = array('RUB' => array('rate' => 1));
		if (is_array($_POST['CURRENCY']) && count($_POST['CURRENCY']) > 0)
		{
			$arCurrency = array();
			foreach ($_POST['CURRENCY'] as $CURRENCY)
			{
				$arCurrency[$CURRENCY] = array(
					'rate' => $_POST['CURRENCY_RATE'][$CURRENCY],
					'plus' => $_POST['CURRENCY_PLUS'][$CURRENCY]
				);
			}
		}

		$type = trim($_POST['type']);
		if ($type != 'google' && !in_array($type,$arTypesConfigKeys))
			$type = 'google';

		$addParams = array(
			'PARAMS' => array(),
		);
		if (isset($_POST['PARAMS_COUNT']) && intval($_POST['PARAMS_COUNT']) > 0)
		{
			$intCount = intval($_POST['PARAMS_COUNT']);
			if (isset($_POST['XML_DATA']['PARAMS']) && is_array($_POST['XML_DATA']['PARAMS']))
			{
				$arTempo = $_POST['XML_DATA']['PARAMS'];
				for ($i = 0; $i < $intCount; $i++)
				{
					if (empty($arTempo['ID_'.$i]))
						continue;
					$code = $arTempo['ID_'.$i];
					$symbol = $arTempo['ID_'.$i.'_symbol'];
					$value = implode(';', $arTempo['ID_'.$i.'_value']);
					if (array_key_exists($code,$arIBlock['PROPERTY']) || array_key_exists($code,$arIBlock['OFFERS_PROPERTY']))
					{
						$addParams['PARAMS'][] = $code.'|'.$symbol.'|'.$value;
					}
				}
			}
		}

		$arTypeParams = array();
		if (isset($_POST['XML_DATA'][$type]) && is_array($_POST['XML_DATA'][$type]))
		{
			$arTypeParams = $_POST['XML_DATA'][$type];
			foreach ($arTypeParams as $key => $value)
			{
				if (!in_array($key,$arTypesConfig[$type]))
				{
					unset($arTypeParams[$key]);
				}
				elseif (!array_key_exists($value,$arIBlock['PROPERTY']) && !array_key_exists($value,$arIBlock['OFFERS_PROPERTY']))
				{
					// $arTypeParams[$key] = '';
				}
			}
		}
		$XML_DATA = array_merge($arTypeParams, $addParams);

		foreach ($XML_DATA as $key => $value)
		{
			if (!$value)
				unset($XML_DATA[$key]);
		}

		$arSKUExport = false;
		if ($boolOffers)
		{
			$arSKUExport = array(
				'SKU_EXPORT_COND' => YANDEX_SKU_EXPORT_ALL,
				'SKU_PROP_COND' => array(
					'PROP_ID' => 0,
					'COND' => '',
					'VALUES' => array(),
				),
			);

			if (!empty($_POST['SKU_EXPORT_COND']) && in_array($_POST['SKU_EXPORT_COND'],$arOffersSelectKeys))
			{
				$arSKUExport['SKU_EXPORT_COND'] = $_POST['SKU_EXPORT_COND'];
			}
			else
			{
				$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_CONDITION_ABSENT');
			}
			if (YANDEX_SKU_EXPORT_PROP == $arSKUExport['SKU_EXPORT_COND'])
			{
				$boolCheck = true;
				$intPropID = 0;
				$strPropCond = '';
				$arPropValues = array();
				if (empty($_POST['SKU_PROP_COND']) || !in_array($_POST['SKU_PROP_COND'],$arSelectOfferProps))
				{
					$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_ABSENT');
					$boolCheck = false;
				}
				if ($boolCheck)
				{
					$intPropID = $_POST['SKU_PROP_COND'];
					if (empty($_POST['SKU_PROP_SELECT']) || empty($arCondSelectProp[$_POST['SKU_PROP_SELECT']]))
					{
						$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_COND_ABSENT');
						$boolCheck = false;
					}
				}
				if ($boolCheck)
				{
					$strPropCond = $_POST['SKU_PROP_SELECT'];
					if ($strPropCond == 'EQUAL' || $strPropCond == 'NONEQUAL')
					{
						if (!isset($_POST['SKU_PROP_VALUE_'.$intPropID]) || !is_array($_POST['SKU_PROP_VALUE_'.$intPropID]))
						{
							$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_VALUES_ABSENT');
							$boolCheck = false;
						}

						if ($boolCheck)
						{
							foreach($_POST['SKU_PROP_VALUE_'.$intPropID] as $strValue)
								if (strlen($strValue) > 0)
									$arPropValues[] = $strValue;
						}
						if (empty($arPropValues))
						{
							$arErrors[] = GetMessage('YANDEX_SKU_EXPORT_ERR_PROPERTY_VALUES_ABSENT');
							$boolCheck = false;
						}
					}
				}
				if ($boolCheck)
				{
					$arSKUExport['SKU_PROP_COND'] = array(
						'PROP_ID' => $intPropID,
						'COND' => $strPropCond,
						'VALUES' => $arPropValues,
					);
				}
			}
		}

		$vatExport = $defaultVatExport;
		if (isset($_POST['USE_VAT_EXPORT']) && is_string($_POST['USE_VAT_EXPORT']))
		{
			if ($_POST['USE_VAT_EXPORT'] == 'Y')
				$vatExport['ENABLE'] = 'Y';
			if ($vatExport['ENABLE'] == 'Y')
			{
				if (isset($_POST['BASE_VAT']) && is_string($_POST['BASE_VAT']) && $_POST['BASE_VAT'] !== '')
				{
					if (isset($vatRates[$_POST['BASE_VAT']]))
						$vatExport['BASE_VAT'] = $_POST['BASE_VAT'];
				}
				if ($vatExport['BASE_VAT'] === '')
				{
					$arErrors[] = GetMessage('YANDEX_VAT_ERR_BASE_VAT_ABSENT');
					$boolCheck = false;
				}
			}
		}
		
		$TEXT_FIELDS = array();
		foreach($_POST["TEXT_FIELDS"] as $groupId=>$fieldAr)
		{
			foreach($fieldAr as $fieldId=>$fieldVal)
			{
				if($fieldVal)
				{
					$TEXT_FIELDS[$groupId][$fieldId] = $fieldVal;
				}
			}
		}
		
		if (empty($arErrors))
		{
			$arXMLData = array(
				'TYPE' => $type,
				'XML_DATA' => $XML_DATA,
				'CURRENCY' => $arCurrency,
				'TEXT_FIELDS' => $TEXT_FIELDS,
				'CURRENCY_SELECT' => $_POST['CURRENCY_SELECT'],
				'PRICE' => intval($_POST['PRICE']),
				'F_PRICE_FROM' => intval($_POST['F_PRICE_FROM']),
				'F_PRICE_TO' => intval($_POST['F_PRICE_TO']),
				'SKU_EXPORT' => $arSKUExport,
				'VAT_EXPORT' => $vatExport
			);
			?>
				<script type="text/javascript">
				top.BX.closeWait();
				top.BX.WindowManager.Get().Close();
				top.setDetailData('<?=CUtil::JSEscape(base64_encode(serialize($arXMLData)));?>');
				</script>
			<?
			die();
		}
		else
		{
			$e = new CAdminException(array(array('text' => implode("\n",$arErrors))));
			$message = new CAdminMessage(GetMessage("YANDEX_SAVE_ERR"), $e);
			echo $message->Show();
		}
	}
	else
	{
		$aTabs = array(
			array("DIV" => "yandex-settings-types", "TAB" => GetMessage('YANDEX_TAB1_TITLE'), "TITLE" => GetMessage('AG_GM_TAB1_DESC')),
			array("DIV" => "yandex-settings-prices", "TAB" => GetMessage('AG_GM_TAB2_TITLE'), "TITLE" => GetMessage('AG_GM_TAB2_DESC')),
			// array("DIV" => "yandex-settings-vats", "TAB" => GetMessage('YANDEX_TAB_VAT_TITLE'), "TITLE" => GetMessage('YANDEX_TAB_VAT_DESC')),
		);
		$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		
		

		/***************************************************************************
		HTML form
		****************************************************************************/
		
		$type = 'google';
		$arTypeValues = array();
		foreach ($arTypesConfigKeys as $key)
		{
			$arTempo = array();
			foreach ($arTypesConfig[$key] as $value)
				$arTempo[$value] = '';
			$arTypeValues[$key] = $arTempo;
		}
		$arAddParams = array();
		$params = array(
			'PARAMS' => array(),
		);
		$PRICE = 0;
		$CURRENCY = array();
		$arSKUExport = array(
			'SKU_EXPORT_COND' => 0,
			'SKU_PROP_COND' => array(
				'PROP_ID' => 0,
				'COND' => '',
				'VALUES' => array(),
			),
		);
		$vatExport = $defaultVatExport;

		$arXmlData = array();
		if (!empty($_REQUEST['XML_DATA']))
		{
			$xmlData = base64_decode($_REQUEST['XML_DATA']);
			if (CheckSerializedData($xmlData))
				$arXmlData = unserialize($xmlData);
			unset($xmlData);
		}

		if (isset($arXmlData['PRICE']))
			$PRICE = (int)$arXmlData['PRICE'];
		
		if (isset($arXmlData['CURRENCY_SELECT']))
			$CURRENCY_SELECT = $arXmlData['CURRENCY_SELECT'];
		
		if (isset($arXmlData['F_PRICE_TO']))
			$F_PRICE_TO = (int)$arXmlData['F_PRICE_TO'];
		if (isset($arXmlData['F_PRICE_FROM']))
			$F_PRICE_FROM = (int)$arXmlData['F_PRICE_FROM'];
		if (isset($arXmlData['CURRENCY']))
			$CURRENCY = $arXmlData['CURRENCY'];
		if (isset($arXmlData['TYPE']))
			$type = $arXmlData['TYPE'];
		if ($type != 'google' && !in_array($type,$arTypesConfigKeys))
			$type = 'google';
		if (isset($arXmlData['XML_DATA']))
		{
			foreach ($arXmlData['XML_DATA'] as $key => $value)
			{
				if ($key == 'PARAMS')
				{
					$params[$key] = $value;
				}
				else
				{
					$arTypeValues[$type][$key] = $value;
				}
			}
		}
		if (is_array($params['PARAMS']) && !empty($params['PARAMS']))
		{
			foreach ($params['PARAMS'] as $strParam)
			{
				$arAddParams[] = array(
					'PARAM' => $strParam,
				);
			}
		}
		if (!empty($arXmlData['SKU_EXPORT']))
		{
			if (!empty($arXmlData['SKU_EXPORT']['SKU_EXPORT_COND']))
				$arSKUExport['SKU_EXPORT_COND'] = $arXmlData['SKU_EXPORT']['SKU_EXPORT_COND'];
			if (!empty($arXmlData['SKU_EXPORT']['SKU_PROP_COND']))
				$arSKUExport['SKU_PROP_COND'] = $arXmlData['SKU_EXPORT']['SKU_PROP_COND'];
		}
		if (!empty($arXmlData['VAT_EXPORT']) && is_array($arXmlData['VAT_EXPORT']))
		{
			$vatExport['ENABLE'] = $arXmlData['VAT_EXPORT']['ENABLE'];
			if ($vatExport['ENABLE'] != 'Y' && $vatExport['ENABLE'] != 'N')
				$vatExport['ENABLE'] = 'N';
			$vatExport['BASE_VAT'] = $arXmlData['VAT_EXPORT']['BASE_VAT'];
			if ($vatExport['BASE_VAT'] !== '' && !isset($vatRates[$vatExport['BASE_VAT']]))
				$vatExport['BASE_VAT'] = '';
		}

		?>
		<script type="text/javascript">
		var currentSelectedType = '<? echo $type; ?>';

		function switchType(type)
		{
			BX('config_' + currentSelectedType).style.display = 'none';
			currentSelectedType = type;
			BX('config_' + currentSelectedType).style.display = 'block';
		}
		</script>
		<form name="yandex_form" method="POST" action="<?=$APPLICATION->GetCurPage(); ?>">
			<input type="hidden" name="lang" value="<?=LANGUAGE_ID; ?>">
			<input type="hidden" name="bxpublic" value="Y">
			<input type="hidden" name="Update" value="Y" />
			<input type="hidden" name="IBLOCK_ID" value="<? echo $intIBlockID; ?>" />
			<? echo bitrix_sessid_post(); ?>
<?
		$tabControl->Begin();
		$tabControl->BeginNextTab();
?>
		<tr class="heading" style="display: none;">
			<td colspan="2"><?=GetMessage('YANDEX_TYPE')?></td>
		</tr>
		<tr style="display: none;">
			<td colspan="2" style="text-align: center;">
				<select name="type" onchange="switchType(this[this.selectedIndex].value)">
					<?
							foreach ($arTypesConfigKeys as $key)
							{
								if ('google' != $key)
								{
									?><option value="<?=$key?>"<? echo ($type == $key ? ' selected' : ''); ?>><?=$key?></option><?
								}
								else
								{
									?><option value="google" selected><?=GetMessage('YANDEX_TYPE_SIMPLE');?></option><?
								}
							}
					?>
				</select>
			</td>
		</tr>
		<?/* <tr>
			<td colspan="2" style="text-align: center;">
				<?echo BeginNote(), GetMessage('YANDEX_TYPE_NOTE'), EndNote();?>
			</td>
		</tr> */?>
		
		
		
		
		<?/* <tr class="heading">
			<td colspan="2" valign="top"><?=GetMessage('YANDEX_PROPS_ADDITIONAL')?></td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="config_param" style="padding: 10px auto; text-align: center;">
				<table class="inner" id="yandex_params_tbl" style="text-align: center; margin: 0 auto;">
					<thead>
					<tr><td style="text-align: center;"> </td>
					<td style="text-align: center;"><? echo GetMessage('YANDEX_PARAMS_TITLE'); ?></td>
					</tr>
					</thead>
					<tbody>
						<?
						$intCount = 0;
						foreach ($arAddParams as $arParamDetail)
						{
							echo __addParamRow($arIBlock, $intCount, $arParamDetail['PARAM'], '');
							$intCount++;
						}
						if ($intCount == 0)
						{
							echo __addParamRow($arIBlock, $intCount, '', '');
							$intCount++;
						}
						?>
					</tbody>
				</table>
				<input type="hidden" name="PARAMS_COUNT" id="PARAMS_COUNT" value="<? echo $intCount; ?>">
				<div style="width: 100%; text-align: center;"><input type="button" onclick="__addYP(); return false;" name="yandex_params_add" value="<? echo GetMessage('YANDEX_PROPS_ADDITIONAL_MORE'); ?>"></div>
				</div>
			<script type="text/javascript">
			function changeVatExport()
			{
				var vatRates = BX('tr_BASE_VAT');

				if (!BX.type.isElementNode(vatRates))
					return;
				BX.style(vatRates, 'display', (this.checked ? 'table-row' : 'none'));
			}

			BX.ready(function(){
				var vatRates = BX('tr_BASE_VAT'),
					vatEnable = BX('USE_VAT_EXPORT');

				if (BX.type.isElementNode(vatRates) && BX.type.isElementNode(vatEnable))
					BX.bind(vatEnable, 'click', changeVatExport);

					setTimeout(function(){
						window.oParamSet = {
							pTypeTbl: BX("yandex_params_tbl"),
							curCount: <? echo ($intCount); ?>,
							intCounter: BX("PARAMS_COUNT")
						};
					},50);
			});

			function __addYP()
			{
				var id = window.oParamSet.curCount++,
					newRow,
					oCell,
					strContent;
				id = id.toString();
				window.oParamSet.intCounter.value = window.oParamSet.curCount;
				newRow = window.oParamSet.pTypeTbl.insertRow(window.oParamSet.pTypeTbl.rows.length);
				newRow.id = 'yandex_params_tbl_'+id;

				oCell = newRow.insertCell(-1);
				oCell.style.textAlign = 'center';
				strContent = '<? echo CUtil::JSEscape(__addParamCode()); ?>';
				strContent = strContent.replace(/tmp_xxx/ig, id);
				oCell.innerHTML = strContent;
				oCell = newRow.insertCell(-1);
				strContent = '<? echo CUtil::JSEscape(__addParamName($arIBlock, 'tmp_xxx', '')); ?>';
				strContent = strContent.replace(/tmp_xxx/ig, id);
				oCell.innerHTML = strContent;
			}
			</script>
			</td>
		</tr> */?>
		
	<?if ($boolOffers):?>
		<tr class="heading">
			<td colspan="2"><? echo GetMessage('YANDEX_SKU_SETTINGS');?></td>
		</tr>
		<tr>
		<td valign="top"><? echo GetMessage('YANDEX_OFFERS_SELECT') ?></td><td><?
		$arOffersSelect = array(
			0 => '--- '.ToLower(GetMessage('YANDEX_OFFERS_SELECT')).' ---',
			YANDEX_SKU_EXPORT_ALL => GetMessage('YANDEX_SKU_EXPORT_ALL_TITLE'),
			YANDEX_SKU_EXPORT_MIN_PRICE => GetMessage('YANDEX_SKU_EXPORT_MIN_PRICE_TITLE'),
		);
		if (!empty($arSelectOfferProps))
		{
			$arOffersSelect[YANDEX_SKU_EXPORT_PROP] = GetMessage('YANDEX_SKU_EXPORT_PROP_TITLE');
		}
		?><select name="SKU_EXPORT_COND" id="SKU_EXPORT_COND"><?
		foreach ($arOffersSelect as $key => $value)
		{
			?><option value="<? echo htmlspecialcharsbx($key);?>" <? echo ($key == $arSKUExport['SKU_EXPORT_COND'] ? 'selected' : '');?>><? echo htmlspecialcharsEx($value); ?></option><?
		}
		?></select><?
		if (!empty($arSelectOfferProps))
		{
			?><div id="PROP_COND_CONT" style="display: <? echo (YANDEX_SKU_EXPORT_PROP == $arSKUExport['SKU_EXPORT_COND'] ? 'block' : 'none'); ?>;"><?
			?><table class="internal"><tbody>
			<tr class="heading">
				<td><? echo GetMessage('YANDEX_SKU_EXPORT_PROP_ID'); ?></td>
				<td><? echo GetMessage('YANDEX_SKU_EXPORT_PROP_COND'); ?></td>
				<td><? echo GetMessage('YANDEX_SKU_EXPORT_PROP_VALUE'); ?></td>
			</tr>
			<tr>
				<td valign="top"><select name="SKU_PROP_COND" id="SKU_PROP_COND">
				<option value="0" <? echo (empty($arSKUExport['SKU_PROP_COND']) ? 'selected' : ''); ?>><? echo GetMessage('YANDEX_SKU_EXPORT_PROP_EMPTY') ?></option>
				<?
				foreach ($arSelectOfferProps as $intPropID)
				{
					$strSelected = '';
					if (!empty($arSKUExport['SKU_PROP_COND']['PROP_ID']) && ($intPropID == $arSKUExport['SKU_PROP_COND']['PROP_ID']))
					{
						$strSelected = 'selected';
					}
					?><option value="<?=$intPropID; ?>" <? echo $strSelected; ?>><? echo htmlspecialcharsEx($arIBlock['OFFERS_PROPERTY'][$intPropID]['NAME']);?></option><?
				}
				?></select></td>
				<td valign="top"><select name="SKU_PROP_SELECT" id="SKU_PROP_SELECT"><option value="">--- <? echo ToLower(GetMessage('YANDEX_SKU_EXPORT_PROP_COND')); ?> ---</option><?
				foreach ($arCondSelectProp as $key => $value)
				{
					?><option value="<? echo htmlspecialcharsbx($key);?>" <? echo ($key == $arSKUExport['SKU_PROP_COND']['COND'] ? 'selected' : ''); ?>><? echo htmlspecialcharsEx($value); ?></option><?
				}
				?></select></td>
				<td><div id="SKU_PROP_VALUE_DV"><?
				foreach ($arSelectOfferProps as $intPropID)
				{
					$arProp = $arIBlock['OFFERS_PROPERTY'][$intPropID];
					?><div id="SKU_PROP_VALUE_DV_<? echo $arProp['ID']?>" style="display: <? echo ($intPropID == $arSKUExport['SKU_PROP_COND']['PROP_ID'] ? 'block' : 'none'); ?>;"><?
					if (!empty($arProp['VALUES']))
					{
						?><select name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" multiple><?
						foreach ($arProp['VALUES'] as $intValueID => $strValue)
						{
							?><option value="<? echo htmlspecialcharsbx($intValueID); ?>" <? echo (!empty($arSKUExport['SKU_PROP_COND']['VALUES']) && in_array($intValueID,$arSKUExport['SKU_PROP_COND']['VALUES']) ? 'selected' : ''); ?>><? echo htmlspecialcharsEx($strValue); ?></option><?
						}
						?></select><?
					}
					else
					{
						if (!empty($arSKUExport['SKU_PROP_COND']['VALUES']))
						{
							foreach ($arSKUExport['SKU_PROP_COND']['VALUES'] as $strValue)
							{
								?><input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value="<? echo htmlspecialcharsbx($strValue);?>"><br><?
							}
						}
						?><input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
						<input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
						<input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
						<input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
						<input type="text" name="SKU_PROP_VALUE_<? echo $arProp['ID']?>[]" value=""><br>
						<?
					}
					?></div><?
				}
				?></div></td>
			</tr>
			</tbody></table><?
			?><script type="text/javascript">
			var obExportConds = null,
				obPropCondCont = null,
				obSelectProps = null,
				arPropLayers = [];
			<?
			$intCount = 0;
			foreach ($arSelectOfferProps as $intPropID)
			{
				?> arPropLayers[<? echo $intCount; ?>] = {'ID': <? echo $intPropID; ?>, 'OBJ': null};
				<?
				$intCount++;
			}
			?>

			function changeValueDiv()
			{
				if (obSelectProps)
				{
					var intCurPropID = obSelectProps.options[obSelectProps.selectedIndex].value;
					for (i = 0; i < arPropLayers.length; i++)
						if (arPropLayers[i].OBJ)
							BX.style(arPropLayers[i].OBJ, 'display', (intCurPropID == arPropLayers[i].ID ? 'block' : 'none'));
				}
			}

			function changePropCondCont()
			{
				if (obExportConds && obPropCondCont)
				{
					var intTypeCond = obExportConds.options[obExportConds.selectedIndex].value;
					BX.style(obPropCondCont, 'display', (intTypeCond == <? echo YANDEX_SKU_EXPORT_PROP; ?> ? 'block' : 'none'));
				}
			}

			BX.ready(function(){
				for (i = 0; i < arPropLayers.length; i++)
				{
					arPropLayers[i].OBJ = BX('SKU_PROP_VALUE_DV_'+arPropLayers[i].ID);
				}

				obSelectProps = BX('SKU_PROP_COND');
				if (obSelectProps)
					BX.bind(obSelectProps, 'change', changeValueDiv);
				obExportConds = BX('SKU_EXPORT_COND');
				obPropCondCont = BX('PROP_COND_CONT');
				if (obExportConds && obPropCondCont)
				{
					BX.bind(obExportConds, 'change', changePropCondCont);
				}
			});
			</script><?
			?></div><?
		}
		?></td>
		</tr>
	<?endif;?>


	<?/* field configurations */?>
	
	<?
	$arFieldSubTitle = array(
		"utm_source" => GetMessage('YANDEX_PROP_UTM_TITLE'),
		"custom_label_0" => GetMessage('YANDEX_PROP_CUSTOM_LABEL_TITLE'),
		"unit_pricing_measure" => GetMessage('YANDEX_PROP_WEIGHT_PARAM'),
	);
	?>
	
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_PROPS_TYPE')?></td>
	</tr>
	<tr>
		<td colspan="2">
			<?foreach ($arTypesConfig as $key => $arConfig):?>
				<div id="config_<?=htmlspecialcharsbx($key)?>" style="padding: 10px; display: <? echo ($type == $key ? 'block' : 'none'); ?>;">
					<table width="90%" class="inner" style="text-align: center;">
						<?foreach ($arConfig as $prop):
							$default_value = GetMessage('YANDEX_PROP_'.$prop."_EMPTY");
						?>
							<?if($arFieldSubTitle[$prop]):?>
								<tr class="heading">
									<td colspan="2"><?=$arFieldSubTitle[$prop]?></td>
								</tr>
							<?endif;?>
							
							<tr>
								<td align="right" valign="middle"><?=(GetMessage('YANDEX_PROP_'.$prop))?>: </td>
								<td style="white-space: nowrap; padding: 8px 0;">
									<?__yand_show_selector($key, $prop, $arIBlock, (isset($arTypeValues[$key][$prop]) ? $arTypeValues[$key][$prop] : ''), $default_value)?>
									&nbsp;
									<small>(<?=htmlspecialcharsbx(str_replace(array('_sku', '_alternative_1', '_alternative_2', '_alternative_3', '_alternative_4'), '', $prop))?>)</small>
									<br>
									
									<?
									$text_field_value = $arXmlData["TEXT_FIELDS"][$key][$prop];
									
									__yand_show_selector_textfield($key, $prop, $arIBlock, (isset($arTypeValues[$key][$prop]) ? $arTypeValues[$key][$prop] : ''), $text_field_value);?>
								</td>
							</tr>
						<?endforeach;?>
					</table>
				</div>
			<?endforeach;?>
		</td>
	</tr>





<?$tabControl->BeginNextTab(); // price and filter?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('GOOGLE_FILTER_ELEMENTS')?></td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="config_param" style="padding: 10px auto; text-align: center;">
				<table class="inner" id="yandex_params_tbl" style="text-align: center; margin: 0 auto;">
					<thead>
					<tr>
						<td style="text-align: left; padding-bottom: 8px; font-weight: bold;"><? echo GetMessage('YANDEX_PARAMS_TITLE'); ?></td>
						<td style="text-align: left; padding-bottom: 8px; font-weight: bold;"><? echo GetMessage('YANDEX_PARAMS_SYMBOL'); ?></td>
						<td style="text-align: left; padding-bottom: 8px; font-weight: bold;"><? echo GetMessage('YANDEX_PARAMS_TITLE_VALUE'); ?></td>
					</tr>
					</thead>
					<tbody>
						<?
						$intCount = 0;
						
						// echo '<pre>'; print_r($arIBlock["PROPERTY"]); echo '</pre>';
						// echo '<pre>'; print_r($arAddParams); echo '</pre>';
						
						foreach ($arAddParams as $arParamDetail)
						{
							echo __addParamRow($arIBlock, $intCount, $arParamDetail['PARAM'], '');
							$intCount++;
						}
						if ($intCount == 0)
						{
							echo __addParamRow($arIBlock, $intCount, '', '');
							$intCount++;
						}
						?>
					</tbody>
				</table>
				<input type="hidden" name="PARAMS_COUNT" id="PARAMS_COUNT" value="<? echo $intCount; ?>">
				<div style="width: 100%; text-align: center;"><input type="button" onclick="__addYP(); return false;" name="yandex_params_add" value="<? echo GetMessage('YANDEX_PROPS_ADDITIONAL_MORE'); ?>"></div>
				
				<div class="adm-info-message-wrap">
					<div class="adm-info-message">
						<div style="font-size: 16px;"><b><?=GetMessage("YANDEX_FILTER_DESCRIPTION_TITLE");?></b></div>
						
						<?=GetMessage("YANDEX_FILTER_DESCRIPTION");?>
					</div>
				</div>
			</div>
			
			<script type="text/javascript">
			function changeVatExport()
			{
				var vatRates = BX('tr_BASE_VAT');

				if (!BX.type.isElementNode(vatRates))
					return;
				BX.style(vatRates, 'display', (this.checked ? 'table-row' : 'none'));
			}

			BX.ready(function(){
				var vatRates = BX('tr_BASE_VAT'),
					vatEnable = BX('USE_VAT_EXPORT');

				if (BX.type.isElementNode(vatRates) && BX.type.isElementNode(vatEnable))
					BX.bind(vatEnable, 'click', changeVatExport);

					setTimeout(function(){
						window.oParamSet = {
							pTypeTbl: BX("yandex_params_tbl"),
							curCount: <? echo ($intCount); ?>,
							intCounter: BX("PARAMS_COUNT")
						};
					},50);
			});

			function __addYP()
			{
				var id = window.oParamSet.curCount++,
					newRow,
					oCell,
					strContent;
				id = id.toString();
				window.oParamSet.intCounter.value = window.oParamSet.curCount;
				newRow = window.oParamSet.pTypeTbl.insertRow(window.oParamSet.pTypeTbl.rows.length);
				newRow.id = 'yandex_params_tbl_'+id;

				oCell = newRow.insertCell(-1);
				oCell.style.textAlign = 'left';
				oCell.style.borderBottom = '1px dotted #666';
				oCell.style.padding = '5px 0';
				oCell.style.verticalAlign = 'top';
				strContent = '<? echo CUtil::JSEscape(__addParamNameFilter($arIBlock, 'tmp_xxx', '')); ?>';
				strContent = strContent.replace(/tmp_xxx/ig, id);
				oCell.innerHTML = strContent;
			
				oCell = newRow.insertCell(-1);
				oCell.style.textAlign = 'left';
				oCell.style.borderBottom = '1px dotted #666';
				oCell.style.padding = '5px 0';
				oCell.style.verticalAlign = 'top';
				strContent = '<? echo CUtil::JSEscape(__addParamSymbol($arIBlock, 'tmp_xxx', '')); ?>';
				strContent = strContent.replace(/tmp_xxx/ig, id);
				oCell.innerHTML = strContent;
				
				oCell = newRow.insertCell(-1);
				oCell.style.textAlign = 'left';
				oCell.style.borderBottom = '1px dotted #666';
				oCell.style.padding = '5px 0';
				oCell.style.verticalAlign = 'top';
				strContent = '<? echo CUtil::JSEscape(__addParamValue($arIBlock, 'tmp_xxx', '')); ?>';
				strContent = strContent.replace(/tmp_xxx/ig, id);
				oCell.innerHTML = strContent;
			}
			
			function addOrField(t)
			{
				var clonedNode = t.parentNode.cloneNode(true), appendto = t.parentNode.parentNode, br = document.createElement('br');
				appendto.appendChild(clonedNode);
				appendto.appendChild(br);
			}
			
			
			function checkChangeParams(element){
				var value = element.value;
				var parentTD = BX.findParent(element);
				var inputTEXT = BX.findChild(parentTD, {"tag" : "input"});
					
				if(value == 'TEXT_FIELD')
				{
					BX.show(inputTEXT);
				}
				else
				{
					BX.hide(inputTEXT);
				}
				
				console.log(element);
				console.log(value);
			}
			</script>
		</td>
	</tr>
	
<?
	/* $arGroups = array();
	$dbRes = CCatalogGroup::GetGroupsList();
	while ($arRes = $dbRes->Fetch())
	{
		echo '<pre>'; print_r(<1arRes></1arRes>); echo '</pre>';
		if ($arRes['BUY'] == 'Y')
			$arGroups[] = $arRes['CATALOG_GROUP_ID'];
	} */
	
?>



<tr class="heading">
	<td colspan="2"><?=GetMessage('AG_GM_PRICE_FILTER')?></td>
</tr>

<tr>
	<td><?=GetMessage('AG_GM_PRICE_FILTER_INCLUDE_TEXT')?></td>
	<td><?=GetMessage('AG_GM_PRICE_FILTER_INCLUDE_TEXT_FROM')?> <input type="text" name="F_PRICE_FROM" value="<?=$F_PRICE_FROM?>" /> <?=GetMessage('AG_GM_PRICE_FILTER_INCLUDE_TEXT_TO')?> <input type="text" name="F_PRICE_TO" value="<?=$F_PRICE_TO?>" /></td>
</tr>
<tr>
	<td colspan="2">
		<div class="adm-info-message-wrap">
			<div class="adm-info-message">
				<?=GetMessage("AG_GM_PRICE_FILTER_ANNOTATION");?>
			</div>
		</div>
	</td>
</tr>


<tr class="heading">
	<td colspan="2"><?=GetMessage('AG_GM_PRICES')?></td>
</tr>

<tr>
	<td><?=GetMessage('AG_GM_PRICE_TYPE');?>: </td>
	<td><br /><select name="PRICE">
		<option value=""<? echo ($PRICE == "" || $PRICE == 0 ? ' selected' : '');?>><?=GetMessage('YANDEX_PRICE_TYPE_NONE');?></option>
		<?
		$dbRes = CCatalogGroup::GetListEx(
			array('SORT' => 'ASC'),
			// array('ID' => $arGroups),
			array(),
			false,
			false,
			array('ID', 'NAME', 'BASE')
		);
		while ($arRes = $dbRes->Fetch())
		{
			?>
			<option value="<?= $arRes['ID'] ?>"<? echo($PRICE == $arRes['ID'] ? ' selected' : ''); ?>><?= '[' . $arRes['ID'] . '] ' . htmlspecialcharsEx($arRes['NAME']); ?></option><?
		}
		?>
	</select><br /><br />
	</td>
</tr>	
<tr>
	<td colspan="2">
		<div class="adm-info-message-wrap">
			<div class="adm-info-message">
				<?=GetMessage("AG_GM_PRICE_ANNOTATION");?>
			</div>
		</div>
	</td>
</tr>	


<?
$saleModuleInfo = CModule::CreateModuleObject('sale');
if(CheckVersion($saleModuleInfo->MODULE_VERSION, '17.5.0')):
	$arCurrencyList = array();
	$arCurrencyAllowed = array('RUR', 'RUB', 'USD', 'EUR', 'UAH', 'BYR', 'KZT');
	$dbRes = CCurrency::GetList($by = 'sort', $order = 'asc');
	while ($arRes = $dbRes->GetNext())
	{
		if (in_array($arRes['CURRENCY'], $arCurrencyAllowed))
			$arCurrencyList[$arRes['CURRENCY']] = $arRes['FULL_NAME'];
	}
	?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('AG_GM_CURRENCY')?></td>
	</tr>
	<tr>
		<td><?=GetMessage('AG_GM_CURRENCY_CONVERT');?>:</td>
		<td><br /><select name="CURRENCY_SELECT">
				<option value=""<? echo ($CURRENCY_SELECT == "" || $CURRENCY_SELECT == 0 ? ' selected' : '');?>><?=GetMessage('AG_GM_CURRENCY_CONVERT_NO_CONVERT');?></option>
				
				<option value="SITE_CURRENCY" <? echo($CURRENCY_SELECT == 'SITE_CURRENCY' ? ' selected' : ''); ?>><?=GetMessage('AG_GM_CURRENCY_CONVERT_SITE_CURRENCY');?></option>
				
				<?foreach ($arCurrencyList as $strCurrency => $strCurrencyName):?>
					<option value="<?=$strCurrency?>" <? echo($CURRENCY_SELECT == $strCurrency ? ' selected' : ''); ?>><?= '[' . $strCurrency . '] ' . htmlspecialcharsEx($strCurrencyName);?></option>
				<?endforeach;?>
			</select>
			<br><br>
		</td>
	</tr>	
<?endif;?>
		
		
<?
		/* $tabControl->BeginNextTab();

	$arGroups = array();
	$dbRes = CCatalogGroup::GetGroupsList(array("GROUP_ID"=>2));
	while ($arRes = $dbRes->Fetch())
	{
		if ($arRes['BUY'] == 'Y')
			$arGroups[] = $arRes['CATALOG_GROUP_ID'];
	}
?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_PRICES')?></td>
	</tr>

	<tr>
		<td><?=GetMessage('YANDEX_PRICE_TYPE');?>: </td>
		<td><br /><select name="PRICE">
			<option value=""<? echo ($PRICE == "" || $PRICE == 0 ? ' selected' : '');?>><?=GetMessage('YANDEX_PRICE_TYPE_NONE');?></option>
<?
	if (!empty($arGroups))
	{
		$dbRes = CCatalogGroup::GetListEx(
			array('SORT' => 'ASC'),
			array('ID' => $arGroups),
			false,
			false,
			array('ID', 'NAME', 'BASE')
		);
		while ($arRes = $dbRes->Fetch())
		{
			?>
			<option value="<?= $arRes['ID'] ?>"<? echo($PRICE == $arRes['ID'] ? ' selected' : ''); ?>><?= '[' . $arRes['ID'] . '] ' . htmlspecialcharsEx($arRes['NAME']); ?></option><?
		}
	}
?>
		</select><br /><br /></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_CURRENCIES')?></td>
	</tr>

	<tr>
		<td colspan="2"><br />
<?
	$arCurrencyList = array();
	$arCurrencyAllowed = array(
		'RUR' => true,
		'RUB' => true,
		'USD' => true,
		'EUR' => true,
		'UAH' => true,
		'BYR' => true,
		'BYN' => true,
		'KZT' => true
	);
	$existCurrrencies = Currency\CurrencyManager::getCurrencyList();
	$arCurrencyList = array_intersect_key(Currency\CurrencyManager::getCurrencyList(), $arCurrencyAllowed);

	$arValues = array(
		'SITE' => GetMessage('YANDEX_CURRENCY_RATE_SITE'),
		'CBRF' => GetMessage('YANDEX_CURRENCY_RATE_CBRF'),
		'NBU' => GetMessage('YANDEX_CURRENCY_RATE_NBU'),
		'NBK' => GetMessage('YANDEX_CURRENCY_RATE_NBK'),
		'CB' => GetMessage('YANDEX_CURRENCY_RATE_CB')
	);
?>
<table cellpadding="2" cellspacing="0" border="0" class="internal" style="text-align: center;">
<thead>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_CURRENCY')?></td>
		<td><?=GetMessage('YANDEX_CURRENCY_RATE')?></td>
		<td><?=GetMessage('YANDEX_CURRENCY_PLUS')?></td>
	</tr>
</thead>
<tbody>
<?
	foreach ($arCurrencyList as $strCurrency => $strCurrencyName)
	{
?>
	<tr>
		<td><input type="checkbox" name="CURRENCY[]" id="CURRENCY_<?=$strCurrency?>" value="<?=$strCurrency?>"<? echo (empty($CURRENCY) || isset($CURRENCY[$strCurrency]) ? ' checked="checked"' : ''); ?> /></td>
		<td><label for="CURRENCY_<?=$strCurrency?>" class="text"><?=$strCurrencyName?></label></td>
		<td><select name="CURRENCY_RATE[<?=$strCurrency?>]" onchange="BX('CURRENCY_PLUS_<?=$strCurrency?>').disabled = this[this.selectedIndex].value == 'SITE'">
<?
		$strRate = 'SITE';
		if (isset($CURRENCY[$strCurrency]) && isset($CURRENCY[$strCurrency]['rate']))
			$strRate = $CURRENCY[$strCurrency]['rate'];
		if (!array_key_exists($strRate,$arValues))
			$strRate = 'SITE';
		foreach ($arValues as $key => $title)
		{
?>
			<option value="<?=htmlspecialcharsbx($key)?>"<? echo ($strRate == $key ? ' selected' : ''); ?>>(<?=htmlspecialcharsEx($key)?>) <?=htmlspecialcharsEx($title)?></option>
<?
		}
?>
		</select></td>
		<?
		$strPlus = '';
		if (isset($CURRENCY[$strCurrency]) && isset($CURRENCY[$strCurrency]['plus']))
			$strPlus = $CURRENCY[$strCurrency]['plus'];
		?>
		<td>+<input type="text" size="3" id="CURRENCY_PLUS_<?=$strCurrency?>" name="CURRENCY_PLUS[<?=$strCurrency?>]"<? echo ($strRate == 'SITE' ? ' disabled="disabled"' : ''); ?> value="<? echo htmlspecialcharsbx($strPlus); ?>">%</td>
	</tr>
<?
	}
?>
</tbody>
</table>

		</td>
	</tr>
<?
		/* $tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><? echo GetMessage('YANDEX_VAT_SETTINGS');?></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: center;">
			<?echo BeginNote(), GetMessage('YANDEX_VAT_ATTENTION'), EndNote();?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage('YANDEX_USE_VAT_EXPORT'); ?></td>
		<td>
			<input type="hidden" name="USE_VAT_EXPORT" value="N">
			<input type="checkbox" name="USE_VAT_EXPORT" value="Y" id="USE_VAT_EXPORT"<?=($vatExport['ENABLE'] == 'Y' ? ' checked' : '');?>>
		</td>
	</tr>
	
	<?
	<tr id="tr_BASE_VAT" style="display: <?=($vatExport['ENABLE'] == 'Y' ? 'table-row' : 'none');?>;">
		<td><?=GetMessage('YANDEX_BASE_VAT') ?></td>
		<td><select name="BASE_VAT">
				<option value=""<?=($vatExport['BASE_VAT'] === '' ? ' selected' : '');?>><?=GetMessage('YANDEX_BASE_VAT_ABSENT'); ?></option>
				<?
				foreach ($vatRates as $value => $title)
				{
					?><option value="<?=htmlspecialcharsbx($value); ?>"<?=($vatExport['BASE_VAT'] === $value ? ' selected' : '');?>><?=htmlspecialcharsbx($title);?></option><?
				}
				unset($value, $title);
				?>
			</select></td>
	</tr>
		<? */
		$tabControl->EndTab();
		$tabControl->Buttons(array());
		$tabControl->End();

		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	}
}