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
		case "Login":
			$responseCode = StatusCodes::OK;
			$responseContent = login();
			break;
		case "Logout":
			closeSession();
			$responseCode = StatusCodes::OK;
			break;
		case "ModifyPassword":
			$newPassword = getParameter("newPassword", true);
			$responseCode = modifyPassword($newPassword);
			break;
		case "RecoverPassword":
			break;
		case "RegisterPush":
			$token = getParameter(DB_PUSH_TOKEN, true);
			$deviceType = getParameter(DB_PUSH_DEVICEOS, true);
			$deviceId = getParameter(DB_PUSH_DEVICEID, true);
			$responseCode = RegistraDevice($token, $deviceType,$deviceId);
			break;
		case "UnregisterPush":
			$token = getParameter(DB_PUSH_TOKEN, true);
			$deviceType = getParameter(DB_PUSH_DEVICEOS, true);
			$deviceId = getParameter(DB_PUSH_DEVICEID, true);
			$responseCode = UnRegistraDevice($token, $deviceType,$deviceId);
			break;
        default:
            $responseCode = StatusCodes::METODO_ASSENTE;
            break;
    }
    sendResponse($responseCode, $responseContent);

	function login()
	{
		//It's not an usual login because login is now done with HTTP Authentication
		$userId = getLoginParameterFromSession();
		$query = "SELECT * FROM ".DB_USERS_TABLE." WHERE ".DB_USERS_ID." = ?";
        $res = dbSelect($query,"i", array($userId), true);
		return array_remove_keys_starts($res, DB_USERS_PASSWORD);
	}
	function modifyPassword($newPassword)
	{
		$userId = getLoginParameterFromSession();
		$passHash = hashPassword($newPassword);
		$query = "UPDATE ".DB_USERS_TABLE." SET ".DB_USERS_PASSWORD." = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "si", array($newPassword, $userId)) ? StatusCodes::OK : StatusCodes::FAIL;
	}
?>