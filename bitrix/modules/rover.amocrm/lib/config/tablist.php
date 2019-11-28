<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.06.2017
 * Time: 16:57
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Config;

use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Helper\CustomField;
use Rover\AmoCRM\Model\Rest;
use Rover\AmoCRM\Model\Rest\Account;
use Bitrix\Main\ArgumentNullException;
use Rover\Fadmin\Inputs\Input;
use Rover\Fadmin\Helper\InputFactory;
use Rover\Fadmin\Inputs\Label;

Loc::loadMessages(__FILE__);
/**
 * Class TabList
 *
 * @package Rover\AmoCRM\Helper
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class TabList
{
    const TYPE__OPTIONS     = 'options';
    const TYPE__STATUS      = 'status';
    const TYPE__USER        = 'user';
    const TYPE__PIPELINE    = 'pipeline';
    const TYPE__TASK_TYPE   = 'task_type';

    /**
     * cache
     * @var array
     */
    protected static $values = array();

    /**
     * @param $restType
     * @param $filterCustomSelectBoxes
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function getOptionTypeKey($restType, $filterCustomSelectBoxes)
    {
        return self::TYPE__OPTIONS . '-' . $restType . ($filterCustomSelectBoxes ? 'fcsb' : '');
    }
    /**
     * @param       $restType
     * @param array $firstElement
     * @param bool  $filterCustomSelectBoxes
     * @return mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getOptions($restType, $firstElement = array(), $filterCustomSelectBoxes = true)
    {
        $type = self::getOptionTypeKey($restType, $filterCustomSelectBoxes);

        if (!self::check($type)){

            self::set($type, 'NO', Loc::getMessage('rover_acrm__no')); // add empty value
            self::setOption($firstElement, $type); // add first element

            // add custom fields
            $options = CustomField::getInputs($restType);

            if (count($options))
                foreach ($options as $option)
                {
                    if (CustomField::isCustomSelectBox($option)){
                        if ($filterCustomSelectBoxes)
                            continue;
                        else
                            $option['label'] .= ' (' . Loc::getMessage('rover_acrm__list') . ')';
                            //pr($option);

                        // for setOption capability
                        unset($option['options']);
                    }

                    self::setOption($option, $type);
                }

            // add note
            // @TODO: add note to task
            if ($restType != Rest\Task::getType())
                self::setOption(InputFactory::getText(Rest\Note::getType()), $type);
        }

        return self::get($type);
    }

    /**
     * @param array $option
     * @param       $type
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function setOption(array $option, $type)
    {
        if (!isset($option['type'])
            || ($option['type'] == Label::getType()))
            return;

        if (isset($option['options'])){
            foreach ($option['options'] as $key => $value)
                if ($key)
                    self::set($type, $option['name'] . ':' . $key,
                        $option['label'] . ' - ' . $value);
        } else
            self::set($type, $option['name'], $option['label']);
    }

    /**
     * @param string $pipelineId
     * @return mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getStatuses($pipelineId = 'main')
    {
        $type = self::TYPE__STATUS . '-' . $pipelineId;

        if (!self::check($type)){

            $pipelines = Account::getInstance()->getPipelines();
            foreach ($pipelines as $pipeline)
                if ((($pipelineId == 'main') && (!empty($pipeline['is_main'])))
                    || ($pipeline['id'] == $pipelineId)) {
                // if main or by id
                    self::setValues($type, $pipeline['statuses']);
                    break;
                }
        }

        return self::get($type);
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getGroupedStatuses()
    {
        $pipelines  = self::getPipelines();
        $result     = array();

        foreach ($pipelines as $pipelineId => $pipelineName)
            $result[$pipelineId] = array(
                'name'      => $pipelineName,
                'options'   => self::getStatuses($pipelineId)
            );

        return $result;
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getStatusesWithPipelines()
    {
        $pipelines  = self::getPipelines();
        $result     = array();

        $commonStatuses = [
            142 => null,
            143 => null,
        ];

        foreach ($pipelines as $pipelineId => $pipelineName)
        {
            $statuses = self::getStatuses($pipelineId);

            foreach ($statuses as $statusId => $statusName) {
                if (array_key_exists($statusId, $commonStatuses)) {
                    if (is_null($commonStatuses[$statusId]))
                        $commonStatuses[$statusId] = '[' . Loc::getMessage('rover_acrm__all') . '] ' .  $statusName;

                    continue;
                }

                $result[$statusId] = '[' . $pipelineName . '] ' . $statusName;
            }
        }

        return $result + $commonStatuses;
    }

    /**
     * @return mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getPipelines()
    {
        if (!self::check(self::TYPE__PIPELINE))
            self::setValues(self::TYPE__PIPELINE, Account::getInstance()->getPipelines());

        return self::get(self::TYPE__PIPELINE);
    }

    /**
     * @return mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getUsers()
    {
        if (!self::check(self::TYPE__USER))
            self::setValues(self::TYPE__USER, Account::getInstance()->getUsers());

        return self::get(self::TYPE__USER);
    }

    /**
     * @return mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */

    public static function getTaskTypes()
    {
        if (!self::check(self::TYPE__TASK_TYPE))
            self::setValues(self::TYPE__TASK_TYPE, Account::getInstance()->getTaskTypes());

        return self::get(self::TYPE__TASK_TYPE);
    }

    /**
     * @param $type
     * @return bool
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function check($type)
    {
        return (bool)self::get($type);
    }

    /**
     * @param       $type
     * @param array $values
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function setValues($type, array $values = null)
    {
        if (!is_array($values))
            return;

        foreach ($values as $value){

            if (isset($value['last_name']))
                $value['name'] .= ' ' . $value['last_name'];

            self::set($type, $value['id'], $value['name']);
        }
    }

    /**
     * @param $type
     * @param $id
     * @param $value
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function set($type, $id, $value)
    {
        $type = trim($type);
        if (!strlen($type))
            throw new ArgumentNullException('type');

        $id = trim($id);
        if (!strlen($id))
            throw new ArgumentNullException('id');

        self::$values[$type][$id] = $value;
    }

    /**
     * @param $type
     * @return mixed|null
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function get($type)
    {
        $type = trim($type);
        if (!strlen($type))
            throw new ArgumentNullException('type');

        return isset(self::$values[$type])
            ? self::$values[$type]
            : array();
    }
}