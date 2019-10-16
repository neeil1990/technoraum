<?php
use \Rover\AmoCRM\Config\Tabs;
use \Rover\AmoCRM\Model\Rest;
use \Rover\AmoCRM\Entity\Handler;
use \Rover\AmoCRM\Model\EventType;
use \Rover\AmoCRM\Helper\Duplicate;
/**
 * Main tab
 */
$MESS[Tabs::TAB__MAIN . '_label'] = 'Basic settings';
$MESS[Tabs::TAB__MAIN . '_descr'] = 'Module setup';

$MESS[Tabs::TAB__PRESET . '_label'] = 'Integration rule';
$MESS[Tabs::TAB__PRESET . '_descr'] = 'Integration settings';

$MESS['banner'] = '<div class="logo_wrapper">
						<div class="logo">
							<a target="_blank" href="http://www.amocrm.com/"><img src="http://www.amocrm.ru/design/images/logo.png" width="172" height="35" alt="amoCRM"></a>
						</div>
						<div class="logo_text">The module allows you to send data to the <a style="color: #fff;" target="_blank" href="http://www.amocrm.com/">amoCRM</a> from web-forms and post-events.<br>
		For operation of the module must connect to amoCRM</div>
					</div>
					<style>
						.logo_wrapper {background:#0581c5;height:56px;margin:0 0 5px;}
						.logo {float:left;margin:10px 15px;}
						.logo a img{border:none;}
						.logo_text {color:#fff;margin:15px 0 0;float:left;}
					</style>';

$MESS[Tabs::INPUT__ENABLED . '_label']    = 'Integration enabled';
$MESS[Tabs::INPUT__ENABLED . '_help']       = 'General inclusion / disabling integration';
$MESS[Tabs::INPUT__LOG_ENABLED . '_label']  = 'Logging enabled';
$MESS[Tabs::INPUT__LOG_ENABLED . '_help']   = 'The log will be maintained in the files <b>note.log</b> and <b>error.log</b> in the /upload/rover.amocrm/log/';
$MESS[Tabs::INPUT__LOG_ENABLED . '_disabled_help']   = 'Folder /upload/rover.amocrm/log/ is not writable';
$MESS[Tabs::INPUT__LOG_MAX_SIZE . '_label']     = 'Maximum size of log files, MB (0 - no limit)';
$MESS[Tabs::INPUT__LOG_MAX_SIZE . '_help']      = 'When the maximum size is reached, the log files will be reset';
$MESS[Tabs::INPUT__SUB_DOMAIN . '_label']     = 'Your subdomain in amoCRM';
$MESS[Tabs::INPUT__SUB_DOMAIN . '_help']     = '<strong><u>subdomain</u></strong>.amocrm.ru';
$MESS[Tabs::INPUT__LOGIN . '_label']      = 'Email (login)';
$MESS[Tabs::INPUT__HASH . '_label']        = "The key for the authorization API";
$MESS[Tabs::INPUT__HASH . '_help']         = 'It located in the settings section of your AmoCRM API.<br>Please note that the api key after changing password <span style="text-decoration: underline">reset and set a new</span>.';
$MESS[Tabs::INPUT__CONNECT . '_label']    = 'Connect to amoCRM';
$MESS[Tabs::INPUT__CONNECT . '_default']  = 'Connect';
$MESS['rover-acrm__alert-log_header']       = 'Alert/log';
$MESS[Tabs::INPUT__UNAVAILABLE_ALERT . '_label']      = 'Send email to administrator when amoCRM is unavailable';
$MESS[Tabs::INPUT__UNAVAILABLE_ALERT . '_help']      = 'Messages will be sent no more often than once per hour.<br>Edit <a href="/bitrix/admin/type_edit.php?EVENT_NAME=' . EventType::TYPE__AMOCRM_UNAVAILABLE .'&lang=' . LANGUAGE_ID . '">post event</a>.';

$MESS['rover-acrm__agent-settings_header']  = 'Agent Settings';
$MESS['rover-acrm__agent-settings_header_demo']  = '(not available in demo version)';
$MESS[Tabs::INPUT__AGENT . '_label']       = 'Use agents';
$MESS[Tabs::INPUT__AGENT . '_help']        = 'Use integration by agents.';
$MESS[Tabs::INPUT__AGENT_COUNT . '_label']  = 'Number of events processed per pass';
$MESS[Tabs::INPUT__AGENT_COUNT . '_help']   = 'Setting the value to more than 5 can seriously slow down the work and / or cause an error in exceeding the execution time of the script.';
$MESS[Tabs::INPUT__AGENT_HANDLE_ERRORS . '_label']  = 'Re-process erroneous events';
$MESS[Tabs::INPUT__AGENT_HANDLE_ERRORS . '_help']   = 'Works only when using agents.';
$MESS['rover-acrm__event-handling_header']  = 'Event handling';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_label']   = 'Event log lifetime';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_help']    = '<a target="_blank" href="/bitrix/admin/perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=rv_amocrm_status">#count#</a> events';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_365']     = '1 year';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_183']     = '6 months';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_92']      = '3 months';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_31']      = '1 month';
$MESS[Tabs::INPUT__HANDLE_NEW_EVENTS . '_label']    = 'Handle new events';
$MESS[Tabs::INPUT__HANDLE_NEW_EVENTS . '_default']  = 'Handle';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_label']  = 'Handle error events';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_help']   = 'If the error is resolved, the event can be re-processed.<br>When logging is enabled, errors are written to the <b>error.log</ b> file. <a href="/bitrix/admin/fileman_admin.php?path=%2Fupload%2Frover.amocrm%2Flog&show_perms_for=0&lang=' . LANGUAGE_ID . '" target="_blank">Open the folder with logs</a>.';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_default']= 'Handle';

