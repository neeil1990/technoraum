<?
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin/task_description.php");

$sites_list = array();
$sites_arr = CSite::GetList($by="def", $order="desc", array("ACTIVE"=>"Y"));
while ($site = $sites_arr->Fetch())
{
	$sites_list[] = array($site["LID"] => $site["NAME"]);
}
for($i=0;$i<count($sites_list);$i++){
    $keys = array_keys($sites_list[$i]);
	$js_sites[] = $keys[0];
}
?>
<?
$VARIANTS = array (
		'snow',
		'blizzard',
		'newyear',
		'fir',
		'santa',
		'snowman',
		'sock',
		'cookie',
		'rain',
		'foliage',
		'hearts',
		'cocktail',
		'flowers',
		'bubble'
);
$no_var = 0;
foreach($VARIANTS as $var) {
	if(!file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/burbon.snow/precipitation/'.$var.'/')) {
		//echo $_SERVER["DOCUMENT_ROOT"].'/bitrix/js/burbon.snow/precipitation/'.$var.'/<br>';
		$no_var = 1;
	}
}
if($no_var == 1)
	CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/burbon.snow/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
?>
<script src="/bitrix/js/burbon.snow/jquery-1.10.0.min.js" type="text/javascript"></script>
<script>
function CHANGE(obj) {
			temp = $(obj).find('option:selected').attr('id');
			arS = temp.split('_');
			site = arS[1];

			if(temp == 'snowSnow_'+site) {
				$('#snowCount_'+site).val(65); $('#snowWidth_'+site).val(25); $('#snowHeight_'+site).val(25); $('#snowSpeedX_'+site).val(2); $('#snowSpeedY_'+site).val(2);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', 'checked');
				$('#snowMouseWind_'+site).attr('checked', 'checked');
				$('#snowRevers_'+site).attr('checked', false);
			}
			if(temp == 'snowBlizzard_'+site) {
				$('#snowCount_'+site).val(85); $('#snowWidth_'+site).val(13); $('#snowHeight_'+site).val(13); $('#snowSpeedX_'+site).val(3); $('#snowSpeedY_'+site).val(2);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', 'checked');
				$('#snowMouseWind_'+site).attr('checked', 'checked');
				$('#snowRevers_'+site).attr('checked', false);
			}
			if(temp == 'rainRain_'+site) {
				$('#snowCount_'+site).val(100); $('#snowWidth_'+site).val(3); $('#snowHeight_'+site).val(30); $('#snowSpeedX_'+site).val(5); $('#snowSpeedY_'+site).val(20);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', 'checked');
				$('#snowRevers_'+site).attr('checked', false);
			}
			if(temp == 'foliageFoliage_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(35); $('#snowHeight_'+site).val(35); $('#snowSpeedX_'+site).val(5); $('#snowSpeedY_'+site).val(3);
				$('#snowRotate_'+site).attr('checked', 'checked');
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', 'checked');
				$('#snowRevers_'+site).attr('checked', false);
			}
			if(temp == 'heartsHearts_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(25); $('#snowHeight_'+site).val(25); $('#snowSpeedX_'+site).val(2); $('#snowSpeedY_'+site).val(1);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', false);
			}
			if(temp == 'cocktailCocktail_'+site) {
				$('#snowCount_'+site).val(7); $('#snowWidth_'+site).val(25); $('#snowHeight_'+site).val(25); $('#snowSpeedX_'+site).val(2); $('#snowSpeedY_'+site).val(1);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', false);
			}
			if(temp == 'flowersFlowers_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(25); $('#snowHeight_'+site).val(25); $('#snowSpeedX_'+site).val(2); $('#snowSpeedY_'+site).val(1);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', true);
				$('#snowRevers_'+site).attr('checked', false);
			}
			if(temp == 'snowFir_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(60); $('#snowHeight_'+site).val(60); $('#snowSpeedX_'+site).val(1); $('#snowSpeedY_'+site).val(2);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', true);
			}
			if(temp == 'snowSanta_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(60); $('#snowHeight_'+site).val(60); $('#snowSpeedX_'+site).val(1); $('#snowSpeedY_'+site).val(2);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', true);
			}
			if(temp == 'snowSnowman_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(60); $('#snowHeight_'+site).val(60); $('#snowSpeedX_'+site).val(1); $('#snowSpeedY_'+site).val(2);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', true);
			}
			if(temp == 'snowSock_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(60); $('#snowHeight_'+site).val(60); $('#snowSpeedX_'+site).val(1); $('#snowSpeedY_'+site).val(2);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', true);
			}
			if(temp == 'snowCokie_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(60); $('#snowHeight_'+site).val(60); $('#snowSpeedX_'+site).val(1); $('#snowSpeedY_'+site).val(2);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', true);
			}
			if(temp == 'snowNewyear_'+site) {
				$('#snowCount_'+site).val(7); $('#snowWidth_'+site).val(100); $('#snowHeight_'+site).val(100); $('#snowSpeedX_'+site).val(1); $('#snowSpeedY_'+site).val(2);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', true);
			}
			if(temp == 'bubbleBubble_'+site) {
				$('#snowCount_'+site).val(10); $('#snowWidth_'+site).val(25); $('#snowHeight_'+site).val(25); $('#snowSpeedX_'+site).val(2); $('#snowSpeedY_'+site).val(3);
				$('#snowRotate_'+site).attr('checked', false);
				$('#snowStick_'+site).attr('checked', false);
				$('#snowMouseWind_'+site).attr('checked', false);
				$('#snowRevers_'+site).attr('checked', true);
			}
}

