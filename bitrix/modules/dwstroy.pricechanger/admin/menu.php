<?
IncludeModuleLangFile(__FILE__);
$iModuleID = "dwstroy.pricechanger";
if ($APPLICATION->GetGroupRight($iModuleID) != "D") {
    $aMenu = array(
        "parent_menu" => "global_menu_marketing",
        "section" => 'dwstroy.pricechanger',
        "sort" => 2000,
        "text" => GetMessage("MENU_PRICECHANGER_TEXT"),
        "title" => GetMessage("MENU_PRICECHANGER_TITLE"),
        "url" => "dwstroy.pricechanger_list.php?lang=" . LANGUAGE_ID,
        "icon" => "pricechanger_menu_icon",
        "page_icon" => "pricechanger_page_icon",
        "items_id" => "menu_dwstroy.pricechanger",
        "items" => array(
            array(
                "text" => GetMessage("MENU_PRICECHANGER_ADMIN_TEXT"),
                "url" => "dwstroy.pricechanger_list.php?lang=" . LANGUAGE_ID,
                "more_url" => array(
                    "dwstroy.pricechanger_list.php",
                    "dwstroy.pricechanger_edit.php"
                ),
                "title" => GetMessage("MENU_PRICECHANGER_ADMIN_TITLE")
            )
        )
    );
    return $aMenu;
}

return false;
?>