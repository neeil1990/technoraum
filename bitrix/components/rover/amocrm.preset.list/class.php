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
use \Rover\AmoCRM\Config\Preset;
use \Rover\AmoCRM\Entity\Source;
use \Bitrix\Main\UI\AdminPageNavigation;
use \Rover\AmoCRM\Config\Options;
use \Rover\AmoCRM\Config\Tabs;
use \Rover\Fadmin\Inputs\Tab;
use \Rover\AmoCRM\Model\Rest\Account;
use \Rover\AmoCRM\Config\TabList;
/**
 * Class RoverAmoCrmImport
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
class RoverAmoCrmPresetList extends CBitrixComponent
{
    const GRID_ID = 'amocrm_preset_list';

    /** @var \CGridOptions */
    protected $gridOptions;

    /** @var AdminPageNavigation */
    protected $nav;

    /** @var string */
    protected $curDir;

    /** @var */
    protected $sourceClasses;

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
        return $params;
    }

    /**
     * @throws Main\LoaderException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function checkParams()
    {
        if (!Main\Loader::includeModule('rover.amocrm'))
            throw new Main\SystemException('rover.amocrm module not found');
    }

    /**
     * @return null|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getRequestAction()
    {
        return $this->request->get($this->getActionButton());
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getActionButton()
    {
        return 'action_button_' . self::GRID_ID;
    }

    /**
     * @return AdminPageNavigation
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getNavObject()
    {
        if (is_null($this->nav)){
            $this->nav = new AdminPageNavigation("nav-amocrm-preset-list");
            $this->nav->allowAllRecords(true)
                ->setPageSize($this->getGridPageSize())
                ->initFromUri();
        }

        return $this->nav;
    }

    /**
     * @return CGridOptions
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getGridOptions()
    {
        if (is_null($this->gridOptions))
            $this->gridOptions = new \CGridOptions(self::GRID_ID);

        return $this->gridOptions;
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
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getGridPageSize()
    {
        $navParams = $this->getGridOptions()->GetNavParams(array("nPageSize"=>10));

        return $navParams['nPageSize'];
    }

    /**
     * @return array|bool|mixed|SplFixedArray|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getCurDir()
    {
        if (is_null($this->curDir)) {
            global $APPLICATION;

            $this->curDir = $APPLICATION->GetCurPage(false);

            if (($i = strrpos($this->curDir, '/')) !== false)
                $this->curDir = substr($this->curDir, 0, $i) . '/';
        }

        return $this->curDir;
    }

    /**
     * @return array
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getAllRows()
    {
        global $APPLICATION;

        $curPage    = $APPLICATION->GetCurPage(true);
        $curDir     = $this->getCurDir();
        $presets    = Preset::getAll();
        $result     = array();
        $sites      = \Rover\Params\Main::getSites(array('empty' => null));
        $users      = TabList::getUsers();

        foreach ($presets as $presetId => $preset)
        {
            $source     = Source::build($preset['type'], $preset['id']);
            $presetTab  = $source->getTab();

            $presetSites = $presetTab->getInputValue(Tabs::INPUT__SITES);
            if (empty($presetSites))
                $presetSites = array_keys($sites);

            try{
                $resultsCnt     = $source->model->getResultsCount();
                $presetName     = $presetTab->getPresetName();
                $unsortedValue  = Account::getInstance()->isUnsortedOn()
                    ? ($presetTab->getInputValue(Tabs::INPUT__UNSORTED_CREATE, true) ? 'Y' : 'N')
                    : Loc::getMessage('rover-apl__unavailable');

                $row = array(
                    'id'    => $presetId,
                    'data'  => array(
                        'ID'            => $presetId,
                        'NAME'          => $presetName,
                        'TYPE'          => $source->getTypeLabel(),
                        'SITE'          => implode(', ', $presetSites),
                        'ACTIVE'        => $presetTab->getInputValue(Tabs::INPUT__CONNECTION_ENABLED, true) ? 'Y' : 'N',
                        'MANAGER'       => $presetTab->getInputValue(Tabs::INPUT__MAIN_USER),
                        'UNSORTED'      => $unsortedValue,
                        'LEAD'          => $presetTab->getInputValue(Tabs::INPUT__LEAD_CREATE, true) ? 'Y' : 'N',
                        'CONTACT'       => $presetTab->getInputValue(Tabs::INPUT__CONTACT_CREATE, true) ? 'Y' : 'N',
                        'COMPANY'       => $presetTab->getInputValue(Tabs::INPUT__COMPANY_CREATE, true) ? 'Y' : 'N',
                        'TASK'          => $presetTab->getInputValue(Tabs::INPUT__TASK_CREATE, true) ? 'Y' : 'N',
                        'ELEMENTS_CNT'  => $resultsCnt
                    ),
                    'columns' => array(
                        'NAME'          => "<a title='" . Loc::getMessage('rover-apl__title-settings', array('#preset-name#' => $presetName))
                            . "' href='{$curDir}rover-acrm__preset-update.php?preset_id={$presetId}&lang=" . LANGUAGE_ID . "'>{$presetName}</a>",
                        'MANAGER'       => $users[$presetTab->getInputValue(Tabs::INPUT__MAIN_USER)],
                        'ELEMENTS_CNT'  => "<a title='" . Loc::getMessage('rover-apl__title-results', array('#preset-name#' => $presetName))
                            . "' href='{$curDir}rover-acrm__preset-elements.php?preset_id={$presetId}&lang=" . LANGUAGE_ID . "'>{$resultsCnt}</a>",
                        'TYPE'          => "<a title='{$source->getName()}' href='{$source->getEditUrl()}'>{$source->getName()}</a> ({$source->getTypeLabel()})",
                    ),
                    'actions' => array(
                        array(
                            'TEXT'      => Loc::getMessage('rover-apl__action-update'),
                            'ONCLICK'   => "jsUtils.Redirect(arguments, '" . $curDir ."rover-acrm__preset-update.php?preset_id=" . $presetId . "&lang=" . LANGUAGE_ID . "')",
                            "ICONCLASS" => "edit",
                            'DEFAULT'   => true,
                        ),
                        array(
                            'TEXT'      => Loc::getMessage('rover-apl__action-elements'),
                            'ONCLICK'   => "jsUtils.Redirect(arguments, '" . $curDir ."rover-acrm__preset-elements.php?preset_id=" . $presetId . "&lang=" . LANGUAGE_ID . "')",
                            "ICONCLASS" => "copy"
                        ),
                        array(
                            'SEPARATOR' => true,
                        ),
                        array(
                            'TEXT'      => Loc::getMessage('rover-apl__action-remove'),
                            'ONCLICK'   => 'if(confirm("' . Loc::getMessage('rover-apl__action-confirm') . '")) window.location="' . $curPage . '?' . $this->getActionButton() . '=delete&ID=' . $presetId . '&' . bitrix_sessid_get()  . "&lang=" . LANGUAGE_ID . '";',
                            "ICONCLASS" => "delete"
                        )
                    ),
                    'editable'  => true,
                    'source'    => $source
                );

                if (!Account::getInstance()->isUnsortedOn())
                    $row['columns']['UNSORTED'] = '<span style="color: #999" title="'
                        . Loc::getMessage('rover-apl__title-unsorted-unavailable') .  '">'
                        . Loc::getMessage('rover-apl__unavailable') . '</span>';

                // @todo: mark unaccesable task
                /*if ($unsortedValue == 'Y'){
                    $row['columns']['TASK'] = '<span style="color: #999" title="'
                        . Loc::getMessage('rover-apl__title-task-unavailable') .  '">'
                        . $row['data']['TASK'] . '</span>';
                    $row['data']['TASK'] = '<span>' . $row['data']['TASK'] . '</span>';
                }*/

                foreach ($row['data'] as $name => $value)
                    $row['data']['~' . $name] = $value;

            } catch (\Exception $e) {
                $row = array(
                    'id' => null,
                    'data' =>array(
                        'ID'            => $presetId,
                        'NAME'          => 'Error: ' . $e->getMessage(),
                        'TYPE'          => null,
                        'SITE'          => null,
                        'ACTIVE'        => 'N',
                        'MANAGER'       => '-',
                        'UNSORTED'      => 'N',
                        'LEAD'          => 'N',
                        'CONTACT'       => 'N',
                        'COMPANY'       => 'N',
                        'TASK'          => 'N',
                        'ELEMENTS_CNT'  => 0
                    ),
                    'actions' => array(
                        array(
                            'TEXT'      => Loc::getMessage('rover-apl__action-remove'),
                            'ONCLICK'   => 'if(confirm("' . Loc::getMessage('rover-apl__action-confirm') . '")) window.location="' . $curPage . '?' . $this->getActionButton() . '=delete&ID=' . $presetId . '&' . bitrix_sessid_get() . "&lang=" . LANGUAGE_ID . '";',
                            "ICONCLASS" => "delete"
                        )
                    ),
                    'editable'  => false,
                    'source'    => null
                );

                Options::load()->handleError($e);
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * @return array|mixed
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getRows()
    {
        $allRows    = $this->getAllRows();
        $allRows    = $this->sort($allRows);
        $nav        = $this->getNavObject();
        $nav->setRecordCount(count($allRows));

        return $nav->allRecordsShown()
            ? $allRows
            : array_slice($allRows, $nav->getOffset(), $nav->getLimit(), true);
    }

    /**
     * @param $array
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function sort($array)
    {
        $sort   = $this->getGridSort();
        $sort   = $sort['sort'];
        $by     = key($sort);
        $order  = $sort[$by];

        usort($array, function($a, $b) use ($by, $order)
        {
            if (is_numeric($a['data'][$by]) && is_numeric($b['data'][$by]))
                $result = $a['data'][$by] > $b['data'][$by]
                    ? 1 : ($a['data'][$by] < $b['data'][$by]
                        ? -1 : 0);
            else
                $result = strcasecmp($a['data'][$by], $b['data'][$by]);

            if ($order == 'desc')
                $result = $result * (-1);

            return $result;
        });

        return $array;
    }

    /**
     * @return array
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getHeaders()
    {
        return array(
            array(
                'id'        => 'ID',
                'name'      => 'ID',
                'default'   => true,
                'sort'      => 'ID'
            ),
            array(
                'id'        => 'ACTIVE',
                'name'      => Loc::getMessage('rover-apl__header-ACTIVE'),
                'default'   => true,
                'sort'      => 'ACTIVE',
                'editable'  => true,
                "type"      => "checkbox"
            ),
            array(
                'id'        => 'NAME',
                'name'      => Loc::getMessage('rover-apl__header-NAME'),
                'default'   => true,
                'sort'      => 'NAME',
                'editable'  => true,
            ),
            array(
                'id'        => 'TYPE',
                'name'      => Loc::getMessage('rover-apl__header-TYPE'),
                'default'   => true,
                'sort'      => 'TYPE'
            ),
            array(
                'id'        => 'SITE',
                'name'      => Loc::getMessage('rover-apl__header-SITE'),
                'default'   => true,

                // 'sort'      => 'TYPE'
            ),
            array(
                'id'        => 'MANAGER',
                'name'      => Loc::getMessage('rover-apl__header-MANAGER'),
                'default'   => true,
                'editable'  => [
                    'items'     => TabList::getUsers()
                ],
                'type'      => 'list',

                // 'sort'      => 'TYPE'
            ),

            array(
                'id'        => 'UNSORTED',
                'name'      => Loc::getMessage('rover-apl__header-UNSORTED'),
                'default'   => true,
                'sort'      => 'UNSORTED',
                'editable'  => Account::getInstance()->isUnsortedOn(),
                "type"      => "checkbox",

            ),
            array(
                'id'        => 'LEAD',
                'name'      => Loc::getMessage('rover-apl__header-LEAD'),
                'default'   => true,
                'sort'      => 'LEAD',
                'editable'  => true,
                "type"      => "checkbox"
            ),
            array(
                'id'        => 'CONTACT',
                'name'      => Loc::getMessage('rover-apl__header-CONTACT'),
                'default'   => true,
                'sort'      => 'CONTACT',
                'editable'  => true,
                "type"      => "checkbox"
            ),
            array(
                'id'        => 'COMPANY',
                'name'      => Loc::getMessage('rover-apl__header-COMPANY'),
                'default'   => true,
                'sort'      => 'COMPANY',
                'editable'  => true,
                "type"      => "checkbox"
            ),
            array(
                'id'        => 'TASK',
                'name'      => Loc::getMessage('rover-apl__header-TASK'),
                'default'   => true,
                'sort'      => 'TASK',
                'editable'  => true,
                "type"      => "checkbox"
            ),
            array(
                'id'        => 'ELEMENTS_CNT',
                'name'      => Loc::getMessage('rover-apl__header-ELEMENTS_CNT'),
                'default'   => true,
                'sort'      => 'ELEMENTS_CNT'
            ),
        );
    }

    /**
     * @return array
     * @throws Main\LoaderException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getActionPanel()
    {
        $buttons = array();

        $buttonAdd = array(
            'TEXT'  => Loc::getMessage('rover-apl__action-add'),
            'TITLE' => Loc::getMessage('rover-apl__action-add_title'),
            'MENU'  => array(),
            'ICON'  => 'btn-list'
        );

        foreach (Source::$types as $type)
        {
            if (!isset(Source::$classes[$type]))
                continue;

            /**
             * @var Source $sourceClassName
             */
            $sourceClassName = Source::$classes[$type];
            if (strlen($sourceClassName::$module) && !Main\Loader::includeModule($sourceClassName::$module))
                continue;

            $buttonAdd['MENU'][] = array(
                'ICONCLASS' => 'add',
                'TEXT'      => Loc::getMessage('rover-apl__action-add_' . $type),
                'ONCLICK'   => "amoCrmPresetList.popup('" . $type . "')"
            );
        }

        $buttons[] = $buttonAdd;
        $buttons[] = array('SEPARATOR' => true);

        $buttons[] = array(
            'TEXT'  => Loc::getMessage('rover-apl__action-settings'),
            'TITLE' => Loc::getMessage('rover-apl__action-settings_title'),
            'LINK'  => '/bitrix/admin/settings.php?lang=' . LANGUAGE_ID . '&mid=rover.amocrm&mid_menu=1',
            'ICON'  => 'btn-settings'
        );

        return $buttons;
    }

    /**
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\NotSupportedException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function requestProcess()
    {
        switch ($this->getRequestAction()){
            case 'delete':
                $this->requestDelete();
                break;
            case 'edit':
                $this->requestEdit();
                break;
            case 'activate':
                $this->requestSetPresetActive('Y');
                break;
            case 'deactivate':
                $this->requestSetPresetActive('N');
                break;
        }
    }

    /**
     * @param Tab $presetTab
     * @param     $active
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function setPresetActive(Tab $presetTab, $active)
    {
        if ($active != 'N')
            $active = 'Y';

        $presetTab->setInputValue(Tabs::INPUT__CONNECTION_ENABLED, $active);
    }

    /**
     * @param $active
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function requestSetPresetActive($active)
    {
        $ids = $this->getRequestIds();
        if (!count($ids))
            return;

        $options = Options::load();

        foreach ($ids as $id){
            $id = intval($id);
            if (!$id) continue;

            $presetTab = $options->getTabControl()->getTabByPresetId($id);
            $this->setPresetActive($presetTab, $active);
        }
    }

    /**
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\NotSupportedException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function requestEdit()
    {
        $fields     = $this->request->get('FIELDS');
        $options    = Options::load();

        foreach ($fields as $presetId => $field){
            $presetTab = $options->getTabControl()->getTabByPresetId($presetId);
            if (!$presetTab instanceof Tab)
                continue;

            foreach ($field as $fieldName => $fieldValue){

                switch ($fieldName) {
                    case 'NAME':
                        $presetTab->setPresetName($fieldValue);
                        break;
                    case 'ACTIVE':
                        $this->setPresetActive($presetTab, $fieldValue);
                        break;
                    case 'LEAD':
                        $presetTab->setInputValue(Tabs::INPUT__LEAD_CREATE, $fieldValue == 'Y' ? 'Y' : 'N');
                        break;
                    case 'CONTACT':
                        $presetTab->setInputValue(Tabs::INPUT__CONTACT_CREATE, $fieldValue == 'Y' ? 'Y' : 'N');
                        break;
                    case 'COMPANY':
                        $presetTab->setInputValue(Tabs::INPUT__COMPANY_CREATE, $fieldValue == 'Y' ? 'Y' : 'N');
                        break;
                    case 'TASK':
                        $presetTab->setInputValue(Tabs::INPUT__TASK_CREATE, $fieldValue == 'Y' ? 'Y' : 'N');
                        break;
                    case 'UNSORTED':
                        $presetTab->setInputValue(Tabs::INPUT__UNSORTED_CREATE, $fieldValue == 'Y' ? 'Y' : 'N');
                        break;
                    case 'MANAGER':
                        $presetTab->setInputValue(Tabs::INPUT__MAIN_USER, $fieldValue);
                        break;
                }
            }
        }

        $options->message->addOk(Loc::getMessage('rover-apl__action-update_success'));
    }

    /**
     * @return array|null|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getRequestIds()
    {
        $ids = $this->request->get('ID');
        if (!is_array($ids))
            $ids = array($ids);

        return $ids;
    }

    /**
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function requestDelete()
    {
        $ids = $this->getRequestIds();

        if (!count($ids))
            return;

        $options = Options::load();

        try{
            foreach ($ids as $id)
                $options->preset->remove($id);

            $options->message->addOk(Loc::getMessage('rover-apl__action-remove_success'));
        } catch (\Exception $e) {
            $options->handleError($e, Loc::getMessage('rover-apl__error_delete', array('#error#' => $e->getMessage())));
        }
    }

    /**
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getSourcesClasses()
    {
        if (is_null($this->sourceClasses)) {
            $this->sourceClasses = array();

            foreach (Source::$types as $sourceType) {

                if (!isset(Source::$classes[$sourceType]))
                    continue;

                $this->sourceClasses[$sourceType] = Source::$classes[$sourceType];
            }
        }

        return $this->sourceClasses;
    }

    /**
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getSourceTypes()
    {
        $sourceClasses  = $this->getSourcesClasses();
        $result         = array();

        foreach ($sourceClasses as $sourceType => $className)
            try{
                $result[$sourceType] = $className::getTypes();
            } catch(\Exception $e) {}

        return $result;
    }

    /**
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\LoaderException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getResult()
    {
        $this->arResult['ROWS']         = $this->getRows();
        $this->arResult['HEADERS']      = $this->getHeaders();
        $this->arResult['NAV']          = $this->getNavObject();
        $this->arResult['SORT']         = $this->getGridSort();
        $this->arResult['SOURCE_TYPES'] = $this->getSourceTypes();
        $this->arResult['ACTION_PANEL'] = $this->getActionPanel();
    }

    /**
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function setTitle()
    {
        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage('rover-apl__title'));
    }

    /**
     * @return mixed|void
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function executeComponent()
    {
        try {
            $this->setFrameMode(false);
            $this->checkParams();
            $this->setTitle();

            $options = Options::load();
            if ($options->getDependenceStatus() && $options->isConnected()){

                if (!Options::isEnabled())
                    $options->message->addError(Loc::getMessage('rover-apl__disabled'), true);

                $this->requestProcess();
                $this->getResult();
            } else {
                $options->message->addError(Loc::getMessage('rover-apl__no-connection'));
            }

            $this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }
}