<?
IncludeModuleLangFile(__FILE__); // в menu.php точно так же можно использовать языковые файлы

//if($APPLICATION->GetGroupRight("form")>"D") // проверка уровня доступа к модулю веб-форм
//{
  // верхний пункт меню оставим без изменений
  $aMenu = array(
    "parent_menu" => "global_menu_store", // поместим в раздел "Каталог"
    "sort"        => 1000,                    // вес пункта меню
    "url"         => "vsfr_export_setup.php?lang=".LANGUAGE_ID,  // ссылка на пункте меню
    "text"        => GetMessage("VSFR_EXPORT"),       // текст пункта меню
    "title"       => GetMessage("VSFR_EXPORT_MAIN_TITLE"), // текст всплывающей подсказки
    "icon"        => "form_menu_icon", // малая иконка
    "page_icon"   => "form_page_icon", // большая иконка
    "items_id"    => "menu_webforms",  // идентификатор ветви
    "items"       => array(),          // остальные уровни меню сформируем ниже.
  );

  
  return $aMenu;
//}
// если нет доступа, вернем false

?>