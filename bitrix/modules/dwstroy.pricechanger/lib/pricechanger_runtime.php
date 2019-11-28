<?
namespace Dwstroy\Pricechanger;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
class RuntimeTable extends Entity\DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_dwstroy_pricechanger_runtime';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
            )),
            new Entity\IntegerField('COND_ID', array(
                'required' => true,
                'title' => Loc::getMessage('PRICECHANGER_COND_ID'),
            )),
            new Entity\StringField('RULES', array(
                'required' => true,
                'title' => Loc::getMessage('PRICECHANGER_RULES'),
            )),
            new Entity\StringField('ACTIONS', array(
                'required' => true,
                'title' => Loc::getMessage('PRICECHANGER_ACTIONS'),
            )),
            new Entity\IntegerField('PAGES', array(
                'required' => true,
                'title' => Loc::getMessage('PRICECHANGER_PAGES'),
            )),
            new Entity\IntegerField('PAGE', array(
                'required' => true,
                'title' => Loc::getMessage('PRICECHANGER_PAGE'),
            )),
            new Entity\IntegerField('CNT', array(
                'required' => true,
                'title' => Loc::getMessage('PRICECHANGER_CNT'),
            )),
            new Entity\IntegerField('RUNNING', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('PRICECHANGER_RUNNING'),
            ))
        );
    }
}