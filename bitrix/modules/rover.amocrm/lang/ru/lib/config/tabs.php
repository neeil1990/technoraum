<?php
use \Rover\AmoCRM\Config\Tabs;
use \Rover\AmoCRM\Model\Rest;
use \Rover\AmoCRM\Entity\Handler;
use \Rover\AmoCRM\Model\EventType;
use \Rover\AmoCRM\Helper\Duplicate;
/**
 * Main tab
 */
$MESS[Tabs::TAB__MAIN . '_label'] = 'Основные настройки';
$MESS[Tabs::TAB__MAIN . '_descr'] = 'Настройка модуля';

$MESS[Tabs::TAB__PRESET . '_label'] = 'Правило интеграции';
$MESS[Tabs::TAB__PRESET . '_descr'] = 'Настройка правила интеграции';

$MESS['banner'] = '<div class="logo_wrapper">
						<div class="logo">
							<a target="_blank" href="http://www.amocrm.ru/"><img src="http://www.amocrm.ru/design/images/logo.png" width="172" height="35" alt="amoCRM"></a>
						</div>
						<div class="logo_text">Модуль позволяет отправлять данные в <a style="color: #fff;" target="_blank" href="http://www.amocrm.ru/">amoCRM</a> из веб-форм и почтовых событий.<br>
		Для работы модуля необходимо подключиться к amoCRM</div>
					</div>
					<style>
						.logo_wrapper {background:#0581c5;height:56px;margin:0 0 5px;}
						.logo {float:left;margin:10px 15px;}
						.logo a img{border:none;}
						.logo_text {color:#fff;margin:15px 0 0;float:left;}
					</style>';

$MESS[Tabs::INPUT__ENABLED . '_label']      = 'Интеграция включена';
$MESS[Tabs::INPUT__ENABLED . '_help']       = 'Включение/отключение <u>всей</u> интеграции на сайте';
$MESS[Tabs::INPUT__LOG_ENABLED . '_label']  = 'Логирование включено';
$MESS[Tabs::INPUT__LOG_ENABLED . '_help']   = 'Лог будет вестись в файлах <b>note.log</b> (размер #note-file-size#) и <b>error.log</b> (размер #error-file-size#) в папке <a href="/bitrix/admin/fileman_admin.php?path=%2Fupload%2Frover.amocrm%2Flog&show_perms_for=0" target="_blank">/upload/rover.amocrm/log/</a>';
$MESS[Tabs::INPUT__LOG_ENABLED . '_disabled_help']   = 'Папка /upload/rover.amocrm/log/ недоступна для записи';
$MESS[Tabs::INPUT__LOG_MAX_SIZE . '_label']     = 'Максимальный размер файлов лога, Мб (0 - без ограничений)';
$MESS[Tabs::INPUT__LOG_MAX_SIZE . '_help']      = 'При достижении максимального размера, файл будет обнулён';
$MESS[Tabs::INPUT__SUB_DOMAIN . '_label']   = 'Ваш субдомен в amoCRM';
$MESS[Tabs::INPUT__SUB_DOMAIN . '_help']    = 'Необходимо ввести ваш субдомен 3го уровня (без https:// и .amocrm.ru).<br>Пример: https://<u>субдомен</u>.amocrm.ru - надо ввести <u>субдомен</u>.';
$MESS[Tabs::INPUT__LOGIN . '_label']        = 'Email (логин)';
$MESS[Tabs::INPUT__HASH . '_label']         = "Ключ для авторизации в API";
$MESS[Tabs::INPUT__HASH . '_help']          = 'Находится в настройках вашей amoCRM в разделе API.<br>Обратите внимание, что после смены пароля, <span style="text-decoration: underline">ключ сбрасывается и устанавливается новый</span>.';
$MESS[Tabs::INPUT__CONNECT . '_label']      = 'Подключиться к amoCRM';
$MESS[Tabs::INPUT__CONNECT . '_default']    = 'Подключиться';
$MESS['rover-acrm__alert-log_header']       = 'Оповещение/логирование';
$MESS[Tabs::INPUT__UNAVAILABLE_ALERT . '_label']      = 'Отправлять письмо администратору о любых ошибках в работе решения';
$MESS[Tabs::INPUT__UNAVAILABLE_ALERT . '_help']      = 'Сообщения будут приходить не чаще раза в час.<br>Перейти к редактированию <a href="/bitrix/admin/type_edit.php?EVENT_NAME=' . EventType::TYPE__AMOCRM_UNAVAILABLE .'&lang=' . LANGUAGE_ID . '">почтового события</a>.';

$MESS['rover-acrm__agent-settings_header']  = 'Настройки агента';
$MESS['rover-acrm__agent-settings_header_demo']  = '(недоступно в демо-версии)';
$MESS[Tabs::INPUT__AGENT . '_label']        = 'Отложить интеграцию на агента';
$MESS[Tabs::INPUT__AGENT . '_help']         = 'Интеграция <b>новых</b> событий будет производиться с периодичностью раз в 5 минут. Ускоряет отклик сайта в момент наступления события при условии <a target="_blank" href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943">перевода агентов на cron</a>..';
$MESS[Tabs::INPUT__AGENT_COUNT . '_label']  = 'Количество событий, обрабатываемых за один проход';
$MESS[Tabs::INPUT__AGENT_COUNT . '_help']   = 'Установка значения более 5 может серьезно замедлить работу и/или вызвать ошибку превышения времени выполнения скрипта.';
$MESS[Tabs::INPUT__AGENT_HANDLE_ERRORS . '_label']  = 'Повторно обрабатывать ошибочные события';
$MESS[Tabs::INPUT__AGENT_HANDLE_ERRORS . '_help']   = 'Работает только при использовании агентов.';
$MESS['rover-acrm__event-handling_header']  = 'Обработка событий';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_label']   = 'Время хранения событий в журнале';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_help']    = 'Всего <a target="_blank" href="/bitrix/admin/perfmon_table.php?lang=' . LANGUAGE_ID . '&table_name=rv_amocrm_status">#count#</a> событий в журнале. Очистка происходит раз в сутки. Необработанные и события с ошибками не удаляются';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_365']     = '1 год';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_183']     = '6 месяцев';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_92']      = '3 месяца';
$MESS[Tabs::INPUT__EVENT_LOG_LIFETIME . '_31']      = '1 месяц';
$MESS[Tabs::INPUT__HANDLE_NEW_EVENTS . '_label']    = 'Обработать новые события';
$MESS[Tabs::INPUT__HANDLE_NEW_EVENTS . '_default']  = 'Обработать';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_label']  = 'Повторно обработать события с ошибками';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_help']   = 'Если ошибка устранена, то событие можно попробовать обработать повторно. <br>При включенном логировании ошибки записываются в файл <b>error.log</b>. <a href="/bitrix/admin/fileman_admin.php?path=%2Fupload%2Frover.amocrm%2Flog&show_perms_for=0&lang=' . LANGUAGE_ID . '" target="_blank">Открыть папку с логами</a>.';
$MESS[Tabs::INPUT__HANDLE_ERROR_EVENTS . '_default']= 'Обработать';

