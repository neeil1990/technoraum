<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 25.09.2017
 * Time: 19:53
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Config;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Config\TabField\Company;
use Rover\AmoCRM\Config\TabField\Contact;
use Rover\AmoCRM\Config\TabField\Lead;
use Rover\AmoCRM\Config\TabField\Task;
use Rover\AmoCRM\Helper\CustomField;
use Rover\AmoCRM\Helper\Placeholder;
use Rover\AmoCRM\Model\AdditionalParam;
use Rover\AmoCRM\Model\Rest;
use Rover\AmoCRM\Entity\Source;
use Rover\Fadmin\Inputs\Input;
use Rover\Fadmin\Inputs\SubTabControl;
use Rover\Fadmin\Inputs\Tab;
use Rover\Fadmin\Helper\InputFactory;

/**
 * Class Field
 *
 * @package Rover\AmoCRM\Config
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class TabField
{
    /**
     * @param Tab $tab
     * @param     $restType
     * @return array
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getMappingSubTabs(Tab $tab, $restType)
    {
        $subTabs = [];

        // auto subtab
        $autoInputs = Source::buildFromTab($tab)->getInputs($restType);
        if (count($autoInputs)){
            $subTabAuto = InputFactory::getSubTab('auto', $autoInputs,
                Tabs::getFieldsHelp($restType), false, false,
                Tabs::getFieldsLabel($restType)
            );

            $subTabs[] = $subTabAuto;
        }

        // custom sub tab
        $selectBoxes = CustomField::getSelectBoxes($restType);
        if (count($selectBoxes)) {
            $subTabCustom = InputFactory::getSubTab('custom', $selectBoxes,
                Tabs::getFieldsHelp($restType, 'custom'), false, false,
                Tabs::getFieldsLabel($restType, 'custom')
            );

            $subTabs[] = $subTabCustom;
        }

        // utm
        $utmInputs = AdditionalParam\AdvMarks::getInputs($tab->getPresetId(), $restType);
        if (count($utmInputs)) {
            $subTabUtm = InputFactory::getSubTab('adv', $utmInputs,
                Tabs::getFieldsHelp($restType, 'adv'), false, false,
                Tabs::getFieldsLabel($restType, 'adv')
            );

            $subTabs[] = $subTabUtm;
        }

        // additional
        $additionalInputs = self::getAdditionalInputs($restType);
        if (count($additionalInputs)) {
            $subTabAdd = InputFactory::getSubTab('add', $additionalInputs,
                Tabs::getFieldsHelp($restType, 'add'), false, false,
                Tabs::getFieldsLabel($restType, 'add')
            );

            $subTabs[] = $subTabAdd;
        }

        return $subTabs;
    }

    /**
     * @param Tab $tab
     * @param     $restType
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function addMappingSubTabs(Tab $tab, $restType)
    {
        $restType = trim($restType);
        if (!strlen($restType))
            throw new ArgumentNullException('restType');

        /** @var SubTabControl $subTabControl */
        $subTabControl = $tab->searchOneByName(Tabs::INPUT__MAPPING_SUBTABCONROL . $restType);

        if (!$subTabControl) return;

        $subTabs = self::getMappingSubTabs($tab, $restType);
        $subTabControl->setSubTabsArray($subTabs);
    }

    /**
     * @param        $type
     * @param        $restType
     * @param        $label
     * @param bool   $disabled
     * @param string $help
     * @param string $postInput
     * @return array|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function createInputArrayByType($type, $restType, $label, $disabled = false, $postInput = '', $help = '')
    {
        if (!in_array($restType, [
            Rest\Contact::getType(),
            Rest\Company::getType(),
            Rest\Lead::getType(),
            Rest\Task::getType()]))
            return null;

        $help       = trim($help);
        $helpLoc    = Loc::getMessage('rover-acrm__' . $type . '-help');

        if (strlen($help) && strlen($helpLoc))
            $helpResult = $help . '<br>' . $helpLoc;
        else
            $helpResult = $help . $helpLoc;

        $fieldsArray    = Tabs::getInputSelectCustomFields($restType, $type . $restType);

        $fieldsArray['label']    = $label;
        $fieldsArray['help']     = trim($helpResult);
        $fieldsArray['postInput']= trim($postInput);
        $fieldsArray['disabled'] = $disabled;

        return $fieldsArray;
    }

    /**
     * @param      $restType
     * @param bool $disabled
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getAdditionalInputs($restType, $disabled = false)
    {
        $additionalParamsClasses = AdditionalParam::getClassesList();
        if (!count($additionalParamsClasses))
            return [];

        $result = [];

        foreach ($additionalParamsClasses as $additionalParamClass) {
            // except 'all adv marks'
            if ($additionalParamClass == AdditionalParam\AdvMarks::getClassName())
                continue;

            /** @var AdditionalParam $additionalParamClass $type */
            $type = trim($additionalParamClass::getTemplate());
            if ($type == trim(AdditionalParam\AdvMarks::getTemplate()))
                continue;

            $label = $additionalParamClass::getLabel();

            $inputArray = self::createInputArrayByType(
                $type,
                $restType,
                $label ? : $additionalParamClass::getName(),
                $disabled,
                '<small style="color: #777">' . Placeholder::build($additionalParamClass::getName()) . '</small>'
            );

            if (is_array($inputArray)) $result[] = $inputArray;
        }

        return $result;
    }

    /**
     * @param Tab $tab
     * @throws ArgumentNullException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function preparePresetTab(Tab $tab)
    {
        // add type
        $inputEnabled = $tab->searchOneByName(Tabs::INPUT__CONNECTION_ENABLED);
        if ($inputEnabled instanceof Input) {
            $source = Source::buildFromTab($tab);
            $inputEnabled->setHelp(str_replace(
                '#type#',
                "<a title='{$source->getName()}' href='{$source->getEditUrl()}'>{$source->getName()}</a> ({$source->getTypeLabel()})",
                $inputEnabled->getHelp()
            ));
        }

        $unsortedStatus = Input::getValueStatic(InputFactory::getCheckbox(Tabs::INPUT__UNSORTED_CREATE, 'N'), Options::MODULE_ID, $tab->getPresetId());
        if ($unsortedStatus == 'Y')
            self::fixSettingsToUnsorted($tab);

        self::fixTagsHelp($tab);

        // unsorted default name
        self::setDefaultValue($tab, Tabs::INPUT__UNSORTED_NAME);

        Lead::add($tab, $unsortedStatus);
        Contact::add($tab, $unsortedStatus);
        Company::add($tab, $unsortedStatus);
        Task::add($tab, $unsortedStatus == 'Y');

        $tab->addInputArray(InputFactory::getHeader(Loc::getMessage('rover-acrm__remove-preset_label')));
        $tab->addInputArray(InputFactory::getRemovePreset(Tabs::INPUT__REMOVE_PRESET, Loc::getMessage('rover-acrm__remove-preset_popup')));
    }

    /**
     * @param Tab $tab
     * @param     $name
     * @throws ArgumentNullException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function setDefaultValue(Tab $tab, $name)
    {
        $name = trim($name);
        if (!strlen($name))
            throw new ArgumentNullException('name');

        $input = $tab->searchOneByName($name);
        if (!$input instanceof Input)
            throw new SystemException($name . ' input not found!');

        $sourceType = Preset::getTypeById($tab->getPresetId());

        if (!$input->isDisabled() && !$input->getValue())
            $input->setValue(Loc::getMessage($name . '_default_' . $sourceType));

        $source = Source::buildFromTab($tab);
        $help   = Placeholder::addLegend($source, Loc::getMessage($name . '_help_' . $sourceType));

        $input->setHelp($help);
    }

    /**
     * @param Tab $tab
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function fixTagsHelp(Tab $tab)
    {
        $tagInput = $tab->searchOneByName(Tabs::INPUT__TAG, $tab->getPresetId(), $tab->getSiteId());

        if ($tagInput instanceof Input){
            $source = Source::buildFromTab($tab);
            $tagInput->setHelp(Placeholder::addLegend($source, $tagInput->getHelp()));
        }
    }

    /**
     * @param Tab $tab
     * @throws ArgumentNullException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function fixSettingsToUnsorted(Tab $tab)
    {
        // disable lead status/pipeline input
        $leadStatusInput = $tab->searchOneByName(Tabs::INPUT__LEAD_STATUS);
        if ($leadStatusInput instanceof Input){
            $leadStatusInput->setDisabled(true);
            $leadStatusInput->setHelp(Loc::getMessage(Tabs::INPUT__LEAD_STATUS . '_unsorted_help'));
        }

        // fix lead duplicate action
        $leadDuplicateAction = $tab->searchOneByName(Tabs::INPUT__LEAD_DUPLICATE_ACTION);
        if ($leadDuplicateAction instanceof Input){
            $leadDuplicateAction->setDisabled(true);
            $leadDuplicateAction->setHelp(Loc::getMessage(Tabs::INPUT__LEAD_DUPLICATE_ACTION . '_unsorted_help'));
        }

        // fix contact duplicate action
        $contactDuplicateAction = $tab->searchOneByName(Tabs::INPUT__CONTACT_DUPLICATE_ACTION);
        if ($contactDuplicateAction instanceof Input){
            $contactDuplicateAction->setDisabled(true);
            $contactDuplicateAction->setHelp(Loc::getMessage(Tabs::INPUT__CONTACT_DUPLICATE_ACTION . '_unsorted_help'));
        }
    }

    /**
     * @param Tab  $tab
     * @param      $inputs
     * @param bool $disabled
     * @return Tab
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addList2tab(Tab $tab, $inputs, $disabled = false)
    {
        foreach ($inputs as $input)
            $tab->addInputArray($input)->setDisabled($disabled);

        return $tab;
    }

    /**
     * @param      $restType
     * @param      $name
     * @param null $label
     * @param null $help
     * @param null $postInput
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getAvailableRestFields($restType, $name, $label = null, $help = null, $postInput = null)
    {
        switch ($restType)
        {
            case Rest\Contact::getType():
                $firstElement           = InputFactory::getText(Rest\Contact::FIELD__NAME);
                $firstElement['label']  = Loc::getMessage(Rest\Contact::FIELD__NAME . '_' . $restType . '_label');
                break;
            case Rest\Company::getType():
                $firstElement           = InputFactory::getText(Rest\Company::FIELD__NAME);
                $firstElement['label']  = Loc::getMessage(Rest\Company::FIELD__NAME . '_' . $restType . '_label');
                break;
            case Rest\Lead::getType():
                $firstElement = InputFactory::getNumber(Tabs::INPUT__LEAD_PRICE);
                break;
            case Rest\Task::getType():
                $firstElement = Tabs::getInputTaskText();
                break;
            default:
                throw new ArgumentOutOfRangeException('object');
        }
        //echo 'fff';
        //pr(TabList::getOptions($restType, $firstElement, false));
        //die('aaa');

        $select = InputFactory::getSelect($name, TabList::getOptions($restType, $firstElement, false), null, false, false, $label, $help);

        if (strlen($postInput))
            $select['postInput'] = $postInput;

        return $select;
    }
}