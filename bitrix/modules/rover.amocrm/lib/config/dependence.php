<?php
namespace Rover\AmoCRM\Config;
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.12.2016
 * Time: 18:30
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
/**
 * Class Dependence
 *
 * @package Rover\AmoCRM\Config
 * @author  Pavel Shulaev (https://rover-it.me)
 */

class Dependence
{
	const MIN_VERSION__FADMIN   = '1.8.16';
	const MIN_VERSION__PARAMS   = '0.9.3';
	const MIN_VERSION__MAIN     = '16.0.0';
    const MIN_VERSION__PHP      = 50600;

	/** @var array */
	protected $errors;

    /** @var bool */
	protected $result;

    /**
     * Dependence constructor.
     */
	public function __construct()
	{
		$this->reset();
	}

	/**
	 * @return mixed
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * @param $error
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	protected function addError($error)
	{
        $this->errors[] = trim($error);
        $this->result   = false;
	}

    /**
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function checkTrialElapsedDays()
    {
        $this->reset();

        if (self::isDemo())
            $this->addError(Loc::getMessage('rover-acrm__is_trial'));

        return $this;
    }

    /**
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function checkTrialExpired()
    {
        if (self::isDemoExpired())
            $this->addError(Loc::getMessage('rover-acrm__trial_expired'));

        return $this;
    }

    /**
     * @return bool
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function isDemo()
    {
        return Loader::includeSharewareModule('rover.amocrm') == Loader::MODULE_DEMO;
    }

    /**
     * @return bool
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function isDemoExpired()
    {
        return Loader::includeSharewareModule('rover.amocrm') == Loader::MODULE_DEMO_EXPIRED;
    }

	/**
	 * @return $this
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function checkPhpVer()
	{
        if (PHP_VERSION_ID < self::MIN_VERSION__PHP)
            $this->addError(Loc::getMessage('rover_acrm__php_version_error', array(
                '#min_php_version#' => self::MIN_VERSION__PHP
            )));

		return $this;
	}

	/**
	 * @return $this
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function checkCurl()
	{
		if (!function_exists('curl_init'))
			$this->addError(Loc::getMessage('rover_acrm__no_curl_error'));

		return $this;
	}

	/**
	 * @return $this
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function checkMainVer()
	{
		if (!CheckVersion(self::getVersion('main'), self::MIN_VERSION__MAIN))
			$this->addError(Loc::getMessage('rover-acrm__main-version-error'));

		return $this;
	}

	/**
	 * @return $this
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function checkFadminVer()
	{
		if (!ModuleManager::isModuleInstalled('rover.fadmin'))
			$this->addError(Loc::getMessage('rover_acrm__rover-fadmin_not_found'));
		elseif (!CheckVersion(self::getVersion('rover.fadmin'), self::MIN_VERSION__FADMIN))
			$this->addError(Loc::getMessage('rover-acrm__fadmin-version-error'));

		return $this;
	}

	/**
	 * @return $this
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function checkParamsVer()
	{
		if (!ModuleManager::isModuleInstalled('rover.params'))
			$this->addError(Loc::getMessage('rover_acrm__rover-params_not_found'));
		elseif (!CheckVersion(self::getVersion('rover.params'), self::MIN_VERSION__PARAMS))
			$this->addError(Loc::getMessage('rover-acrm__params-version-error'));

		return $this;
	}

    /**
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function checkCookieDir()
    {
        return self::checkDir(Application::getDocumentRoot() . '/upload/rover.amocrm/');
    }

    /**
     * @param $dir
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function checkExists($dir)
    {
        if (!file_exists($dir) && !mkdir($dir))
            $this->addError(Loc::getMessage('rover-acrm__mkdir-error', array('#dir#' => $dir)));

        return $this;
    }

    /**
     * @param $path
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function checkWritable($path)
    {
        if (!is_writable($path))
            $this->addError(Loc::getMessage('rover-acrm__writable-error', array('#path#' => $path)));

        return $this;
    }

    /**
     * @param $dir
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function checkDir($dir)
    {
        return $this->checkExists($dir)
            ->checkWritable($dir);
    }

	/**
	 * @return $this
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function checkBase()
	{
		$this->reset();

		return $this
			->checkPhpVer()
			->checkMainVer()
			->checkCurl()
			->checkFadminVer()
			->checkParamsVer();
	}

    /**
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function checkCritical()
    {
        $this->reset();

        return $this
            ->checkPhpVer()
            ->checkMainVer();
    }

	/**
	 * @param $moduleName
	 * @return bool|string
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function getVersion($moduleName)
	{
		$moduleName = preg_replace("/[^a-zA-Z0-9_.]+/i", "", trim($moduleName));
		if ($moduleName == '')
			return false;

		if (!ModuleManager::isModuleInstalled($moduleName))
			return false;

		if ($moduleName == 'main')
		{
			if (!defined("SM_VERSION"))
				include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/version.php");

			return SM_VERSION;
		}

		$modulePath = getLocalPath("modules/".$moduleName."/install/version.php");
		if ($modulePath === false)
			return false;

		$arModuleVersion = array();
		include($_SERVER["DOCUMENT_ROOT"] . $modulePath);

		return array_key_exists("VERSION", $arModuleVersion)
			? $arModuleVersion["VERSION"]
			: false;
	}

    /**
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function reset()
	{
		$this->errors = array();
		$this->result = true;

		return $this;
	}

	/**
	 * @return array
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}