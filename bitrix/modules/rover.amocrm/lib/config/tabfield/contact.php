<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 17.10.2017
 * Time: 11:13
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Config\TabField;

use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Config\TabField;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Model\Rest;
use Rover\Fadmin\Inputs\Tab;
use Rover\Fadmin\Helper\InputFactory;

/**
 * Class Contact
 *
 * @package Rover\AmoCRM\Config\TabField
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Contact extends TabField
{
    /**
     * @param Tab $tab
     * @param     $unsortedStatus
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function add(Tab $tab, $unsortedStatus)
    {
        self::addInputs($tab,
            $unsortedStatus,
            Rest\Contact::getType(),
            Tabs::INPUT__CONTACT_CREATE,
            Tabs::INPUT__CONTACT_DUPLICATE_CONTROL,
            Tabs::INPUT__CONTACT_DUPLICATE_FIELDS,
            Tabs::INPUT__CONTACT_DUPLICATE_ACTION,
            Tabs::INPUT__CONTACT_DUPLICATE_LOGIC
        );

    }

    /**
     * @param Tab $tab
     * @param     $unsortedStatus
     * @param     $restType
     * @param     $enabledInput
     * @param     $duplicateControlInput
     * @param     $duplicateFieldsInput
     * @param     $duplicateActionInput
     * @param     $duplicateLogicInput
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addInputs(Tab $tab,
                                        $unsortedStatus,
                                        $restType,
                                        $enabledInput,
                                        $duplicateControlInput,
                                        $duplicateFieldsInput,
                                        $duplicateActionInput,
                                        $duplicateLogicInput)
    {
        $tab->addInputArray(Tabs::getObjectHeader($restType));
        $tab->addInputArray(InputFactory::getCheckbox($enabledInput, $restType == Rest\Contact::getType() ? 'Y' : 'N'));

        // fields mapping
        $tab->addInputArray(InputFactory::getSubTabControl(Tabs::INPUT__MAPPING_SUBTABCONROL . $restType, TabField::getMappingSubTabs($tab, $restType)));

        // duplicates
        $tab->addInputArray(Tabs::getFieldsHeader($restType, 'duplicates'));
        $tab->addInputArray(InputFactory::getCheckbox($duplicateControlInput, 'N', false, Loc::getMessage('duplicate__control-label'), Loc::getMessage('duplicate__control-contact-help')));
        $tab->addInputArray(self::getDuplicatesFields($restType, $duplicateFieldsInput));

        $tab->addInputArray(Tabs::getDuplicateLogic($duplicateLogicInput, $unsortedStatus));
        $tab->addInputArray(Tabs::getDuplicateAction($duplicateActionInput, $unsortedStatus));
    }

    /**
     * @param $restType
     * @param $duplicateFieldsInput
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function getDuplicatesFields($restType, $duplicateFieldsInput)
    {
        $fields = Tabs::getInputSelectCustomFields($restType,
            $duplicateFieldsInput,
            Loc::getMessage('duplicate__fields-label'),
            Loc::getMessage('duplicate__fields-help')
        );
        $fields['multiple'] = true;
        unset($fields['options'][Rest\Note::getType()]);

        return $fields;
    }
}