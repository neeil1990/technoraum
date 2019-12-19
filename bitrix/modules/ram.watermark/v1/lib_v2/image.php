<?php
namespace Ram\Watermark;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class ImageTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> IMAGEID int mandatory
 * <li> WIDTH int optional
 * <li> HEIGHT int optional
 * <li> TYPE string(50) optional
 * <li> MODULE string(50) optional
 * <li> ENTITY int optional
 * <li> OBJECT int optional
 * <li> FIELD string(50) optional
 * <li> DATE datetime optional
 * <li> HASH string(50) optional
 * <li> ITEM string(50) optional
 * <li> TAG string(50) optional
 * </ul>
 *
 * @package Ram\Watermark
 **/

class ImageTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'ram_watermark_image';
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
				'title' => Loc::getMessage('IMAGE_ENTITY_ID_FIELD'),
			),
			'IMAGEID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('IMAGE_ENTITY_IMAGEID_FIELD'),
			),
			'WIDTH' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IMAGE_ENTITY_WIDTH_FIELD'),
			),
			'HEIGHT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IMAGE_ENTITY_HEIGHT_FIELD'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateType'),
				'title' => Loc::getMessage('IMAGE_ENTITY_TYPE_FIELD'),
			),
			'MODULE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateModule'),
				'title' => Loc::getMessage('IMAGE_ENTITY_MODULE_FIELD'),
			),
			'ENTITY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IMAGE_ENTITY_ENTITY_FIELD'),
			),
			'OBJECT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('IMAGE_ENTITY_OBJECT_FIELD'),
			),
			'FIELD' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateField'),
				'title' => Loc::getMessage('IMAGE_ENTITY_FIELD_FIELD'),
			),
			'DATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('IMAGE_ENTITY_DATE_FIELD'),
			),
			'HASH' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateHash'),
				'title' => Loc::getMessage('IMAGE_ENTITY_HASH_FIELD'),
			),
			'ITEM' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateItem'),
				'title' => Loc::getMessage('IMAGE_ENTITY_ITEM_FIELD'),
			),
			'TAG' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTag'),
				'title' => Loc::getMessage('IMAGE_ENTITY_TAG_FIELD'),
			),
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
			new Main\Entity\Validator\Length(null, 50),
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
	 * Returns validators for HASH field.
	 *
	 * @return array
	 */
	public static function validateHash()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for ITEM field.
	 *
	 * @return array
	 */
	public static function validateItem()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for TAG field.
	 *
	 * @return array
	 */
	public static function validateTag()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
}
?>