$MESS['rover-acrm__agent-new_label']        = 'Событий в очереди';
$MESS['rover-acrm__agent-new_help']         = 'Из них новых: #new-count#, с ошибками: #err-count#';

$MESS['rover-acrm__integration-no-connection']  = '<p style="padding: 15px; border: 1px solid #ccc; text-align: center;">Настройте подключение к amoCRM, чтобы начать добавлять правила интеграции</p>';
$MESS['rover-acrm__integration']                = '<p style="padding: 15px; border: 1px solid #ccc; text-align: center;"><a href="/bitrix/admin/rover-acrm__preset-list.php?lang=' . LANGUAGE_ID . '"><b>Добавить или отредактировать правило интеграции</b></a>.</p>';

/**
 * Pareset tab
 */
// settings
$MESS['header_settings_label']                      = 'Общие настройки';
$MESS[Tabs::INPUT__CONNECTION_ENABLED . '_label']   = 'Интеграция включена';
$MESS[Tabs::INPUT__CONNECTION_ENABLED . '_help']    = 'Включение/отключение текущего правила интеграции<br>Тип: #type#';
$MESS[Tabs::INPUT__SITES . '_label']                = 'Сайты, на которых работает правило';
$MESS[Tabs::INPUT__SITES . '_help']                 = 'Если не выбран ни один сайт, правило будет работать на всех';
$MESS[Tabs::INPUT__MAIN_USER . '_label']            = 'Ответственный пользователь';
$MESS[Tabs::INPUT__MAIN_USER . '_help']             = 'Этот пользователь будет назначен отвественным за создаваемые сделки, контакты и компании. Так же ему будет ставиться создаваемая задача.';
$MESS[Tabs::INPUT__TAG . '_label']                  = 'Тэги';
$MESS[Tabs::INPUT__TAG . '_help']                   = 'Будут добавлены к сделкам, контактам и компаниям. Доступны <abbr title="#legend#">стандартные плейсхолдеры</abbr>.';
$MESS[Tabs::INPUT__FORMAT . '_label']               = 'Формат';
$MESS[Tabs::INPUT__FORMAT . '_help']                = '"html" - значения будут переданы "как есть",<br>"text" - будут вырезаны все теги с сохранением переноса строк';
$MESS[Handler::FORMAT__HTML . '_label']             = 'html';
$MESS[Handler::FORMAT__TEXT . '_label']             = 'текст';
$MESS[Tabs::INPUT__GROUP_NOTES . '_label']          = 'Группировать примечания';
$MESS[Tabs::INPUT__GROUP_NOTES . '_help']           = 'К сделке, контакту и компании будет добавлено по одному примечанию, группирующему общую информацию.';
$MESS[Tabs::INPUT__IGNORE_SAME_HIT_EVENTS . '_label'] = 'Игнорировать дублирование событий на одном хите';
$MESS[Tabs::INPUT__IGNORE_SAME_HIT_EVENTS . '_help']  = 'Помогает избавиться от дублирования при интеграции почтового события отправки веб-формы и в некоторых других случаях.';

