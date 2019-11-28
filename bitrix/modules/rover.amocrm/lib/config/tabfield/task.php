<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 17.10.2017
 * Time: 11:17
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Config\TabField;

use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Config\TabField;
use Rover\AmoCRM\Config\TabList;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Helper\Placeholder;
use Rover\AmoCRM\Model\Rest;
use Rover\AmoCRM\Entity\Source;
use Rover\Fadmin\Inputs\Tab;
use Rover\Fadmin\Helper\InputFactory;
use \Rover\AmoCRM\Entity\Handler;
/**
 * Class Task
 *
 * @package Rover\AmoCRM\Config\TabField
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Task extends TabField
{
    /**
     * @param Tab $tab
     * @param     $disabled
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function add(Tab $tab, $disabled)
    {
        $header = $tab->addInputArray(Tabs::getObjectHeader(Rest\Task::getType()));

        if ($disabled)
            $header->setLabel(Loc::getMessage('rover-acrm__header-' . Rest\Task::getType() . '-disabled'));

        $tab->addInputArray(InputFactory::getCheckbox(Tabs::INPUT__TASK_CREATE, 'N'))->setDisabled($disabled);

        $types = array(
            Rest::ELEMENT_TYPE__CONTACT => Loc::getMessage(Rest\Contact::getType() . '_label'),
            Rest::ELEMENT_TYPE__COMPANY => Loc::getMessage(Rest\Company::getType() . '_label'),
            Rest::ELEMENT_TYPE__LEAD    => Loc::getMessage(Rest\Lead::getType() . '_label')
        );

        $tab->addInputArray(InputFactory::getSelect(Tabs::INPUT__TASK_ELEMENT_TYPE, $types, null, false, $disabled));
        $tab->addInputArray(InputFactory::getSelect(Tabs::INPUT__TASK_TYPE, TabList::getTaskTypes(), null, false, $disabled));

        $taskText           = Tabs::getInputTaskText();
        $taskText['help']   = Placeholder::addLegend(Source::buildFromTab($tab), $taskText['help']);

        $tab->addInputArray($taskText)->setDisabled($disabled);
        $tab->addInputArray(InputFactory::getSelect(Tabs::INPUT__TASK_DEADLINE, [
            Handler\Task::DEADLINE__NOW     => Loc::getMessage(Tabs::INPUT__TASK_DEADLINE . '_now'),
            Handler\Task::DEADLINE__DAY_END => Loc::getMessage(Tabs::INPUT__TASK_DEADLINE . '_day_end'),
        ], 'day_end'));

        // fields mapping
        $tab->addInputArray(InputFactory::getSubTabControl(Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Task::getType(),
            TabField::getMappingSubTabs($tab, Rest\Task::getType()), null, false, $disabled));
    }
}