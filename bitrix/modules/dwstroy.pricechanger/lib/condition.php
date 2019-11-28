<?
namespace Dwstroy\Pricechanger;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
class ConditionTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_dwstroy_pricechanger';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			new Entity\StringField('NAME', array(
				'required' => true,
				'title' => Loc::getMessage('PRICECHANGER_NAME'),
			)),
			new Entity\BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PRICECHANGER_ACTIVE'),
			)),
			new Entity\IntegerField('SORT', array(
				'required' => true,
				'title' => Loc::getMessage('PRICECHANGER_SORT'),
			)),
			new Entity\DatetimeField('DATE_CHANGE', array(
				'title' => Loc::getMessage('PRICECHANGER_DATE_CHANGE'),
			)),
			new Entity\DatetimeField('DATE_EXEC', array(
				'title' => Loc::getMessage('PRICECHANGER_DATE_EXEC'),
			)),
			new Entity\TextField('SITES', array(
				'title' => Loc::getMessage('PRICECHANGER_SITES'),
			)),
			new Entity\TextField('RULE', array(
				'title' => Loc::getMessage('PRICECHANGER_RULE'),
			)),
            new Entity\TextField('ACTIONS', array(
                'title' => Loc::getMessage('PRICECHANGER_ACTIONS'),
            )),
            new Entity\IntegerField('COUNT', array(
                'required' => true,
                'title' => Loc::getMessage('PRICECHANGER_COUNT'),
            )),
            new Entity\IntegerField('INTERVAL', array(
                'required' => false,
                'title' => Loc::getMessage('PRICECHANGER_INTERVAL'),
            )),
            new Entity\BooleanField('PERIOD', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('PRICECHANGER_PERIOD'),
            )),
            new Entity\DatetimeField('NEXT_EXEC', array(
                'title' => Loc::getMessage('PRICECHANGER_NEXT_EXEC'),
            ))
		);
	}
}