<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define("STOP_STATISTICS", true);
global $APPLICATION;
$APPLICATION->ShowAjaxHead();
CModule::IncludeModule( 'catalog' );

$ar_res = CCatalogProduct::GetByID($_REQUEST['id']);
?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-1">��������� �� ��������</a></li>
        <li><a href="#tabs-2">��������� �� ������ ������ ����</a></li>
        <li><a href="#tabs-3">�������� �� ����� ����</a></li>
        <li><a href="#tabs-4">�������� ������ ������������ ���������</a></li>
    </ul>
    <div id="tabs-1">
        <?$APPLICATION->IncludeComponent(
            "nbrains:catalog.store.list",
            ".store.list",
            Array(
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "N",
                "MAP_TYPE" => "0",
                "PATH_TO_ELEMENT" => "store/#store_id#",
                "PHONE" => "Y",
                "SCHEDULE" => "Y",
                "SET_TITLE" => "N",
                "TITLE" => "",
                "PRODUCT_ID" => $ElementID
            ),
                false
        );?>
    </div>
    <div id="tabs-2">
        <?$APPLICATION->IncludeComponent("nbrains:ipol.sdekPickup", ".sdekPickup", Array(
            "CITIES" => "",	// ������������ ������ (���� �� ������� �� ������ - ������������ ���)
            "CNT_BASKET" => "N",	// ����������� �������� ��� �������
            "CNT_DELIV" => "Y",	// ����������� �������� ��� �����������
            "COUNTRIES" => "",	// ������������ ������
            "FORBIDDEN" => array(
                0 => "courier",
                1 => "inpost",
            ),
            "NOMAPS" => "Y",	// �� ���������� ������-����� (���� �� ���������� ���-�� ��� �� ��������)
            "PAYER" => "1",	// ��� �����������, �� ���� �������� ������� ��������
            "PAYSYSTEM" => "",	// ��� ��������� �������, � ������� ����� �������� ��������
            "PRODUCT_ID" => $ElementID,
            "WIDTH" => $ar_res["WIDTH"],
            "HEIGHT" => $ar_res["HEIGHT"],
            "LENGTH" => $ar_res["LENGTH"],
            "WEIGHT" => $ar_res["WEIGHT"]
        ),
            false
        );?>
        <p class="small-message" style="text-align: center">����� � ��������� �������� ���������� �� ������ ������, ��������������� ������������� ����������.</p>
    </div>
    <div id="tabs-3">
        <? if($ar_res["WEIGHT"] &&
            $ar_res["WIDTH"] &&
            $ar_res["HEIGHT"] &&
            $ar_res["LENGTH"]){
            $APPLICATION->IncludeComponent(
                "nbrains:sdek.ajax.delivery",
                "",
                Array(
                    "WIDTH" => $ar_res["WIDTH"],
                    "HEIGHT" => $ar_res["HEIGHT"],
                    "LENGTH" => $ar_res["LENGTH"],
                    "WEIGHT" => $ar_res["WEIGHT"],
                    "PRODUCT_ID" => $ElementID
                )
            );
        }else{
            print "������ �� ��������! ���������� ������� �������� ������.";
        }
        ?>
        <p class="small-message" style="text-align: center">����� � ��������� �������� ���������� �� ������ ������, ��������������� ������������� ����������.</p>
    </div>
    <div id="tabs-4">
        <div class="custom-form">
            <h3>�������� ������ ������������ ���������</h3>
            <form method="post" class="mform">
                <input type="hidden" name="form_id" value="9" />
                <div class="c-input">
                    <label>������� ������������ �������� (��)</label>
                    <input type="text" name="name_tk" value="" placeholder="�������� ��" required>
                </div>
                <div class="c-input">
                    <label>��� �������</label>
                    <input type="text" name="phone" autocomplete="tel" value="" placeholder="�������" required>
                </div>
                <div class="c-input">
                    <label>������� ������ ��������</label>
                    <select name="delivery" required>
                        <option value="�� ��������� ��" data-text="����� ���������" selected>�� ��������� ��</option>
                        <option value="�� �����" data-text="����� ��������">�� �����</option>
                    </select>
                </div>
                <div class="c-input">
                    <label>�����</label>
                    <textarea name="street" placeholder="����� ���������" required></textarea>
                </div>
                <div class="c-input">
                    <input type="submit" name="submit1" value="���������">
                </div>
            </form>
        </div>
    </div>
</div>

