<?
use Dwstroy\PriceChanger\ConditionTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;
use Bitrix\Main\Text\Converter;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$id_module='dwstroy.pricechanger';
Loader::includeModule($id_module);

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("dwstroy.pricechanger");
if($POST_RIGHT=="D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$CCPriceChanger= new CCPriceChanger();

$sTableID = "b_dwstroy_pricechanger";
$oSort = new CAdminSorting($sTableID, "ID", "desc"); 
$lAdmin = new CAdminList($sTableID, $oSort); 

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	return count($lAdmin->arFilterErrors)==0; 
}



$FilterArr = Array(
	"find",
	"find_id",
	"find_name",
	"find_active",
);


$lAdmin->InitFilter($FilterArr);
$arFilter=array();

if (CheckFilter())
{
	
	if($find!='' && $find_type=='id')
		$arFilter['ID']=$find;
	elseif($find_id!='')
		$arFilter['ID']=$find_id;
	$arFilter['NAME']=$find_name;
	$arFilter['ACTIVE']=$find_active;
	if(empty($arFilter['ID'])) unset($arFilter['ID']);
	if(empty($arFilter['NAME'])) unset($arFilter['NAME']);
	if(empty($arFilter['ACTIVE'])) unset($arFilter['ACTIVE']);
}



if($lAdmin->EditAction())
{
	
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		
		$ID = IntVal($ID);
		if($ID>0)
		{
			foreach($arFields as $key=>$value)
				$arData[$key]=$value;
			$arData['DATE_CHANGE']=new Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
			$result = ConditionTable::update($ID,$arData);
			if (!$result->isSuccess())
        {
            $lAdmin->AddGroupError(GetMessage("PRICE_CHANGER_SAVE_ERROR")." ".GetMessage("PRICE_CHANGER_NO_ZAPIS"), $ID);
        }
		}
		else
		{
			$lAdmin->AddGroupError(GetMessage("PRICE_CHANGER_SAVE_ERROR")." ".GetMessage("PRICE_CHANGER_NO_ZAPIS"), $ID);
		}
	}
}

if($arID = $lAdmin->GroupAction())
{

	if($_REQUEST['action_target']=='selected')
	{
		$rsData=ConditionTable::getList(array(
			'select' => array('ID','NAME','SORT','ACTIVE','DATE_CHANGE'),
			'filter' =>$arFilter,
			'order' => array($by => $order),
		));
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
		continue;
		$ID = IntVal($ID);

		switch($_REQUEST['action'])
		{
			case "delete":
				$result=ConditionTable::delete($ID);
				if(!$result->isSuccess())
				{
					$lAdmin->AddGroupError(GetMessage("PRICE_CHANGER_DEL_ERROR")." ".GetMessage("PRICE_CHANGER_NO_ZAPIS"), $ID);
				}
				break;

			case "activate":
			case "deactivate":
				if($ID>0)
				{
					$arFields["ACTIVE"]=($_REQUEST['action']=="activate"?"Y":"N");
					$result = ConditionTable::update($ID, array(
						'ACTIVE' => $arFields["ACTIVE"],
					));
					if (!$result->isSuccess())
						$lAdmin->AddGroupError(GetMessage("PRICE_CHANGER_SAVE_ERROR")." ".GetMessage("PRICE_CHANGER_NO_ZAPIS"), $ID);
				}
				else
					$lAdmin->AddGroupError(GetMessage("PRICE_CHANGER_SAVE_ERROR")." ".GetMessage("PRICE_CHANGER_NO_ZAPIS"), $ID);

				break;
			case "copy":
				if($ID>0)
				{
					$conditionRes=ConditionTable::getById($ID);
					$condition=$conditionRes->fetch();
					$arFields = Array(
                        "ACTIVE" => $condition['ACTIVE'],
                        "NAME" => $condition['NAME'],
                        "SORT" => $condition['SORT'],
                        "SITES" => $condition['SITES'],
                        "DATE_CHANGE" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
                        "DATE_EXEC" => $condition['DATE_EXEC'],
                        "RULE" => $condition['RULE'],
                        "ACTIONS" => $condition['ACTIONS'],
                        "COUNT" => $condition['COUNT'],
                        "INTERVAL" => $condition['INTERVAL'],
                        "PERIOD" => $condition['PERIOD'],
                        "NEXT_EXEC" => $condition['NEXT_EXEC'],
					);
					$result=ConditionTable::add($arFields);
					if($result->isSuccess())
					{
						$ID = $result->getId();
					}
				}
			break;
		}
	}
}

