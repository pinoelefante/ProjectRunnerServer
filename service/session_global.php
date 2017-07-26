<?php
    require_once(__DIR__."/../configs/app-config.php");
    require_once("enums.php");
    require_once("connections.php");
    
    function isLogged($required = true)
    {
        /* Overengineered can just simply return true */
        $sessionVer = sessionVerification();
        if($required && !$sessionVer)
            sendResponse(StatusCodes::LOGIN_NON_LOGGATO, "");
        return $sessionVer;
    }
    function getLoginParameterFromSession($failIfNotLogged = true)
    {
        if(isset($GLOBALS[LOGIN_SESSION_PARAMETER]))
            return $GLOBALS[LOGIN_SESSION_PARAMETER];
        if($failIfNotLogged)
            sendResponse(StatusCodes::LOGIN_NON_LOGGATO, "");
        return -1;
    }
    function sessionVerification()
    {
        return isset($GLOBALS[LOGIN_SESSION_PARAMETER]);
    }
    function closeSession()
    {
        unset($GLOBALS[LOGIN_SESSION_PARAMETER]);
    }
?>