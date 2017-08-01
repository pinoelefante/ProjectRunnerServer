<?php
	require_once("./configs/app-config.php");
	require_once("./configs/database_tables.php");
	require_once("./service/connections.php");
	require_once("./service/database.php");
    require_once("./service/enums.php");
    require_once("./service/functions.php");
	require_once("./service/push_notifications.php");
	require_once("./service/session_global.php");
    
    $action = getParameter("action", true);
    $responseCode = StatusCodes::FAIL;
    $responseContent = "";
    switch($action)
    {
        case "ModifyField":
			$field = getParameter("field", true);
			$value = getParameter("newValue", true);
			$responseCode = modifyField($field, $value);
			break;
        case "SaveOptions":
			/*
			$locationId = getParameter("", true);
			*/
			break;
    }
    sendResponse($responseCode, $responseContent);

    function modifyField($field, $value)
	{
		$userId = getLoginParameterFromSession();
		$query = "UPDATE ".DB_USERS_TABLE." SET $field = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "si", array($value, $userId)) ? StatusCodes::OK : StatusCodes::FAIL;
	}
    function SaveOptions($defaultLocation, $timezone, $notifyNearbyActivities)
	{
		$userId = getLoginParameterFromSession();
		$query = "UPDATE ".DB_USERS_TABLE." SET ".DB_USERS_LOCATION_ID." = ?, ".DB_USERS_TIMEZONE." = ?, ".DB_USERS_NOTIFY_NEARBY." = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "isii", array($defaultLocation,$timezone,$notifyNearbyActivities,$userId));
	}
?>