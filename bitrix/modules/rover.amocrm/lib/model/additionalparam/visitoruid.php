<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.10.2017
 * Time: 11:52
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model\AdditionalParam;

use Rover\AmoCRM\Model\AdditionalParam;

/**
 * Class AdvMarks
 *
 * @package Rover\AmoCRM\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class VisitorUid extends AdditionalParam
{
    /**
     * @return null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getValue()
    {
        return isset($_COOKIE['amocrm_visitor_uid'])
            ? $_COOKIE['amocrm_visitor_uid']
            : null;
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getName()
    {
        return self::PARAM__VISITOR_UID;
    }
}