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
use Rover\Fadmin\Layout\Preset\Form;
use \Rover\AmoCRM\Config\Options;

Loc::loadMessages(__FILE__);

$this->setFrameMode(false);

$options = Options::load();

$options->message->showAdmin();
$options->message->clear();

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

$form = new Form(Options::load(), array(
    'form_id'   => RoverAmoCrmPresetUpdate::FORM_ID,
    'preset_id' => $arParams['PRESET_ID'],
    'back_url'  => '/bitrix/admin/rover-acrm__preset-list.php?lang=' . LANGUAGE_ID,
    'this_url'  => '/bitrix/admin/rover-acrm__preset-update.php?preset_id=' . $arParams['PRESET_ID'] . '&lang=' . LANGUAGE_ID,
));

$form->show();