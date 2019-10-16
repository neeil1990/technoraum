<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 08.01.2016
 * Time: 18:35
 *
 * @author Pavel Shulaev (http://rover-it.me)
 */

namespace Rover\Fadmin;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main;
use \Bitrix\Main\ArgumentNullException;
use \Rover\Fadmin\Inputs\Input;
use \Bitrix\Main\Application;
use Rover\Fadmin\Inputs\Tabcontrol;
use Rover\Fadmin\Options\Cache;
use \Rover\Fadmin\Options\Message;
use \Rover\Fadmin\Options\Settings;
use \Rover\Fadmin\Options\Event;
use \Rover\Fadmin\Options\TabMap;
use \Rover\Fadmin\Options\Preset;

Loc::LoadMessages(__FILE__);

/**
 * Class Options
 *
 * @package Rover\Fadmin
 * @author  Pavel Shulaev (https://rover-it.me)
 */
abstract class Options
{
    /** @deprecated  */
    const SEPARATOR = '__';

	/** @var string */
	protected $moduleId;

    /** @var string */
    protected static $curSiteId;

    /**
     * @var TabMap
     * @deprecated
     */
	public $tabMap;

	/** @var Tabcontrol */
	public $tabControl;

	/** @var Message */
	public $message;

	/** @var Settings */
	public $settings;

	/** @var Event */
	public $event;

	/** @var Preset */
	public $preset;

    /** @var Cache */
	public $cache;

	/** @var array */
	protected static $instances = array();

    /**
     * @param $moduleId
     * @throws Main\ArgumentNullException
     */
    protected function __construct($moduleId)
    {
        if (!strlen($moduleId))
            throw new ArgumentNullException('moduleId');

        $this->moduleId = $moduleId;

        $this->message  = new Message();
        $this->event    = new Event($this);
        $this->preset   = new Preset($this);
        $this->tabMap   = new TabMap($this);
        $this->settings = new Settings($this);
        $this->cache    = new Cache($this);
    }

    /**
     * @param        $name
     * @param null   $default
     * @param string $presetId
     * @param string $siteId
     * @return string
     * @throws ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDirectValue($name, $default = null, $presetId = '', $siteId = '')
    {
        return Main\Config\Option::get($this->moduleId, Input::getFullPath($name, $presetId, $siteId), $default);
    }

    /**
     * @param $moduleId
     * @return mixed
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getInstance($moduleId)
	{
		if (!isset(self::$instances[$moduleId]))
			self::$instances[$moduleId] = new static($moduleId);

		return self::$instances[$moduleId];
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return mixed|null
	 * @throws ArgumentNullException
	 * @throws Main\SystemException
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	public function __call($name, $arguments)
	{
		if (0 !== strpos($name, 'get'))
			throw new Main\SystemException('unacceptable method name');

		$name       = substr($name, 3);
		$isPreset   = (0 === strpos($name, 'Preset'));

		if ($isPreset)
			$name = substr($name, 6);

		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $name, $matches);
		$ret = $matches[0];
		foreach ($ret as &$match)
			$match = strtoupper($match);

		$constName = 'static::OPTION__' . implode('_', $ret);

		if (!defined($constName))
			throw new Main\SystemException('option "' . $constName . '" not found');

		return $isPreset
			? $this->getPresetValue(constant($constName), $arguments[0], $arguments[1], $arguments[2])
			: $this->getNormalValue(constant($constName), $arguments[0], $arguments[1]);
	}

    /**
     * @param bool $reload
     * @return Tabcontrol
     * @throws ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getTabControl($reload = false)
    {
        if (is_null($this->tabControl) || $reload) {
            $config = $this->getConfigCache($reload);
            $tabs   = isset($config['tabs'])? $config['tabs'] : array();
            $this->tabControl = new Tabcontrol($tabs, $this);
        }

        return $this->tabControl;
    }

    /**
     * @param       $name
     * @param array $params
     * @return bool
     * @throws ArgumentNullException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     * @deprecated
     */
	public function runEventOldStyle($name, &$params = array())
	{
		if (!method_exists($this, $name))
			return true;

		try{
			return $this->$name($params);
		} catch (\Exception $e) {
		    $this->handleException($e);

			return false;
		}
	}

    /**
     * @param \Exception $e
     * @throws ArgumentNullException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function handleException(\Exception $e)
    {
        $this->message->addError($e->getMessage());

        if ($this->settings->getLogErrors())
            Application::getInstance()->getExceptionHandler()->writeToLog($e);
    }

    /**
     * @param bool $reload
     * @return null
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getConfigCache($reload = false)
    {
        if (!$this->cache->check('config', 'config') || $reload)
            $this->cache->set('config', $this->getConfig(), 'config');

        return $this->cache->get('config', 'config');
    }

	/**
	 * @return mixed
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	public function getModuleId()
	{
		return $this->moduleId;
	}

    /**
     * @param        $name
     * @param string $presetId
     * @param string $siteId
     * @return string
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     * @deprecated
     */
	public static function getFullName($name, $presetId = '', $siteId = '')
	{
	    return Input::getFullPath($name, $presetId, $siteId);
	}

    /**
     * @param        $inputName
     * @param        $presetId
     * @param string $siteId
     * @param bool   $reload
     * @return mixed
     * @throws ArgumentNullException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getPresetValue($inputName, $presetId, $siteId = '', $reload = false)
	{
		if (is_null($presetId))
			throw new ArgumentNullException('presetId');

		return $this->getValue($inputName, $presetId, $siteId, $reload);
	}

    /**
     * @param        $inputName
     * @param string $siteId
     * @param bool   $reload
     * @return mixed
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getNormalValue($inputName, $siteId = '', $reload = false)
	{
		return $this->getValue($inputName, '', $siteId, $reload);
	}

	/**
	 * @param            $inputName
	 * @param string     $presetId
	 * @param string     $siteId
	 * @param bool|false $reload
	 * @return mixed
	 * @throws Main\SystemException
	 * @author Pavel Shulaev (http://rover-it.me)
	 */
	public function getValue($inputName, $presetId = '', $siteId = '', $reload = false)
	{
		if (is_null($inputName))
			throw new ArgumentNullException('inputName');

		$key = md5($inputName . $presetId . $siteId);

		if (!$this->cache->check($key) || $reload) {
            $input = $this->getTabControl()->searchOneByName($inputName, $presetId, $siteId);

            if (false === $input instanceof Input)
                throw new Main\SystemException('input "' . $inputName . '" not found');

            $this->cache->set($key, $input->getValue());
		}

		return $this->cache->get($key);
	}

    /**
     * @param        $inputName
     * @param        $value
     * @param string $presetId
     * @param string $siteId
     * @throws ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function setValue($inputName, $value, $presetId = '', $siteId = '')
    {
        if (is_null($inputName))
            throw new ArgumentNullException('inputName');

        $input = $this->getTabControl()->searchOneByName($inputName, $presetId, $siteId);

        if (false === $input instanceof Input)
            throw new Main\SystemException('input "' . $inputName . '" not found');

        $input->setValue($value);
    }

    /**
     * @param        $inputName
     * @param string $presetId
     * @param string $siteId
     * @return mixed
     * @throws ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getDefaultValue($inputName, $presetId = '', $siteId = '')
	{
		$input = $this->getTabControl()->searchOneByName($inputName, $presetId, $siteId);

		if ($input instanceof Input)
			return $input->getDefault();

		throw new Main\SystemException('input ' . $inputName . ' not found');
	}

    /**
     * @param        $moduleId
     * @param        $name
     * @param string $presetId
     * @param string $siteId
     * @param null   $default
     * @return string
     * @throws ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getValueStatic($moduleId, $name, $presetId = '', $siteId = '', $default = null)
    {
        $moduleId = trim($moduleId);
        if (!strlen($moduleId))
            throw new ArgumentNullException('moduleId');

        $name = trim($name);
        if (!strlen($name))
            throw new ArgumentNullException('name');

        $params = array(
            'name'      => $name,
            'default'   => $default
        );

        return Input::getValueStatic($params, $moduleId, $presetId, $siteId);
    }

    /**
     * @return Preset
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getPreset()
    {
        return $this->preset;
    }

    /**
     * @return bool|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getCurSiteId()
    {
        if (empty(self::$curSiteId)) {
            require_once(Application::getDocumentRoot() . "/bitrix/modules/main/include/mainpage.php");
            $mainPage = new \CMainPage();
            self::$curSiteId = $mainPage->GetSiteByHost();
        }

        return self::$curSiteId;
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getConfig();
}