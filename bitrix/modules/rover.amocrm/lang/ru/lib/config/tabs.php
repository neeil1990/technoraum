<?php
use \Rover\AmoCRM\Config\Tabs;
use \Rover\AmoCRM\Model\Rest;
use \Rover\AmoCRM\Entity\Handler;
use \Rover\AmoCRM\Model\EventType;
use \Rover\AmoCRM\Helper\Duplicate;
/**
 * Main tab
 */
$MESS[Tabs::TAB__MAIN . '_label'] = '�������� ���������';
$MESS[Tabs::TAB__MAIN . '_descr'] = '��������� ������';

$MESS[Tabs::TAB__PRESET . '_label'] = '������� ����������';
$MESS[Tabs::TAB__PRESET . '_descr'] = '��������� ������� ����������';

$MESS['banner'] = '<div class="logo_wrapper">
						<div class="logo">
							<a target="_blank" href="http://www.amocrm.ru/"><img src="http://www.amocrm.ru/design/images/logo.png" width="172" height="35" alt="amoCRM"></a>
						</div>
						<div class="logo_text">������ ��������� ���������� ������ � <a style="color: #fff;" target="_blank" href="http://www.amocrm.ru/">amoCRM</a> �� ���-���� � �������� �������.<br>
		��� ������ ������ ���������� ������������ � amoCRM</div>
					</div>
					<style>
						.logo_wrapper {background:#0581c5;height:56px;margin:0 0 5px;}
						.logo {float:left;margin:10px 15px;}
						.logo a img{border:none;}
						.logo_text {color:#fff;margin:15px 0 0;float:left;}
					</style>';

$MESS[Tabs::INPUT__ENABLED . '_label']      = '���������� ��������';
$MESS[Tabs::INPUT__ENABLED . '_help']       = '���������/���������� <u>����</u> ���������� �� �����';
$MESS[Tabs::INPUT__LOG_ENABLED . '_label']  = '����������� ��������';
$MESS[Tabs::INPUT__LOG_ENABLED . '_help']   = '��� ����� ������� � ������ <b>note.log</b> (������ #note-file-size#) � <b>error.log</b> (������ #error-file-size#) � ����� <a href="/bitrix/admin/fileman_admin.php?path=%2Fupload%2Frover.amocrm%2Flog&show_perms_for=0" target="_blank">/upload/rover.amocrm/log/</a>';
$MESS[Tabs::INPUT__LOG_ENABLED . '_disabled_help']   = '����� /upload/rover.amocrm/log/ ���������� ��� ������';
$MESS[Tabs::INPUT__LOG_MAX_SIZE . '_label']     = '������������ ������ ������ ����, �� (0 - ��� �����������)';
$MESS[Tabs::INPUT__LOG_MAX_SIZE . '_help']      = '��� ���������� ������������� �������, ���� ����� ������';
$MESS[Tabs::INPUT__SUB_DOMAIN . '_label']   = '��� �������� � amoCRM';
$MESS[Tabs::INPUT__SUB_DOMAIN . '_help']    = '���������� ������ ��� �������� 3�� ������ (��� https:// � .amocrm.ru).<br>������: https://<u>��������</u>.amocrm.ru - ���� ������ <u>��������</u>.';
$MESS[Tabs::INPUT__LOGIN . '_label']        = 'Email (�����)';
$MESS[Tabs::INPUT__HASH . '_label']         = "���� ��� ����������� � API";
$MESS[Tabs::INPUT__HASH . '_help']          = '��������� � ���������� ����� amoCRM � ������� API.<br>�������� ��������, ��� ����� ����� ������, <span style="text-decoration: underline">���� ������������ � ��������������� �����</span>.';
$MESS[Tabs::INPUT__CONNECT . '_label']      = '������������ � amoCRM';
$MESS[Tabs::INPUT__CONNECT . '_default']    = '������������';
$MESS['rover-acrm__alert-log_header']       = '����������/�����������';
$MESS[Tabs::INPUT__UNAVAILABLE_ALERT . '_label']      = '���������� ������ �������������� � ����� ������� � ������ �������';
$MESS[Tabs::INPUT__UNAVAILABLE_ALERT . '_help']      = '��������� ����� ��������� �� ���� ���� � ���.<br>������� � �������������� <a href="/bitrix/admin/type_edit.php?EVENT_NAME=' . EventType::TYPE__AMOCRM_UNAVAILABLE .'&lang=' . LANGUAGE_ID . '">��������� �������</a>.';

$MESS['rover-acrm__agent-settings_header']  = '��������� ������';
$MESS['rover-acrm__agent-settings_header_demo']  = '(���������� � ����-������)';
$MESS[Tabs::INPUT__AGENT . '_label']        = '�������� ���������� �� ������';
$MESS[Tabs::INPUT__AGENT . '_help']         = '���������� <b>�����</b> ������� ����� ������������� � �������������� ��� � 5 �����. �������� ������ ����� � ������ ����������� ������� ��� ������� <a target="_blank" href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943">�������� ������� �� cron</a>..';
$MESS[Tabs::INPUT__AGENT_COUNT . '_label']  = '���������� �������, �������������� �� ���� ������';
$MESS[Tabs::INPUT__AGENT_COUNT . '_help']   = '��������� �������� ����� 5 ����� �������� ��������� ������ �/��� ������� ������ ���������� ������� ���������� �������.';
$MESS[Tabs::INPUT__AGENT_HANDLE_ERRORS . '_label']  = '�������� ������������ ��������� �������';
$MESS[Tabs::INPUT__AGENT_HANDLE_ERRORS . '_help']   = '�������� ������ ��� ������������� �������.';
$MESS['rover-acrm__event-handling_header']  = '��������� �������';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_label']   = '����� �������� ������� � �������';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_help']    = '����� <a target="_blank" href="/bitrix/admin/perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=rv_amocrm_status">#count#</a> ������� � �������. ������� ���������� ��� � �����. �������������� � ������� � �������� �� ���������';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_365']     = '1 ���';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_183']     = '6 �������';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_92']      = '3 ������';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_31']      = '1 �����';
$MESS[Tabs::INPUT__HANDLE_NEW_EVENTS . '_label']    = '���������� ����� �������';
$MESS[Tabs::INPUT__HANDLE_NEW_EVENTS . '_default']  = '����������';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_label']  = '�������� ���������� ������� � ��������';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_help']   = '���� ������ ���������, �� ������� ����� ����������� ���������� ��������. <br>��� ���������� ����������� ������ ������������ � ���� <b>error.log</b>. <a href="/bitrix/admin/fileman_admin.php?path=%2Fupload%2Frover.amocrm%2Flog&show_perms_for=0&lang=' . LANGUAGE_ID . '" target="_blank">������� ����� � ������</a>.';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_default']= '����������';

