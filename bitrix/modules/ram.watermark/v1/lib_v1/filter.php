<?
	namespace Ram\Watermark;

	use Bitrix\Main;
	use Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);

	/**
	 * Class FilterTable
	 * 
	 * Fields:
	 * <ul>
	 * <li> ID int mandatory
	 * <li> WMID int mandatory
	 * <li> MODULE string(50) mandatory
	 * <li> OBJECT int mandatory
	 * <li> FIELD string(50) mandatory
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
				'OBJECT' => array(
					'data_type' => 'integer',
					'required' => true,
					'title' => Loc::getMessage('FILTER_ENTITY_OBJECT_FIELD'),
				),
				'FIELD' => array(
					'data_type' => 'string',
					'validation' => array(__CLASS__, 'validateField'),
					'title' => Loc::getMessage('FILTER_ENTITY_FIELD_FIELD'),
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
	}
?>