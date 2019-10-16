<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 20.02.2016
 * Time: 17:57
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
namespace Rover\AmoCRM\Model\Rest;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Helper\Event;
use Rover\AmoCRM\Model\Rest;
/**
 * Class Contact
 *
 * @package Rover\AmoCRM\Model\Rest
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Contact extends Rest
{
    const NAME  = 'contacts';
	const URL   = '/api/v2/' . self::NAME;

    const FIELD__NAME   = 'name';

    /**
     * @param $data
     * @return null
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function add($data)
	{
	    if (!isset($data['name']))
            throw new ArgumentNullException('name');

		$request = array('add' => array($data));

		$eventParams = Event::run('beforeContactAdd', static::URL, $request);
		if ($eventParams === false)
		    return null;

		$eventUrl       = $eventParams[0];
		$eventRequest   = $eventParams[1];

		$this->requestPost($eventUrl, $eventRequest);
        Event::run('afterContactAdd', $eventUrl, $eventRequest, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        return (isset($this->response['_embedded']['items'][0]['id']))
            ? $this->response['_embedded']['items'][0]['id']
            : null;
	}

    /**
     * @param $id
     * @param $data
     * @return null
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function update($id, $data)
	{
		$id = intval($id);
		if (!$id)
			throw new ArgumentNullException('id');

		$data['id']         = $id;
		$data['updated_at'] = time();

		$request = ['update' => [$data]];

        $eventParams = Event::run('beforeContactUpdate', static::URL, $request);
        if ($eventParams === false)
            return null;

        $eventUrl       = $eventParams[0];
        $eventRequest   = $eventParams[1];

		$this->requestPost($eventUrl, $eventRequest);
        Event::run('afterContactUpdate', $eventUrl, $eventRequest, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        return (isset($this->response['_embedded']['items'][0]['id']))
            ? $this->response['_embedded']['items'][0]['id']
            : null;
	}

    /**
     * @param array $data
     * @param array $select
     * @return null
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getList(array $data = array(), array $select = array())
	{
        if (false === Event::run('beforeContactGetList', static::URL, $data, $select))
            return null;

        $eventParams = Event::run('beforeContactGetList', static::URL, $data, $select);
        if ($eventParams === false)
            return null;

        $eventUrl       = $eventParams[0];
        $eventData      = $eventParams[1];
        $eventSelect    = $eventParams[2];

		$this->requestGet($eventUrl, $eventData);
        Event::run('afterContactGetList', $eventUrl, $eventData, $eventSelect, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        $items = (isset($this->response['_embedded']['items']))
            ? $this->response['_embedded']['items']
            : null;

        return self::filterSelectedFields($items, $eventSelect);
	}

    /**
     * @param $id
     * @return array|null
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getById($id)
	{
        $id = intval($id);
		if (!$id)
            throw new ArgumentNullException('id');

        return reset(static::getList(array('id' => $id)));
	}

    /**
     * @param array $filter
     * @param array $select
     * @return array
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getAll(array $filter = array(), array $select = array())
	{
		$page       = 0;
		$result     = array();

		do {
			$page++;
            $listFilter = array_merge(
                $filter,
                array(
                    'limit_rows'    => static::LIMIT__DEFAULT,
                    'limit_offset'  => ($page - 1) *  static::LIMIT__DEFAULT
                )
            );

			$list   = static::getList($listFilter, $select);
			$result = array_merge($result, $list);

		} while (count($list) >= static::LIMIT__DEFAULT);

		return $result;
	}

    /**
     * @return null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getCustomFields()
	{
	    return Account::getInstance()->getCustomFields(self::NAME);
	}

    /**
     * @param $id
     * @return null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getLeadsIds($id)
    {
        $id = intval($id);
        if (!$id)
            throw new ArgumentNullException('id');

        $contact = $this->getById($id);

        return (isset($contact[Lead::NAME]['id']))
            ? $contact[Lead::NAME]['id']
            : null;
    }
}