$MESS['rover-acrm__agent-new_label']        = 'Queue events';
$MESS['rover-acrm__agent-new_help']         = 'New: #new-count#, with errors: #err-count#';

$MESS['rover-acrm__integration-no-connection']  = '<p style="padding: 15px; border: 1px solid #ccc; text-align: center;">Configure a connection to amoCRM to start adding integration rules</p>';
$MESS['rover-acrm__integration']                = '<p style="padding: 15px; border: 1px solid #ccc; text-align: center;"><a href="/bitrix/admin/rover-acrm__preset-list.php?lang=' . LANGUAGE_ID . '"><b>Add and edit integration rules</b></a>.</p>';

/**
 * Pareset tab
 */
// settings
$MESS['header_settings_label']                      = 'Common settings';
$MESS[Tabs::INPUT__CONNECTION_ENABLED . '_label']    = 'Connection enabled';
$MESS[Tabs::INPUT__CONNECTION_ENABLED . '_help']    = 'Enable/disable the current integration rule<br>Type: #type#';
$MESS[Tabs::INPUT__SITES . '_label']        = 'Rule\'s sites';
$MESS[Tabs::INPUT__SITES . '_help']         = 'If is empty, rule works on all sites';
$MESS[Tabs::INPUT__MAIN_USER . '_label']    = 'Responsible user';
$MESS[Tabs::INPUT__MAIN_USER . '_help']     = 'This user will be appointed responsible for the created leads, contacts and companies. He will also be given the task to be created.';
$MESS[Tabs::INPUT__TAG . '_label']          = 'Tags';
$MESS[Tabs::INPUT__TAG . '_help']           = 'adds to all created objects. <attr title="#legend#">Standart placeholders are available</attr>.';
$MESS[Tabs::INPUT__FORMAT . '_label']       = 'Format';
$MESS[Tabs::INPUT__FORMAT . '_help']        = '"html" - values will be sended "as is",<br>"text" - all tags will be stripped, line breaks will be saved';
$MESS[Handler::FORMAT__HTML . '_label']     = 'html';
$MESS[Handler::FORMAT__TEXT . '_label']     = 'text';
$MESS[Tabs::INPUT__GROUP_NOTES . '_label']  = 'Group notes';
$MESS[Tabs::INPUT__GROUP_NOTES . '_help']   = 'Instead of a lot of notes, one note with sum of information will be added to the lead and contact.';
$MESS[Tabs::INPUT__IGNORE_SAME_HIT_EVENTS . '_label'] = 'Ignore duplication of events on one hit';
$MESS[Tabs::INPUT__IGNORE_SAME_HIT_EVENTS . '_help']  = 'Helps you get rid of duplication when integrating a post event by sending a web form and in some other cases.';