$MESS['rover-acrm__agent-new_label']        = '������� � �������';
$MESS['rover-acrm__agent-new_help']         = '�� ��� �����: #new-count#, � ��������: #err-count#';

$MESS['rover-acrm__integration-no-connection']  = '<p style="padding: 15px; border: 1px solid #ccc; text-align: center;">��������� ����������� � amoCRM, ����� ������ ��������� ������� ����������</p>';
$MESS['rover-acrm__integration']                = '<p style="padding: 15px; border: 1px solid #ccc; text-align: center;"><a href="/bitrix/admin/rover-acrm__preset-list.php?lang=' . LANGUAGE_ID . '"><b>�������� ��� ��������������� ������� ����������</b></a>.</p>';

/**
 * Pareset tab
 */
// settings
$MESS['header_settings_label']                      = '����� ���������';
$MESS[Tabs::INPUT__CONNECTION_ENABLED . '_label']   = '���������� ��������';
$MESS[Tabs::INPUT__CONNECTION_ENABLED . '_help']    = '���������/���������� �������� ������� ����������<br>���: #type#';
$MESS[Tabs::INPUT__SITES . '_label']                = '�����, �� ������� �������� �������';
$MESS[Tabs::INPUT__SITES . '_help']                 = '���� �� ������ �� ���� ����, ������� ����� �������� �� ����';
$MESS[Tabs::INPUT__MAIN_USER . '_label']            = '������������� ������������';
$MESS[Tabs::INPUT__MAIN_USER . '_help']             = '���� ������������ ����� �������� ������������ �� ����������� ������, �������� � ��������. ��� �� ��� ����� ��������� ����������� ������.';
$MESS[Tabs::INPUT__TAG . '_label']                  = '����';
$MESS[Tabs::INPUT__TAG . '_help']                   = '����� ��������� � �������, ��������� � ���������. �������� <abbr title="#legend#">����������� ������������</abbr>.';
$MESS[Tabs::INPUT__FORMAT . '_label']               = '������';
$MESS[Tabs::INPUT__FORMAT . '_help']                = '"html" - �������� ����� �������� "��� ����",<br>"text" - ����� �������� ��� ���� � ����������� �������� �����';
$MESS[Handler::FORMAT__HTML . '_label']             = 'html';
$MESS[Handler::FORMAT__TEXT . '_label']             = '�����';
$MESS[Tabs::INPUT__GROUP_NOTES . '_label']          = '������������ ����������';
$MESS[Tabs::INPUT__GROUP_NOTES . '_help']           = '� ������, �������� � �������� ����� ��������� �� ������ ����������, ������������� ����� ����������.';
$MESS[Tabs::INPUT__IGNORE_SAME_HIT_EVENTS . '_label'] = '������������ ������������ ������� �� ����� ����';
$MESS[Tabs::INPUT__IGNORE_SAME_HIT_EVENTS . '_help']  = '�������� ���������� �� ������������ ��� ���������� ��������� ������� �������� ���-����� � � ��������� ������ �������.';

