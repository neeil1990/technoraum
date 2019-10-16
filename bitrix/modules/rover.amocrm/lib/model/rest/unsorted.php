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

use Rover\AmoCRM\Helper\Event;
use Rover\AmoCRM\Model\Rest;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
/**
 * Class Contact
 *
 * @package Rover\AmoCRM\Rest
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Unsorted extends Rest
{
	const URL__ADD          = '/api/unsorted/add/';
	const URL__LIST         = '/api/unsorted/list/';
	const URL__ALL_SUMMARY  = '/api/unsorted/get_all_summary/';

	const URL__FORM = '/api/v2/incoming_leads/form';

	const LIMIT_DEFAULT = 500;

    /**
     * @param $data
     * @return null
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function addFromForm($data)
	{
        $request = array(
			'request' => array(
				'unsorted' => array(
					'category'  => 'forms',
					'add'       => $data
                )
            )
        );

		$url = self::URL__ADD . '?api_key=' . $this->hash . '&login=' . $this->login . '&type=json';

        $eventParams = Event::run('beforeUnsortedAdd', $url, $request);
        if ($eventParams === false)
            return null;

        $eventUrl       = $eventParams[0];
        $eventRequest   = $eventParams[1];

        $this->requestPost($eventUrl, $eventRequest);
        Event::run('afterUnsortedAdd', $eventUrl, $eventRequest, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleErrorV1('unsorted');

        return ($this->response['response']['unsorted']['add']['status'] == 'success')
			? $this->response['response']['unsorted']['add']['data'][0]
            : null;
	}

	/**
	 * @param $data
	 * @return null
	 * @throws \Bitrix\Main\SystemException
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function getList(array $data = array())
	{
		if (!isset($data['page_size']) || !intval($data['page_size']))
			$data['page_size'] = self::LIMIT_DEFAULT;

		if (!isset($data['PAGEN_1']) || !intval($data['PAGEN_1']))
			$data['PAGEN_1'] = 1;

		if (!isset($data['categories']))
			$data['categories'] = array('all');

		$data['api_key']    = $this->hash;
		$data['login']      = $this->login;

        if (false === Event::run('beforeUnsortedGetList', self::URL__LIST, $data))
            return null;

        $eventParams = Event::run('beforeUnsortedGetList', self::URL__LIST, $data);
        if ($eventParams === false)
            return null;

        $eventUrl   = $eventParams[0];
        $eventData  = $eventParams[1];

        $this->requestGet($eventUrl, $eventData);
        Event::run('afterUnsortedGetList', $eventUrl, $eventData, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError('unsorted');

        return isset($this->response['response']['unsorted'])
			? $this->response['response']['unsorted']
            : null;
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \Bitrix\Main\SystemException
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public function getAllSummary(array $data = array())
	{
		$data['api_key']    = $this->hash;
		$data['login']      = $this->login;

		return $this->requestGet(self::URL__ALL_SUMMARY, $data);
	}
}