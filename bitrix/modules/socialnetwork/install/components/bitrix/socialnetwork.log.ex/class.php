<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;

Loader::includeModule('socialnetwork');

final class SocialnetworkLogList extends \Bitrix\Socialnetwork\Component\LogList
{
	/**
	 * Prepares filter for the gratUserId gratitudes list
	 *
	 * @param \Bitrix\Main\Event $event Event.
	 * @return void
	 */
	private function prepareGratPostFilter()
	{
		global $APPLICATION;

		$this->arResult['GRAT_POST_FILTER'] = [];
		$this->arResult['RETURN_EMPTY_LIST'] = false;

		$userId = (
			!empty($_GET['gratUserId'])
			&& intval($_GET['gratUserId']) > 0
				? intval($_GET['gratUserId'])
				: false
		);

		$gratCode = (
			isset($_GET['gratCode'])
			&& strlen($_GET['gratCode']) > 0
				? $_GET['gratCode']
				: false
		);

		if (
			$userId
			&& \Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
		)
		{
			$res = \CUser::getByID($userId);
			$gratUserName = '';
			if ($userFields = $res->fetch())
			{
				$gratUserName = \CUser::formatName(\CSite::getNameFormat(false), $userFields, true);
			}

			$this->arResult['RETURN_EMPTY_LIST'] = true;
			$filterParams = [
				'userId' => $userId
			];

			if ($gratCode)
			{
				$filterParams['gratCode'] = $gratCode;
			}

			$gratitudesData = \Bitrix\Socialnetwork\Component\LogList::getGratitudesIblockData($filterParams);
			$iblockElementsIdList = $gratitudesData['ELEMENT_ID_LIST'];
			$gratValue = '';
			if (strlen($gratitudesData['GRAT_VALUE']) > 0)
			{
				$gratValue = $gratitudesData['GRAT_VALUE'];
			}

			$postIdList = [];
			if (!empty($iblockElementsIdList))
			{
				$gratitudesData = \Bitrix\Socialnetwork\Component\LogList::getGratitudesBlogData([
					'iblockElementsIdList' => $iblockElementsIdList
				]);
				$postIdList = $gratitudesData['POST_ID_LIST'];
			}

			if (!empty($postIdList))
			{
				$this->arResult['GRAT_POST_FILTER'] = $postIdList;
				$this->arResult['RETURN_EMPTY_LIST'] = false;
			}

			if (strlen($gratUserName) > 0)
			{
				$APPLICATION->setTitle(Bitrix\Main\Localization\Loc::getMessage(strlen($gratValue) > 0 ? 'SONET_LOG_LIST_TITLE_GRAT2' : 'SONET_LOG_LIST_TITLE_GRAT', [
					'#USER_NAME#' => $gratUserName,
					'#GRAT_NAME#' => $gratValue
				]));
			}
		}
	}

	public function executeComponent()
	{
		$this->prepareGratPostFilter();

		return $this->__includeComponent();
	}
}