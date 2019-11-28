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

$MESS['rover-apl__header-NAME']         = 'Name';
$MESS['rover-apl__header-TYPE']         = 'Type';
$MESS['rover-apl__header-SITE']         = 'Site';
$MESS['rover-apl__header-ACTIVE']       = 'Active';
$MESS['rover-apl__header-UNSORTED']     = '«Unsorted»';
$MESS['rover-apl__header-LEAD']         = 'Lead';
$MESS['rover-apl__header-CONTACT']      = 'Contact';
$MESS['rover-apl__header-COMPANY']      = 'Company';
$MESS['rover-apl__header-TASK']         = 'Task';
$MESS['rover-apl__header-ELEMENTS_CNT'] = 'Results';

$MESS['rover-apl__action-remove']       = 'Remove';
$MESS['rover-apl__action-remove_title'] = 'Delete checked elements';
$MESS['rover-apl__action-remove_success'] = 'Elements successfully deleted';
$MESS['rover-apl__action-confirm']      = 'Confirm action for checked elements';
$MESS['rover-apl__action-update']       = 'Update';
$MESS['rover-apl__action-update_success'] = 'Elements successfully updated';
$MESS['rover-apl__action-elements']     = 'Results';
$MESS['rover-apl__title-results']       = 'Go to the results «#preset-name#»';
$MESS['rover-apl__title-settings']       = 'Go to rule settings «#preset-name#»';
$MESS['rover-apl__unavailable']             = 'n/a';
$MESS['rover-apl__no']                  = 'No';
$MESS['rover-apl__title-task-unavailable']  = 'Creating a task is impossible, because included the creation of "unsorted"';
$MESS['rover-apl__action-cancel']       = 'Cancel';
$MESS['rover-apl__action-add']          = 'Add';
$MESS['rover-apl__action-add_title']    = 'Add integration rule';
$MESS['rover-apl__action-add_' . Source::TYPE__FORM]     = 'Web form';
$MESS['rover-apl__action-add_' . Source::TYPE__EVENT]    = 'Post event';

$MESS['rover-apl__action-settings']         = 'Module settings';
$MESS['rover-apl__action-settings_title']   = 'Module settings';
$MESS['rover-apl__title']                   = 'List of rules for integrating with amoCRM';
$MESS['rover-apl__error_delete']            = 'Filed to remove: #error#';
$MESS['rover-apl__no-connection']           = 'There is no connection with amoCRM';