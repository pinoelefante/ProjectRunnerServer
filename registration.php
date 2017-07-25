<?php
    /* WARNING: Do non include in this page connections.php or files that using it
       or set $GLOBALS["IGNORE_AUTH"]
    */
    $GLOBALS["IGNORE_AUTH"] = 1;

    require_once("./configs/app-config.php");
    require_once("./configs/databse_tables.php");
    require_once("./service/database.php");
    require_once("./service/connection.php");

    $username = getParameter(DB_USERS_USERNAME, true);
	$password = getParameter(DB_USERS_PASSWORD, true);
	$email = getParameter(DB_USERS_EMAIL, true);
	$firstName = getParameter(DB_USERS_FIRSTNAME, true);
	$lastName = getParameter(DB_USERS_LASTNAME, true);
	$birth = getParameter(DB_USERS_BIRTH, true);
	$phone = getParameter(DB_USERS_PHONE, true);
	$timezone = getParameter(DB_USERS_TIMEZONE, true);
	
    if(Register($username,$password, $firstName,$lastName,$birth,$phone,$email, $timezone))
    {
        unset($GLOBALS["IGNORE_AUTH"]);
        DoLogin($username, $password);
    }
    else
        sendResponse(StatusCodes::FAIL);
        
    function Register($username,$password, $firstName,$lastName,$birth,$phone,$email,$timezone)
	{
		$query = "INSERT INTO ".DB_USERS_TABLE." (".DB_USERS_USERNAME.",".DB_USERS_PASSWORD.",".DB_USERS_FIRSTNAME.",".DB_USERS_LASTNAME.",".DB_USERS_BIRTH.",".DB_USERS_PHONE.",".DB_USERS_EMAIL.",".DB_USERS_TIMEZONE.") VALUES (?,?,?,?,?,?,?,?)";
		$passHash = hashPassword($password);
		return dbUpdate($query,"ssssssss",array($username,$passHash,$firstName,$lastName,$birth,$phone,$email,$timezone), DatabaseReturns::RETURN_INSERT_ID);
	}
    function DoLogin($username, $password)
    {
		$process = curl_init($_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"].substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/"))."/authentication.php?action=Login");
		curl_setopt($process, CURLOPT_USERPWD, $username.":".$password);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		$return = curl_exec($process);
		curl_close($process);
        header('Content-Type: application/json');
		echo $return;
    }
?>