<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 08.07.2017
 * Time: 15:39
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Helper;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Model\AdditionalParam;
use Rover\AmoCRM\Model\AdditionalParam\AdvMarks;
use Rover\AmoCRM\Model\Rest;

/**
 * Class Field
 *
 * @package Rover\AmoCRM\Helper
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class HandlerField
{
    const GROUP__FIELDS = 'fields';
    const GROUP__NOTES  = 'notes';

    /**
     * @param Handler $handler
     * @param         $type
     * @param         $value
     * @param array   $fields
     * @param null    $label
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addValue(Handler $handler, $type, $value, array $fields = array(), $label = null)
    {
        $presetId = intval($handler->getSourceTab()->getPresetId());
        if (!$presetId)
            throw new ArgumentNullException('presetId');

        $object = trim($handler->getRestType());
        if (!strlen($object))
            throw new ArgumentNullException('restObject');

        $name   = $type . $object;
        $target = Option::get(Options::MODULE_ID, Options::getFullName($name, $presetId));
        $fields = self::addValueByTarget($fields, $target,
            $label ? $label : Loc::getMessage('rover-acrm__' . $type . '-label'), $value, $name);

        return $fields;
    }

    /**
     * @param array $result
     * @param       $target
     * @param       $label
     * @param       $value
     * @param       $code
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addValueByTarget(array $result = array(), $target, $label, $value, $code)
    {
        $target = trim($target);
        $value  = trim($value);

        switch ($target) {
            case '':
            case 'NO':
            case 'no':
                break;

            case Rest\Note::getType():

                if (!isset($result[self::GROUP__NOTES][$label]))
                    $result[self::GROUP__NOTES][$label] = array();

                $result[self::GROUP__NOTES][$label][]
                    = self::prepare($label, $value, $code);

                break;

            default:
                $result[self::GROUP__FIELDS][$target][]
                    = self::prepare($label, $value, $code);
        }

        return $result;
    }

    /**
     * @param $label
     * @param $value
     * @param $code
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function prepare($label, $value, $code)
    {
        $label  = trim($label);
        if (!strlen($label))
            $label = 'no-label';

        return array(
            'title' => $label,
            'value' => trim($value),
            'code'  => trim($code)
        );
    }

    /**
     * @param Handler $handler
     * @param Result  $result
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getFullObjectData(Handler $handler, Result $result)
    {
        $fieldsData = self::addFromSource($handler, $result);
        $fieldsData = self::addAdvMarks($handler, $result, $fieldsData);
        $fieldsData = self::addAdditionalParams($handler, $result, $fieldsData);

        return $fieldsData;
    }

    /**
     * @param Handler $handler
     * @param Result  $result
     * @param array   $data
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addFromSource(Handler $handler, Result $result, $data = array())
    {
        $values     = $result->getData();
        $labels     = $handler->getSource()->model->getLabels(true);
        $map        = $handler->getSource()->model->getMapByObject($handler->getRestType());

        foreach ($map as $code => $target)
        {
            if (!isset($values[$code]))
                continue;

            $value  = $values[$code];
            $label  = isset($labels[$code])
                ? $labels[$code]
                : 'no-label';

            $data = self::addValueByTarget($data, $target, $label, $value, $code);
        }

        return $data;
    }

    /**
     * @param Handler $handler
     * @param Result  $result
     * @param array   $data
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addAdvMarks(Handler $handler, Result $result, $data = array())
    {
        $marks      = AdvMarks::filter($handler->getSourceTab()->getPresetId(), $result->getMarks(), true);
        $template   = trim(AdvMarks::getTemplate());

        foreach ($marks as $markCode => $markValue)
        {
            if (!$markValue)
                continue;

            $fullType = $template . $markCode . '_';

            $data = self::addValue($handler, $fullType, $markValue, $data,
                Loc::getMessage('rover-acrm__hf_adv-mark', array('#mark#' => $markCode)));
        }

        return $data;
    }

    /**
     * @param Handler $handler
     * @param Result  $result
     * @param array   $data
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addAdditionalParams(Handler $handler, Result $result, $data = array())
    {
        $additionalParams = AdditionalParam::getClassesList();
        foreach ($additionalParams as $additionalParam)
        {
            $type = trim($additionalParam::getTemplate());
            if (!strlen($type))
                throw new ArgumentNullException('type');

            $value = $additionalParam::getResultValue($result, $handler);

            $data = self::addValue($handler, $type, $value, $data);
        }

        return $data;
    }
}