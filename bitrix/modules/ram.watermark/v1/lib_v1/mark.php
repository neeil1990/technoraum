<?
	namespace Ram\Watermark;

	use Bitrix\Main;
	use Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);

	/**
	 * Class MarkTable
	 * 
	 * Fields:
	 * <ul>
	 * <li> ID int mandatory
	 * <li> NAME string(255) mandatory
	 * <li> PARAMS string mandatory
	 * </ul>
	 *
	 * @package Ram\Watermark
	 **/

	class MarkTable extends Main\Entity\DataManager
	{
		/**
		 * Returns DB table name for entity.
		 *
		 * @return string
		 */
		public static function getTableName()
		{
			return 'ram_watermark_mark';
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
					'title' => Loc::getMessage('MARK_ENTITY_ID_FIELD'),
				),
				'NAME' => array(
					'data_type' => 'string',
					'required' => true,
					'validation' => array(__CLASS__, 'validateName'),
					'title' => Loc::getMessage('MARK_ENTITY_NAME_FIELD'),
				),
				'PARAMS' => array(
					'data_type' => 'text',
					'required' => true,
					'title' => Loc::getMessage('MARK_ENTITY_PARAMS_FIELD'),
					'serialized' => true,
				),
			);
		}
		/**
		 * Returns validators for NAME field.
		 *
		 * @return array
		 */
		public static function validateName()
		{
			return array(
				new Main\Entity\Validator\Length(null, 255),
			);
		}
	}
?>