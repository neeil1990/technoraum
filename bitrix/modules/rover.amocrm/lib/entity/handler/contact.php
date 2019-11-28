<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.05.2017
 * Time: 14:09
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Entity\Handler;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Helper\Duplicate;
use Rover\AmoCRM\Entity\Handler;
use \Rover\AmoCRM\Model\Rest;

Loc::loadMessages(__FILE__);

/**
 * Class Contact
 *
 * @package Rover\AmoCRM\Entity\Handler
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Contact extends Handler
{
    /** @var array */
    protected $leadsIds;

    /** @var */
    protected $companyId;

    /** @var */
    protected $contactsIds;

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
     * @param $leadsIds
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setLeadsIds($leadsIds)
    {
        if (empty($leadsIds))
            throw new ArgumentNullException('leadId');

        if (!is_array($leadsIds))
            $leadsIds = array($leadsIds);

        $this->leadsIds = $leadsIds;
    }

    /**
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getLeadsIds()
    {
        return $this->leadsIds;
    }

    /**
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateAction($reload = false)
    {
        return $this->getDuplicateActionByInput(Tabs::INPUT__CONTACT_DUPLICATE_ACTION, $reload);
    }

    /**
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateLogic($reload = false)
    {
        return $this->getSourceTabValue(Tabs::INPUT__CONTACT_DUPLICATE_LOGIC, $reload);
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param Result $result
     * @param bool   $reload
     * @return array|mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getPushData(Result $result, $reload = false)
	{
	    if (!isset($this->pushDataCache[$result->getCacheId()]) || $reload) {

            $data = array(
                'tags'          => $this->getSourceTags($result),
                'custom_fields' => array()
            );

            $data = $this->addResponsibleId($data);

            if ($this->leadsIds)
                $data['leads_id'] = $this->getLeadsIds();

            // bind company for contact
            if ($this->companyId
                && (static::getRestType() == Rest\Contact::getType()))
                $data['company_id'] = $this->companyId;

            // bind contact for company
            if ($this->contactsIds
                && (static::getRestType() == Rest\Company::getType()))
                $data['contacts_id'] = $this->contactsIds;

            $data = $this->addCustomFields($data, $result, static::getRestType());
            $data = $this->addCustomSelectBoxes($data, static::getRestType());

            $data = Duplicate::check($this, $result, $data);

            /** @var Rest\Contact | Rest\Company $restObject */
            $restObject = static::getRest();

            if (empty($data[$restObject::FIELD__NAME]))
                $data[$restObject::FIELD__NAME] = Loc::getMessage("rover-acrm__default-" . static::getRestType() . "-name");

            $this->pushDataCache[$result->getCacheId()] = $data;
        }

		return $this->pushDataCache[$result->getCacheId()];
	}

    /**
     * @param Result $result
     * @return int|mixed|null
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function push(Result $result)
	{
        $data = $this->getPushData($result);

        if (isset($data['id'])) {
            $this->lastId = $data['id'];
            // update, if action is not skip
            if (($this->getDuplicateAction() != Duplicate::ACTION__SKIP)
                || !empty($this->leadId))
                $this->getRest()->update($data['id'], $data);
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
     * @param bool $reload
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getDuplicateStatus($reload = false)
    {
        return $this->getSourceTabValue(Tabs::INPUT__CONTACT_DUPLICATE_CONTROL, $reload);
    }

    /**
     * @param $select
     * @return array|mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateCompareData($select)
    {
        return $this->getRest()->getAll(array(), $select);
    }

    /**
     * @return array|mixed|null|string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getDuplicateFields()
    {
        return $this->source->getTab(true)
            ->getValue(Tabs::INPUT__CONTACT_DUPLICATE_FIELDS);
    }

    /**
     * @return Note
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getNoteHandler()
	{
	    $note = new Note($this->source);

		return $note->setTargetType(static::getRestType());
	}
}