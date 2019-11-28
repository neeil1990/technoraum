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

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Config\TabField;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Helper\Placeholder;
use Rover\AmoCRM\Model\AdditionalParam;

Loc::loadMessages(__FILE__);
/**
 * Class AdvMarks
 *
 * @package Rover\AmoCRM\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class AdvMarks extends AdditionalParam
{
    /** @var array */
    public static $defaultList = array(
        '_ga',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'utm_referrer',
        '_openstat',
        'roistat',
        'gclid',
        'yclid',
        'from',
    );

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getFullDefaultList()
    {
        $defaultListCount   = count(self::$defaultList);
        $result             = [];

        for ($i = 0; $i < $defaultListCount; ++$i){
            $advMark            = self::$defaultList[$i];
            $result[$advMark]   = Loc::getMessage('rover-acrm__' . self::getTemplate() . '-' . $advMark . '-label');
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getValue()
    {
        /*$server         = Application::getInstance()->getContext()->getServer();
        $serverResult   = array();

        if ($server->get('HTTP_REFERER')) {
            $sResult = parse_url($server->get('HTTP_REFERER'));
            if (isset($sResult['query']))
                parse_str($sResult['query'], $serverResult);
        }*/

        $preResult  = array_merge($_COOKIE, $_SESSION/*, $serverResult*/, $_REQUEST);
        $result     = [];

        foreach ($preResult as $key => $value)
        {
            if (!is_string($value)) continue;

            $encoding = mb_detect_encoding($value, LANG_CHARSET);
            if ($encoding != LANG_CHARSET)
                $value = iconv($encoding, LANG_CHARSET, $value);

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getName()
    {
        return self::PARAM__MARKS;
    }

    /**
     * @param $presetId
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getFilter($presetId)
    {
        $presetId = intval($presetId);
        if (!$presetId)
            throw new ArgumentNullException('presetId');

        $filter = unserialize(Options::getValueStatic(Options::MODULE_ID, Tabs::INPUT__ADV_MARKS_FILTER, $presetId));
        if (empty($filter) || in_array('all', $filter))
            $filter = self::$defaultList;

        // add custom filter
        $customMarksFilter  = array();
        $customMarksRaw     = explode("\n", Options::getValueStatic(Options::MODULE_ID, Tabs::INPUT__ADV_MARKS_CUSTOM_FILTER, $presetId));

        $count = count($customMarksRaw);
        for ($i = 0; $i < $count; ++$i)
        {
            $customMark = trim($customMarksRaw[$i]);
            if (strlen($customMark))
                $customMarksFilter[] = $customMark;
        }

        return array_merge($filter, $customMarksFilter);
    }

    /**
     * @param      $presetId
     * @param      $marks
     * @param bool $prepareValue
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function filter($presetId, $marks, $prepareValue = false)
    {
        $filter = self::getFilter($presetId);
        $result = array();

        foreach ($marks as $markCode => $markValue)
        {
            if (!is_string($markValue)) continue;

            $markValue = trim($markValue);
            if (!strlen($markValue))
                continue;

            foreach ($filter as $filterAdvMark)
                if (mb_strtoupper(trim($filterAdvMark))
                    == mb_strtoupper(trim($markCode)))
                {
                    $result[$markCode] = $prepareValue
                        ? self::prepareValue($presetId, $markCode, $markValue)
                        : $markValue;
                    break;
                }
        }

        return $result;
    }

    /**
     * @param $presetId
     * @param $code
     * @param $value
     * @return null|string|string[]
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function prepareValue($presetId, $code, $value)
    {
        $presetId = intval($presetId);
        if (!$presetId)
            throw new ArgumentNullException('presetId');

        $code = trim($code);
        if (!strlen($code))
            throw new ArgumentNullException('code');

        $value = trim($value);

        switch ($code) {
            case '_ga':
                if (Options::getValueStatic(Options::MODULE_ID, Tabs::INPUT__REMOVE_GA_VERSION, $presetId) == 'Y')
                    $value = preg_replace('#^GA1\.\d{1}\.#usi', '', $value);
        }

        return $value;
    }

    /**
     * @param Result  $result
     * @param Handler $handler
     * @return null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getResultValue(Result $result, Handler $handler)
    {
        $marks  = self::filter($handler->getSourceTab()->getPresetId(), $result->getMarks());
        $result = array();

        foreach ($marks as $markName => $markValue)
            $result[] = $markName . '=' . $markValue;

        return implode(', ', $result);
    }

    /**
     * @param $presetId
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function saveToSession($presetId)
    {
        $enabled = Options::getValueStatic(Options::MODULE_ID, Tabs::INPUT__SAVE_ADV_MARKS, $presetId);
        if ($enabled != 'Y')
            return;

        $marks = self::filter($presetId, self::getValue());

        foreach ($marks as $markCode => $markValue)
        {
            $markValue = trim($markValue);
            if (!strlen($markValue))
                continue;

            $_SESSION[$markCode] = $markValue;
        }
    }

    /**
     * @param      $presetId
     * @param      $restType
     * @param bool $disabled
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getInputs($presetId, $restType, $disabled = false)
    {
        $advMarks   = self::getFilter($presetId);
        $inputs     = [];

        foreach ($advMarks as $advMark){

            if (!strlen($advMark))
                continue;

            $template   = AdditionalParam\AdvMarks::getTemplate();
            $type       = $template . $advMark . '_';
            $label      = AdditionalParam\AdvMarks::getLabel(null, $advMark);
            $postInput  = '<small style="color: #777">' . Placeholder::build($template . $advMark) . '<small>';

            $inputArray = TabField::createInputArrayByType($type, $restType, $label, $disabled, $postInput);

            if (is_array($inputArray)) $inputs[] = $inputArray;
        }

        // add 'all adv marks'
        $type   = self::getTemplate();
        $label  = self::getLabel();

        $inputArray = TabField::createInputArrayByType(
            $type,
            $restType,
            $label ?: self::getName(),
            $disabled,
            '<small style="color: #777">' . Placeholder::build(AdditionalParam\AdvMarks::getName()) . '</small>'
        );

        if (is_array($inputArray)) $inputs[] = $inputArray;

        return $inputs;
    }
}