<?php
    //SELECT * FROM log_request as req join log_response as resp ON req.id=resp.request_id 
    require_once("config.php");
    require_once("database.php");
    require_once("session.php");
    function LogRequest()
    {
        if(DEBUG_ENABLE && DEBUG_SAVE_REQUEST)
        {
            $server = GetArrayToString($_SERVER);
            $get = GetArrayToString($_GET);
            $post = GetArrayToString($_POST);
            $session = GetArrayToString($_SESSION);
            $query = "INSERT INTO log_request (_SERVER,_POST,_GET,_SESSION) VALUES (?,?,?,?)";
            return dbUpdate($query,"ssss",array($server,$post,$get,$session), DatabaseReturns::RETURN_INSERT_ID);
        }
    }
    function LogResponse($responseJson, $requestId)
    {
        if(DEBUG_ENABLE && DEBUG_SAVE_RESPONSE)
        {
            $query = "INSERT INTO log_response (request_id,response) VALUES (?,?)";
            dbUpdate($query,"is",array($requestId, $responseJson));
        }
    }
    function LogMessage($messaggio, $file = "log_error.log")
    {
        if(DEBUG_ENABLE && DEBUG_LOG_MESSAGE)
        {
            $timestamp = date("d/m/Y - H:i:s");
            $line = "$timestamp: $messaggio\n";
            file_put_contents ("./logs/$file", $line, FILE_APPEND | LOCK_EX);
        }
    }
    function GetArrayToString($array)
    {
        $content = "";
        if(!empty($array))
        {
            foreach($array as $key=>$value)
                $content = $content."$key = $value\n";
        }
        return $content;
    }
    function GetDebugMessage()
    {
        $loginParameter = getLoginParameterFromSession(false);
        $remoteAddr = $_SERVER['REMOTE_ADDR'];
        $server = GetArrayToString($_SERVER);
        $get = GetArrayToString($_GET);
        $post = GetArrayToString($_POST);
        $session = GetArrayToString($_SESSION);
        $message = "RequestId: ".$GLOBALS['requestId']."\n<br>Login parameter(default - UserID): $loginParameter<br>IP Address: $remoteAddr\n<br>SERVER:\n<br>$server\n<br>POST:\n<br>$post\n<br>GET:\n<br>$get\n<br>SESSION:\n<br>$session";
        return $message;
    }
?>