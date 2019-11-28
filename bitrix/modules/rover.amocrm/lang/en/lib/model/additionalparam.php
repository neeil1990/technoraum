<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.10.2017
 * Time: 20:53
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

use \Rover\AmoCRM\Model\AdditionalParam\AdvMarks;
use \Rover\AmoCRM\Model\AdditionalParam\Domain;
use \Rover\AmoCRM\Model\AdditionalParam\SiteName;
use \Rover\AmoCRM\Model\AdditionalParam\PageUrl;
use \Rover\AmoCRM\Model\AdditionalParam\Ip;
use \Rover\AmoCRM\Model\AdditionalParam\VisitorUid;

$MESS['rover-acrm__' . AdvMarks::getTemplate() . '-label']    = 'Advertising marks';
$MESS['rover-acrm__' . AdvMarks::getTemplate() . '-help']     = 'params like utm_* and roistat_* from HTTP_REFERER header<br>when you enable the corresponding option, you can search for marks in the cookies';
$MESS['rover-acrm__' . Domain::getTemplate() . '-label']       = 'Current domain';
$MESS['rover-acrm__' . Domain::getTemplate() . '-help']        = 'domain of site with current event';
$MESS['rover-acrm__' . SiteName::getTemplate() . '-label']    = 'Current site name';
$MESS['rover-acrm__' . SiteName::getTemplate() . '-help']     = 'name of site with current event';
$MESS['rover-acrm__' . PageUrl::getTemplate() . '-label']     = 'Event\'s page url';
$MESS['rover-acrm__' . PageUrl::getTemplate() . '-help']      = 'url of page with current event';
$MESS['rover-acrm__' . Ip::getTemplate() . '-label']     = 'ip - address';
//$MESS['rover-acrm__' . Ip::getTemplate() . '-help']      = 'url of page with current event';
$MESS['rover-acrm__' . VisitorUid::getTemplate() . '-label']= 'Visitor UID';
$MESS['rover-acrm__' . VisitorUid::getTemplate() . '-help'] = 'Read more in <a href="https://www.amocrm.ru/developers/content/digital_pipeline/site_visit" target="_blank">amoCRM documentation</a>';