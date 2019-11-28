<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 04.09.2017
 * Time: 16:01
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Entity\Source as SourceEntity;
use Bitrix\Main\UI\PageNavigation;
/**
 * Class Map
 *
 * @package Rover\AmoCRM\Entity\Source
 * @author  Pavel Shulaev (https://rover-it.me)
 */
abstract class Source
{
    /** @var array */
    protected static $instances = array();

    /** @var SourceEntity */
    protected $source;

    /** @var array */
    protected static $prefix;

    /** @var array */
    protected $map = array();

    /** @var */
    protected $labels = array();

    /**
     * Source constructor.
     *
     * @param SourceEntity $source
     */
    private function __construct(SourceEntity $source)
    {
        $this->source = $source;
    }

    /**
     * @param SourceEntity $source
     * @return mixed
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getInstance(SourceEntity $source)
    {
        $key = md5($source->getType() . $source->getId());

        if (!isset(self::$instances[$key])){
            $modelClassName = str_replace('\\Entity\\', "\\Model\\", $source->getClassName());
            if (!class_exists($modelClassName))
                throw new ArgumentOutOfRangeException('sourceModelClassName');

            self::$instances[$key] = new $modelClassName($source);
        }

        return self::$instances[$key];
    }

    /**
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getId2Placeholder()
    {
        return array();
    }

    /**
     * @param      $amoObject
     * @param bool $reload
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getMapByObject($amoObject, $reload = false)
    {
        $this->loadObjectValues($amoObject, $reload);

        return $this->map[$amoObject];
    }

    /**
     * @param $amoObject
     * @param $valueCode
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getObjectValueByCode($amoObject, $valueCode)
    {
        $name = Options::getFullName(
            $this->getFullFieldName($amoObject, $valueCode),
            $this->source->getPresetId()
        );

        return Option::get(Options::MODULE_ID, $name);
    }

    /**
     * @param      $amoObject
     * @param      $valueCode
     * @param bool $reload
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function loadObjectValueByCode($amoObject, $valueCode, $reload = false)
    {
        if (!isset($this->map[$amoObject][$valueCode]) || $reload)
            $this->map[$amoObject][$valueCode]
                = $this->getObjectValueByCode($amoObject, $valueCode);
    }

    /**
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getLabelsCodes()
    {
        return array_keys($this->getLabels());
    }

    /**
     * @param      $labels
     * @param bool $clear
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function clearLabels($labels, $clear = false)
    {
        if (!$clear)
            return $labels;

        $clearLabels = array();
        foreach ($labels as $labelCode => $labelValue)
            $clearLabels[$labelCode] = trim(preg_replace('/(\(#[^#]*#\))/Umsi', '', $labelValue));

        return $clearLabels;
    }

    /**
     * @param $restObject
     * @param $field
     * @return string
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getFullFieldName($restObject, $field)
    {
        $prefix = static::$prefix;
        if (empty($prefix))
            throw new ArgumentNullException('prefix');

        return $prefix . ':' . $restObject . ':' . $field;
    }

    /**
     * @param $resultId
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getResultParamsById($resultId);

    /**
     * @param bool $clear
     * @param bool $reload
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getLabels($clear = false, $reload = false);

    /**
     * @param      $amoObject
     * @param bool $reload
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract protected function loadObjectValues($amoObject, $reload = false);

    /**
     * @param array               $query
     * @param PageNavigation|null $nav
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getResults(array $query = array(), PageNavigation &$nav = null);

    /**
     * @param array               $query
     * @param PageNavigation|null $nav
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getNavList(array $query = array(), PageNavigation &$nav = null);

    /**
     * @param $resultId
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getSiteIdById($resultId);

    /**
     * @param array $filter
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getResultsCount(array $filter = array());
}