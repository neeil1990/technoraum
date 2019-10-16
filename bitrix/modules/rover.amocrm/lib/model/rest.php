<?php
namespace Rover\AmoCRM\Model;
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 20.02.2016
 * Time: 14:36
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Config\Dependence;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Config\Tabs;
use \Bitrix\Main\Web\Json;
use Rover\AmoCRM\Helper\CustomField;
use Rover\Fadmin\Inputs\Input;
use Rover\AmoCRM\Helper\Event;

Loc::LoadMessages(__FILE__);

/**
 * Class Rest
 *
 * @package Rover\AmoCRM\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Rest
{
    const WAIT__MKSEK = 200000;

    const URL__AUTH     = '/private/api/auth.php?type=json';
    
    const METHOD__POST  = 'post';
    const METHOD__GET   = 'get';

    const ELEMENT_TYPE__CONTACT = 1;
    const ELEMENT_TYPE__LEAD    = 2;
    const ELEMENT_TYPE__COMPANY = 3;
    const ELEMENT_TYPE__TASK    = 4;
    const ELEMENT_TYPE__BUYER   = 12;

    const LIMIT__DEFAULT = 500;

	/** @var string */
	protected $subDomain;

	/** @var string */
	protected $login;

	/** @var string */
	protected $hash;

    /** @var int */
	protected $code;

    /** @var string */
	protected $lastError;

    /** @var boolean */
	protected $success;

    /** @var array */
	protected $response;

    /** @var bool */
    protected static $auth = false;

    /** @var array */
    protected static $instances = array();

    /** @var array */
    protected static $typesCache;

    /** @var array */
    protected static $elementsTypesCache;

    /**
	 * @param $subDomain
	 * @param $login
	 * @param $hash
	 * @throws SystemException
	 */
	private function __construct($subDomain, $login, $hash)
	{
		$errors     = array();
		$subDomain  = trim($subDomain);

		if (!strlen($subDomain))
			$errors[] = Loc::getMessage('rover-acrm__no-sub-domain');
		elseif (preg_match('/[^a-zA-Z0-9_-]/u', $subDomain))
			$errors[] = Loc::getMessage('rover-acrm__sub-domain-incorrect');

		$login = trim($login);
		if (!strlen($login))
			$errors[] = Loc::getMessage('rover-acrm__no-login');

		$hash = trim($hash);
		if (!strlen($hash))
			$errors[] = Loc::getMessage('rover-acrm__no-nash');

		if (count($errors))
			throw new SystemException(implode("\n", $errors));

		$this->subDomain    = $subDomain;
		$this->login        = $login;
		$this->hash         = $hash;
	}

	private function __clone() {}
	private function __wakeup() {}

	/**
	 * @param bool|false $reload
	 * @return Rest|static
	 * @throws ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function getInstance($reload = false)
	{
		return self::build(static::getClassName(), $reload);
	}

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getClassName()
    {
        return get_called_class();
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getType()
    {
        $className = static::getClassName();

        return strtolower(substr($className, strrpos($className, '\\') + 1));
    }

    /**
     * @param      $type
     * @param bool $reload
     * @return mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function buildByType($type, $reload = false)
    {
        $type = trim($type);
        if (!$type)
            throw new ArgumentNullException('type');

        $types = self::getTypes();

        if (!isset($types[$type]))
            throw new ArgumentOutOfRangeException('type');

        return self::build($types[$type], $reload);
    }

    /**
     * @param bool $reload
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getTypes($reload = false)
    {
        if (is_null(self::$typesCache) || $reload) {
            self::$typesCache    = array();
            $patches        = glob(dirname(__FILE__) . '/rest/*.php');
            $patchesCount   = count($patches);

            for ($i = 0; $i < $patchesCount; ++$i) {
                /** @var Rest $className */
                $className = __CLASS__ . '\\' . ucfirst(basename($patches[$i], ".php"));
                self::$typesCache[$className::getType()] = $className;
            }
        }

        return self::$typesCache;
    }

    /**
     * @param      $className
     * @param bool $reload
     * @return mixed
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function build($className, $reload = false)
	{
		if (!strlen($className) || !(class_exists($className)))
			throw new ArgumentOutOfRangeException('className');

		if (!isset(self::$instances[$className]) || $reload){

			$subDomain  = Input::getValueStatic(Tabs::getInputSubDomain(), Options::MODULE_ID);
			$email      = Option::get(Options::MODULE_ID, Tabs::INPUT__LOGIN);
			$hash       = Option::get(Options::MODULE_ID, Tabs::INPUT__HASH);

			$restObject = new $className($subDomain, $email, $hash);
			if (!$restObject instanceof Rest)
				throw new ArgumentOutOfRangeException('instance');

			self::$instances[$className] = $restObject;
		}

		return self::$instances[$className];
	}

    /**
     * @param       $url
     * @param array $data
     * @return Rest
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function requestPost($url, array $data = array())
    {
        return $this->request(self::METHOD__POST, $url, $data);
    }

    /**
     * @param       $url
     * @param array $data
     * @return Rest
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function requestGet($url, array $data = array())
    {
        return $this->request(self::METHOD__GET, $url, $data);
    }

    /**
     * @param       $method
     * @param       $url
     * @param array $data
     * @return $this
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	private function request($method, $url, array $data = array())
	{
	    // auth if needed
		if (!$this->isAuth() && ($url != self::URL__AUTH)) $this->auth();

		$url = 'https://' . $this->subDomain . '.amocrm.ru' . $url;

		if (($method == self::METHOD__GET) && !empty($data)) {
            $url .= (strpos($url, '?') === false) ? '?' : '&';
            $url .= http_build_query($data);
        }

        Event::run('beforeRestRequest', $method, $url, $data);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "AmoCRM-API-client/2.0");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

		if ($method == self::METHOD__POST) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		}

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_COOKIEFILE, self::getCookiePath());
		curl_setopt($ch, CURLOPT_COOKIEJAR, self::getCookiePath());

		// for ssl
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);

		$response   = curl_exec($ch);
		$this->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // wait
        usleep(self::WAIT__MKSEK);

        try{
            $this->response = strlen($response) ? Json::decode($response) : array();
            $this->success  = in_array($this->code, array(200, 201, 204));
            $this->lastError= $this->success
                ? ''
                : Loc::getMessage('rover-acrm__rest-error-template', array(
                    '#code#'    => intval($this->code),
                    '#message#' => Loc::getMessage('rover-acrm__server-error-' . intval($this->code))
                ));
        } catch (\Exception $e) {
            $this->response     = array();
            $this->lastError    = $e->getMessage();
            $this->success      = false;
        }

        if (!$this->success)
            self::clearCookie();

        Event::run('afterRestRequest', $method, $url, $data, $this->response, $this->code, $this->lastError);

        return $this;
	}

    /**
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function auth()
	{
		$data = array(
			'USER_LOGIN'=> $this->login,
			'USER_HASH' => $this->hash
        );

		if (false === Event::run('beforeRestAuth', self::URL__AUTH, $data)) {
            self::$auth = false;
        } else {
            $this->requestPost(self::URL__AUTH, $data);
            // @TODO: if 401 then try to reconnect
            Event::run('afterRestAuth', self::URL__AUTH, $data, $this->response, $this->code, $this->lastError);

            if (!$this->success)
                $this->handleErrorV1('auth');

            self::$auth = isset($this->response['response']['auth']) && $this->response['response']['auth'];
        }
	}

    /**
     * @param $name
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function handleError($name)
    {
        $this->_handleError($name, $this->response['status'], $this->response['detail']);
    }

    /**
     * @param $name
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     * @deprecated
     */
	protected function handleErrorV1($name)
    {
        $this->_handleError($name, $this->response['response']['error_code'], $this->response['response']['error']);
    }

    /**
     * @param $name
     * @param $code
     * @param $message
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function _handleError($name, $code, $message)
    {
        $name       = trim($name);
        $code       = trim($code);
        $message    = trim($message);

        // adding specific message
        if (strlen($name) && strlen($code))
        {
            $specificMessage = Loc::getMessage('rover-acrm__rest-' . $name . '-error-' . $code);
            if (strlen($specificMessage))
                $this->lastError .= "\n" . Loc::getMessage('rover-acrm__rest-error-specific-template', [
                        '#status#' => $specificMessage
                    ]);
        }

        // additional message
        if (strlen($code)) {
            $additionalMessage = Loc::getMessage('rover-acrm__rest-error-' . $code);
            if (strlen($additionalMessage))
                $this->lastError .= "\n" . Loc::getMessage('rover-acrm__rest-error-specific-template', [
                        '#status#' => $additionalMessage
                    ]);
        }

        // adding common message
        if (strlen($code) || strlen($message)){
            $this->lastError .= "\n" . Loc::getMessage('rover-acrm__rest-error-add-template', array(
                    '#code#'    => $code,
                    '#message#' => $message,
                ));
        }

        $this->lastError .= Loc::getMessage('rover-acrm__rest-error-codes');

        throw new SystemException($this->lastError, $this->code);
    }

	/**
	 * @return bool
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function isAuth()
	{
		return self::$auth;
	}

    /**
     * @return string
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getCookiePath()
    {
        $dir = Application::getDocumentRoot() . '/upload/rover.amocrm/';

        $dependence = new Dependence();
        if (!$dependence->checkDir($dir)->getResult())
            throw new SystemException(implode('<br>', $dependence->getErrors()));

        return $dir . 'cookie.txt';
    }

    /**
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function clearCookie()
    {
        file_put_contents(self::getCookiePath(), '');
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getElementsTypes()
    {
        if (is_null(self::$elementsTypesCache))
        {
            $class      = new \ReflectionClass(__CLASS__);
            $constants  = $class->getConstants();

            foreach ($constants as $constantName => $constantValue)
                if (strpos($constantName, 'ELEMENT_TYPE__') !== false)
                    self::$elementsTypesCache[] = $constantValue;
        }

        return self::$elementsTypesCache;
    }

    /**
     * @param       $items
     * @param array $select
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function filterSelectedFields($items, array $select = array())
    {
        if (!is_array($items))
            return $items;

        $selectCnt  = count($select);
        if (!$selectCnt)
            return $items;

        $itemsCnt   = count($items);
        $result     = array();
        for ($i = 0; $i < $itemsCnt; ++$i) {

            $item       = $items[$i];
            $resultItem = array();

            for ($j = 0; $j < $selectCnt; ++$j) {
                $selectField = $select[$j];

                if (CustomField::isCustom($selectField)) {
                    $params = CustomField::getParams($selectField);
                    if (isset($params['id']) && isset($item['custom_fields'])) {

                        $itemCFCount = count($item['custom_fields']);

                        for ($k = 0; $k < $itemCFCount; ++$k){
                            $cf = $item['custom_fields'][$k];
                            if ($cf['id'] == $params['id']){
                                $resultItem['custom_fields'][] = $cf;
                                break;
                            }
                        }
                    }

                } else {
                    $resultItem[$selectField] = array_key_exists($selectField, $item)
                        ? $item[$selectField] : null;
                }
            }

            $result[] = $resultItem;
        }

        return $result;
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getName()
    {
        return static::NAME;
    }
}