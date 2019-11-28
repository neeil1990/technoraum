<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 18.02.2016
 * Time: 18:39
 *
 * @author Pavel Shulaev (https://rover-it.me)
 */

namespace Rover\AmoCRM\Entity\Source;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\SystemException;
use Rover\AmoCRM\Entity\Source;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Rover\Params\Form;

if (!Loader::includeModule('rover.params'))
	throw new SystemException('rover.params module not found');

Loc::LoadMessages(__FILE__);

/**
 * Class WebForm
 *
 * @package Rover\AmoCRM\Params
 * @author  Pavel Shulaev (https://rover-it.me)
 */
class WebForm extends Source
{
	/** @var string */
	public static $module = 'form';

	/** @var string */
	public static $type = Source::TYPE__FORM;

    /** @var string */
	protected static $namePlaceholder = '#FORM_NAME#';

    /**
     * @return array|mixed
     * @throws ArgumentOutOfRangeException
     * @author Pavel Shulaev (https://rover-it.me)
     */
	public function loadData()
	{
        $form   = new \CForm;
        $data   = $form->GetByID($this->id)->Fetch();
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
		$form = $this->getData();

		return strip_tags(html_entity_decode($form['NAME']));
	}

    /**
     * @return array|null
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public static function getTypes()
    {
        return Form::getWebForms();
    }


    /**
     * @return mixed|string
     * @author Pavel Shulaev (https://rover-it.me)
     */
    public function getEditUrl()
    {
        return '/bitrix/admin/form_edit.php?ID=' . $this->getId() . "&lang=" . LANGUAGE_ID;;
    }
}