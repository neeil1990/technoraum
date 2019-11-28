<?
require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Type;

$id_module = 'dwstroy.pricechanger';
if (!Loader::includeModule( $id_module ) )
    die();

$POST_RIGHT = $APPLICATION->GetGroupRight( "dwstroy.pricechanger" );
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$PID = intval($_REQUEST['PID']);
$ID = intval($_REQUEST['ID']);

if($_REQUEST['action'] == 'price_changer_run' && check_bitrix_sessid())
{
    $priceChanger = new CPriceChanger();

    $res = $priceChanger->run($ID, ($PID?$PID:false));

    $msg = '';
    if( $res['TOTAL'] == 100 ){
        $msg = GetMessage('PRICE_CHANGER_RUN_FINISH');
    }

    echo CCPriceChanger::showProgress($msg, GetMessage('PRICE_CHANGER_RUN_TITLE'), $res['TOTAL']);


    if($res['PID'] !== false)
    {
         ?>
        <script>
            top.BX.runPC(<?=$ID?>, '<?=$res['PID']?>');
        </script>
    <?
    }
    else
    {
        ?>
        <script>
            top.BX.finishPC();
        </script>
    <?
    }
}