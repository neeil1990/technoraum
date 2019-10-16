<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 21.02.2016
 * Time: 2:17
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Helper;

use Bitrix\Main\ArgumentNullException;
use \Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Model\Rest;
use Rover\Fadmin\Inputs\Checkbox;
use Bitrix\Main\ArgumentOutOfRangeException;
use Rover\Fadmin\Inputs\Label;
use Rover\Fadmin\Inputs\Number;
use Rover\Fadmin\Inputs\Selectbox;
use Rover\Fadmin\Inputs\Text;
use Rover\Fadmin\Inputs\Textarea;

Loc::loadMessages(__FILE__);

/**
 * Class CustomField
 *
 * @package Rover\AmoCRM\Params
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class CustomField
{
	const CUSTOM_FIELD          = 'custom_field_';

	const TYPE__TEXT            = '1';
	const TYPE__NUMBER          = '2';
	const TYPE__FLAG            = '3';
	const TYPE__ENUM            = '4';
	const TYPE__MULTIENUM       = '5';
	const TYPE__DATE            = '6';
	const TYPE__LINK            = '7';
	const TYPE__MULTIENUM_2     = '8';
	const TYPE__TEXTAREA        = '9';
	const TYPE__RADIO           = '10';
	const TYPE__SHORT_ADDRESS   = '11';
	const TYPE__ADDRESS         = '13';

	const ADDRESS_SYBTYPE__ADDRESS_LINE_1   = 1;
	const ADDRESS_SYBTYPE__ADDRESS_LINE_2   = 2;
	const ADDRESS_SYBTYPE__CITY             = 3;
	const ADDRESS_SYBTYPE__STATE            = 4;
	const ADDRESS_SYBTYPE__ZIP              = 5;
	const ADDRESS_SYBTYPE__COUNTRY          = 6;

    /**
     * @var array
     */
	protected static $smartAddressTypes = array(
	    'address_line_1'    => self::ADDRESS_SYBTYPE__ADDRESS_LINE_1,
	    'address_line_2'    => self::ADDRESS_SYBTYPE__ADDRESS_LINE_2,
	    'city'              => self::ADDRESS_SYBTYPE__CITY,
        'state'             => self::ADDRESS_SYBTYPE__STATE,
        'zip'               => self::ADDRESS_SYBTYPE__ZIP,
        'country'           => self::ADDRESS_SYBTYPE__COUNTRY,
    );

    /**
     * @var array
     */
    protected static $countryCodes = array(
        'RU', 'UA', 'KZ', 'AU', 'AT', 'AZ', 'AX', 'AL', 'DZ', 'VI', 'AS', 'AO', 'AI', 'AD', 'AQ', 'AG', 'AR', 'AM', 'AW',
        'AF', 'BS', 'BD', 'BB', 'BH', 'BY', 'BZ', 'BE', 'BJ', 'BM', 'BG', 'BO', 'BA', 'BW', 'BR', 'BQ', 'IO', 'VG', 'BN',
        'BF', 'BI', 'BT', 'VU', 'VA', 'GB', 'HU', 'VE', 'UM', 'TL', 'VN', 'GA', 'HT', 'GY', 'GM', 'GH', 'GP', 'GT', 'GN',
        'GW', 'DE', 'DD', 'GG', 'GI', 'HN', 'HK', 'LY', 'GD', 'GL', 'GR', 'GE', 'GU', 'DK', 'VD', 'CD', 'JE', 'DJ', 'DO',
        'EG', 'ZM', 'EH', 'NQ', 'ZW', 'PZ', 'IL', 'IN', 'ID', 'JO', 'IQ', 'IE', 'IR', 'IS', 'ES', 'IT', 'YE', 'KY', 'KH',
        'CM', 'CA', 'CT', 'QA', 'KE', 'CY', 'KI', 'CN', 'CC', 'CO', 'KM', 'CG', 'KP', 'CR', 'CI', 'CU', 'KW', 'KG', 'CW',
        'LA', 'LV', 'LS', 'LR', 'LB', 'LT', 'LI', 'LU', 'MU', 'MR', 'MG', 'YT', 'MO', 'MW', 'MY', 'ML', 'MV', 'MT', 'MA',
        'MQ', 'MH', 'MX', 'FX', 'MZ', 'MC', 'MN', 'MS', 'MM', 'NA', 'YD', 'NR', 'ZZ', 'NT', 'NP', 'NE', 'NG', 'AN', 'NL',
        'NI', 'NU', 'NZ', 'NC', 'NO', 'AE', 'PU', 'OM', 'BV', 'JT', 'DM', 'MI', 'IM', 'NF', 'CX', 'BL', 'MF', 'SH', 'WK',
        'CV', 'CK', 'TC', 'HM', 'PK', 'PW', 'PS', 'PA', 'PG', 'PY', 'PE', 'PN', 'PC', 'PL', 'PT', 'PR', 'KR', 'MK', 'MD',
        'RE', 'RW', 'RO', 'US', 'SV', 'WS', 'SM', 'ST', 'SA', 'SZ', 'SJ', 'MP', 'SC', 'PM', 'SN', 'VC', 'KN', 'LC', 'RS',
        'CS', 'SG', 'SY', 'SK', 'SI', 'SB', 'SO', 'SU', 'SD', 'SR', 'SL', 'TJ', 'TH', 'TW', 'TZ', 'TG', 'TK', 'TO', 'TT',
        'TV', 'TN', 'TM', 'TR', 'UG', 'UZ', 'WF', 'UY', 'FO', 'FM', 'FJ', 'PH', 'FI', 'FK', 'FR', 'GF', 'PF', 'TF', 'FQ',
        'HR', 'CF', 'TD', 'ME', 'CZ', 'CL', 'CH', 'SE', 'LK', 'EC', 'GQ', 'ER', 'EE', 'ET', 'ZA', 'GS', 'JM', 'JP',
    );

    /**
     * @param $restType
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getInputs($restType)
	{
		$rest   = Rest::buildByType($restType);
        $result = array();

		if (!method_exists($rest, 'getCustomFields'))
		    return $result;

		$customFields = $rest->getCustomFields();

		foreach ($customFields as $customField){

            if (($customField['field_type'] == self::TYPE__ADDRESS)
                && isset($customField['subtypes']))
            {
                $customField['enums'] = array();
                foreach ($customField['subtypes'] as $id => $value)
                    $customField['enums'][$value['name']] = $value['title'];

                unset($customField['subtypes']);
            }

			if (isset($customField['enums']))
				$customField['enums']
                    = array('0' => Loc::getMessage('rover_acrm__no'))
					+ $customField['enums'];

            $item = array(
                'is_system'     => $customField['is_system'],
                'multiple'      => $customField['is_multiple'],
                'id'            => $customField['id'],
                'field_type'    => $customField['field_type'],
                'sort'          => $customField['sort'],
                'label'         => $customField['name'] . ($customField['is_editable'] ? '' : ' ' . Loc::getMessage('rover_acrm__api-only')),
                'name'          => self::CUSTOM_FIELD . $customField['id']
            );

			switch ($customField['field_type']){
				case self::TYPE__ENUM:
				case self::TYPE__MULTIENUM:
				case self::TYPE__RADIO:
				case self::TYPE__MULTIENUM_2:
                case self::TYPE__ADDRESS:
                    $item['type']       = Selectbox::getType();
                    $item['multiple']   = $item['multiple']
                        || ($customField['field_type'] == self::TYPE__MULTIENUM)
                        || ($customField['field_type'] == self::TYPE__MULTIENUM_2);
                    $item['options']    = $customField['enums'];
                    break;
				case self::TYPE__FLAG:
                    $item['type'] = Checkbox::getType();
					break;
				case self::TYPE__TEXTAREA:
                    $item['type'] = Textarea::getType();
					break;
				case self::TYPE__TEXT:
				case self::TYPE__DATE:
				case self::TYPE__LINK:
				case self::TYPE__SHORT_ADDRESS:
                    $item['type'] = Text::getType();
					break;
				case self::TYPE__NUMBER:
                    $item['type'] = Number::getType();
					break;
				default:
                    $item['type']       = Label::getType();
                    $item['default']    = Loc::getMessage('rover_acrm__not_allowed_in_this_version');
			}

            $result[] = $item;
		}

		return $result;
	}

    /**
     * @param $restType
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getSelectBoxes($restType)
	{
		$customFields   = self::getInputs($restType);
		$selectBoxes    = array();

		foreach ($customFields as $customField)
			if (self::isCustomSelectBox($customField))
				$selectBoxes[] = $customField;

		return $selectBoxes;
	}

    /**
     * @param $restType
     * @param $cfName
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function isCustomSelectBoxByName($restType, $cfName)
    {
        $inputs = self::getInputs($restType);
        foreach ($inputs as $input)
            if ($input['name'] == $cfName)
                return self::isCustomSelectBox($input);

        return false;
    }

    /**
     * @param array $option
     * @return bool
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function isCustomSelectBox(array $option)
	{
		if (!isset($option['type']))
			throw new ArgumentOutOfRangeException('type');

		return ((($option['type'] == Selectbox::getType())
			    || ($option['type'] == Checkbox::getType()))
            && ($option['field_type'] != self::TYPE__ADDRESS))
            && !$option['is_system'];
	}

    /**
     * @param $key
     * @param $value
     * @return array|bool
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function prepare2request($key, $value)
	{
        $value = trim($value);
        if (!strlen($value))
            return false;

		$customFieldParams = self::getParams($key);
		if (!$customFieldParams)
			return false;

		$values = array('value' => $value);

		if (isset($customFieldParams['enum']))
			$values['enum'] = $customFieldParams['enum'];

		if (isset($customFieldParams['subtype'])){
            $values['subtype'] = $customFieldParams['subtype'];
            // try to get country name
            if ($values['subtype'] == 'country')
                $values['value'] = self::getCountryCodeByName($values['value']);
                if  (empty($values['value']))
                    return false;
        }

		return array(
			'id'        => $customFieldParams['id'],
			'values'    => array($values)
        );
	}

    /**
     * @param $fullName
     * @return bool|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getIdByName($fullName)
    {
        $fullName = trim($fullName);
        if (!strlen($fullName))
            return false;

        if (strpos($fullName, self::CUSTOM_FIELD) === false)
            return false;

        return substr($fullName, strlen(self::CUSTOM_FIELD));
    }

    /**
     * @param $id
     * @return string
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getNameById($id)
    {
        $id = trim($id);
        if (!$id)
            throw new ArgumentNullException('id');

        return self::CUSTOM_FIELD . $id;
    }

    /**
     * @param $key
     * @return bool
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function isCustom($key)
    {
        return (bool)self::getIdByName($key);
    }

    /**
     * @param $key
     * @return array|bool
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public static function getParams($key)
	{
		$customFieldId = self::getIdByName($key);
		if (!$customFieldId)
		    return false;

        $result = array();

		// get enum id
        if (strpos($customFieldId, ':')){
            list($customFieldId, $enumId) = explode(':', $customFieldId);
            if (isset(self::$smartAddressTypes[$enumId]))
                $result['subtype'] = $enumId;
            else
                $result['enum'] = $enumId;
        }

		$result['id'] = $customFieldId;

		return $result;
	}

    /**
     * @param $name
     * @return mixed|null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getCountryCodeByName($name)
    {
        $name = trim($name);
        if (!strlen($name))
            return null;

        $name = mb_strtoupper($name);

        foreach (self::$countryCodes as $code){

            if ($code == $name)
                return $code;

            if (mb_strtoupper(Loc::getMessage('rover-acrm__country-' . $code)) == $name)
                return $code;
        }

        return null;
    }

    /**
     * @param $customFields
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function groupValues($customFields)
    {
        $result = array();
        foreach ($customFields as $customField)
        {
            if (isset($result[$customField['id']]))
                $result[$customField['id']]['values']
                    = array_merge($result[$customField['id']]['values'], $customField['values']);
            else
                $result[$customField['id']] = $customField;
        }

        return $result;
    }

    /**
     * @param      $item
     * @param      $id
     * @param null $enumId
     * @return mixed|null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getValueFromItemById($item, $id, $enumId = null)
    {
        $customField    = self::getFromItemById($item, $id);
        $enumId         = intval($enumId);

        if ($enumId)
            return self::getEnumValueFromFieldByEnumId($customField, $enumId);

        return is_array($customField['values']) && count($customField['values'])
            ? reset($customField['values'])
            : null;
    }

    /**
     * @param array $entity
     * @param       $id
     * @return null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getFromItemById(array $entity, $id)
    {
        $id = intval($id);
        if (!$id)
            return null;

        if (!is_array($entity)
            || !array_key_exists('custom_fields', $entity))
            return null;

        foreach ($entity['custom_fields'] as $customField)
            if ($customField['id'] == $id)
                return $customField;

        return null;
    }

    /**
     * @param $customField
     * @param $enumId
     * @return null
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function getEnumValueFromFieldByEnumId($customField, $enumId)
    {
        $enumId = intval($enumId);
        if (!$enumId)
            return null;

        if (!is_array($customField) || !array_key_exists('values', $customField))
            return null;

        foreach ($customField['values'] as $value)
            if ($value['enum'] == $enumId)
                return $value;

        return null;
    }
}