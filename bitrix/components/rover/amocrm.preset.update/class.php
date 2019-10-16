<?php

/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 02.10.2015
 * Time: 19:12
 *
 * @author Shulaev (pavel.shulaev@gmail.com)
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;
use \Rover\AmoCRM\Entity\Source;
use \Rover\AmoCRM\Config\Options;
use \Rover\Fadmin\Inputs\Tab;

if (!Main\Loader::includeModule('rover.amocrm'))
    throw new Main\SystemException('rover.amocrm module not found');
/**
 * Class RoverAmoCrmImport
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
class RoverAmoCrmPresetUpdate extends CBitrixComponent
{
    const FORM_ID = 'amocrm_preset_update';

    /**
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	/**
	 * @param $params
	 * @return mixed
	 * @author Shulaev (pavel.shulaev@gmail.com)
	 */
	public function onPrepareComponentParams($params)
	{
	    $params['PRESET_ID']    = intval($params['PRESET_ID']);
	    $params['SOURCE_TYPE']  = trim($params['SOURCE_TYPE']);
	    $params['SITE_ID']      = trim($params['SITE_ID']);

	    if (!empty($params['SOURCE_TYPE']) && !isset(Source::$classes[$params['SOURCE_TYPE']]))
            $params['SOURCE_TYPE'] = null;

		return $params;
	}

    /**
     * @throws Main\ArgumentNullException
     * @throws Main\LoaderException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function checkParams()
	{
	    if (!Main\Loader::includeModule('rover.amocrm'))
	        throw new Main\SystemException('rover.amocrm module not found');

        if (!Options::load()->getDependenceStatus() || !Options::load()->isConnected())
            return;

	    if (!empty($this->arParams['PRESET_ID']))
	        return;

        if (empty($this->arParams['SOURCE_TYPE']))
            throw new Main\ArgumentNullException('SOURCE_TYPE');

	    // try to load exists preset
	    try {
            $source = Source::buildFromRequest($this->arParams['SOURCE_TYPE']);
            $this->arParams['PRESET_ID'] = $source->getPresetId();

            if (!empty($this->arParams['PRESET_ID']))
                Options::load()->message->addOk(Loc::getMessage('rover-apu__preset-exists'));
        } catch (\Exception $e) {
	        Options::load()->handleError($e);
        }

        if (!empty($this->arParams['PRESET_ID']))
            return;

	    // try to create preset
        $this->arParams['PRESET_ID'] = Options::load()->preset->add($this->arParams['SOURCE_TYPE']);

        if (empty($this->arParams['PRESET_ID']))
            throw new Main\ArgumentNullException('PRESET_ID');
	}

    /**
     * @return int
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function addPreset()
    {
        $presetId = intval(Options::load()->preset->add($this->arParams['NAME'], $this->arParams['SITE_ID']));
        if (!$presetId)
            throw new Main\ArgumentNullException('presetId');

        return $presetId;
    }

    /**
     * @param Tab $presetTab
     * @return array
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getActionPanel(Tab $presetTab)
    {
        $source     = Source::buildFromTab($presetTab);

        $buttons = array(
            array(
                'TEXT'  => Loc::getMessage('rover-apu__action-back', array('#cnt#' => Options::load()->preset->getCount())),
                'TITLE' => Loc::getMessage('rover-apu__action-back_title'),
                'LINK'  => '/bitrix/admin/rover-acrm__preset-list.php?lang=' . LANGUAGE_ID,
                'ICON'  => 'btn-list'
            ),
            array(
                'TEXT'  => Loc::getMessage('rover-apu__action-elements', array('#cnt#' => $source->model->getResultsCount())),
                'TITLE' => Loc::getMessage('rover-apu__action-elements_title'),
                'LINK'  => '/bitrix/admin/rover-acrm__preset-elements.php?preset_id=' . $this->arParams['PRESET_ID'] . '&lang=' . LANGUAGE_ID,
                'ICON'  => 'btn-list'
            ),
            array('SEPARATOR' => true),
            array(
                'TEXT'  => Loc::getMessage('rover-apu__action-settings'),
                'TITLE' => Loc::getMessage('rover-apu__action-settings_title'),
                'LINK'  => '/bitrix/admin/settings.php?lang=' . LANGUAGE_ID . '&mid=rover.amocrm&mid_menu=1',
                'ICON'  => 'btn-settings'
            )
        );
        return $buttons;
    }

    /**
     * @param Options $options
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getResult(Options $options)
    {
        $this->arResult['OPTIONS']  = $options;

        $presetTab = $options->getTabControl()->getTabByPresetId($this->arParams['PRESET_ID']);

        $this->arResult['ACTION_PANEL'] = $this->getActionPanel($presetTab);

        $this->setTitle($presetTab);
    }

    /**
     * @param Tab $tab
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function setTitle(Tab $tab)
    {
        $source = Source::buildFromTab($tab);

        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage('rover-apu__title', array(
            '#name#' => $tab->getPresetName(),
            '#type#' => $source->getTypeLabel()
        )));
    }

	/**
	 * @author Shulaev (pavel.shulaev@gmail.com)
	 */
	public function executeComponent()
	{
		try {
			$this->setFrameMode(false);
			$this->checkParams();

            $options = Options::load();

            if ($options->getDependenceStatus() && $options->isConnected()) {
                if (!Options::isEnabled())
                    $options->message->addError(Loc::getMessage('rover-apu__disabled'), true);


                $this->getResult($options);
            } else {
                $options->message->addError(Loc::getMessage('rover-apu__no-connection'));
            }

			$this->includeComponentTemplate();
		} catch (Exception $e) {
			ShowError($e->getMessage());
		}
	}
}