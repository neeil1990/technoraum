<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.01.2016
 * Time: 17:33
 *
 * @author Pavel Shulaev (http://rover-it.me)
 */

namespace Rover\Fadmin\Inputs;

/**
 * Class Selectbox
 *
 * @package Rover\Fadmin\Inputs
 * @author  Pavel Shulaev (http://rover-it.me)
 */
class Selectgroup extends Selectbox
{
    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getGroupName()
    {
        return $this->name . '_group';
    }

    /**
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getGroupValueName()
    {
        return self::getFullPath($this->getGroupName(),
            $this->getPresetId(), $this->getSiteId());
    }

    /**
     * @return Input
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getGroupInput()
    {
        $params = array(
            'name' => $this->getGroupName(),
            'type' => Hidden::getType()
        );

        return self::build($params, $this->optionsEngine, $this->parent);
    }

    /**
     * @return array|string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getGroupValue()
    {
        return $this->getGroupInput()->getValue();
    }

    /**
     * @param $value
     * @return $this|Input
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setGroupValue($value)
    {
        return $this->getGroupInput()->setValue($value);
    }

    /**
     * @return int|null|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function calcGroupValue()
    {
        $searchValue = $this->value;
        if (!is_array($searchValue))
            $searchValue = array($searchValue);

        reset($this->options);

        if (!count($searchValue))
            return key($this->options);

        foreach ($this->options as $key => $group)
            if (count(array_intersect($searchValue, array_keys($group['options']))))
                return $key;

        reset($this->options);

        return key($this->options);
    }

    /**
     * @param $value
     * @return bool
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     * @internal
     */
    public function beforeSaveValue(&$value)
    {
        $this->getGroupInput()->setValueFromRequest();

        return true;
    }
}