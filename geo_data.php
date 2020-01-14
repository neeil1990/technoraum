<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Куки и сессия");
?><?
CModule::IncludeModule("altasib.geobase");

echo "<pre>";

$ip = CAltasibGeoBaseIP::getUserHostIP();
echo "\nset_cookie=".COption::GetOptionString("altasib.geobase", "set_cookie", "")."\n";
echo "\n ip=". $ip;
$last_ip = $APPLICATION->get_cookie("ALTASIB_LAST_IP");
echo "\nlast_ip=". $last_ip."\n";

echo "SESSION ALTASIB_GEOBASE\n";
print_r($_SESSION["ALTASIB_GEOBASE"]);

echo "SESSION ALTASIB_GEOBASE_CODE\n";
print_r($_SESSION["ALTASIB_GEOBASE_CODE"]);

echo "cookie ALTASIB_GEOBASE\n";
$arDataC = CAltasibGeoBase::deCodeJSON($APPLICATION->get_cookie("ALTASIB_GEOBASE"));
print_r($arDataC);

echo "cookie ALTASIB_GEOBASE_CODE\n";
$arDataC2 = CAltasibGeoBase::deCodeJSON($APPLICATION->get_cookie("ALTASIB_GEOBASE_CODE"));
print_r($arDataC2);


echo "</pre>";
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>