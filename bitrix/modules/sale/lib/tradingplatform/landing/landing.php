<?php

namespace Bitrix\Sale\TradingPlatform\Landing;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\DB;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * Class Landing
 * @package Bitrix\Sale\TradingPlatform\Landing
 */
class Landing extends Sale\TradingPlatform\Platform
{
	const TRADING_PLATFORM_CODE = 'landing';
	const CODE_DELIMITER = '_';

	protected $site = [];

	/**
	 * @return bool|int
	 * @throws \Exception
	 */
	public function install()
	{
		$data = $this->getInfo();

		$result = Sale\TradingPlatformTable::add([
			"CODE" => $this->getCode(),
			"ACTIVE" => "Y",
			"NAME" => Loc::getMessage('SALE_LANDING_NAME', ['#NAME#' => $data['TITLE']]),
			"DESCRIPTION" => '',
			"CLASS" => '\\'.static::class,
		]);

		if ($result->isSuccess())
		{
			$this->isInstalled = true;
			$this->id = $result->getId();
		}

		return $result->isSuccess();
	}

	/**
	 * @return int
	 */
	protected function getSiteId()
	{
		return (int)substr($this->getCode(), strrpos($this->getCode(), '_') + 1);
	}

	/**
	 * @return void
	 */
	public static function setShipmentTableOnAfterUpdateEvent()
	{
		return;
	}

	/**
	 * @return void
	 */
	protected static function unSetShipmentTableOnAfterUpdateEvent()
	{
		return;
	}

	/**
	 * @return void
	 */
	protected function setCatalogSectionsTabEvent()
	{
		return;
	}

	/**
	 * @return void
	 */
	protected function unSetCatalogSectionsTabEvent()
	{
		return;
	}

	/**
	 * @param Event $event
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @return void
	 */
	public static function onLandingSiteAdd(Event $event)
	{
		$fields = $event->getParameter('fields');
		if ($fields['TYPE'] !== 'STORE')
		{
			return;
		}

		$primary = $event->getParameter('primary');
		$landing = Landing::getInstanceByCode(static::getCodeBySiteId($primary['ID']));
		if (!$landing->isInstalled())
		{
			$landing->install();
		}
	}

	/**
	 * @param $id
	 * @return string
	 */
	public static function getCodeBySiteId($id)
	{
		return static::TRADING_PLATFORM_CODE.static::CODE_DELIMITER.$id;
	}

	/**
	 * @return array|false
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getInfo()
	{
		if (!Loader::includeModule('landing'))
		{
			return [];
		}

		if ($this->site)
		{
			return $this->site;
		}

		/** @var DB\Result $dbRes */
		$dbRes = \Bitrix\Landing\Site::getList([
			'filter' => [
				'=ID' => $this->getSiteId()
			]
		]);

		if ($data = $dbRes->fetch())
		{
			$this->site = $data;
			$this->site['PUBLIC_URL'] = \Bitrix\Landing\Site::getPublicUrl($this->getCode());
		}

		return $this->site;
	}

	/**
	 * @param $type
	 * @param Sale\Order $order
	 * @return string
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getExternalLink($type, Sale\Order $order)
	{
		if ($type === static::LINK_TYPE_PUBLIC_DETAIL_ORDER)
		{
			if (Loader::includeModule('landing'))
			{
				$sysPages = \Bitrix\Landing\Syspage::get($this->getSiteId());
				if (isset($sysPages['personal']))
				{
					$landing = \Bitrix\Landing\Landing::createInstance(
						$sysPages['personal']['LANDING_ID']
					);
					if ($landing->exist())
					{
						$url = $landing->getPublicUrl(
							$sysPages['personal']['LANDING_ID'],
							[
								'blocks_limit' => 1
							]
						);
						$url .= '?SECTION=orders&ID=' . $order->getId();

						return \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl().$url;
					}
				}
			}

			return '';
		}

		throw new ArgumentException("Unsupported link type: {$type}");
	}

	/**
	 * @return mixed
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getRealName()
	{
		return $this->getInfo()['TITLE'];
	}

}
