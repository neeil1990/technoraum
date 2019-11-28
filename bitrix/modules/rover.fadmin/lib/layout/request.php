<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 17.09.2017
 * Time: 15:05
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\Fadmin\Layout;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Rover\Fadmin\Inputs\Addpreset;
use Rover\Fadmin\Inputs\Removepreset;
use Rover\Fadmin\Options;
use Rover\Fadmin\Options\Event;

/**
 * Class Request
 *
 * @package Rover\Fadmin\Layout
 * @author  Pavel Shulaev (https://rover-it.me)
 */
abstract class Request
{
    /** @var Options */
    protected $options;

    /** @var array */
    protected $params;

    /** @var \Bitrix\Main\HttpRequest */
    protected $request;

    /**
     * Request constructor.
     *
     * @param Options $options
     * @param array   $params
     * @throws \Bitrix\Main\SystemException
     */
    public function __construct(Options $options, array $params = array())
    {
        $this->options  = $options;
        $this->params   = $params;
        $this->request  = Application::getInstance()->getContext()->getRequest();
    }

    /**
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function process()
    {
        // action before
        if (!$this->options->event->handle(Event::BEFORE_GET_REQUEST)->isSuccess())
            return;

        if ($this->request->get(Addpreset::getType())) {
            $this->addPreset();
        } elseif ($this->request->get(Removepreset::getType())) {
            $this->removePreset();
        } else {
            $this->setValues();
        }
    }

    /**
     * @return mixed
     * @author Pavel Shulaev (https://rover-it.me)
     */
    abstract function setValues();

    /**
     * @return int
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function addPreset()
    {
        list($siteId, $value) = explode(Addpreset::SEPARATOR,
            $this->request->get(Addpreset::getType()));

        return intval($this->options->preset->add($value, $siteId));
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function removePreset()
    {
        list($siteId, $id) = explode(Removepreset::SEPARATOR,
            $this->request->get(Removepreset::getType()));

        return $this->options->preset->remove($id, $siteId);
    }

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     * @author Pavel Shulaev (http://rover-it.me)
     */
    protected function restoreDefaults()
    {
        Option::delete($this->options->getModuleId());
    }

    /**
     * @param $url
     * @throws \Bitrix\Main\SystemException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    protected function redirect($url)
    {
        if (!$this->options->event
            ->handle(Event::BEFORE_REDIRECT_AFTER_REQUEST, compact('url'))
            ->isSuccess())
            return;

        $url = $this->options->event->getParameter('url');

        if (empty($url))
            return;

        LocalRedirect($url);
    }
}