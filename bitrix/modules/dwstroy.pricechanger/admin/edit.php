<?
use Dwstroy\PriceChanger\ConditionTable;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;
use Bitrix\Main\Text\Converter;

require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$id_module = 'dwstroy.pricechanger';
if (!Loader::includeModule( 'iblock' ) || !Loader::includeModule( $id_module ) || !Loader::includeModule( 'sale' ))
	die();

$POST_RIGHT = $APPLICATION->GetGroupRight( "dwstroy.pricechanger" );
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$aTabs = array(
		array(
				"DIV" => "edit1",
				"TAB" => GetMessage( "PRICE_CHANGER_EDIT_TAB_CONDITION" ),
				"ICON" => "main_user_edit",
				"TITLE" => GetMessage( "PRICE_CHANGER_EDIT_TAB_CONDITION_TITLE" )
		)
);
$tabControl = new CAdminForm( "tabControl", $aTabs );

$ID = intval( $ID );

if ($ID > 0 && $_REQUEST["refresh"] != 'OK')
{
	$conditionRes = ConditionTable::getById( $ID );
	$condition = $conditionRes->fetch();
}elseif($_REQUEST["refresh"] == 'OK'){
    $CONDITIONS = '';
    $obCond3 = new AMCondTree();
    $boolCond = $obCond3->Init( BT_COND_MODE_PARSE, BT_COND_BUILD_CATALOG, array() );
    $condition["RULE"] = $obCond3->Parse( $rule );


    $ACTIONS = '';
    $obAct3 = new AMSaleActionTree();
    $boolAct = $obAct3->Init(BT_COND_MODE_PARSE, BT_COND_BUILD_SALE_ACTIONS, array('PREFIX' => 'actrl', 'INIT_CONTROLS' => array()));
    $condition["ACTIONS"] = $obAct3->Parse();
}


if (isset( $_REQUEST["NAME"] ) && $_REQUEST["NAME"])
	$condition["NAME"] = $_REQUEST["NAME"];
if (isset( $_REQUEST["ACTIVE"] ) && $_REQUEST["ACTIVE"])
	$condition["ACTIVE"] = $_REQUEST["ACTIVE"];
if (isset( $_REQUEST["SORT"] ) && $_REQUEST["SORT"])
	$condition["SORT"] = $_REQUEST["SORT"];
if (isset( $_REQUEST["COUNT"] ) && $_REQUEST["COUNT"])
    $condition["COUNT"] = $_REQUEST["COUNT"];
if (isset( $_REQUEST["PERIOD"] ) && $_REQUEST["PERIOD"])
    $condition["PERIOD"] = $_REQUEST["PERIOD"];
if (isset( $_REQUEST["INTERVAL"] ) && $_REQUEST["INTERVAL"])
    $condition["INTERVAL"] = $_REQUEST["INTERVAL"];
if (isset( $_REQUEST["SITES"] ) && $_REQUEST["SITES"])
	$condition["SITES"] = $_REQUEST["SITES"];
if (isset( $_REQUEST["NEXT_EXEC"] ) && $_REQUEST["NEXT_EXEC"]){
    $date = new DateTime($_REQUEST["NEXT_EXEC"]);
    $condition["NEXT_EXEC"] = new Type\DateTime( $date->format('Y-m-d H:i:s'), 'Y-m-d H:i:s' );
}


if (isset( $_REQUEST["RULE"] ) && $_REQUEST["RULE"])
	$condition["RULE"] = $_REQUEST["RULE"];

if (isset( $_REQUEST["ACTIONS"] ) && $_REQUEST["ACTIONS"])
    $condition["ACTIONS"] = $_REQUEST["ACTIONS"];




$message = null;

