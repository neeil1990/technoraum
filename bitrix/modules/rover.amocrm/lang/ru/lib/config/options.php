<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 10.02.2016
 * Time: 0:50
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use \Rover\AmoCRM\Entity\Source;
use \Rover\AmoCRM\Config\Tabs;

$MESS['rover-acrm__preset-add-success'] = 'Подключение успешно создано';
$MESS['rover-acrm__preset-del-success'] = 'Подключение удалено';



$MESS[Tabs::INPUT__UNSORTED_NAME . '_default_' . Source::TYPE__FORM]  = '«Неразобранное» из формы «#NAME#»';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_default_' . Source::TYPE__EVENT] = '«Неразобранное» из почтового события «#NAME#»';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_help_' . Source::TYPE__FORM]     = 'Доступны <abbr title="#legend#">стандартные плейсхолдеры</abbr>, а так же #NAME# - название текущей веб-формы<br>Если поле пустое, берется значение по-умолчанию';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_help_' . Source::TYPE__EVENT]    = 'Доступны <abbr title="#legend#">стандартные плейсхолдеры</abbr>, а так же #NAME# - название текущего почтового события<br>Если название пустое, берется значение по-умолчанию';

$MESS[Tabs::INPUT__LEAD_NAME . '_default_' . Source::TYPE__FORM]  = 'Сделка из формы «#FORM_NAME#»';
$MESS[Tabs::INPUT__LEAD_NAME . '_default_' . Source::TYPE__EVENT] = 'Сделка из почтового события «#EVENT_NAME#»';
$MESS[Tabs::INPUT__LEAD_NAME . '_help_' . Source::TYPE__FORM]     = 'Доступны <abbr title="#legend#">стандартные плейсхолдеры</abbr>, а так же #FORM_NAME# - название текущей веб-формы<br>Если поле пустое, берется значение по-умолчанию';
$MESS[Tabs::INPUT__LEAD_NAME . '_help_' . Source::TYPE__EVENT]    = 'Доступны <abbr title="#legend#">стандартные плейсхолдеры</abbr>, а так же #EVENT_NAME# - название текущего почтового события<br>Если название пустое, берется значение по-умолчанию';