$MESS['rover-acrm__header-fields-advertising-common']   = 'Advertising campaign marks';
$MESS['rover-acrm__header-fields-advertising-common-help']   = 'to update the list in the entity settings, save the integration rule';
$MESS[Tabs::INPUT__ADV_MARKS_FILTER . '_label']         = 'Advertising marks filter';
$MESS[Tabs::INPUT__ADV_MARKS_FILTER . '_help']          = 'Only the selected marks will be transferred (if any)<br>You can specify the missing labels in the box below ?';
$MESS[Tabs::INPUT__ADV_MARKS_CUSTOM_FILTER . '_label'] = 'Advertising marks custom filter';
$MESS[Tabs::INPUT__ADV_MARKS_CUSTOM_FILTER . '_help']  = 'each mark in new line';
$MESS[Tabs::INPUT__SAVE_ADV_MARKS . '_label']           = 'Save marks when navigating between pages';
$MESS[Tabs::INPUT__SAVE_ADV_MARKS . '_help']            = 'Saves the above marks throughout the whole session with the site.';
$MESS[Tabs::INPUT__REMOVE_GA_VERSION . '_label']        = 'Remove the version information api from the _ga mark';
$MESS[Tabs::INPUT__REMOVE_GA_VERSION . '_help']         = 'Deletes values like <b>GA1.2.</b>, <b>GA1.3.</b>, and so on. from the beginning of the label. Example: <b>GA1.2.</b>1054613344.1468150257 ? 1054613344.1468150257';

$MESS['rover-acrm__header-fields-unsorted-common']      = 'ЂUnsortedї';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_label']     = 'Add to Unsorted';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_help']     = 'Leads and Contacts will be added to "Unsorted". Task will be not create;';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_disabled_help']      = '"Unsorted" disabled on your account';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_label'] = 'ЂUnsortedї lead\'s name';

$MESS[Duplicate::ACTION__ADD_NOTE . '_label']  = 'Add a note with reference to the original';
$MESS[Duplicate::ACTION__COMBINE . '_label']   = 'Update original with duplicate data';
$MESS[Duplicate::ACTION__SKIP . '_label']      = 'Do not create duplicate';

$MESS[Duplicate::LOGIC__AND . '_label']    = 'of all selected fields';
$MESS[Duplicate::LOGIC__OR . '_label']     = 'any of the selected fields';

$MESS['duplicate__unsorted-help']       = 'This option is not available when the option "Add to "Unsorted" is enabled';
$MESS['duplicate__fields-label']        = 'Search by fields';
$MESS['duplicate__fields-help']         = 'At least one field must be selected';
$MESS['duplicate__action-label']        = 'If a duplicate is found';
$MESS['duplicate__logic-label']         = 'Search by match';
$MESS['duplicate__control-label']       = 'Control duplicates';
$MESS['duplicate__control-lead-help']   = 'Inclusion of this function can slow down the response of the system with a large number of already existing leads. To solve this problem, it is recommended to defer integration to the agent. At the same time, agents on the site <u>must</ u> have to be <a target="_blank" href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943">translated on cron</a>.';
$MESS['duplicate__control-contact-help']= 'Inclusion of this function can slow down the response of the system with a large number of already existing contacts. To solve this problem, it is recommended to defer integration to the agent. At the same time, agents on the site <u>must</ u> have to be <a target="_blank" href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943">translated on cron</a>.';

// lead
$MESS[Tabs::INPUT__LEAD_CREATE . '_label']      = 'Create lead';
$MESS[Tabs::INPUT__LEAD_CREATE . '_help']       = 'The lead will be connected to the contact, if it is also created';
$MESS[Tabs::INPUT__LEAD_NAME . '_label']        = 'Name';
$MESS[Tabs::INPUT__LEAD_PRICE . '_label']       = 'Budget';
$MESS[Tabs::INPUT__LEAD_STATUS . '_label']      = 'Status';
$MESS[Tabs::INPUT__LEAD_STATUS . '_unsorted_help']  = 'Lead will be added to Ђunsortedї';

$MESS[Tabs::INPUT__LEAD_VISITOR_UID . '_label']     = 'Add visitor UID (visitor_uid)';
$MESS[Tabs::INPUT__LEAD_VISITOR_UID . '_help']      = 'Allows you to track the visitor in the autopipelines. Read more in <a href="https://www.amocrm.com/developers/content/digital_pipeline/site_visit" target="_blank">documentation of amoCRM</a>';

$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Lead::getType() . '_label']        = 'Fields mapping';
$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Lead::getType() . '_default']      = 'Specify the fields that you want to transfer to the lead';

