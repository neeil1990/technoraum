<?php
namespace Rover\AmoCRM;
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 18.02.2016
 * Time: 18:39
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Entity\AddResult;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Config\Tabs;
use \Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Entity\Handler\Company;
use Rover\AmoCRM\Helper\Event as EventHelper; // fix bug "Cannot use Rover\AmoCRM\Helper\Event as Event because the name is already in use [0]" (wtf?)
use Rover\AmoCRM\Model\AdditionalParam;
use Rover\AmoCRM\Model\StatusTable;
use Rover\AmoCRM\Entity\Source;
use Rover\AmoCRM\Entity\Handler\Contact;
use Rover\AmoCRM\Entity\Handler\Lead;
use Rover\AmoCRM\Entity\Handler\Task;
use Rover\AmoCRM\Entity\Handler\Unsorted;
use Rover\AmoCRM\Entity\Result;

Loc::LoadMessages(__FILE__);
/**
 * Class Entry
 *
 * @package Rover\AmoCRM
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Entry
{
    /** @var array */
    protected static $eventDuplicates = array();

    /**
     * @param Source $source
     * @return bool
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function hasEventDuplicatesOnHit(Source $source)
    {
        if (!$source->isPresetExists())
            return false;

        if (!$source->getTab()->getInputValue(Tabs::INPUT__IGNORE_SAME_HIT_EVENTS))
            return false;

        if (in_array($source->getId(), self::$eventDuplicates[$source->getType()]))
            return true;

        self::$eventDuplicates[$source->getType()] = array($source->getId());

        return false;
    }

    /**
     * @param        $sourceType
     * @param        $sourceId
     * @param        $eventParams
     * @param string $siteId
     * @return AddResult|bool
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addToProcessingTable($sourceType, $sourceId, $eventParams, $siteId = '')
    {
        // check source
        try{
            $source = Source::build($sourceType, $sourceId);
            if (!$source->isPresetExists())
                return false;

            if (self::hasEventDuplicatesOnHit($source))
                return false;

            $eventResult = StatusTable::addNew($sourceType, $sourceId, $eventParams, self::getAdditionalParams($siteId));

            if (!$eventResult->isSuccess()){
                Options::load()->handleError(implode('<br>', $eventResult->getErrorMessages()));

                return false;
            }

        } catch (\Exception $e) {
            Options::load()->handleError($e);

            return false;
        }

        return $eventResult;
    }

    /**
     * @param        $sourceType
     * @param        $sourceId
     * @param        $eventParams
     * @param string $siteId
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function pushNew($sourceType, $sourceId, $eventParams, $siteId = '')
    {
        if (!Options::isEnabled())
            return;

        $eventResult = self::addToProcessingTable($sourceType, $sourceId, $eventParams, $siteId);
        if (!$eventResult instanceof AddResult)
            return;

        // agent is enabled
        if (Options::isAgentEnabled())
            return;

        self::pushWithLog($eventResult->getId(), $eventResult->getData());
    }

    /**
     * @param string $siteId
     * @return array
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function getAdditionalParams($siteId = '')
    {
        $additionalParams = array(
            AdditionalParam::PARAM__MARKS       => AdditionalParam\AdvMarks::getValue(),
            AdditionalParam::PARAM__PAGE_URL    => AdditionalParam\PageUrl::getValue(),
            AdditionalParam::PARAM__IP          => AdditionalParam\Ip::getValue(),
            AdditionalParam::PARAM__VISITOR_UID => AdditionalParam\VisitorUid::getValue(),
        );

        $siteId = trim($siteId);
        if (strlen($siteId))
            $additionalParams[Result::FIELD__SITE_ID] = $siteId;

        return $additionalParams;
    }

    /**
     * @param $eventResultId
     * @param $eventResultData
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function pushWithLog($eventResultId, $eventResultData)
    {
        $eventResultId = intval($eventResultId);
        if (!$eventResultId)
            throw new ArgumentNullException('eventResultId');

        try{
            $status = self::push($eventResultData)
                ? StatusTable::STATUS__SUCCESS
                : StatusTable::STATUS__SKIPPED;

        } catch (\Exception $e) {
            Options::load()->handleError($e);

            $status = StatusTable::STATUS__ERROR;
        }

        StatusTable::updateStatus($eventResultId, $status);
    }

    /**
     * @param $data
     * @return bool
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function push($data)
	{
	    if (!isset($data['SOURCE_TYPE']))
	        throw new ArgumentNullException('SOURCE_TYPE');

	    if (!isset($data['SOURCE_ID']))
	        throw new ArgumentNullException('SOURCE_ID');

	    if (!isset($data['EVENT_PARAMS']))
	        throw new ArgumentNullException('EVENT_PARAMS');

        $data['EVENT_PARAMS']       = unserialize($data['EVENT_PARAMS']);
        $data['ADDITIONAL_PARAMS']  = isset($data['ADDITIONAL_PARAMS'])
            ? unserialize($data['ADDITIONAL_PARAMS'])
            : array();

        if (!Options::isEnabled())
            return false;

        $options = Options::load();

        if (!$options->getDependenceStatus())
            throw new SystemException('Dependence error');

        if (!$options->isConnected())
            throw new SystemException('No connection');

        $source = Source::build($data['SOURCE_TYPE'], $data['SOURCE_ID']);
        if (!$source->isPresetExists())
            return false;

        $result = Result::build($source, $data['EVENT_PARAMS'], $data['ADDITIONAL_PARAMS']);
        if (!$result instanceof Result)
            return false;

        if (!$source->isEnabled($result->getSiteId()))
            return false;

        // send data to amo
        return self::pushData($source, $result);
    }

    /**
     * @param Source $source
     * @param Result $result
     * @return bool
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function pushData(Source $source, Result $result)
    {
        // event
        if (!EventHelper::run('onBeforeAmoPush', $source, $result))
            return false;

        $tab            = $source->getTab(true);

        $unsortedMode   = Options::getUnsortedStatus($tab);

        $leadCreate     = $tab->getInputValue(Tabs::INPUT__LEAD_CREATE);
        $contactCreate  = $tab->getInputValue(Tabs::INPUT__CONTACT_CREATE);
        $companyCreate  = $tab->getInputValue(Tabs::INPUT__COMPANY_CREATE);
        $taskCreate     = $tab->getInputValue(Tabs::INPUT__TASK_CREATE);

        if (!$unsortedMode && !$leadCreate && !$contactCreate && !$companyCreate)
            return false;

        $eventResult = EventHelper::run('pushData', $source, $result, $unsortedMode, $contactCreate, $companyCreate, $leadCreate, $taskCreate);

        if ($eventResult === false)
            return false;

        $source         = $eventResult[0];
        $result         = $eventResult[1];
        $unsortedMode   = $eventResult[2];
        $contactCreate  = $eventResult[3];
        $companyCreate  = $eventResult[4];
        $leadCreate     = $eventResult[5];
        $taskCreate     = $eventResult[6];

        if ($unsortedMode)
            self::pushUnsortedData($source, $result, $contactCreate, $companyCreate);
        else
            self::pushStandardData($source, $result, $contactCreate, $companyCreate, $leadCreate, $taskCreate);

        EventHelper::run('afterPushData', $source, $result, $unsortedMode, $contactCreate, $companyCreate, $leadCreate, $taskCreate);

        return true;
    }

    /**
     * @param     $status
     * @param int $limit
     * @return int
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function pushByStatus($status, $limit = 3)
    {
        $limit = intval($limit);
        if (!$limit)
            $limit = 3;

        $events = StatusTable::getByStatus($status, $limit);
        $count  = 0;

        while ($event = $events->fetch()){
            self::pushWithLog($event['ID'], $event);
            ++$count;
        }

        return $count;
    }

    /**
     * @param Source $source
     * @param Result $result
     * @param        $contactCreate
     * @param        $companyCreate
     * @param        $leadCreate
     * @param        $taskCreate
     * @return bool
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     * @deprecated
     */
    public static function pushNormalData(Source $source, Result $result, $contactCreate, $companyCreate, $leadCreate, $taskCreate)
    {
        return self::pushStandardData($source, $result, $contactCreate, $companyCreate, $leadCreate, $taskCreate);
    }

    /**
     * @param Source $source
     * @param Result $result
     * @param        $contactCreate
     * @param        $companyCreate
     * @param        $leadCreate
     * @param        $taskCreate
     * @return bool
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \ReflectionException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function pushStandardData(Source $source, Result $result, $contactCreate, $companyCreate, $leadCreate, $taskCreate)
    {
        $eventResult = EventHelper::run('beforePushStandardData', $source, $result, $contactCreate, $companyCreate, $leadCreate, $taskCreate);
        if ($eventResult === false)
            return false;

        $source         = $eventResult[0];
        $result         = $eventResult[1];
        $contactCreate  = $eventResult[2];
        $companyCreate  = $eventResult[3];
        $leadCreate     = $eventResult[4];
        $taskCreate     = $eventResult[5];

        $contact = $lead = $company = $task = null;

        // push contact
        if ($contactCreate){
            $contact = new Contact($source);
            $contact->push($result);
        }

        // push company
        if ($companyCreate) {
            $company = new Company($source);

            if (($contact instanceof Contact)
                && intval($contact->getLastId()))
                $company->setContactsIds($contact->getLastId());

            $company->push($result);
        }

        // push lead
        if ($leadCreate) {
            $lead = new Lead($source);

            // for duplicates search
            if (($contact instanceof Contact)
                && intval($contact->getLastId()))
                $lead->setContactsIds($contact->getLastId());

            if (($company instanceof Company)
                && intval($company->getLastId()))
                $lead->setCompanyId($company->getLastId());

            $lead->push($result);
        }

        // push task
        if ($taskCreate) {
            $task = new Task($source);

            if (($lead instanceof Lead)
                && intval($lead->getLastId()))
                $task->setLeadId($lead->getLastId());

            if (($contact instanceof Contact)
                && intval($contact->getLastId()))
                $task->setContactId($contact->getLastId());

            if (($company instanceof Company)
                && intval($company->getLastId()))
                $task->setCompanyId($company->getLastId());

            $task->push($result);
        }

        EventHelper::run('afterPushStandardData', $source, $result, $contact, $company, $lead, $task);

        return true;
    }

    /**
     * @param Source $source
     * @param Result $result
     * @param        $contactCreate
     * @param        $companyCreate
     * @return bool
     * @throws ArgumentNullException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function pushUnsortedData(Source $source, Result $result, $contactCreate, $companyCreate)
    {
        $eventResult1 = EventHelper::run('beforePushUnsortedData', $source, $result, $contactCreate, $companyCreate);
        if ($eventResult1 === false)
            return false;

        $source         = $eventResult1[0];
        $result         = $eventResult1[1];
        $contactCreate  = $eventResult1[2];
        $companyCreate  = $eventResult1[3];

        $unsorted = new Unsorted($source);

        if ($contactCreate)
            $unsorted->addContact(new Contact($source));

        if ($companyCreate)
            $unsorted->addCompany(new Company($source));

        // if ($leadCreate)
        // always add lead to unsorted
        $unsorted->addLead(new Lead($source));

        $eventResult2 = EventHelper::run('pushUnsortedData', $source, $result, $unsorted);
        if ($eventResult2 === false)
            return false;

        $source     = $eventResult2[0];
        $result     = $eventResult2[1];
        /** @var Unsorted $unsorted */
        $unsorted   = $eventResult2[2];

        $unsorted->push($result);

        EventHelper::run('afterPushUnsortedData', $source, $result, $unsorted);

        return true;
    }
}