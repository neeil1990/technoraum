<?
// v1.6.1
//<title>Yandex</title>

use Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Catalog;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/export_setup_templ.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/arturgolubev.gmerchant/export_google_setup.php');

IncludeModuleLangFile(__FILE__);

global $APPLICATION, $USER;

if(!CModule::IncludeModule('catalog'))
	die('No have catalog module');

$arSetupErrors = array();

$strAllowExportPath = COption::GetOptionString("catalog", "export_default_path", "/bitrix/catalog_export/");

if (($ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY') && $STEP == 1)
{
	if (isset($arOldSetupVars['IBLOCK_ID']))
		$IBLOCK_ID = $arOldSetupVars['IBLOCK_ID'];
	if (isset($arOldSetupVars['SITE_ID']))
		$SITE_ID = $arOldSetupVars['SITE_ID'];
	if (isset($arOldSetupVars['SETUP_FILE_NAME']))
		$SETUP_FILE_NAME = str_replace($strAllowExportPath,'',$arOldSetupVars['SETUP_FILE_NAME']);
	if (isset($arOldSetupVars['COMPANY_NAME']))
		$COMPANY_NAME = $arOldSetupVars['COMPANY_NAME'];
	if (isset($arOldSetupVars['COMPANY_DESCRIPTION']))
		$COMPANY_DESCRIPTION = $arOldSetupVars['COMPANY_DESCRIPTION'];
	if (isset($arOldSetupVars['SETUP_PROFILE_NAME']))
		$SETUP_PROFILE_NAME = $arOldSetupVars['SETUP_PROFILE_NAME'];
	if (isset($arOldSetupVars['V']))
		$V = $arOldSetupVars['V'];
	if (isset($arOldSetupVars['XML_DATA']))
	{
		if (get_magic_quotes_gpc())
			$XML_DATA = base64_encode(stripslashes($arOldSetupVars['XML_DATA']));
		else
			$XML_DATA = base64_encode($arOldSetupVars['XML_DATA']);
	}
	if (isset($arOldSetupVars['SETUP_SERVER_NAME']))
		$SETUP_SERVER_NAME = $arOldSetupVars['SETUP_SERVER_NAME'];
	if (isset($arOldSetupVars['GOOGLE_EXPORT_UTM']))
		$GOOGLE_EXPORT_UTM = $arOldSetupVars['GOOGLE_EXPORT_UTM'];
	if (isset($arOldSetupVars['USE_HTTPS']))
		$USE_HTTPS = $arOldSetupVars['USE_HTTPS'];
	
	if (isset($arOldSetupVars['HIDE_WITHOT_PICTURES']))
		$HIDE_WITHOT_PICTURES = $arOldSetupVars['HIDE_WITHOT_PICTURES'];
	
	if (isset($arOldSetupVars['USE_PREVIEW_TEXT']))
		$USE_PREVIEW_TEXT = $arOldSetupVars['USE_PREVIEW_TEXT'];
	
	if (isset($arOldSetupVars['NO_USE_STANDART_PICTURES']))
		$NO_USE_STANDART_PICTURES = $arOldSetupVars['NO_USE_STANDART_PICTURES'];
	
	if (isset($arOldSetupVars['ONLY_STANDART_PRICE']))
		$ONLY_STANDART_PRICE = $arOldSetupVars['ONLY_STANDART_PRICE'];
	
	if (isset($arOldSetupVars['HIDE_WITHOT_DESCRIPTION']))
		$HIDE_WITHOT_DESCRIPTION = $arOldSetupVars['HIDE_WITHOT_DESCRIPTION'];
	
	if (isset($arOldSetupVars['HIDE_QUANTITY_NULL']))
		$HIDE_QUANTITY_NULL = $arOldSetupVars['HIDE_QUANTITY_NULL'];
	
	if (isset($arOldSetupVars['LOCK_CUPON_CHECK']))
		$LOCK_CUPON_CHECK = $arOldSetupVars['LOCK_CUPON_CHECK'];
	
	if (isset($arOldSetupVars['FILTER_AVAILABLE']))
		$filterAvalable = $arOldSetupVars['FILTER_AVAILABLE'];
	// if (isset($arOldSetupVars['DISABLE_REFERERS']))
		// $disableReferers = $arOldSetupVars['DISABLE_REFERERS'];
	if (isset($arOldSetupVars['MAX_EXECUTION_TIME']))
		$maxExecutionTime = $arOldSetupVars['MAX_EXECUTION_TIME'];
	if (isset($arOldSetupVars['CHECK_PERMISSIONS']))
		$checkPermissions = $arOldSetupVars['CHECK_PERMISSIONS'];
}

if ($STEP > 1)
{
	$IBLOCK_ID = (int)$IBLOCK_ID;
	$rsIBlocks = CIBlock::GetByID($IBLOCK_ID);
	if ($IBLOCK_ID <= 0 || !($arIBlock = $rsIBlocks->Fetch()))
	{
		$arSetupErrors[] = GetMessage("CET_ERROR_NO_IBLOCK1")." #".$IBLOCK_ID." ".GetMessage("CET_ERROR_NO_IBLOCK2");
	}
	else
	{
		$bRightBlock = !CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display");
		if ($bRightBlock)
		{
			$arSetupErrors[] = str_replace('#IBLOCK_ID#',$IBLOCK_ID,GetMessage("CET_ERROR_IBLOCK_PERM"));
		}
	}

	$SITE_ID = trim($SITE_ID);
	if ($SITE_ID === '')
	{
		$arSetupErrors[] = GetMessage('BX_CATALOG_EXPORT_YANDEX_ERR_EMPTY_SITE');
	}
	else
	{
		$iterator = Main\SiteTable::getList(array(
			'select' => array('LID'),
			'filter' => array('=LID' => $SITE_ID, '=ACTIVE' => 'Y')
		));
		$site = $iterator->fetch();
		if (empty($site))
		{
			$arSetupErrors[] = GetMessage('BX_CATALOG_EXPORT_YANDEX_ERR_BAD_SITE');
		}
	}

	if (!isset($SETUP_FILE_NAME) || $SETUP_FILE_NAME == '')
	{
		$arSetupErrors[] = GetMessage("CET_ERROR_NO_FILENAME");
	}
	elseif (preg_match(BX_CATALOG_FILENAME_REG, $strAllowExportPath.$SETUP_FILE_NAME))
	{
		$arSetupErrors[] = GetMessage("CES_ERROR_BAD_EXPORT_FILENAME");
	}
	elseif ($APPLICATION->GetFileAccessPermission($strAllowExportPath.$SETUP_FILE_NAME) < "W")
	{
		$arSetupErrors[] = str_replace("#FILE#", $strAllowExportPath.$SETUP_FILE_NAME, GetMessage('CET_YAND_RUN_ERR_SETUP_FILE_ACCESS_DENIED'));
	}

	$SETUP_SERVER_NAME = (isset($SETUP_SERVER_NAME) ? trim($SETUP_SERVER_NAME) : '');
	$GOOGLE_EXPORT_UTM = (isset($GOOGLE_EXPORT_UTM) ? trim($GOOGLE_EXPORT_UTM) : '');
	$COMPANY_NAME = (isset($COMPANY_NAME) ? trim($COMPANY_NAME) : '');
	$COMPANY_DESCRIPTION = (isset($COMPANY_DESCRIPTION) ? trim($COMPANY_DESCRIPTION) : '');

	if (empty($arSetupErrors))
	{
		$bAllSections = false;
		$arSections = array();
		if (!empty($V) && is_array($V))
		{
			foreach ($V as $key => $value)
			{
				if (trim($value) == "0")
				{
					$bAllSections = true;
					break;
				}
				$value = (int)$value;
				if ($value > 0)
					$arSections[] = $value;
			}
		}

		if (!$bAllSections && !empty($arSections))
		{
			$arCheckSections = array();
			$rsSections = CIBlockSection::GetList(array(), array('IBLOCK_ID' => $IBLOCK_ID, 'ID' => $arSections), false, array('ID'));
			while ($arOneSection = $rsSections->Fetch())
			{
				$arCheckSections[] = $arOneSection['ID'];
			}
			$arSections = $arCheckSections;
		}

		if (!$bAllSections && empty($arSections))
		{
			$arSetupErrors[] = GetMessage("CET_ERROR_NO_GROUPS");
			$V = array();
		}
	}

	if (is_array($V))
	{
		$V = array_unique(array_values($V));
		$_REQUEST['V'] = $V;
	}

	$arCatalog = CCatalogSku::GetInfoByIBlock($IBLOCK_ID);
	if (CCatalogSku::TYPE_PRODUCT == $arCatalog['CATALOG_TYPE'] || CCatalogSku::TYPE_FULL == $arCatalog['CATALOG_TYPE'])
	{
		if (!isset($XML_DATA) || $XML_DATA == '')
		{
			$arSetupErrors[] = GetMessage('YANDEX_ERR_SKU_SETTINGS_ABSENT');
		}
	}

	if (!isset($USE_HTTPS) || $USE_HTTPS != 'Y')
		$USE_HTTPS = 'N';
	
	if (!isset($HIDE_WITHOT_PICTURES) || $HIDE_WITHOT_PICTURES != 'Y')
		$HIDE_WITHOT_PICTURES = 'N';
	
	if (!isset($USE_PREVIEW_TEXT) || $USE_PREVIEW_TEXT != 'Y')
		$USE_PREVIEW_TEXT = 'N';
	
	if (!isset($NO_USE_STANDART_PICTURES) || $NO_USE_STANDART_PICTURES != 'Y')
		$NO_USE_STANDART_PICTURES = 'N';
	
	if (!isset($ONLY_STANDART_PRICE) || $ONLY_STANDART_PRICE != 'Y')
		$ONLY_STANDART_PRICE = 'N';
	
	if (!isset($HIDE_WITHOT_DESCRIPTION) || $HIDE_WITHOT_DESCRIPTION != 'Y')
		$HIDE_WITHOT_DESCRIPTION = 'N';
	
	if (!isset($HIDE_QUANTITY_NULL) || $HIDE_QUANTITY_NULL != 'Y')
		$HIDE_QUANTITY_NULL = 'N';
	
	if (!isset($LOCK_CUPON_CHECK) || $LOCK_CUPON_CHECK != 'Y')
		$LOCK_CUPON_CHECK = 'N';
	
	if (isset($_POST['FILTER_AVAILABLE']) && is_string($_POST['FILTER_AVAILABLE']))
		$filterAvalable = $_POST['FILTER_AVAILABLE'];
	if (!isset($filterAvalable) || $filterAvalable != 'Y')
		$filterAvalable = 'N';
	// if (isset($_POST['DISABLE_REFERERS']) && is_string($_POST['DISABLE_REFERERS']))
		// $disableReferers = $_POST['DISABLE_REFERERS'];
	// if (!isset($disableReferers) || $disableReferers != 'Y')
		// $disableReferers = 'N';
	if (isset($_POST['MAX_EXECUTION_TIME']) && is_string($_POST['MAX_EXECUTION_TIME']))
		$maxExecutionTime = $_POST['MAX_EXECUTION_TIME'];
	$maxExecutionTime = (!isset($maxExecutionTime) ? 0 : (int)$maxExecutionTime);
	if ($maxExecutionTime < 0)
		$maxExecutionTime = 0;

	if ($ACTION=="EXPORT_SETUP" || $ACTION=="EXPORT_EDIT" || $ACTION=="EXPORT_COPY")
	{
		if (!isset($SETUP_PROFILE_NAME) || $SETUP_PROFILE_NAME == '')
			$arSetupErrors[] = GetMessage("CET_ERROR_NO_PROFILE_NAME");
	}

	if (!empty($arSetupErrors))
	{
		$STEP = 1;
	}
}

$aMenu = array(
	array(
		"TEXT"=>GetMessage("CATI_ADM_RETURN_TO_LIST"),
		"TITLE"=>GetMessage("CATI_ADM_RETURN_TO_LIST_TITLE"),
		"LINK"=>"/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID,
		"ICON"=>"btn_list",
	)
);

$context = new CAdminContextMenu($aMenu);

$context->Show();

if (!empty($arSetupErrors))
	ShowError(implode('<br>', $arSetupErrors));
?>
<!--suppress JSUnresolvedVariable -->
<form method="post" action="<?echo $APPLICATION->GetCurPage() ?>" name="yandex_setup_form" id="yandex_setup_form">
<?
$aTabs = array(
	array("DIV" => "yand_edit1", "TAB" => GetMessage("CAT_ADM_MISC_EXP_TAB1"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_MISC_EXP_TAB1_TITLE")),
	array("DIV" => "yand_edit2", "TAB" => GetMessage("CAT_ADM_MISC_EXP_TAB2"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_MISC_EXP_TAB2_TITLE")),
);

$tabControl = new CAdminTabControl("tabYandex", $aTabs, false, true);
$tabControl->Begin();

$tabControl->BeginNextTab();

if ($STEP == 1)
{
	if (!isset($SITE_ID))
		$SITE_ID = '';
	if (!isset($XML_DATA))
		$XML_DATA = '';
	if (!isset($filterAvalable) || $filterAvalable != 'Y')
		$filterAvalable = 'N';
	if (!isset($USE_HTTPS) || $USE_HTTPS != 'Y')
		$USE_HTTPS = 'N';
	
	if (!isset($HIDE_WITHOT_PICTURES) || $HIDE_WITHOT_PICTURES != 'Y')
		$HIDE_WITHOT_PICTURES = 'N';
	
	if (!isset($USE_PREVIEW_TEXT) || $USE_PREVIEW_TEXT != 'Y')
		$USE_PREVIEW_TEXT = 'N';
	
	if (!isset($NO_USE_STANDART_PICTURES) || $NO_USE_STANDART_PICTURES != 'Y')
		$NO_USE_STANDART_PICTURES = 'N';
	
	if (!isset($ONLY_STANDART_PRICE) || $ONLY_STANDART_PRICE != 'Y')
		$ONLY_STANDART_PRICE = 'N';
	
	if (!isset($HIDE_WITHOT_DESCRIPTION) || $HIDE_WITHOT_DESCRIPTION != 'Y')
		$HIDE_WITHOT_DESCRIPTION = 'N';
	
	if (!isset($HIDE_QUANTITY_NULL) || $HIDE_QUANTITY_NULL != 'Y')
		$HIDE_QUANTITY_NULL = 'N';
	
	if (!isset($LOCK_CUPON_CHECK) || $LOCK_CUPON_CHECK != 'Y')
		$LOCK_CUPON_CHECK = 'N';
	
	// if (!isset($disableReferers) || $disableReferers != 'Y')
		// $disableReferers = 'N';
	if (!isset($SETUP_SERVER_NAME)) $SETUP_SERVER_NAME = '';
	if (!isset($GOOGLE_EXPORT_UTM)) $GOOGLE_EXPORT_UTM = '';
	if (!isset($COMPANY_NAME))
		$COMPANY_NAME = '';
	if (!isset($COMPANY_DESCRIPTION))
		$COMPANY_DESCRIPTION = '';
	if (!isset($SETUP_FILE_NAME))
		$SETUP_FILE_NAME = 'google_'.mt_rand(0, 999999).'.xml';
	if (!isset($checkPermissions) || $checkPermissions != 'Y')
		$checkPermissions = 'N';

	$siteList = array();
	$iterator = Main\SiteTable::getList(array(
		'select' => array('LID', 'NAME', 'SORT'),
		'filter' => array('=ACTIVE' => 'Y'),
		'order' => array('SORT' => 'ASC')
	));
	while ($row = $iterator->fetch())
		$siteList[$row['LID']] = $row['NAME'];
	unset($row, $iterator);
	$iblockIds = array();
	$iblockSites = array();
	$iblockMultiSites = array();
	
	/*
	// no have in 16.0 version
	$iterator = Catalog\CatalogIblockTable::getList(array(
		'select' => array(
			'IBLOCK_ID',
			'PRODUCT_IBLOCK_ID',
			'IBLOCK_ACTIVE' => 'IBLOCK.ACTIVE',
			'PRODUCT_IBLOCK_ACTIVE' => 'PRODUCT_IBLOCK.ACTIVE'
		),
		'filter' => array('')
	));
	while ($row = $iterator->fetch())
	{
		$row['PRODUCT_IBLOCK_ID'] = (int)$row['PRODUCT_IBLOCK_ID'];
		$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
		if ($row['PRODUCT_IBLOCK_ID'] > 0)
		{
			if ($row['PRODUCT_IBLOCK_ACTIVE'] == 'Y')
				$iblockIds[$row['PRODUCT_IBLOCK_ID']] = true;
		}
		else
		{
			if ($row['IBLOCK_ACTIVE'] == 'Y')
				$iblockIds[$row['IBLOCK_ID']] = true;
		}
	}
	unset($row, $iterator); */
	$rsCatalogs = CCatalog::GetList(
		array(),
		array('!PRODUCT_IBLOCK_ID' => 0),
		false,
		false,
		array('PRODUCT_IBLOCK_ID')
	);
	while ($arCatalog = $rsCatalogs->Fetch())
	{
		$arCatalog['PRODUCT_IBLOCK_ID'] = (int)$arCatalog['PRODUCT_IBLOCK_ID'];
		if ($arCatalog['PRODUCT_IBLOCK_ID'] > 0)
			$iblockIds[$arCatalog['PRODUCT_IBLOCK_ID']] = true;
	}
	$rsCatalogs = CCatalog::GetList(
		array(),
		array('PRODUCT_IBLOCK_ID' => 0),
		false,
		false,
		array('IBLOCK_ID')
	);
	while ($arCatalog = $rsCatalogs->Fetch())
	{
		$arCatalog['IBLOCK_ID'] = (int)$arCatalog['IBLOCK_ID'];
		if ($arCatalog['IBLOCK_ID'] > 0)
			$iblockIds[$arCatalog['IBLOCK_ID']] = true;
	}
	// END REPLACER
	
	if (!empty($iblockIds))
	{
		$activeIds = array();
		$iterator = Iblock\IblockSiteTable::getList(array(
			'select' => array('IBLOCK_ID', 'SITE_ID', 'SITE_SORT' => 'SITE.SORT'),
			'filter' => array('@IBLOCK_ID' => array_keys($iblockIds), '=SITE.ACTIVE' => 'Y'),
			'order' => array('IBLOCK_ID' => 'ASC', 'SITE_SORT' => 'ASC')
		));
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['IBLOCK_ID'];

			if (!isset($iblockSites[$id]))
				$iblockSites[$id] = array(
					'ID' => $id,
					'SITES' => array()
				);
			$iblockSites[$id]['SITES'][] = array(
				'ID' => $row['SITE_ID'],
				'NAME' => $siteList[$row['SITE_ID']]
			);

			if (!isset($iblockMultiSites[$id]))
				$iblockMultiSites[$id] = false;
			else
				$iblockMultiSites[$id] = true;

			$activeIds[$id] = true;
		}
		unset($id, $row, $iterator);
		if (empty($activeIds))
		{
			$iblockIds = array();
			$iblockSites = array();
			$iblockMultiSites = array();
		}
		else
		{
			$iblockIds = array_intersect_key($iblockIds, $activeIds);
		}
		unset($activeIds);
	}
	if (empty($iblockIds))
	{

	}

	$currentList = array();
	if ($IBLOCK_ID > 0 && isset($iblockIds[$IBLOCK_ID]))
	{
		$currentList = $iblockSites[$IBLOCK_ID]['SITES'];
		if ($SITE_ID === '')
		{
			$firstSite = reset($currentList);
			$SITE_ID = $firstSite['ID'];
		}
	}
?>

<?if(!CModule::IncludeModule("arturgolubev.gmerchant")){?>
	<tr>
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><?//=GetMessage($MODULE_NAME . "_ALLOW_URL_FOPEN_NOT_FOUND")?></div>
					<?=GetMessage("ARTURGOLUBEV_GMERCHANT_DEMO_IS_EXPIRED")?>
				<div class="adm-info-message-icon"></div>
			</div>
		</div><br>
	</tr>
<?}?>

<tr class="heading">
	<td colspan="2"><?=GetMessage('BX_CATALOG_EXPORT_IBLOCK_SELECT'); ?></td>
</tr>
<tr>
	<td width="40%"><?=GetMessage('BX_CATALOG_EXPORT_IBLOCK'); ?></td>
	<td width="60%"><?
	echo GetIBlockDropDownListEx(
		$IBLOCK_ID, 'IBLOCK_TYPE_ID', 'IBLOCK_ID',
		array(
			'ID' => array_keys($iblockIds),
			'CHECK_PERMISSIONS' => 'Y',
			'MIN_PERMISSION' => 'U'
		),
		"ClearSelected(); changeIblockSites(0); BX('id_ifr').src='/bitrix/tools/catalog_export/yandex_util.php?IBLOCK_ID=0&'+'".bitrix_sessid_get()."';",
		"ClearSelected(); changeIblockSites(this[this.selectedIndex].value); BX('id_ifr').src='/bitrix/tools/catalog_export/yandex_util.php?IBLOCK_ID='+this[this.selectedIndex].value+'&'+'".bitrix_sessid_get()."';",
		'class="adm-detail-iblock-types"',
		'class="adm-detail-iblock-list"'
	);
	?>
		<script type="text/javascript">
		var TreeSelected = [];
		<?
		$intCountSelected = 0;
		if (!empty($V) && is_array($V))
		{
			foreach ($V as $oneKey)
			{
				?>TreeSelected[<? echo $intCountSelected ?>] = <? echo (int)$oneKey; ?>;
				<?
				$intCountSelected++;
			}
		}
		?>
		function ClearSelected()
		{
			BX.showWait();
			TreeSelected = [];
		}
		</script>
	</td>
</tr>
<tr id="tr_SITE_ID" style="display: <?=(count($currentList) > 1 ? 'table-row' : 'none' ); ?>;">
	<td width="40%"><?=GetMessage('BX_CATALOG_EXPORT_YANDEX_SITE'); ?></td>
	<td width="60%">
		<script type="text/javascript">
		function changeIblockSites(iblockId)
		{
			var iblockSites = <?=CUtil::PhpToJSObject($iblockSites); ?>,
				iblockMultiSites = <?=CUtil::PhpToJSObject($iblockMultiSites); ?>,
				tableRow = null,
				siteControl = null,
				i,
				currentSiteList;

			tableRow = BX('tr_SITE_ID');
			siteControl = BX('SITE_ID');
			if (!BX.type.isElementNode(tableRow) || !BX.type.isElementNode(siteControl))
				return;

			for (i = siteControl.length-1; i >= 0; i--)
				siteControl.remove(i);
			if (typeof(iblockSites[iblockId]) !== 'undefined')
			{
				currentSiteList = iblockSites[iblockId]['SITES'];
				for (i = 0; i < currentSiteList.length; i++)
				{
					siteControl.appendChild(BX.create(
						'option',
						{
							props: {value: BX.util.htmlspecialchars(currentSiteList[i].ID)},
							html: BX.util.htmlspecialchars('[' + currentSiteList[i].ID + '] ' + currentSiteList[i].NAME)
						}
					));
				}
			}
			if (siteControl.length > 0)
				siteControl.selectedIndex = 0;
			else
				siteControl.selectedIndex = -1;
			BX.style(tableRow, 'display', (siteControl.length > 1 ? 'table-row' : 'none'));
		}
		</script>
		<select id="SITE_ID" name="SITE_ID">
		<?
		foreach ($currentList as $site)
		{
			$selected = ($site['ID'] == $SITE_ID ? ' selected' : '');
			$name = '['.$site['ID'].'] '.$site['NAME'];
			?><option value="<?=htmlspecialcharsbx($site['ID']); ?>"<?=$selected; ?>><?=htmlspecialcharsbx($name); ?></option><?
		}
		unset($name, $selected, $site);
		?>
		</select>
	</td>
</tr>
<tr>
	<td width="40%" valign="top"><?echo GetMessage("CET_SELECT_GROUP");?></td>
	<td width="60%"><?
	if ($intCountSelected)
	{
		foreach ($V as $oneKey)
		{
			$oneKey = (int)$oneKey;
			?><input type="hidden" value="<? echo $oneKey; ?>" name="V[]" id="oldV<? echo $oneKey; ?>"><?
		}
		unset($oneKey);
	}
	?><div id="tree"></div>
	<script type="text/javascript">
	BX.showWait();
	clevel = 0;

	function delOldV(obj)
	{
		if (!!obj)
		{
			var intSelKey = BX.util.array_search(obj.value, TreeSelected);
			if (obj.checked == false)
			{
				if (-1 < intSelKey)
				{
					TreeSelected = BX.util.deleteFromArray(TreeSelected, intSelKey);
				}

				var objOldVal = BX('oldV'+obj.value);
				if (!!objOldVal)
				{
					objOldVal.parentNode.removeChild(objOldVal);
					objOldVal = null;
				}
			}
			else
			{
				if (-1 == intSelKey)
				{
					TreeSelected[TreeSelected.length] = obj.value;
				}
			}
		}
	}

	function buildNoMenu()
	{
		var buffer;
		buffer = '<?echo GetMessageJS("CET_FIRST_SELECT_IBLOCK");?>';
		BX('tree', true).innerHTML = buffer;
		BX.closeWait();
	}

	function buildMenu()
	{
		var i,
			buffer,
			imgSpace,
			space;

		buffer = '<table border="0" cellspacing="0" cellpadding="0">';
		buffer += '<tr>';
		buffer += '<td colspan="2" valign="top" align="left"><input type="checkbox" name="V[]" value="0" id="v0"'+(BX.util.in_array(0,TreeSelected) ? ' checked' : '')+' onclick="delOldV(this);"><label for="v0"><font class="text"><b><?echo CUtil::JSEscape(GetMessage("CET_ALL_GROUPS"));?></b></font></label></td>';
		buffer += '</tr>';

		for (i in Tree[0])
		{
			if (!Tree[0][i])
			{
				space = '<input type="checkbox" name="V[]" value="'+i+'" id="V'+i+'"'+(BX.util.in_array(i,TreeSelected) ? ' checked' : '')+' onclick="delOldV(this);"><label for="V'+i+'"><span class="text">' + Tree[0][i][0] + '</span></label>';
				imgSpace = '';
			}
			else
			{
				space = '<input type="checkbox" name="V[]" value="'+i+'"'+(BX.util.in_array(i,TreeSelected) ? ' checked' : '')+' onclick="delOldV(this);"><a href="javascript: collapse(' + i + ')"><span class="text"><b>' + Tree[0][i][0] + '</b></span></a>';
				imgSpace = '<img src="/bitrix/images/catalog/load/plus.gif" width="13" height="13" id="img_' + i + '" OnClick="collapse(' + i + ')">';
			}

			buffer += '<tr>';
			buffer += '<td width="20" valign="top" align="center">' + imgSpace + '</td>';
			buffer += '<td id="node_' + i + '">' + space + '</td>';
			buffer += '</tr>';
		}

		buffer += '</table>';

		BX('tree', true).innerHTML = buffer;
		BX.adminPanel.modifyFormElements('yandex_setup_form');
		BX.closeWait();
	}

	function collapse(node)
	{
		if (!BX('table_' + node))
		{
			var i,
				buffer,
				imgSpace,
				space;

			buffer = '<table border="0" id="table_' + node + '" cellspacing="0" cellpadding="0">';

			for (i in Tree[node])
			{
				if (!Tree[node][i])
				{
					space = '<input type="checkbox" name="V[]" value="'+i+'" id="V'+i+'"'+(BX.util.in_array(i,TreeSelected) ? ' checked' : '')+' onclick="delOldV(this);"><label for="V'+i+'"><font class="text">' + Tree[node][i][0] + '</font></label>';
					imgSpace = '';
				}
				else
				{
					space = '<input type="checkbox" name="V[]" value="'+i+'"'+(BX.util.in_array(i,TreeSelected) ? ' checked' : '')+' onclick="delOldV(this);"><a href="javascript: collapse(' + i + ')"><font class="text"><b>' + Tree[node][i][0] + '</b></font></a>';
					imgSpace = '<img src="/bitrix/images/catalog/load/plus.gif" width="13" height="13" id="img_' + i + '" OnClick="collapse(' + i + ')">';
				}

				buffer += '<tr>';
				buffer += '<td width="20" align="center" valign="top">' + imgSpace + '</td>';
				buffer += '<td id="node_' + i + '">' + space + '</td>';
				buffer += '</tr>';
			}

			buffer += '</table>';

			BX('node_' + node).innerHTML += buffer;
			BX('img_' + node).src = '/bitrix/images/catalog/load/minus.gif';
		}
		else
		{
			var tbl = BX('table_' + node);
			tbl.parentNode.removeChild(tbl);
			BX('img_' + node).src = '/bitrix/images/catalog/load/plus.gif';
		}
		BX.adminPanel.modifyFormElements('yandex_setup_form');
	}
	</script>
	<iframe src="/bitrix/tools/catalog_export/yandex_util.php?IBLOCK_ID=<?=intval($IBLOCK_ID)?>&<? echo bitrix_sessid_get(); ?>" id="id_ifr" name="ifr" style="display:none"></iframe>
	</td>
</tr>

<tr>
	<td width="40%"><?=GetMessage('CAT_DETAIL_PROPS')?>:</td>
	<td width="60%">
		<script type="text/javascript">
		function showDetailPopup()
		{
			if (!obDetailWindow)
			{
				var s = BX('IBLOCK_ID');
				var dat = BX('XML_DATA');
				var obDetailWindow = new BX.CAdminDialog({
					'content_url': '/bitrix/tools/arturgolubev.gmerchant/google_detail.php?lang=<?=LANGUAGE_ID?>&bxpublic=Y&IBLOCK_ID=' + s[s.selectedIndex].value,
					'content_post': 'XML_DATA='+BX.util.urlencode(dat.value)+'&'+'<?echo bitrix_sessid_get(); ?>',
					'width': 900, 'height': 550,
					'resizable': true
				});
				obDetailWindow.Show();
			}
		}

		function setDetailData(data)
		{
			BX('XML_DATA').value = data;
		}
		</script>
		<input type="button" onclick="showDetailPopup(); return false;" value="<? echo GetMessage('CAT_DETAIL_PROPS_RUN'); ?>">
		<input type="hidden" id="XML_DATA" name="XML_DATA" value="<?=htmlspecialcharsbx($XML_DATA); ?>">
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('CAT_YANDEX_CHECK_PERMISSIONS'); ?></td>
	<td width="60%">
		<input type="hidden" name="CHECK_PERMISSIONS" value="N">
		<input type="checkbox" name="CHECK_PERMISSIONS" value="Y"<?=($checkPermissions == 'Y' ? ' checked' : ''); ?>>
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('CAT_YANDEX_FILTER_AVAILABLE'); ?></td>
	<td width="60%">
		<input type="hidden" name="FILTER_AVAILABLE" value="N">
		<input type="checkbox" name="FILTER_AVAILABLE" value="Y"<? echo ($filterAvalable == 'Y' ? ' checked' : ''); ?>>
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('CAT_YANDEX_HIDE_WITHOT_PICTURES'); ?></td>
	<td width="60%">
		<input type="hidden" name="HIDE_WITHOT_PICTURES" value="N">
		<input type="checkbox" name="HIDE_WITHOT_PICTURES" value="Y"<? echo ($HIDE_WITHOT_PICTURES == 'Y' ? ' checked' : ''); ?>>
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('CAT_YANDEX_HIDE_WITHOT_DESCRIPTION'); ?></td>
	<td width="60%">
		<input type="hidden" name="HIDE_WITHOT_DESCRIPTION" value="N">
		<input type="checkbox" name="HIDE_WITHOT_DESCRIPTION" value="Y"<? echo ($HIDE_WITHOT_DESCRIPTION == 'Y' ? ' checked' : ''); ?>>
	</td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('CAT_GOOGLE_HIDE_QUANTITY_NULL'); ?></td>
	<td width="60%">
		<input type="hidden" name="HIDE_QUANTITY_NULL" value="N">
		<input type="checkbox" name="HIDE_QUANTITY_NULL" value="Y"<? echo ($HIDE_QUANTITY_NULL == 'Y' ? ' checked' : ''); ?>>
	</td>
</tr>


<tr class="heading">
	<td colspan="2"><?echo GetMessage("BX_CATALOG_EXPORT_DATASETTING");?></td>
</tr>
<?/* <tr>
	<td width="40%"><? echo GetMessage('CAT_GOOGLE_USE_PREVIEW_TEXT'); ?></td>
	<td width="60%">
		<input type="hidden" name="USE_PREVIEW_TEXT" value="N">
		<input type="checkbox" name="USE_PREVIEW_TEXT" value="Y"<? echo ($USE_PREVIEW_TEXT == 'Y' ? ' checked' : ''); ?>
	</td>
</tr> */?>
<tr>
	<td width="40%"><? echo GetMessage('CAT_GOOGLE_NO_USE_STANDART_PICTURES'); ?></td>
	<td width="60%">
		<input type="hidden" name="NO_USE_STANDART_PICTURES" value="N">
		<input type="checkbox" name="NO_USE_STANDART_PICTURES" value="Y" <? echo ($NO_USE_STANDART_PICTURES == 'Y' ? ' checked' : ''); ?>>
	</td>
</tr>
<tr>
	<td colspan="2">
		<div class="adm-info-message-wrap"><div class="adm-info-message small-admin-message"><?echo GetMessage('CAT_GOOGLE_NO_USE_STANDART_PICTURES_NOTE');?></div>		</div>
	</td>
</tr>

<tr>
	<td width="40%"><? echo GetMessage('CAT_GOOGLE_ONLY_STANDART_PRICE'); ?></td>
	<td width="60%">
		<input type="hidden" name="ONLY_STANDART_PRICE" value="N">
		<input type="checkbox" name="ONLY_STANDART_PRICE" value="Y" <? echo ($ONLY_STANDART_PRICE == 'Y' ? ' checked' : ''); ?> >
	</td>
</tr>
<tr>
	<td colspan="2">
		<div class="adm-info-message-wrap"><div class="adm-info-message small-admin-message"><?echo GetMessage('CAT_GOOGLE_ONLY_STANDART_PRICE_NOTE');?></div></div>
	</td>
</tr>

<tr class="heading">
	<td colspan="2"><?echo GetMessage("BX_CATALOG_EXPORT_PERFOMANCE");?></td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('CAT_YANDEX_LOCK_CUPON_CHECK'); ?></td>
	<td width="60%">
		<input type="hidden" name="LOCK_CUPON_CHECK" value="N">
		<input type="checkbox" name="LOCK_CUPON_CHECK" value="Y"<? echo ($LOCK_CUPON_CHECK == 'Y' ? ' checked' : ''); ?>>
	</td>
</tr>
<tr>
	<td colspan="2">
		<div class="adm-info-message-wrap"><div class="adm-info-message small-admin-message"><?echo GetMessage('CAT_YANDEX_LOCK_CUPON_CHECK_NOTE');?></div></div>
	</td>
</tr>

<tr class="heading">
	<td colspan="2"><?echo GetMessage("BX_CATALOG_EXPORT_FILE_PROPS");?></td>
</tr>
<tr>
	<td width="40%"><? echo GetMessage('CAT_YANDEX_USE_HTTPS'); ?></td>
	<td width="60%">
		<input type="hidden" name="USE_HTTPS" value="N">
		<input type="checkbox" name="USE_HTTPS" value="Y"<? echo ($USE_HTTPS == 'Y' ? ' checked' : ''); ?>>
	</td>
</tr>
<?/* <tr>
	<td width="40%"><? echo GetMessage('CAT_YANDEX_DISABLE_REFERERS'); ?></td>
	<td width="60%">
		<input type="hidden" name="DISABLE_REFERERS" value="N">
		<input type="checkbox" name="DISABLE_REFERERS" value="Y"<? echo ($disableReferers == 'Y' ? ' checked' : ''); ?>
	</td>
</tr>
 */?>
<?
$show_exicution_time = 0;
if(CModule::IncludeModule('arturgolubev.gmerchant'))
{
	$moduleWorker = new CArturgolubevGmerchant();
	if($moduleWorker->checkModule('sale', '17.0.0'))
	{
		$show_exicution_time = 1;
	}
}

$maxExecutionTime = (isset($maxExecutionTime) ? (int)$maxExecutionTime : 0);
?>
<?if($show_exicution_time):?>
	<tr>
		<td width="40%"><?=GetMessage('CAT_MAX_EXECUTION_TIME');?></td>
		<td width="60%">
			<input type="text" name="MAX_EXECUTION_TIME" size="40" value="<?=$maxExecutionTime; ?>">
		</td>
	</tr>
<?else:?>
	<tr>
		<td width="40%"><?=GetMessage('CAT_MAX_EXECUTION_TIME');?></td>
		<td width="60%">
			0 <input type="hidden" name="MAX_EXECUTION_TIME" size="40" value="<?=$maxExecutionTime; ?>">
		</td>
	</tr>
<?endif;?>
<tr>
	<td width="40%" style="padding-top: 0;">&nbsp;</td>
	<td width="60%" style="padding-top: 0;"><small><?=GetMessage("CAT_MAX_EXECUTION_TIME_NOTE");?></small></td>
</tr>
<tr>
	<td width="40%"><?echo GetMessage("CET_SERVER_NAME");?></td>
	<td width="60%">
		<input type="text" name="SETUP_SERVER_NAME" value="<?=htmlspecialcharsbx($SETUP_SERVER_NAME); ?>" size="50"> <input type="button" onclick="this.form['SETUP_SERVER_NAME'].value = window.location.host;" value="<?echo htmlspecialcharsbx(GetMessage('CET_SERVER_NAME_SET_CURRENT'))?>">
	</td>
</tr>



<?/* <tr>
	<td width="40%"><?echo GetMessage("BX_CATALOG_GOOGLE_EXPORT_UTM");?></td>
	<td width="60%">
		<input type="text" name="GOOGLE_EXPORT_UTM" value="<?=htmlspecialcharsbx($GOOGLE_EXPORT_UTM); ?>" size="50">
	</td>
</tr>
<tr>
	<td width="40%" style="padding-top: 0;">&nbsp;</td>
	<td width="60%" style="padding-top: 0;"><small><?=GetMessage("BX_CATALOG_GOOGLE_EXPORT_UTM_EXAMPLE");?></small></td>
</tr> */?>


<tr>
	<td width="40%"><?=GetMessage("BX_CATALOG_EXPORT_YANDEX_COMPANY_NAME");?></td>
	<td width="60%">
		<input type="text" name="COMPANY_NAME" value="<?=htmlspecialcharsbx($COMPANY_NAME); ?>" size="50">
	</td>
</tr>
<tr>
	<td width="40%"><?=GetMessage("BX_CATALOG_EXPORT_YANDEX_COMPANY_DESCRIPTION");?></td>
	<td width="60%">
		<input type="text" name="COMPANY_DESCRIPTION" value="<?=htmlspecialcharsbx($COMPANY_DESCRIPTION); ?>" size="50">
	</td>
</tr>
<tr>
	<td width="40%"><?echo GetMessage("CET_SAVE_FILENAME");?></td>
	<td width="60%">
		<b><? echo htmlspecialcharsbx(COption::GetOptionString("catalog", "export_default_path", "/bitrix/catalog_export/"));?></b><input type="text" name="SETUP_FILE_NAME" value="<?=htmlspecialcharsbx($SETUP_FILE_NAME); ?>" size="50">
	</td>
</tr>
<?
	if ($ACTION=="EXPORT_SETUP" || $ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY')
	{
?><tr>
	<td width="40%"><?echo GetMessage("CET_PROFILE_NAME");?></td>
	<td width="60%">
		<input type="text" name="SETUP_PROFILE_NAME" value="<?echo htmlspecialcharsbx($SETUP_PROFILE_NAME) ?>" size="30">
	</td>
</tr><?
	}
}

$tabControl->EndTab();

$tabControl->BeginNextTab();

if ($STEP==2)
{
	$SETUP_FILE_NAME = $strAllowExportPath.$SETUP_FILE_NAME;
	if (strlen($XML_DATA) > 0)
	{
		$XML_DATA = base64_decode($XML_DATA);
	}
	$SETUP_SERVER_NAME = htmlspecialcharsbx($SETUP_SERVER_NAME);
	$_POST['SETUP_SERVER_NAME'] = htmlspecialcharsbx($_POST['SETUP_SERVER_NAME']);
	$_REQUEST['SETUP_SERVER_NAME'] = htmlspecialcharsbx($_REQUEST['SETUP_SERVER_NAME']);

	$FINITE = true;
}
$tabControl->EndTab();

$tabControl->Buttons();

?><? echo bitrix_sessid_post();?><?
if ($ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY')
{
	?><input type="hidden" name="PROFILE_ID" value="<? echo intval($PROFILE_ID); ?>"><?
}

if (2 > $STEP)
{
	?><input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
	<input type="hidden" name="ACT_FILE" value="<?echo htmlspecialcharsbx($_REQUEST["ACT_FILE"]) ?>">
	<input type="hidden" name="ACTION" value="<?echo htmlspecialcharsbx($ACTION) ?>">
	<input type="hidden" name="STEP" value="<?echo intval($STEP) + 1 ?>">
	<input type="hidden" name="SETUP_FIELDS_LIST" value="V,IBLOCK_ID,SITE_ID,SETUP_SERVER_NAME,GOOGLE_EXPORT_UTM,COMPANY_NAME,COMPANY_DESCRIPTION,SETUP_FILE_NAME,XML_DATA,USE_HTTPS,HIDE_WITHOT_PICTURES,USE_PREVIEW_TEXT,NO_USE_STANDART_PICTURES,ONLY_STANDART_PRICE,LOCK_CUPON_CHECK,HIDE_WITHOT_DESCRIPTION,HIDE_QUANTITY_NULL,FILTER_AVAILABLE,DISABLE_REFERERS,MAX_EXECUTION_TIME,CHECK_PERMISSIONS">
	<input type="submit" value="<?echo ($ACTION=="EXPORT")?GetMessage("CET_EXPORT"):GetMessage("CET_SAVE")?>"><?
}

$tabControl->End();
?></form>


<?=BeginNote();?>
	<?=GetMessage("ARTURGOLUBEV_GMERCHANT_INFORMATION");?>
<?=EndNote();?>

<style>
	.small-admin-message {
		margin-top: 0 !important;
		padding: 5px 10px !important;
		display: block !important;
		text-align: center !important;
	}
</style>

<script type="text/javascript">
<?if ($STEP < 2):?>
tabYandex.SelectTab("yand_edit1");
tabYandex.DisableTab("yand_edit2");
<?elseif ($STEP == 2):?>
tabYandex.SelectTab("yand_edit2");
tabYandex.DisableTab("yand_edit1");
<?endif;?>
</script>