$MESS[Tabs::INPUT__LEAD_DUPLICATE_CONTACT_FILTER . '_label']= 'Search only for contact\'s leads';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_CONTACT_FILTER . '_help'] = 'Limits the scope of duplicate search by the leads of the created contact. For the created contact, the duplicate control in modes Ђ' . $MESS[Duplicate::ACTION__COMBINE . '_label'] . 'ї or Ђ' . $MESS[Duplicate::ACTION__SKIP . '_label'] . 'ї.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_PIPELINE_FILTER . '_label']   = 'Search in pipelines';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_PIPELINE_FILTER . '_help']    = 'Limits the scope of duplicate search by leads in selected pipelines.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_STATUS_FILTER . '_label']     = 'Search in statuses';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_STATUS_FILTER . '_help']      = 'Limits the scope of duplicate search by leads in selected statuses.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_UPDATE_NAME . '_label']       = 'Update duplicate name';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_UPDATE_NAME . '_help']        = 'Works if action Ђ' . $MESS[Duplicate::ACTION__COMBINE . '_label'] . 'ї is selected';

// contact
$MESS[Tabs::INPUT__CONTACT_CREATE . '_label']   = 'Create contact';
$MESS[Tabs::INPUT__CONTACT_CREATE . '_help']    = '';
$MESS[Rest\Contact::FIELD__NAME . '_' . Rest\Contact::getType() . '_label']    = 'Contact\'s name';

// company
$MESS[Tabs::INPUT__COMPANY_CREATE . '_label']   = 'Create company';
$MESS[Tabs::INPUT__COMPANY_CREATE . '_help']    = '';
$MESS[Rest\Company::FIELD__NAME . '_' . Rest\Company::getType() . '_label']  = 'Company\'s name';

// task
$MESS[Tabs::INPUT__TASK_CREATE . '_label']          = 'Create task';
$MESS[Tabs::INPUT__TASK_CREATE . '_help']           = 'The contact or transaction must be tied to the task. If a contact or transaction is not created, the task will not be created either';
$MESS[Tabs::INPUT__TASK_ELEMENT_TYPE . '_label']    = 'In case of creation contact and transaction, the task is tied to';
$MESS[Tabs::INPUT__TASK_ELEMENT_TYPE . '_help']     = 'The task will be bound to the selected entity if it exists. Otherwise, the first existing one will be chosen: lead, contact, company';
$MESS[Rest\Contact::getType() . '_label']           = 'contact';
$MESS[Rest\Lead::getType() . '_label']              = 'lead';
$MESS[Rest\Company::getType() . '_label']           = 'company';
$MESS[Tabs::INPUT__TASK_TYPE . '_label']    = 'Type';
$MESS[Tabs::INPUT__TASK_TEXT . '_label']    = 'Text';
$MESS[Tabs::INPUT__TASK_TEXT . '_default']  = "Task from integration module.\nFields:\n#FIELDS#";
$MESS[Tabs::INPUT__TASK_TEXT . '_help']     = '<abbr title="#legend#">Standart placeholders</abbr> & #FIELDS# (task\' fields values) are available';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_label']= 'Deadline';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_help']= 'Please, make sure that the time zones of your site and amoCrm are synchronized';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_now']  = 'At the time of setting the task';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_day_end']= 'At 11:59 pm the current day';

// common
$MESS['rover-acrm__remove-preset_label']        = 'Remove connection';
$MESS[Tabs::INPUT__REMOVE_PRESET . '_label'] = 'Remove connection';
$MESS[Tabs::INPUT__REMOVE_PRESET . '_popup'] = 'Are you sure you want to delete the connection?';

$MESS['rover-acrm__advertising-marks_all']      = '(all)';

/** Auto */
$MESS['rover-acrm__header-' . Rest\Lead::getType()]       = 'Lead';
$MESS['rover-acrm__header-' . Rest\Contact::getType()]    = 'Contact';
$MESS['rover-acrm__header-' . Rest\Company::getType()]    = 'Company';
$MESS['rover-acrm__header-' . Rest\Task::getType()]       = 'Task';
$MESS['rover-acrm__header-' . Rest\Task::getType() . '-disabled'] = 'Task (disabled, because the option "Add to "Unsorted" is selected")';

$MESS['rover-acrm__header-template']     = '<p style="text-align: center; color: #333; font-weight: bold; border-bottom: 2px solid #e0e8ea; padding-bottom: 5px; margin-bottom: 0">#label#</p><small style="display: block; text-align: center; color: #777; margin-bottom: 10px;">#help#</small>';

