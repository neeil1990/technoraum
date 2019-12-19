<?php
namespace Ram\Watermark;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class FilterTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> WMID int mandatory
 * <li> MODULE string(50) mandatory
 * <li> FIELD string(50) optional
 * <li> TYPE string(7) optional
 * <li> GROUP int optional
 * <li> OBJECT string(50) optional
 * <li> ENTITY string(50) optional
 * </ul>
 *
 * @package Ram\Watermark
 **/

class FilterTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'ram_watermark_filter';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('FILTER_ENTITY_ID_FIELD'),
			),
			'WMID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('FILTER_ENTITY_WMID_FIELD'),
			),
			'MODULE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateModule'),
				'title' => Loc::getMessage('FILTER_ENTITY_MODULE_FIELD'),
			),
			'FIELD' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateField'),
				'title' => Loc::getMessage('FILTER_ENTITY_FIELD_FIELD'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateType'),
				'title' => Loc::getMessage('FILTER_ENTITY_TYPE_FIELD'),
			),
			'GROUP' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILTER_ENTITY_GROUP_FIELD'),
			),
			'OBJECT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateObject'),
				'title' => Loc::getMessage('FILTER_ENTITY_OBJECT_FIELD'),
			),
			'ENTITY' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntity'),
				'title' => Loc::getMessage('FILTER_ENTITY_ENTITY_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for MODULE field.
	 *
	 * @return array
	 */
	public static function validateModule()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for FIELD field.
	 *
	 * @return array
	 */
	public static function validateField()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 7),
		);
	}
	/**
	 * Returns validators for OBJECT field.
	 *
	 * @return array
	 */
	public static function validateObject()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for ENTITY field.
	 *
	 * @return array
	 */
	public static function validateEntity()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
}
?>