<?php
namespace Rover\AmoCRM\Entity\Handler;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Helper\Duplicate;
use Rover\AmoCRM\Entity\Handler;
use Rover\Fadmin\Inputs\Selectgroup;
use \Rover\AmoCRM\Model\Rest;

Loc::loadMessages(__FILE__);
/**
 * Class Lead
 *
 * @package Rover\AmoCRM\Entity\Handler
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Lead extends Handler
{
    /** @var array */
    protected $contactsIds;

    /** @var int */
    protected $companyId;

    /**
     * @param Result $result
     * @param bool   $reload
     * @return array|mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getPushData(Result $result, $reload = false)
	{
	    if (empty($this->pushDataCache[$result->getCacheId()]) || $reload) {

            $data = array(
                Rest\Lead::FIELD__NAME  => $this->replacePlaceholders($result, $this->source->getLeadName()),
                Rest\Lead::FIELD__SALE  => null,
                'tags' 	                => $this->getSourceTags($result),
                'custom_fields'         => array()
            );

            $data = $this->addResponsibleId($data);
            $data = $this->addStatusIdPipelineId($data);
            $data = $this->addCustomFields($data, $result, Rest\Lead::getType());
            $data = $this->addCustomSelectBoxes($data, Rest\Lead::getType());

            $data = $this->addVisitorUid($result, $data);

            if ($this->getContactsIds())
                $data['contacts_id'] = $this->getContactsIds();

            if ($this->getCompanyId())
                $data['company_id'] = $this->getCompanyId();

            $data = Duplicate::check($this, $result, $data);

            if (empty($data[Rest\Lead::FIELD__NAME]))
                $data[Rest\Lead::FIELD__NAME] = Loc::getMessage("rover-acrm__default-" . self::getRestType() . "-name");

            $this->pushDataCache[$result->getCacheId()] = $data;
        }

		return $this->pushDataCache[$result->getCacheId()];
	}

    /**
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateStatus($reload = false)
    {
        return $this->getSourceTabValue(Tabs::INPUT__LEAD_DUPLICATE_CONTROL, $reload);
    }

    /**
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateLogic($reload = false)
    {
        return $this->getSourceTabValue(Tabs::INPUT__LEAD_DUPLICATE_LOGIC, $reload);
    }

    /**
     * @param array $data
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function addStatusIdPipelineId(array $data = array())
    {
        $leadStatusInput = $this->getSourceTab()->searchOneByName(Tabs::INPUT__LEAD_STATUS);

        // add status id
        $statusId = intval($leadStatusInput->getValue());
        if (is_null($statusId))
            throw new ArgumentNullException('statusId');

        $data[Rest\Lead::FIELD__STATUS_ID] = $statusId;

        // add pipeline id
        if ($leadStatusInput instanceof Selectgroup) {
            $pipelineId = intval($leadStatusInput->getGroupValue());

            if ($pipelineId)
                $data['pipeline_id'] = $pipelineId;
        }

        return $data;
    }

    /**
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateFields()
    {
        return $this->getSourceTabValue(Tabs::INPUT__LEAD_DUPLICATE_FIELDS);
    }

    /**
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateAction($reload = false)
    {
        return $this->getDuplicateActionByInput(Tabs::INPUT__LEAD_DUPLICATE_ACTION, $reload);
    }

    /**
     * @param $select
     * @return array|mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateCompareData($select)
    {
        $tab    = $this->getSourceTab();
        $filter = [];

        // add contacts filter
        $contactFilter  = $tab->getInputValue(Tabs::INPUT__LEAD_DUPLICATE_CONTACT_FILTER);
        if ($contactFilter && count($this->getContactsIds()))
            $filter['id'] = $this->getRest()->getLeadsIdsByContactsIds($this->getContactsIds());

        $statusFilter = $tab->getInputValue(Tabs::INPUT__LEAD_DUPLICATE_STATUS_FILTER);
        if (isset($statusFilter['']))
            unset($statusFilter['']);

        if (count($statusFilter))
            $filter['status'] = $statusFilter;

        //@todo add $companyFilter etc.;
        return $this->getRest()->getAll($filter, $select);
    }

    /**
     * @param $contactsIds
     * @return $this
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setContactsIds($contactsIds)
    {
        if (empty($contactsIds))
            throw new ArgumentNullException('contactsIds');

        if (!is_array($contactsIds))
            $contactsIds = array($contactsIds);

        $this->contactsIds = $contactsIds;

        return $this;
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getContactsIds()
    {
        return $this->contactsIds;
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
     * @param Result $result
     * @return int|mixed|null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function push(Result $result)
	{
	    $data = $this->getPushData($result, true);

        if (isset($data['id'])) {
            $this->lastId = $data['id'];
            // update, if action is not skip
            if (($this->getDuplicateAction() != Duplicate::ACTION__SKIP))
                $this->lastId = $this->getRest()->update($data['id'], $data);
        } else {
            $this->lastId = $this->getRest()->add($data);
        }

        if ($this->lastId)
            $this->getNoteHandler()
                ->setTargetId($this->lastId)
                ->setNotes($this->getNotes($result))
                ->add();

		return $this->lastId;
	}

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     * @TODO: update to 'lead'
     */
    public function getRestType()
    {
        return 'deal';
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

        return $note->setTargetType(Rest\Lead::getType());
	}
}