// ACTION
if (isset( $_REQUEST['action'] ))
{
	if ($_REQUEST['action'] == "copy")
	{
		if ($ID > 0)
		{
			$conditionRes = ConditionTable::getById( $ID );
			$condition = $conditionRes->fetch();
			$arFields = Array(
					"ACTIVE" => $condition['ACTIVE'],
					"NAME" => $condition['NAME'],
					"SORT" => $condition['SORT'],
					"SITES" => $condition['SITES'],
					"DATE_CHANGE" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
					"RULE" => $condition['RULE'],
					"ACTIONS" => $condition['ACTIONS'],
					"COUNT" => $condition['COUNT'],
					"INTERVAL" => $condition['INTERVAL'],
					"PERIOD" => $condition['PERIOD'],
					"NEXT_EXEC" => $condition['NEXT_EXEC'],
			);
			$result = ConditionTable::add( $arFields );
			if ($result->isSuccess())
			{
				$ID = $result->getId();
				LocalRedirect( "/bitrix/admin/dwstroy.pricechanger_edit.php?ID=" . $ID . "lang=" . LANG);
			}
		}
	}
}

// POST
if ($REQUEST_METHOD == "POST" && ($save != "" || $apply != "" || $save_and_add != "") && $POST_RIGHT == "W" && check_bitrix_sessid())
{
	$CONDITIONS = '';
	$obCond3 = new AMCondTree();
	$boolCond = $obCond3->Init( BT_COND_MODE_PARSE, BT_COND_BUILD_CATALOG, array() );
	$CONDITIONS = $obCond3->Parse( $rule );


    $ACTIONS = '';
    $obAct3 = new AMSaleActionTree();
    $boolAct = $obAct3->Init(BT_COND_MODE_PARSE, BT_COND_BUILD_SALE_ACTIONS, array('PREFIX' => 'actrl', 'INIT_CONTROLS' => array()));
    if (!$boolAct)
    {
        if ($ex = $APPLICATION->GetException())
            $errors[] = $ex->GetString();
        else
            $errors[] = (0 < $discountID ? str_replace('#ID#', $discountID, GetMessage('PRICE_CHANGER_DISCOUNT_EDIT_ERR_UPDATE')) : GetMessage('PRICE_CHANGER_DISCOUNT_EDIT_ERR_ADD'));
    }
    else
    {
        $boolAct = false;

        if (!$boolAct)
            $ACTIONS = $obAct3->Parse();
        if (empty($ACTIONS))
        {
            if ($ex = $APPLICATION->GetException())
                $errors[] = $ex->GetString();
            else
                $errors[] = (0 < $discountID ? str_replace('#ID#', $discountID, GetMessage('PRICE_CHANGER_DISCOUNT_EDIT_ERR_UPDATE')) : GetMessage('PRICE_CHANGER_DISCOUNT_EDIT_ERR_ADD'));
            $boolActParseError = true;
        }
    }


    $date = new DateTime($NEXT_EXEC);
    $NEXT_EXEC = new Type\DateTime( $date->format('Y-m-d H:i:s'), 'Y-m-d H:i:s' );

	if (!isset( $SITES ))
		$SITES = array();
	$arFields = Array(
			"ACTIVE" => ($ACTIVE != "Y" ? "N" : "Y"),
			"NAME" => $NAME,
			"SORT" => $SORT,
			"SITES" =>  $SITES ,
			"DATE_CHANGE" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
			"RULE" => serialize( $CONDITIONS ),
			"ACTIONS" => serialize( $ACTIONS ),
            "COUNT" => $COUNT,
            "INTERVAL" => $INTERVAL,
            "PERIOD" => ($PERIOD != "Y" ? "N" : "Y"),
            "NEXT_EXEC" => $NEXT_EXEC,
	);
    if( $PERIOD == "Y" ){
        if( !$INTERVAL ){
            $errors[] = GetMessage('PRICE_CHANGER_DISCOUNT_INTERVAL_ERR_EMPTY');
        }
    }

	if ($ID > 0 && empty($errors))
	{
		$result = ConditionTable::update( $ID, $arFields );
		if (!$result->isSuccess())
		{
			$errors = $result->getErrorMessages();
			$res = false;
		}
		else
			$res = true;
	}
	elseif( empty($errors) )
	{
		$result = ConditionTable::add( $arFields );
		if ($result->isSuccess())
		{
			$ID = $result->getId();
			$res = true;
		}
		else
		{
			$errors = $result->getErrorMessages();
			$res = false;
		}
	}

	if ($res)
	{
        if( $PERIOD == "Y" ){
            $curDate = new DateTime();
            $rsAgents = CAgent::GetList(
                array(),
                array(
                    'NAME' => "CCPriceChanger::periodPriceChange(".$ID.", false);",
                )
            );

            if( $arAgent = $rsAgents->Fetch() ){

                CAgent::Update($arAgent['ID'],
                               array(
                                   'NAME' => "CCPriceChanger::periodPriceChange(".$ID.", false);",
                                   'NEXT_EXEC' => $NEXT_EXEC,
                                   'DATE_CHECK' => '',
                                   'RUNNING' => 'N'
                               )
                );
            }else{
                CAgent::AddAgent(
                    "CCPriceChanger::periodPriceChange(".$ID.", false);",  // имя функции
                    "dwstroy.pricechanger",                // идентификатор модуля
                    "N",                      // агент не критичен к кол-ву запусков
                    0,                    // интервал запуска - 1 сутки
                    $NEXT_EXEC,                       // дата первой проверки - текущее
                    "Y",                      // агент активен
                    $NEXT_EXEC,                       // дата первого запуска - текущее
                    30);
            }
        }else{
            $rsAgents = CAgent::GetList(
                array(),
                array(
                    'NAME' => "CCPriceChanger::periodPriceChange(".$ID.", false);",
                )
            );

            if( $arAgent = $rsAgents->Fetch() ){

                CAgent::Delete($arAgent['ID']);
            }
        }

        if ($apply != "")
			LocalRedirect( BX_ROOT."/admin/dwstroy.pricechanger_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam() );
		elseif( $save != "" )
			LocalRedirect( BX_ROOT."/admin/dwstroy.pricechanger_list.php?lang=" . LANG );
        elseif( $save_and_add != "" )
            LocalRedirect(BX_ROOT."/admin/dwstroy.pricechanger_list.php?lang=".LANGUAGE_ID."&run=".$ID."&".bitrix_sessid_get());
	}
}

$APPLICATION->SetTitle( ($ID > 0 ? GetMessage( "PRICE_CHANGER_EDIT_EDIT" ) . $ID : GetMessage( "PRICE_CHANGER_EDIT_ADD" )) );
require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


$aMenu = array(
		array(
				"TEXT" => GetMessage( "PRICE_CHANGER_EDIT_LIST" ),
				"TITLE" => GetMessage( "PRICE_CHANGER_EDIT_LIST_TITLE" ),
				"LINK" => "dwstroy.pricechanger_list.php?lang=" . LANG,
				"ICON" => "btn_list" 
		) 
);
if ($ID > 0)
{
	$aMenu[] = array(
			"SEPARATOR" => "Y" 
	);
	$aMenu[] = array(
			"TEXT" => GetMessage( "PRICE_CHANGER_EDIT_ADD" ),
			"TITLE" => GetMessage( "PRICE_CHANGER_EDIT_ADD_TITLE" ),
			"LINK" => "dwstroy.pricechanger_edit.php?lang=" . LANG,
			"ICON" => "btn_new" 
	);
	$aMenu[] = array(
			"TEXT" => GetMessage( "PRICE_CHANGER_EDIT_COPY" ),
			"TITLE" => GetMessage( "PRICE_CHANGER_EDIT_COPY_TITLE" ),
			"LINK" => "dwstroy.pricechanger_edit.php?action=copy&ID=" . $ID . "lang=" . LANG . "&" . bitrix_sessid_get() . "';",
			"ICON" => "btn_new" 
	);
	$aMenu[] = array(
			"TEXT" => GetMessage( "PRICE_CHANGER_EDIT_DEL" ),
			"TITLE" => GetMessage( "PRICE_CHANGER_EDIT_DEL_TITLE" ),
			"LINK" => "javascript:if(confirm('" . GetMessage( "PRICE_CHANGER_EDIT_DEL_CONF" ) . "'))window.location='dwstroy.pricechanger_list.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&" . bitrix_sessid_get() . "';",
			"ICON" => "btn_delete" 
	);
}
$context = new CAdminContextMenu( $aMenu );
$context->Show();
?>

<?
if ($_REQUEST["mess"] == "ok" && $ID > 0)
	CAdminMessage::ShowMessage( array(
			"MESSAGE" => GetMessage( "PRICE_CHANGER_EDIT_SAVED" ),
			"TYPE" => "OK" 
	) );
elseif( count( $errors ) )
    CAdminMessage::ShowMessage( array(
                                    "MESSAGE" => implode("\r\n", $errors),
                                    "TYPE" => "ERROR"
                                ) );
	
	// Calculate start values

$arIBlockTypeSel = array();

$SitesAll = array();
$rsSites = CSite::GetList( $by = "sort", $order = "desc", Array() );
while ( $arSite = $rsSites->Fetch() )
{
	array_push( $SitesAll, $arSite['LID'] );
}

$tabControl->Begin( array(
		"FORM_ACTION" => $APPLICATION->GetCurPage() 
) );

$tabControl->BeginNextFormTab();

$tabControl->AddViewField( 'ID', GetMessage( "PRICE_CHANGER_EDIT_ID" ), $ID, false ); // ID
$tabControl->AddCheckBoxField( "ACTIVE", GetMessage( "PRICE_CHANGER_EDIT_ACT" ), false, "Y", ($condition['ACTIVE'] == "Y" || !isset( $condition['ACTIVE'] )) );

$tabControl->AddEditField( "NAME", GetMessage( "PRICE_CHANGER_EDIT_NAME" ), true, array(
		"size" => 50,
		"maxlength" => 255 
), htmlspecialcharsbx( $condition['NAME'] ) );
$tabControl->AddEditField( "SORT", GetMessage( "PRICE_CHANGER_EDIT_SORT" ), true, array(
		"size" => 6,
		"maxlength" => 255 
), htmlspecialcharsbx( isset( $condition['SORT'] ) && !empty( $condition['SORT'] ) ) ? $condition['SORT'] : 100 );
$tabControl->AddViewField( 'DATE_CHANGE_TEXT', GetMessage( "PRICE_CHANGER_EDIT_DATE_CHANGE" ), $condition['DATE_CHANGE'], false );

/*$tabControl->BeginCustomField( "SITES", GetMessage( 'PRICE_CHANGER_EDIT_SITES' ), false );
?>
<tr id="tr_SITES">
	<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
	<td>
        <span style="width: 30%;display: inline-block;">
            <?=CLang::SelectBox("SITES", $condition['SITES']);?>
        </span>
        <span style="width: 40%;display: inline-block;">
            <?echo '<input type="submit" name="refresh" value="OK" />';?>
        </span>
</td>
</tr>
<?

$tabControl->EndCustomField( "SITES" );*/


$tabControl->BeginCustomField( "CONDITIONS", GetMessage( 'PRICE_CHANGER_EDIT_SECTIONS_COND' ) . ":", false );
?>
<tr id="tr_CONDITIONS">
	<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
	<td width="60%">
		<div id="tree" style="position: relative; z-index: 1;"></div><?
		if (!is_array( $condition['RULE'] ))
		{
			if (CheckSerializedData( $condition['RULE'] ))
			{
				$condition['RULE'] = unserialize( $condition['RULE'] );
			}
			else
			{
				$condition['RULE'] = '';
			}
		}
		$obCond = new AMCondTree();
		$boolCond = $obCond->Init( BT_COND_MODE_DEFAULT, BT_COND_BUILD_CATALOG, array(
                'SITE_ID' => ( !empty($condition['SITES'])?$condition['SITES']:current($SitesAll)),
				'FORM_NAME' => 'tabControl_form',
				'CONT_ID' => 'tree',
				'JS_NAME' => 'JSCond' 
		) );
		if (!$boolCond)
		{
			
			if ($ex = $APPLICATION->GetException())
				echo $ex->GetString() . "<br>";
		}
		else
		{
			$obCond->Show( $condition['RULE'] );
		}
		?></td>
</tr>
<?$APPLICATION->AddHeadString('<style>span.condition-alert{display:none;}</style>',true)?>

<?
$tabControl->EndCustomField( "CONDITIONS" );

$tabControl->BeginCustomField( "ACTIONS", GetMessage( 'PRICE_CHANGER_EDIT_SECTIONS_ACTIONS' ) . ":", false );
?>
    <tr id="tr_ACTIONS">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td width="60%">
            <div id="tree_actions" style="position: relative; z-index: 1;"></div><?
            if (!is_array( $condition['ACTIONS'] ))
            {
                if (CheckSerializedData( $condition['ACTIONS'] ))
                {
                    $condition['ACTIONS'] = unserialize( $condition['ACTIONS'] );
                }
                else
                {
                    $condition['ACTIONS'] = '';
                }
            }
            $arCondParams = array(
                'SITE_ID' => ( !empty($condition['SITES'])?$condition['SITES']:current($SitesAll)),
                'FORM_NAME' => 'tabControl_form',
                'CONT_ID' => 'tree_actions',
                'JS_NAME' => 'JSSaleAct',
                'PREFIX' => 'actrl',
                'INIT_CONTROLS' => array(),
                'SYSTEM_MESSAGES' => array(
                    'SELECT_CONTROL' => GetMessage('PRICE_CHANGER_DISCOUNT_ACTIONS_SELECT_CONTROL'),
                    'ADD_CONTROL' => GetMessage('PRICE_CHANGER_DISCOUNT_ACTIONS_ADD_CONTROL'),
                    'DELETE_CONTROL' => GetMessage('PRICE_CHANGER_DISCOUNT_ACTIONS_DELETE_CONTROL'),
                ),
            );
            $obAct = new AMSaleActionTree();
            $boolAct = $obAct->Init(BT_COND_MODE_DEFAULT, BT_COND_BUILD_SALE_ACTIONS, $arCondParams);
            if (!$boolAct)
            {
                if ($ex = $APPLICATION->GetException())
                    echo $ex->GetString()."<br>";
            }
            else
            {
                $obAct->Show($condition['ACTIONS']);
            }
            ?></td>
    </tr>
<?$APPLICATION->AddHeadString('<style>span.condition-alert{display:none;}</style>',true)?>

<?
$tabControl->EndCustomField( "ACTIONS" );

$tabControl->AddEditField( "COUNT", GetMessage( "PRICE_CHANGER_EDIT_COUNT" ), false, array(
    "size" => 50,
    "maxlength" => 255
),  (empty($condition['COUNT'])?'25':htmlspecialcharsbx( $condition['COUNT'] )));

$tabControl->AddSection("PRICE_CHANGER_PERIOD", GetMessage("PRICE_CHANGER_PREID_LABEL"));

$tabControl->AddCheckBoxField( "PERIOD", GetMessage( "PRICE_CHANGER_EDIT_PERIOD" ), false, "Y", ($condition['PERIOD'] == "Y" ) );

$tabControl->AddEditField( "INTERVAL", GetMessage( "PRICE_CHANGER_EDIT_INTERVAL" ), false, array(
    "size" => 50,
    "maxlength" => 255
),  (empty($condition['INTERVAL'])?'86400':htmlspecialcharsbx( $condition['INTERVAL'] ))  );

$tabControl->BeginCustomField( "NEXT_EXEC", GetMessage( "PRICE_CHANGER_EDIT_NEXT_EXEC" ), false );
?>
    <tr class="adm-detail-field">
        <td width="40%"><?echo GetMessage( "PRICE_CHANGER_EDIT_NEXT_EXEC" )?></td>
        <td width="60%"><?echo CalendarDate("NEXT_EXEC", htmlspecialcharsbx($condition['NEXT_EXEC']) , "tabControl_form", 20);?></td>
    </tr>
<?

$tabControl->EndCustomField( "NEXT_EXEC" );

$tabControl->BeginCustomField( "HID", '', false );
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?


$tabControl->EndCustomField( "HID" );

?>
<?
$arButtonsParams = array(
		"disabled" => $readOnly,
		"back_url" => "/bitrix/admin/dwstroy.pricechanger_list.php?lang=" . LANG
);

$tabControl->Buttons( $arButtonsParams, '<input type="submit" name="save_and_add" value="'.Converter::getHtmlConverter()->encode(GetMessage('PRICE_CHANGER_SAVEANDRUN')).'" />' );
?>

<?
$tabControl->Show();
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>