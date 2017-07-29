<?php
    /* WARNING: Do non include in this page connections.php or files that using it
       or set $GLOBALS["IGNORE_AUTH"]
    */
    $GLOBALS["IGNORE_AUTH"] = 1;

    require_once("./configs/app-config.php");
    require_once("./configs/database_tables.php");
    require_once("./service/database.php");
    require_once("./service/enums.php");
    require_once("./service/connections.php");

    $responseCode = StatusCodes::METODO_ASSENTE;
    $action = getParameter("action", true);
    switch($action)
    {
        case "Register":
            $username = getParameter(DB_USERS_USERNAME, true);
            $password = getParameter(DB_USERS_PASSWORD, true);
            $email = getParameter(DB_USERS_EMAIL, true);
            $firstName = ucfirst(getParameter(DB_USERS_FIRSTNAME, true));
            $lastName = ucfirst(getParameter(DB_USERS_LASTNAME, true));
            $birth = getParameter(DB_USERS_BIRTH);
            $phone = getParameter(DB_USERS_PHONE);
            $timezone = getParameter(DB_USERS_TIMEZONE, true);
            
            if(Register($username,$password, $firstName,$lastName,$birth,$phone,$email, $timezone))
            {
                unset($GLOBALS["IGNORE_AUTH"]);
                DoLogin($username, $password);
                return;
            }
            $responseCode = StatusCodes::FAIL;
            break;
        case "ListTimezones":
            $country = getParameter("country", true);
            $offset = getParameter("offset", true);
            $responseContent = ListTimezones($country, $offset);
            $responseCode = is_array($responseContent) && count($responseContent) > 0 ? StatusCodes::OK : StatusCodes::FAIL;
            break;
    }
    sendResponse($responseCode, $responseContent);
      
    function Register($username,$password, $firstName,$lastName,$birth,$phone,$email,$timezone)
	{
		$query = "INSERT INTO ".DB_USERS_TABLE." (".DB_USERS_USERNAME.",".DB_USERS_PASSWORD.",".DB_USERS_FIRSTNAME.",".DB_USERS_LASTNAME.",".DB_USERS_BIRTH.",".DB_USERS_PHONE.",".DB_USERS_EMAIL.",".DB_USERS_TIMEZONE.") VALUES (?,?,?,?,?,?,?,?)";
		$passHash = hashPassword($password);
		return dbUpdate($query,"ssssssss",array($username,$passHash,$firstName,$lastName,$birth,$phone,$email,$timezone), DatabaseReturns::RETURN_INSERT_ID);
	}
    function DoLogin($username, $password)
    {
		$process = curl_init(/*$_SERVER["REQUEST_SCHEME"].*/"https://".$_SERVER["SERVER_NAME"].substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/"))."/authentication.php?action=Login");
		curl_setopt($process, CURLOPT_USERPWD, $username.":".$password);
        curl_setopt($process, CURLOPT_USERAGENT, CLIENT_USER_AGENT);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		$return = curl_exec($process);
		curl_close($process);
        header('Content-Type: application/json');
		echo $return;
    }
    function ListTimezones($country, $offset)
    {
        $timezone = timezone_identifiers_list(DateTimeZone::PER_COUNTRY, $country);
        if(count($timezone) > 1)
        {
            $filter = array();
			$time = new DateTime();
            foreach ($timezone as $t) 
            {
                $o = timezone_offset_get(new DateTimeZone($t), $time);
                if($offset == $o)
                    array_push($filter, $t);
            }
            if(count($filter) > 0)
                return $filter;
        }
	    return $timezone;
    }
    /* Not used - Just used to generate Resx for client*/
    function ListTimezonesToResourceFileResx()
    {
        $list = timezone_identifiers_list(DateTimeZone::ALL);
        $content = "";
        foreach($list as $t)
        {
            $newName = str_replace("-","_minus_",str_replace("/", "__", $t));
            $value = str_replace("_", " ",substr($t, strrpos($t, "/")+1));
            $content .= "<data name=\"$newName\" xml:space=\"preserve\">\n\t<value>$value</value>\n</data>\n";
        }
        file_put_contents("res.xml", $content);
    }
?>