$MESS['rover-acrm__header-fields-advertising-common']   = '����� ��������� �������� � ���������';
$MESS['rover-acrm__header-fields-advertising-common-help']   = '��� ���������� ������ � ���������� ��������� ��������� ������� ����������';
$MESS[Tabs::INPUT__ADV_MARKS_FILTER . '_label']         = '������ �����';
$MESS[Tabs::INPUT__ADV_MARKS_FILTER . '_help']          = '����� ��������� ������ ��������� ����� (� ������ �� �������)<br>����������� ����� �� ������ ������� � ���� ���� ?';
$MESS[Tabs::INPUT__ADV_MARKS_CUSTOM_FILTER . '_label']  = '������������� ������ �����';
$MESS[Tabs::INPUT__ADV_MARKS_CUSTOM_FILTER . '_help']   = '������ ����� ������ ���� ������� � ����� ������. ������:<br><code>abd_defgh<br>_klm</code><br><br>��� ���������� ������ ��������� ����� � ���������� ������, ���������, �������� � ������, ���������� ��������� ������� ����������.';
$MESS[Tabs::INPUT__SAVE_ADV_MARKS . '_label']           = '��������� ����� ��� �������� ����� ����������';
$MESS[Tabs::INPUT__SAVE_ADV_MARKS . '_help']            = '��������� ��������� ���� ����� �� ���������� ���� ������ ������ � ������.';
$MESS[Tabs::INPUT__REMOVE_GA_VERSION . '_label']        = '������� �� ����� _ga ���������� � ������ api';
$MESS[Tabs::INPUT__REMOVE_GA_VERSION . '_help']         = '������� �������� ���� <b>GA1.2.</b>, <b>GA1.3.</b> � �.�. �� ������ �����. ������: <b>GA1.2.</b>1054613344.1468150257 ? 1054613344.1468150257';

$MESS['rover-acrm__header-fields-unsorted-common']  = '��������������';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_label']             = '��������� � ��������������';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_help']              = '������ � �������� ����� ��������� � �������������� �������� �� ����������, �� ���� ��������� �����������:<ul><li>������ ��������� ������, ���� ���� �� �������� ���������;</li><li>������ �� ���������;</li><li>��� ���������� �������� ����������, � ��������� � ������� ������ ����������� ������ �� ���;</li><li>�������� ������������ ���������� �� ������������.</li></ul>';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_disabled_help']     = '� ����� �������� �������������� ���������';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_label']        = '�������� ������ � ��������������';

$MESS[Duplicate::ACTION__ADD_NOTE . '_label']   = '�������� ���������� �� �������� �� ���������';
$MESS[Duplicate::ACTION__COMBINE . '_label']    = '�������� � ������������ ����� ������ �� �������� ����������';
$MESS[Duplicate::ACTION__SKIP . '_label']       = '������������ ����� ������ �� �������� ���������� ��� ����������';

$MESS[Duplicate::LOGIC__AND . '_label']    = '���� ��������� �����';
$MESS[Duplicate::LOGIC__OR . '_label']     = '������ �� ��������� �����';

