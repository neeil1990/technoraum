<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 17.10.2017
 * Time: 11:15
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Config\TabField;

use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Model\Rest;
use Rover\Fadmin\Inputs\Tab;

/**
 * Class Company
 *
 * @package Rover\AmoCRM\Config\TabField
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Company extends Contact
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
        return self::addInputs($tab,
            $unsortedStatus,
            Rest\Company::getType(),
            Tabs::INPUT__COMPANY_CREATE,
            Tabs::INPUT__COMPANY_DUPLICATE_CONTROL,
            Tabs::INPUT__COMPANY_DUPLICATE_FIELDS,
            Tabs::INPUT__COMPANY_DUPLICATE_ACTION,
            Tabs::INPUT__COMPANY_DUPLICATE_LOGIC
        );
    }
}