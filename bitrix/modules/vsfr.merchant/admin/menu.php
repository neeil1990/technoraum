<?
IncludeModuleLangFile(__FILE__); // � menu.php ����� ��� �� ����� ������������ �������� �����

//if($APPLICATION->GetGroupRight("form")>"D") // �������� ������ ������� � ������ ���-����
//{
  // ������� ����� ���� ������� ��� ���������
  $aMenu = array(
    "parent_menu" => "global_menu_store", // �������� � ������ "�������"
    "sort"        => 1000,                    // ��� ������ ����
    "url"         => "vsfr_export_setup.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
    "text"        => GetMessage("VSFR_EXPORT"),       // ����� ������ ����
    "title"       => GetMessage("VSFR_EXPORT_MAIN_TITLE"), // ����� ����������� ���������
    "icon"        => "form_menu_icon", // ����� ������
    "page_icon"   => "form_page_icon", // ������� ������
    "items_id"    => "menu_webforms",  // ������������� �����
    "items"       => array(),          // ��������� ������ ���� ���������� ����.
  );

  
  return $aMenu;
//}
// ���� ��� �������, ������ false

?>