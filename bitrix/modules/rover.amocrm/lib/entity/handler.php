<?php
namespace Rover\AmoCRM\Entity;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Config\Tabs;
use Rover\AmoCRM\Helper\CustomField;
use Rover\AmoCRM\Helper\HandlerField;
use Rover\AmoCRM\Helper\Placeholder;
use Rover\AmoCRM\Model\Rest;
use Rover\Fadmin\Inputs\Checkbox;
use Rover\Fadmin\Inputs\Input;
use Rover\Fadmin\Inputs\Label;
use Rover\Fadmin\Tab;

/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 11.05.2017
 * Time: 13:45
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
abstract class Handler
{
    const FORMAT__HTML  = 'html';
    const FORMAT__TEXT  = 'text';

    /**
     * @var int
     */
    protected $lastId;

    /** @var Source */
	protected $source;

    /** @var array */
	protected $pushDataCache = array();

    /** @var array */
	protected $resultFieldsCache = array();

    /** @var */
    protected $duplicateAction;

    /**
     * Handler constructor.
     *
     * @param Source $source
     */
	public function __construct(Source $source)
	{
        $this->source = $source;
	}

    /**
     * @param bool $reload
     * @return mixed|null|Rest|Rest\Lead|Rest\Company|Rest\Contact
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getRest($reload = false)
    {
        return Rest::buildByType(static::getRestType(), $reload);
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getLastId()
    {
        return $this->lastId;
    }

    /**
     * @return string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDetailUrl()
    {
        $lastId = $this->getLastId();
        if (empty($lastId)) return '';

        $rest = static::getRest();
        if (!$rest instanceof Rest) return '';

        $name   = $rest::getName();
        if (empty($name)) return '';

        $domain = Option::get(Options::MODULE_ID, Tabs::INPUT__SUB_DOMAIN);
        if (empty($domain)) return '';

        return 'https://' . $domain . '.amocrm.ru/' . $name . '/detail/' . $lastId;
    }

    /**
     * @return string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getRestType()
    {
        $className = get_called_class();

        return strtolower(substr($className, strrpos($className, '\\') + 1));
    }

    /**
     * @param bool $reload
     * @return mixed|null|Tab
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getSourceTab($reload = false)
    {
        return $this->source->getTab($reload);
    }

    /**
     * @param      $valueId
     * @param bool $reload
     * @return array|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getSourceTabValue($valueId, $reload = false)
    {
        $valueId = trim($valueId);
        if (!$valueId)
            throw new ArgumentNullException('valueId');

        return $this->getSourceTab()->getInputValue($valueId, $reload);
    }

    /**
     * @return Source
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getRestName()
    {
        $rest = static::getRest();

        return $rest::getName();
    }

    /**
	 * @param $fieldValues
	 * @return array
	 * @author Pavel Shulaev (https://rover-it.me)
	 */
	protected function ejectValues($fieldValues)
	{
		$result = array();

		foreach ($fieldValues as $fieldValue)
			if (is_array($fieldValue) && isset($fieldValue['value']))
                $result[] = $fieldValue['value'];

		return $result;
	}

