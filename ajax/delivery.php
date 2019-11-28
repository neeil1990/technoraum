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
        <li><a href="#tabs-1">Самовывоз из магазина</a></li>
        <li><a href="#tabs-2">Самовывоз из пункта выдачи СДЭК</a></li>
        <li><a href="#tabs-3">Доставка до двери СДЭК</a></li>
        <li><a href="#tabs-4">Доставка другой транспортной компанией</a></li>
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
            "CITIES" => "",	// Подключаемые города (если не выбрано ни одного - подключаются все)
            "CNT_BASKET" => "N",	// Расчитывать доставку для корзины
            "CNT_DELIV" => "Y",	// Расчитывать доставку при подключении
            "COUNTRIES" => "",	// Подключенные страны
            "FORBIDDEN" => array(
                0 => "courier",
                1 => "inpost",
            ),
            "NOMAPS" => "Y",	// Не подключать Яндекс-карты (если их подключает что-то еще на странице)
            "PAYER" => "1",	// Тип плательщика, от лица которого считать доставку
            "PAYSYSTEM" => "",	// Тип платежной системы, с которой будет считатся доставка
            "PRODUCT_ID" => $ElementID,
            "WIDTH" => $ar_res["WIDTH"],
            "HEIGHT" => $ar_res["HEIGHT"],
            "LENGTH" => $ar_res["LENGTH"],
            "WEIGHT" => $ar_res["WEIGHT"]
        ),
            false
        );?>
        <p class="small-message" style="text-align: center">Сроки и стоимость доставки рассчитаны на основе данных, предоставленных транспортными компаниями.</p>
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
            print "Расчет не выполнен! Неуказанны размеры текущего товара.";
        }
        ?>
        <p class="small-message" style="text-align: center">Сроки и стоимость доставки рассчитаны на основе данных, предоставленных транспортными компаниями.</p>
    </div>
    <div id="tabs-4">
        <div class="custom-form">
            <h3>Доставка другой транспортной компанией</h3>
            <form method="post" class="mform">
                <input type="hidden" name="form_id" value="9" />
                <div class="c-input">
                    <label>Укажите транспортную компанию (ТК)</label>
                    <input type="text" name="name_tk" value="" placeholder="Название ТК" required>
                </div>
                <div class="c-input">
                    <label>Ваш телефон</label>
                    <input type="text" name="phone" autocomplete="tel" value="" placeholder="Телефон" required>
                </div>
                <div class="c-input">
                    <label>Укажите способ доставки</label>
                    <select name="delivery" required>
                        <option value="До терминала ТК" data-text="Адрес терминала" selected>До терминала ТК</option>
                        <option value="До двери" data-text="Адрес доставки">До двери</option>
                    </select>
                </div>
                <div class="c-input">
                    <label>Адрес</label>
                    <textarea name="street" placeholder="Адрес терминала" required></textarea>
                </div>
                <div class="c-input">
                    <input type="submit" name="submit1" value="Отправить">
                </div>
            </form>
        </div>
    </div>
</div>

