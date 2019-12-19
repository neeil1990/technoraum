<?
#################################################
#   Company developer: ALTASIB                  #
#   Developer: Serge                            #
#   Site: http://www.altasib.ru                 #
#   E-mail: dev@altasib.ru                      #
#   Copyright (c) 2006-2011 ALTASIB             #
#################################################
?>
<?
global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen("/index.php"));
IncludeModuleLangFile(__FILE__);

Class altasib_snow extends CModule
{
        var $MODULE_ID = "altasib.snow";
        var $MODULE_VERSION;
        var $MODULE_VERSION_DATE;
        var $MODULE_NAME;
        var $MODULE_DESCRIPTION;
        var $MODULE_CSS;

        function altasib_snow()
        {
                $arModuleVersion = array();

                $path = str_replace("\\", "/", __FILE__);
                $path = substr($path, 0, strlen($path) - strlen("/index.php"));
                include($path."/version.php");

                if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
                {
                        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
                        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
                }
                else
                {
                        $this->MODULE_VERSION = "1.0";
                        $this->MODULE_VERSION_DATE = "2011-12-19 10:00:00";
                }

                $this->MODULE_NAME = GetMessage("ALTASIB_SNOW_MODULE_NAME");
                $this->MODULE_DESCRIPTION = GetMessage("ALTASIB_SNOW_MODULE_DESCRIPTION");

                $this->PARTNER_NAME = "ALTASIB";
                $this->PARTNER_URI = "http://www.altasib.ru/";
        }
        function DoInstall()
        {
                global $DB, $APPLICATION, $step;
                $step = IntVal($step);
                $this->InstallFiles();
                $this->InstallDB();
                $this->InstallEvents();

                $GLOBALS["errors"] = $this->errors;
                $APPLICATION->IncludeAdminFile(GetMessage("ALTASIB_SNOW_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/altasib.snow/install/step1.php");
        }
        function DoUninstall()
        {
                global $DB, $APPLICATION, $step;
                $step = IntVal($step);
                $this->UnInstallDB();
                $this->UnInstallEvents();
                $this->UnInstallFiles();
                $APPLICATION->IncludeAdminFile(GetMessage("ALTASIB_SNOW_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/altasib.snow/install/unstep1.php");
        }
        function InstallDB()
        {
                global $DB, $DBType, $APPLICATION;
                $this->errors = false;

                RegisterModule("altasib.snow");
                RegisterModuleDependences("main","OnBeforeEndBufferContent","altasib.snow","SNOW_alx","SNOWOnBeforeEndBufferContent", "100");
        }
        function UnInstallDB($arParams = array())
        {
                global $DB, $DBType, $APPLICATION;
                $this->errors = false;

                UnRegisterModuleDependences("main", "OnBeforeEndBufferContent", "altasib.snow", "SNOW_alx", "SNOWOnBeforeEndBufferContent");
                COption::RemoveOption("altasib_snow");
                UnRegisterModule("altasib.snow");

                return true;

        }
        Function InstallEvents()
        {

        }

        Function UnInstallEvents()
        {
        }

        function InstallFiles()
        {
                CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/altasib.snow/install/js",$_SERVER["DOCUMENT_ROOT"]."/bitrix/js",true,true);
                return true;
        }

        function UnInstallFiles()
        {
                DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/altasib/snow");
                return true;
        }
}
?>