$MESS['duplicate__unsorted-help']   = '���� �������� ���������� ��� ���������� ����� "��������� � ��������������"';
$MESS['duplicate__fields-label']    = '������ �� �����';
$MESS['duplicate__fields-help']     = '������ ���� ������� ���� �� ���� ����, ����� ����� ���������� �� �����';
$MESS['duplicate__action-label']    = '��� ����������� ���������';
$MESS['duplicate__logic-label']     = '������ �� ����������';
$MESS['duplicate__control-label']   = '�������������� ���������';
$MESS['duplicate__control-lead-help']    = '��������� ����� ���������� ����� ��������� ������ ������� ��� ������� ���������� ��� ������������ ������. ��� ������� ���� �������� ������������� �������� ���������� �� ������. ��� ���� ������ �� ����� <u>�����������</u> ������ ���� <a target="_blank" href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943">���������� �� cron</a>.';
$MESS['duplicate__control-contact-help']    = '��������� ����� ���������� ����� ��������� ������ ������� ��� ������� ���������� ��� ������������ ���������. ��� ������� ���� �������� ������������� �������� ���������� �� ������. ��� ���� ������ �� ����� <u>�����������</u> ������ ���� <a target="_blank" href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943">���������� �� cron</a>.';

// lead
$MESS[Tabs::INPUT__LEAD_CREATE . '_label']  = '��������� ������';
$MESS[Tabs::INPUT__LEAD_CREATE . '_help']   = '������ ����� ��������� � ��������, � ������ ��� ��������.<br>��� ���������� � �������������� ������ ����� ����������� ������, �������� ����� ����������.';
$MESS[Tabs::INPUT__LEAD_NAME . '_label']    = '��������';
$MESS[Tabs::INPUT__LEAD_PRICE . '_label']   = '������';
$MESS[Tabs::INPUT__LEAD_STATUS . '_label']  = '������';
$MESS[Tabs::INPUT__LEAD_STATUS . '_unsorted_help']  = '������ ����� ��������� � ��������������';

$MESS[Tabs::INPUT__LEAD_VISITOR_UID . '_label']     = '��������� ������������� ���������� (visitor_uid)';
$MESS[Tabs::INPUT__LEAD_VISITOR_UID . '_help']      = '��������� ����������� ���������� � ������������. ��������� � <a href="https://www.amocrm.ru/developers/content/digital_pipeline/site_visit" target="_blank">������������ amoCRM</a>';

$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Lead::getType() . '_label']      = '������� �����';
$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Contact::getType() . '_label']   = '������� �����';
$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Company::getType() . '_label']   = '������� �����';
$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Task::getType() . '_label']      = '������� �����';

$MESS[Tabs::INPUT__LEAD_DUPLICATE_CONTACT_FILTER . '_label']    = '���������� ������� ������ ������������� �������� ��������';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_CONTACT_FILTER . '_help']     = '�� ������� "�������" <u>�����������</u> ������ ���� ������� �������� ���������� � ������� <i>�' . $MESS[Duplicate::ACTION__COMBINE . '_label'] . '�</i> ��� <i>�' . $MESS[Duplicate::ACTION__SKIP . '_label'] . '�</i>, ����� �� ����� ������ ������� �, �������������, ��������� ��� ������.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_PIPELINE_FILTER . '_label']   = '������ � ��������';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_PIPELINE_FILTER . '_help']    = '������������ ������� ������ ���������� �������� � ��������� ��������.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_STATUS_FILTER . '_label']     = '������ � ��������';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_STATUS_FILTER . '_help']      = '������������ ������� ������ ���������� �������� � ��������� ��������.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_UPDATE_NAME . '_label']       = '�������� �������� ���������';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_UPDATE_NAME . '_help']        = '��������, ���� ������� �������� �' . $MESS[Duplicate::ACTION__COMBINE . '_label'] . '�';

// contact
$MESS[Tabs::INPUT__CONTACT_CREATE . '_label']   = '��������� �������';
$MESS[Tabs::INPUT__CONTACT_CREATE . '_help']    = '';
$MESS[Rest\Contact::FIELD__NAME . '_' . Rest\Contact::getType() .  '_label'] = '��� ��������';

// company
$MESS[Tabs::INPUT__COMPANY_CREATE . '_label']   = '��������� ��������';
$MESS[Tabs::INPUT__COMPANY_CREATE . '_help']    = '';
$MESS[Rest\Contact::FIELD__NAME . '_' . Rest\Company::getType() . '_label'] = '�������� ��������';

