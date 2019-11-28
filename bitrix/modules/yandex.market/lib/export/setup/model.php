<?php

namespace Yandex\Market\Export\Setup;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Yandex\Market;

Loc::loadMessages(__FILE__);

class Model extends Market\Reference\Storage\Model
{
	/** @var \Yandex\Market\Export\Xml\Format\Reference\Base */
	protected $format;
	protected $domainParsed;

	public static function getDataClass()
	{
		return Table::getClassName();
	}

	public static function normalizeFileName($fileName, $primary = null)
	{
		$fileName = basename(trim($fileName), '.xml');

		if ($fileName === '' && !empty($primary))
		{
			$fileName = 'setup_' . $primary;
		}

		return ($fileName !== '' ? $fileName . '.xml' : null);
	}

	public function onBeforeRemove()
    {
        if ($this->isAutoUpdate())
        {
            $this->handleChanges(false);
        }

        if ($this->hasFullRefresh())
        {
            $this->handleRefresh(false);
        }
    }

	public function onAfterSave()
	{
	    $isAutoUpdate = $this->isAutoUpdate();
	    $hasFullRefresh = $this->hasFullRefresh();

        $this->handleChanges($isAutoUpdate);
        $this->handleRefresh($hasFullRefresh);
        $this->updatePromoListener();
	}

	public function handleChanges($direction)
	{
		if (!$direction || $this->isFileReady())
		{
		    $entityType = Market\Export\Track\Table::ENTITY_TYPE_SETUP;
		    $entityId = $this->getId();

            if ($direction)
            {
                $trackSourceList = $this->getTrackSourceList();

                Market\Export\Track\Registry::addEntitySources($entityType, $entityId, $trackSourceList);
            }
            else
            {
                Market\Export\Track\Registry::removeEntitySources($entityType, $entityId);
            }
		}
	}

	public function updatePromoListener()
    {
        /** @var Market\Export\Promo\Model $promo */
        foreach ($this->getPromoCollection() as $promo)
        {
            $promo->updateListener();
        }
    }

	public function getTrackSourceList()
    {
        $result = [];

        foreach ($this->getIblockLinkCollection() as $iblockLink)
        {
            $result = array_merge($result, $iblockLink->getTrackSourceList());
        }

        return $result;
    }

	public function handleRefresh($direction)
	{
		$interval = $this->getRefreshPeriod();

		$agentParams = [
			'method' => 'refreshStart',
			'arguments' => [ (int)$this->getId() ],
			'interval' => $interval,
			'next_exec' => ConvertTimeStamp(time() + $interval, 'FULL')
		];

		if ($direction)
		{
			if ($this->isFileReady())
			{
				Market\Export\Run\Agent::register($agentParams);
			}
		}
		else
		{
			Market\Export\Run\Agent::unregister($agentParams);
			Market\Export\Run\Agent::unregister([
				'method' => 'refresh',
				'arguments' => [ (int)$this->getId() ]
			]);

			Market\Export\Run\Agent::releaseState('refresh', (int)$this->getId());
		}
	}

	/**
	 * @return array
	 */
	public function getContext()
	{
		$format = $this->getFormat();
		$result = [
			'SETUP_ID' => $this->getId(),
			'EXPORT_SERVICE' => $this->getField('EXPORT_SERVICE'),
			'EXPORT_FORMAT' => $this->getField('EXPORT_FORMAT'),
			'EXPORT_FORMAT_TYPE' => $format->getType(),
			'ENABLE_AUTO_DISCOUNTS' => $this->isAutoDiscountsEnabled(),
			'HTTPS' => $this->isHttps(),
			'DOMAIN_URL' => $this->getDomainUrl(),
			'ORIGINAL_URL' => $this->getDomainUrl($this->getField('DOMAIN')),
			'USER_GROUPS' => [2], // support only public
			'HAS_CATALOG' => Main\ModuleManager::isModuleInstalled('catalog'),
			'HAS_SALE' => Main\ModuleManager::isModuleInstalled('sale'),
			'SHOP_DATA' => $this->getShopData()
		];

		// sales notes

		$salesNotes = $this->getSalesNotes();

		if (strlen($salesNotes) > 0)
		{
			$result['SALES_NOTES'] = $salesNotes;
		}

		// delivery options

		$deliveryOptions = $this->getDeliveryOptions();

		if (!empty($deliveryOptions))
		{
			$result['DELIVERY_OPTIONS'] = $deliveryOptions;
		}

		return $result;
	}

	public function getDeliveryOptions()
	{
		$deliveryCollection = $this->getDeliveryCollection();

		return $deliveryCollection->getDeliveryOptions();
	}

	public function getSalesNotes()
	{
		return trim($this->getField('SALES_NOTES'));
	}

