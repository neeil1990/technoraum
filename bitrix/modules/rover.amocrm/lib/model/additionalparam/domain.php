<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.10.2017
 * Time: 19:58
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model\AdditionalParam;

use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Model\AdditionalParam;
use Rover\AmoCRM\Model\Site;

/**
 * Class Domain
 *
 * @package Rover\AmoCRM\Model\Field
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Domain extends AdditionalParam
{
    /**
     * @param $siteId
     * @return null|string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getValue($siteId)
    {
        $siteId = trim($siteId);
        if (!$siteId)
            return '';

        return Site::getFieldById($siteId, 'SERVER_NAME');
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getName()
    {
        return self::PARAM__DOMAIN;
    }

    /**
     * @param Result  $result
     * @param Handler $handler
     * @return null|string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getResultValue(Result $result, Handler $handler)
    {
        return self::getValue($result->getSiteId());
    }
}