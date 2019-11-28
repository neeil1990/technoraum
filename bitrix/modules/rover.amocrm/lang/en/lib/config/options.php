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

$MESS['rover-acrm__preset-add-success']  = 'Connection successfully created';
$MESS['rover-acrm__preset-del-success']  = 'Connection deleted';

$MESS[Tabs::INPUT__UNSORTED_NAME . '_default_' . Source::TYPE__FORM]  = '«Unsorted» from form «#NAME#»';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_default_' . Source::TYPE__EVENT] = '«Unsorted» from event «#NAME#»';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_help_' . Source::TYPE__FORM]     = 'Available placeholder #NAME# - name of current web-form.<br>If the field is empty, the value is taken by default.';
$MESS[Tabs::INPUT__UNSORTED_NAME . '_help_' . Source::TYPE__EVENT]    = 'Available placeholder #NAME# - name of current post-event.<br>If the field is empty, the value is taken by default.';

$MESS[Tabs::INPUT__LEAD_NAME . '_help_' . Source::TYPE__FORM]     = 'Placeholders available:<br>#FORM_NAME# - name of current web-form;<br>#***# - other fields (replace *** to field name)<br>If the field is empty, the value is taken by default.';
$MESS[Tabs::INPUT__LEAD_NAME . '_help_' . Source::TYPE__EVENT]    = 'Placeholders available:<br>#EVENT_NAME# - name of current post-event;<br>#***# - other fields (replace *** to field name)<br>If the field is empty, the value is taken by default.';
$MESS[Tabs::INPUT__LEAD_NAME . '_default_' . Source::TYPE__FORM]  = 'Deal from web-form «#FORM_NAME#»';
$MESS[Tabs::INPUT__LEAD_NAME . '_default_' . Source::TYPE__EVENT] = 'Deal from post-event «#EVENT_NAME#»';