$MESS['rover-acrm__header-fields-advertising-common']   = 'Метки рекламных кампаний и аналитики';
$MESS['rover-acrm__header-fields-advertising-common-help']   = 'для обновления списка в настройках сущностей сохраните правило интеграции';
$MESS[Tabs::INPUT__ADV_MARKS_FILTER . '_label']         = 'Искать метки';
$MESS[Tabs::INPUT__ADV_MARKS_FILTER . '_help']          = 'Будут перенданы только выбранные метки (в случае их наличия)<br>Недостающие метки вы можете указать в окне ниже ?';
$MESS[Tabs::INPUT__ADV_MARKS_CUSTOM_FILTER . '_label']  = 'Дополнительно искать метки';
$MESS[Tabs::INPUT__ADV_MARKS_CUSTOM_FILTER . '_help']   = 'Каждая метка должна быть указана с новой строки. Пример:<br><code>abd_defgh<br>_klm</code><br><br>Для обновления списка доступных меток в настройках сделки, котнтакта, компании и задачи, необходимо сохранить правило интеграции.';
$MESS[Tabs::INPUT__SAVE_ADV_MARKS . '_label']           = 'Сохранять метки при переходе между страницами';
$MESS[Tabs::INPUT__SAVE_ADV_MARKS . '_help']            = 'Сохраняет указанные выше метки на протяжении всей сессии работы с сайтом.';
$MESS[Tabs::INPUT__REMOVE_GA_VERSION . '_label']        = 'Удалять из метки _ga информацию о версии api';
$MESS[Tabs::INPUT__REMOVE_GA_VERSION . '_help']         = 'Удаляет значения вида <b>GA1.2.</b>, <b>GA1.3.</b> и т.п. из начала метки. Пример: <b>GA1.2.</b>1054613344.1468150257 ? 1054613344.1468150257';

