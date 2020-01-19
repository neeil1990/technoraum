<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Config;
use Bitrix\Landing\Hook;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Assets;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

$this->setFrameMode(true);
$landing = $arResult['LANDING'];
/** @var \Bitrix\Landing\Landing $landing */

Manager::setPageTitle(
	Loc::getMessage('LANDING_TPL_TITLE')
);

if ($arResult['ERRORS'])
{
	\showError(implode("\n", $arResult['ERRORS']));
	return;
}

// edit menu
if (
	$arParams['SHOW_EDIT_PANEL'] == 'Y' &&
	$arResult['CAN_EDIT'] == 'Y'
)
{
	Extension::load([
		'ui.buttons',
		'ui.buttons.icons',
		'landing.wiki.public',
		'sidepanel',
	]);
	?>
	<div class="landing-pub-top-panel-wrapper">
		<div class="landing-pub-top-panel">
			<div class="landing-pub-top-panel-left">
				<div class="landing-pub-top-panel-actions">
					<a href="<?= $arParams['PAGE_URL_LANDING_VIEW'];?>" class="ui-btn ui-btn-primary ui-btn-icon-edit landing-pub-top-panel-edit-button">
						<?= $component->getMessageType('LANDING_TPL_EDIT_PAGE');?>
					</a>
				</div>
				<div class="landing-pub-top-panel-chain">
					<?$title = $component->getMessageType('LANDING_TPL_SITES');?>
					<a href="#<?//= $arParams['PAGE_URL_SITES'];?>" class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-pub-top-panel-chain-link" title="<?= $title;?>">
						<?= $title;?>
					</a>
					<strong class="landing-pub-top-panel-chain-separator"><span></span></strong>
					<?$title = \htmlspecialcharsbx($landing->getTitle());?>
					<a href="#<?//= $arParams['PAGE_URL_SITE_SHOW'];?>" class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-pub-top-panel-chain-link" title="<?= $title;?>">
						<?= $title;?>
					</a>
				</div>
			</div>
			<?/*<div class="landing-pub-top-panel-right">
				<span class="ui-btn ui-btn-light-border ui-btn-icon-setting landing-ui-panel-top-menu-link landing-ui-panel-top-menu-link-settings" title="<?= Loc::getMessage('LANDING_TPL_SETTINGS_BUTTON_TITLE');?>"></span>
			</div>*/?>
		</div>
		<script>
			BX.ready(function() {
				void new BX.Landing.Pub.TopPanel();
			});
		</script>
	</div>
	<?
}

// landing view
$landing->view([
	'check_permissions' => false
]);

$enableHook = Manager::checkFeature(
	Manager::FEATURE_ENABLE_ALL_HOOKS,
	['hook' => 'copyright']
);
if ($enableHook)
{
	$hooksSite = Hook::getForSite($arResult['LANDING']->getSiteId());
}

// set meta og:image
$metaOG = Manager::getPageView('MetaOG');
if (strpos($metaOG, '"og:image"') === false)
{
	Manager::setPageView('MetaOG',
		'<meta property="og:image" content="' . $arResult['LANDING']->getPreview() . '" />'
	);
}

// assets
$assets = Assets\Manager::getInstance();
$assets->addAsset(
		'landing_public',
		Assets\Location::LOCATION_AFTER_TEMPLATE
);
$assets->addAsset(
	Config::get('js_core_public'),
	Assets\Location::LOCATION_KERNEL
);
$assets->addAsset('landing_critical_grid', Assets\Location::LOCATION_BEFORE_ALL);
?>

<?ob_start(); ?>
<?if (!$enableHook || isset($hooksSite['COPYRIGHT']) && $hooksSite['COPYRIGHT']->enabled()):?>
<div class="bitrix-footer">
	<?if (Manager::isB24()):?>
		<span class="bitrix-footer-text">
			<?
			$zone = Manager::getZone();
			$fullCopy = in_array($zone, array('ru', 'by'))
						? Loc::getMessage('LANDING_TPL_COPY_FULL')
						: Loc::getMessage('LANDING_TPL_COPY_FULL2');
			$logo = '<img src="' .
						$this->getFolder() . '/images/' .
						(in_array($zone, array('ru', 'ua', 'en')) ? $zone : 'en') .
						'.svg?1" alt="' . Loc::getMessage('LANDING_TPL_COPY_NAME') . '">';
			if ($fullCopy)
			{
				echo str_replace(
					[
						'#LOGO#',
						'<linklogo>', '</linklogo>',
						'<linksite>', '</linksite>',
						'<linkcrm>', '</linkcrm>',
						'<linkcreate>', '</linkcreate>'
					],
					[
						$logo,
						'<a target="_blank" href="' . $this->getComponent()->getRefLink('bitrix24_logo') . '">', '</a>',
						'<a class="bitrix-footer-link" target="_blank" href="' . $this->getComponent()->getRefLink('websites') . '">', '</a>',
						'<a class="bitrix-footer-link" target="_blank" href="' . $this->getComponent()->getRefLink('crm') . '">', '</a>',
						'<a class="bitrix-footer-link" target="_blank" href="' . $this->getComponent()->getRefLink('create', false) . '">', '</a>'
					],
					$fullCopy
				);
			}
			else
			{
				echo Loc::getMessage('LANDING_TPL_COPY_NAME_0') . ' ';
				echo $logo;
				echo ' &mdash; ';
				echo Loc::getMessage('LANDING_TPL_COPY_REVIEW');
			}
			?>
		</span>
		<?if (!$fullCopy):?>
		<a class="bitrix-footer-link" target="_blank" href="<?= $this->getComponent()->getRefLink('create', false);?>">
			<?= Loc::getMessage('LANDING_TPL_COPY_LINK');?>
		</a>
		<?endif;?>
	<?else:?>
		<span class="bitrix-footer-text"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_SMN_0');?></span>
		<a href="https://www.1c-bitrix.ru/?<?= $arResult['ADV_CODE'];?>" target="_blank" class="bitrix-footer-link"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_SMN_1');?></a>
	<?endif;?>
</div>
<?endif;?>
<?
$footer = ob_get_contents();
ob_end_clean();
Manager::setPageView('BeforeBodyClose', $footer);
?>