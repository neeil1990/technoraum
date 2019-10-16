<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 14.06.2017
 * Time: 11:47
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use \Rover\AmoCRM\Entity\Source;

$MESS['rover-apl__header-NAME']         = 'Название';
$MESS['rover-apl__header-TYPE']         = 'Тип';
$MESS['rover-apl__header-SITE']         = 'Сайт';
$MESS['rover-apl__header-MANAGER']      = 'Отвественный';
$MESS['rover-apl__header-ACTIVE']       = 'Акт.';
$MESS['rover-apl__header-UNSORTED']     = '«Неразобранное»';
$MESS['rover-apl__header-LEAD']         = 'Сделка';
$MESS['rover-apl__header-CONTACT']      = 'Контакт';
$MESS['rover-apl__header-COMPANY']      = 'Компания';
$MESS['rover-apl__header-TASK']         = 'Задача';
$MESS['rover-apl__header-ELEMENTS_CNT'] = 'Результаты';

$MESS['rover-apl__action-remove']       = 'Удалить';
$MESS['rover-apl__action-remove_title'] = 'Удалить отмеченные элементы';
$MESS['rover-apl__action-remove_success'] = 'Элементы успешно удалены';
$MESS['rover-apl__action-confirm']      = 'Подтвердите действие для отмеченных элементов';
$MESS['rover-apl__action-update']       = 'Изменить';
$MESS['rover-apl__action-update_success'] = 'Элементы успешно изменены';
$MESS['rover-apl__action-elements']     = 'Результаты';
$MESS['rover-apl__title-results']       = 'Перейти к результатам «#preset-name#»';
$MESS['rover-apl__title-settings']       = 'Перейти к настройкам правила «#preset-name#»';
$MESS['rover-apl__unavailable']             = 'n/a';
$MESS['rover-apl__no']                  = 'Нет';
$MESS['rover-apl__title-task-unavailable']  = 'Создание задачи невозможно, т.к. включено создание «неразобранного»';
$MESS['rover-apl__action-cancel']       = 'Отменить';
$MESS['rover-apl__action-add']          = 'Добавить';
$MESS['rover-apl__action-add_title']    = 'Добавить правило интеграции';
$MESS['rover-apl__action-add_' . Source::TYPE__FORM]     = 'Веб-форму';
$MESS['rover-apl__action-add_' . Source::TYPE__EVENT]    = 'Почтовое событие';

$MESS['rover-apl__action-settings']         = 'Настройки модуля';
$MESS['rover-apl__action-settings_title']   = 'Настройки модуля интеграции';
$MESS['rover-apl__title']                   = 'Список правил интеграции с amoCRM';
$MESS['rover-apl__error_delete']            = 'Ошибка удаления: #error#';
$MESS['rover-apl__no-connection']           = 'Отсустствует соединение с amoCRM';
$MESS['rover-apl__disabled']                = 'Интеграция отключена в <a href="/bitrix/admin/settings.php?lang=' . LANGUAGE_ID . '&mid=rover.amocrm&mid_menu=1">настройках модуля</a>';