// task
$MESS[Tabs::INPUT__TASK_CREATE . '_label']          = '��������� ������';
$MESS[Tabs::INPUT__TASK_CREATE . '_help']           = '� ������ ������ ���� ��������� ������� ��� ������. ���� �� ������� �� ������ ������� �� �����, ������ ��� �� �� ����� �������';
$MESS[Tabs::INPUT__TASK_ELEMENT_TYPE . '_label']    = '�������� ������';
$MESS[Tabs::INPUT__TASK_ELEMENT_TYPE . '_help']     = '������ ����� ��������� � ��������� �������� � ������ �� �������. � ��������� ������ ����� ������� ������ �� ������������: ������, �������, ��������';
$MESS[Rest\Contact::getType() . '_label']             = '�������';
$MESS[Rest\Lead::getType() . '_label']                = '������';
$MESS[Rest\Company::getType() . '_label']             = '��������';
$MESS[Tabs::INPUT__TASK_TYPE . '_label']    = '���';
$MESS[Tabs::INPUT__TASK_TEXT . '_label']    = '�����';
$MESS[Tabs::INPUT__TASK_TEXT . '_default']  = "������ �� ������ ����������.\n�������� �����:\n#FIELDS#";
$MESS[Tabs::INPUT__TASK_TEXT . '_help']     = '�������� <abbr title="#legend#">����������� ������������</abbr>, � ��� �� #FIELDS# - �������� ���������� �����.<br>� ������ ��������� ������������ #FIELDS#, �������� ����� ��������� � ����� ������';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_label']= '������';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_help']= '����������, ���������, ��� ������� ����� ������ ����� � ������ ����������������';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_now']  = '� ������ ���������� ������';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_day_end']= '� 23:59 �������� ���';

// common
$MESS['rover-acrm__remove-preset_label']   = '�������� �����������';
$MESS[Tabs::INPUT__REMOVE_PRESET . '_label'] = '������� �����������';
$MESS[Tabs::INPUT__REMOVE_PRESET . '_popup'] = '�� �������, ��� ������ ������� �����������?';

$MESS['rover-acrm__advertising-marks_all'] = '(���)';

/** Auto */
$MESS['rover-acrm__header-' . Rest\Lead::getType()]       = '������';
$MESS['rover-acrm__header-' . Rest\Contact::getType()]    = '�������';
$MESS['rover-acrm__header-' . Rest\Company::getType()]    = '��������';
$MESS['rover-acrm__header-' . Rest\Task::getType()]       = '������';
$MESS['rover-acrm__header-' . Rest\Task::getType() . '-disabled'] = '������ (���������, �.�. ������� ����� "��������� � ��������������")';

$MESS['rover-acrm__header-template']     = '<p style="text-align: center; color: #333; font-weight: bold; border-bottom: 2px solid #e0e8ea; padding-bottom: 5px; margin-bottom: 0">#label#</p><small style="display: block; text-align: center; color: #777; margin-bottom: 10px;">#help#</small>';


$MESS['rover-acrm__header-fields-custom-' . Rest\Lead::getType()]     = '����������������� �������� ���� "������"';//������� ����������������� �������� ����� ���� "������" � ������';
$MESS['rover-acrm__header-fields-custom-' . Rest\Contact::getType()]  = '����������������� �������� ���� "������"';//������� ����������������� �������� ����� ���� "������" � ��������';
$MESS['rover-acrm__header-fields-custom-' . Rest\Company::getType()]  = '����������������� �������� ���� "������"';//������� ����������������� �������� ����� ���� "������" � ��������';
$MESS['rover-acrm__header-fields-custom-' . Rest\Task::getType()]     = '����������������� �������� ���� "������"';//������� ����������������� �������� ����� ���� "������" � ������';
$MESS['rover-acrm__header-fields-custom-' . Rest\Lead::getType() . '-help']     = '����� � ���� ���� "������" ������ � amoCRM, ������ � ��������� ��������������� ��������';
$MESS['rover-acrm__header-fields-custom-' . Rest\Contact::getType() . '-help']  = '����� � ���� ���� "������" �������� � amoCRM, ������ � ��������� ��������������� ��������';
$MESS['rover-acrm__header-fields-custom-' . Rest\Company::getType() . '-help']  = '����� � ���� ���� "������" �������� � amoCRM, ������ � ��������� ��������������� ��������';
$MESS['rover-acrm__header-fields-custom-' . Rest\Task::getType() . '-help']     = '����� � ���� ���� "������" ������ � amoCRM, ������ � ��������� ��������������� ��������';


