<?php
namespace Rover\AmoCRM\Model;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);
/**
 * Class EventsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SOURCE_TYPE string(128) mandatory
 * <li> SOURCE_ID string(32) mandatory
 * <li> PARAMS string mandatory
 * <li> DATE_INSERT datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> STATUS bool optional default 'N'
 * <li> DATE_UPDATE datetime mandatory default '0000-00-00 00:00:00'
 * </ul>
 *
 * @package Bitrix\Amocrm
 **/
class StatusTable extends Entity\DataManager
{
    const STATUS__NEW       = 'N';
    const STATUS__ERROR     = 'E';
    const STATUS__SUCCESS   = 'Y';
    const STATUS__SKIPPED   = 'S';

    /** @var array */
    protected static $statuses = array(
        self::STATUS__NEW,
        self::STATUS__ERROR,
        self::STATUS__SKIPPED,
        self::STATUS__SUCCESS
    );

    /** @var array */
    protected static $countCache = array();

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'rv_amocrm_status';
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
                'primary'   => true,
                'title'     => Loc::getMessage('EVENTS_ENTITY_ID_FIELD'),
            ),
            'SOURCE_TYPE' => array(
                'data_type' => 'string',
                'required'  => true,
                'validation' => array(__CLASS__, 'validateSourceType'),
                'title'     => Loc::getMessage('EVENTS_ENTITY_SOURCE_TYPE_FIELD'),
            ),
            'SOURCE_ID' => array(
                'data_type' => 'string',
                'required'  => true,
                'validation' => array(__CLASS__, 'validateSourceId'),
                'title'     => Loc::getMessage('EVENTS_ENTITY_SOURCE_ID_FIELD'),
            ),
            'EVENT_PARAMS' => array(
                'data_type' => 'text',
                'required'  => true,
                'title'     => Loc::getMessage('EVENTS_ENTITY_PARAMS_FIELD'),
            ),
            'ADDITIONAL_PARAMS' => array(
                'data_type' => 'text',
                'required'  => true,
                'title'     => Loc::getMessage('EVENTS_ENTITY_PARAMS_FIELD'),
            ),
            'DATE_INSERT' => array(
                'data_type' => 'datetime',
                //'required' => true,
                'title' => Loc::getMessage('EVENTS_ENTITY_DATE_INSERT_FIELD'),
            ),
            'STATUS' => array(
                'data_type' => 'string',
                'values'    => self::$statuses,
                'title'     => Loc::getMessage('EVENTS_ENTITY_STATUS_FIELD'),
            ),
            'DATE_UPDATE' => array(
                'data_type' => 'datetime',
                'title'     => Loc::getMessage('EVENTS_ENTITY_DATE_UPDATE_FIELD'),
            ),
        );
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentTypeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function validateSourceType()
    {
        return array(
            new Entity\Validator\Length(null, 63),
        );
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentTypeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function validateSourceId()
    {
        return array(
            new Entity\Validator\Length(null, 63),
        );
    }

    /**
     * @param $sourceType
     * @param $sourceId
     * @param $eventParams
     * @param $additionalParams
     * @return Entity\AddResult
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function addNew($sourceType, $sourceId, $eventParams, $additionalParams)
    {
        $sourceType = trim($sourceType);
        if (!strlen($sourceType))
            throw new ArgumentNullException('sourceType');

        $sourceId = trim($sourceId);
        if (!strlen($sourceId))
            throw new ArgumentNullException('sourceId');

        if (empty($eventParams))
            throw new ArgumentNullException('eventParams');

        $data = array(
            'SOURCE_TYPE'   => $sourceType,
            'SOURCE_ID'     => $sourceId,
            'EVENT_PARAMS'  => serialize($eventParams),
            'ADDITIONAL_PARAMS'  => serialize($additionalParams),
            'DATE_INSERT'   => new DateTime(),
            'DATE_UPDATE'   => new DateTime(),
        );

        return self::add($data);
    }

    /**
     * @param        $id
     * @param string $status
     * @return Entity\UpdateResult
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Exception
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function updateStatus($id, $status = self::STATUS__SUCCESS)
    {
        if (!in_array($status, self::$statuses))
            throw new ArgumentOutOfRangeException('status');

        return self::update($id, array(
            'STATUS'        => $status,
            'DATE_UPDATE'   => new DateTime()
        ));
    }

    /**
     * @param     $status
     * @param int $limit
     * @return \Bitrix\Main\DB\Result
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getByStatus($status, $limit = 0)
    {
        if (empty($status))
            throw new ArgumentNullException('status');

        $query = array(
            'filter'    => array('=STATUS' => $status),
            'order'     => array('ID' => 'ASC')
        );

        $limit = intval($limit);
        if ($limit)
            $query['limit'] = $limit;

        return self::getList($query);
    }

    /**
     * @param bool $reload
     * @return mixed
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getActiveCount($reload = false)
    {
        if (!isset(self::$countCache[self::STATUS__NEW . self::STATUS__ERROR]) || $reload)
            self::$countCache[self::STATUS__NEW . self::STATUS__ERROR] = self::getCount(array('=STATUS' => array(
                self::STATUS__NEW,
                self::STATUS__ERROR
            )));

        return self::$countCache[self::STATUS__NEW . self::STATUS__ERROR];
    }

    /**
     * @param bool $reload
     * @return mixed
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getErrorCount($reload = false)
    {
        if (!isset(self::$countCache[self::STATUS__ERROR]) || $reload)
            self::$countCache[self::STATUS__ERROR] = self::getCount(array('=STATUS' => self::STATUS__ERROR));

        return self::$countCache[self::STATUS__ERROR];
    }

    /**
     * @param bool $reload
     * @return mixed
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getNewCount($reload = false)
    {
        if (!isset(self::$countCache[self::STATUS__NEW]) || $reload)
            self::$countCache[self::STATUS__NEW] = self::getCount(array('=STATUS' => self::STATUS__NEW));

        return self::$countCache[self::STATUS__NEW];
    }

    /**
     * @param int $periodInDays
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function deleteOld($periodInDays = 365)
    {
        $periodInDays = intval($periodInDays);
        if (!$periodInDays) $periodInDays = 365;

        $connection = Application::getConnection();

        $sql = 'DELETE FROM ' . self::getTableName() . ' WHERE (STATUS != "E") AND (STATUS != "N") AND (TO_DAYS(NOW()) - TO_DAYS(DATE_INSERT) > ' . $periodInDays . ')';
       //$sql = 'SELECT ID FROM ' . self::getTableName() . ' WHERE (STATUS != "E") AND (STATUS != "N") AND (TO_DAYS(NOW()) - TO_DAYS(DATE_INSERT) > ' . $periodInDays . ')';

        $connection->queryExecute($sql);
    }
}