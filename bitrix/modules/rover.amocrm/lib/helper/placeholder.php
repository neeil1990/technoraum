<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 03.10.2017
 * Time: 17:13
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Helper;

use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Entity\Source;
use Rover\AmoCRM\Model\AdditionalParam;

Loc::loadMessages(__FILE__);
/**
 * Class Placeholder
 *
 * @package Rover\AmoCRM\Helper
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Placeholder
{
    /**
     * @param $field
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function build($field)
    {
        $field = trim($field);
        if (!$field)
            return '';

        return '#' . $field . '#';
    }

    /**
     * @param Source $source
     * @param        $field
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function sourceBuild(Source $source, $field)
    {
        $id2placeholder = $source->model->getId2Placeholder();
        if (isset($id2placeholder[$field]))
            $field = $id2placeholder[$field];

        return self::build($field);
    }

    /**
     * @param Handler $handler
     * @param Result  $result
     * @param         $string
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function replace(Handler $handler, Result $result, $string)
    {
        $string = self::prepareString($handler->getSource(), $string);
        $values = self::getList($handler, $result);

        foreach ($values as $key => $value)
            $string = str_replace(self::build($key), $value, $string);

        return $string;
    }

    /**
     * @param Handler $handler
     * @param Result  $result
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getList(Handler $handler, Result $result)
    {
        $values             = $result->getData();
        $additionalParams   = AdditionalParam::getClassesList();

        foreach ($additionalParams as $additionalParam)
            $values[$additionalParam::getName()] = $additionalParam::getResultValue($result, $handler);

        $advMarks = AdditionalParam\AdvMarks::filter($handler->getSourceTab()->getPresetId(), $result->getMarks(), true);
        foreach ($advMarks as $advMarkCode => $advMarkValue)
            $values[AdditionalParam\AdvMarks::getTemplate() . $advMarkCode] = $advMarkValue;

        return $values;
    }

    /**
     * @param Source $source
     * @return array|mixed
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getLabels(Source $source)
    {
        $labels         = $source->model->getLabels(true);
        $result         = array();
        $id2placeholder = $source->model->getId2Placeholder();

        if (empty($id2placeholder))
            $result = $labels;
        else
            foreach ($id2placeholder as $id => $placeholder)
                if (isset($labels[$id]))
                    $result[$placeholder] = strip_tags($labels[$id]);

        $additionalParams = AdditionalParam::getClassesList();
        foreach ($additionalParams as $additionalParam)
            /** @var AdditionalParam $additionalParam */
            $result[$additionalParam::getName()] = $additionalParam::getLabel();

        $advMarks = AdditionalParam\AdvMarks::getFilter($source->getPresetId());
        foreach ($advMarks as $advMark)
            $result[AdditionalParam\AdvMarks::getName() . '_' . $advMark] = Loc::getMessage('rover-acrm__placeholder_adv-mark', array(
                "#mark#" => $advMark
            ));

        return $result;
    }

    /**
     * @param Source $source
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getLegend(Source $source)
    {
        $labels = self::getLabels($source);
        $result = array();

        foreach ($labels as $placeholder => $name)
            $result[] = self::build($placeholder) . ' - ' . $name;

        return implode("\n", $result);
    }

    /**
     * @param Source $source
     * @param        $string
     * @param string $replace
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function addLegend(Source $source, $string, $replace = '#legend#')
    {
        return str_replace($replace, self::getLegend($source), $string);
    }

    /**
     * @param Source $source
     * @param        $string
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function prepareString(Source $source, $string)
    {
        $id2placeholder = $source->model->getId2Placeholder();

        foreach ($id2placeholder as $id => $placeholder)
            $string = str_replace(self::build($placeholder), self::build($id), $string);

        return $string;
    }
}