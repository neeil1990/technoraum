<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 18.02.2016
 * Time: 15:48
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Config;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use \Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Helper\Duplicate;
use Rover\AmoCRM\Helper\Log;
use Rover\AmoCRM\Model\AdditionalParam\AdvMarks;
use Rover\AmoCRM\Model\StatusTable;
use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Model\Rest;
use Rover\AmoCRM\Model\Rest\Account;
use Rover\Fadmin\Helper\InputFactory;
use Rover\Params\Main;
use \Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class Tabs
 *
 * @package Rover\AmoCRM\Config
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Tabs
{
	const TAB__MAIN     = 'settings';
	const TAB__PRESET   = 'form';

	// main inputs
	const INPUT__ENABLED                    = 'enabled';
	const INPUT__LOG_ENABLED                = 'log-enabled';
	const INPUT__LOG_MAX_SIZE               = 'log-max-size';
	const INPUT__SUB_DOMAIN                 = 'domain';
	const INPUT__LOGIN                      = 'email';
	const INPUT__HASH                       = 'hash';
	const INPUT__CONNECT                    = 'Apply';
	const INPUT__EVENT_LOG_LIFETIME         = 'event-log-lifetime';
	const INPUT__GROUP_NOTES                = 'group-notes';
	const INPUT__IGNORE_SAME_HIT_EVENTS     = 'ignore-same-hit-events';
	const INPUT__SAVE_ADV_MARKS             = 'save-adv-marks';
	const INPUT__REMOVE_GA_VERSION          = 'remove-ga-version';
	const INPUT__ADV_MARKS_FILTER           = 'adv-marks-filter';
    const INPUT__ADV_MARKS_CUSTOM_FILTER    = 'adv-marks-custom-filter';
	const INPUT__UNSORTED_CREATE            = 'unsorted';
	const INPUT__UNSORTED_NAME              = 'unsorted-name';
	const INPUT__UNAVAILABLE_ALERT          = 'unavailable-alert';
	const INPUT__AGENT                      = 'agent';
	const INPUT__AGENT_COUNT                = 'agent-count';
	const INPUT__AGENT_HANDLE_ERRORS        = 'agent-handle-errors';
	const INPUT__HANDLE_NEW_EVENTS          = 'handle-new-events';
	const INPUT__HANDLE_ERROR_EVENTS        = 'handle-error-events';

	// preset inputs
	const INPUT__CONNECTION_ENABLED         = 'rile-enabled';
	const INPUT__SITES                      = 'sites';
	const INPUT__MAIN_USER                  = 'main_user';
	const INPUT__TAG                        = 'tag';
	const INPUT__FORMAT                     = 'format';
    const INPUT__MAPPING_SUBTABCONROL       = 'mapping_subtabcontrol_';

	const INPUT__LEAD_CREATE                    = 'lead_create';
	const INPUT__LEAD_STATUS                    = 'status_deal';
	const INPUT__LEAD_VISITOR_UID               = 'lead_visitor_uid';
	const INPUT__LEAD_NAME                      = 'deal_name';
	const INPUT__LEAD_PRICE                     = 'deal_budget';
    const INPUT__LEAD_DUPLICATE_CONTROL         = 'lead_duplicate_control';
    const INPUT__LEAD_DUPLICATE_PIPELINE_FILTER = 'lead_duplicate_pipeline';
    const INPUT__LEAD_DUPLICATE_STATUS_FILTER   = 'lead_duplicate_status';
    const INPUT__LEAD_DUPLICATE_CONTACT_FILTER  = 'lead_duplicate_contact-filter';
    const INPUT__LEAD_DUPLICATE_FIELDS          = 'lead_duplicate_fields';
    const INPUT__LEAD_DUPLICATE_ACTION          = 'lead_duplicate_action';
    const INPUT__LEAD_DUPLICATE_LOGIC           = 'lead_duplicate_logic';
    const INPUT__LEAD_DUPLICATE_UPDATE_NAME     = 'lead_duplicate_update_name';

	const INPUT__CONTACT_CREATE             = 'contact_create';
	const INPUT__CONTACT_DUPLICATE_CONTROL  = 'contact_duplicate_control';
	const INPUT__CONTACT_DUPLICATE_FIELDS   = 'contact_duplicate_fields';
	const INPUT__CONTACT_DUPLICATE_ACTION   = 'contact_duplicate_action';
	const INPUT__CONTACT_DUPLICATE_LOGIC    = 'contact_duplicate_logic';

	const INPUT__COMPANY_CREATE             = 'company_create';
	const INPUT__COMPANY_DUPLICATE_CONTROL  = 'company_duplicate_control';
	const INPUT__COMPANY_DUPLICATE_FIELDS   = 'company_duplicate_fields';
	const INPUT__COMPANY_DUPLICATE_ACTION   = 'company_duplicate_action';
	const INPUT__COMPANY_DUPLICATE_LOGIC    = 'company_duplicate_logic';

	const INPUT__TASK_CREATE        = 'task_create';
	const INPUT__TASK_ELEMENT_TYPE  = 'task_element_type';
	const INPUT__TASK_TYPE          = 'task_type';
	const INPUT__TASK_TEXT          = 'task_text';
	const INPUT__TASK_DEADLINE      = 'task_deadline';

	const INPUT__REMOVE_PRESET      = 'remove_form';

    /**
     * @param Options $options
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function get(Options $options)
	{
	    if (!$options->getDependenceStatus())
	        return array();

	    $tabs = array(self::getMainTab($options));

	    if ($options->isConnected())
	        $tabs[] = self::getPresetTab($options);

		return $tabs;
	}

    /**
     * @param Options $options
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function getMainTab(Options $options)
	{
		return array(
			'name'          => self::TAB__MAIN,
			'label'         => Loc::getMessage(self::TAB__MAIN . '_label'),
			'description'   => Loc::getMessage(self::TAB__MAIN . '_descr'),
			'inputs'        => self::getMainTabInputs($options)
        );
	}

    /**
     * @param Options $options
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function getMainTabInputs(Options $options)
	{
	    //$isDemo = Dependence::isDemo() || Dependence::isDemoExpired();
        $isDemo = false; // @TODO: disable agent in demo version
        $useAgents = Options::getValueStatic(Options::MODULE_ID, self::INPUT__AGENT);

		$inputs = array(
		    // main settings
			InputFactory::getCustom('banner', Loc::getMessage('banner')),
			self::getInputSubDomain(),
            InputFactory::getText(self::INPUT__LOGIN),
            InputFactory::getPassword(Tabs::INPUT__HASH),
            InputFactory::getSubmit(self::INPUT__CONNECT),
			InputFactory::getCheckbox(self::INPUT__ENABLED),
            // event handling
            InputFactory::getHeader(Loc::getMessage('rover-acrm__event-handling_header')),
            InputFactory::getLabelShort(Loc::getMessage('rover-acrm__agent-new_label'),
                Loc::getMessage('rover-acrm__agent-new_help', array(
                    '#new-count#' => StatusTable::getNewCount(),
                    '#err-count#' => StatusTable::getErrorCount()
                )), StatusTable::getActiveCount()),
            InputFactory::getNumber(self::INPUT__AGENT_COUNT, 3, $isDemo),
            InputFactory::getSelect(self::INPUT__EVENT_LOG_LIFETIME, [
                '365'   => Loc::getMessage(self::INPUT__EVENT_LOG_LIFETIME . '_365'),
                '183'   => Loc::getMessage(self::INPUT__EVENT_LOG_LIFETIME . '_183'),
                '92'    => Loc::getMessage(self::INPUT__EVENT_LOG_LIFETIME . '_92'),
                '31'    => Loc::getMessage(self::INPUT__EVENT_LOG_LIFETIME . '_31'),
            ], 365, false, false, null,
                Loc::getMessage(self::INPUT__EVENT_LOG_LIFETIME . '_help', [
                    '#count#' => StatusTable::getCount()
                ])),

            InputFactory::getSubmit(self::INPUT__HANDLE_NEW_EVENTS),
            InputFactory::getSubmit(self::INPUT__HANDLE_ERROR_EVENTS),
            // agent
            InputFactory::getHeader(Loc::getMessage('rover-acrm__agent-settings_header') . ($isDemo
                    ? ' ' . Loc::getMessage('rover-acrm__agent-settings_header_demo') : '')),
            InputFactory::getCheckbox(self::INPUT__AGENT, 'N', $isDemo),
            InputFactory::getCheckbox(self::INPUT__AGENT_HANDLE_ERRORS, 'N', $useAgents == 'N'),

            // alert/log
            InputFactory::getHeader(Loc::getMessage('rover-acrm__alert-log_header')),
            InputFactory::getCheckbox(self::INPUT__UNAVAILABLE_ALERT, 'N'),
            self::getInputLog(),
            InputFactory::getNumber(self::INPUT__LOG_MAX_SIZE, 1),
        );

		$inputs[] = InputFactory::getCustom('rover-acrm__add-integration', $options->isConnected()
                ? Loc::getMessage('rover-acrm__integration')
                : Loc::getMessage('rover-acrm__integration-no-connection'));

		return $inputs;
	}

    /**
     * @param Options $options
     * @return array|null
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function getPresetTab(Options $options)
	{
		if (!$options->isConnected())
			return null;

		try{
		    return array(
                'name'          => self::TAB__PRESET,
                'label'         => Loc::getMessage(self::TAB__PRESET . '_label'),
                'description'   => Loc::getMessage(self::TAB__PRESET . '_descr'),
                'preset'        => true,
                'inputs'        => self::getPresetInputs()
            );
        } catch (\Exception $e) {
		    $options->handleError($e);

            return array();
		}
	}

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function getPresetInputs()
	{
	    // formats
		$formats = array(
			Handler::FORMAT__HTML => Loc::getMessage(Handler::FORMAT__HTML . '_label'),
            Handler::FORMAT__TEXT => Loc::getMessage(Handler::FORMAT__TEXT . '_label')
        );

		//adv marks filter
        $advMarksFilter = array('all' => Loc::getMessage('rover-acrm__advertising-marks_all'));
        $advMarks       = AdvMarks::getFullDefaultList();

        foreach ($advMarks as $mark => $markLabel)
        {
            $markLabel = trim($markLabel);
            $markLabel = strlen($markLabel) ? $markLabel : $mark;

            $advMarksFilter[$mark] = $markLabel;
        }

		$inputs = array(
			InputFactory::getHeader(Loc::getMessage('header_settings_label')),
			InputFactory::getCheckbox(self::INPUT__CONNECTION_ENABLED),
        );

		// add sites, if sites > 1
        $sites = Main::getSites(array('empty' => null));
        if (count($sites) > 1)
            $inputs[] = InputFactory::getSelect(self::INPUT__SITES, $sites, array(), true);

		$inputs = array_merge($inputs, array(
			InputFactory::getSelect(self::INPUT__MAIN_USER, TabList::getUsers()),
			InputFactory::getText(self::INPUT__TAG),
            InputFactory::getSelect(self::INPUT__FORMAT, $formats, Handler::FORMAT__HTML),
			InputFactory::getCheckbox(self::INPUT__GROUP_NOTES, 'N'),
            InputFactory::getCheckbox(self::INPUT__IGNORE_SAME_HIT_EVENTS, 'N'),
            self::getFieldsHeader('common', 'advertising'),
			InputFactory::getSelect(self::INPUT__ADV_MARKS_FILTER, $advMarksFilter, false, true),
			InputFactory::getTextarea(self::INPUT__ADV_MARKS_CUSTOM_FILTER, '', 40, 5),
            InputFactory::getCheckbox(self::INPUT__SAVE_ADV_MARKS, 'N'),
            InputFactory::getCheckbox(self::INPUT__REMOVE_GA_VERSION, 'N'),
            self::getFieldsHeader('common', 'unsorted'),
            self::getInputUnsorted(),
            InputFactory::getText(self::INPUT__UNSORTED_NAME, '', !Account::getInstance()->isUnsortedOn()),
            self::getObjectHeader(Rest\Lead::getType()),
            InputFactory::getCheckbox(self::INPUT__LEAD_CREATE),
            InputFactory::getText(self::INPUT__LEAD_NAME),
        ));

        $pipelines  = TabList::getPipelines();
        $inputs[]   = is_array($pipelines) && count($pipelines) > 1
            ? InputFactory::getSelectGroup(Tabs::INPUT__LEAD_STATUS, TabList::getGroupedStatuses())
            : InputFactory::getSelect(self::INPUT__LEAD_STATUS, TabList::getStatuses());

        //visitor_uid
        $inputs[]   = InputFactory::getCheckbox(self::INPUT__LEAD_VISITOR_UID, 'N');

        // add empty mapping sub tab control
        $inputs[] = InputFactory::getSubTabControl(self::INPUT__MAPPING_SUBTABCONROL . Rest\Lead::getType());


        //duplicates
        $inputs[] = Tabs::getFieldsHeader(Rest\Lead::getType(), 'duplicates');
        $inputs[] = InputFactory::getCheckbox(self::INPUT__LEAD_DUPLICATE_CONTROL, 'N', false, Loc::getMessage('duplicate__control-label'), Loc::getMessage('duplicate__control-lead-help'));
    //    $inputs[] = InputFactory::getSelect(self::INPUT__LEAD_DUPLICATE_PIPELINE_FILTER, array_merge(['' => Loc::getMessage('rover-acrm__advertising-marks_all')], $pipelines), '', true);
        $inputs[] = self::getInputLeadDuplicatesFields();
        $inputs[] = InputFactory::getCheckbox(self::INPUT__LEAD_DUPLICATE_CONTACT_FILTER);
        $inputs[] = InputFactory::getSelect(self::INPUT__LEAD_DUPLICATE_STATUS_FILTER, array_merge(['' => Loc::getMessage('rover-acrm__advertising-marks_all')], TabList::getStatusesWithPipelines()), '', true);
        $inputs[] = self::getDuplicateLogic(self::INPUT__LEAD_DUPLICATE_LOGIC);
        $inputs[] = self::getDuplicateAction(self::INPUT__LEAD_DUPLICATE_ACTION);
        $inputs[] = InputFactory::getCheckbox(self::INPUT__LEAD_DUPLICATE_UPDATE_NAME, 'Y');

		return $inputs;
	}

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected static function getInputUnsorted()
    {
        $unsortedInput = InputFactory::getCheckbox(self::INPUT__UNSORTED_CREATE, 'N');

        if (!Rest\Account::getInstance()->isUnsortedOn()){
            $unsortedInput['disabled']  = true;
            $unsortedInput['help']      = Loc::getMessage(self::INPUT__UNSORTED_CREATE . '_disabled_help');
        }

        return $unsortedInput;
    }

    /**
     * @param        $object
     * @param string $type
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getFieldsHeader($object, $type = 'auto')
	{
        $label  = self::getFieldsLabel($object, $type);
        $help   = self::getFieldsHelp($object, $type);
        if (strlen($help))
            $help = ' (' . $help . ')';

        $header = Loc::getMessage('rover-acrm__header-template', array(
            '#label#'   => $label,
            '#help#'    => $help,
        ));

		return InputFactory::getCustom('fields-settings-' . $object . '-' . trim($type), $header);
	}

    /**
     * @param        $object
     * @param string $type
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getFieldsLabel($object, $type = 'auto')
    {
        return Loc::getMessage('rover-acrm__header-fields-' . trim($type) . '-' . $object);
    }

    /**
     * @param        $object
     * @param string $type
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getFieldsHelp($object, $type = 'auto')
    {
        return Loc::getMessage('rover-acrm__header-fields-' . trim($type) . '-' . $object . '-help');
    }

	/**
	 * @param $object
	 * @return array
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	public static function getObjectHeader($object)
	{
		return InputFactory::getHeader(Loc::getMessage('rover-acrm__header-' . $object));
	}

    /**
     * @param      $restType
     * @param      $name
     * @param null $label
     * @param null $help
     * @param null $postInput
     * @param bool $filterCustomSelectBoxes
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getInputSelectCustomFields($restType, $name, $label = null, $help = null, $postInput = null, $filterCustomSelectBoxes = true)
	{
		switch ($restType)
        {
			case Rest\Contact::getType():
				$firstElement           = InputFactory::getText(Rest\Contact::FIELD__NAME);
				$firstElement['label']  = Loc::getMessage(Rest\Contact::FIELD__NAME . '_' . $restType . '_label');
				break;
			case Rest\Company::getType():
				$firstElement           = InputFactory::getText(Rest\Company::FIELD__NAME);
                $firstElement['label']  = Loc::getMessage(Rest\Company::FIELD__NAME . '_' . $restType . '_label');
				break;
			case Rest\Lead::getType():
				$firstElement = InputFactory::getNumber(Tabs::INPUT__LEAD_PRICE);
				break;
			case Rest\Task::getType():
				$firstElement = Tabs::getInputTaskText();
				break;
			default:
				throw new ArgumentOutOfRangeException('object');
		}

		$select = InputFactory::getSelect($name, TabList::getOptions($restType, $firstElement, $filterCustomSelectBoxes), null, false, false, $label, $help);

		if (strlen($postInput))
		    $select['postInput'] = $postInput;

		return $select;
	}

    /**
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getInputLeadDuplicatesFields()
    {
        $fields = self::getInputSelectCustomFields(Rest\Lead::getType(),
            self::INPUT__LEAD_DUPLICATE_FIELDS,
            Loc::getMessage('duplicate__fields-label'),
            Loc::getMessage('duplicate__fields-help')
        );
        $fields['multiple'] = true;
        unset($fields['options'][Rest\Note::getType()]);

        $fields['options'][self::INPUT__LEAD_NAME]      = Loc::getMessage(self::INPUT__LEAD_NAME . '_label');
        $fields['options'][self::INPUT__LEAD_STATUS]    = Loc::getMessage(self::INPUT__LEAD_STATUS . '_label');

        return $fields;
    }

    /**
     * @param        $name
     * @param string $unsortedStatus
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getDuplicateAction($name, $unsortedStatus = 'N')
    {
        $options = array(
            Duplicate::ACTION__ADD_NOTE => Loc::getMessage(Duplicate::ACTION__ADD_NOTE . '_label'),
            Duplicate::ACTION__COMBINE  => Loc::getMessage(Duplicate::ACTION__COMBINE . '_label'),
            Duplicate::ACTION__SKIP     => Loc::getMessage(Duplicate::ACTION__SKIP . '_label'),
        );

        return InputFactory::getSelect(
            $name,
            $options,
            Duplicate::ACTION__ADD_NOTE,
            false,
            $unsortedStatus == 'Y',
            Loc::getMessage('duplicate__action-label'),
            $unsortedStatus == 'Y' ? Loc::getMessage('duplicate__unsorted-help') : null);
    }

    /**
     * @param        $name
     * @param string $unsortedStatus
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getDuplicateLogic($name, $unsortedStatus = 'N')
    {
        $options = array(
            Duplicate::LOGIC__AND => Loc::getMessage(Duplicate::LOGIC__AND . '_label'),
            Duplicate::LOGIC__OR  => Loc::getMessage(Duplicate::LOGIC__OR . '_label'),
        );

        return InputFactory::getSelect(
            $name,
            $options,
            Duplicate::LOGIC__AND,
            false,
            $unsortedStatus == 'Y',
            Loc::getMessage('duplicate__logic-label'),
            $unsortedStatus == 'Y' ? Loc::getMessage('duplicate__unsorted-help') : null);
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getInputSubDomain()
	{
        $text = InputFactory::getText(self::INPUT__SUB_DOMAIN);

		$text['preInput']   = 'https://';
		$text['postInput']  = '.amocrm.ru';
		$text['size']       = '20';

		return $text;
	}

    /**
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getInputLog()
    {
        $dependence = new Dependence();
        $logEnabled = $dependence->checkDir(Log::getDir())->getResult();
        $logInput   = InputFactory::getCheckbox(self::INPUT__LOG_ENABLED, 'N', !$logEnabled);
        $logInput['help'] = $logEnabled
            ? str_replace(array('#note-file-size#','#error-file-size#'),
                array(Log::getFileSize(Log::FILE__NOTE), Log::getFileSize(Log::FILE__ERROR)),
                $logInput['help'])
            : Loc::getMessage(self::INPUT__LOG_ENABLED . '_disabled_help');

        return $logInput;
    }

    /**
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getInputTaskText()
	{
		return InputFactory::getTextarea(Tabs::INPUT__TASK_TEXT);
	}
}