$MESS['rover-acrm__header-fields-unsorted-common']  = '«Неразобранное»';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_label']             = 'Добавлять в «Неразобранное»';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_help']              = 'Сделки и контакты будут добавлены в «Неразобранное» согласно их настройкам, но есть некоторые ограничения:<ul><li>сделка создается всегда, даже если ее создание отключено;</li><li>задачи не создаются;</li><li>при включенном контроле дубликатов, к контактам и сделкам всегда добавляются ссылки на них;</li><li>контроль уникальности примечаний не производится.</li></ul>';
$MESS[Tabs::INPUT__UNSORTED_CREATE . '_disabled_help']     = 'В вашем аккаунте «Неразобранное» отключено';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_label']        = 'Название сделки в «Неразобранном»';

$MESS[Duplicate::ACTION__ADD_NOTE . '_label']   = 'Добавить примечание со ссылками на дубликаты';
$MESS[Duplicate::ACTION__COMBINE . '_label']    = 'Обновить и использовать самый первый из найденых дубликатов';
$MESS[Duplicate::ACTION__SKIP . '_label']       = 'Использовать самый первый из найденых дубликатов без обновления';

$MESS[Duplicate::LOGIC__AND . '_label']    = 'всех выбранных полей';
$MESS[Duplicate::LOGIC__OR . '_label']     = 'любого из выбранных полей';

$MESS['duplicate__unsorted-help']   = 'Этот параметр недоступен при включенной опции "Добавлять в «Неразобранное»"';
$MESS['duplicate__fields-label']    = 'Искать по полям';
$MESS['duplicate__fields-help']     = 'Должно быть выбрано хотя бы одно поле, иначе поиск осуществлён не будет';
$MESS['duplicate__action-label']    = 'При обнаружении дубликата';
$MESS['duplicate__logic-label']     = 'Искать по совпадению';
$MESS['duplicate__control-label']   = 'Контролировать дубликаты';
$MESS['duplicate__control-lead-help']    = 'Включение этого функцинала может замедлить отклик системы при большом количестве уже существующих сделок. Для решения этой проблемы рекомендуется отложить интеграцию на агента. При этом агенты на сайте <u>обязательно</u> должны быть <a target="_blank" href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943">переведены на cron</a>.';
$MESS['duplicate__control-contact-help']    = 'Включение этого функцинала может замедлить отклик системы при большом количестве уже существующих контактов. Для решения этой проблемы рекомендуется отложить интеграцию на агента. При этом агенты на сайте <u>обязательно</u> должны быть <a target="_blank" href="https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943">переведены на cron</a>.';

// lead
$MESS[Tabs::INPUT__LEAD_CREATE . '_label']  = 'Создавать сделку';
$MESS[Tabs::INPUT__LEAD_CREATE . '_help']   = 'Сделка будет привязана к контакту, в случае его создания.<br>При добавлении в «Неразобранное» сделка будет создаваться всегда, согласно своим настройкам.';
$MESS[Tabs::INPUT__LEAD_NAME . '_label']    = 'Название';
$MESS[Tabs::INPUT__LEAD_PRICE . '_label']   = 'Бюджет';
$MESS[Tabs::INPUT__LEAD_STATUS . '_label']  = 'Статус';
$MESS[Tabs::INPUT__LEAD_STATUS . '_unsorted_help']  = 'Сделка будет добавлена в «неразобранное»';

$MESS[Tabs::INPUT__LEAD_VISITOR_UID . '_label']     = 'Добавлять идентификатор посетителя (visitor_uid)';
$MESS[Tabs::INPUT__LEAD_VISITOR_UID . '_help']      = 'Позволяет отслеживать посетителя в автоворонках. Подробнее в <a href="https://www.amocrm.ru/developers/content/digital_pipeline/site_visit" target="_blank">документации amoCRM</a>';

