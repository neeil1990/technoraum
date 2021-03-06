<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentProps = CComponentUtil::GetComponentProps("bitrix:socialnetwork.activity", $arCurrentValues);

$arEventID = Array(
	"" => GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_ALL"), 
	"system" => GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_SYSTEM"), 
	"system_groups" => GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_SYSTEM_GROUPS"), 
);

if (CSocnetUser::IsFriendsAllowed())
	$arEventID["system_friends"] = GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_SYSTEM_FRIENDS");

if (IsModuleInstalled("forum"))
	$arEventID["forum"] = GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_FORUM");
	
if (IsModuleInstalled("blog"))
	$arEventID["blog"] = GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_BLOG");

if (IsModuleInstalled("photogallery"))
	$arEventID["photo"] = GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_PHOTO");

if (IsModuleInstalled("intranet"))
{
	$arEventID["calendar"] = GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_CALENDAR");
	$arEventID["tasks"] = GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_TASKS");
}

if (IsModuleInstalled("webdav"))
	$arEventID["files"] = GetMessage("GD_ACTIVITY_P_EVENT_ID_VALUE_FILES");

$arParameters = Array(
		"USER_PARAMETERS"=> Array(
			"LOG_DATE_DAYS"=>$arComponentProps["PARAMETERS"]["LOG_DATE_DAYS"],
			"LOG_CNT" => Array(
				"NAME" => GetMessage("GD_ACTIVITY_P_LOG_CNT"),
				"TYPE" => "STRING",
				"DEFAULT" => "7"
			),
			"EVENT_ID" => Array(
				"NAME" => GetMessage("GD_ACTIVITY_P_EVENT_ID"),
				"TYPE" => "LIST",
				"VALUES" => $arEventID,
				"MULTIPLE" => "N",
				"DEFAULT" => ""
			),			
		),
	);
	
?>