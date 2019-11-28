<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 19.07.2018
 * Time: 9:07
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use \Rover\AmoCRM\Entity\Source;

$MESS['rover-acrm__connected']      = 'connected';
$MESS['rover-acrm__disconnected']   = 'disconnected';
$MESS['rover-acrm__account-id']     = 'account #id#';

$MESS['rover-acrm__preset-type-' . Source::TYPE__EVENT]       = 'post-event';
$MESS['rover-acrm__preset-type-' . Source::TYPE__FORM]        = 'web-form';

$MESS['rover-acrm__preset-' . Source::TYPE__FORM . '-exists'] = 'Web-form is already connected';
$MESS['rover-acrm__preset-' . Source::TYPE__EVENT . '-exists']= 'Post-event is already connected';