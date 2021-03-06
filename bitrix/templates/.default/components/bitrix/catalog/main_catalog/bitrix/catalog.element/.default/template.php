<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	CModule::IncludeModule("sale");
	$arID = array();
	$arBasketItems = array();

	$dbBasketItems = CSaleBasket::GetList(
    array(
		"NAME" => "ASC",
		"ID" => "ASC"
		),
		array(
		"FUSER_ID" => CSaleBasket::GetBasketUserID(),
		"LID" => SITE_ID,
		"ORDER_ID" => "NULL"
		),
		false,
		false,
		array("ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "PRODUCT_PROVIDER_CLASS")
		);
while ($arItems = $dbBasketItems->Fetch())
{
	if ('' != $arItems['PRODUCT_PROVIDER_CLASS'] || '' != $arItems["CALLBACK_FUNC"])
	{
	CSaleBasket::UpdatePrice($arItems["ID"],
	$arItems["CALLBACK_FUNC"],
	$arItems["MODULE"],
	$arItems["PRODUCT_ID"],
	$arItems["QUANTITY"],
	"N",
	$arItems["PRODUCT_PROVIDER_CLASS"]
	);
	$arID[] = $arItems["ID"];
	}
}

	$dbBasketItems = CSaleBasket::GetList(
	array(
	"NAME" => "ASC",
	"ID" => "ASC"
	),
	array(
	"ID" => $arID,
        "ORDER_ID" => "NULL"
	),
        false,
        false,
        array("ID", "CALLBACK_FUNC", "MODULE",
	"PRODUCT_ID", "QUANTITY", "DELAY",
	"CAN_BUY", "PRICE", "WEIGHT", "PRODUCT_PROVIDER_CLASS", "NAME")
	);
while ($arItems = $dbBasketItems->Fetch())
{
    $arBasketItems[] = $arItems;
}
?>
<style>
	.card_page_specs span.in_store{background: url(/bitrix/templates/TechnoRaum/img/green_check.png) no-repeat 0 4px}
</style>
<div class="card_page_wrap clearfix">
	<input type="hidden" name="product_id" value="<?=$arResult["ID"]?>" />
	<input type="hidden" name="product_name" value="<?=$arResult["NAME"]?>" />
	<input type="hidden" name="product_price" value="<?=$arResult["PRICES"]["price"]["VALUE"]?>" />
	<div class="card_page_img">
		<?
		if($arResult["PROPERTIES"]["ARTICLE"]["VALUE"])
		{
			?><p class="card_article">�������: <span><?=$arResult["PROPERTIES"]["ARTICLE"]["VALUE"]?></span></p><?
		}
		?>
		<div class="big_img">
			<?
				if($arResult["DETAIL_PICTURE"]["SRC"])
					$img = $arResult["DETAIL_PICTURE"]["SRC"];
				else
					$img = $arResult["PREVIEW_PICTURE"]["SRC"];
			?>
			<a href="<?=$img?>" class="fancy" rel="card_gal">
				<em></em><img src="<?=$img?>" alt="" />
			</a>
			<?
				$stick = $arResult["PROPERTIES"]["STICKER"]["VALUE_XML_ID"];
				switch($stick)
				{
					case "val1":
						$block = '<span class="hit">'.$arResult["PROPERTIES"]["STICKER"]["VALUE"].'</span>';
						break;
					case "val2":
						$block = '<span class="new">'.$arResult["PROPERTIES"]["STICKER"]["VALUE"].'</span>';
						break;
					case "val3":
						$block = '<span class="action">'.$arResult["PROPERTIES"]["STICKER"]["VALUE"].'</span>';
						break;
					default:
						unset($block);
						break;
				}
			?>
			<div class="flag">
				<?=$block?>
				<? if($arResult["PROPERTIES"]["STICKER_WARRANTY"]["VALUE"]):?>
				<div class="line">
					<img src="<?=CFile::ResizeImageGet($arResult["PROPERTIES"]["STICKER_WARRANTY"]["VALUE"], array('width' => 80, 'height' => 80), BX_RESIZE_IMAGE_PROPORTIONAL, true)['src']?>" alt="<?=$arResult['NAME']?>">
				</div>
				<? endif; ?>
			</div>
		</div>
		<?
			if($arResult["PROPERTIES"]["PHOTO"]["VALUE"])
			{
				?>
				<div class="card_img_mini_wrap">
					<div class="mini_img mini_img_slider mini_img_scroll modern-skin">
						<ul class="slides clearfix">
						<?
							foreach($arResult["PROPERTIES"]["PHOTO"]["VALUE"] as $img)
							{
								$big_img = CFile::GetPath($img);
								$sm_img = CFile::ResizeImageGet($img, array('width'=>150, 'height'=>150), BX_RESIZE_IMAGE_PROPORTIONAL, true);
								?>
								<li>
									<a href="<?=$big_img?>" class="fancy" rel="card_gal">
										<em></em><img src="<?=$sm_img["src"]?>" alt="" />
									</a>
								</li>
								<?
							}
						?>
						</ul>
					</div>
				</div>
				<?
			}
		?>
	</div>
	<div class="card_page_specs">
        
		<div class="the_price">
			<? if($arResult["PROPERTIES"]["OLD_PRICE"]["VALUE"]):?>
				<p class="old_price"><?=$arResult["PROPERTIES"]["OLD_PRICE_VAL"]["VALUE"];?> &#8381;</p>
			<? endif; ?>

			<p class="price"
			   sale-procent="<?=$arResult["PRICES"]["price"]["DISCOUNT_DIFF_PERCENT"]?>"
			   profit="<?=$arResult["PRICES"]["price"]["PRINT_DISCOUNT_DIFF"]?>"
			   old_price="<?=$arResult["PRICES"]["price"]["PRINT_VALUE"];?>"
				>
				
					<?if(checkPrice($arResult['IBLOCK_ID'], $arResult['ID'])):?>
						<a class="fancy button request-a-price" data-name="<?=$arResult['NAME']?>" href="#request-a-price" style="width:250px;">��������� ����</a>				
					<?else:?>
                        <span><?=$arResult["PRICES"]["price"]["PRINT_VALUE"]?></span>
					<?endif;?>
				
			</p>
		</div>

        <div class="button_wrap">
            <?
            $url = $_SERVER["REQUEST_URI"];
            $url = explode("/" , $url);
            for($i=0;$i<=count($url)-3;$i++)
                $new_url .= $url[$i]."/";
            $url = $new_url;
            $url .= "?action=BUY&id=".$arResult["ID"];
            ?>
            <input type="hidden" name="to-cart-action" value="<?=$url?>" />

            <a class="button add_to_cart_button" href="<?=$url?>">�������� � �������</a>
            <a class="fancy button one_click_button" href="#click_one_buy">������ � ���� ����</a>
        </div>

		<script type="text/javascript">
			arrProducts[0] = {
				id : '<?=$arResult['DIRECT_CREDIT']['id']?>',
				price: '<?=$arResult['DIRECT_CREDIT']['price']?>',
				count: '<?=$arResult['DIRECT_CREDIT']['count']?>',
				type: '<?=$arResult['DIRECT_CREDIT']['type']?>',
				name: '<?=$arResult['DIRECT_CREDIT']['name']?>',
				id_order: '<?=$arResult['DIRECT_CREDIT']['id_order']?>'
			};
		</script>

		<div class="i_creditbtn_first" id="getCredit">
			<p id="getPaymentDc"></p>
			<a class="i_creditgreen" href="javascript:void(0)">������ � ������</a>
		</div>

		<? if($arResult["PROPERTIES"]["GIFT"]["VALUE"]):?>
				<? if(count($arResult["PROPERTIES"]["GIFT"]["ITEM"]) > 0): ?>
                <div class="items-gifts">
                    <div class="item main">
                        <a href="#">
                            <div class="thumb-gift">
                                <img width="100%" src="<?=SITE_TEMPLATE_PATH?>/img/gift_icon.png">
                            </div>
                            <div class="desc-gift">
                                <span>������� �� ����� <?=number_format($arResult["GIFT_SUM"] , 0 , " " , " ");?> &#8381; :</span>
                            </div>
                        </a>
                    </div>

                    <? foreach($arResult["PROPERTIES"]["GIFT"]["ITEM"] as $product):?>
                    <div class="item">
                        <a href="<?=$product["URL"]?>">
                            <div class="thumb-gift">
                                <img src="<?=$product["PICTURE"]["src"]?>" alt="<?=$product["NAME"]?>">
                            </div>
                            <div class="desc-gift">
                                <span><?=$product["NAME"]?></span>
                                <span>
                                    <?=number_format($product["PRICE"] , 0 , " " , " ");?> &#8381;
                                    <?if($product['COUNT']):?>
                                    * <?=$product['COUNT']?> ��. = <?=number_format($product['PRICE_COUNT'] , 0 , " " , " ");?> &#8381;
                                    <?else:?>
                                      <?=$product['DESC']?>
                                    <?endif;?>
                                </span>
                            </div>
                        </a>
                    </div>
                    <? endforeach; ?>
                </div>
				<? endif; ?>
			<? endif; ?>
		<p class="title">��� �������� �����</p>
		<div class="card_dil_list">
			<div class="row">
				<div></div>
				<div>���������</div>
				<div></div>
				<div>����� ����� �������</div>
			</div>
			<div class="row">
				<div>
					<a style="text-decoration:none" class="card-scroll" href="#delivery_load"><span>��������� �� ��������</span></a>
				</div>
				<div>���������</div>
				<div>|</div>
				<div><a class="fancy" href="#callback2_popup">�������� � ���������</a></div>
			</div>
			<div class="row">
				<div>
					<a style="text-decoration:none" class="card-scroll" href="#delivery_load"><span>��������� �� ������ ������ ����</span></a>
				</div>
				<div>���������</div>
				<div>|</div>
				<div><a class="fancy" href="#callback2_popup">�������� � ���������</a></div>
			</div>

			<div class="row">
				<div><a style="text-decoration:none" class="card-scroll" href="#delivery_load"><span>�������� �� ����� ����</span></a></div>
				<div></div>
				<div>|</div>
				<div><a class="fancy" href="#callback2_popup">�������� � ���������</a></div>
			</div>

            <div class="row">
                <div><a style="text-decoration:none" class="card-scroll" href="#delivery_load"><span>�������� ������ ������������ ���������</span></a></div>
                <div></div>
                <div>|</div>
                <div><a class="fancy" href="#callback2_popup">�������� � ���������</a></div>
            </div>
		</div>

	</div>
</div>


<div class="card_page_description clearfix">

	<div class="card_page_properties">
		<p class="title">��������������</p>

		<? foreach($arResult["DISPLAY_PROPERTIES"] as $display_prop):?>
			<? switch ($display_prop["PROPERTY_TYPE"]):

				 case "S":
					 if($display_prop["VALUE"]):
						 foreach($display_prop["VALUE"] as $desc => $value): ?>
							<p>
								<b><?=$display_prop["DESCRIPTION"][$desc];?></b>
								<i><a><?=($value == "Y")? '<img src="'.SITE_TEMPLATE_PATH.'/img/green_check.png" alt="">' : $value;?></a></i>
							</p>
						<? endforeach;
					 endif;
				break;

				case "N":
				case "L":
					if($display_prop["VALUE"]):?>
							<p>
								<b><?=$display_prop["NAME"];?></b>
								<i><a><?=(is_array($display_prop["VALUE"])) ? implode("<br> ",$display_prop["VALUE"]) : $display_prop["VALUE"]?></a></i>
							</p>
					<?endif;
				break;

			 endswitch; ?>
		<? endforeach; ?>
	</div>

	<?

			?>
			<div class="card_page_descr">
			<?
			$er = 0;
			$list = $_SESSION["comp"];
			$list = explode("&" , $list);
			foreach($list as $item)
			{
				if($item == $arResult["ID"])
				{
					$er++;
					break;
				}
			}
			?>


		<div class="">
			<input vl="<?=$arResult["ID"]?>" type="checkbox" class="compare-checkbox" name="compare"
				   <?=($er) ? "checked" : ""?>
				   data-tt-type="square"
				   data-tt-label-uncheck="�������� � ���������"
				   data-tt-label-check="��������� � ���������"
			/>
		</div>


		<div class="card_consult_text">
			<br>
			<p>
				���� ��� ��������� ������ � ������ ��� ������������ � �������<br/>
				�� ������� ����� <?= tplvar('phone');?> ��� <a class="fancy" style="text-decoration:none;font-weight: normal;color: #35a2e8;border-bottom: 1px dashed #35a2e8;" href="#callback_popup">�������� �������� ������</a>
			</p>
		</div>
<?
		if($arResult["DETAIL_TEXT"])
		{
?>
				<p class="title">��������</p>
				<div class="text_toggling_div desktop" data-start-height="100">
					<p><?=$arResult["DETAIL_TEXT"]?></p>
				</div>
				<a href="#" class="read_more_toggler"><span>�������� �����</span></a>
			</div>
			<?
		}
	?>
</div>
<div style="clear:both"></div>

<? if($arResult["PROPERTIES"]["DN_FILES"]["VALUE"]):?>

<div class="row" id="downloads">
	<div class="col-xs-12">
		<h5>��������� ��� ����������.</h5>
		<div class="row">
			<? foreach($arResult["PROPERTIES"]["DN_FILES"]["VALUE"] as $img => $file):
				$arFile = CFile::GetFileArray($file);
				?>
			<div class="col-xs-6 col-sm-3 col-lg-2 image-fit">
				<h6><?=stristr($arFile["ORIGINAL_NAME"],'.',true)?></h6>
				<p>
					<a href="<?=$arFile["SRC"]?>" target="_blank">
						<?if($arResult["PROPERTIES"]["DN_FILES_IMG"]["VALUE"][$img]):?>
							<img class="img-bordershadow" src="<?=CFile::GetPath($arResult["PROPERTIES"]["DN_FILES_IMG"]["VALUE"][$img]);?>">
						<?else:?>
							<i class="fa fa-file-pdf-o fa-4x" aria-hidden="true"></i>
						<?endif;?>
					</a>
				</p>
				<p><a href="<?=$arFile["SRC"]?>" class="btn-download-docs" target="_blank"><i class="fa fa-arrow-circle-o-down" aria-hidden="true"></i> �������</a></p>
			</div>
			<? endforeach; ?>
		</div>
	</div>
</div>

<?endif; ?>

<!--schema.org-->
<script type="application/ld+json">
    {
        "@context": "http://schema.org/",
        "@type": "Product",
        "name": "<?=$arResult[NAME];?>",
        "image": [
            "<?=$_SERVER[REQUEST_SCHEME].'://'.$_SERVER[SERVER_NAME].$arResult[DETAIL_PICTURE][SRC];?>"
        ],
        "offers": {
            "@type": "Offer",
            "priceCurrency": "RUB",
            "price": "<?=$arResult[PRICES][price][VALUE];?>"
        }
    }
</script>
<!--//schema.org-->


<?
$GLOBALS["recom_filter"] = array("SECTION_ID" => $arResult["IBLOCK_SECTION_ID"]);
?>



