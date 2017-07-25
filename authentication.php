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
		case "Register":
			if(!isLogged(false))
			{
				$username = getParameter(DB_USERS_USERNAME, true);
				$password = getParameter(DB_USERS_PASSWORD, true);
				$email = getParameter(DB_USERS_EMAIL, true);
				$firstName = getParameter(DB_USERS_FIRSTNAME, true);
				$lastName = getParameter(DB_USERS_LASTNAME, true);
				$birth = getParameter(DB_USERS_BIRTH, true);
				$phone = getParameter(DB_USERS_PHONE, true);
				$timezone = getParameter(DB_USERS_TIMEZONE, true);
				$responseContent = register($username,$password, $firstName,$lastName,$birth,$phone,$email, $timezone);
				if(is_array($responseContent))
					$responseCode = StatusCodes::OK;
				else 
				{
					$responseCode = $responseContent;
					$responseContent = null;
				}
			}
			break;
		case "Login":
			$responseCode = StatusCodes::OK;
			$responseContent = login();
			break;
		case "Logout":
			closeSession();
			$responseCode = StatusCodes::OK;
			break;
		case "ModifyField":
			$field = getParameter("field", true);
			$value = getParameter("newValue", true);
			$responseCode = modifyField($field, $value);
			break;
		case "ModifyPassword":
			$newPassword = getParameter("newPassword", true);
			$responseCode = modifyPassword($newPassword);
			break;
		
		case "SaveOptions":
			$locationId = getParameter("", true);

			break;
		case "RecoverPassword":
			break;
		case "RegisterPush":
			if(isLogged())
			{
				$token = getParameter(DB_PUSH_TOKEN, true);
				$deviceType = getParameter(DB_PUSH_DEVICEOS, true);
				$deviceId = getParameter(DB_PUSH_DEVICEID, true);
				$responseCode = RegistraDevice($token, $deviceType,$deviceId);
			}
			break;
		case "UnregisterPush":
			if(isLogged())
			{
				$token = getParameter(DB_PUSH_TOKEN, true);
				$deviceType = getParameter(DB_PUSH_DEVICEOS, true);
				$deviceId = getParameter(DB_PUSH_DEVICEID, true);
				$responseCode = UnRegistraDevice($token, $deviceType,$deviceId);
			}
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
	function register($username,$password, $firstName,$lastName,$birth,$phone,$email,$timezone)
	{
		$query = "INSERT INTO ".DB_USERS_TABLE." (".DB_USERS_USERNAME.",".DB_USERS_PASSWORD.",".DB_USERS_FIRSTNAME.",".DB_USERS_LASTNAME.",".DB_USERS_BIRTH.",".DB_USERS_PHONE.",".DB_USERS_EMAIL.",".DB_USERS_TIMEZONE.") VALUES (?,?,?,?,?,?,?,?)";
		$passHash = hashPassword($password);
		$res = dbUpdate($query,"ssssssss",array($username,$passHash,$firstName,$lastName,$birth,$phone,$email,$timezone), DatabaseReturns::RETURN_INSERT_ID);
		if($res > 0)
			return login($username, $password);
		return StatusCodes::FAIL;
	}
	function modifyField($field, $value)
	{
		$userId = getLoginParameterFromSession();
		$query = "UPDATE ".DB_USERS_TABLE." SET $field = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "si", array($value, $userId)) ? StatusCodes::OK : StatusCodes::FAIL;
	}
	function modifyPassword($newPassword)
	{
		$userId = getLoginParameterFromSession();
		$passHash = hashPassword($newPassword);
		$query = "UPDATE ".DB_USERS_TABLE." SET ".DB_USERS_PASSWORD." = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "si", array($newPassword, $userId)) ? StatusCodes::OK : StatusCodes::FAIL;
	}
	function SaveOptions($defaultLocation, $timezone, $notifyNearbyActivities)
	{
		$userId = getLoginParameterFromSession();
		$query = "UPDATE ".DB_USERS_TABLE." SET ".DB_USERS_LOCATION_ID." = ?, ".DB_USERS_TIMEZONE." = ?, ".DB_USERS_NOTIFY_NEARBY." = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "isii", array($defaultLocation,$timezone,$notifyNearbyActivities,$userId));
	}
?>