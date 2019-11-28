<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 04.09.2017
 * Time: 16:02
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
namespace Rover\AmoCRM\Model\Source;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Model\Source;
use Rover\Fadmin\Inputs\Input;
use \Rover\AmoCRM\Entity\Source as SourceEntity;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Mail\Internal\EventTable;
/**
 * Class WebForm
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
class PostEvent extends Source
{
    /** @var string */
    protected static $prefix = 'event-field';

    /** @var array */
    protected $questions;

    /**
     * @param      $amoObject
     * @param bool $reload
     * @return mixed
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function loadObjectValues($amoObject, $reload = false)
    {
        if (!isset($this->map[$amoObject]) || $reload){

            $this->map[$amoObject]  = array();
            $arrInputs              = $this->source->getInputs($amoObject);
            $tab                    = $this->source->getTab($reload);

            foreach ($arrInputs as $arrInput) {

                /** @var Input $input */
                $input  = Input::factory($arrInput, $tab);
                $name   = $input->getName();
                $field  = substr($name, strrpos($name, ':') + 1);

                $this->map[$amoObject][$field] = $input->getValue();
            }
        }

        return $this->map[$amoObject];
    }

    /**
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function getFields()
    {
        $type           = $this->source->getData();
        $descriptionRaw = explode("\n", $type['DESCRIPTION']);
        $description    = array();

        foreach ($descriptionRaw as $key => $text)
            if (strlen(trim($text)))
                $description[] = $text;

        return $description;
    }

    /**
     * @param bool $clear
     * @param bool $reload
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getLabels($clear = false, $reload = false)
    {
        if (empty($this->labels) || $reload){

            $fields         = $this->getFields();
            $this->labels   = array();

            foreach ($fields as $fieldLabel) {
                if (!preg_match('/#([^#]*)#/Umsi', $fieldLabel, $match))
                    continue;

                $name = trim($match[1]);
                if (!$name)
                    continue;

                $fixedFieldLabel = str_replace($match[0], '', $fieldLabel);
                $fixedFieldLabel = trim($fixedFieldLabel, ' -');
                $fixedFieldLabel = $fixedFieldLabel . ' (' . $match[0] . ')';

                $this->labels[$name] = $fixedFieldLabel;
            }

            // add files
            $this->labels[SourceEntity\PostEvent::FIELD__FILES] = Loc::getMessage('rover-acrm__' . SourceEntity\PostEvent::FIELD__FILES . '-label');
        }

        return $this->clearLabels($this->labels, $clear);
    }

    /**
     * @param $itemId
     * @return mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getResultParamsById($itemId)
    {
        $itemId = intval($itemId);
        if (!$itemId)
            throw new ArgumentNullException('itemId');

        $item = reset($this->getResults(array('filter' => array('=ID' => $itemId))));
        if (!$item)
            throw new ArgumentOutOfRangeException('itemId');

        return $item;
    }

    /**
     * @param array               $query
     * @param PageNavigation|null $nav
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getResults(array $query = array(), PageNavigation &$nav = null)
    {
        $items  = $this->getNavList($query, $nav);
        $fields = $this->getLabels();
        $result = array();

        while ($item = $items->fetch())
        {
            $resultItem = array();//[/*'ID' => $item['ID']*/];

            if (!is_array($item['C_FIELDS']))
                $item['C_FIELDS'] = unserialize($item['C_FIELDS']);

            foreach ($fields as $fieldCode => $fieldLabel)
                if (isset($item['C_FIELDS'][$fieldCode]))
                    $resultItem[$fieldCode] = $item['C_FIELDS'][$fieldCode];

            /* $resultItem['DATE_INSERT'] = $item['DATE_INSERT'];*/

            $result[$item['ID']] = $resultItem;
        }

        return $result;
    }

    /**
     * @param array               $query
     * @param PageNavigation|null $nav
     * @return \Bitrix\Main\DB\Result|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getNavList(array $query = array(), PageNavigation &$nav = null)
    {
        if (!isset($query['filter']))
            $query['filter'] = array();

        $query['filter'] = $this->addEventNameToFilter($query['filter']);

        if ($nav instanceof PageNavigation) {
            $query['limit']         = $nav->getLimit();
            $query['offset']        = $nav->getOffset();
            $query['count_total']   = true;
        }

        if (!isset($query['order']))
            $query['order'] = array('ID' => 'DESC');

        $items = EventTable::getList($query);

        if ($nav instanceof PageNavigation)
            $nav->setRecordCount($items->getCount());

        return $items;
    }

    /**
     * @param array $filter
     * @return array
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function addEventNameToFilter(array $filter = array())
    {
        $event = $this->source->getData();
        $filter['=EVENT_NAME'] = $event['EVENT_NAME'];

        return $filter;
    }

    /**
     * @param $resultId
     * @return mixed
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getSiteIdById($resultId)
    {
        $resultId = intval($resultId);
        if (!$resultId)
            throw new ArgumentNullException('resultId');

        $query = array(
            'filter' => array('=ID' => $resultId),
            'select' => array('LID')
        );

        $result = self::getNavList($query)->fetch();
        if (!$result)
            throw new ArgumentOutOfRangeException('resultId');

        return $result['LID'];
    }

    /**
     * @param array $filter
     * @return int|mixed
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getResultsCount(array $filter = array())
    {
        $filter = $this->addEventNameToFilter($filter);

        return EventTable::getCount($filter);
    }
}