$rsData=ConditionTable::getList(array(
	'select' => array('ID','NAME','SORT','ACTIVE','DATE_CHANGE', 'DATE_EXEC'),
	'filter' =>$arFilter,
	'order' => array($by => $order),
));

$rsData = new CAdminResult($rsData, $sTableID);

$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PRICE_CHANGER_NAV")));

$lAdmin->AddHeaders(array(
	array(  "id"    =>"ID",
		"content"  =>GetMessage("PRICE_CHANGER_TABLE_ID"),
		"sort"    =>"ID",
		"align"    =>"right",
		"default"  =>true,
	),
	array(  "id"    =>"NAME",
		"content"  =>GetMessage("PRICE_CHANGER_TABLE_TITLE"),
		"sort"    =>"NAME",
		"default"  =>true,
	),
	array(  "id"    =>"SORT",
		"content"  =>GetMessage("PRICE_CHANGER_TABLE_SORT"),
		"sort"    =>"SORT",
		"align"    =>"right",
		"default"  =>true,
	),
	array(  "id"    =>"ACTIVE",
		"content"  =>GetMessage("PRICE_CHANGER_TABLE_ACTIVE"),
		"sort"    =>"ACTIVE",
		"default"  =>true,
	),
	array(  "id"    =>"DATE_CHANGE",
		"content"  =>GetMessage("PRICE_CHANGER_TABLE_DATE_CHANGE"),
		"sort"    =>"DATE_CHANGE",
		"default"  =>true,
	),
	array(  "id"    =>"DATE_EXEC",
		"content"  =>GetMessage("PRICE_CHANGER_TABLE_DATE_EXEC"),
		"sort"    =>"DATE_EXEC",
		"default"  =>true,
	),
    array(  "id"    =>"RUN",
            "content"  => "",
            "sort"    =>"RUN",
            "default"  =>true,
    )
));
while($arRes = $rsData->NavNext(true, "f_")):

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddInputField("NAME", array("size"=>20));
	$row->AddViewField("NAME", '<a href="dwstroy.pricechanger_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_NAME.'</a>');

	$row->AddInputField("SORT", array("size"=>20));

	$row->AddCheckField("ACTIVE");

    $row->AddField("RUN", '<input type="button" class="adm-btn-save" value="'.Converter::getHtmlConverter()->encode(GetMessage('PRICE_CHANGER_RUN')).'" onclick="runPriceChange('.$f_ID.')" name="save" id="price_changer_run_button_'.$f_ID.'" />');

	$arActions = Array();


	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("PRICE_CHANGER_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("dwstroy.pricechanger_edit.php?ID=".$f_ID)
	);

	$arActions[] = array(
			"ICON"=>"copy",
			"TEXT"=>GetMessage("PRICE_CHANGER_COPY"),
			"ACTION"=>$lAdmin->ActionDoGroup($f_ID, "copy")
	);

    $arActions[] = array(
        "ICON"=>"move",
        "TEXT"=>GetMessage("PRICE_CHANGER_RUN"),
        "ACTION"=>'runPriceChange('.$f_ID.');'
    );
	
	if ($POST_RIGHT>="W")
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("PRICE_CHANGER_DEL"),
			"ACTION"=>"if(confirm('".GetMessage('PRICE_CHANGER_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);

			$arActions[] = array("SEPARATOR"=>true);
			if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
				unset($arActions[count($arActions)-1]);

	$row->AddActions($arActions);

	endwhile;

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("PRICE_CHANGER_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("PRICE_CHANGER_LIST_CHECKED"), "value"=>"0"),
	)
);

$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("PRICE_CHANGER_LIST_DELETE"),
	"activate"=>GetMessage("PRICE_CHANGER_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("PRICE_CHANGER_LIST_DEACTIVATE"),
));


