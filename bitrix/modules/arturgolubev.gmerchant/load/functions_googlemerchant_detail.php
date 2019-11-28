<?
// v1.6.1
if (!function_exists("__yand_show_selector"))
{
	function __yand_show_selector($group, $key, $IBLOCK, $value = "", $emptyValue = ""){
		?><select onChange="checkChangeParams(this);" name="XML_DATA[<? echo htmlspecialcharsbx($group)?>][<? echo htmlspecialcharsbx($key)?>]">
		<option value=""<? echo ($value == "" ? ' selected' : ''); ?>><?=(($emptyValue) ? $emptyValue : GetMessage('YANDEX_SKIP_PROP'))?></option>
		<?
		if (!empty($IBLOCK['TEXT_FIELDS']))
		{
			foreach ($IBLOCK['TEXT_FIELDS'] as $fieldname)
			{
				?><option value="<?=$fieldname?>"<? echo ($value == $fieldname ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_SALE_FIELD_".$fieldname)?></option><?
			}
		}
		if (!empty($IBLOCK['SALE_FIELDS']))
		{
			?><option value=""><? echo GetMessage('GOOGLE_SALE_FIELDS')?></option><?
			foreach ($IBLOCK['SALE_FIELDS'] as $fieldname)
			{
				?><option value="<?=$fieldname?>"<? echo ($value == $fieldname ? ' selected' : ''); ?>>[<?=str_replace(array("_SKU", "_MAIN"),'',$fieldname)?>] <?=GetMessage("GOOGLE_SALE_FIELD_".$fieldname)?></option><?
			}
		}
		if (!empty($IBLOCK['SALE_SKU_FIELDS']))
		{
			?><option value=""><? echo GetMessage('GOOGLE_SALE_SKU_FIELDS')?></option><?
			foreach ($IBLOCK['SALE_SKU_FIELDS'] as $fieldname)
			{
				?><option value="<?=$fieldname?>"<? echo ($value == $fieldname ? ' selected' : ''); ?>>[<?=str_replace(array("_SKU", "_MAIN"),'',$fieldname)?>] <?=GetMessage("GOOGLE_SALE_FIELD_".$fieldname)?></option><?
			}
		}
		if (!empty($IBLOCK['OFFERS_PROPERTY']))
		{
			?><option value=""><? echo GetMessage('YANDEX_PRODUCT_PROPS')?></option><?
		}
		foreach ($IBLOCK['PROPERTY'] as $key => $arProp)
		{
			?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
		}
		if (!empty($IBLOCK['OFFERS_PROPERTY']))
		{
			?><option value=""><? echo GetMessage('YANDEX_OFFERS_PROPS')?></option><?
			foreach ($IBLOCK['OFFERS_PROPERTY'] as $key => $arProp)
			{
				?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?></option><?
			}
		}
		?></select><?
	}

	function __yand_show_selector_textfield($group, $key, $IBLOCK, $value = "", $text_field_value) {
		?>
			<input type="text" size="45" name="TEXT_FIELDS[<? echo htmlspecialcharsbx($group)?>][<? echo htmlspecialcharsbx($key)?>]" style="margin-top: 5px; <?=(($value == "TEXT_FIELD") ? '' : 'display:none;');?>" value="<?=$text_field_value?>">
		<?
	}

	function __addParamCode()
	{
		return '<small>(param)</small>';
	}

	function __addParamName(&$IBLOCK, $intCount, $value)
	{
		ob_start();
		__yand_show_selector('PARAMS','ID_'.$intCount, $IBLOCK, $value);
		$strResult = ob_get_contents();
		ob_end_clean();
		return $strResult;
	}



	function __addParamUnit(&$IBLOCK, $intCount, $value)
	{
		return '<input type="text" size="3" name="XML_DATA[PARAMS][UNIT_'.$intCount.']" value="'.htmlspecialcharsbx($value).'">';
	}

	//// 
	function __yand_show_selector_filter($group, $key, $IBLOCK, $valueSaved = "")
	{
		$tmp = explode('|', $valueSaved);
		$value = $tmp[0];
		
		/* foreach ($IBLOCK['PROPERTY'] as $key => $arProp)
		{
			// echo '<pre>'; print_r($arProp); echo '</pre>';
			echo '<pre>'; print_r($arProp["NAME"]); echo '</pre>';
			echo '<pre>'; print_r($arProp["PROPERTY_TYPE"]); echo '</pre>';
			echo '<pre>'; print_r($arProp["LIST_TYPE"]); echo '</pre>';
			echo '<pre>'; print_r($arProp["FILE_TYPE"]); echo '</pre>';
			echo '<pre>'; print_r($arProp["LINK_IBLOCK_ID"]); echo '</pre>';
			echo '<pre>'; print_r($arProp["USER_TYPE"]); echo '</pre>';
			echo '<pre>'; print_r($arProp["USER_TYPE_SETTINGS"]); echo '</pre>';
			
			// $res = CIBlockProperty::GetByID($arProp["ID"]);
			// if($ar_res = $res->GetNext())
			// {
				// echo '<pre>'; print_r($ar_res); echo '</pre>';
			// }
		} */
			
		?><select name="XML_DATA[<? echo htmlspecialcharsbx($group)?>][<? echo htmlspecialcharsbx($key)?>]">
		<option value=""<? echo ($value == "" ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_FILTER_ELEMENTS_EMPTY");?></option>
		<?
		foreach ($IBLOCK['PROPERTY'] as $key => $arProp)
		{
			if($arProp["PROPERTY_TYPE"] == 'F' || $arProp["USER_TYPE"] == 'map_yandex')
				continue;
			
			$subtext = ''; 
			if ($arProp["USER_TYPE"] == "directory") $subtext .= GetMessage("GOOGLE_FILTER_PROPERTY_DIRECTORY");
			if ($arProp["PROPERTY_TYPE"] == "N") $subtext .= GetMessage("GOOGLE_FILTER_PROPERTY_NUMBER");
			if ($arProp["LINK_IBLOCK_ID"] > 0) $subtext .= GetMessage("GOOGLE_FILTER_PROPERTY_IB_ELEMENT");
			if($subtext) $subtext = '('.$subtext.')';
			
			?><option value="<?=$arProp['ID']?>"<? echo ($value == $arProp['ID'] ? ' selected' : ''); ?>>[<?=htmlspecialcharsbx($key)?>] <?=htmlspecialcharsbx($arProp['NAME'])?> <?=$subtext?></option><?
		}
		?></select><?
	}
	function __yand_show_symbol($group, $key, $IBLOCK, $valueSaved = "")
	{
		$tmp = explode('|', $valueSaved);
		$value = $tmp[1];
		
		?><select name="XML_DATA[<? echo htmlspecialcharsbx($group)?>][<? echo htmlspecialcharsbx($key)?>_symbol]">
			<option value="equally" <? echo ($value == "" ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_FILTER_SYMBOL_EQUALL");?></option>
			<option value="noequally" <? echo ($value == "noequally" ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_FILTER_SYMBOL_NOEQUALL");?></option>
			
			
			<option value="empty" <? echo ($value == "empty" ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_FILTER_SYMBOL_EMPTY");?></option>
			<option value="noempty" <? echo ($value == "noempty" ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_FILTER_SYMBOL_NOEMPTY");?></option>
			<option value="like" <? echo ($value == "like" ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_FILTER_SYMBOL_LIKE");?></option>
			
			<option value="more" <? echo ($value == "more" ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_FILTER_SYMBOL_MORE");?></option>
			<option value="less" <? echo ($value == "less" ? ' selected' : ''); ?>><?=GetMessage("GOOGLE_FILTER_SYMBOL_LESS");?></option>
		</select><?
	}
	function __yand_show_valuer($group, $key, $IBLOCK, $valueSaved = "")
	{
		$tmp = explode('|', $valueSaved);
		$value = explode(';', $tmp[2]);
		
		foreach($value as $val):
			?><span style="white-space: nowrap">
				<input type="text" name="XML_DATA[<? echo htmlspecialcharsbx($group)?>][<? echo htmlspecialcharsbx($key)?>_value][]" value="<?=$val?>">
				<input type="button"  onClick="addOrField(this); return false;" value="<?=GetMessage("GOOGLE_FILTER_SYMBOL_OR");?>">
			</span><br><?
		endforeach;
	}
	function __addParamNameFilter(&$IBLOCK, $intCount, $value)
	{
		ob_start();
		__yand_show_selector_filter('PARAMS','ID_'.$intCount, $IBLOCK, $value);
		$strResult = ob_get_contents();
		ob_end_clean();
		return $strResult;
	}
	function __addParamValue(&$IBLOCK, $intCount, $value)
	{
		ob_start();
		__yand_show_valuer('PARAMS','ID_'.$intCount, $IBLOCK, $value);
		$strResult = ob_get_contents();
		ob_end_clean();
		return $strResult;
	}
	function __addParamSymbol(&$IBLOCK, $intCount, $value)
	{
		ob_start();
		__yand_show_symbol('PARAMS','ID_'.$intCount, $IBLOCK, $value);
		$strResult = ob_get_contents();
		ob_end_clean();
		return $strResult;
	}

	function __addParamRow(&$IBLOCK, $intCount, $strParam, $strUnit)
	{
		return '<tr id="yandex_params_tbl_'.$intCount.'">
			<td style="text-align: left; border-bottom: 1px dotted #666; padding: 5px 0;" valign="top">'.__addParamNameFilter($IBLOCK, $intCount, $strParam).'</td>
			<td style="text-align: left; border-bottom: 1px dotted #666; padding: 5px 0;" valign="top">'.__addParamSymbol($IBLOCK, $intCount, $strParam).'</td>
			<td style="text-align: left; border-bottom: 1px dotted #666; padding: 5px 0;" valign="top">'.__addParamValue($IBLOCK, $intCount, $strParam).'</td>
			</tr>';
	}
}
?>