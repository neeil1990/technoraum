<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 04.09.2017
 * Time: 17:31
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */
namespace Rover\AmoCRM\Entity\Result;

use Bitrix\Main\ArgumentNullException;
use Rover\AmoCRM\Config\Options;
use Rover\AmoCRM\Entity\Result;

/**
 * Class WebForm
 *
 * @package Rover\AmoCRM\Entity\Source\Value
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class WebForm extends Result
{
    /**
     * @return mixed
     * @throws ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function loadData()
    {
        $resultId = intval($this->eventParams);
        if (!$resultId)
            throw new ArgumentNullException('resultId');

        return reset($this->source->model->getResults(
                array('filter' => array("RESULT_ID" => $resultId))));
    }

    /**
     * @author Pavel Shulaev (https://rover-it.me)
     * @todo: load from event params
     */
    public function loadSiteId()
    {
        $this->additionalParams[self::FIELD__SITE_ID] = Options::getCurSiteId();
    }
}