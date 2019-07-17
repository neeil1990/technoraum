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
		array("DIV" => "edit0", "TAB" => "��������� �������", "ICON" => "currency_settings", "TITLE" => "��������� �������"),
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
            <���������������������� �����������="2.021" ����������������="2019-07-07T10:31:43">
                <�������������>

                    <��>spares</��>
                    <������������>������� ���������</������������>

                    <?= build_tree($arSection, 0); ?>

                    <��������>

                        <��������>
                            <��>CML2_ACTIVE</��>
                            <������������>�����������������</������������>
                            <�������������>false</�������������>
                        </��������>
                        <��������>
                            <��>CML2_CODE</��>
                            <������������>���������� ���</������������>
                            <�������������>false</�������������>
                        </��������>
                        <��������>
                            <��>CML2_SORT</��>
                            <������������>����������</������������>
                            <�������������>false</�������������>
                        </��������>

                        <��������>
                            <��>ARTICLE</��>
                            <������������>�������</������������>
                            <�������������>false</�������������>
                            <�����������������>500</�����������������>
                            <����������>ARTICLE</����������>
                            <������������������>S</������������������>
                            <������������>1</������������>
                            <��������������>30</��������������>
                            <����������������>L</����������������>
                            <�����������������></�����������������>
                            <����������������������>5</����������������������>
                            <������������������������></������������������������>
                            <������������������������>false</������������������������>
                            <��������������������>false</��������������������>
                            <���������������������>false</���������������������>
                            <���������������������></���������������������>
                            <�������������������>false</�������������������>
                            <�������������>1</�������������>
                        </��������>
                        <��������>
                            <��>COUNT_COMPLECT</��>
                            <������������>���������� � ���������</������������>
                            <�������������>false</�������������>
                            <�����������������>500</�����������������>
                            <����������>COUNT_COMPLECT</����������>
                            <������������������>S</������������������>
                            <������������>1</������������>
                            <��������������>30</��������������>
                            <����������������>L</����������������>
                            <�����������������></�����������������>
                            <����������������������>5</����������������������>
                            <������������������������></������������������������>
                            <������������������������>false</������������������������>
                            <��������������������>false</��������������������>
                            <���������������������>false</���������������������>
                            <���������������������></���������������������>
                            <�������������������>false</�������������������>
                            <�������������>1</�������������>
                        </��������>

                    </��������>

                    <�������>
                        <�������>
                            <��>price</��>
                            <������������>price</������������>
                        </�������>
                    </�������>

                </�������������>

                <������� �����������������������="true">

                    <��>spares</��>
                    <����������������>spares</����������������>
                    <������������>������� ���������</������������>

                    <������>

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
                            <�����>

                                <��><?= str_replace(array('.', '-'), '', $product['articlenumber']) ?></��>
                                <�������><?= $product['articlenumber'] ?></�������>
                                <���������������>false</���������������>
                                <������������><?= $product['partname'] ?></������������>

                                <������>
                                    <?
                                    foreach ($arElementSection as $section): ?>
                                        <��><?= $section; ?></��>
                                    <?endforeach; ?>
                                </������>

                                <���������������>

                                    <����������������>
                                        <��>CML2_ACTIVE</��>
                                        <��������>true</��������>
                                    </����������������>

                                    <����������������>
                                        <��>CML2_CODE</��>
                                        <��������><?= $product['articlenumber'] ?></��������>
                                    </����������������>

                                    <����������������>
                                        <��>CML2_SORT</��>
                                        <��������><?= $product['media_pixel_id'] ?></��������>
                                    </����������������>

                                    <����������������>
                                        <��>ARTICLE</��>
                                        <��������><?= $product['articlenumber'] ?></��������>
                                        <����������������>
                                            <��������><?= $product['articlenumber'] ?></��������>
                                            <��������></��������>
                                        </����������������>
                                    </����������������>

                                    <����������������>
                                        <��>COUNT_COMPLECT</��>
                                        <��������><?= $product['quantity'] ?></��������>
                                        <����������������>
                                            <��������><?= $product['quantity'] ?></��������>
                                            <��������></��������>
                                        </����������������>
                                    </����������������>

                                    <����������������>
                                        <��>CML2_PREVIEW_TEXT</��>
                                        <��������><?= $product['footer_text'] ?></��������>
                                        <���>text</���>
                                    </����������������>

                                </���������������>

                                <����>
                                    <����>
                                        <����������>price</����������>
                                        <�������������>0</�������������>
                                        <������>RUB</������>
                                        <�������>796</�������>
                                        <������������></������������>
                                        <������������></������������>
                                    </����>
                                </����>

                            </�����>
                        <?endforeach; ?>

                    </������>

                    <��������>������� ���������</��������>

                </�������>

            </����������������������>
            <?
            $data_xml = ob_get_contents();
            ob_end_clean();

            if ($data_xml){
                $file_path = $_SERVER['DOCUMENT_ROOT'].'/upload/import/'.implode('_',$condition["SECTIONS"]).'.xml';
                $save = file_put_contents($file_path, $data_xml);
                if($save){
                    $message = "������ �����������. ���������: <a target='_blank' href='/bitrix/admin/fileman_file_view.php?path=/upload/import/".implode('_',$condition[SECTIONS]).".xml&site=".SITE_ID."&lang=ru'>����: ".implode('_',$condition[SECTIONS]).".xml</a>";
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

    $tabControl->BeginCustomField( "TYPE_OF_INFOBLOCK", "��� ���������", false );
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

    $tabControl->BeginCustomField( "INFOBLOCK", "��������", false );
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

    $tabControl->BeginCustomField( "SECTIONS", "�������", false );
    ?>
        <tr id="SECTIONS">
            <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
            <td width="60%">
                <?echo SelectBoxMFromArray('SECTIONS'.'[]', $sectionLinc, is_array($condition['SECTIONS'])?$condition['SECTIONS']:unserialize($condition['SECTIONS']),'',false,'','style="min-width: 350px;"');?>
            </td>
        </tr>
    <?
    $tabControl->EndCustomField( "SECTIONS" );


    $tabControl->BeginCustomField("URL_DATA_FILE", "���� ��� ��������",false);
    ?>
        <tr>
            <td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
            <td width="60%">
                <input type="text" id="URL_DATA_FILE" name="URL_DATA_FILE" size="30" value="<?=$condition["URL_DATA_FILE"]?>">
                <input type="button" value="�������..." OnClick="BtnClick()">
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
        $tree = '<������>';
        foreach($cats[$parent_id] as $cat){

            $tree .= "<������>";
            $tree .= "<��>".$cat['ID']."</��>";
            $tree .= "<����������>".$cat['CODE']."</����������>";
            $tree .= "<���������������>false</���������������>";
            $tree .= "<�����������������>true</�����������������>";
            $tree .= "<������������>".$cat['NAME']."</������������>";
            $tree .=  build_tree($cats,$cat['ID']);
            $tree .= '</������>';
        }
        $tree .= '</������>';
    }
    else return null;

    return $tree;
}


