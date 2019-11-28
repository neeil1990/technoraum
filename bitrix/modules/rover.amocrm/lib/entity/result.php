<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 04.09.2017
 * Time: 17:37
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Entity;

use Bitrix\Main\ArgumentOutOfRangeException;
use Rover\AmoCRM\Helper\Event;
use \Rover\AmoCRM\Model\AdditionalParam;

/**
 * Class Value
 *
 * @package Rover\AmoCRM\Entity\Source
 * @author  Pavel Shulaev (https://rover-it.me)
 */
abstract class Result
{
    const FIELD__SITE_ID    = 'site_id';

    /** @var */
    protected $eventParams;

    /** @var array */
    protected $additionalParams;

    /** @var */
    protected $data;

    /** @var */
    public $source;

    /**
     * Result constructor.
     *
     * @param Source $source
     * @param        $eventParams
     * @param        $additionalParams
     */
    public function __construct(Source $source, $eventParams, $additionalParams)
    {
        $this->source           = $source;
        $this->eventParams      = $eventParams;
        $this->additionalParams = $additionalParams;
    }

    /**
     * @param Source $source
     * @param        $eventParams
     * @param        $additionalParams
     * @return mixed
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function build(Source $source, $eventParams, $additionalParams)
    {
        $className = str_replace('\\Source\\', "\\Result\\", $source->getClassName());
        if (!class_exists($className))
            throw new ArgumentOutOfRangeException('resultEntityClassName');

        Event::run('beforeBuildResult', $source, $eventParams, $additionalParams);
        $result = new $className($source, $eventParams, $additionalParams);
        Event::run('afterBuildResult', $source, $eventParams, $additionalParams, $result);

        return $result;
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getEventParams()
    {
        return $this->eventParams;
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getAdditionalParams()
    {
        return $this->additionalParams;
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getPageUrl()
    {
        return $this->additionalParams[AdditionalParam::PARAM__PAGE_URL];
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getMarks()
    {
        return $this->additionalParams[AdditionalParam::PARAM__MARKS];
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getVisitorUid()
    {
        return $this->additionalParams[AdditionalParam::PARAM__VISITOR_UID];
    }

    /**
     * @return mixed|null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getSiteId()
    {
        if (!isset($this->additionalParams[self::FIELD__SITE_ID]))
            $this->loadSiteId();

        return isset($this->additionalParams[self::FIELD__SITE_ID])
            ? $this->additionalParams[self::FIELD__SITE_ID]
            : null;
    }

    /**
     * @param bool $reload
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getData($reload = false)
    {
        if (!$this->data || $reload)
            $this->data = $this->loadData();

        return $this->data;
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getCacheId()
    {
        return md5(serialize($this->getEventParams())
            . serialize($this->getAdditionalParams()));
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function loadSiteId();

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function loadData();
}