$MESS['rover-acrm__header-fields-custom-' . Rest\Lead::getType()]    = 'Custom lead\'s fields';
$MESS['rover-acrm__header-fields-custom-' . Rest\Contact::getType()] = 'Custom contact\'s fields';
$MESS['rover-acrm__header-fields-custom-' . Rest\Company::getType()]  = 'Custom company\'s fields';
$MESS['rover-acrm__header-fields-custom-' . Rest\Task::getType()]    = 'Custom task\'s fields';
$MESS['rover-acrm__header-fields-auto-' . Rest\Lead::getType()]    = 'Automatic lead\'s fields';
$MESS['rover-acrm__header-fields-auto-' . Rest\Contact::getType()] = 'Automatic contact\'s fields';
$MESS['rover-acrm__header-fields-auto-' . Rest\Company::getType()]    = 'Automatic company\'s fields';
$MESS['rover-acrm__header-fields-auto-' . Rest\Task::getType()]    = 'Automatic task\'s fields';

$MESS['rover-acrm__header-fields-duplicates-' . Rest\Contact::getType()]   = 'Duplicates control';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Company::getType()] = 'Duplicates control';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Lead::getType()]    = 'Duplicates control';

$MESS['rover-acrm__header-fields-custom-' . Rest\Lead::getType() . '-help']     = 'left Ч lead field in amoCRM, right Ч set value';
$MESS['rover-acrm__header-fields-custom-' . Rest\Contact::getType() . '-help']  = 'left Ч contact field in amoCRM, right Ч set value';
$MESS['rover-acrm__header-fields-custom-' . Rest\Company::getType() . '-help']  = 'left Ч company field in amoCRM, right Ч set value';
$MESS['rover-acrm__header-fields-custom-' . Rest\Task::getType() . '-help']     = 'left Ч task field in amoCRM, right - set value';
$MESS['rover-acrm__header-fields-auto-' . Rest\Lead::getType() . '-help']            = 'on the left Ч the value of the event field, on the right Ч the lead field in amoCRM';
$MESS['rover-acrm__header-fields-auto-' . Rest\Contact::getType() . '-help']         = 'on the left Ч the value of the event field, on the right Ч the contact field in amoCRM';
$MESS['rover-acrm__header-fields-auto-' . Rest\Company::getType() . '-help']         = 'on the left Ч the value of the event field, on the right Ч the company field in amoCRM';
$MESS['rover-acrm__header-fields-auto-' . Rest\Task::getType() . '-help']            = 'on the left Ч the value of the event field, on the right Ч the task field in amoCRM';
/*$MESS['rover-acrm__header-fields-duplicates-' . Rest\Contact::getType() . '-help']   = 'Ќастройка поиска дубликатов дл€ нового контакта';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Company::getType() . '-help']   = 'Ќастройка поиска дубликатов дл€ новой компании';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Lead::getType() . '-help']      = 'Ќастройка поиска дубликатов дл€ новой сделки';
*/

$MESS['rover-acrm__header-fields-add-' . Rest\Task::getType()]    = 'Additional fields for task';
$MESS['rover-acrm__header-fields-add-' . Rest\Contact::getType()] = 'Additional fields for contact';
$MESS['rover-acrm__header-fields-add-' . Rest\Company::getType()] = 'Additional fields for company';
$MESS['rover-acrm__header-fields-add-' . Rest\Lead::getType()]    = 'Additional fields for lead';

$MESS['rover-acrm__header-fields-add-' . Rest\Task::getType(). '-help']    = 'on the left Ч the value of the additional field of the event, on the right Ч the task field in amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Contact::getType(). '-help'] = 'on the left Ч the value of the additional field of the event, on the right Ч the contact field in amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Company::getType(). '-help'] = 'on the left Ч the value of the additional field of the event, on the right Ч the company field in amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Lead::getType(). '-help']    = 'on the left Ч the value of the additional field of the event, on the right Ч the lead field in amoCRM';

$MESS['rover-acrm__header-fields-adv-' . Rest\Task::getType()]    = 'Marks of advertising campaigns and analytics for the task';
$MESS['rover-acrm__header-fields-adv-' . Rest\Contact::getType()] = 'Marks of advertising campaigns and analytics for the contact';
$MESS['rover-acrm__header-fields-adv-' . Rest\Company::getType()] = 'Marks of advertising campaigns and analytics for the company';
$MESS['rover-acrm__header-fields-adv-' . Rest\Lead::getType()]    = 'Marks of advertising campaigns and analytics for the lead';