$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Lead::getType() . '_label']      = 'Маппинг полей';
$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Contact::getType() . '_label']   = 'Маппинг полей';
$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Company::getType() . '_label']   = 'Маппинг полей';
$MESS[Tabs::INPUT__MAPPING_SUBTABCONROL . Rest\Task::getType() . '_label']      = 'Маппинг полей';

$MESS[Tabs::INPUT__LEAD_DUPLICATE_CONTACT_FILTER . '_label']    = 'Ограничить область поиска существующими сделками контакта';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_CONTACT_FILTER . '_help']     = 'На вкладке "Контакт" <u>обязательно</u> должен быть включен контроль дубликатов в режимах <i>«' . $MESS[Duplicate::ACTION__COMBINE . '_label'] . '»</i> или <i>«' . $MESS[Duplicate::ACTION__SKIP . '_label'] . '»</i>, иначе не будет найден контакт и, соотвественно, дубликаты его сделок.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_PIPELINE_FILTER . '_label']   = 'Искать в воронках';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_PIPELINE_FILTER . '_help']    = 'Ограничивает область поиска дубликатов сделками в выбранных воронках.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_STATUS_FILTER . '_label']     = 'Искать в статусах';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_STATUS_FILTER . '_help']      = 'Ограничивает область поиска дубликатов сделками в выбранных статусах.';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_UPDATE_NAME . '_label']       = 'Обновить название дубликата';
$MESS[Tabs::INPUT__LEAD_DUPLICATE_UPDATE_NAME . '_help']        = 'Работает, если выбрано действие «' . $MESS[Duplicate::ACTION__COMBINE . '_label'] . '»';

// contact
$MESS[Tabs::INPUT__CONTACT_CREATE . '_label']   = 'Создавать контакт';
$MESS[Tabs::INPUT__CONTACT_CREATE . '_help']    = '';
$MESS[Rest\Contact::FIELD__NAME . '_' . Rest\Contact::getType() .  '_label'] = 'Имя контакта';

// company
$MESS[Tabs::INPUT__COMPANY_CREATE . '_label']   = 'Создавать компанию';
$MESS[Tabs::INPUT__COMPANY_CREATE . '_help']    = '';
$MESS[Rest\Contact::FIELD__NAME . '_' . Rest\Company::getType() . '_label'] = 'Название компании';

// task
$MESS[Tabs::INPUT__TASK_CREATE . '_label']          = 'Создавать задачу';
$MESS[Tabs::INPUT__TASK_CREATE . '_help']           = 'К задаче должны быть привязаны контакт или сделка. Если ни контакт ни сделка созданы не будут, задача так же не будет создана';
$MESS[Tabs::INPUT__TASK_ELEMENT_TYPE . '_label']    = 'Привязка задачи';
$MESS[Tabs::INPUT__TASK_ELEMENT_TYPE . '_help']     = 'Задача будет привязана к выбранной сущности в случае ее наличия. В противном случае будет выбрана первая из существующих: сделка, контакт, компания';
$MESS[Rest\Contact::getType() . '_label']             = 'контакт';
$MESS[Rest\Lead::getType() . '_label']                = 'сделка';
$MESS[Rest\Company::getType() . '_label']             = 'компания';
$MESS[Tabs::INPUT__TASK_TYPE . '_label']    = 'Тип';
$MESS[Tabs::INPUT__TASK_TEXT . '_label']    = 'Текст';
$MESS[Tabs::INPUT__TASK_TEXT . '_default']  = "Задача из модуля интеграции.\nЗначения полей:\n#FIELDS#";
$MESS[Tabs::INPUT__TASK_TEXT . '_help']     = 'Доступны <abbr title="#legend#">стандартные плейсхолдеры</abbr>, а так же #FIELDS# - значения переданных полей.<br>В случае отсуствия плейсхолдера #FIELDS#, значения будут добавлены в конец текста';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_label']= 'Делайн';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_help']= 'Пожалуйста, убедитесь, что часовые пояса Вашего сайта и амоСрм синхронизированы';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_now']  = 'В момент постановки задачи';
$MESS[Tabs::INPUT__TASK_DEADLINE . '_day_end']= 'В 23:59 текущего дня';

