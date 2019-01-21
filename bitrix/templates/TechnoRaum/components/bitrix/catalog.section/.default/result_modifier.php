<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

foreach($arResult["ITEMS"] as &$item){
    $price = round($item[PRICES][price][DISCOUNT_VALUE]);
    $id_order = (time() + $item["ID"]);

    $item['DIRECT_CREDIT'] = array(
        'id' => "$item[ID]",
        'price' => "$price",
        'count' => '1',
        'type' => $arResult["NAME"],
        'name' => $item["NAME"],
        'id_order' => "$id_order",
    );


    foreach($item["PROPERTIES"]["GIFT"]["VALUE"] as $gifts)
    {
        $res = CIBlockElement::GetByID($gifts);
        if($ar_res = $res->GetNext())
        {
            $price = CPrice::GetBasePrice($gifts);

            $item["PROPERTIES"]["GIFT"]["ITEM"][$ar_res['ID']]["ID"] = $ar_res["ID"];
            $item["PROPERTIES"]["GIFT"]["ITEM"][$ar_res['ID']]["NAME"] = $ar_res["NAME"];
            $item["PROPERTIES"]["GIFT"]["ITEM"][$ar_res['ID']]["PICTURE"] = CFile::ResizeImageGet($ar_res["PREVIEW_PICTURE"], array('width'=>50, 'height'=>50), BX_RESIZE_IMAGE_PROPORTIONAL, true);
            $item["PROPERTIES"]["GIFT"]["ITEM"][$ar_res['ID']]["PRICE"] = $price["PRICE"];
            $item["PROPERTIES"]["GIFT"]["ITEM"][$ar_res['ID']]["URL"] = $ar_res["DETAIL_PAGE_URL"];
            $item["GIFT_SUM"] += $price["PRICE"];
        }
    }
}