$(document).ready(function() {
	SITES_LIST = <?=CUtil::PhpToJsObject($js_sites)?>;
	for(y=0; y<<?=count($sites_list)?>;y++) {
		$('#snowTemplates_'+SITES_LIST[y]).change(
			function() {
				CHANGE(this);
			}
		);
	}
});
</script>
<?
$module_id = "burbon.snow";

if($REQUEST_METHOD=="POST" && check_bitrix_sessid()) {
  for($i=0;$i<count($sites_list);$i++){
    $keys = array_keys($sites_list[$i]);
	if($_POST['default']) {
		if($i == 0)
			COption::RemoveOption($module_id);
		$_POST['snowCount_'.$keys[0]] = 65;
		$_POST['snowWidth_'.$keys[0]] = 25;
		$_POST['snowHeight_'.$keys[0]] = 25;
		$_POST['snowSpeedX_'.$keys[0]] = 2;
		$_POST['snowSpeedY_'.$keys[0]] = 2;
		$_POST['snowPath_'.$keys[0]] = '/bitrix/js/burbon.snow/precipitation/snow/';
		if($i == 0)
			$_POST['snowActive_'.$keys[0]] = 'Y';
		else
			$_POST['snowActive_'.$keys[0]] = '';
		$_POST['snowAutorizeActive_'.$keys[0]] = '';
		$_POST['snowAdminActive_'.$keys[0]] = '';
		$_POST['snowStick_'.$keys[0]] = '';
		$_POST['snowMouseWind_'.$keys[0]] = 'Y';
		$_POST['snowRotate_'.$keys[0]] = '';
		$_POST['snowRevers_'.$keys[0]] = '';
	}
	//echo "<pre>"; print_r ($_POST); echo "</pre>";
	
	if(isset($_POST['snowActive_'.$keys[0]]) && htmlspecialcharsEx($_POST['snowActive_'.$keys[0]]) == "Y")
		COption::SetOptionString($module_id, "snowActive_".$keys[0], "Y");
	else
		//COption::RemoveOption($module_id, "snowActive");
		COption::SetOptionString($module_id, "snowActive_".$keys[0], "N");
		
	if(isset($_POST['snowAutorizeActive_'.$keys[0]]) && htmlspecialcharsEx($_POST['snowAutorizeActive_'.$keys[0]]) == "Y")
		COption::SetOptionString($module_id, "snowAutorizeActive_".$keys[0], "Y");
	else
		COption::RemoveOption($module_id, "snowAutorizeActive");
		
	if(isset($_POST['snowAdminActive_'.$keys[0]]) && htmlspecialcharsEx($_POST['snowAdminActive_'.$keys[0]]) == "Y")
		COption::SetOptionString($module_id, "snowAdminActive_".$keys[0], "Y");
	else
		COption::RemoveOption($module_id, "snowAdminActive_".$keys[0]);
		
	if(isset($_POST['snowPath_'.$keys[0]]) && htmlspecialcharsEx($_POST['snowPath_'.$keys[0]]) != 'N') {
		COption::SetOptionString($module_id, "snowPath_".$keys[0], htmlspecialcharsEx($_POST['snowPath_'.$keys[0]]));
		COption::RemoveOption($module_id, "snowPathUser_".$keys[0]);
	}
	elseif(htmlspecialcharsEx($_POST['snowPath_'.$keys[0]]) == 'N') {
		if(isset($_POST['snowPathUser_'.$keys[0]]) && strlen(htmlspecialcharsEx($_POST['snowPathUser_'.$keys[0]])) > 0) {
			COption::SetOptionString($module_id, "snowPathUser_".$keys[0], htmlspecialcharsEx($_POST['snowPathUser_'.$keys[0]]));
			COption::RemoveOption($module_id, "snowPath_".$keys[0]);
		}
		else {
			COption::RemoveOption($module_id, "snowPathUser_".$keys[0]);
		}
	}
	else {
		COption::RemoveOption($module_id, "snowPath_".$keys[0]);
	}
	
	if(isset($_POST['snowCount_'.$keys[0]]) && strlen(htmlspecialcharsEx($_POST['snowCount_'.$keys[0]])) > 0)
		COption::SetOptionString($module_id, "snowCount_".$keys[0], htmlspecialcharsEx($_POST['snowCount_'.$keys[0]]));
	else
		COption::RemoveOption($module_id, "snowCount_".$keys[0]);
	
	if(isset($_POST['snowSpeedX_'.$keys[0]]) && strlen(htmlspecialcharsEx($_POST['snowSpeedX_'.$keys[0]])) > 0)
		COption::SetOptionString($module_id, "snowSpeedX_".$keys[0], htmlspecialcharsEx($_POST['snowSpeedX_'.$keys[0]]));
	else
		COption::RemoveOption($module_id, "snowSpeedX_".$keys[0]);
		
	if(isset($_POST['snowSpeedY_'.$keys[0]]) && strlen(htmlspecialcharsEx($_POST['snowSpeedY_'.$keys[0]])) > 0)
		COption::SetOptionString($module_id, "snowSpeedY_".$keys[0], htmlspecialcharsEx($_POST['snowSpeedY_'.$keys[0]]));
	else
		COption::RemoveOption($module_id, "snowSpeedY_".$keys[0]);
		
	if(isset($_POST['snowStick_'.$keys[0]]) && htmlspecialcharsEx($_POST['snowStick_'.$keys[0]]) == "Y")
		COption::SetOptionString($module_id, "snowStick_".$keys[0], "Y");
	else
		COption::RemoveOption($module_id, "snowStick_".$keys[0]);
		
	if(isset($_POST['snowRotate_'.$keys[0]]) && htmlspecialcharsEx($_POST['snowRotate_'.$keys[0]]) == "Y")
		COption::SetOptionString($module_id, "snowRotate_".$keys[0], "Y");
	else
		COption::RemoveOption($module_id, "snowRotate_".$keys[0]);
		
	if(isset($_POST['snowRevers_'.$keys[0]]) && htmlspecialcharsEx($_POST['snowRevers_'.$keys[0]]) == "Y")
		COption::SetOptionString($module_id, "snowRevers_".$keys[0], "Y");
	else
		COption::RemoveOption($module_id, "snowRevers_".$keys[0]);
	
	if(isset($_POST['snowMouseWind_'.$keys[0]]) && htmlspecialcharsEx($_POST['snowMouseWind_'.$keys[0]]) == "Y")
		COption::SetOptionString($module_id, "snowMouseWind_".$keys[0], "Y");
	else
		COption::RemoveOption($module_id, "snowMouseWind_".$keys[0]);
	
	if(isset($_POST['snowWidth_'.$keys[0]]) && strlen(htmlspecialcharsEx($_POST['snowWidth_'.$keys[0]])) > 0)
		COption::SetOptionString($module_id, "snowWidth_".$keys[0], htmlspecialcharsEx($_POST['snowWidth_'.$keys[0]]));
	else
		COption::RemoveOption($module_id, "snowWidth_".$keys[0]);
		
	if(isset($_POST['snowHeight_'.$keys[0]]) && strlen(htmlspecialcharsEx($_POST['snowHeight_'.$keys[0]])) > 0)
		COption::SetOptionString($module_id, "snowHeight_".$keys[0], htmlspecialcharsEx($_POST['snowHeight_'.$keys[0]]));
	else
		COption::RemoveOption($module_id, "snowHeight_".$keys[0]);
 }
}

