<?php
    session_start();
    
	require_once("./service/config.php");
	require_once("./service/connections.php");
	require_once("./service/database.php");
    require_once("./service/database_tables.php");
    require_once("./service/enums.php");
    require_once("./service/functions.php");
    require_once("./service/logger.php");
	require_once("./service/push_notifications.php");
	require_once("./service/session.php");
    
    $action = getParameter("action", true);
    $responseCode = StatusCodes::FAIL;
    $responseContent = "";
    switch($action)
    {
		case "CreateActivity":
            $startTime = getParameter(DB_ACTIVITIES_STARTTIME, true);
            $mpLong = getParameter(DB_ACTIVITIES_MPLONG);
            $mpLat = getParameter(DB_ACTIVITIES_MPLAT);
            $mpAddress = getParameter(DB_ACTIVITIES_MPADDR);
            $requiredPlayers = getParameter(DB_ACTIVITIES_REQUIREDPLAYERS, true);
            $guestUsers = getParameter(DB_ACTIVITIES_GUESTUSERS);
            $fee = getParameter(DB_ACTIVITIES_FEE);
            $sport = getParameter(DB_ACTIVITIES_SPORT, true);
            $feedback = getParameter(DB_ACTIVITIES_FEEDBACK);
            $sportDetails = getParametersStartingBy("sportDetails_");
            $responseCode = createActivity($startTime, $mpLong, $mpLat, $mpAddress, $requiredPlayers, $guestUsers, $sport, $fee, $feedback, $sportDetails);
            break;
        case "JoinActivity":
            $idActivity = getParameter(DB_ACTIVITIES_ID,true);
            $responseCode = joinActivity($activityId) ? StatusCodes::OK : StatusCodes::FAIL;
            break;
        case "LeaveActivity":
            $idActivity = getParameter(DB_ACTIVITIES_ID, true);
            $responseCode = leaveActivity($idActivity) ? StatusCodes:: OK : StatusCodes::FAIL;
            break;
        case "InfoActivity":
            $idActivity = getParameter(DB_ACTIVITIES_ID, true);
            $responseCode = StatusCodes::OK;
            $responseContent = getActivity($idActivity);
            break;
        case "DeleteActivity":
            $idActivity = getParameter(DB_ACTIVITIES_ID,true);
            $responseCode = deleteActivity($idActivity) ? StatusCodes::OK : StatusCodes::FAIL;
            break;
        case "ModifyActivity":

            break;
        default:
            $responseCode = StatusCodes::METODO_ASSENTE;
            break;
    }
    sendResponse($responseCode, $responseContent);

    function createActivity($startTime, $mpLongitude, $mpLatitude, $mpAddress, $requiredPlayers, $guestUsers, $sport, $fee, $feedback, $sportDetails)
    {
        $userId = getLoginParameterFromSession();
        $query = "INSERT INTO ".DB_ACTIVITIES_TABLE." (".DB_ACTIVITIES_CREATEDBY.",".DB_ACTIVITIES_STARTTIME.",".DB_ACTIVITIES_MPLONG.",".DB_ACTIVITIES_MPLAT.",".DB_ACTIVITIES_MPADDR.",".DB_ACTIVITIES_SPORT.",".DB_ACTIVITIES_FEE.",".DB_ACTIVITIES_FEEDBACK.",".DB_ACTIVITIES_REQUIREDPLAYERS.",".DB_ACTIVITIES_GUESTUSERS.") VALUES (?,?,?,?,?,?,?,?,?,?)";
        $activityId = dbUpdate($query, "isddsidiii", array($userId,$startTime,$mpLongitude,$mpLatitude, $mpAddress, $sport, $fee, $feedback,$requiredPlayers, $guestUsers), DatabaseReturns::RETURN_INSERT_ID);

        if($activityId > 0)
        {
            if(createSport($activityId, $sport, $sportDetails))
                return StatusCodes::OK;
            else
            {
                deleteActivitySystem($activityId);
                return StatusCodes::FAIL;
            }
        }
        return StatusCodes::FAIL;
    }
    function createSport($activityId, $sport, $sportDetails)
    {
        switch($sport)
        {
            case Sports::RUNNING:
                return createRunning($activityId, $sportDetails);
            case Sports::FOOTBALL:
                return createFootball($activityId, $sportDetails);
            case Sports::BICYCLE:
                return createBicycle($activityId, $sportDetails);
            case Sports::TENNIS:
                return createTennis($activityId, $sportDetails);
        }
        return false;
    }
    function createFootball($activityId, $details)
    {
        if($details[DB_FOOTBALL_PLAYERSPERTEAM]<=0)
            return false;
        $query = "INSERT INTO ".DB_FOOTBALL_TABLE." (".DB_FOOTBALL_ID.",".DB_FOOTBALL_PLAYERSPERTEAM.") VALUES (?,?)";
        return dbUpdate($query, "ii", array($activityId, $details[DB_FOOTBALL_PLAYERSPERTEAM]));
    }
    function createRunning($activityId, $details)
    {
        if($details[DB_RUNNING_DISTANCE]<=0)
            return false;
        $query = "INSERT INTO ".DB_RUNNING_TABLE." (".DB_RUNNING_ID.",".DB_RUNNING_DISTANCE.",".DB_RUNNING_FITNESS.") VALUES (?,?,?)";
        return dbUpdate($query, "idi", array($activityId, $details[DB_RUNNING_DISTANCE], $details[DB_RUNNING_FITNESS]));
    }
    function createBicycle($activityId, $details)
    {
        if($details[DB_BICYCLE_DISTANCE]<=0)
            return false;
        $query = "INSERT INTO ".DB_BICYCLE_TABLE." (".DB_BICYCLE_ID.",".DB_BICYCLE_DISTANCE.") VALUES (?,?)";
        return dbUpdate($query, "id", array($activityId, $details[DB_BICYCLE_DISTANCE]));
    }
    function createTennis($activityId, $details)
    {
        $query = "INSERT INTO ".DB_TENNIS_TABLE." (".DB_TENNIS_ID.",".DB_TENNIS_DOUBLE.") VALUES (?,?)";
        return dbUpdate($query, "ii", array($activityId, $details[DB_TENNIS_DOUBLE]));
    }
    function deleteActivitySystem($activityId)
    {
        $query = "DELETE FROM ".DB_ACTIVITIES_TABLE." WHERE ".DB_ACTIVITIES_ID." = ?";
        return dbUpdate($query, "i", array($activityId));
    }
    function getActivity($activityId)
    {
        $query = "SELECT ".DB_ACTIVITIES_ID.",".DB_ACTIVITIES_CREATEDBY.",".DB_ACTIVITIES_STARTTIME.",".DB_ACTIVITIES_MPLONG.",".DB_ACTIVITIES_MPLAT.",".DB_ACTIVITIES_MPADDR.",".DB_ACTIVITIES_REQUIREDPLAYERS.",".DB_ACTIVITIES_STATUS.",".DB_ACTIVITIES_SPORT.",".DB_ACTIVITIES_FEE.",".DB_ACTIVITIES_FEEDBACK.",".DB_ACTIVITIES_GUESTUSERS.", (SELECT COUNT(*) FROM ".DB_ACTIVITIES_JOINS_TABLE." WHERE ".DB_ACTIVITIES_JOINS_ACTIVITY." = ?) as joinedPlayers FROM ".DB_ACTIVITIES_TABLE." WHERE ".DB_ACTIVITIES_ID." = ?";
        $res = dbSelect($query,"ii",array($activityId,$activityId), true);
        $players = 1 + $res[DB_ACTIVITIES_GUESTUSERS] + $res["joinedPlayers"];
        //$res["totalPlayers"] = $players;
        switch($res[DB_ACTIVITIES_SPORT])
        {
            case Sports::RUNNING:
                return array_merge($res, getRunning($activityId));
            case Sports::FOOTBALL:
                return array_merge($res, getFootball($activityId));
            case Sports::BICYCLE:
                return array_merge($res, getBicycle($activityId));
            case Sports::TENNIS:
                return array_merge($res, getTennis($activityId));
        }
    }
    function getFootball($activityId)
    {
        $query = "SELECT ".DB_FOOTBALL_PLAYERSPERTEAM." FROM ".DB_FOOTBALL_TABLE." WHERE ".DB_FOOTBALL_ID." = ?";
        return dbSelect($query,"i", array($activityId));
    }
    function getRunning($activityId)
    {
        $query = "SELECT ".DB_RUNNING_DISTANCE.",".DB_RUNNING_TRAVELED.",".DB_RUNNING_FITNESS." FROM ".DB_RUNNING_TABLE." WHERE ".DB_RUNNING_ID." = ?";
        return dbSelect($query, "i", array($activityId), true);
    }
    function getTennis($activityId)
    {
        $query = "SELECT ".DB_TENNIS_DOUBLE." FROM ".DB_TENNIS_TABLE." WHERE ".DB_TENNIS_ID." = ?";
        return dbSelect($query,"i", array($activityId), true);
    }
    function getBicycle($activityId)
    {
        $query = "SELECT ".DB_BICYCLE_DISTANCE.",".DB_BICYCLE_TRAVELED." FROM ".DB_BICYCLE_TABLE." WHERE ".DB_BICYCLE_ID." = ?";
        return dbSelect($query, "i", array($activityId), true);
    }
    function joinActivity($activityId)
    {
        //TODO verificare il numero di utenti partecipanti
        //creatore + guestUsers + utenti join < requiredPlayers

        $userId = getLoginParameterFromSession();
        $query = "INSERT INTO ".DB_ACTIVITIES_JOINS_TABLE." (".DB_ACTIVITIES_JOINS_ACTIVITY.",".DB_ACTIVITIES_JOINS_USER.") VALUES (?,?)";
        return dbUpdate($query, "ii", array($activityId, $userId));
    }
    function leaveActivity($activityId)
    {
        //TODO verificare che l'activity non sia già conclusa
        $userId = getLoginParameterFromSession();
        $query = "DELETE FROM ".DB_ACTIVITIES_JOINS_TABLE." WHERE ".DB_ACTIVITIES_JOINS_ACTIVITY." = ? AND ".DB_ACTIVITIES_JOINS_USER." = ?";
        return dbUpdate($query,"ii",array($activityId, $userId), DatabaseReturns::RETURN_AFFECTED_ROWS) > 0 ? true : false;
    }
    function deleteActivity($activityId)
    {
        $userId = getLoginParameterFromSession();
        $query = "DELETE FROM ".DB_ACTIVITIES_TABLE." WHERE ".DB_ACTIVITIES_ID." = ? AND ".DB_ACTIVITIES_CREATEDBY." = ?";
        return dbUpdate($query, "ii", array($activityId,$userId), DatabaseReturns::RETURN_AFFECTED_ROWS) > 0 ? true : false;
    }
?>