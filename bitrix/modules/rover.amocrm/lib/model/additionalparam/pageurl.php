<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.10.2017
 * Time: 20:32
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model\AdditionalParam;

use Rover\AmoCRM\Model\AdditionalParam;

/**
 * Class SiteName
 *
 * @package Rover\AmoCRM\Model\Field
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class PageUrl extends AdditionalParam
{
    /**
     * @return null|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getValue()
    {
        global $APPLICATION;
        return $APPLICATION->GetCurPage(true);
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getName()
    {
        return self::PARAM__PAGE_URL;
    }
}