// common
$MESS['rover-acrm__remove-preset_label']   = 'Удаление подключения';
$MESS[Tabs::INPUT__REMOVE_PRESET . '_label'] = 'Удалить подключение';
$MESS[Tabs::INPUT__REMOVE_PRESET . '_popup'] = 'Вы уверены, что хотите удалить подключение?';

$MESS['rover-acrm__advertising-marks_all'] = '(все)';

/** Auto */
$MESS['rover-acrm__header-' . Rest\Lead::getType()]       = 'Сделка';
$MESS['rover-acrm__header-' . Rest\Contact::getType()]    = 'Контакт';
$MESS['rover-acrm__header-' . Rest\Company::getType()]    = 'Компания';
$MESS['rover-acrm__header-' . Rest\Task::getType()]       = 'Задача';
$MESS['rover-acrm__header-' . Rest\Task::getType() . '-disabled'] = 'Задача (отключено, т.к. выбрана опция "Добавлять в «Неразобранное»")';

$MESS['rover-acrm__header-template']     = '<p style="text-align: center; color: #333; font-weight: bold; border-bottom: 2px solid #e0e8ea; padding-bottom: 5px; margin-bottom: 0">#label#</p><small style="display: block; text-align: center; color: #777; margin-bottom: 10px;">#help#</small>';


$MESS['rover-acrm__header-fields-custom-' . Rest\Lead::getType()]     = 'Предустановленные значения типа "список"';//Задание предустановленных значений полей типа "список" у сделки';
$MESS['rover-acrm__header-fields-custom-' . Rest\Contact::getType()]  = 'Предустановленные значения типа "список"';//Задание предустановленных значений полей типа "список" у контакта';
$MESS['rover-acrm__header-fields-custom-' . Rest\Company::getType()]  = 'Предустановленные значения типа "список"';//Задание предустановленных значений полей типа "список" у компании';
$MESS['rover-acrm__header-fields-custom-' . Rest\Task::getType()]     = 'Предустановленные значения типа "список"';//Задание предустановленных значений полей типа "список" у задачи';
$MESS['rover-acrm__header-fields-custom-' . Rest\Lead::getType() . '-help']     = 'слева — поля типа "список" сделки в amoCRM, справа — возможные устанавливаемые значения';
$MESS['rover-acrm__header-fields-custom-' . Rest\Contact::getType() . '-help']  = 'слева — поля типа "список" контакта в amoCRM, справа — возможные устанавливаемые значения';
$MESS['rover-acrm__header-fields-custom-' . Rest\Company::getType() . '-help']  = 'слева — поля типа "список" компании в amoCRM, справа — возможные устанавливаемые значения';
$MESS['rover-acrm__header-fields-custom-' . Rest\Task::getType() . '-help']     = 'слева — поля типа "список" задачи в amoCRM, справа — возможные устанавливаемые значения';


$MESS['rover-acrm__header-fields-duplicates-' . Rest\Contact::getType()] = 'Контроль дубликатов нового контакта';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Company::getType()] = 'Контроль дубликатов новой компании';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Lead::getType()]    = 'Контроль дубликатов новой сделки';

