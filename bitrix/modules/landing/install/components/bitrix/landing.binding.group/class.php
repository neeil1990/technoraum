<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Connector;
use \Bitrix\Landing\Binding;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingBindingGroupComponent extends LandingBaseComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		if (
			!\Bitrix\Main\Loader::includeModule('landing') ||
			!\Bitrix\Main\Loader::includeModule('socialnetwork')
		)
		{
			return;
		}

		$this->checkParam('GROUP_ID', 0);
		$this->checkParam('PATH_AFTER_CREATE', '');
		$this->arResult['ERROR'] = [];
		$this->arResult['TYPE'] = Connector\SocialNetwork::SITE_TYPE;

		if ($this->arParams['GROUP_ID'] <= 0)
		{
			$this->arResult['ERROR']['NOT_GROUP_ID'] = Loc::getMessage('LANDING_CMP_NOT_GROUP_ID');
		}
		else if (!Connector\SocialNetwork::userInGroup($this->arParams['GROUP_ID']))
		{
			$this->arResult['ERROR']['NOT_IN_GROUP'] = Loc::getMessage('LANDING_CMP_NOT_IN_GROUP');
		}
		else if (Binding\Group::getList($this->arParams['GROUP_ID']))
		{
			$this->arResult['ERROR']['ALREADY_EXIST'] = Loc::getMessage('LANDING_CMP_ALREADY_EXIST');
		}

		$this->includeComponentTemplate();
	}
}