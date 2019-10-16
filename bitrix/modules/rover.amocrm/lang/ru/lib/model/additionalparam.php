<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.10.2017
 * Time: 20:50
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use \Rover\AmoCRM\Model\AdditionalParam\AdvMarks;
use \Rover\AmoCRM\Model\AdditionalParam\Domain;
use \Rover\AmoCRM\Model\AdditionalParam\SiteName;
use \Rover\AmoCRM\Model\AdditionalParam\PageUrl;
use \Rover\AmoCRM\Model\AdditionalParam\Ip;
use \Rover\AmoCRM\Model\AdditionalParam\VisitorUid;

$MESS['rover-acrm__' . AdvMarks::getTemplate() . '-label']  = '¬се метки рекламных компаний';
$MESS['rover-acrm__' . AdvMarks::getTemplate() . '-help']   = '¬се выбранные метки рекламных компаний и аналитики в виде <code>mark1=value1&mark2=value2&...</code>';
$MESS['rover-acrm__' . Domain::getTemplate() . '-label']    = 'ƒомен сайта';
//$MESS['rover-acrm__' . Domain::getTemplate() . '-help']   = 'домен сайта, на котором произошло событие';
$MESS['rover-acrm__' . SiteName::getTemplate() . '-label']  = 'Ќазвание сайта';
//$MESS['rover-acrm__' . SiteName::getTemplate() . '-help'] = 'название сайта, на котором произошло событие';
$MESS['rover-acrm__' . PageUrl::getTemplate() . '-label']   = 'јдрес страницы';
//$MESS['rover-acrm__' . PageUrl::getTemplate() . '-help']  = 'url страницы, на которой произошло событие';
$MESS['rover-acrm__' . Ip::getTemplate() . '-label']        = 'ip-адрес';
//$MESS['rover-acrm__' . Ip::getTemplate() . '-help']       = 'ip адрес сервера, на котором произошло событие';
$MESS['rover-acrm__' . VisitorUid::getTemplate() . '-label']= '»дентификатор посетител€';
$MESS['rover-acrm__' . VisitorUid::getTemplate() . '-help'] = 'ѕодробнее в <a href="https://www.amocrm.ru/developers/content/digital_pipeline/site_visit" target="_blank">документации amoCRM</a>';