$MESS['rover-acrm__header-fields-auto-' . Rest\Lead::getType()]                 = 'Основные';//Автоматическое задание полей сделки';
$MESS['rover-acrm__header-fields-auto-' . Rest\Contact::getType()]              = 'Основные';//Автоматическое задание полей задачи';
$MESS['rover-acrm__header-fields-auto-' . Rest\Company::getType()]              = 'Основные';//Автоматическое задание полей задачи';
$MESS['rover-acrm__header-fields-auto-' . Rest\Task::getType()]                 = 'Основные';//Автоматическое задание полей задачи';
$MESS['rover-acrm__header-fields-auto-' . Rest\Lead::getType() . '-help']       = 'слева — поля события, справа — поля сделки в amoCRM<div style=\'font-size: 70%; color: #777; font-style: italic\'>* в поля типа \'список\' необходимо передавать id существующего значения из amoCRM</div>';
$MESS['rover-acrm__header-fields-auto-' . Rest\Contact::getType() . '-help']    = 'слева — поля события, справа — поля контакта в amoCRM<div style=\'font-size: 70%; color: #777; font-style: italic\'>* в поля типа \'список\' необходимо передавать id существующего значения из amoCRM</div>';
$MESS['rover-acrm__header-fields-auto-' . Rest\Company::getType() . '-help']    = 'слева — поля события, справа — поля компании в amoCRM<div style=\'font-size: 70%; color: #777; font-style: italic\'>* в поля типа \'список\' необходимо передавать id существующего значения из amoCRM</div>';
$MESS['rover-acrm__header-fields-auto-' . Rest\Task::getType() . '-help']       = 'слева — поля события, справа — поля задачи в amoCRM';


/*$MESS['rover-acrm__header-fields-duplicates-' . Rest\Contact::getType() . '-help']   = 'Настройка поиска дубликатов для нового контакта';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Company::getType() . '-help']   = 'Настройка поиска дубликатов для новой компании';
$MESS['rover-acrm__header-fields-duplicates-' . Rest\Lead::getType() . '-help']      = 'Настройка поиска дубликатов для новой сделки';
*/

$MESS['rover-acrm__header-fields-add-' . Rest\Task::getType()]    = 'Дополнительные поля';
$MESS['rover-acrm__header-fields-add-' . Rest\Contact::getType()] = 'Дополнительные поля';
$MESS['rover-acrm__header-fields-add-' . Rest\Company::getType()] = 'Дополнительные поля';
$MESS['rover-acrm__header-fields-add-' . Rest\Lead::getType()]    = 'Дополнительные поля';
$MESS['rover-acrm__header-fields-add-' . Rest\Task::getType() . '-help']    = 'слева — дополнительные поля события, справа — поля задачи в amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Contact::getType() . '-help'] = 'слева — дополнительные поля события, справа — поля контакта в amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Company::getType() . '-help'] = 'слева — дополнительные поля события, справа — поля компании в amoCRM';
$MESS['rover-acrm__header-fields-add-' . Rest\Lead::getType() . '-help']    = 'слева — дополнительные поля события, справа — поля сделки в amoCRM';

$MESS['rover-acrm__header-fields-adv-' . Rest\Task::getType()]    = 'Метки рекламных кампаний и аналитики';
$MESS['rover-acrm__header-fields-adv-' . Rest\Contact::getType()] = 'Метки рекламных кампаний и аналитики';
$MESS['rover-acrm__header-fields-adv-' . Rest\Company::getType()] = 'Метки рекламных кампаний и аналитики';
$MESS['rover-acrm__header-fields-adv-' . Rest\Lead::getType()]    = 'Метки рекламных кампаний и аналитики';
$MESS['rover-acrm__header-fields-adv-' . Rest\Task::getType() . '-help']    = 'слева — доступные метки РК и аналитики, справа — поля задачи в amoCRM';
$MESS['rover-acrm__header-fields-adv-' . Rest\Contact::getType() . '-help'] = 'слева — доступные метки РК и аналитики, справа — поля контакта в amoCRM';
$MESS['rover-acrm__header-fields-adv-' . Rest\Company::getType() . '-help'] = 'слева — доступные метки РК и аналитики, справа — поля компании в amoCRM';
$MESS['rover-acrm__header-fields-adv-' . Rest\Lead::getType() . '-help']    = 'слева — доступные метки РК и аналитики, справа — поля сделки в amoCRM';