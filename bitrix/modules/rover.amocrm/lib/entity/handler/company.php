<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.05.2017
 * Time: 14:09
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Entity\Handler;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Config\Tabs;

Loc::loadMessages(__FILE__);
/**
 * Class Contact
 *
 * @package Rover\AmoCRM\Entity\Handler
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Company extends Contact
{
    /**
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateAction($reload = false)
    {
        return $this->getDuplicateActionByInput(Tabs::INPUT__COMPANY_DUPLICATE_ACTION, $reload);
    }

    /**
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getDuplicateStatus($reload = false)
    {
        return $this->getSourceTabValue(Tabs::INPUT__COMPANY_DUPLICATE_CONTROL, $reload);
    }

    /**
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateLogic($reload = false)
    {
        return $this->getSourceTabValue(Tabs::INPUT__COMPANY_DUPLICATE_LOGIC, $reload);
    }

    /**
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getDuplicateFields()
    {
        return $this->getSourceTabValue(Tabs::INPUT__COMPANY_DUPLICATE_FIELDS);
    }
}