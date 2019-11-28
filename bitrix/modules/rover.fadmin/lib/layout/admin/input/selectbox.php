<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 12.09.2017
 * Time: 14:55
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\Fadmin\Layout\Admin\Input;

use Rover\Fadmin\Layout\Admin\Input;

/**
 * Class Selectbox
 *
 * @package Rover\Fadmin\Layout\Admin\Input
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Selectbox extends Input
{
    /**
     * @param bool $empty
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function showLabel($empty = false)
    {
        if ($this->input->isMultiple())
            parent::showMultiLabel();
        else
            parent::showLabel($empty);
    }

    /**
     * @return mixed|void
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function showInput()
    {
        if (!$this->input instanceof \Rover\Fadmin\Inputs\Selectbox)
            return;

        $value      = $this->input->getValue();
        $valueId    = $this->input->getFieldId();
        $valueName  = $this->input->getFieldName();
        $multiple   = $this->input->isMultiple();

        ?><select
        <?=$this->input->isDisabled() ? ' disabled="disabled" ': '';?>
        <?=$this->input->isRequired() ? ' required="required" ': '';?>
        name="<?=$valueName . ($multiple ? '[]' : '')?>"
        id="<?=$valueId?>"
        size="<?=$this->input->getSize()?>"
        <?=$multiple ? ' multiple="multiple" ' : ''?>>
        <?php

        foreach($this->input->getOptions() as $v => $k){
            if ($multiple) {
                $selected = is_array($value) && in_array($v, $value)
                    ? true
                    : false;
            } else {
                $selected = $value==$v ? true : false;
            }

            ?><option value="<?=$v?>"<?=$selected ? " selected=\"selected\" ": ''?>><?=$k?></option><?php
        }
        ?>
        </select>
        <?php
    }
}