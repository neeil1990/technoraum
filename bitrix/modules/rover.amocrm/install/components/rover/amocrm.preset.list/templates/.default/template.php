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

try{

$options    = Options::load();
$messages   = $options->message->get();

$options->message->showAdmin();

if (!$options->getDependenceStatus()|| !$options->isConnected())
    return;

CUtil::InitJSCore(array(/*'ajax' ,*/ 'popup'));

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
        "GRID_ID"   => RoverAmoCrmPresetList::GRID_ID,
        "HEADERS"   => $arResult["HEADERS"],
        'SORT'      => $arResult['SORT']['sort'],
        'SORT_VARS' => $arResult['SORT']['vars'],
        "ROWS"      => $arResult["ROWS"],
        'FOOTER'    => array(
            array(
                'title' => Loc::getMessage('rover-apl__all'),
                'value' => is_object($arResult["NAV"])
                    ? $arResult["NAV"]->getRecordCount()
                    : 0
            )
        ),
        'ACTIONS'   => array(
            "delete"=> true,
            "list"  => array(
                "activate"      => Loc::getMessage('rover-apl__activate'),
                "deactivate"    => Loc::getMessage('rover-apl__deactivate'),
                //    'delete'        => Loc::getMessage('rover-apl__delete'),
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

$types = \Rover\AmoCRM\Entity\Source::$types;

?><script type="text/javascript">
    BX.message({
        <?php foreach ($types as $type): ?>
        rover_acrm__<?=$type?>_title: '<?=GetMessageJS("rover-acrm__" . $type ."_title")?>',
        <?php endforeach; ?>
        rover_acrm__button_close: '<?=GetMessageJS('rover-acrm__button_close')?>',
        rover_acrm__button_add: '<?=GetMessageJS('rover-acrm__button_add')?>',
        rover_acrm__language_id: '<?=LANGUAGE_ID?>'
    });

    var amoCrmPresetList;

    BX.ready(function(){

        var types = <?=CUtil::PhpToJSObject($arResult['SOURCE_TYPES'])?>;

        amoCrmPresetList = new AmoCrmPresetList(types);
    });
</script><?php
} catch (\Exception $e) {
    ShowError($e->getMessage());
}