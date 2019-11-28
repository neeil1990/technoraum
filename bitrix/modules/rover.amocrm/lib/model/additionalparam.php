<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.10.2017
 * Time: 19:46
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Helper\Placeholder;

Loc::loadMessages(__FILE__);
/**
 * Class Field
 *
 * @package Rover\AmoCRM\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
abstract class AdditionalParam
{
    const PARAM__MARKS      = 'utm';
    const PARAM__IP         = 'ip';
    const PARAM__VISITOR_UID= 'visitor_uid';
    const PARAM__DOMAIN     = 'domain';
    const PARAM__SITE_NAME  = 'site-name';
    const PARAM__PAGE_URL   = 'page-url';

    /**
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     * @return string
     */
    public static function getName()
    {
        throw new NotImplementedException();
    }

    /**
     * @return AdditionalParam[]
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getClassesList()
    {
        return array (
            __CLASS__ . '\VisitorUid',
            __CLASS__ . '\Domain',
            __CLASS__ . '\SiteName',
            __CLASS__ . '\Ip',
            __CLASS__ . '\PageUrl',
            __CLASS__ . '\AdvMarks',
        );
    }

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getTemplate()
    {
        $name = static::getName();
        if (!strlen($name))
            throw new ArgumentNullException('name');

        return $name . '_';
    }

    /**
     * @param null   $placeholder
     * @param string $param
     * @return string
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getLabel($placeholder = null, $param = '')
    {
        $code   = 'rover-acrm__' . static::getTemplate();
        $param  = trim($param);
        if (strlen($param))
            $code .= '-' . $param;

        $code .= '-label';

        $label = Loc::getMessage($code);
        if (!strlen($label) && strlen($param))
            $label = $param;

        if (strlen($placeholder))
            $label .= ' (' . Placeholder::build($placeholder) . ')';

        return strip_tags($label);
    }

    /**
     * @param Result       $result
     * @param Handler|null $handler
     * @return null
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getResultValue(Result $result, Handler $handler)
    {
        $additionalParams = $result->getAdditionalParams();

        return isset($additionalParams[static::getName()])
            ? $additionalParams[static::getName()]
            : null;
    }

    /**
     * @return string | AdditionalParam
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getClassName()
    {
        return get_called_class();
    }
}