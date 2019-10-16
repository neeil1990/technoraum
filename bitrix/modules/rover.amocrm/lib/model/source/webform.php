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
use Rover\AmoCRM\Model\Source;
use Rover\Params\Form;
use Bitrix\Main\UI\PageNavigation;
use Rover\AmoCRM\Model\File;
use Rover\AmoCRM\Config\Options;
/**
 * Class WebForm
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
class WebForm extends Source
{
    /** @var array */
    protected $sid;

    /** @var string */
    protected static $prefix = 'form-field';

    /**
     * @param      $amoObject
     * @param bool $reload
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function loadObjectValues($amoObject, $reload = false)
    {
        if (!isset($this->map[$amoObject]) || $reload){

            $this->map[$amoObject]  = array();
            $questions              = $this->getLabelsCodes();

            foreach ($questions as $questionId)
                $this->loadObjectValueByCode($amoObject, $questionId, $reload);
        }
    }

    /**
     * @param bool $clear
     * @param bool $reload
     * @return array|mixed
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getLabels($clear = false, $reload = false)
    {
        if (empty($this->labels) || $reload)
            $this->labels = Form::getQuestions($this->source->getId(), array(
                'template'  => array('{ID}' => '{TITLE} (#{SID}#)'),
                'filter'    => array('ACTIVE' => 'Y'),
                'empty'     => null
            ));

        return $this->clearLabels($this->labels, $clear);
    }

    /**
     * @return array|null
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getId2Placeholder()
    {
        if (is_null($this->sid)) {
            $this->sid = Form::getQuestions($this->source->getId(), array(
                'template'  => array('{ID}' => '{SID}'),
                'filter'    => array('ACTIVE' => 'Y'),
                'empty'     => null
            ));
        }

        return $this->sid;
    }

    /**
     * @param $itemId
     * @return int
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getResultParamsById($itemId)
    {
        $itemId = intval($itemId);
        if (!$itemId)
            throw new ArgumentNullException('itemId');

        return $itemId;
    }

    /**
     * @param array               $query
     * @param PageNavigation|null $nav
     * @return array
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getResults(array $query = array(), PageNavigation &$nav = null)
    {
        $questionsIds   = $this->getLabelsCodes();
        $items          = $this->getNavList($query, $nav);
        $result         = array();

        if (!is_array($items) || !is_array($questionsIds))
            return $result;

        foreach ($items as $resultId => $answers)
        {
            foreach ($questionsIds as $questionId)
            {
                if (!isset($items[$resultId][$questionId])
                    || !is_array($items[$resultId][$questionId]))
                {
                    continue;
                }

                foreach ($items[$resultId][$questionId] as $keyD => $valD)
                {
                    // check answer id
                    if ($valD['FIELD_ID'] != $questionId)
                        continue;

                    $result[$resultId][$questionId] = self::getAnswerValue($valD);
                }
            }
        }

        return $result;
    }

    /**
     * @param $answer
     * @return null|string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected static function getAnswerValue($answer)
    {
        switch ($answer['FIELD_TYPE']) {
            case 'text':
            case 'date':
            case 'textarea':
            case 'email':
            case 'url':
            case 'password':
            case 'hidden':
                if (!empty($answer['USER_TEXT']))
                    return $answer['USER_TEXT'];

            case 'radio':
            case 'dropdown':
            case 'multiselect':
            case 'checkbox':
                return $answer['ANSWER_TEXT'];

            case 'file':
                return File::getValueById($answer['USER_FILE_ID']);

            default:
                return null;
        }
    }

    /**
     * @param array               $query
     * @param PageNavigation|null $nav
     * @return array|null
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getNavList(array $query = array(), PageNavigation &$nav = null)
    {
        $formId = intval($this->source->getId());
        if (!$formId)
            throw new ArgumentNullException('formId');

        if (!isset($query['filter']))
            $query['filter'] = array();

        if (!isset($query['order']))
            $query['order'] = array('ID' => 'DESC');

        $arrColumns = $arrAnswers = $arrAnswersVarname = null;

        $form = new \CForm;
        $form->GetResultAnswerArray($formId,
            $arrColumns,
            $arrAnswers,
            $arrAnswersVarname,
            $query['filter']);

        $by     = key($query['order']);
        $order  = $query['order'][$by];

        // empty = null
        if (empty($arrAnswers)) $arrAnswers = [];

        if ($by == 'ID') {
            if (strtoupper($order) == 'DESC')
                krsort($arrAnswers);
            else
                ksort($arrAnswers);
        }

        /** @todo: full sorting */
        /*uasort($arrAnswers, function($a, $b) use ($by, $order){

            $aVal = isset($a[$by][$by])
                ? $a[$by][$by]
                : null;

            $bVal = isset($b[$by][$by])
                ? $b[$by][$by]
                : null;

            if (($aVal instanceof Date)
                && ($bVal instanceof Date)) {
                $aVal = $aVal->getTimestamp();
                $bVal = $bVal->getTimestamp();
            }

            if (is_numeric($aVal) && is_numeric($bVal))
                $result = ($aVal > $bVal)
                    ? 1
                    : (($aVal < $bVal)
                        ? -1
                        : 0);
            else
                $result = strnatcmp($aVal, $bVal);

            return strtoupper($order) == 'DESC'
                ? $result * (-1)
                : $result;
        });*/

        if ($nav instanceof PageNavigation){
            $offset = $nav->getOffset();
            $limit  = $nav->getLimit();
            $nav->setRecordCount(count($arrAnswers));

            $result = array();
            $num    = 0;

            foreach ($arrAnswers as $key => $arrAnswer)
            {
                $num++;
                if (($num <= $offset)
                    || $num > $offset + $limit)
                    continue;

                $result[$key] = $arrAnswer;
            }

            return $result;
        }

        return $arrAnswers;
    }

    /**
     * @param $resultId
     * @return bool
     * @author Pavel Shulaev (https://rover-it.me)
     * @todo: return actual site
     */
    public function getSiteIdById($resultId)
    {
        return Options::getCurSiteId();
    }

    /**
     * @param array $filter
     * @return mixed|string
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getResultsCount(array $filter = array())
    {
        return strval(count($this->getNavList(array('filter' => $filter))));
    }
}