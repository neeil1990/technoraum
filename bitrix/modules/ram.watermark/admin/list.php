<?
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Bitrix\User;
use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock as HL;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

$module_id = 'ram.watermark';

$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($MODULE_RIGHT === 'D')
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

\Bitrix\Main\Loader::includeModule($module_id);

IncludeModuleLangFile(__FILE__);

$version1 = \Bitrix\Main\Application::getConnection()->query('SHOW COLUMNS FROM ram_watermark_image LIKE "STATUS"')->fetch();
$version1 = !empty($version1);
if ($version1)
{
	$APPLICATION->IncludeAdminFile(Loc::getMessage("ram.watermark_V1_CONVERT"), $DOCUMENT_ROOT."/bitrix/modules/ram.watermark/v1/convert.php");
	die();
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

$sTableID = 'tbl_ram_watermark_marks';

$context = Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

$uploadDir = \Bitrix\Main\Config\Option::get('main', 'upload_dir', 'upload');

$oSort = new CAdminSorting($sTableID, 'ID', 'asc');
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$MARKS = \Ram\Watermark\MarkTable::getList(array('order' => array($by => $order), 'select' => array('ID', 'ACTIVE', 'NAME', 'PARAMS')));

$MARKS = new CAdminResult($MARKS, $sTableID);
$MARKS->NavStart();

$lAdmin->NavText($MARKS->GetNavPrint(Loc::getMessage('ram.watermark_NAV')));

$lAdmin->AddHeaders(array(
	array('id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true),
	array('id' => 'ACTIVE', 'content' => Loc::getMessage('ram.watermark_ACTIVE'), 'sort' => 'ACTIVE', 'default' => true),
	array('id' => 'NAME', 'content' => Loc::getMessage('ram.watermark_NAME'), 'sort' => 'NAME', 'default' => true),
	array('id' => 'MARK', 'content' => Loc::getMessage('ram.watermark_MARK'), 'default' => true),
	array('id' => 'FIELDS_INCLUDE', 'content' => Loc::getMessage('ram.watermark_FIELDS_INCLUDE'), 'default' => true),
	array('id' => 'FIELDS_EXCLUDE', 'content' => Loc::getMessage('ram.watermark_FIELDS_EXCLUDE'), 'default' => true),
	array('id' => 'LIMIT', 'content' => Loc::getMessage('ram.watermark_LIMIT'), 'default' => true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($MARK = $MARKS->NavNext(true, 'f_'))
{
	$markFiltersTotal = 0;
	
	$row =& $lAdmin->AddRow($f_ID, $MARK);
	
	$row->AddField('ID', $f_ID);
	$row->AddField('ACTIVE', $f_ACTIVE === 'Y' ? Loc::getMessage('ram.watermark_Y') : Loc::getMessage('ram.watermark_N'));
	$row->AddField('NAME', $f_NAME);
	
	$uploadDir = \Bitrix\Main\Config\Option::get('main', 'upload_dir', 'upload');
	$watermarkCacheDir = '/'.$uploadDir.'/ram.watermark/images/cache/'.$MARK['ID'].'/';
	$watermarkCacheFile = $MARK['ID'].'.png?'.md5(serialize($MARK['PARAMS']));
	
	$image = new CRamWatermarkImage();
	$image->from($_SERVER['DOCUMENT_ROOT'].$watermarkCacheDir.$MARK['ID'].'.png');
	$image->resize(Array('width' => 100, 'height' => 100, 'resizeType' => BX_RESIZE_IMAGE_PROPORTIONAL));
	if ($image->luminance() >= 127)
	{
		$mark = '<div class="ramwmadminlist-markpreview ramwmadminlist-markpreviewdark"><img src="'.$watermarkCacheDir.$watermarkCacheFile.'"/></div>';
	}
	else
	{
		$mark = '<div class="ramwmadminlist-markpreview"><img src="'.$watermarkCacheDir.$watermarkCacheFile.'"/></div>';
	}
	
	$row->AddViewField('MARK', $mark);
	
	$arrFilters = Array('include' => Array(), 'exclude' => Array());
	$filtersList = \Ram\Watermark\FilterTable::getList(array('filter' => array('WMID' => $MARK['ID'])));
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
	
	ksort($arrFilters['include']);
	ksort($arrFilters['exclude']);
	
	$arrFiltersHtml = Array('include' => '', 'exclude' => '');
	
	foreach ($arrFilters as $strType => $arrTypeFilters)
	{
		foreach ($arrTypeFilters as $arrFilter)
		{
			$arrFilterHtml = Array();
			$module = CModule::CreateModuleObject($arrFilter['PATH'][0]);
			$moduleName = '';
			if ($module)
			{
				if ($arrFilter['PATH'][0] === 'fileman')
				{
					$module->MODULE_NAME = 'Ìåäèàáèáëèîòåêà ('.$module->MODULE_NAME.')';
				}
				
				$moduleName = $module->MODULE_NAME;
			}
			if (!in_array($arrFilter['PATH'][0], Array('iblock', 'forum', 'highloadblock', 'fileman')))
			{
				if ($arrFilter['PATH'][0] !== 'all')
				{
					$objModule = CModule::CreateModuleObject($arrFilter['PATH'][0]);
					if ($objModule)
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_MODULE').' "'.$moduleName.'"';
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ALL_IMAGES');
					}
				}
				else
				{
					$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ALL_MODULES');
					$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ALL_IMAGES');
				}
			}
			else
			{
				if ($arrFilter['PATH'][0] === 'iblock' && Bitrix\Main\Loader::includeModule('iblock'))
				{
					if (is_numeric($arrFilter['PATH'][1]))
					{
						$dbIBlock = CIBlock::GetList(Array('iblock_type' => 'ASC', 'name' => 'ASC'), Array('ID' => $arrFilter['PATH'][1], 'CHECK_PERMISSIONS' => 'N'), false);
						if ($arIBlock = $dbIBlock->Fetch())
						{
							$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_IBLOCK').' "'.$arIBlock['NAME'].'" ('.$arIBlock['ID'].')';
						}
					}
					else
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ALL_IBLOCK');
					}
					if (is_numeric($arrFilter['PATH'][2]))
					{
						$sections = Array();
						$sectionChain = CIBlockSection::GetNavChain($arrFilter['PATH'][1], $arrFilter['PATH'][2]);
						while ($sectionPath = $sectionChain->GetNext())
						{
							$sections[] = Loc::getMessage('ram.watermark_FILTER_SECTION').' "'.$sectionPath['NAME'].'" ('.$sectionPath['ID'].')';
						}
						$arrFilterHtml = array_merge($arrFilterHtml, $sections);
					}
					if ($arrFilter['PATH'][count($arrFilter['PATH']) - 1] === 'elements')
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ELEMENTS');
					}
					else if ($arrFilter['PATH'][count($arrFilter['PATH']) - 1] === 'subelements')
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_SUBELEMENTS');
					}
					else if ($arrFilter['PATH'][count($arrFilter['PATH']) - 1] === 'sections')
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_SECTIONS');
					}
					$fields = Array();
					foreach ($arrFilter['FIELDS'] as $field)
					{
						if (!is_numeric($field))
						{
							$fields[] = Loc::getMessage('ram.watermark_FILTER_FIELD_'.$field);
						}
						else
						{
							if (substr_count($arrFilter['PATH'][count($arrFilter['PATH']) - 1], 'element'))
							{
								$prop = \Bitrix\Iblock\PropertyTable::getRowByID($field);
								if ($prop)
								{
									$fields[] = Loc::getMessage('ram.watermark_FILTER_PROPERTY').' "'.$prop['NAME'].'" ('.$prop['CODE'].')';
								}
							}
							else
							{
								$prop = \Bitrix\Main\UserFieldTable::getRowByID($field);
								if ($prop)
								{
									$propLang = CUserTypeEntity::GetByID($prop['ID']);
									
									$fields[] = Loc::getMessage('ram.watermark_FILTER_PROPERTY').' "'.$propLang['EDIT_FORM_LABEL'][LANGUAGE_ID].'" ('.$prop['FIELD_NAME'].')';
								}
							}
						}
					}
					
					sort($fields);
					
					$arrFilterHtml[] = implode(', ', $fields);
				}
				else if ($arrFilter['PATH'][0] === 'fileman' && Bitrix\Main\Loader::includeModule('fileman'))
				{
					$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_FILEMAN');
					if (is_numeric($arrFilter['PATH'][1]))
					{
						CMedialib::Init();
						$ñollections = Array();
						$ñollection = CMedialibCollection::GetList(Array('arFilter' => Array('ID' => $arrFilter['PATH'][1])));
						$ñollections[] = $ñollection[0];
						while ($ñollection[0]['PARENT_ID'])
						{
							$ñollection = CMedialibCollection::GetList(Array('arFilter' => Array('ID' => $ñollection[0]['PARENT_ID'])));
							array_unshift($ñollections, $ñollection[0]);
						}
						foreach ($ñollections as $ñollection)
						{
							$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_COLLECTION').' "'.$ñollection['NAME'].'" ('.$ñollection['ID'].')';
						}
					}
					else
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ALL_COLLECTION');
					}
					if ($arrFilter['PATH'][count($arrFilter['PATH']) - 1] === 'elements')
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_COLLECTION_ELEMENTS');
					}
					else if ($arrFilter['PATH'][count($arrFilter['PATH']) - 1] === 'subelements')
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_COLLECTION_SUBELEMENTS');
					}
					else if ($arrFilter['PATH'][count($arrFilter['PATH']) - 1] === 'all')
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_COLLECTION_ALL');
					}
				}
				else if ($arrFilter['PATH'][0] === 'highloadblock' && Bitrix\Main\Loader::includeModule('highloadblock'))
				{
					if (is_numeric($arrFilter['PATH'][1]))
					{
						$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getRow(Array('select' => Array('*', 'NAME_LANG' => 'LANG.NAME'), 'filter' => Array('ID' => $arrFilter['PATH'][1])));
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_HIGHLOADIBLOCK').' "'.$hlblock['NAME'].'" ('.$hlblock['ID'].')';
					}
					else
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ALL_HIGHLOADIBLOCK');
					}
					$fields = Array();
					foreach ($arrFilter['FIELDS'] as $field)
					{
						if (!is_numeric($field))
						{
							$fields[] = Loc::getMessage('ram.watermark_FILTER_FIELD_'.$field);
						}
						else
						{
							$prop = \Bitrix\Main\UserFieldTable::getRowByID($field);
							if ($prop)
							{
								$propLang = CUserTypeEntity::GetByID($prop['ID']);
								$fields[] = Loc::getMessage('ram.watermark_FILTER_PROPERTY').' "'.$propLang['EDIT_FORM_LABEL'][LANGUAGE_ID].'" ('.$prop['FIELD_NAME'].')';
							}
						}
					}
					sort($fields);
					$arrFilterHtml[] = implode(', ', $fields);
				}
				else if ($arrFilter['PATH'][0] === 'forum' && Bitrix\Main\Loader::includeModule('forum'))
				{
					if (is_numeric($arrFilter['PATH'][1]))
					{
						$forum = CForumNew::GetByID($arrFilter['PATH'][1]);						
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_FORUM').' "'.$forum['NAME'].'" ('.$forum['ID'].')';
					}
					else
					{
						$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ALL_FORUMS');
					}
					$arrFilterHtml[] = Loc::getMessage('ram.watermark_FILTER_ELEMENTS');
				}
			}
			$arrFiltersHtml[$strType] .= '<ul><li>'.implode('</li><li>', $arrFilterHtml).'</li></ul>';
		}
	}
	
	$row->AddViewField('FIELDS_INCLUDE', '<div class="ramwmlist-fields">'.$arrFiltersHtml['include'].'</div>');
	$row->AddViewField('FIELDS_EXCLUDE', '<div class="ramwmlist-fields">'.$arrFiltersHtml['exclude'].'</div>');
	
	$htmlLimit = '<div class="ramwmlist-fields">';
	
	if ($MARK['PARAMS']['LIMIT_TYPE'] === 'Y')
	{
		$htmlLimit .= '<ul><li>'.Loc::getMessage('ram.watermark_TYPE');
		$limitTypes = Array();
		
		if ($MARK['PARAMS']['LIMIT_JPG'] === 'Y') $limitTypes[] = 'JPG';
		if ($MARK['PARAMS']['LIMIT_PNG'] === 'Y') $limitTypes[] = 'PNG';
		if ($MARK['PARAMS']['LIMIT_GIF'] === 'Y') $limitTypes[] = 'GIF';
		if ($MARK['PARAMS']['LIMIT_BMP'] === 'Y') $limitTypes[] = 'BMP';
		if ($MARK['PARAMS']['LIMIT_WEBP'] === 'Y') $limitTypes[] = 'WEBP';
		
		$htmlLimit .= implode(', ', $limitTypes).'</li></ul>';
	}
	
	if ($MARK['PARAMS']['LIMIT_SIZES'] === 'Y')
	{
		$htmlLimit .= '<ul><li>'.Loc::getMessage('ram.watermark_WIDTH').$MARK['PARAMS']['LIMIT_MIN_WIDTH'].'px - '.$MARK['PARAMS']['LIMIT_MAX_WIDTH'].'px</li></ul>';
		$htmlLimit .= '<ul><li>'.Loc::getMessage('ram.watermark_HEIGHT').$MARK['PARAMS']['LIMIT_MIN_HEIGHT'].'px - '.$MARK['PARAMS']['LIMIT_MAX_HEIGHT'].'px</li></ul>';
	}
	
	if ($MARK['PARAMS']['LIMIT_DATE'] === 'Y')
	{
		$htmlLimit .= '<ul><li>'.Loc::getMessage('ram.watermark_DATE').$MARK['PARAMS']['LIMIT_DATE_FROM'].' - '.$MARK['PARAMS']['LIMIT_DATE_TO'].'</li></ul>';
	}
	
	$htmlLimit .= '</div>';
	
	$row->AddViewField('LIMIT', $htmlLimit);
	
	if ($MODULE_RIGHT === 'W')
	{
		$arActions = array(
			array('ICON' => 'edit', 'TEXT' => Loc::getMessage('ram.watermark_EDIT'), 'ACTION' => $lAdmin->ActionRedirect('ram.watermark_edit.php?ID='.$f_ID.'&lang='.LANG), 'DEFAULT' => true),
			array('ICON' => 'copy', 'TEXT' => Loc::getMessage('ram.watermark_COPY'), 'ACTION' => $lAdmin->ActionRedirect('ram.watermark_edit.php?ID='.$f_ID.'&lang='.LANG.'&action=copy')),
			array('SEPARATOR' => true),
			array('ICON' => 'delete', 'TEXT' => Loc::getMessage('ram.watermark_DELETE'), 'ACTION' => 'if(confirm("'.Loc::getMessage('ram.watermark_DELETE_CONFIRM').'")) '.$lAdmin->ActionRedirect('ram.watermark_edit.php?ID='.$f_ID.'&lang='.LANG.'&action=delete'))
		);
	}
	else
	{
		$arActions = array(
			array('ICON' => 'view', 'TEXT' => Loc::getMessage('ram.watermark_VIEW'), 'ACTION' => $lAdmin->ActionRedirect('ram.watermark_edit.php?ID='.$f_ID.'&lang='.LANG), 'DEFAULT' => true)
		);
	}
	
	$row->AddActions($arActions);
}

if ($MODULE_RIGHT === 'W')
{
	$aContext = array(
		array(
			'TEXT' => Loc::getMessage('ram.watermark_ADD'),
			'LINK' => 'ram.watermark_edit.php?lang='.LANG,
			'TITLE' => Loc::getMessage('ram.watermark_ADD_TITLE'),
			'ICON' => 'btn_new',
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('ram.watermark_TITLE'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$APPLICATION->SetAdditionalCSS('/bitrix/panel/ram.watermark/admin.css');

$lAdmin->DisplayList();

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>