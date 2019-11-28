<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.03.2017
 * Time: 20:56
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model\Rest;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Helper\Event;
use Rover\AmoCRM\Model\Rest;

Loc::loadMessages(__FILE__);
/**
 * Class Task
 *
 * @package Rover\AmoCRM\Model\Rest
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Task extends Rest
{
	const NAME  = 'tasks';
	const URL   = '/api/v2/' . self::NAME;

    /**
     * @param $data
     * @return null
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function add($data)
	{
		if (empty($data['element_id']))
			throw new ArgumentNullException('element_id');

		if (empty($data['element_type']))
			throw new ArgumentNullException('element_type');

		if (!in_array($data['element_type'], $this->getElementsTypes())
            || $data['element_type'] == self::ELEMENT_TYPE__TASK)
			throw new ArgumentOutOfRangeException('element_type');

		if (empty($data['task_type']))
			throw new ArgumentNullException('task_type');

		if (!in_array($data['task_type'], $this->getTypesIds()))
            throw new ArgumentOutOfRangeException('task_type');

		if (empty($data['responsible_user_id']))
			throw new ArgumentNullException('responsible_user_id');

		if (empty($data['text']))
			throw new ArgumentNullException('text');

		if (empty($data['complete_till_at']))
			$data['complete_till_at'] = strtotime("tomorrow") - 1;

		$request = array('add' => array($data));

        $eventParams = Event::run('beforeTaskAdd', self::URL, $request);
        if ($eventParams === false)
            return null;

        $eventUrl       = $eventParams[0];
        $eventRequest   = $eventParams[1];

		$this->requestPost($eventUrl, $eventRequest);
        Event::run('afterTaskAdd', $eventUrl, $eventRequest, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        return isset($this->response['_embedded']['items'][0]['id'])
            ? $this->response['_embedded']['items'][0]['id']
            : null;
	}

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getTypesIds()
    {
        $taskTypes = Account::getInstance()->getTaskTypes();
        if (!is_array($taskTypes) || empty($taskTypes))
            return [];

        return array_keys($taskTypes);
    }
}