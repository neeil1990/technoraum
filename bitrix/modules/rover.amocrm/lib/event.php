<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 03.06.2018
 * Time: 9:53
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM;

use Bitrix\Main\EventResult;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Config\Preset;
use Rover\AmoCRM\Config\TabField;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Entity\Source;
use Rover\AmoCRM\Helper\Log;
use \Bitrix\Main;
use Rover\AmoCRM\Model\Rest;
use Rover\AmoCRM\Model\StatusTable;
use Rover\Fadmin\Inputs\Tab;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\ArgumentOutOfRangeException;
use \Bitrix\Main\ArgumentNullException;

Loc::loadMessages(__FILE__);
/**
 * Class Event
 *
 * @package Rover\AmoCRM
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Event
{
    /**
     * @param Main\Event $event
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeRequest(Main\Event $event)
    {
        Log::addNote('Rest::request parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterRequest(Main\Event $event)
    {
        Log::addNote('Rest::request parameters & result:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeContactGetList(Main\Event $event)
    {
        Log::addNote('Contact::getList parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterContactGetList(Main\Event $event)
    {
        $result = $event->getParameter(3);

        Log::addNote('Contact::getList results count:',  isset($result['_embedded']['items']) ? count($result['_embedded']['items']) : 0);
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeContactUpdate(Main\Event $event)
    {
        Log::addNote('Contact::update parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterContactUpdate(Main\Event $event)
    {
        Log::addNote('Contact::update parameters & result:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeContactAdd(Main\Event $event)
    {
        Log::addNote('Contact::add parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterContactAdd(Main\Event $event)
    {
        Log::addNote('Contact::add parameters & result:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeLeadAdd(Main\Event $event)
    {
        Log::addNote('Lead::add parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterLeadAdd(Main\Event $event)
    {
        Log::addNote('Lead::add parameters & result:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeLeadUpdate(Main\Event $event)
    {
        Log::addNote('Lead::update parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterLeadUpdate(Main\Event $event)
    {
        Log::addNote('Lead::update parameters & result:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeLeadGetList(Main\Event $event)
    {
        Log::addNote('Lead::getList parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterLeadGetList(Main\Event $event)
    {
        $result = $event->getParameter(3);

        Log::addNote('Lead::getList results count:', isset($result['_embedded']['items']) ? count($result['_embedded']['items']) : 0);
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeNoteAdd(Main\Event $event)
    {
        Log::addNote('Note::add parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterNoteAdd(Main\Event $event)
    {
        Log::addNote('Note::add parameters & result:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeNoteGetList(Main\Event $event)
    {
        Log::addNote('Note::getList parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterNoteGetList(Main\Event $event)
    {
        $result = $event->getParameter(3);

        Log::addNote('Note::getList results count:', isset($result['_embedded']['items']) ? count($result['_embedded']['items']) : 0);
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeTaskAdd(Main\Event $event)
    {
        Log::addNote('Task::add parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterTaskAdd(Main\Event $event)
    {
        Log::addNote('Task::add parameters & result:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeUnsortedAdd(Main\Event $event)
    {
        Log::addNote('Unsorted::add parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterUnsortedAdd(Main\Event $event)
    {
        Log::addNote('Unsorted::add parameters & result:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeUnsortedGetList(Main\Event $event)
    {
        Log::addNote('Unsorted::getList parameters:', $event->getParameters());
    }

    /**
     * @param Main\Event $event
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterUnsortedGetList(Main\Event $event)
    {
        $result = $event->getParameter(2);

        Log::addNote('Unsorted::getList results count:', isset($result['unsorted']) ? count($result['unsorted']) : 0);
    }

    /**
     * @param Main\Event $event
     * @return EventResult
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterMakePresetTab(Main\Event $event)
    {
        $parameters = $event->getParameters();
        if (isset($parameters['tab']) && $parameters['tab'] instanceof Tab)
            try{
                TabField::preparePresetTab($parameters['tab']);
            } catch (\Exception $e) {
                return self::handleError($e, $parameters);
            }

        return new EventResult(EventResult::SUCCESS, $parameters, Options::MODULE_ID);
    }

    /**
     * @param Main\Event $event
     * @return EventResult
     * @throws Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeAddPreset(Main\Event $event)
    {
        $parameters = $event->getParameters();
        $options    = Options::load();

        try{
            $source = Source::buildFromRequest($parameters['value']);

            // if preset exists
            if ($source->getPresetId()){
                $options->message->addError(Loc::getMessage('rover-acrm__preset-' . $source->getType() . '-exists'));
                return new EventResult(EventResult::ERROR, $parameters, Options::MODULE_ID);
            }

            $parameters['name'] = $source->getName();
        } catch (\Exception $e) {
            return self::handleError($e, $parameters);
        }

        return new EventResult(EventResult::SUCCESS, $parameters, Options::MODULE_ID);
    }

    /**
     * @param Main\Event $event
     * @return EventResult
     * @throws Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterRemovePreset(Main\Event $event)
    {
        $parameters = $event->getParameters();

        if (isset($parameters['id']))
            try{
                if (!Preset::removeById($parameters['id']))
                    return new EventResult(EventResult::ERROR, $parameters, Options::MODULE_ID);

            } catch (\Exception $e) {
                return self::handleError($e, $parameters);
            }

        return new EventResult(EventResult::SUCCESS, $parameters, Options::MODULE_ID);
    }

    /**
     * @param Main\Event $event
     * @return EventResult
     * @throws Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeGetTabInfo(Main\Event $event)
    {
        $parameters = $event->getParameters();

        if (isset($parameters['tab']) && $parameters['tab'] instanceof Tab)
        {
            /** @var Tab $tab */
            $tab                    = $parameters['tab'];
            $parameters['label']    = htmlspecialchars_decode($parameters['label']);
            $options                = Options::load();

            try {
                // set connection status
                if (!$tab->isPreset()) {

                    $accountId = $options->getAccountId();

                    $parameters['label'] .= $accountId ? ' [' . Loc::getMessage('rover-acrm__connected') . ']' : ' [' . Loc::getMessage('rover-acrm__disconnected') . ']';

                    if ($accountId)
                        $parameters['description'] .= ' [' . Loc::getMessage('rover-acrm__account-id', array('#id#' => $accountId)) . ']';
                } else {
                    $sourceType = Preset::getTypeById($tab->getPresetId());
                    if (!$sourceType)
                        return new EventResult(EventResult::SUCCESS, $parameters, Options::MODULE_ID);

                    $parameters['label'] .= ' (' . Loc::getMessage('rover-acrm__preset-type-' . $sourceType) . ')';

                    //check sites
                    $sites = \Rover\Params\Main::getSites(array('empty' => null));
                    if (count($sites) <= 1)
                        return new EventResult(EventResult::SUCCESS, $parameters, Options::MODULE_ID);

                    $presetSites = $tab->getInputValue(Tabs::INPUT__SITES);
                    if (empty($presetSites))
                        $presetSites = array_keys($sites);

                    $parameters['label'] .= ' [' . implode(', ', $presetSites) . ']';
                }
            } catch (\Exception $e) {
                return self::handleError($e, $parameters);
            }
        }

        return new EventResult(EventResult::SUCCESS, $parameters, Options::MODULE_ID);
    }

    /**
     * @param Main\Event $event
     * @return EventResult
     * @throws Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onAfterAddPreset(Main\Event $event)
    {
        $parameters = $event->getParameters();

        if (isset($parameters['id']) && isset($parameters['value']))
            try{
                $source = Source::buildFromRequest($parameters['value']);

                if (!Preset::add($parameters['id'], $source->getId(), $source->getType()))
                    return new EventResult(EventResult::ERROR, $parameters, Options::MODULE_ID);

            } catch (\Exception $e) {
                return self::handleError($e, $parameters);
            }

        return new EventResult(EventResult::SUCCESS, $parameters, Options::MODULE_ID);
    }

    /**
     * @param Main\Event $event
     * @return EventResult
     * @throws Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeGetRequest(Main\Event $event)
    {
        $parameters = $event->getParameters();

        try{
            // @TODO:
            /*if (Dependence::isDemo() || Dependence::isDemoExpired())
                return;*/

            $request = Main\Application::getInstance()->getContext()->getRequest();

            // handle events
            if ($request->getPost(Tabs::INPUT__HANDLE_NEW_EVENTS))
                $status = StatusTable::STATUS__NEW;
            elseif ($request->getPost(Tabs::INPUT__HANDLE_ERROR_EVENTS))
                $status = StatusTable::STATUS__ERROR;

            if (isset($status))
                Entry::pushByStatus($status, Options::getAgentEventsCountStatic());
        } catch (\Exception $e) {
            return self::handleError($e, $parameters);
        }

        return new EventResult(EventResult::SUCCESS, $parameters, Options::MODULE_ID);
    }

    /**
     * @param Main\Event $event
     * @return EventResult
     * @throws Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function onBeforeAddValuesFromRequest(Main\Event $event)
    {
        try{
            Rest::clearCookie();
        } catch (\Exception $e) {
            return self::handleError($e, $event->getParameters());
        }

        return new EventResult(EventResult::SUCCESS, $event->getParameters(), Options::MODULE_ID);
    }

    /**
     * @param \Exception $e
     * @param array      $parameters
     * @return EventResult
     * @throws Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)s
     */
    protected static function handleError(\Exception $e, array $parameters)
    {
        Options::load()->message->addError($e->getMessage());
        $parameters['error_message'] = $e->getMessage();

        return new EventResult(EventResult::ERROR, $parameters, Options::MODULE_ID);

    }
}