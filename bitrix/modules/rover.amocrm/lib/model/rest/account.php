<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 03.06.2018
 * Time: 20:11
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model\Rest;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Helper\Event;
use Rover\AmoCRM\Model\Rest;

Loc::loadMessages(__FILE__);

/**
 * Class Account
 *
 * @package Rover\AmoCRM\Model\Rest
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Account extends Rest
{
    const URL       = '/api/v2/account';
    const URL_OLD   = '/private/api/v2/json/accounts/current';

    /** @var array  */
    protected static $account;

    /**
     * @var array
     * @deprecated
     */
    protected static $accountOld;

    /**
     * @param array $with
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function get(array $with = array('custom_fields', 'users' , 'pipelines', 'groups', 'note_types', 'task_types'))
    {
        if (is_null(self::$account)){

            $url = self::URL . (!empty($with) ? '?with=' . implode(',', $with) : '');

            $eventResult = Event::run('beforeRestGetAccount', $url);

            if (false === $eventResult)
                return null;

            $url = $eventResult[0];

            $this->requestGet($url);
            Event::run('afterRestGetAccount', $url, $this->response, $this->code, $this->lastError);

            if (!$this->success)
                $this->handleError('account');

            self::$account = $this->response;
        }

        return self::$account;
    }

    /**
     * @return mixed|null
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getSubdomain()
    {
        $account = $this->get();

        return isset($account['subdomain']) ? $account['subdomain'] : null;
    }

    /**
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getPipelines()
    {
        $account = $this->get();

        return isset($account['_embedded']['pipelines'])
            ? $account['_embedded']['pipelines']
            : null;
    }

    /**
     * @return null
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getTaskTypes()
    {
        $account = $this->get();

        return isset($account['_embedded']['task_types'])
            ? $account['_embedded']['task_types']
            : null;
    }

    /**
     * @param null $entity
     * @return null
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getCustomFields($entity = null)
    {
        $account = $this->get();

        if (!isset($account['_embedded']['custom_fields']))
            return null;

        $entity = trim($entity);
        if (!strlen($entity))
            return $account['_embedded']['custom_fields'];

        return (isset($account['_embedded']['custom_fields'][$entity]))
            ? $account['_embedded']['custom_fields'][$entity]
            : null;
    }

    /**
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     * @deprecated
     */
    public function getOld()
    {
        if (is_null(self::$accountOld)){
            if (false === Event::run('beforeRestGetAccountOld', self::URL_OLD))
                return null;

            $this->requestGet(self::URL_OLD);
            Event::run('afterRestGetAccount', self::URL_OLD, $this->response, $this->code, $this->lastError);

            if (!$this->success)
                $this->handleErrorV1('account');

            self::$accountOld = $this->response['response']['account'];
        }

        return self::$accountOld;
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     * @todo upgrade to v2
     */
    public function isUnsortedOn()
    {
        $account = self::getOld();

        return (isset($account['unsorted_on']))
            ? $account['unsorted_on'] == 'Y'
            : null;
    }
    /**
     * @return mixed|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getId()
    {
        $account = $this->get();

        return isset($account['id']) ? $account['id'] : null;
    }

    /**
     * @return null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getUsers()
    {
        $account = $this->get();

        return (isset($account['_embedded']['users']))
            ? $account['_embedded']['users']
            : null;
    }
}