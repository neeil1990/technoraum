<?
namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;

class Livefeed extends \Bitrix\Main\Engine\Controller
{
	public function changeFavoritesAction($logId, $value)
	{
		global $APPLICATION;

		$result = [
			'success' => false,
			'newValue' => false
		];

		$logId = intval($logId);
		if ($logId <= 0)
		{
			$this->addError(new Error('No Log Id', 'SONET_CONTROLLER_LIVEFEED_NO_LOG_ID'));
			return null;
		}

		if (!(
			Loader::includeModule('socialnetwork')
			&& ($logFields = \CSocNetLog::getById($logId))
		))
		{
			$this->addError(new Error('Cannot get log entry', 'SONET_CONTROLLER_LIVEFEED_EMPTY_LOG_ENTRY'));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();

		if ($res = \CSocNetLogFavorites::change($currentUserId, $logId))
		{
			if ($res == "Y")
			{
				\Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe(array(
					'logId' => $logId,
					'userId' => $currentUserId,
					'typeList' => array(
						'FOLLOW',
						'COUNTER_COMMENT_PUSH'
					),
					'followDate' => $logFields["LOG_UPDATE"]
				));
			}
			$result['success'] = true;
			$result['newValue'] = $res;
		}
		else
		{
			$this->addError(new Error((($e = $APPLICATION->getException()) ? $e->getString() : 'Cannot change log entry favorite value'), 'SONET_CONTROLLER_LIVEFEED_FAVORITES_CHANGE_ERROR'));
			return null;
		}

		return $result;
	}

	public function changeFollowAction($logId, $value)
	{
		$result = [
			'success' => false
		];

		$logId = intval($logId);
		if ($logId <= 0)
		{
			return $result;
		}

		$logId = intval($logId);
		if ($logId <= 0)
		{
			$this->addError(new Error('No Log Id', 'SONET_CONTROLLER_LIVEFEED_NO_LOG_ID'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		$result['success'] = (
			$value == "Y"
				? \Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe([
					'logId' => $logId,
					'userId' => $currentUserId,
					'typeList' => [ 'FOLLOW', 'COUNTER_COMMENT_PUSH' ]
				])
				: \CSocNetLogFollow::set($currentUserId, "L".$logId, "N")
		);

		return $result;
	}

	public function changeFollowDefaultAction($value)
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		return [
			'success' => \CSocNetLogFollow::set($this->getCurrentUser()->getId(), "**", ($value == "Y" ? "Y" : "N"))
		];
	}

	public function changeExpertModeAction($value)
	{
		$result = [
			'success' => false
		];

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_LIVEFEED_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		\Bitrix\Socialnetwork\LogViewTable::set($this->getCurrentUser()->getId(), 'tasks', ($value == "Y" ? "N" : "Y"));
		$result['success'] = true;

		return $result;
	}

	public function mobileLogErrorAction($message, $url, $lineNumber)
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled("bitrix24"))
		{
			AddMessage2Log("Mobile Livefeed javascript error:\nMessage: ".$message."\nURL: ".$url."\nLine number: ".$lineNumber."\nUser ID: ".$this->getCurrentUser()->getId());
		}

		return [
			'success' => true
		];
	}

	public function mobileGetDetailAction($logId)
	{
		$logId = intval($logId);
		if ($logId <= 0)
		{
			$this->addError(new Error('No Log Id', 'SONET_CONTROLLER_LIVEFEED_NO_LOG_ID'));
			return null;
		}

		return new \Bitrix\Main\Engine\Response\Component('bitrix:mobile.socialnetwork.log.ex', '', [
			'LOG_ID' => $logId,
			'SITE_TEMPLATE_ID' => 'mobile_app',
			'TARGET' => 'postContent',
		]);
	}
}

