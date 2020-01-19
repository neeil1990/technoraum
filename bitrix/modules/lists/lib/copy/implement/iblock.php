<?php
namespace Bitrix\Lists\Copy\Implement;

use Bitrix\Iblock\Copy\Implement\Iblock as IblockImplementer;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Copy\Container;

class Iblock extends IblockImplementer
{
	public function copyChildren(Container $container, $iblockId, $copiedIblockId)
	{
		$this->copyLockFeature($iblockId, $copiedIblockId);

		return parent::copyChildren($container, $iblockId, $copiedIblockId);
	}

	protected function cleanCache(int $iblockId): void
	{
		parent::cleanCache($iblockId);

		if ($this->cacheManager)
		{
			$this->cacheManager->clearByTag("lists_list_".$iblockId);
			$this->cacheManager->clearByTag("lists_list_any");
		}
	}

	protected function getSocnetPermission($iblockId, $socnetGroupId): array
	{
		$rights = [];
		$i = 0;
		$socnetPerm = \CLists::getSocnetPermission($iblockId);
		foreach ($socnetPerm as $role => $permission)
		{
			if ($permission > "W")
			{
				$permission = "W";
			}
			switch ($role)
			{
				case "A":
				case "E":
				case "K":
					$rights["n" . ($i++)] = [
						"GROUP_CODE" => "SG" . $socnetGroupId . "_" . $role,
						"IS_INHERITED" => "N",
						"TASK_ID" => \CIBlockRights::letterToTask($permission),
					];
					break;
				case "L":
					$rights["n" . ($i++)] = [
						"GROUP_CODE" => "AU",
						"IS_INHERITED" => "N",
						"TASK_ID" => \CIBlockRights::letterToTask($permission),
					];
					break;
				case "N":
					$rights["n" . ($i++)] = [
						"GROUP_CODE" => "G2",
						"IS_INHERITED" => "N",
						"TASK_ID" => \CIBlockRights::letterToTask($permission),
					];
					break;
			}
		}
		return $rights;
	}

	private function copyLockFeature(int $iblockId, int $copiedIblockId): void
	{
		$option = Option::get("lists", "iblock_lock_feature");
		$iblockIdsWithLockFeature = ($option !== "" ? unserialize($option) : []);
		if (isset($iblockIdsWithLockFeature[$iblockId]))
		{
			$iblockIdsWithLockFeature[$copiedIblockId] = $copiedIblockId;
			Option::set("lists", "iblock_lock_feature", serialize($iblockIdsWithLockFeature));
		}
	}
}