	public function getShopData()
	{
		$fieldValue = $this->getField('SHOP_DATA');

		return is_array($fieldValue) ? $fieldValue : null;
	}

	public function getFormat()
	{
		if (!isset($this->format))
		{
			$this->format = $this->loadFormat();
		}

		return $this->format;
	}

	protected function loadFormat()
	{
		$service = $this->getField('EXPORT_SERVICE');
		$format = $this->getField('EXPORT_FORMAT');

		return Market\Export\Xml\Format\Manager::getEntity($service, $format);
	}

	public function getFileName()
	{
		return static::normalizeFileName($this->getField('FILE_NAME'), $this->getId());
	}

	public function getFileRelativePath()
	{
		return BX_ROOT . '/catalog_export/' . $this->getFileName();
	}

	public function getFileAbsolutePath()
	{
		$relativePath = $this->getFileRelativePath();

		return Main\IO\Path::convertRelativeToAbsolute($relativePath);
	}

	public function isFileReady()
	{
		$path = $this->getFileAbsolutePath();

		return Main\IO\File::isFileExists($path);
	}

	public function getFileUrl()
	{
		return $this->getDomainUrl() . $this->getFileRelativePath();
	}

	public function getDomainUrl($domain = null)
	{
		if ($domain === null)
		{
			$domain = $this->getDomain();
		}

		return 'http' . ($this->isHttps() ? 's' : '') . '://' . $domain;
	}

	public function getDomain()
	{
		$parsedDomain = $this->getDomainParsed();

		if ($parsedDomain !== false)
		{
			$result = $parsedDomain['DOMAIN'];
		}
		else
		{
			$result = $this->getField('DOMAIN');
		}

		return $result;
	}

	public function getDomainPath()
	{
		$parsedDomain = $this->getDomainParsed();

		if ($parsedDomain !== false)
		{
			$result = $parsedDomain['PATH'];
		}
		else
		{
			$result = '';
		}

		return $result;
	}

	protected function getDomainParsed()
	{
		if ($this->domainParsed === null)
		{
			$domain = $this->getField('DOMAIN');

			$this->domainParsed = $this->parseDomain($domain);
		}

		return $this->domainParsed;
	}

	protected function parseDomain($domain)
	{
		$result = false;

		if (preg_match('#^([^/]+)([^?\#]*)(.*)?$#', $domain, $matches))
		{
			$result = [
				'DOMAIN' => $matches[1],
				'PATH' => $matches[2],
				'QUERY' => $matches[3]
			];
		}

		return $result;
	}

	public function isHttps()
	{
		return ($this->getField('HTTPS') === '1');
	}

	public function isAutoDiscountsEnabled()
	{
		return ($this->getField('ENABLE_AUTO_DISCOUNTS') === '1');
	}

	public function isAutoUpdate()
	{
		return ($this->getField('AUTOUPDATE') === '1');
	}

	public function hasFullRefresh()
	{
		return $this->getRefreshPeriod() !== null;
	}

	public function getRefreshPeriod()
	{
		$period = (int)$this->getField('REFRESH_PERIOD');
		$result = null;

		if ($period > 0)
		{
			$result = $period;
		}

		return $result;
	}

	/**
	 * @return \Yandex\Market\Export\IblockLink\Collection
	 */
	public function getIblockLinkCollection()
	{
		return $this->getChildCollection('IBLOCK_LINK');
	}

	/**
	 * @return \Yandex\Market\Export\Delivery\Collection
	 */
	public function getDeliveryCollection()
	{
		return $this->getChildCollection('DELIVERY');
	}

    /**
     * @return \Yandex\Market\Export\Promo\Collection
     */
    public function getPromoCollection()
    {
        return $this->getChildCollection('PROMO');
    }

	protected function getChildCollectionReference($fieldKey)
	{
		$result = null;

		switch ($fieldKey)
		{
			case 'IBLOCK_LINK':
				$result = Market\Export\IblockLink\Collection::getClassName();
			break;

			case 'DELIVERY':
				$result = Market\Export\Delivery\Collection::getClassName();
			break;

            case 'PROMO':
                $result = Market\Export\Promo\Collection::getClassName();
            break;
		}

		return $result;
	}

    protected function getChildCollectionQueryParameters($fieldKey)
    {
        $result = [];

        switch ($fieldKey)
        {
            case 'PROMO':
				$result['distinct'] = true;
                $result['filter'] = [
                    'LOGIC' => 'OR',
                    [ 'SETUP_LINK.SETUP_ID' => $this->getId() ],
                    [ 'SETUP_EXPORT_ALL' => Market\Export\Promo\Table::BOOLEAN_Y ]
                ];
            break;

            default:
                $result = parent::getChildCollectionQueryParameters($fieldKey);
            break;
        }

        return $result;
    }
}
