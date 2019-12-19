<?php
	namespace Ram\Watermark;

	use Bitrix\Main;
	use Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);

	/**
	 * Class ImageTable
	 * 
	 * Fields:
	 * <ul>
	 * <li> ID int mandatory
	 * <li> IMAGEID int mandatory
	 * <li> WMID int mandatory
	 * <li> FILTERID int mandatory
	 * <li> STATUS string(1) mandatory
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
				'WMID' => array(
					'data_type' => 'integer',
					'required' => true,
					'title' => Loc::getMessage('IMAGE_ENTITY_WMID_FIELD'),
				),
				'FILTERID' => array(
					'data_type' => 'integer',
					'required' => true,
					'title' => Loc::getMessage('IMAGE_ENTITY_FILTERID_FIELD'),
				),
				'STATUS' => array(
					'data_type' => 'string',
					'required' => true,
					'validation' => array(__CLASS__, 'validateStatus'),
					'title' => Loc::getMessage('IMAGE_ENTITY_STATUS_FIELD'),
				),
			);
		}
		/**
		 * Returns validators for STATUS field.
		 *
		 * @return array
		 */
		public static function validateStatus()
		{
			return array(
				new Main\Entity\Validator\Length(null, 1),
			);
		}
	}
?>