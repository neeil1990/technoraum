<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 17.09.2017
 * Time: 12:48
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
$aMenu[] = array(
    "parent_menu"   => "global_menu_services",
    "section"       => "services",
    "sort"          => 200,
    "text"          => Loc::getMessage("rover-acrm__menu-main"),
    "title"         => Loc::getMessage("rover-acrm__menu-main_title"),
    "url"           => "rover-acrm__preset-list.php?lang=" . LANGUAGE_ID,
    "icon"          => "workflow_menu_icon",
    "page_icon"     => "workflow_menu_icon",
    "items_id"      => "menu_rover-acrm",
);

return $aMenu;