$aTabs = array();
foreach($sites_list as $site_arr){
   foreach($site_arr as $site_id=>$site_name){
      $aTabs[] = array('DIV' => 'set'.$site_id, 'TAB' => $site_name, 'ICON' => 'main_user_edit', 'TITLE' => GetMessage('SNOW_OPTION').' '.$site_name);
   }
}
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>


<form action="<?php echo $_SERVER['PHP_SELF'];?>?mid=<?=htmlspecialcharsEx($_REQUEST["mid"])?>" enctype='multipart/form-data' method="post"> 
<?for($i=0;$i<count($aTabs);$i++):?>
<?$keys = array_keys($sites_list[$i]);?>
<?$tabControl->BeginNextTab();?>	
	<tr class="heading">
		<td colspan="2"><b><?=GetMessage("SNOW_MAIN_OPTION")?></b></td>
	</tr>
	<tr>
		<td style="width: 50%;"><label for="snowActive_<?=$keys[0]?>"><?=GetMessage("SNOW_ACTIVE")?></label></td>
		<td>
			<input type="checkbox" id="snowActive_<?=$keys[0]?>" class="adm-designed-checkbox" name="snowActive_<?=$keys[0]?>" value="Y" <?if(/*!COption::GetOptionString($module_id, "snowActive_".$keys[0]) || */COption::GetOptionString($module_id, "snowActive_".$keys[0]) == 'Y') echo 'checked="checked"';?>>
			<label class="adm-designed-checkbox-label" for="snowActive_<?=$keys[0]?>" title=""></label>
		</td>
	</tr>
	<tr>
		<td style="width: 50%;"><label for="snowAutorizeActive_<?=$keys[0]?>"><?=GetMessage("SNOW_AUTORIZE_ACTIVE")?></label></td>
		<td>
			<input type="checkbox" id="snowAutorizeActive_<?=$keys[0]?>" class="adm-designed-checkbox" name="snowAutorizeActive_<?=$keys[0]?>" value="Y" <?if(COption::GetOptionString($module_id, "snowAutorizeActive_".$keys[0])) echo 'checked="checked"';?>>
			<label class="adm-designed-checkbox-label" for="snowAutorizeActive_<?=$keys[0]?>" title=""></label>
		</td>
	</tr>
	<tr>
		<td style="width: 50%;"><label for="snowAdminActive_<?=$keys[0]?>"><?=GetMessage("SNOW_ADMIN_ACTIVE")?></label></td>
		<td>
			<input type="checkbox" id="snowAdminActive_<?=$keys[0]?>" class="adm-designed-checkbox" name="snowAdminActive_<?=$keys[0]?>" value="Y" <?if(COption::GetOptionString($module_id, "snowAdminActive_".$keys[0])) echo 'checked="checked"';?>>
			<label class="adm-designed-checkbox-label" for="snowAdminActive_<?=$keys[0]?>" title=""></label>
		</td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("SNOW_DIR")?><br>
			<small style=" width: 300px; display: inline-block;"><?=GetMessage("SNOW_DIR_SMALL")?></small>
		</td>
		<td>
			<select name="snowPath_<?=$keys[0]?>" id="snowTemplates_<?=$keys[0]?>">
				<option id="snowSnow_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/snow/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/snow/') echo 'selected="selected"';?>><?=GetMessage("SNOW_SNOW")?></option>
				<option id="snowBlizzard_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/blizzard/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/blizzard/') echo 'selected="selected"';?>><?=GetMessage("SNOW_BLIZZARD")?></option>
				<option id="snowNewyear_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/newyear/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/newyear/') echo 'selected="selected"';?>><?=GetMessage("SNOW_NEWYEAR")?></option>
				<option id="snowFir_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/fir/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/fir/') echo 'selected="selected"';?>><?=GetMessage("SNOW_FIR")?></option>
				<option id="snowSanta_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/santa/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/santa/') echo 'selected="selected"';?>><?=GetMessage("SNOW_SANTA")?></option>
				<option id="snowSnowman_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/snowman/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/snowman/') echo 'selected="selected"';?>><?=GetMessage("SNOW_SNOWMAN")?></option>
				<option id="snowSock_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/sock/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/sock/') echo 'selected="selected"';?>><?=GetMessage("SNOW_SOCK")?></option>
				<option id="snowCokie_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/cokie/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/cokie/') echo 'selected="selected"';?>><?=GetMessage("SNOW_COKIE")?></option>
				<option id="rainRain_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/rain/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/rain/') echo 'selected="selected"';?>><?=GetMessage("SNOW_RAIN")?></option>
				<option id="foliageFoliage_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/foliage/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/foliage/') echo 'selected="selected"';?>><?=GetMessage("SNOW_FOLIAGE")?></option>
				<option id="heartsHearts_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/hearts/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/hearts/') echo 'selected="selected"';?>><?=GetMessage("SNOW_HEARTS")?></option>
				<option id="cocktailCocktail_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/cocktail/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/cocktail/') echo 'selected="selected"';?>><?=GetMessage("SNOW_COCKTAIL")?></option>
				<option id="flowersFlowers_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/flowers/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/flowers/') echo 'selected="selected"';?>><?=GetMessage("SNOW_FLOWERS")?></option>
				<option id="bubbleBubble_<?=$keys[0]?>" value="/bitrix/js/burbon.snow/precipitation/bubble/" <?if(COption::GetOptionString($module_id, "snowPath_".$keys[0]) == '/bitrix/js/burbon.snow/precipitation/bubble/') echo 'selected="selected"';?>><?=GetMessage("SNOW_BUBBLE")?></option>
				<option value="N" <?if(COption::GetOptionString($module_id, "snowPathUser_".$keys[0])) echo 'selected="selected"';?>><?=GetMessage("SNOW_ATHER")?></option>
			</select>
			<input type="text" id="snowPathUser_<?=$keys[0]?>" name="snowPathUser_<?=$keys[0]?>" value="<?if(COption::GetOptionString($module_id, "snowPathUser_".$keys[0])) echo COption::GetOptionString($module_id, "snowPathUser_".$keys[0]);?>">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SNOW_MAX_COUNT")?></td>
		<td><input type="text" id="snowCount_<?=$keys[0]?>" name="snowCount_<?=$keys[0]?>" value="<?if(COption::GetOptionString($module_id, "snowCount_".$keys[0])) echo COption::GetOptionString($module_id, "snowCount_".$keys[0]); else echo '65';?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("SNOW_WIDTH")?></td>
		<td><input type="text" id="snowWidth_<?=$keys[0]?>" name="snowWidth_<?=$keys[0]?>" value="<?if(COption::GetOptionString($module_id, "snowWidth_".$keys[0])) echo COption::GetOptionString($module_id, "snowWidth_".$keys[0]); else echo '15';?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("SNOW_HEIGHT")?></td>
		<td><input type="text" id="snowHeight_<?=$keys[0]?>" name="snowHeight_<?=$keys[0]?>" value="<?if(COption::GetOptionString($module_id, "snowHeight_".$keys[0])) echo COption::GetOptionString($module_id, "snowHeight_".$keys[0]); else echo '15';?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("SNOW_MAX_SPEED_X")?></td>
		<td><input type="text" id="snowSpeedX_<?=$keys[0]?>" name="snowSpeedX_<?=$keys[0]?>" value="<?if(COption::GetOptionString($module_id, "snowSpeedX_".$keys[0])) echo COption::GetOptionString($module_id, "snowSpeedX_".$keys[0]); else echo '2';?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("SNOW_MAX_SPEED_Y")?></td>
		<td><input type="text" id="snowSpeedY_<?=$keys[0]?>" name="snowSpeedY_<?=$keys[0]?>" value="<?if(COption::GetOptionString($module_id, "snowSpeedY_".$keys[0])) echo COption::GetOptionString($module_id, "snowSpeedY_".$keys[0]); else echo '2';?>"></td>
	</tr>
	<tr>
		<td style="width: 50%;"><label for="snowRotate_<?=$keys[0]?>"><?=GetMessage("SNOW_ROTATE")?></label></td>
		<td>
			<input type="checkbox" id="snowRotate_<?=$keys[0]?>" class="adm-designed-checkbox" name="snowRotate_<?=$keys[0]?>" value="Y" <?if(COption::GetOptionString($module_id, "snowRotate_".$keys[0])) echo 'checked="checked"'; else echo '2';?>>
			<label class="adm-designed-checkbox-label" for="snowRotate_<?=$keys[0]?>" title=""></label>
		</td>
	</tr>
	<tr>
		<td style="width: 50%;"><label for="snowStick_<?=$keys[0]?>"><?=GetMessage("SNOW_STICK")?></label></td>
		<td>
			<input type="checkbox" id="snowStick_<?=$keys[0]?>" class="adm-designed-checkbox" name="snowStick_<?=$keys[0]?>" value="Y" <?if(COption::GetOptionString($module_id, "snowStick_".$keys[0])) echo 'checked="checked"';?>>
			<label class="adm-designed-checkbox-label" for="snowStick_<?=$keys[0]?>" title=""></label>
		</td>
	</tr>
	<tr>
		<td style="width: 50%;"><label for="snowMouseWind_<?=$keys[0]?>"><?=GetMessage("SNOW_MOUSE_WIND")?></label></td>
		<td>
			<input type="checkbox" id="snowMouseWind_<?=$keys[0]?>" class="adm-designed-checkbox" name="snowMouseWind_<?=$keys[0]?>" value="Y" <?if(COption::GetOptionString($module_id, "snowMouseWind_".$keys[0])) echo 'checked="checked"';?>>
			<label class="adm-designed-checkbox-label" for="snowMouseWind_<?=$keys[0]?>" title=""></label>
		</td>
	</tr>
	<tr>
		<td style="width: 50%;"><label for="snowRevers_<?=$keys[0]?>"><?=GetMessage("SNOW_REVERS")?></label></td>
		<td>
			<input type="checkbox" id="snowRevers_<?=$keys[0]?>" class="adm-designed-checkbox" name="snowRevers_<?=$keys[0]?>" value="Y" <?if(COption::GetOptionString($module_id, "snowRevers_".$keys[0])) echo 'checked="checked"'; else echo '2';?>>
			<label class="adm-designed-checkbox-label" for="snowRevers_<?=$keys[0]?>" title=""></label>
		</td>
	</tr>
<?endfor;?>
<?$tabControl->Buttons();?>
	<p>
		<input type="submit" value="<?=GetMessage("SNOW_SAVE")?>" class="adm-btn-save">
		<input type="submit" value="<?=GetMessage("SNOW_DEFAULT")?>" name="default">
		<?=bitrix_sessid_post();?>
	</p>
<?$tabControl->End();?>
</form>