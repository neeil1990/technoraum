<?php
namespace Rover\AmoCRM\Model\Rest;

/**
 * Class Company
 *
 * @package Rover\AmoCRM\Model\Rest
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Company extends Contact
{
    const NAME  = 'companies';
    const URL   = '/api/v2/' . self::NAME;

    /**
     * @return null
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getCustomFields()
	{
        return Account::getInstance()->getCustomFields(self::NAME);
	}
}