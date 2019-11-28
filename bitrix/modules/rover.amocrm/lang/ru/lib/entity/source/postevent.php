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

$MESS['rover-acrm__' . PostEvent::FIELD__FILES . '-label']  = 'Файлы';
$MESS['rover-acrm__' . PostEvent::FIELD__FILES . '-help']   = 'Поддерживаются файлы, переданные непосредственно через почтовое событие (файлы пользователя).<br>Файлы, прикреплённые к шаблонам писем, не передаются.<br>О дополнительных способах передачи файлов, см. <a href="https://github.com/pavelshulaev/amocrm/blob/master/events/onbeforeamopush.md">документацию</a>';
$MESS['rover-acrm__' . Source::TYPE__EVENT . '-label'] = 'Почтовое событие';