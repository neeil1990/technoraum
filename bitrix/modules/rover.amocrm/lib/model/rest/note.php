<?php
namespace Rover\AmoCRM\Model\Rest;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Helper\Event;
use Rover\AmoCRM\Model\Rest;

Loc::loadMessages(__FILE__);
/**
 * Class Note
 *
 * @package Rover\AmoCRM\Model\Rest
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Note extends Rest
{
    const NAME  = 'notes';
    const URL   = '/api/v2/' . self::NAME;

    const TYPE__DEAL_CREATED    = 1;
    const TYPE__CONTACT_CREATED = 2;
    const TYPE__COMMON          = 4;
    const TYPE__TASK_RESULT     = 13;
    const TYPE__SYSTEM          = 25;

    /** @var array */
    protected static $elementTypeMap;

    /**
     * @param $data
     * @return null
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function add($data)
    {
        if (empty($data['element_id']))
            throw new ArgumentNullException('element_id');

        if (empty($data['element_type']))
            throw new ArgumentNullException('element_type');

        if (empty($data['text']))
            throw new ArgumentNullException('text');

        if (empty($data['note_type']))
            $data['note_type'] = self::TYPE__COMMON;

        if (($data['note_type'] == self::TYPE__SYSTEM)
            && (!isset($data['params'])))
            throw new ArgumentNullException('params');

        if ($data['element_type'] == self::ELEMENT_TYPE__TASK)
            $data['note_type'] = self::TYPE__TASK_RESULT;

        $request = array('add' => array($data));

        $eventParams = Event::run('beforeNoteAdd', self::URL, $request);
        if ($eventParams === false)
            return null;

        $eventUrl       = $eventParams[0];
        $eventRequest   = $eventParams[1];

        $this->requestPost($eventUrl, $eventRequest);
        Event::run('afterNoteAdd', $eventUrl, $eventRequest, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        return isset($this->response['_embedded']['items'][0]['id'])
            ? $this->response['_embedded']['items'][0]['id']
            : null;
    }

    /**
     * @param       $data
     * @param array $select
     * @return array|null
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getList($data, array $select = array())
    {
        if (!isset($data['id']) && !isset($data['type']))
            throw new ArgumentNullException('type');

        if (false === Event::run('beforeNoteGetList', self::URL, $data, $select))
            return null;

        $this->requestGet(self::URL, $data);
        Event::run('afterNoteGetList', self::URL, $data, $select, $this->response, $this->code, $this->lastError);

        if (!$this->success)
            $this->handleError(self::NAME);

        $items = (isset($this->response['_embedded']['items']))
            ? $this->response['_embedded']['items']
            : null;

        return self::filterSelectedFields($items, $select);
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

    /**
     * @param       $elementType
     * @param       $elementId
     * @param array $select
     * @return null
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getByElement($elementType, $elementId, array $select = array())
    {
        $elementType = intval($elementType);
        if (!$elementType)
            throw new ArgumentNullException('elementType');

        if (!in_array($elementType, $this->getElementsTypes()))
            throw new ArgumentOutOfRangeException('elementType');

        $elementId = intval($elementId);
        if (!$elementId)
            throw new ArgumentNullException('elementId');

        $data = array(
            'type'      => $elementType,
            'element_id'=> $elementId
        );

        return $this->getList($data, $select);
    }

    /**
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getElementTypeMap()
    {
        if (is_null(self::$elementTypeMap))
            self::$elementTypeMap = array(
                Company::getType()  => self::ELEMENT_TYPE__COMPANY,
                Contact::getType()  => self::ELEMENT_TYPE__CONTACT,
                Lead::getType()     => self::ELEMENT_TYPE__LEAD,
                Task::getType()     => self::ELEMENT_TYPE__TASK
            );

        return self::$elementTypeMap;
    }
}