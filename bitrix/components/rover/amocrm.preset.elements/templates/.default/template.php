<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use \Bitrix\Main\Localization\Loc;
use \Rover\AmoCRM\Config\Options;

Loc::loadMessages(__FILE__);

$this->setFrameMode(false);

$options    = Options::load();
$messages   = $options->message->get();

$options->message->showAdmin();

if (!$options->getDependenceStatus() || !$options->isConnected())
    return;

$APPLICATION->IncludeComponent(
    "bitrix:main.interface.toolbar",
    "",
    array(
        "BUTTONS"=> $arResult['ACTION_PANEL']
    ),
    false,
    array('HIDE_ICONS' => 'Y')
);


$APPLICATION->IncludeComponent(
    "bitrix:main.interface.grid",
    "",
    array(
        "GRID_ID"   => $arResult['GRID_ID'],
        "HEADERS"   => $arResult["HEADERS"],
        'SORT'      => $arResult['SORT']['sort'],
        'SORT_VARS' => $arResult['SORT']['vars'],
        "ROWS"      => $arResult["ROWS"],
        'FOOTER'    => array(
            array(
                'title' => Loc::getMessage('rover-ape__all'),
                'value' => is_object($arResult["NAV"])
                    ? $arResult["NAV"]->getRecordCount()
                    : 0
            )
        ),
        'ACTIONS'   => array(
            "delete"=> false,
            "list"  => array(
                RoverAmoCrmPresetElements::ACTION__EXPORT      => Loc::getMessage('rover-ape__import'),
                // "deactivate"    => Loc::getMessage('rover-ape__deactivate'),
                //    'delete'        => Loc::getMessage('rover-ape__delete'),
            ),
        ),

        "NAV_OBJECT"=> $arResult["NAV"],

        "AJAX_MODE"         =>"N",
        "AJAX_OPTION_JUMP"  =>"N",
        "AJAX_OPTION_STYLE" =>"Y",
        'EDITABLE'          => true,
        'ACTION_ALL_ROWS'   => false,
    ),
    $component, array("HIDE_ICONS" => "Y")
);