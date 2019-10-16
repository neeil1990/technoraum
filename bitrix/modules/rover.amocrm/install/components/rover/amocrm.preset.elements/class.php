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
use \Rover\AmoCRM\Config\Preset;
use \Rover\AmoCRM\Config\Options;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;

/**
 * Class RoverAmoCrmImport
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
class RoverAmoCrmPresetElements extends CBitrixComponent
{
    const GRID_ID           = 'amocrm_preset_elements_';
    const ACTION__EXPORT    = 'export';
    /**
     * @var
     */
    protected $gridOptions;

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
	    $params['ID'] = intval($params['ID']);

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

	    if (empty($this->arParams['ID']))
	        throw new Main\ArgumentNullException('ID');
	}

    /**
     * @return array
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getActionPanel()
    {
        $presets        = Preset::getAll();
        $presetsList    = array();

        foreach ($presets as $presetInternalID => $preset){
            $source = Source::build($preset['type'], $preset['id']);

            $presetsList[] = array(
                'ICONCLASS' => 'view',
                'TEXT'      => $source->getTab()->getPresetName() . ' [' . $source->getTypeLabel() . '] (' . $source->model->getResultsCount() . ')',
                'ONCLICK'   => "jsUtils.Redirect(arguments, '/bitrix/admin/rover-acrm__preset-elements.php?preset_id=" . $presetInternalID  . "&lang=" . LANGUAGE_ID . "')"
            );
        }

        $buttons = array(
            array(
                'TEXT'  => Loc::getMessage('rover-ape__action-back', array('#cnt#' => Options::load()->preset->getCount())),
                'TITLE' => Loc::getMessage('rover-ape__action-back_title'),
                'LINK'  => '/bitrix/admin/rover-acrm__preset-list.php?lang=' . LANGUAGE_ID ,
                'ICON'  => 'btn-list'
            ),
            array(
                'TEXT'  => Loc::getMessage('rover-ape__action-settings'),
                'TITLE' => Loc::getMessage('rover-ape__action-settings_title'),
                'LINK'  => '/bitrix/admin/rover-acrm__preset-update.php?preset_id=' . $this->arParams['ID']  . "&lang=" . LANGUAGE_ID,
                'ICON'  => 'btn-settings'
            ),
            array(
                'TEXT'  => Loc::getMessage('rover-ape__action-presets'),
                'TITLE' => Loc::getMessage('rover-ape__action-presets_title'),
                'MENU'  => $presetsList,
                'ICON'  => 'btn-new'
            ),
            array(
                'SEPARATOR' => true
            ),
            array(
                'TEXT'  => Loc::getMessage('rover-ape__action-module-settings'),
                'TITLE' => Loc::getMessage('rover-ape__action-module-settings_title'),
                'LINK'  => '/bitrix/admin/settings.php?lang=' . LANGUAGE_ID . '&mid=rover.amocrm&mid_menu=1',
                'ICON'  => 'btn-settings'
            )
        );

        return $buttons;
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getActionButton()
    {
        return 'action_button_' . $this->getGridId();
    }

    /**
     * @return CGridOptions
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getGridOptions()
    {
        if (is_null($this->gridOptions))
            $this->gridOptions = new \CGridOptions($this->getGridId());

        return $this->gridOptions;
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getGridId()
    {
        return self::GRID_ID . $this->arParams['ID'];
    }

    /**
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getGridSort()
    {
        $sort = $this->getGridOptions()->GetSorting(array(
            "sort" => array("ID" => "ASC"),
            "vars" => array("by" => "by", "order" => "order")
        ));

        return $sort;
    }

    /**
     * @param Source $source
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function requestActions(Source $source)
    {
        $action = $this->request->get($this->getActionButton());
        switch ($action) {
            case self::ACTION__EXPORT:
                $this->requestExport($source);
                break;
        }
    }

    /**
     * @param Source $source
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function requestExport(Source $source)
    {
        $elementsIds = $this->request->get('ID');
//
        if (!is_array($elementsIds))
            $elementsIds = array($elementsIds);

        foreach ($elementsIds as $elementId)
            try{
                \Rover\AmoCRM\Entry::pushNew(
                    $source->getType(),
                    $source->getId(),
                    $source->model->getResultParamsById($elementId),
                    $source->model->getSiteIdById($elementId)
                );

            } catch (\Exception $e){
                $this->arResult['MESSAGES'][] = array(
                    'MESSAGE'   => $e->getMessage(),
                    'TYPE'      => 'ERROR'
                );
            }

        if (empty($this->arResult['MESSAGES'])) {
            $request    = Application::getInstance()->getContext()->getRequest();
            $uriString  = $request->getRequestUri();
            $uri        = new Uri($uriString);

            $uri->deleteParams(array("ID", $this->getActionButton()));

            LocalRedirect($uri->getUri());
        }
    }

    /**
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getResult()
    {
        $source = Source::buildByPresetId($this->arParams['ID']);

        $this->requestActions($source);
        $this->setTitle($source);

        $this->arResult['ROWS']         = $this->getRows($source);
        $this->arResult['HEADERS']      = $this->getHeaders($source);
        $this->arResult['ACTION_PANEL'] = $this->getActionPanel();
        $this->arResult['GRID_ID']      = $this->getGridId();
        $this->arResult['SORT']         = $this->getGridSort();
    }

    /**
     * @param Source $source
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getRows(Source $source)
    {
        global $APPLICATION;

        $curPage    = $APPLICATION->GetCurPage(true);
        $elements   = $this->getElements($source);
        $rows       = array();

        foreach ($elements as $elementId => $element){

            $element = array('ID' => $elementId) + $element;
            $rows[] = array(
                'id'        => $elementId,
                'data'      => $element,
                'actions'   => array(
                    array(
                        'TEXT'      => Loc::getMessage('rover-ape__action-export'),
                        'ONCLICK'   => "jsUtils.Redirect(arguments, '" . $curPage
                            . "?preset_id=" . $this->arParams['ID']
                            . "&ID=" . $elementId
                            . "&lang=" . LANGUAGE_ID
                            . "&" . $this->getActionButton() ."=" . self::ACTION__EXPORT . "')",
                        "ICONCLASS" => "copy",
                        //'DEFAULT'   => true
                    ),
                ),
                'editable'  => true
            );
        }

        return $rows;
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getGridPageSize()
    {
        $navParams = $this->getGridOptions()->GetNavParams(array("nPageSize"=>10));

        return $navParams['nPageSize'];
    }

    /**
     * @param Source $source
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getElements(Source $source)
    {
        $nav = new \Bitrix\Main\UI\PageNavigation("nav-preset-elements");
        $nav->allowAllRecords(true)
            ->setPageSize($this->getGridPageSize())
            ->initFromUri();

        $sort                   = $this->getGridSort();
        $elements               = $source->model->getResults(array('order' => $sort['sort']), $nav);

        $this->arResult['NAV']  = $nav;

        return $elements;
    }

    /**
     * @param Source $source
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getHeaders(Source $source)
    {
        $labels = $source->model->getLabels(true);
        $result = array(
            array(
                'id'        => 'ID',
                'name'      => 'ID',
                'default'   => true,
                'sort'      => 'ID',
            )
        );

        foreach ($labels as $code => $name)
            $result[] = array(
                'id'        => $code,
                'name'      => $name,
                'default'   => true,
                //'sort'      => $code
            );

        /*$result[] = [
            'id'        => 'DATE_CREATE',
            'name'      => Loc::getMessage('rover-ape__datetime_created'),
            'default'   => true,
            'type'      => 'date'
        ];*/

        return $result;
    }

    /**
     * @param Source $source
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function setTitle(Source $source)
    {
        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage('rover-ape__title', array(
            '#name#' => $source->getTab()->getPresetName(),
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
                    $options->message->addError(Loc::getMessage('rover-ape__disabled'), true);

                $this->getResult();
            } else {
                $options->message->addError(Loc::getMessage('rover-ape__no-connection'));
            }

			$this->includeComponentTemplate();
		} catch (Exception $e) {
			ShowError($e->getMessage());
		}
	}
}