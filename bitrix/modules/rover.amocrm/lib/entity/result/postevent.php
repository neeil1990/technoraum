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

use Rover\AmoCRM\Config\Options;

use Rover\AmoCRM\Entity\Result;
use Rover\AmoCRM\Entity\Source;
use Rover\AmoCRM\Model\File;

/**
 * Class PostEvent
 *
 * @package Rover\AmoCRM\Entity\Source\Value
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class PostEvent extends Result
{
    /**
     * @return array|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function loadData()
    {
        $data = array();

        foreach ($this->eventParams as $paramName => $paramValue)
        {
            if ($paramName == Source\PostEvent::FIELD__FILES){
                $filesIds   = $paramValue;
                $value      = array();

                if (!is_array($filesIds))
                    $filesIds = array($filesIds);

                foreach ($filesIds as $fileId)
                    $value[] = File::getValueById($fileId);

                $paramValue = implode("\n", $value);
            }

            $data[$paramName] = $paramValue;
        }

        return $data;
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