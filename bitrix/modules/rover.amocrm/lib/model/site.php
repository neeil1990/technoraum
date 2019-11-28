<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 16.10.2017
 * Time: 20:00
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Model;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\SiteTable;

/**
 * Class Site
 *
 * @package Rover\AmoCRM\Model
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class Site
{
    /** @var array */
    protected static $sites = array();

    /** @var */
    protected static $firstSite;

    /**
     * @param $id
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getById($id)
    {
        $id = trim($id);
        if (!$id)
            throw new ArgumentNullException('siteId');

        if (!isset(self::$sites[$id]))
            self::$sites[$id] = SiteTable::getRowById($id);

        return self::$sites[$id];
    }

    /**
     * @return array|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getFirst()
    {
        if (!isset(self::$firstSite))
            self::$firstSite = SiteTable::getRow(array('order' => array('SORT' => 'ASC')));

        return self::$firstSite;
    }

    /**
     * @param $id
     * @param $field
     * @return null
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getFieldById($id, $field)
    {
        $field = trim($field);
        if (!strlen($field))
            throw new ArgumentNullException('field');

        $site = self::getById($id);
        if (isset($site[$field]))
            return $site[$field];

        return null;
    }
}