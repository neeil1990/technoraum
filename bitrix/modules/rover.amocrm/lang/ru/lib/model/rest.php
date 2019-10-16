<?php
$MESS["rover-acrm__server-error-301"]   = "Ошибка. Запрошенный документ был окончательно перенесен";
$MESS["rover-acrm__server-error-400"]   = "Ошибка. Сервер обнаружил в запросе клиента синтаксическую ошибку";
$MESS["rover-acrm__server-error-401"]   = "Ошибка. Запрос требует идентификации пользователя";
$MESS["rover-acrm__server-error-402"]   = "Ошибка. Обслуживание аккаунта приостановлено";
$MESS["rover-acrm__server-error-403"]   = "Ошибка. Ограничение в доступе к указанному ресурсу.";
$MESS["rover-acrm__server-error-404"]   = "Ошибка. Страница не найдена";
$MESS["rover-acrm__server-error-500"]   = "Внутрення ошибка сервера";
$MESS["rover-acrm__server-error-502"]   = "Ошибка. Неудачное выполнение";
$MESS["rover-acrm__server-error-503"]   = "Ошибка. Сервер временно недоступен";
$MESS["rover-acrm__server-error-0"]     = "Ошибка. Нет связи с amoCRM";

$MESS['rover-acrm__no-sub-domain']  = "Не задан ваш субдомен";
$MESS['rover-acrm__sub-domain-incorrect']  = "Ваш субдомен содержит некорректные символы. Доступны только латинские буквы, цифры, знак подчеркивания и тире.";
$MESS['rover-acrm__no-login']       = "Не задан email (логин)";
$MESS['rover-acrm__no-nash']        = "Не задан api-ключ";

$MESS["rover-acrm__rest-auth-error-110"]     = "Неправильный логин (e-mail) или пароль (api-ключ).";
$MESS["rover-acrm__rest-auth-error-111"]     = "Перевышено количество попыток авторизации. Необходимо авторизоваться в аккаунте через браузер, введя код капчи.";
$MESS["rover-acrm__rest-auth-error-112"]     = "Пользователь выключен в настройках аккаунта \"Пользователи и права\" или не состоит в аккаунте.";
$MESS["rover-acrm__rest-auth-error-113"]     = "Доступ к данному аккаунту запрещён с Вашего IP адреса. Возникает, когда в настройках безопасности аккаунта включена фильтрация доступа к API по \"белому списку IP адресов\".";
$MESS["rover-acrm__rest-auth-error-101"]     = "Запрос к несуществующему аккаунту (субдомену).";
$MESS["rover-acrm__rest-auth-error-401"]     = "На сервере нет данных аккаунта. Нужно сделать запрос на другой сервер по переданному IP.";

$MESS["rover-acrm__rest-error-102"]     = "POST-параметры должны передаваться в формате JSON.";
$MESS["rover-acrm__rest-error-103"]     = "Параметры не переданы.";
$MESS["rover-acrm__rest-error-104"]     = "Запрашиваемый метод API не найден.";
$MESS["rover-acrm__rest-error-400"]     = "Неверная структура массива передаваемых данных, либо не верные идентификаторы кастомных полей.";
$MESS["rover-acrm__rest-error-402"]     = "Подписка закончилась.";
$MESS["rover-acrm__rest-error-403"]     = "Аккаунт заблокирован за неоднократное превышение количества запросов в секунду.";
$MESS["rover-acrm__rest-error-429"]     = "Превышено допустимое количество запросов в секунду.";
$MESS["rover-acrm__rest-error-2002"]    = "По вашему запросу ничего не найдено.";

$MESS['rover-acrm__rest-error-template']        = 'Код ответа сервера #code#: #message#';
$MESS['rover-acrm__rest-error-specific-template'] = "Расшифровка: #status#";
$MESS['rover-acrm__rest-error-add-template']    = "Внутренний код ошибки #code#: #message#";
$MESS['rover-acrm__rest-error-codes']           = "\n---\nПодробнее о кодах ошибок amoCRM: https://www.amocrm.ru/developers/content/api/errors";