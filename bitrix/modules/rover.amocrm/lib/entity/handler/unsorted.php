<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.05.2017
 * Time: 15:13
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Entity\Handler;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Helper\Duplicate;
use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Model\AdditionalParam\Domain;
use Rover\AmoCRM\Model\AdditionalParam\Ip;
use Rover\AmoCRM\Model\Rest\Unsorted as UnsortedRest;
use \Rover\AmoCRM\Model\Rest;

Loc::loadMessages(__FILE__);

/**
 * Class Unsorted
 *
 * @package Rover\AmoCRM\Entity\Handler
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Unsorted extends Handler
{
    /** @var array */
    protected $leads = array();

    /** @var array */
    protected $contacts = array();

    /** @var array */
    protected $companies = array();

    /**
     * @param Contact $contact
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function addContact(Contact $contact)
	{
	    $this->contacts[] = $contact;

		return $this;
	}

    /**
     * @param Company $company
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function addCompany(Company $company)
	{
	    $this->companies[] = $company;

		return $this;
	}

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
     * @return mixed|null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateStatus($reload = false)
    {
        return false;
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
     * @param Result $result
     * @return array
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function getContactsPushData(Result $result)
    {
        return $this->getCCData($result, $this->contacts);
    }

    /**
     * @param Result $result
     * @return array
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function getCompaniesPushData(Result $result)
    {
        return $this->getCCData($result, $this->companies);
    }

    /**
     * @param Result $result
     * @param array  $entities
     * @return array
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getCCData(Result $result, array $entities)
    {
        $resultData = array();

        /**
         *
         */
        foreach ($entities as $cc){
            /**
             * @var Contact|Company $cc
             */
            $data = $cc
                ->setDuplicateAction(Duplicate::ACTION__ADD_NOTE)
                ->getPushData($result);

            unset($data['responsible_user_id']);

            $noteHandler = $cc->getNoteHandler();
            $noteHandler->setNotes($cc->getNotes($result));

            $data['notes']          = $this->getPreparedNotes($noteHandler->getAddData());
            $data['date_create']    = time();

            $resultData[] = $data;
        }

        return $resultData;
    }

    /**
     * @param Lead $lead
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function addLead(Lead $lead)
	{
		$this->leads[] = $lead;

		return $this;
	}

    /**
     * @param Result $result
     * @return array
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function getLeadsPushData(Result $result)
    {
        $resultData = array();

        foreach ($this->leads as $lead){
            /**
             * @var Lead $lead
             */
            $data = $lead
                ->setDuplicateAction(Duplicate::ACTION__ADD_NOTE)
                ->getPushData($result);

            unset($data[Rest\Lead::FIELD__STATUS_ID]);
            unset($data['responsible_user_id']);

            $noteHandler = $lead->getNoteHandler();
            $noteHandler->setNotes($lead->getNotes($result));

            $data['notes']          = $this->getPreparedNotes($noteHandler->getAddData());
            $data['date_create']    = time();

            $resultData[] = $data;
        }

        return $resultData;
    }

    /**
     * @param $notesData
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getPreparedNotes($notesData)
    {
        foreach ($notesData as &$noteData)
            unset($noteData['responsible_user_id']);

        return $notesData;
    }

    /**
     * @param Result $result
     * @return mixed
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getName(Result $result)
    {
        $unsortedName = $this->source->getTab()->getValue(Tabs::INPUT__UNSORTED_NAME);
        if (!$unsortedName)
            $unsortedName = Loc::getMessage('rover-acrm__unsorded-from');

        $unsortedName = str_replace('#NAME#', $this->source->getName(), $unsortedName);

        return $this->replacePlaceholders($result, $unsortedName);
    }

    /**
     * @param Result $result
     * @param bool   $reload
     * @return array|mixed
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getPushData(Result $result, $reload = false)
	{
	    if (empty($this->pushDataCache[$result->getCacheId()]) || $reload) {
            $httpHost = Domain::getResultValue($result, $this);
            if (!$httpHost)
                $httpHost = '<host not found>';

            $ip = Ip::getResultValue($result, $this);
            if (!$ip)
                $ip = '<ip not found>';

            $name = $this->getName($result);

            $this->pushDataCache[$result->getCacheId()] = array(
                array(
                    'source'        => $httpHost,
                    'source_uid'    => NULL,
                    'data'          => array(
                        Rest\Lead::NAME     => $this->getLeadsPushData($result),
                        Rest\Contact::NAME  => $this->getContactsPushData($result),
                        Rest\Company::NAME  => $this->getCompaniesPushData($result)
                    ),
                    'source_data' => array(
                        'data' => array(
                            'name_1' => array(
                                'type'          => 'text',
                                'id'            => 'name',
                                'element_type'  => '1',
                                'name'          => 'From',
                                'value'         => $httpHost,
                            ),
                        ),
                        'form_id'   => 1,
                        'form_type' => 1,
                        'origin' => array(
                            'ip'        => $ip,
                            'datetime'  => '',
                            'referer'   => ''
                        ),
                        'date'      => time(),
                        'from'      => $name,
                        'from_name' => $name
                    )
                )
            );
        }

		return $this->pushDataCache[$result->getCacheId()];
	}

    /**
     * @param Result $result
     * @return int|mixed|null
     * @throws NotImplementedException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function push(Result $result)
	{
	    $data           = $this->getPushData($result);
        $this->lastId   = UnsortedRest::getInstance()->addFromForm($data);

        return $this->lastId;
	}
}