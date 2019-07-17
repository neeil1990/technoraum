<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/xml.php');
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\File;
use Bitrix\Main\Type;

    if (isset( $_REQUEST["TYPE_OF_INFOBLOCK"] ) && $_REQUEST["TYPE_OF_INFOBLOCK"])
        $condition["TYPE_OF_INFOBLOCK"] = $_REQUEST["TYPE_OF_INFOBLOCK"];
    if (isset( $_REQUEST["INFOBLOCK"] ) && $_REQUEST["INFOBLOCK"])
        $condition["INFOBLOCK"] = $_REQUEST["INFOBLOCK"];
    if (isset( $_REQUEST["SECTIONS"] ) && $_REQUEST["SECTIONS"])
        $condition["SECTIONS"] = $_REQUEST["SECTIONS"];
    if (isset( $_REQUEST["URL_DATA_FILE"] ) && $_REQUEST["URL_DATA_FILE"])
        $condition["URL_DATA_FILE"] = $_REQUEST["URL_DATA_FILE"];

    $module_id = 'nbrains.import';

	Loader::includeModule('catalog');
	Loc::loadMessages(__FILE__);

	$aTabs = array(
		array("DIV" => "edit0", "TAB" => "Ќастройка импорта", "ICON" => "currency_settings", "TITLE" => "Ќастройка импорта"),
	);
	$tabControl = new CAdminForm("currencyTabControl", $aTabs);


    $arIBlockTypeSel = array();
    $arIBlockType = CIBlockParameters::GetIBlockTypes();
    foreach ( $arIBlockType as $code => $val )
    {
        $arIBlockTypeSel["REFERENCE_ID"][] = $code;
        $arIBlockTypeSel["REFERENCE"][] = $val;
    }

    if ($condition["TYPE_OF_INFOBLOCK"])
    {

        $rsIBlock = CIBlock::GetList(
            array(
                "sort" => "asc"
            ),
            array(
                "TYPE" => $condition["TYPE_OF_INFOBLOCK"],
                "ACTIVE" => "Y"
            ));
        while ( $arr = $rsIBlock->Fetch() )
        {
            $arIBlockSel["REFERENCE_ID"][] = $arr["ID"];
            $arIBlockSel["REFERENCE"][] = "[" . $arr["ID"] . "] " . $arr["NAME"];
        }
    }

    $sectionLinc = array();
    $arFilter = array(
        'IBLOCK_ID' => $condition["INFOBLOCK"],
    );
    $arSelect = array(
        'ID',
        'NAME',
        'DEPTH_LEVEL'
    );
    $arOrder = array(
        "left_margin"=>"asc"
    );
    if ($condition["INFOBLOCK"])
    {
        $rsSections = CIBlockSection::GetList( $arOrder, $arFilter, false, $arSelect );
        while ( $arSection = $rsSections->GetNext() )
        {
            $sectionLinc["REFERENCE_ID"][] = $arSection["ID"];
            $sectionLinc["REFERENCE"][] = "[" . $arSection["ID"] . "] ". str_repeat(" . ", $arSection["DEPTH_LEVEL"]) . $arSection["NAME"];
        }
    }

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
	{
	    if($condition["INFOBLOCK"] && count($condition["SECTIONS"]) > 0) {

            $arSection = [];
            $tree = CIBlockSection::GetTreeList(Array('IBLOCK_ID' => $condition["INFOBLOCK"]), Array("ID", "XML_ID", "ACTIVE", "CODE", "NAME", "DEPTH_LEVEL", "IBLOCK_SECTION_ID"));
            while ($section = $tree->GetNext()) {
                $arSection[(!$section['IBLOCK_SECTION_ID']) ? 0 : $section['IBLOCK_SECTION_ID']][] = $section;
            }

        }


        if($condition["URL_DATA_FILE"]){

            $xml = new CDataXML();
            $file_path = $condition["URL_DATA_FILE"];
            $xml->Load($_SERVER['DOCUMENT_ROOT'].$file_path);
            $arData = $xml->GetArray();
            $arProduct = array();

            if(count($arData['table']['#']['body']) > 0){
                foreach($arData['table']['#']['body'] as $body){
                    foreach ($body['#']['row'] as $k_row => $row){
                        $ar = array();
                        foreach($row['#'] as $key => $field){
                            $ar[$key] = $field[0]['#'];
                        }
                        $arProduct[$k_row] = $ar;
                    }
                }
            }
        }

        if($condition["INFOBLOCK"] && count($condition["SECTIONS"]) > 0 && $condition["URL_DATA_FILE"]) {

            ob_start();
            print '<?xml version="1.0" encoding="windows-1251"?>';
            ?>
            < оммерческа€»нформаци€ ¬ерси€—хемы="2.021" ƒата‘ормировани€="2019-07-07T10:31:43">
                < лассификатор>

                    <»д>spares</»д>
                    <Ќаименование> аталог запчастей</Ќаименование>

                    <?= build_tree($arSection, 0); ?>

                    <—войства>

                        <—войство>
                            <»д>CML2_ACTIVE</»д>
                            <Ќаименование>Ѕитриксјктивность</Ќаименование>
                            <ћножественное>false</ћножественное>
                        </—войство>
                        <—войство>
                            <»д>CML2_CODE</»д>
                            <Ќаименование>—имвольный код</Ќаименование>
                            <ћножественное>false</ћножественное>
                        </—войство>
                        <—войство>
                            <»д>CML2_SORT</»д>
                            <Ќаименование>—ортировка</Ќаименование>
                            <ћножественное>false</ћножественное>
                        </—войство>

                        <—войство>
                            <»д>ARTICLE</»д>
                            <Ќаименование>јртикул</Ќаименование>
                            <ћножественное>false</ћножественное>
                            <Ѕитрикс—ортировка>500</Ѕитрикс—ортировка>
                            <Ѕитрикс од>ARTICLE</Ѕитрикс од>
                            <Ѕитрикс“ип—войства>S</Ѕитрикс“ип—войства>
                            <Ѕитрикс—трок>1</Ѕитрикс—трок>
                            <Ѕитрикс олонок>30</Ѕитрикс олонок>
                            <Ѕитрикс“ип—писка>L</Ѕитрикс“ип—писка>
                            <Ѕитрикс“ипы‘айлов></Ѕитрикс“ипы‘айлов>
                            <Ѕитрикс оличествоѕолей>5</Ѕитрикс оличествоѕолей>
                            <Ѕитрикс—в€занный»нфоблок></Ѕитрикс—в€занный»нфоблок>
                            <Ѕитриксќписание–азрешено>false</Ѕитриксќписание–азрешено>
                            <Ѕитриксѕоиск–азрешен>false</Ѕитриксѕоиск–азрешен>
                            <Ѕитрикс‘ильтр–азрешен>false</Ѕитрикс‘ильтр–азрешен>
                            <Ѕитрикс–асширение“ипа></Ѕитрикс–асширение“ипа>
                            <Ѕитриксќб€зательное>false</Ѕитриксќб€зательное>
                            <—ериализовано>1</—ериализовано>
                        </—войство>
                        <—войство>
                            <»д>COUNT_COMPLECT</»д>
                            <Ќаименование> оличество в комплекте</Ќаименование>
                            <ћножественное>false</ћножественное>
                            <Ѕитрикс—ортировка>500</Ѕитрикс—ортировка>
                            <Ѕитрикс од>COUNT_COMPLECT</Ѕитрикс од>
                            <Ѕитрикс“ип—войства>S</Ѕитрикс“ип—войства>
                            <Ѕитрикс—трок>1</Ѕитрикс—трок>
                            <Ѕитрикс олонок>30</Ѕитрикс олонок>
                            <Ѕитрикс“ип—писка>L</Ѕитрикс“ип—писка>
                            <Ѕитрикс“ипы‘айлов></Ѕитрикс“ипы‘айлов>
                            <Ѕитрикс оличествоѕолей>5</Ѕитрикс оличествоѕолей>
                            <Ѕитрикс—в€занный»нфоблок></Ѕитрикс—в€занный»нфоблок>
                            <Ѕитриксќписание–азрешено>false</Ѕитриксќписание–азрешено>
                            <Ѕитриксѕоиск–азрешен>false</Ѕитриксѕоиск–азрешен>
                            <Ѕитрикс‘ильтр–азрешен>false</Ѕитрикс‘ильтр–азрешен>
                            <Ѕитрикс–асширение“ипа></Ѕитрикс–асширение“ипа>
                            <Ѕитриксќб€зательное>false</Ѕитриксќб€зательное>
                            <—ериализовано>1</—ериализовано>
                        </—войство>

                    </—войства>

                    <“ипы÷ен>
                        <“ип÷ены>
                            <»д>price</»д>
                            <Ќаименование>price</Ќаименование>
                        </“ип÷ены>
                    </“ипы÷ен>

                </ лассификатор>

                < аталог —одержит“олько»зменени€="true">

                    <»д>spares</»д>
                    <»д лассификатора>spares</»д лассификатора>
                    <Ќаименование> аталог запчастей</Ќаименование>

                    <“овары>

                        <?
                        foreach ($arProduct as $product):
                            $arElementSection = array();
                            $arSelect = Array("ID", "NAME");
                            $arFilter = Array("IBLOCK_ID" => $condition["INFOBLOCK"], "CODE" => ($product['articlenumber']) ? trim($product['articlenumber']) : false);
                            $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                            if($ar_fields = $res->GetNext())
                            {
                                $db_old_groups = CIBlockElement::GetElementGroups($ar_fields['ID'], true);
                                while($ar_group = $db_old_groups->Fetch())
                                    $arElementSection[] = $ar_group["ID"];
                            }
                            $arElementSection = array_unique(array_merge($condition["SECTIONS"], $arElementSection));
                            ?>
                            <“овар>

                                <»д><?= str_replace(array('.', '-'), '', $product['articlenumber']) ?></»д>
                                <јртикул><?= $product['articlenumber'] ?></јртикул>
                                <ѕометка”далени€>false</ѕометка”далени€>
                                <Ќаименование><?= $product['partname'] ?></Ќаименование>

                                <√руппы>
                                    <?
                                    foreach ($arElementSection as $section): ?>
                                        <»д><?= $section; ?></»д>
                                    <?endforeach; ?>
                                </√руппы>

                                <«начени€—войств>

                                    <«начени€—войства>
                                        <»д>CML2_ACTIVE</»д>
                                        <«начение>true</«начение>
                                    </«начени€—войства>

                                    <«начени€—войства>
                                        <»д>CML2_CODE</»д>
                                        <«начение><?= $product['articlenumber'] ?></«начение>
                                    </«начени€—войства>

                                    <«начени€—войства>
                                        <»д>CML2_SORT</»д>
                                        <«начение><?= $product['media_pixel_id'] ?></«начение>
                                    </«начени€—войства>

                                    <«начени€—войства>
                                        <»д>ARTICLE</»д>
                                        <«начение><?= $product['articlenumber'] ?></«начение>
                                        <«начение—войства>
                                            <«начение><?= $product['articlenumber'] ?></«начение>
                                            <ќписание></ќписание>
                                        </«начение—войства>
                                    </«начени€—войства>

                                    <«начени€—войства>
                                        <»д>COUNT_COMPLECT</»д>
                                        <«начение><?= $product['quantity'] ?></«начение>
                                        <«начение—войства>
                                            <«начение><?= $product['quantity'] ?></«начение>
                                            <ќписание></ќписание>
                                        </«начение—войства>
                                    </«начени€—войства>

                                    <«начени€—войства>
                                        <»д>CML2_PREVIEW_TEXT</»д>
                                        <«начение><?= $product['footer_text'] ?></«начение>
                                        <“ип>text</“ип>
                                    </«начени€—войства>

                                </«начени€—войств>

                                <÷ены>
                                    <÷ена>
                                        <»д“ипа÷ены>price</»д“ипа÷ены>
                                        <÷ена«а≈диницу>0</÷ена«а≈диницу>
                                        <¬алюта>RUB</¬алюта>
                                        <≈диница>796</≈диница>
                                        < оличествоќт></ оличествоќт>
                                        < оличествоƒо></ оличествоƒо>
                                    </÷ена>
                                </÷ены>

                            </“овар>
                        <?endforeach; ?>

                    </“овары>

                    <ќписание> аталог запчастей</ќписание>

                </ аталог>

            </ оммерческа€»нформаци€>
            <?
            $data_xml = ob_get_contents();
            ob_end_clean();

            if ($data_xml){
                $file_path = $_SERVER['DOCUMENT_ROOT'].'/upload/import/'.implode('_',$condition["SECTIONS"]).'.xml';
                $save = file_put_contents($file_path, $data_xml);
                if($save){
                    $message = "»мпорт сформирован. ѕодробнее: <a target='_blank' href='/bitrix/admin/fileman_file_view.php?path=/upload/import/".implode('_',$condition[SECTIONS]).".xml&site=".SITE_ID."&lang=ru'>‘айл: ".implode('_',$condition[SECTIONS]).".xml</a>";
                    CAdminMessage::ShowMessage(array(
                        "MESSAGE" => $message,
                        "HTML" => true,
                        "TYPE" => "OK"
                    ));
                }
            }
        }
	}

    $tabControl->Begin( array(
        "FORM_ACTION" => $APPLICATION->GetCurUri()
    ));

    $tabControl->BeginNextFormTab();

    $tabControl->BeginCustomField( "TYPE_OF_INFOBLOCK", "“ип инфоблока", false );
    echo bitrix_sessid_post();
	?>
        <tr id="tr_TYPE_OF_INFOBLOCK">
            <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
            <td width="60%">
                <?
                echo SelectBoxFromArray( 'TYPE_OF_INFOBLOCK', $arIBlockTypeSel, $condition['TYPE_OF_INFOBLOCK'], '', 'style="min-width: 350px; margin-right: 7px;"', false, '' );
                echo '<input type="submit" name="refresh" value="OK" />';
                ?>
            </td>
        </tr>

	<?
    $tabControl->EndCustomField( "TYPE_OF_INFOBLOCK" );

    $tabControl->BeginCustomField( "INFOBLOCK", "»нфоблок", false );
    ?>
        <tr id="tr_INFOBLOCK">
            <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
            <td width="60%">
                <?
                echo SelectBoxFromArray( 'INFOBLOCK', $arIBlockSel, $condition['INFOBLOCK'], '', 'style="min-width: 350px; margin-right: 7px;"', false, '' );
                echo '<input type="submit" name="refresh" value="OK" />';
                ?>
            </td>
        </tr>

    <?
    $tabControl->EndCustomField( "INFOBLOCK" );

    $tabControl->BeginCustomField( "SECTIONS", "–азделы", false );
    ?>
        <tr id="SECTIONS">
            <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
            <td width="60%">
                <?echo SelectBoxMFromArray('SECTIONS'.'[]', $sectionLinc, is_array($condition['SECTIONS'])?$condition['SECTIONS']:unserialize($condition['SECTIONS']),'',false,'','style="min-width: 350px;"');?>
            </td>
        </tr>
    <?
    $tabControl->EndCustomField( "SECTIONS" );


    $tabControl->BeginCustomField("URL_DATA_FILE", "‘айл дл€ выгрузки",false);
    ?>
        <tr>
            <td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
            <td width="60%">
                <input type="text" id="URL_DATA_FILE" name="URL_DATA_FILE" size="30" value="<?=$condition["URL_DATA_FILE"]?>">
                <input type="button" value="ќткрыть..." OnClick="BtnClick()">
                <?
                CAdminFileDialog::ShowScript
                (
                    Array(
                        "event" => "BtnClick",
                        "arResultDest" => array("FORM_NAME" => "currencyTabControl_form", "FORM_ELEMENT_NAME" => "URL_DATA_FILE"),
                        "arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
                        "select" => 'F',// F - file only, D - folder only
                        "operation" => 'S',// O - open, S - save
                        "showUploadTab" => true,
                        "showAddToMenuTab" => false,
                        "fileFilter" => 'xml',
                        "allowAllFiles" => true,
                        "SaveConfig" => true,
                    )
                );
                ?>
            </td>
        </tr>
    <?
    $tabControl->EndCustomField("URL_DATA_FILE");


    $arButtonsParams = array(
        "disabled" => $readOnly,
        "back_url" => $backUrl,
    );

    $tabControl->Buttons( $arButtonsParams );
    $tabControl->Show();


function build_tree($cats,$parent_id){

    if(is_array($cats) and  isset($cats[$parent_id])){
        $tree = '<√руппы>';
        foreach($cats[$parent_id] as $cat){

            $tree .= "<√руппа>";
            $tree .= "<»д>".$cat['ID']."</»д>";
            $tree .= "<Ѕитрикс од>".$cat['CODE']."</Ѕитрикс од>";
            $tree .= "<ѕометка”далени€>false</ѕометка”далени€>";
            $tree .= "<Ѕитриксјктивность>true</Ѕитриксјктивность>";
            $tree .= "<Ќаименование>".$cat['NAME']."</Ќаименование>";
            $tree .=  build_tree($cats,$cat['ID']);
            $tree .= '</√руппа>';
        }
        $tree .= '</√руппы>';
    }
    else return null;

    return $tree;
}


