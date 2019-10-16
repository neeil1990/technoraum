<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 17.10.2017
 * Time: 11:09
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
namespace Rover\AmoCRM\Config\TabField;

use Rover\AmoCRM\Config\TabField;
use Rover\AmoCRM\Config\TabList;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Model\Rest;
use Rover\Fadmin\Inputs\Selectbox;
use Rover\Fadmin\Inputs\Selectgroup;
use \Rover\Fadmin\Inputs\Tab;

/**
 * Class Lead
 *
 * @package Rover\AmoCRM\Config\TabField
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Lead extends TabField
{
    /**
     * @param Tab    $tab
     * @param string $unsortedStatus
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function add(Tab $tab, $unsortedStatus = 'N')
    {
        self::setDefaultValue($tab, Tabs::INPUT__LEAD_NAME);
        self::fixPipelineStatuses($tab);
        self::addMappingSubTabs($tab, Rest\Lead::getType());
    }

    /**
     * @param Tab $tab
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function fixPipelineStatuses(Tab $tab)
    {
        $statusesInput = $tab->searchOneByName(Tabs::INPUT__LEAD_STATUS, $tab->getPresetId(), $tab->getSiteId());

        // fix pipeline
        if ($statusesInput instanceof Selectgroup){
            $pipelineId = $statusesInput->getGroupValue();
            if ($pipelineId){
                $pipelines = Rest\Account::getInstance()->getPipelines();

                if (!isset($pipelines[$pipelineId])){
                    $firstPipeline      = reset($pipelines);
                    $firstPipelineId    = isset($firstPipeline['id'])
                        ? $firstPipeline['id']
                        : null;

                    $statusesInput->setGroupValue($firstPipelineId);
                }
            }
        } elseif ($statusesInput instanceof Selectbox) {
            $statusesInput->setOptions(TabList::getStatuses());
        }
    }
}