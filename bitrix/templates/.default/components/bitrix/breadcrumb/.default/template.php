<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '';
$arSchema = [];

//we can't use $APPLICATION->SetAdditionalCSS() here because we are inside the buffered function GetNavChain()
$css = $APPLICATION->GetCSSArray();
if(!is_array($css) || !in_array("/bitrix/css/main/font-awesome.css", $css))
{
	$strReturn .= '<link href="'.CUtil::GetAdditionalFileURL("/bitrix/css/main/font-awesome.css").'" type="text/css" rel="stylesheet" />'."\n";
}

$strReturn .= '<div class="bx-breadcrumb" itemprop="http://schema.org/breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	$arrow = ($index > 0? '<i class="fa fa-angle-right"></i>' : '');

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
	{
		$strReturn .= '
			<div class="bx-breadcrumb-item" id="bx_breadcrumb_'.$index.'" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				'.$arrow.'
				<a href="'.$arResult[$index]["LINK"].'" title="'.$title.'" itemprop="url">
					<span itemprop="name">'.$title.'</span>
				</a>
				<meta itemprop="position" content="'.($index + 1).'" />
			</div>';
	}
	else
	{
		$strReturn .= '
			<div class="bx-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				'.$arrow.'
				<span itemprop="name">'.$title.'</span>
				<meta itemprop="position" content="'.($index + 1).'" />
			</div>';
	}

    $arSchema[$index]['position'] = $index + 1;
    $arSchema[$index]['id'] = $arResult[$index]["LINK"];
    $arSchema[$index]['name'] = $title;

}

$strReturn .= '<div style="clear:both"></div></div>';

ob_start();
?>
<!--schema.org-->
<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement":
        [
            <? foreach($arSchema as $i => $schema): ?>
            {
                "@type": "ListItem",
                "position": <?=$schema[position]?>,
                "item":
                {
                    "@id": "<?=$_SERVER[REQUEST_SCHEME].'://'.$_SERVER[SERVER_NAME].$schema[id]?>",
                    "name": "<?=$schema[name]?>"
                }
            }<?=(count($arSchema) != $i+1) ? ',' : '';?>
            <? endforeach; ?>
        ]
    }
</script>
<!--//schema.org-->
<?
$strReturn .= ob_get_contents();
ob_end_clean();

return $strReturn;
?>


