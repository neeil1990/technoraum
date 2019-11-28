<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use \Bitrix\Main\SystemException;
use \Rover\Fadmin\Layout\Admin\Form;
use \Rover\AmoCRM\Config\Options;

Loc::LoadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");

try{
    if (Loader::includeSharewareModule($mid) == Loader::MODULE_DEMO_EXPIRED)
        throw new SystemException(Loc::getMessage('rover-acrm__expired'));

    if (!Loader::includeModule($mid))
        throw new SystemException(Loc::getMessage('rover-acrm__module-not-found', array('#mid#' => $mid)));

    if (!Loader::includeModule('rover.fadmin'))
        throw new SystemException(Loc::getMessage('rover-acrm__module-not-found', array('#mid#' => 'rover.fadmin')));

    $options = Options::load();
    $params  = array();

    if ($options->getDependenceStatus()){
        $params['top_buttons'] = array(
            array(
                'TEXT' => Loc::getMessage('rover-acrm__button-riles'),
                'LINK' => '/bitrix/admin/rover-acrm__preset-list.php?lang=' . LANGUAGE_ID,
                'TITLE' => Loc::getMessage('rover-acrm__button-riles_title')
            )
        );
    }

    $form = new Form($options, $params);
    $form->show();

} catch (\Exception $e) {
    ShowError($e->getMessage());
}