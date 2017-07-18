<?php
    session_start();
    
	require_once("./configs/app-config.php");
	require_once("./service/connections.php");
	require_once("./service/database.php");
	require_once("./service/database_tables.php");
    require_once("./service/enums.php");
    require_once("./service/functions.php");
	require_once("./service/push_notifications.php");
	require_once("./service/session.php");
    
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
				$responseCode = register($username,$password, $firstName,$lastName,$birth,$phone,$email, $timezone);
			}
			break;
		case "Login":
			$username = getParameter(DB_USERS_USERNAME, true);
            $password = getParameter(DB_USERS_PASSWORD, true);
			$responseCode = login($username, $password) ? StatusCodes::OK : StatusCodes::LOGIN_ERROR;
			if($responseCode==StatusCodes::OK)
				$responseContent = $_SESSION["user_profile"];
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
		case "GetProfileInfo":
			$responseContent = getProfileInfo();
			$responseCode = StatusCodes::OK;
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

	function login($username, $password)
	{
		$query = "SELECT * FROM ".DB_USERS_TABLE." WHERE ".DB_USERS_USERNAME." = ?";
        $res = dbSelect($query,"s", array($username), true);
		if($res != null && password_verify($password, $res[DB_USERS_PASSWORD]))
		{
			$_SESSION[LOGIN_SESSION_PARAMETER] = $res[DB_USERS_ID];
			$_SESSION["user_profile"] = array_remove_keys_starts($res, DB_USERS_PASSWORD);
			return true;
		}
		return false;
	}
	function register($username,$password, $firstName,$lastName,$birth,$phone,$email,$timezone)
	{
		$query = "INSERT INTO ".DB_USERS_TABLE." (".DB_USERS_USERNAME.",".DB_USERS_PASSWORD.",".DB_USERS_FIRSTNAME.",".DB_USERS_LASTNAME.",".DB_USERS_BIRTH.",".DB_USERS_PHONE.",".DB_USERS_EMAIL.",".DB_USERS_TIMEZONE.") VALUES (?,?,?,?,?,?,?,?)";
		$passHash = hashPassword($password);
		$res = dbUpdate($query,"ssssssss",array($username,$passHash,$firstName,$lastName,$birth,$phone,$email,$timezone), DatabaseReturns::RETURN_INSERT_ID);
		if($res > 0)
			return login($username, $password) ? StatusCodes::OK : StatusCodes::FAIL;
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
	function getProfileInfo($userId)
	{
		$query = "SELECT ".DB_USERS_USERNAME.",".DB_USERS_FIRSTNAME.",".DB_USERS_LASTNAME.",".DB_USERS_EMAIL.",".DB_USERS_BIRTH.",".DB_USERS_PHONE.",".DB_USERS_REGISTRATION.",".DB_USERS_LASTUPDATE.",".DB_USERS_SEX.", (SELECT COUNT(*) FROM ".DB_FRIEND_TABLE." WHERE ".DB_FRIEND_USER." = ?) as friends FROM ".DB_USERS_TABLE." WHERE ".DB_USERS_ID." = ?";
		return dbSelect($query,"ii",array($userId, $userId));
	}
	function SaveOptions($defaultLocation, $timezone, $notifyNearbyActivities)
	{
		$userId = getLoginParameterFromSession();
		$query = "UPDATE ".DB_USERS_TABLE." SET ".DB_USERS_LOCATION_ID." = ?, ".DB_USERS_TIMEZONE." = ?, ".DB_USERS_NOTIFY_NEARBY." = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "isii", array($defaultLocation,$timezone,$notifyNearbyActivities,$userId));
	}
?>