<?php
namespace Bitrix\Landing\Connector;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Binding;

Loc::loadMessages(__FILE__);

class Intranet
{
	/**
	 * Site type, binding for menu.
	 */
	const SITE_TYPE = 'KNOWLEDGE';

	/**
	 * Returns one service menu item for binding entity.
	 * @param string $bindCode Binding code.
	 * @return array
	 */
	protected static function getMenuForBind($bindCode)
	{
		return [
			'text' => Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_TITLE'),
			'href' => '#',
			'onclick' => 'BX.SidePanel.Instance.open(\'/kb/wiki/edit/0/?binding=' . $bindCode . '\', {allowChangeHistory: false});'
		];
	}

	/**
	 * Returns menu items for different binding places in Intranet.
	 * @param \Bitrix\Main\Event $event
	 * @return array
	 */
	public static function onBuildBindingMenu(\Bitrix\Main\Event $event)
	{
		\CJSCore::init('sidepanel');
	//	\Bitrix\Landing\Site\Type::setScope(self::SITE_TYPE);

		$bindings = Binding\Menu::getList(null);
		$bindings = [];
		if (!$bindings)
		{
			\Bitrix\Landing\Site\Type::clearScope();
			return [];
		}

		$bindingsAssoc = [];
		foreach ($bindings as $binding)
		{
			if (!isset($bindingsAssoc[$binding['BINDING_ID']]))
			{
				$bindingsAssoc[$binding['BINDING_ID']] = [];
			}
			$bindingsAssoc[$binding['BINDING_ID']][] = $binding;
		}
		$bindings = $bindingsAssoc;
		unset($bindingsAssoc);

		// start vars
		$items = [];
		$bindingMap = \Bitrix\Intranet\Binding\Menu::getMap();
		$menuUnbind = [
			'text' => Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_UNBIND_TITLE'),
			'href' => '#unbind'
		];

		//Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_TITLE')

		// build binding map
		foreach ($bindingMap as $sectionCode => $bindingSection)
		{
			foreach ($bindingSection['items'] as $itemCode => $foo)
			{
				$menuItems = [];
				$additionalItems = [
					[
						'text' => 1111,
						'href' => '#123'
					]
				];
				$bindingCode = $sectionCode . ':' . $itemCode;
				if (isset($bindings[$bindingCode]))
				{
					foreach ($bindings[$bindingCode] as $bindingItem)
					{
						$menuItems[] = [
						  'text' => $bindingItem['TITLE'],
						  'href' => $bindingItem['PUBLIC_URL']
						];
					}
				}
				else
				{
					$menuItems[] = self::getMenuForBind($bindingCode);
				}
				$items[] = [
					'bindings' => [
						$sectionCode => [
							'include' => [
								$itemCode
							]
						]
					],
					'items' => $menuItems,
					'additionalItems' => $additionalItems
				];
			}
		}

		\Bitrix\Landing\Site\Type::clearScope();

		return $items;
	}
}

/*[
						[
							'text' => Loc::getMessage('LANDING_CONNECTOR_INTRANET_MENU_TITLE'),
							'items' => isset($bindings[$bindingCode])
									? array_merge(
										[
											[
												'text' => $bindings[$bindingCode]['TITLE'],
												'href' => $bindings[$bindingCode]['PUBLIC_URL']
											]
										],
										[
											[
												'delimiter' => true
											]
										],
										[
											$menuUnbind
										]

									)
									: self::getMenuForBind($bindingCode)
						]*/