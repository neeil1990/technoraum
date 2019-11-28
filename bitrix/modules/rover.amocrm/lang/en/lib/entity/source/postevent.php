<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 09.07.2017
 * Time: 17:50
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
use Rover\AmoCRM\Entity\Source\PostEvent;
use \Rover\AmoCRM\Entity\Source;

$MESS['rover-acrm__' . PostEvent::FIELD__FILES . '-label'] = 'Files';
$MESS['rover-acrm__' . PostEvent::FIELD__FILES . '-help'] = 'Post event files only.<br>Files attached to messages will not be send.<br>See more about files transfer in <a href="https://github.com/pavelshulaev/amocrm/blob/master/events/onbeforeamopush.md">documentation</a>';
$MESS['rover-acrm__' . Source::TYPE__EVENT . '-label'] = 'Post event';