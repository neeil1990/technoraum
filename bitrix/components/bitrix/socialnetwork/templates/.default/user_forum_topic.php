<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$pageId = "user_forum";
include("util_menu.php");
include("util_profile.php");
?>
<?$arInfo = $APPLICATION->IncludeComponent("bitrix:socialnetwork.forum.topic.read", "", 
	Array(
		"FID"	=>	$arParams["FORUM_ID"],
		"TID"	=>	$arResult["VARIABLES"]["topic_id"],
		"MID"	=>	$arResult["VARIABLES"]["message_id"],
		"ACTION" => $arResult["VARIABLES"]["action"], 
		
		"SOCNET_GROUP_ID" => 0, 
		"USER_ID" => $arResult["VARIABLES"]["user_id"], 
		
		"URL_TEMPLATES_TOPIC_LIST"	=>	$arResult["~PATH_TO_USER_FORUM"],
		"URL_TEMPLATES_TOPIC"	=>	$arResult["~PATH_TO_USER_FORUM_TOPIC"],
		"URL_TEMPLATES_TOPIC_EDIT"	=>	$arResult["~PATH_TO_USER_FORUM_TOPIC_EDIT"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["~PATH_TO_USER_FORUM_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["~PATH_TO_USER"],
		
		"PAGEN" => $arParams["PAGEN"],
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"PAGE_NAVIGATION_SHOW_ALL" =>  $arParams["PAGE_NAVIGATION_SHOW_ALL"],
		
		"MESSAGES_PER_PAGE"	=>	$arParams["MESSAGES_PER_PAGE"],
		
		"PATH_TO_ICON"	=> $arParams["PATH_TO_FORUM_ICON"],
		"PATH_TO_SMILE"	=> $arParams["PATH_TO_FORUM_SMILE"],
		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"],
		"IMAGE_SIZE"	=>	$arParams["IMAGE_SIZE"],
		"DATE_FORMAT"	=>	$arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT"	=>	$arParams["DATE_TIME_FORMAT"],
		
		"SHOW_RATING"	=>	$arParams["SHOW_RATING"],
		"RATING_ID"	=>	$arParams["RATING_ID"],
		"SET_TITLE"	=>	$arParams["SET_TITLE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	), 
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
if (!empty($arInfo) && $arInfo["PERMISSION"] > "E"):
?><?$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.forum.post_form", 
	"", 
	Array(
		"FID"	=>	$arParams["FORUM_ID"],
		"TID"	=>	$arResult["VARIABLES"]["topic_id"],
		"MID"	=>	$arResult["VARIABLES"]["message_id"],
		"PAGE_NAME"	=>	"user_forum_message",
		"MESSAGE_TYPE"	=>	"REPLY",
		"bVarsFromForm" => $arInfo["bVarsFromForm"],
		
		"SOCNET_GROUP_ID" => 0, 
		"USER_ID" => $arResult["VARIABLES"]["user_id"], 
		
		"URL_TEMPLATES_TOPIC_LIST" =>  $arResult["~PATH_TO_USER_FORUM_TOPIC"],
		"URL_TEMPLATES_MESSAGE" => $arResult["~PATH_TO_USER_FORUM_MESSAGE"],
		
		"MESSAGE" => $arInfo["MESSAGE"],
		"ERROR_MESSAGE" => $arInfo["ERROR_MESSAGE"],
		
		"PATH_TO_SMILE"	=>	$arParams["PATH_TO_FORUM_SMILE"],
		"PATH_TO_ICON"	=>	$arParams["PATH_TO_FORUM_ICON"],
		"SMILE_TABLE_COLS" => $arParams["SMILE_TABLE_COLS"],
		"AJAX_TYPE" => $arParams["AJAX_TYPE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"]),
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
endif;
?>