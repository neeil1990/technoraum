<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\ModuleManager,
	\Bitrix\Main\Config\Option;

/**
 * Class SaleBsmSiteMasterButton
 */
class SaleBsmSiteMasterButton extends \CBitrixComponent
{
	private const IS_SALE_CRM_SITE_MASTER_FINISH = "~IS_SALE_CRM_SITE_MASTER_FINISH";

	/**
	 * @return mixed|void
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function executeComponent()
	{
		if ($this->isShowButton())
		{
			$this->prepareResult();
			$this->includeComponentTemplate();
		}
	}

	private function prepareResult(): void
	{
		$this->arResult["MASTER_PATH"] = $this->getMasterPath();
	}

	/**
	 * @return bool|string
	 */
	private function getMasterPath()
	{
		$bsmSiteMasterPath = \CComponentEngine::makeComponentPath('bitrix:sale.bsm.site.master');
		$bsmSiteMasterPath = getLocalPath('components'.$bsmSiteMasterPath.'/slider.php');

		return $bsmSiteMasterPath;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private function isShowButton(): bool
	{
		return ($this->isSaleCrmSiteMasterFinish()
			||
			(
				ModuleManager::isModuleInstalled('extranet')
				&& !ModuleManager::isModuleInstalled('bitrix24')
				&& LANGUAGE_ID === "ru"
			)
		);
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private function isSaleCrmSiteMasterFinish(): bool
	{
		return (Option::get("sale", self::IS_SALE_CRM_SITE_MASTER_FINISH, "N") === "Y");
	}
}