<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.05.2017
 * Time: 13:55
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
use Rover\AmoCRM\Model\Rest;

/**
 * Class Note
 *
 * @package Rover\AmoCRM\Entity\Handler
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Note extends Handler
{
    /** @var string */
    protected $targetType;

    /** @var integer */
    protected $targetId;

    /** @var array */
    protected $notes = array();

    /**
     * @param $select
     * @return array|mixed|null
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateCompareData($select)
    {
        if (empty($this->targetType) || empty($this->targetId))
            return array();

        return Rest\Note::getInstance()->getByElement($this->targetType, $this->targetId, $select);
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
     * @return mixed|null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateStatus($reload = false)
    {
        return true;
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
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateAction($reload = false)
    {
        throw new NotImplementedException();
    }

    /**
     * @param $notes
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @param bool $reload
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getAddData($reload = false)
    {
        if (!$this->targetType)
            throw new ArgumentNullException('targetType');

        if (empty($this->pushDataCache) || $reload) {

            $this->pushDataCache = array();
            $notes = $this->notes;

            if (empty($notes))
                return $this->pushDataCache;

            $tab    = $this->getSourceTab();
            $fields = array();

            $dataTemplate = array(
                'note_type'     => $this->targetType == Rest\Task::getType()
                    ? Rest\Note::TYPE__TASK_RESULT
                    : Rest\Note::TYPE__COMMON,
                'element_type'  => $this->targetType
            );

            $dataTemplate = $this->addResponsibleId($dataTemplate);

            foreach ($notes as $keyN => $fieldValues)
            {
                $value  = $this->ejectValues($fieldValues);
                $text   = $keyN . ": " . implode(', ', $value);
                $text   = $this->formatText($text);

                if (!strlen($text))
                    continue;

                $fields[] = $text;
            }

            if (empty($fields))
                return $this->pushDataCache;

            if ($tab->getValue(Tabs::INPUT__GROUP_NOTES))
                $fields = array(implode("\n", $fields));

            foreach ($fields as $field)
            {
                $data           = $dataTemplate;
                $data['text']   = $field;

                $this->pushDataCache[] = $data;
            }
        }

        return $this->pushDataCache;
    }

    /**
     * @param $restType
     * @return $this
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setTargetType($restType)
    {
        $restType = trim($restType);
        if (!$restType)
            throw new ArgumentNullException('restType');

        $map = Rest\Note::getElementTypeMap();

        if (!isset($map[$restType]))
            throw new ArgumentOutOfRangeException('targetType');

        $this->targetType = $map[$restType];

        return $this;
    }

    /**
     * @param $targetId
     * @return $this
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setTargetId($targetId)
    {
        $targetId = intval($targetId);
        if (!$targetId)
            throw new ArgumentNullException('targetId');

        $this->targetId = $targetId;

        return $this;
    }

    /**
     * @return int|null
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function add()
    {
        if (!$this->targetId)
            throw new ArgumentNullException('targetId');

        $fields         = $this->getAddData();
        $compareNotes   = $this->getDuplicateCompareData(['text']);

        foreach ($fields as $data){

            // check if exists
            if (is_array($compareNotes) && count($compareNotes)){

                $exists         = false;
                $data['text']   = trim($data['text']);

                foreach ($compareNotes as $compareNote){
                    $compareNote['text'] = trim($compareNote['text']);

                    if (strlen($compareNote['text'])
                        && $compareNote['text'] == $data['text']){
                        $exists = true;
                        break;
                    }
                }

                if ($exists)
                    continue;
            }

            $data['element_id'] = $this->targetId;

            $this->lastId = Rest\Note::getInstance()->add($data);
        }

        return $this->lastId;
    }

    /**
     * @param Result $result
     * @return mixed|void
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function push(Result $result)
    {
        throw new NotImplementedException();
    }

    /**
     * @param Result $result
     * @param bool   $reload
     * @return mixed|void
     * @throws NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getPushData(Result $result, $reload = false)
    {
        throw new NotImplementedException();
    }
}