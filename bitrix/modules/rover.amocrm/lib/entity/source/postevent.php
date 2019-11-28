<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 18.02.2016
 * Time: 18:40
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Entity\Source;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;
use Rover\AmoCRM\Model\EventType;
use Rover\AmoCRM\Entity\Source;
use Rover\Params\Main;

Loc::loadMessages(__FILE__);
/**
 * Class PostEvent
 *
 * @package Rover\AmoCRM\Params
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class PostEvent extends Source
{
    const FIELD__FILES  = '~files';

	/** @var */
	protected static $list;

	/** @var string */
    public static $type = Source::TYPE__EVENT;

    /** @var string */
    protected static $namePlaceholder = '#EVENT_NAME#';

    /**
     * @return array|mixed|null
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function loadData()
	{
        $data = EventType::getById($this->getId());
        if (!$data)
            throw new ArgumentOutOfRangeException('id');

		return $data;
	}

    /**
     * @return mixed|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getName()
	{
		$type = $this->getData();

		return strip_tags(html_entity_decode($type['NAME']));
	}

    /**
     * @param $amoObject
     * @return array
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function getInputs($amoObject)
	{
	    $result     = parent::getInputs($amoObject);
	    $fileCode   = $this->model->getFullFieldName($amoObject, self::FIELD__FILES);

		foreach ($result as &$arrInput){
		    if ($arrInput['name'] != $fileCode)
		        continue;

            $arrInput['help'] = Loc::getMessage('rover-acrm__' . PostEvent::FIELD__FILES . '-help');

            break;
        }

		return $result;
	}

    /**
     * @return array|null
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getTypes()
    {
        return Main::getEventTypes(SITE_ID, array(
            'order'         => array('EVENT_NAME' => 'asc'),
            'add_filter'    => array('!EVENT_NAME' => EventType::TYPE__AMOCRM_UNAVAILABLE),
            'template'      => array('{ID}' => '[{EVENT_NAME}] {NAME}'))
        );
    }

    /**
     * @return mixed|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getEditUrl()
    {
        $type = $this->getData();

        return '/bitrix/admin/type_edit.php?EVENT_NAME=' . $type['EVENT_NAME'] . "&lang=" . LANGUAGE_ID;
    }
}