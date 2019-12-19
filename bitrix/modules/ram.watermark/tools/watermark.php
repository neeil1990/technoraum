<?
	define("NOT_CHECK_PERMISSIONS", true);
	define("NO_KEEP_STATISTIC", true);
	
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	
	use Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);
	
	$module_id = "ram.watermark";
	
	$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
	
	if ($MODULE_RIGHT === 'D')
	{
		die(GetMessage("ACCESS_DENIED"));
	}
	
	if ($moduleStat = \Bitrix\Main\Loader::includeSharewareModule('ram.watermark'))
	{
		if ($moduleStat == 3)
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ram.watermark/include.php");
		}
		
		$ACTION = htmlspecialcharsbx($_REQUEST['action']);
		
		switch ($ACTION)
		{
			case 'v1_ajax':
			{
				switch (htmlspecialcharsbx($_REQUEST['step']))
				{
					case 'set_clear_status':
					{
						$dbImages = Ram\Watermark\ImageTable::getList();
						while ($arImage = $dbImages -> Fetch())
						{
							Ram\Watermark\ImageTable::update($arImage['ID'], Array('STATUS' => 'C'));
						}
						
						$text = Loc::getMessage('ram.watermark_V1_SET_CLEAR_STATUS');
						if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
						
						echo(json_encode(Array('title' => $text, 'status' => 'next')));
						break;
					}
					case 'clear_iblock_cache':
					{
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
						
						$text = Loc::getMessage('ram.watermark_V1_CLEAR_IBLOCK_CACHE');
						if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
						
						echo(json_encode(Array('title' => $text, 'status' => 'next')));
						break;
					}
					case 'clear_files':
					{
						$start = microtime(true);
						$dbFile = \Ram\Watermark\ImageTable::getList(Array('limit' => 500));
						while ($arFile = $dbFile->fetch())
						{
							if (microtime(true) - $start > 5)
							{
								break;
							}
							
							if ($arFile['STATUS'] === 'C')
							{
								if (\CRamWatermark::clearFile($arFile['IMAGEID']))
								{
									Ram\Watermark\ImageTable::delete($arFile['ID']);
									$count++;
								}
							}
						}
						
						$text = Loc::getMessage('ram.watermark_V1_CLEAR_FILES');
						if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
						
						$total = \Ram\Watermark\ImageTable::getCount();
						if ($total == 0)
						{
							echo(json_encode(Array('title' => $text.$total, 'status' => 'next')));
						}
						else
						{
							echo(json_encode(Array('title' => $text.$total, 'status' => 'repeat')));
						}
						break;
					}
					case 'remove_old_events':
					{
						CAgent::RemoveModuleAgents('ram.watermark');
						UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementUpdate', 'ram.watermark', 'CRamWatermark', 'OnAfterIBlockElementEvent');
						UnRegisterModuleDependences('iblock', 'OnAfterIBlockElementAdd', 'ram.watermark', 'CRamWatermark', 'OnAfterIBlockElementEvent');
						UnRegisterModuleDependences('iblock', 'OnAfterIBlockSectionUpdate', 'ram.watermark', 'CRamWatermark', 'OnAfterIBlockSectionEvent');
						UnRegisterModuleDependences('iblock', 'OnAfterIBlockSectionAdd', 'ram.watermark', 'CRamWatermark', 'OnAfterIBlockSectionEvent');
						UnRegisterModuleDependences('forum', 'onAfterMessageAdd', 'ram.watermark', 'CRamWatermark', 'OnAfterForumMessageEvent');
						UnRegisterModuleDependences('forum', 'onAfterMessageUpdate', 'ram.watermark', 'CRamWatermark', 'OnAfterForumMessageEvent');
						$eventManager = \Bitrix\Main\EventManager::getInstance();
						if (\Bitrix\Main\Loader::includeModule('highloadblock'))
						{
							$dbHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::GetList();
							while ($arHLBlock = $dbHLBlock -> Fetch())
							{
								$eventManager->unRegisterEventHandler('', $arHLBlock['NAME'].'OnAfterAdd', 'ram.watermark', 'CRamWatermark', 'OnHLBlockEvent');
								$eventManager->unRegisterEventHandler('', $arHLBlock['NAME'].'OnAfterUpdate', 'ram.watermark', 'CRamWatermark', 'OnHLBlockEvent');
							}
						}
						
						$text = Loc::getMessage('ram.watermark_V1_REMOVE_OLD_EVENTS');
						if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
						
						echo(json_encode(Array('title' => $text, 'status' => 'next')));
						break;
					}
					case 'add_new_events':
					{
						RegisterModuleDependences('main', 'OnBeforeResizeImage', 'ram.watermark', 'CRamWatermark', 'OnBeforeResizeImage');
						RegisterModuleDependences('main', 'OnAfterResizeImage', 'ram.watermark', 'CRamWatermark', 'OnAfterResizeImage');
						RegisterModuleDependences('main', 'OnGetFileSRC', 'ram.watermark', 'CRamWatermark', 'OnGetFileSRC');
						RegisterModuleDependences('main', 'OnEndBufferContent', 'ram.watermark', 'CRamWatermark', 'OnEndBufferContent');
						
						$text = Loc::getMessage('ram.watermark_V1_ADD_NEW_EVENTS');
						if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
						
						echo(json_encode(Array('title' => $text, 'status' => 'next')));
						break;
					}
					case 'set_structure_v1':
					{
						CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ram.watermark/v1/lib_v1", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ram.watermark/lib", true, true);
						
						$text = Loc::getMessage('ram.watermark_V1_UPDATE_STRUCTURE_V1');
						if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
						
						echo(json_encode(Array('title' => $text, 'status' => 'repeat')));
						break;
					}
					case 'convert_data':
					{
						$data = unserialize(base64_decode($_REQUEST['data']));
						
						$watermarks = $data['watermarks'];
						$filters = $data['filters'];
						
						foreach ($watermarks as $watermark)
						{
							$font = explode('/', $watermark['PARAMS']['TEXT_FONT']);
							$watermark['PARAMS']['TEXT_FONT'] = $font[count($font) - 1];
							
							$image = explode('/', $watermark['PARAMS']['IMAGE']);
							$watermark['PARAMS']['IMAGE'] = $image[count($image) - 1];
							
							$wmOldId = $watermark['ID'];
							
							unset($watermark['ID']);
							
							$watermark['ACTIVE'] = 'Y';
							
							$wmNewId = \Ram\Watermark\MarkTable::add($watermark)->getID();
							
							foreach ($filters as $filter)
							{
								if ($filter['WMID'] == $wmOldId)
								{
									unset($filter['ID']);
									
									$filter['ENTITY'] = $filter['OBJECT'];
									$filter['TYPE'] = 'include';
									$filter['GROUP'] = null;
									$filter['OBJECT'] = null;
									
									if ($filter['MODULE'] === 'iblock')
									{
										if ($filter['FIELD'] === 'ELEMENT_PREVIEW_PICTURE')
										{
											$filter['OBJECT'] = 'elements';
											$filter['FIELD'] = 'PREVIEW_PICTURE';
										}
										else if ($filter['FIELD'] === 'ELEMENT_DETAIL_PICTURE')
										{
											$filter['OBJECT'] = 'elements';
											$filter['FIELD'] = 'DETAIL_PICTURE';
										}
										else if (substr_count($filter['FIELD'], 'ELEMENT_PROPERTY_'))
										{
											$filter['OBJECT'] = 'elements';
											$filter['FIELD'] = str_replace('ELEMENT_PROPERTY_', '', $filter['FIELD']);
										}
										else if ($filter['FIELD'] === 'SECTION_PICTURE')
										{
											$filter['OBJECT'] = 'sections';
											$filter['FIELD'] = 'PICTURE';
										}
										else if ($filter['FIELD'] === 'SECTION_DETAIL_PICTURE')
										{
											$filter['OBJECT'] = 'sections';
											$filter['FIELD'] = 'DETAIL_PICTURE';
										}
										else if (substr_count($filter['FIELD'], 'SECTION_'))
										{
											$filter['OBJECT'] = 'sections';
											
											$filter['FIELD'] = str_replace('SECTION_', '', $filter['FIELD']);
											
											$prop = \Bitrix\Main\UserFieldTable::getRow(Array('filter' => Array('ENTITY_ID' => 'IBLOCK_'.$filter['ENTITY'].'_SECTION', 'FIELD_NAME' => $filter['FIELD'])));
											if ($prop)
											{
												$filter['FIELD'] = $prop['ID'];
											}
										}
									}
									else if ($filter['MODULE'] === 'highloadblock')
									{
										$prop = \Bitrix\Main\UserFieldTable::getRow(Array('filter' => Array('ENTITY_ID' => 'HLBLOCK_'.$filter['ENTITY'], 'FIELD_NAME' => $filter['FIELD'])));
										if ($prop)
										{
											$filter['FIELD'] = $prop['ID'];
										}
									}
									
									if (!$filter['ENTITY']) $filter['ENTITY'] = 'all';
									
									$filter['WMID'] = $wmNewId;
									
									\Ram\Watermark\FilterTable::add($filter);
								}
							}
						}
						
						$text = Loc::getMessage('ram.watermark_V1_CONVERT_DATA');
						if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
						
						echo(json_encode(Array('title' => $text, 'status' => 'next')));
						
						break;
					}
					case 'get_data_v1':
					{
						$version1 = \Bitrix\Main\Application::getConnection()->query('SHOW COLUMNS FROM ram_watermark_image LIKE "STATUS"')->fetch();
						$version1 = !empty($version1);
						$watermarks = null;
						$filters = null;
						
						if ($version1)
						{
							$watermarks = \Ram\Watermark\MarkTable::getList()->fetchAll();
							$filters = \Ram\Watermark\FilterTable::getList()->fetchAll();
							
							global $DB;
							$DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/ram.watermark/install/db/".strtolower($DB->type)."/uninstall.sql");
							$DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/ram.watermark/install/db/".strtolower($DB->type)."/install.sql");
							CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ram.watermark/v1/lib_v2", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ram.watermark/lib", true, true);
						}
						
						$text = Loc::getMessage('ram.watermark_V1_GET_DATA_V1');
						if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
						
						echo(json_encode(Array('title' => $text, 'status' => 'next', 'data' => base64_encode(serialize(Array('watermarks' => $watermarks, 'filters' => $filters))))));
						break;
					}
				}
				break;
			}
			case 'admin_add_filter':
			{
				$arModules = \CRamWatermark::adminFilterList(Array('OBJECT' => 'modules', 'TYPE' => htmlspecialcharsbx($_REQUEST['type'])));
				
				$html = '<div class="ramwmadmin-filter"><div class="ramwmadmin-filterselects"><div class="ramwmadmin-filterselect"><select onchange="RamWmAdminFilterSelectChange(this);">';
				foreach ($arModules as $arModule)
				{
					$html .= '<option value="'.$arModule['ID'].'">'.$arModule['NAME'].'</option>';
				}
				$html .= '</select></div></div><div class="ramwmadmin-filterfields"><div class="ramwmadmin-filteremptyfield">'.Loc::getMessage('ram.watermark_EMPTY_SELECT').'</div></div><div class="ramwmadmin-filterdelete" onclick="RamWmAdminFilterDelete(this);" title="'.Loc::getMessage('ram.watermark_DELETE_FILTER').'"></div><div class="ramwmadmin-filtercopy" onclick="RamWmAdminFilterCopy(this);" title="'.Loc::getMessage('ram.watermark_COPY_FILTER').'"></div></div>';
				
				echo($html);
				
				break;
			}
			case 'admin_filter':
			{
				$FULL_ID = htmlspecialcharsbx($_REQUEST['id']);
				
				$arAdminFilter = \CRamWatermark::adminFilter($FULL_ID);
				
				$arResult = Array();
				
				if (isset($arAdminFilter['list']))
				{
					$htmlList = '<div class="ramwmadmin-filterselect"><select onchange="RamWmAdminFilterSelectChange(this);">';
					foreach ($arAdminFilter['list'] as $arItem)
					{
						if (@mb_detect_encoding($arItem['NAME'], 'UTF-8', true) !== 'UTF-8') $arItem['NAME'] = iconv('windows-1251', 'utf-8', $arItem['NAME']);
						
						$htmlList .= '<option '.($arItem['ID']==-1?"disabled='disabled'":"").' value="'.$arItem['ID'].'">'.$arItem['NAME'].'</option>';
					}
					$htmlList .= '</select></div>';
					
					$arResult['list'] = $htmlList;
				}
				
				if (isset($arAdminFilter['fields']))
				{
					$htmlFields = '';
					foreach ($arAdminFilter['fields'] as $arItem)
					{
						if (@mb_detect_encoding($arItem['NAME'], 'UTF-8', true) !== 'UTF-8') $arItem['NAME'] = iconv('windows-1251', 'utf-8', $arItem['NAME']);
						
						if ($arItem['HIDDEN'] === 'Y')
						{
							$htmlFields .= '<div class="ramwmadmin-filterfield hidden"><input type="checkbox" checked="checked" name="FILTERS['.$FULL_ID.'][]" value="'.$arItem['ID'].'"/>'.$arItem['NAME'].'</div>';
						}
						else
						{
							$htmlFields .= '<label class="ramwmadmin-filterfield"><input type="checkbox" name="FILTERS['.$FULL_ID.'][]" value="'.$arItem['ID'].'"/>'.$arItem['NAME'].'</label>';
						}
					}
					
					$arResult['fields'] = $htmlFields;
				}
				
				echo(json_encode($arResult));
				
				break;
			}
			case "uploadfont":
			{
				if ($MODULE_RIGHT !== 'W')
				{
					$text = GetMessage("ACCESS_DENIED");
					
					if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
					
					die(json_encode(Array('status' => 'error', 'message' => $text)));
				}
				
				$file = $_FILES[0];
				
				$status = 'error';
				
				$text = '';
				
				$fonts = '';
				
				if ($file['error'] == UPLOAD_ERR_OK)
				{
					if (substr_count(strtolower($file['name']), '.ttf'))
					{
						$uploadDir = \COption::GetOptionString("main", "upload_dir", "upload");
						
						if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$uploadDir.'/ram.watermark/fonts/'.$file['name']))
						{
							if (move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/'.$uploadDir.'/ram.watermark/fonts/'.$file['name']))
							{
								$status = 'success';
								$text = GetMessage("ram.watermark_SUCCESS_UPLOAD");
								$fonts = \CRamWatermark::getFonts();
								$fontHtml = "";
								foreach ($fonts as $fontValue => $fontData)
								{
									if ($fontValue === $file['name'])
									{
										$fontName = strtolower(str_replace('.ttf', '', $fontValue));
										$fontName = preg_replace('/[^\\w]/', '', $fontName);
										
										$fontHtml .= "<label title='".$fontData['title']."'><input type='radio' ".($fontValue===$_REQUEST['font']?'checked':'')." name='PARAMS[TEXT_FONT]' onchange='RamWmAdminUpdateParams();' value='".$fontValue."' /><span><img height='16' src='".$fontData['src']."'/></span><div style='font-family: \"".$fontName."\";'>1</div><style type='text/css'>@font-face {font-family: '".$fontName."'; src: url('/".$uploadDir."/ram.watermark/fonts/".$fontValue."'); font-weight: normal;}</style></label>";
										
										break;
									}
								}
							}
							else $text = GetMessage("ram.watermark_ERROR_UPLOAD");
						}
						else $text = GetMessage("ram.watermark_ERROR_EXISTS");
					}
					else $text = GetMessage("ram.watermark_ERROR_TTF_TYPE");
				}
				else $text = GetMessage("ram.watermark_ERROR_UPLOAD");
				
				if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
				
				echo(json_encode(Array('status' => $status, 'message' => $text, 'font' => $fontHtml)));
				
				break;
			}
			case "uploadimage":
			{
				if ($MODULE_RIGHT !== 'W')
				{
					$text = GetMessage("ACCESS_DENIED");
					
					if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
					
					die(json_encode(Array('status' => 'error', 'message' => $text)));
				}
				
				$file = $_FILES[0];
				
				$status = 'error';
				
				$text = '';
				
				$images = '';
				
				if ($file['error'] == UPLOAD_ERR_OK)
				{
					$file_info = pathinfo($file['name']);
					
					if (in_array($file_info['extension'], Array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp')))
					{
						$uploadDir = \COption::GetOptionString("main", "upload_dir", "upload");
						
						if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$uploadDir.'/ram.watermark/images/watermarks/'.$file['name']))
						{
							if (move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/'.$uploadDir.'/ram.watermark/images/watermarks/'.$file['name']))
							{
								$status = 'success';
								$text = GetMessage("ram.watermark_SUCCESS_UPLOAD");
								$images = \CRamWatermark::getImages();
								$imageHtml = "";
								foreach ($images as $imageValue => $imageData)
								{
									if ($imageValue === $file['name'])
									{
										$image = new CRamWatermarkImage();
										$image->from($_SERVER['DOCUMENT_ROOT'].'/'.$uploadDir.'/ram.watermark/images/watermarks/'.$file['name']);
										$image->resize(Array('width' => 100, 'height' => 100, 'resizeType' => BX_RESIZE_IMAGE_PROPORTIONAL));
										if ($image->luminance() >= 127)
										{
											$imageHtml .= "<label title='".$imageData['title']."'><input type='radio' ".($imageValue===$_REQUEST['image']?'checked':'')." name='PARAMS[IMAGE]' onchange='RamWmAdminUpdateParams();' value='".$imageValue."' /><span class='dark'><img src='".$imageData['src']."'/></span></label>";
										}
										else
										{
											$imageHtml .= "<label title='".$imageData['title']."'><input type='radio' ".($imageValue===$_REQUEST['image']?'checked':'')." name='PARAMS[IMAGE]' onchange='RamWmAdminUpdateParams();' value='".$imageValue."' /><span><img src='".$imageData['src']."'/></span></label>";
										}
										
										break;
									}
								}
								
							}
							else $text = GetMessage("ram.watermark_ERROR_UPLOAD");
						}
						else $text = GetMessage("ram.watermark_ERROR_EXISTS");
					}
					else $text = GetMessage("ram.watermark_ERROR_IMAGE_TYPE");
				}
				else $text = GetMessage("ram.watermark_ERROR_UPLOAD");
				
				if (@mb_detect_encoding($text, 'UTF-8', true) !== 'UTF-8') $text = iconv('windows-1251', 'utf-8', $text);
				
				echo(json_encode(Array('status' => $status, 'message' => $text, 'image' => $imageHtml)));
				
				break;
			}
		}
	}
?>