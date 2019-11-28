<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.05.2017
 * Time: 14:39
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Entity\Handler;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Model\Rest\Task as TaskRest;

/**
 * Class Task
 *
 * @package Rover\AmoCRM\Entity\Handler
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Task extends Handler
{
    /** @var int */
    protected $contactId;

    /** @var int */
    protected $companyId;

    /** @var int */
    protected $leadId;

    /** @var */
    protected $availableTypes;

    const DEADLINE__NOW     = 'now';
    const DEADLINE__DAY_END = 'day_end';
    /**
     * @param $select
     * @return mixed|void
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateCompareData($select)
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateFields()
    {
        throw new NotImplementedException();
    }

    /**
     * @param bool $reload
     * @throws NotImplementedException
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateAction($reload = false)
    {
        throw new NotImplementedException();
    }

    /**
     * @param bool $reload
     * @return mixed|void
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateLogic($reload = false)
    {
        throw new NotImplementedException();
    }

    /**
     * @param bool $reload
     * @return mixed|null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateStatus($reload = false)
    {
        return false;
    }

    /**
     * @param $contactId
     * @return $this
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setContactId($contactId)
    {
        $contactId = intval($contactId);
        if (!$contactId)
            throw new ArgumentNullException('contactId');

        $this->contactId = $contactId;

        return $this;
    }

    /**
     * @param $companyId
     * @return $this
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setCompanyId($companyId)
    {
        $companyId = intval($companyId);
        if (!$companyId)
            throw new ArgumentNullException('companyId');

        $this->companyId = $companyId;

        return $this;
    }

    /**
     * @param $leadId
     * @return $this
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setLeadId($leadId)
    {
        $leadId = intval($leadId);
        if (!$leadId)
            throw new ArgumentNullException('leadId');

        $this->leadId = $leadId;

        return $this;
    }

    /**
     * @param Result $result
     * @return string
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getPreparedText(Result $result)
    {
        $task   = $this->getFields($result);
        $fields = array();

        foreach ($task[Tabs::INPUT__TASK_TEXT] as $field)
            $fields[] = $field['title'] . ': ' . $field['value'];

        $fields = implode("\n", $fields);
        $fields = $this->formatText($fields);

        $text   = $this->getSourceTabValue(Tabs::INPUT__TASK_TEXT);
        $text   = trim((false === strpos($text, '#FIELDS#'))
            ? $text . $fields
            : str_replace('#FIELDS#', $fields, $text));

        return strlen($text) ? $text : '-';
    }

    /**
     * @param bool $reload
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getAvailableTypes($reload = false)
    {
        if (!is_array($this->availableTypes) || $reload) {

            $this->availableTypes = array();

            if ($this->leadId)
                $this->availableTypes[TaskRest::ELEMENT_TYPE__LEAD] = $this->leadId;

            if ($this->contactId)
                $this->availableTypes[TaskRest::ELEMENT_TYPE__CONTACT] = $this->contactId;

            if ($this->companyId)
                $this->availableTypes[TaskRest::ELEMENT_TYPE__COMPANY] = $this->companyId;
        }

        return $this->availableTypes;
    }

    /**
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getElementType()
    {
        $availableTypes = $this->getAvailableTypes();
        $type           = $this->getSourceTabValue(Tabs::INPUT__TASK_ELEMENT_TYPE);

        return isset($availableTypes[$type])
            ? $type
            : reset(array_keys($availableTypes));
    }

    /**
     * @return mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getElementId()
    {
        $availableTypes = $this->getAvailableTypes();
        $type           = $this->getElementType();

        return isset($availableTypes[$type])
            ? $availableTypes[$type]
            : null;
    }

    /**
     * @param Result $result
     * @param bool   $reload
     * @return mixed
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getPushData(Result $result, $reload = false)
	{
	    if (!is_array($this->pushDataCache[$result->getCacheId()]) || $reload)
	    {
            $elementType = $this->getElementType();
            if (!$elementType)
                throw new ArgumentNullException('elementType');

            $elementId = $this->getElementId();
            if (!$elementId)
                throw new ArgumentNullException('elementId');

            $data = array(
                'element_id'    => $elementId,
                'element_type'  => $elementType,
                'task_type'     => $this->getSourceTabValue(Tabs::INPUT__TASK_TYPE),
                'text'          => $this->replacePlaceholders($result, $this->getPreparedText($result)),
                'complete_till_at'  => $this->getCompleteTillAt()
            );

            $data = $this->addResponsibleId($data);

            $this->pushDataCache[$result->getCacheId()] = $data;
        }

        return $this->pushDataCache[$result->getCacheId()];
	}

    /**
     * @return false|int
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function getCompleteTillAt()
    {
        switch ( $this->getSourceTabValue(Tabs::INPUT__TASK_DEADLINE))
        {
            case self::DEADLINE__NOW:
                return time();
            case self::DEADLINE__DAY_END:
                return strtotime("tomorrow") - 1;
            default:
                throw new ArgumentOutOfRangeException('complete_till_at');
        }
    }

    /**
     * @param Result $result
     * @return int|mixed|null
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function push(Result $result)
	{
		$this->lastId = $this->getRest()->add($this->getPushData($result));

		if ($this->lastId)
            $this->getNoteHandler()
                ->setTargetId($this->lastId)
                ->setNotes($this->getNotes($result))
                ->add();

		return $this->lastId;
	}


    /**
     * @return Note
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getNoteHandler()
    {
        $note = new Note($this->source);

        return $note->setTargetType(static::getRestType());
    }
}