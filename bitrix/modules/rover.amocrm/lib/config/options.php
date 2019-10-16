<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 10.02.2016
 * Time: 0:45
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Config;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Helper\Log;
use Rover\AmoCRM\Model\Rest\Account;
use Rover\Fadmin\Options\Settings;
use \Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Rover\AmoCRM\Model\Event;
use Rover\Fadmin\Inputs\Tab;
use \Rover\Fadmin\Options as FadminOptions;

if (!Loader::includeModule("rover.fadmin")
	|| !Loader::includeModule('rover.params'))
	throw new SystemException('rover.fadmin or rover.params modules not found');

Loc::loadMessages(__FILE__);
/**
 * Class Options
 *
 * @package Rover\AmoCRM\Config
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Options extends FadminOptions
{
	const MODULE_ID = 'rover.amocrm';

    /** @var bool|mixed */
	protected $dependenceStatus = false;

    /** @var bool */
    protected $accountId;

    /**
     * Options constructor.
     *
     * @param $moduleId
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentNullException
     */
	protected function __construct($moduleId)
	{
		$dependence = new Dependence();

		if (!$dependence->checkCritical()->getResult())
		    throw new SystemException(implode('<br>', $dependence->getResult()));

		$this->dependenceStatus = $dependence
            ->checkBase()
            ->checkCookieDir()
            ->checkTrialExpired()
            ->getResult();

		parent::__construct($moduleId);

		if (!$this->dependenceStatus){
            $this->message->addError(implode('<br>',
                $dependence->getErrors()), true);

            Log::addError('dependence error(s)', $dependence->getErrors());
        }

		// check elapsed demo-days
		if (!$dependence->checkTrialElapsedDays()->getResult())
            $this->message->addOk($dependence->getErrors(), true);
	}

	protected function __clone() {}
	protected function __wakeup() {}

    /**
     * @return array|mixed
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getConfig()
	{
		return array(
            'tabs'      => Tabs::get($this),
            'settings'  => array(
                Settings::BOOL_CHECKBOX         => true,
                Settings::SHOW_ADMIN_PRESETS    => false,
            )
        );
	}

	/**
	 * @return bool
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function getDependenceStatus()
	{
		return $this->dependenceStatus;
	}

    /**
     * @return static|static|Options|\Rover\Fadmin\Options
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     * @deprecated
     */
	public static function get()
	{
		return self::load();
	}

    /**
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function load()
    {
        return parent::getInstance(self::MODULE_ID);
    }

    /**
     * @return bool
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getUnavailableAlert()
    {
        return self::getValueStatic(self::MODULE_ID, Tabs::INPUT__UNAVAILABLE_ALERT) == 'Y';
    }

    /**
     * @param bool $reload
     * @return mixed
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function isEnabled()
	{
		return self::getValueStatic(self::MODULE_ID, Tabs::INPUT__ENABLED, '', '', 'N') == 'Y';
	}

    /**
     * @param bool $reload
     * @return mixed
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function isAgentEnabled()
    {
        return self::getValueStatic(self::MODULE_ID, Tabs::INPUT__AGENT, '', '', 'N') == 'Y';
    }

    /**
     * @return string
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getLogMaxSize()
    {
        return self::getValueStatic(self::MODULE_ID, Tabs::INPUT__LOG_MAX_SIZE);
    }

    /**
     * @return float|int
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getLogMaxSizeBytes()
    {
        $sizeMb = self::getLogMaxSize();

        return $sizeMb * 1024 * 1024;
    }

    /**
     * @return string
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getAgentHandleErrorsStatic()
    {
        return self::getValueStatic(self::MODULE_ID, Tabs::INPUT__AGENT_HANDLE_ERRORS);
    }

    /**
     * @return string
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getAgentEventsCountStatic()
    {
        return self::getValueStatic(self::MODULE_ID, Tabs::INPUT__AGENT_COUNT);
    }

    /**
     * @param      $presetId
     * @param bool $reload
     * @return mixed
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getIsPresetEnabled($presetId, $reload = false)
	{
		return $this->getPresetValue(Tabs::INPUT__CONNECTION_ENABLED, $presetId, '', $reload);
	}

    /**
     * @return \Bitrix\Main\HttpRequest
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getRequest()
	{
		return Application::getInstance()
			->getContext()
			->getRequest();
	}

    /**
     * @return array
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getPresetsIds()
    {
        return $this->preset->getIds();
    }

    /**
     * @param $siteId
     * @return array
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getPresetsIdsStatic($siteId = '')
    {
        $presets = self::getPresetsListStatic($siteId);

        return array_keys($presets);
    }

    /**
     * @param $siteId
     * @return array|mixed
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getPresetsListStatic($siteId = '')
    {
        $presets = unserialize(Option::get(self::MODULE_ID,
            FadminOptions\Preset::OPTION_ID, '', $siteId));

        if (empty($presets))
            $presets = [];

        return $presets;
    }

    /**
     * @param bool $reload
     * @return bool
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function isConnected($reload = false)
    {
        return (bool)$this->getAccountId($reload);
    }

    /**
     * @param bool $reload
     * @return bool
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getAccountId($reload = false)
    {
        if (is_null($this->accountId) || $reload)
            try{
                $this->accountId = Account::getInstance()->getId();
            } catch (\Exception $e) {
                $this->accountId = false;
                $this->handleError($e);

                if (Options::getUnavailableAlert())
                    try{
                        Event::sendUnavailable($e->getMessage());
                    } catch (\Exception $e) {
                        $this->handleError($e);
                    }
            }

        return $this->accountId;
    }

    /**
     * @param Tab $tab
     * @return bool
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getUnsortedStatus(Tab $tab)
    {
        if (!$tab->getInputValue(Tabs::INPUT__UNSORTED_CREATE))
            return false;

        $status = Account::getInstance()->isUnsortedOn();

        if (!$status)
            $tab->setInputValue(Tabs::INPUT__UNSORTED_CREATE, 'N');

        return $status;
    }

    /**
     * @param        $message
     * @param string $publicMessage
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function handleError($message, $publicMessage = '')
    {
        // add public message
        $publicMessage = trim($publicMessage);
        if (!strlen($publicMessage))
            $publicMessage = $message instanceof \Exception
                ? $message->getMessage()
                : (string)$message;

        $this->message->addError($publicMessage);

        // add log message
        $logMessage = $message instanceof \Exception
            ? $message->getMessage() . "\n" . $message->getTraceAsString()
            : (string)$message;

        Log::addError($logMessage);

        // add exception
        if ($this->settings->getLogErrors() && ($message instanceof \Exception))
            Application::getInstance()->getExceptionHandler()->writeToLog($message);
    }
}