<?php
    session_start();
    
	require_once("./service/config.php");
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
			$username = getParameter("username", true);
			$password = getParameter("password", true);
			$email = getParameter("email", true);
			$firstName = getParameter("firstName");
			$lastName = getParameter("lastName");
			$birth = getParameter("birth");
			$phone = getParameter("phone");
			$responseCode = register($username,$password, $firstName,$lastName,$birth,$phone,$email);
			break;
		case "Login":
			$username = getParameter("username", true);
            $password = getParameter("password", true);
			$responseCode = login($username, $password) ? StatusCodes::OK : StatusCodes::LOGIN_ERROR;
			break;
		case "Logout":
			closeSession();
			$responseCode = StatusCodes::OK;
			break;
		case "Modify":
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
		case "RecoverPassword":
			break;
		case "RegisterPush":
			if(isLogged())
			{
				$token = getParameter("token", true);
				$deviceType = getParameter("deviceOS", true);
				$deviceId = getParameter("deviceId", true);
				$responseCode = RegistraDevice($token, $deviceType,$deviceId);
			}
			break;
		case "UnregisterPush":
			if(isLogged())
			{
				$token = getParameter("token", true);
				$deviceType = getParameter("deviceOS", true);
				$deviceId = getParameter("deviceId", true);
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
		$query = "SELECT ".DB_USERS_PASSWORD.",".DB_USERS_ID." FROM ".DB_USERS_TABLE." WHERE ".DB_USERS_USERNAME." = ?";
        $res = dbSelect($query,"s", array($username), true);
		if($res != null && password_verify($password, $res[DB_USERS_PASSWORD]))
		{
			$_SESSION[LOGIN_SESSION_PARAMETER] = $res[DB_USERS_ID];
			return true;
		}
		return false;
	}
	function register($username,$password, $firstName,$lastName,$birth,$phone,$email)
	{
		$query = "INSERT INTO ".DB_USERS_TABLE." (".DB_USERS_USERNAME.",".DB_USERS_PASSWORD.",".DB_USERS_FIRSTNAME.",".DB_USERS_LASTNAME.",".DB_USERS_BIRTH.",".DB_USERS_PHONE.",".DB_USERS_EMAIL.") VALUES (?,?,?,?,?,?,?)";
		$passHash = hashPassword($password);
		$res = dbUpdate($query,"sssssss",array($username,$passHash,$firstName,$lastName,$birth,$phone,$email), DatabaseReturns::RETURN_INSERT_ID);
		if($res > 0)
		{
			$_SESSION[LOGIN_SESSION_PARAMETER] = $res;
			return StatusCodes::OK;
		}
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
	function getProfileInfo()
	{
		$userId = getLoginParameterFromSessions();
		$query = "SELECT ".DB_USERS_USERNAME.",".DB_USERS_FIRSTNAME.",".DB_USERS_LASTNAME.",".DB_USERS_EMAIL.",".DB_USERS_BIRTH.",".DB_USERS_PHONE.",".DB_USERS_REGISTRATION.",".DB_USERS_LASTUPDATE." FROM ".DB_USERS_TABLE." WHERE ".DB_USERS_ID." = ?";
		return dbSelect($query,"i",array($userId));
	}
?>