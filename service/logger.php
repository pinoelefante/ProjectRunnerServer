<?php
    //SELECT * FROM log_request as req join log_response as resp ON req.id=resp.request_id 
    require_once(__DIR__."/../configs/app-config.php");
    require_once("database.php");
    require_once("session_global.php");
    
    set_error_handler("app_error_logger");

    function LogRequest()
    {
        if(DEBUG_ENABLE && DEBUG_SAVE_REQUEST)
        {
            $server = GetArrayToString($_SERVER);
            $get = GetArrayToString($_GET);
            $post = GetArrayToString($_POST);
            $session = HTTP_AUTHENTICATION_ENABLED ? ("GLOBALS:\n".GetGlobalsVariables()) : GetArrayToString($_SESSION);
            $query = "INSERT INTO log_request (_SERVER,_POST,_GET,_SESSION) VALUES (?,?,?,?)";
            return dbUpdate($query,"ssss",array($server,$post,$get,$session), DatabaseReturns::RETURN_INSERT_ID);
        }
    }
    function LogResponse($responseJson, $requestId, $executionTime = 0)
    {
        if(DEBUG_ENABLE && DEBUG_SAVE_RESPONSE)
        {
            $query = "INSERT INTO log_response (request_id,response, executionTime) VALUES (?,?,?)";
            dbUpdate($query,"isd",array($requestId, $responseJson,$executionTime));
        }
    }
    function LogArray($array, $file = "log_array.log")
    {
        if(DEBUG_ENABLE)
        {
            LogMessage(GetArrayToString($array), $file);
        }
    }
    function LogMessage($messaggio, $file = "log_error.log", $backtrace = false)
    {
        if(DEBUG_ENABLE && DEBUG_LOG_MESSAGE)
        {
            $timestamp = date("d/m/Y - H:i:s");
            $line = "$timestamp: $messaggio\n".($backtrace ? GetArrayToString(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))."\n" : "");
            file_put_contents ("./logs/$file", $line, FILE_APPEND | LOCK_EX);
        }
    }
    function GetArrayToString($array)
    {
        $content = "";
        if(!empty($array))
        {
            foreach($array as $key=>$value)
                $content = $content."$key".(is_array($value) ? ":\n{ ".GetArrayToString($value)."}" : " = ".$value)."\n";
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
        $session = HTTP_AUTHENTICATION_ENABLED ? ("GLOBALS:\n".GetGlobalsVariables()) : GetArrayToString($_SESSION);
        $message = "RequestId: ".$GLOBALS['requestId']."\n<br>Login parameter(default - UserID): $loginParameter<br>IP Address: $remoteAddr\n<br>SERVER:\n<br>$server\n<br>POST:\n<br>$post\n<br>GET:\n<br>$get\n<br>SESSION:\n<br>$session";
        return $message;
    }
    function GetGlobalsVariables()
    {
        $glob = "";
        if(isset($GLOBALS[LOGIN_SESSION_PARAMETER]))
            $glob=$glob."UserId: ".$GLOBALS[LOGIN_SESSION_PARAMETER]."\n";
        if(isset($_SERVER["PHP_AUTH_USER"]))
            $glob=$glob."RequestBy: ".$_SERVER["PHP_AUTH_USER"]."\n";
        return $glob;
    }
    function app_error_logger($errno, $errstr, $errfile, $errline)
    {
        $message = "[$errno] $errstr : line $errline in file $errfile";
        LogMessage("RequestId: ".$GLOBALS['requestId']."\n\n".$message, "php_warnings.log", true);
        return false;
    }
?>