$aContext = array(
	array(
		"TEXT"=>GetMessage("PRICE_CHANGER_POST_ADD_TEXT"),
		"LINK"=>"dwstroy.pricechanger_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("PRICE_CHANGER_POST_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);


$lAdmin->AddAdminContextMenu($aContext);


$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("PRICE_CHANGER_TITLE"));


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("PRICE_CHANGER_ID"),
		GetMessage("PRICE_CHANGER_NAME"),
		GetMessage("PRICE_CHANGER_ACTIVE"),
	)
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
<td><b><?=GetMessage("PRICE_CHANGER_FIND")?>:</b></td>
<td>
<input type="text" size="25" name="find" value="<?echo htmlspecialchars($find)?>" title="<?=GetMessage("PRICE_CHANGER_FIND_TITLE")?>">
<?
$arr = array(
		"reference" => array(
			"ID",
		),
		"reference_id" => array(
			"id",
		)
	);
	echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
	?>
	</td>
	</tr>
	<tr>
	<td><?=GetMessage("PRICE_CHANGER_ID")?>:</td>
	<td>
	<input type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>">
</td>
	</tr>
<tr>
<td><?=GetMessage("PRICE_CHANGER_NAME")?>:</td>
<td>
	<input type="text" name="find_name" size="47" value="<?echo htmlspecialchars($find_name)?>">
</td>
</tr>
<tr>
<td><?=GetMessage("PRICE_CHANGER_ACTIVE")?>:</td>
<td>
<?
$arr = array(
	"reference" => array(
		GetMessage("PRICE_CHANGER_POST_YES"),
		GetMessage("PRICE_CHANGER_POST_NO"),
	),
	"reference_id" => array(
		"Y",
		"N",
	)
);
echo SelectBoxFromArray("find_active", $arr, $find_active, "", "");
?>
</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>

    <script>
        function runPriceChange(ID)
        {
            var node = BX('price_changer');

            node.style.display = 'block';

            var windowPos = BX.GetWindowSize();
            var pos = BX.pos(node);

            if(pos.top > windowPos.scrollTop + windowPos.innerHeight)
            {
                window.scrollTo(windowPos.scrollLeft, pos.top + 150 - windowPos.innerHeight);
            }

            BX.runPC(ID, false);
        }

        BX.runPC = function(ID, pid)
        {
            BX.adminPanel.showWait(BX('price_changer_run_button_' + ID));
            BX.ajax.post('/bitrix/admin/dwstroy.pricechanger_run.php', {
                site_id:'<?=LANGUAGE_ID?>',
                action: 'price_changer_run',
                ID: ID,
                PID: pid,
                sessid: BX.bitrix_sessid()
            }, function(data)
            {
                BX.adminPanel.closeWait(BX('price_changer_run_button_' + ID));
                BX('price_changer_progress').innerHTML = data;
            });
        };

        BX.finishPC = function()
        {
            window.tbl_sitemap.GetAdminList('/bitrix/admin/dwstroy.pricechanger_list.php?lang=<?=LANGUAGE_ID?>');
        };
    </script>

    <div id="price_changer" style="display: none;">
        <div id="price_changer_progress"><?=CCPriceChanger::showProgress(GetMessage('PRICE_CHANGER_RUN_INIT'),GetMessage('PRICE_CHANGER_RUN_TITLE'), 0)?></div>
    </div>
<?
if(isset($_REQUEST['run']) && check_bitrix_sessid())
{
    $ID = intval($_REQUEST['run']);
    if($ID > 0)
    {
        ?>
        <script>BX.ready(BX.defer(function(){
                runPriceChange(<?=$ID?>);
            }));
        </script>
    <?
    }
}
?>
<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>