$MESS['rover-acrm__header-fields-duplicates-' . Rest\Contact::getType()] = '�������� ���������� ������ ��������';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Company::getType()] = '�������� ���������� ����� ��������';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Lead::getType()]    = '�������� ���������� ����� ������';

$MESS['rover-acrm__header-fields-auto-' . Rest\Lead::getType()]                 = '��������';//�������������� ������� ����� ������';
$MESS['rover-acrm__header-fields-auto-' . Rest\Contact::getType()]              = '��������';//�������������� ������� ����� ������';
$MESS['rover-acrm__header-fields-auto-' . Rest\Company::getType()]              = '��������';//�������������� ������� ����� ������';
$MESS['rover-acrm__header-fields-auto-' . Rest\Task::getType()]                 = '��������';//�������������� ������� ����� ������';
$MESS['rover-acrm__header-fields-auto-' . Rest\Lead::getType() . '-help']       = '����� � ���� �������, ������ � ���� ������ � amoCRM<div style=\'font-size: 70%; color: #777; font-style: italic\'>* � ���� ���� \'������\' ���������� ���������� id ������������� �������� �� amoCRM</div>';
$MESS['rover-acrm__header-fields-auto-' . Rest\Contact::getType() . '-help']    = '����� � ���� �������, ������ � ���� �������� � amoCRM<div style=\'font-size: 70%; color: #777; font-style: italic\'>* � ���� ���� \'������\' ���������� ���������� id ������������� �������� �� amoCRM</div>';
$MESS['rover-acrm__header-fields-auto-' . Rest\Company::getType() . '-help']    = '����� � ���� �������, ������ � ���� �������� � amoCRM<div style=\'font-size: 70%; color: #777; font-style: italic\'>* � ���� ���� \'������\' ���������� ���������� id ������������� �������� �� amoCRM</div>';
$MESS['rover-acrm__header-fields-auto-' . Rest\Task::getType() . '-help']       = '����� � ���� �������, ������ � ���� ������ � amoCRM';


/*$MESS['rover-acrm__header-fields-duplicates-' . Rest\Contact::getType() . '-help']   = '��������� ������ ���������� ��� ������ ��������';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Company::getType() . '-help']   = '��������� ������ ���������� ��� ����� ��������';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Lead::getType() . '-help']      = '��������� ������ ���������� ��� ����� ������';
*/

$MESS['rover-acrm__header-fields-add-' . Rest\Task::getType()]    = '�������������� ����';
$MESS['rover-acrm__header-fields-add-' . Rest\Contact::getType()] = '�������������� ����';
$MESS['rover-acrm__header-fields-add-' . Rest\Company::getType()] = '�������������� ����';
$MESS['rover-acrm__header-fields-add-' . Rest\Lead::getType()]    = '�������������� ����';
$MESS['rover-acrm__header-fields-add-' . Rest\Task::getType() . '-help']    = '����� � �������������� ���� �������, ������ � ���� ������ � amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Contact::getType() . '-help'] = '����� � �������������� ���� �������, ������ � ���� �������� � amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Company::getType() . '-help'] = '����� � �������������� ���� �������, ������ � ���� �������� � amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Lead::getType() . '-help']    = '����� � �������������� ���� �������, ������ � ���� ������ � amoCRM';

$MESS['rover-acrm__header-fields-adv-' . Rest\Task::getType()]    = '����� ��������� �������� � ���������';
$MESS['rover-acrm__header-fields-adv-' . Rest\Contact::getType()] = '����� ��������� �������� � ���������';
$MESS['rover-acrm__header-fields-adv-' . Rest\Company::getType()] = '����� ��������� �������� � ���������';
$MESS['rover-acrm__header-fields-adv-' . Rest\Lead::getType()]    = '����� ��������� �������� � ���������';
$MESS['rover-acrm__header-fields-adv-' . Rest\Task::getType() . '-help']    = '����� � ��������� ����� �� � ���������, ������ � ���� ������ � amoCRM';
$MESS['rover-acrm__header-fields-adv-' . Rest\Contact::getType() . '-help'] = '����� � ��������� ����� �� � ���������, ������ � ���� �������� � amoCRM';
$MESS['rover-acrm__header-fields-adv-' . Rest\Company::getType() . '-help'] = '����� � ��������� ����� �� � ���������, ������ � ���� �������� � amoCRM';
$MESS['rover-acrm__header-fields-adv-' . Rest\Lead::getType() . '-help']    = '����� � ��������� ����� �� � ���������, ������ � ���� ������ � amoCRM';