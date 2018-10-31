<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\SystemException;

final class SettingsContainer
{
	const DISALLOW_NEW_USER_CREATION = 0;
	const ALLOW_NEW_USER_CREATION = 1;
	const SET_ANONYMOUS_USER = 2;

	private $settings = [
		//Allow creation new user if it doesn't exist yet.
		'createUserIfNeed' => self::ALLOW_NEW_USER_CREATION,
		//Do we need update the price of just added products.
		//Now it is used only after the buyerId was changed.
		'needUpdateNewProductPrice' => false,
		//Refresh all products data.
		//Now it is used only during order recalculation
		'isRefreshData' => false,
		//For performance purposes
		'cacheProductProviderData' => true,
		//Other errors will be ignored.
		//We need this mostly during order creation
		//empty means - all acceptable
		'acceptableErrorCodes' => [],
		//We need this if some of order properties upload files.
		'propsFiles' => []
	];

	public function __construct (array $settings)
	{
		$diff = array_diff(array_keys($settings), $this->getAvailableItems());

		if(!empty($diff))
		{
			throw new ArgumentOutOfRangeException('Unknown settings: "'.implode('",', $diff).'"');
		}

		$this->settings = array_merge($this->settings, $settings);
	}

	public function getItemValue($name)
	{
		if(!isset($this->settings[$name]))
		{
			throw new SystemException('Unknown setting: "'.$name.'"');
		}

		return $this->settings[$name];
	}

	private function getAvailableItems()
	{
		return array_keys($this->settings);
	}
}