<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 20.02.2016
 * Time: 16:12
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model\Rest;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Helper\Event;
use Rover\AmoCRM\Model\Rest;

Loc::loadMessages(__FILE__);
/**
 * Class Lead
 *
 * @package Rover\AmoCRM\Model\Rest
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Lead extends Rest
{
    const NAME  = 'leads';
	const URL   = '/api/v2/' . self::NAME;

    const FIELD__NAME       = 'name';
    const FIELD__SALE       = 'sale';
    const FIELD__STATUS_ID  = 'status_id';

	/**
	 * @param $data
	 * @return mixed
	 * @throws \Bitrix\Main\SystemException
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function add($data)
	{
        if (!isset($data['name']))
            throw new ArgumentNullException('name');

		$request = array('add' => array($data));

        $eventParams = Event::run('beforeLeadAdd', self::URL, $request);
        if ($eventParams === false)
            return null;

        $eventUrl       = $eventParams[0];
        $eventRequest   = $eventParams[1];

		$this->requestPost($eventUrl, $eventRequest);
        Event::run('afterLeadAdd', $eventUrl, $eventRequest, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        return isset($this->response['_embedded']['items'][0]['id'])
            ? $this->response['_embedded']['items'][0]['id']
            : null;
	}

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     * @TODO: update to 'lead'
     */
	public static function getType()
    {
        return 'deal';
    }

    /**
     * @param $id
     * @param $data
     * @return null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function update($id, $data)
    {
        $id = intval($id);
        if (!$id)
            throw new ArgumentNullException('id');

        $data['id']         = $id;
        $data['updated_at'] = time();

        if (isset($data['loss_reason_id']))
            unset($data['loss_reason_id']);

        $request = array('update' => array($data));

        $eventParams = Event::run('beforeLeadUpdate', self::URL, $request);
        if ($eventParams === false)
            return null;

        $eventUrl       = $eventParams[0];
        $eventRequest   = $eventParams[1];

        $this->requestPost($eventUrl, $eventRequest);
        Event::run('afterLeadUpdate', $eventUrl, $eventRequest, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        return (isset($this->response['_embedded']['items'][0]['id']))
            ? $this->response['_embedded']['items'][0]['id']
            : null;
    }

    /**
     * @return array|null
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getCustomFields()
	{
	    return Account::getInstance()->getCustomFields(self::NAME);
	}

    /**
     * @param array $data
     * @param array $select
     * @return array|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getList($data = array(), array $select = array())
    {
        $eventParams = Event::run('beforeLeadGetList', self::URL, $data, $select);
        if ($eventParams === false)
            return null;

        $eventUrl       = $eventParams[0];
        $eventData      = $eventParams[1];
        $eventSelect    = $eventParams[2];

        $this->requestGet($eventUrl, $eventData);
        Event::run('afterLeadGetList', $eventUrl, $eventData, $eventSelect, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        $items = (isset($this->response['_embedded']['items']))
            ? $this->response['_embedded']['items']
            : null;

        return self::filterSelectedFields($items, $eventSelect);
    }

    /**
     * @param array $data
     * @param array $select
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getAll(array $data = array(), array $select = array())
    {
        $page       = 0;
        $result     = array();

        do {
            $page++;
            $listData = array_merge(
                $data,
                array(
                    'limit_rows'    => self::LIMIT__DEFAULT,
                    'limit_offset'  => ($page - 1) *  self::LIMIT__DEFAULT
                )
            );

            $list = $this->getList($listData, $select);
            if (is_array($list))
                $result = array_merge($result, $list);

        } while (count($list) >= self::LIMIT__DEFAULT);

        return $result;
    }

    /**
     * @param       $contactId
     * @param array $select
     * @return null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getByContactId($contactId, array $select = array())
    {
        $contactId = intval($contactId);
        if (!$contactId)
            throw new ArgumentNullException('contactId');

        $leadsIds = Contact::getInstance()->getLeadsIds($contactId);

        asort($leadsIds);

        return $this->getList(array('id' => $leadsIds), $select);
    }

    /**
     * @param array $contactsIds
     * @param array $select
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getByContactsIds(array $contactsIds, array $select = array())
    {
        $result = [];
        foreach ($contactsIds as $contactId)
            $result = array_merge($result, $this->getByContactId($contactId, $select));

        return $result;
    }

    /**
     * @param array $contactsIds
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getLeadsIdsByContactsIds(array $contactsIds)
    {
        $result = [];
        foreach ($contactsIds as $contactId)
        {
            $leadIds = Contact::getInstance()->getLeadsIds($contactId);
            if (is_array($leadIds))
                $result = array_merge($result,  $leadIds);
        }

        return array_unique($result);
    }

    /**
     * @param $id
     * @return null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getById($id)
    {
        $id = intval($id);
        if (!$id)
            throw new ArgumentNullException('id');

        return reset($this->getList(array('id' => $id)));
    }
}