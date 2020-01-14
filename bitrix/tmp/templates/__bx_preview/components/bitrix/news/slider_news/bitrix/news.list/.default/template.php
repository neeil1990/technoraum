<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
	<div class="glav_news_block flexslider glav_news_block_slider clearfix">
		<ul class="slides">
		<?
		foreach($arResult["ITEMS"] as $arItem)
		{
			?>
				<li>
					<div class="glav_news_div">
						<input type="hidden" class="year" value="" />
						<input type="hidden" class="unit" value="<?=$arItem["PROPERTIES"]["UNIT"]["VALUE"]?>" />
						<div class="img">
							<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" alt="" /></a>
						</div>
						<div class="text">
							<?
								$unit = $arItem["PROPERTIES"]["UNIT"]["VALUE"];
								if($unit == "Новости")
								{
									?><p class="type"><?=$arItem["PROPERTIES"]["UNIT"]["VALUE"]?></p><?
								}
								else
								{
									?><p class="type articles"><?=$arItem["PROPERTIES"]["UNIT"]["VALUE"]?></p><?
								}
							?>
							<p class="date"><?=$arItem['DISPLAY_ACTIVE_FROM']?></p>
							<p class="title">
								<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
							</p>	
						</div>
					</div>
				</li>
			<?
			$i++;
		}
		?>
		</ul>
	</div>