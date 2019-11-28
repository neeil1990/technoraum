<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 25.07.2017
 * Time: 12:04
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Helper;

use Bitrix\Main\ArgumentNullException;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Entity\Handler;
use Rover\AmoCRM\Entity\Result;
use \Rover\AmoCRM\Model\Rest\Account;
use \Rover\AmoCRM\Model\Rest;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
/**
 * Class Duplicate
 *
 * @package Rover\AmoCRM\Helper
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Duplicate
{
    const ACTION__ADD_NOTE    = 'contact_duplicate_action__add_note';
    const ACTION__COMBINE     = 'contact_duplicate_action__combine';
    const ACTION__SKIP        = 'contact_duplicate_action__skip';

    const LOGIC__AND    = 'duplicate_logic__and';
    const LOGIC__OR     = 'duplicate_logic__or';
    /**
     * @param Handler $handler
     * @param Result  $result
     * @param array   $data
     * @return array|mixed
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function check(Handler $handler, Result $result, array $data = array())
    {
        if (!$handler->getDuplicateStatus())
            return $data;

        $duplicates = self::filter($data, $handler);

        if (!is_array($duplicates) || !count($duplicates))
            return $data;

        switch ($handler->getDuplicateAction()){
            case self::ACTION__ADD_NOTE:

                $subdomain  = Account::getInstance()->getSubdomain();

                if (strlen($subdomain)) {
                    foreach ($duplicates as $duplicate)
                        $handler->addNote($result, Loc::getMessage('rover-acrm__duplicate'), array(
                            'value'     => "\n" . 'https://' . $subdomain . '.amocrm.ru/' . $handler->getRestName() .'/detail/' . $duplicate['id'],
                            'title'     => Loc::getMessage('rover-acrm__duplicate'),
                            'code'      => 'duplicate',
                        ));
                }

                return $data;

            case self::ACTION__COMBINE:
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/amo.log', print_r($duplicates, 1), FILE_APPEND);
                $duplicate  = reset($duplicates);
                $original   = self::prepareOriginal($handler->getRest()->getById($duplicate['id']));
                $updateName = $handler->getSourceTabValue(Tabs::INPUT__LEAD_DUPLICATE_UPDATE_NAME);

                foreach ($data as $field => $value){

                    if (!is_array($value))
                        $value = trim($value);

                    if (empty($value))
                        continue;

                    if (!$updateName && $field == 'name')
                        continue;

                    // links were added later...
                    if (in_array($field, ['leads_id', 'contacts_id', 'company_id']))
                        continue;

                    $original[$field] = $value;
                }

                $original = self::addLinks($handler, $original);

                return $original;

            case self::ACTION__SKIP:
                $duplicate  = reset($duplicates);
                $original   = self::prepareOriginal($handler->getRest()->getById($duplicate['id']));
                $original   = self::addLinks($handler, $original);

                return $original;

            default:
                return $data;
        }
    }

    /**
     * @param Handler $handler
     * @param         $original
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addLinks(Handler $handler, $original)
    {
        // add leads 2 contact & company
        if ($handler instanceof Handler\Contact) {
            $leadsIds = $handler->getLeadsIds();

            if (is_array($leadsIds))
                foreach ($leadsIds as $leadId)
                    $original = self::addLinkedLeadId($original, $leadId);

            // add company 2 contacts
            if ($handler->getRestType() == Rest\Contact::getType())
                $original = self::addFieldArray($original, 'company_id', $handler->getCompanyId());

            // add contacts 2 company
            if ($handler->getRestType() == Rest\Company::getType())
            {
                $contactsIds = $handler->getContactsIds();
                foreach ($contactsIds as $contactId)
                    $original = self::addFieldArray($original, 'contacts_id', $contactId);
            }
            // add contacts & company 2 lead
        } elseif ($handler instanceof Handler\Lead) {
            $original = self::addFieldArray($original, 'company_id', $handler->getCompanyId());

            $contactsIds = $handler->getContactsIds();
            foreach ($contactsIds as $contactId)
                $original = self::addFieldArray($original, 'contacts_id', $contactId);
        }

        return $original;
    }

    /**
     * @param array $original
     * @param       $fieldName
     * @param       $fieldValue
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addFieldArray(array $original, $fieldName, $fieldValue)
    {
        $fieldName = trim($fieldName);
        if (!strlen($fieldName))
            throw new ArgumentNullException('fieldName');

        $fieldValue = intval($fieldValue);
        if (!$fieldValue)
            return $original;

        if (!isset($original[$fieldName]))
            $original[$fieldName] = array();

        if (!is_array($original[$fieldName]))
            $original[$fieldName] = array($original[$fieldName]);

        if (!in_array($fieldValue, $original[$fieldName]))
            $original[$fieldName][] = $fieldValue;

        return $original;
    }

    /**
     * @param $original
     * @param $leadId
     * @return array
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function addLinkedLeadId($original, $leadId)
    {
        return self::addFieldArray($original, 'leads_id', $leadId);
    }

    /**
     * @param $original
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function prepareOriginal($original)
    {
        unset($original['custom_fields']);
        unset($original['tags']);

        return $original;
    }

    /**
     * @param array   $item
     * @param Handler $handler
     * @return array|bool
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function filter(array $item, Handler $handler)
    {
        $checkFields = $handler->getDuplicateFields();
        if (empty($checkFields))
            return false;

        $select = $checkFields;
        if (!in_array('id', $select))
            $select[] = 'id';

        $compareItems       = $handler->getDuplicateCompareData($select);
        $compareItemsCnt    = count($compareItems);

        if (!$compareItemsCnt)
            return false;

        $duplicates = array();
        $logic      = $handler->getDuplicateLogic();

        for ($i = 0; $i < $compareItemsCnt; ++$i)
        {
            $compareItem = $compareItems[$i];
            if (self::compare($item, $compareItem, $checkFields, $logic))
                $duplicates[$compareItem['id']] = $compareItem;
        }

        ksort($duplicates);

        return $duplicates;
    }

    /**
     * @param        $item1
     * @param        $item2
     * @param array  $checkFields
     * @param string $logic
     * @return bool
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function compare($item1, $item2, array $checkFields, $logic = self::LOGIC__AND)
    {
        $result = $logic == self::LOGIC__AND ? true : false;

        foreach ($checkFields as $checkField)
        {
            if (CustomField::isCustom($checkField)) {
                $customFieldParams = CustomField::getParams($checkField);

                $enumId = isset($customFieldParams['enum'])
                    ? intval($customFieldParams['enum'])
                    : null;

                $newValue = CustomField::getValueFromItemById($item1, $customFieldParams['id'], $enumId);
                $oldValue = CustomField::getValueFromItemById($item2, $customFieldParams['id'], $enumId);

                if (isset($newValue['value']) && isset($oldValue['value'])){
                    $newValue['value'] = trim($newValue['value']);
                    $oldValue['value'] = trim($oldValue['value']);

                    $fieldResult = strlen($newValue['value']) && strlen($oldValue['value'])
                        && $newValue['value'] == $oldValue['value'];

                } else {
                    $fieldResult = false;
                }
            } else {

                switch ($checkField) {
                    case Tabs::INPUT__LEAD_NAME:
                        $checkField = Rest\Lead::FIELD__NAME;
                        break;
                    case Tabs::INPUT__LEAD_STATUS:
                        $checkField = Rest\Lead::FIELD__STATUS_ID;
                        break;
                    case Tabs::INPUT__LEAD_PRICE:
                        $checkField = Rest\Lead::FIELD__SALE;
                }

                $fieldResult = isset($item1[$checkField]) && isset($item2[$checkField])
                    && $item1[$checkField] == $item2[$checkField];
            }

            $result = $logic == self::LOGIC__AND
                ? $result && $fieldResult
                : $result || $fieldResult;

            if (!$result && $logic == self::LOGIC__AND)
                break;
        }

        return $result;
    }
}