    /**
     * @param        $value
     * @param string $delimiter
     * @return string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function prepareValue($value, $delimiter = ', ')
	{
		$value = implode($delimiter, $value);
        $value = $this->formatText($value);

		return trim($value);
	}

    /**
     * @param $customFields
     * @param $values
     * @param $keyC
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function addCustomField($customFields, $values, $keyC)
    {
        if (empty($customFields))
            $customFields = array();

        $value          = $this->prepareValue($values);
        $customField    = CustomField::prepare2request($keyC, $value);

        if (false !== $customField)
            $customFields[] = $customField;

        return $customFields;
    }

    /**
     * @param array $data
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function addResponsibleId(array $data)
    {
        $responsibleId = intval($this->getSourceTabValue(Tabs::INPUT__MAIN_USER));
        if (is_null($responsibleId))
            throw new ArgumentNullException('responsibleId');

        $data['responsible_user_id'] = $responsibleId;

        return $data;
    }

    /**
     * @param Result $result
     * @param array  $data
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function addVisitorUid(Result $result, array $data)
    {
        if (!$this->getSourceTabValue(Tabs::INPUT__LEAD_VISITOR_UID))
            return $data;

        $visitorUid = $result->getVisitorUid();
        if (!strlen($visitorUid))
            return $data;

        $data['visitor_uid'] = $visitorUid;

        return $data;
    }

    /**
     * @param $value
     * @return string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function formatText($value)
	{
	    $format = $this->getSourceTabValue(Tabs::INPUT__FORMAT);

        if ($format == self::FORMAT__TEXT)
            $value = strip_tags(preg_replace('/<[\/]*br[^>]*>/Usi', "\r\n", $value));

        return $value;
	}

    /**
     * @param Result $result
     * @param        $string
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	protected function replacePlaceholders(Result $result, $string)
    {
        return Placeholder::replace($this, $result, $string);
    }

    /**
     * @param Result $result
     * @param bool   $reload
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function loadFieldsData(Result $result, $reload = false)
    {
        if (!isset($this->resultFieldsCache[$result->getCacheId()]) || $reload)
            $this->resultFieldsCache[$result->getCacheId()]
                = HandlerField::getFullObjectData($this, $result);
    }

    /**
     * @param $data
     * @param $object
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function addCustomSelectBoxes($data, $object)
    {
        // add selectboxes
        $fields = CustomField::getSelectBoxes($object);

        if (!$fields)
            return $data;

        foreach ($fields as $field) {

            if ($field['type'] == Label::getType())
                continue;

            $input = $this->getSourceTab()->searchOneByName(CustomField::CUSTOM_FIELD . $field['id']);
            if (!$input instanceof Input)
                continue;

            $value = $input->getValue();

            if (($input->getType() == Checkbox::getType())
                && ($value != 'Y'))
                $value = null;

            if (!$value)
                continue;

            $values = ($input->isMultiple())
                ? $value
                : array(array('value' => $value));

            $data['custom_fields'][] = array(
                'id'        => $field['id'],
                'values'    => $values
            );
        }

        return $data;
    }


    /**
     * @param Result $result
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getSourceTags(Result $result)
    {
        $sourceTags = $this->getSourceTabValue(Tabs::INPUT__TAG);

        return $this->replacePlaceholders($result, $sourceTags);
    }

    /**
     * @param        $data
     * @param Result $result
     * @param        $object
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function addCustomFields($data, Result $result, $object)
    {
        $fields         = $this->getFields($result);
        $customFields   = array();

        if (!is_array($fields))
            return $data;

        foreach($fields as $keyC => $fieldValues) {

            $values = $this->ejectValues($fieldValues);

            // add budget 2 lead
            if (($keyC == Tabs::INPUT__LEAD_PRICE)
                && ($object == Rest\Lead::getType()))
            {
                foreach ($values as $budget){
                    $budget = str_replace(',', '.', $budget);
                    $data[Rest\Lead::FIELD__SALE] += floatval(preg_replace('~[^0-9.]+~', '', $budget));
                }

                continue;
            }

            // add name 2 contact/company
            if ((($keyC == Rest\Contact::FIELD__NAME)
                    && ($object == Rest\Contact::getType()))
                ||  (($keyC == Rest\Company::FIELD__NAME)
                    && ($object == Rest\Company::getType())))
            {
                $data['name'] = $this->prepareValue($values, ' ');
                continue;
            }

            /**  @TODO: add selectboxes
             if (CustomField::isCustomSelectBoxByName($this->getRestType(), $keyC))
            {
                $finalValues = [];
                // search/create values
                foreach ($values as $value)
                {

                }
            }*/

            $customFields = $this->addCustomField($customFields, $values, $keyC);
        }

        $data['custom_fields'] = CustomField::groupValues($customFields);

        return $data;
    }

    /**
     * @param Result $result
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getFields(Result $result)
    {
        $this->loadFieldsData($result);

        return $this->resultFieldsCache[$result->getCacheId()]['fields'];
    }

    /**
     * @param Result $result
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getNotes(Result $result)
    {
        $this->loadFieldsData($result);

        return $this->resultFieldsCache[$result->getCacheId()]['notes'];
    }

    /**
     * @param Result $result
     * @param        $key
     * @param        $note
     * @return $this
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function addNote(Result $result, $key, $note)
    {
        $this->loadFieldsData($result);

        $this->resultFieldsCache[$result->getCacheId()]['notes'][$key][] = $note;

        return $this;
    }

    /**
     * @param $action
     * @return $this
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function setDuplicateAction($action)
    {
        $this->duplicateAction = $action;

        return $this;
    }

    /**
     * @param      $inputName
     * @param bool $reload
     * @return array|null|string
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getDuplicateActionByInput($inputName, $reload = false)
    {

        if (empty($this->duplicateAction) || $reload)
            $this->duplicateAction = $this->getSourceTabValue($inputName);

        return $this->duplicateAction;
    }

    /**
     * @param Result $result
     * @param bool   $reload
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
	abstract public function getPushData(Result $result, $reload = false);

    /**
     * @param bool $reload
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getDuplicateStatus($reload = false);

    /**
     * @param bool $reload
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getDuplicateAction($reload = false);

    /**
     * @param bool $reload
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getDuplicateLogic($reload = false);

    /**
     * @param $select
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getDuplicateCompareData($select);

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract public function getDuplicateFields();

    /**
     * @param Result $result
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
	abstract public function push(Result $result);
}