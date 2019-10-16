<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 23.02.2017
 * Time: 0:56
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Config\Preset;
use Rover\AmoCRM\Config\TabField;
use Rover\AmoCRM\Config\TabList;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Helper\Placeholder;
use Rover\AmoCRM\Model;
use Rover\Fadmin\Inputs\Tab;
use \Bitrix\Main\Loader;
use Rover\AmoCRM\Config\Options;

Loc::loadMessages(__FILE__);
Loc::loadMessages(dirname(__FILE__) . '/../config/options.php'); // for default lead's name
/**
 * Class Model
 *
 * @package Rover\AmoCRM\Params
 * @author  Pavel Shulaev (https://rover-it.me)
 */
abstract class Source
{
    const TYPE__FORM    = 'form';
    const TYPE__EVENT   = 'event';

    const INPUT__TYPE   = 'source_type';
    const INPUT__ID     = 'source_id';

	/** @var */
	protected $id;

	/** @var Tab */
	protected $tab;

    /** @var Model\Source */
    public $model;

	/** @var array */
	protected static $instances = array();

	/** @var string */
	public static $module = '';

	/** @var string */
	public static $type = '';

    /** @var string */
	protected static $namePlaceholder;

    /** @var */
	protected $data;

	/** @var array */
	public static $classes = array(
		self::TYPE__FORM  => '\Rover\AmoCRM\Entity\Source\WebForm',
		self::TYPE__EVENT => '\Rover\AmoCRM\Entity\Source\PostEvent',
    );

    /** @var array */
	public static $types = array(
        self::TYPE__FORM,
        self::TYPE__EVENT,
    );

	/**
	 * @param $id
	 * @throws ArgumentNullException
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function __construct($id)
	{
		if (strlen(static::$module) && !Loader::includeModule(static::$module))
			throw new SystemException('module ' . static::$module . ' not found');

		$id = trim($id);
		if (!strlen($id))
			throw new ArgumentNullException('id');

		$this->id       = $id;
		$this->model    = Model\Source::getInstance($this);
	}

	private function __clone() {}
	private function __wakeup() {}

	/**
	 * @param $id
	 * @return mixed
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function getInstance($id)
	{
		return self::build(static::$type, $id);
	}

    /**
     * @param Tab $tab
     * @return Source
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function buildFromTab(Tab $tab)
    {
        if (!$tab->isPreset())
            throw new ArgumentOutOfRangeException('tab');

        return self::buildByPresetId($tab->getPresetId());
    }

    /**
     * @param $presetId
     * @return Source
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function buildByPresetId($presetId)
    {
        $presetId = intval($presetId);
        if (!$presetId)
            throw new ArgumentNullException('presetId');

        $preset = Preset::getById($presetId);

        if (empty($preset))
            try{
                throw new ArgumentOutOfRangeException('presetId');
            } catch (\Exception $e) {
                Options::load()->handleError($e);
            }

        return self::build($preset['type'], $preset['id']);
    }

	/**
	 * @param $type
	 * @param $id
	 * @return Source
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function build($type, $id)
	{
        $type = trim($type);
        if (!strlen($type))
            throw new ArgumentNullException('type');

		$id = trim($id);
		if (!strlen($id))
			throw new ArgumentNullException('id');

        $className = self::$classes[$type];

		if (!strlen($className) || !(class_exists($className)))
			throw new ArgumentOutOfRangeException('type');

		if (!isset(self::$instances[$className][$id])){
			$instance = new $className($id);
			if (!$instance instanceof Source)
				throw new ArgumentOutOfRangeException('instance');

			self::$instances[$className][$id] = $instance;
		}

		return self::$instances[$className][$id];
	}

	/**
	 * @param string $type
	 * @return Source
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 * @throws SystemException
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function buildFromRequest($type = '')
	{
	    $request = Application::getInstance()->getContext()->getRequest();

	    $type = trim($type);
		$type = strlen($type)
            ? $type
            : ($request->get(self::INPUT__TYPE) ? : static::$type);

		if (!strlen($type))
			throw new ArgumentNullException('type');

		$id = intval(Options::getRequest()->get(self::INPUT__ID));
		if (!$id)
			throw new SystemException(Loc::getMessage('rover-acrm__' . $type . '-not-send'));

		return self::build($type, $id);
	}

	/**
	 * @return int
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function getType()
	{
		return static::$type;
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
	public function getTypeLabel()
    {
        return Loc::getMessage('rover-acrm__' . static::getType() . '-label');
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getEditUrl();

    /**
     * @param bool $reload
     * @return mixed|null|Tab
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getTab($reload = false)
	{
		if (is_null($this->tab)) {

			$tab = Options::load()->getTabControl()->getTabByPresetId(static::getPresetId(), '', $reload);
			if (false === $tab instanceof Tab)
				throw new ArgumentOutOfRangeException('tab');

			$this->tab = $tab;
		}

		return $this->tab;
	}

    /**
     * @param bool $reload
     * @return mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getLeadName($reload = false)
	{
		$tab    = $this->getTab($reload);
		$name   = $tab->getInputValue(Tabs::INPUT__LEAD_NAME);
		if (empty($name))
			$name = Loc::getMessage(Tabs::INPUT__LEAD_NAME . '_default_' . $this->getType());

		return str_replace(static::$namePlaceholder, $this->getName(), $name);
	}

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getClassName()
    {
        return get_called_class();
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function isPresetExists()
    {
        return (bool)static::getPresetId();
    }

    /**
     * @return int|null|string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getPresetId()
    {
        return Preset::getIdByEntityId(static::getId(), static::$type);
    }

    /**
     * @param $restType
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getInputs($restType)
    {
        $labels = $this->model->getLabels(true);
        $result = array();

        foreach ($labels as $questionId => $questionLabel)
            $result[] = Tabs::getInputSelectCustomFields(
                $restType,
                $this->model->getFullFieldName($restType, $questionId),
                $questionLabel ?: $questionId,
                '',
                '<small style="color: #777">' . Placeholder::sourceBuild($this, $questionId) . '</small>',
                false
            );

        return $result;
    }

    /**
     * @param $siteId
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function isEnabled($siteId)
    {
        return $this->isPresetEnabled()
            && $this->isSiteEnabled($siteId);
    }

    /**
     * @return bool|mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function isPresetEnabled()
    {
        $presetId = $this->getPresetId();

        if (!$presetId)
            return false;

        // check preset enabled
        return Options::load()->getIsPresetEnabled($presetId);
    }

    /**
     * @param      $siteId
     * @param bool $reload
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function isSiteEnabled($siteId, $reload = false)
    {
        $siteId = trim($siteId);
        if (!strlen($siteId))
            throw new ArgumentNullException('siteId');

        $sitesIds = $this->getTab($reload)->getInputValue(Tabs::INPUT__SITES);
        if (empty($sitesIds))
            return true;

        if (!is_array($sitesIds))
            $sitesIds = array($sitesIds);

        return in_array($siteId, $sitesIds);
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function loadData();

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getName();

    /**
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     * @return array|null
     */
    public static function getTypes()
    {
        throw new NotImplementedException();
    }
}