<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 29.05.2017
 * Time: 10:58
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Test;

/**
 * Class Form
 *
 * @package Rover\AmoCRM\Test
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Form
{
    /**
     * @param int $formId
     * @param int $resultId
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function newForm($formId = 1, $resultId = 1)
    {
        \RoverAmoCRMEvents::onAfterResultAddCRM($formId, $resultId, true);
    }
}