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
            $maxPlayers = getParameter(DB_ACTIVITIES_MAXPLAYERS, true);
            $guestUsers = getParameter(DB_ACTIVITIES_GUESTUSERS);
            $fee = getParameter(DB_ACTIVITIES_FEE);
            $sport = getParameter(DB_ACTIVITIES_SPORT, true);
            $feedback = getParameter(DB_ACTIVITIES_FEEDBACK);
            $sportDetails = getParametersStartingBy("sportDetails_");
            $responseCode = createActivity($startTime, $mpLong, $mpLat, $mpAddress, $maxPlayers, $guestUsers, $sport, $fee, $feedback, $sportDetails);
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
        case "ModifyActivityField":

            break;
        case "MyActivitiesList":
            $status = getParameter(DB_ACTIVITIES_STATUS);
            $sport = getParameter(DB_ACTIVITIES_SPORT);
            //$orderBy = getParameter("orderBy");
            //$orderDirection = getParameter("order", false, 4);
            $responseContent = getMyActivities($status, $sport/*,$orderBy,$orderDirection*/);
            $responseCode = StatusCodes::OK;
            break;
        case "SearchActivities":

            break;
        default:
            $responseCode = StatusCodes::METODO_ASSENTE;
            break;
    }
    sendResponse($responseCode, $responseContent);

    function createActivity($startTime, $mpLongitude, $mpLatitude, $mpAddress, $maxPlayers, $guestUsers, $sport, $fee, $feedback, $sportDetails)
    {
        $userId = getLoginParameterFromSession();
        $query = "INSERT INTO ".DB_ACTIVITIES_TABLE." (".DB_ACTIVITIES_CREATEDBY.",".DB_ACTIVITIES_STARTTIME.",".DB_ACTIVITIES_MPLONG.",".DB_ACTIVITIES_MPLAT.",".DB_ACTIVITIES_MPADDR.",".DB_ACTIVITIES_SPORT.",".DB_ACTIVITIES_FEE.",".DB_ACTIVITIES_FEEDBACK.",".DB_ACTIVITIES_MAXPLAYERS.",".DB_ACTIVITIES_GUESTUSERS.") VALUES (?,?,?,?,?,?,?,?,?,?)";
        $activityId = dbUpdate($query, "isddsidiii", array($userId,$startTime,$mpLongitude,$mpLatitude, $mpAddress, $sport, $fee, $feedback,$maxPlayers, $guestUsers), DatabaseReturns::RETURN_INSERT_ID);

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
        $query = "SELECT act.*,bike.".DB_BICYCLE_DISTANCE." as bicycle_".DB_BICYCLE_DISTANCE.", bike.".DB_BICYCLE_TRAVELED." as bicycle_".DB_BICYCLE_TRAVELED.", run.".DB_RUNNING_DISTANCE." as running_".DB_RUNNING_DISTANCE.", run.".DB_RUNNING_TRAVELED." as running_".DB_RUNNING_TRAVELED.", run.".DB_RUNNING_FITNESS." as running_".DB_RUNNING_FITNESS.", foot.".DB_FOOTBALL_PLAYERSPERTEAM." as football_".DB_FOOTBALL_PLAYERSPERTEAM.", ten.".DB_TENNIS_DOUBLE." as tennis_".DB_TENNIS_DOUBLE.",(SELECT COUNT(*) FROM ".DB_ACTIVITIES_JOINS_TABLE." WHERE ".DB_ACTIVITIES_JOINS_ACTIVITY." = act.".DB_ACTIVITIES_ID.") as joinedPlayers FROM ".DB_ACTIVITIES_TABLE." AS act".
            " LEFT JOIN ".DB_RUNNING_TABLE." AS run ON (act.".DB_ACTIVITIES_SPORT." = ".Sports::RUNNING." AND act.".DB_ACTIVITIES_ID." = run.".DB_RUNNING_ID.")".
            " LEFT JOIN ".DB_FOOTBALL_TABLE." AS foot ON (act.".DB_ACTIVITIES_SPORT." = ".Sports::FOOTBALL." AND act.".DB_ACTIVITIES_ID." = foot.".DB_FOOTBALL_ID.")".
            " LEFT JOIN ".DB_BICYCLE_TABLE." AS bike ON (act.".DB_ACTIVITIES_SPORT." = ".Sports::BICYCLE." AND act.".DB_ACTIVITIES_ID." = bike.".DB_BICYCLE_ID.")".
            " LEFT JOIN ".DB_TENNIS_TABLE." AS ten ON (act.".DB_ACTIVITIES_SPORT." = ".Sports::TENNIS." AND act.".DB_ACTIVITIES_ID." = ten.".DB_TENNIS_ID.")".
            " WHERE act.".DB_ACTIVITIES_ID." = ?";
        $activity = dbSelect($query, "i", array($activityId), true);
        $activity = normalizeActivity($activity);
        return $activity;
    }
    function joinActivity($activityId)
    {
        $activity = getActivity($activityId);
        $actualUsers = 1 + intval($activity["joinedPlayers"]) + intval($activity[DB_ACTIVITIES_GUESTUSERS]);
        if($actualUsers >= intval($activity[DB_ACTIVITIES_MAXPLAYERS]))
            return false;

        if(intval($activity[DB_ACTIVITIES_STATUS]) !== ActivityStatus::PENDING)
            return false;

        $userId = getLoginParameterFromSession();
        $query = "INSERT INTO ".DB_ACTIVITIES_JOINS_TABLE." (".DB_ACTIVITIES_JOINS_ACTIVITY.",".DB_ACTIVITIES_JOINS_USER.") VALUES (?,?)";
        return dbUpdate($query, "ii", array($activityId, $userId));
    }
    function leaveActivity($activityId)
    {
        $activity = getActivity($activityId);
        switch(intval($activity[DB_ACTIVITIES_STATUS]))
        {
            case ActivityStatus::STARTED:
            case ActivityStatus::CANCELLED:
            case ActivityStatus::DELETED:
                return false;
        }
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
    function getMyActivities($status = NULL, $sport = NULL, $orderBy = DB_ACTIVITIES_STARTTIME, $order = "ASC")
    {
        $userId = getLoginParameterFromSession();
        $query = "SELECT act.*,bike.".DB_BICYCLE_DISTANCE." as bicycle_".DB_BICYCLE_DISTANCE.", bike.".DB_BICYCLE_TRAVELED." as bicycle_".DB_BICYCLE_TRAVELED.", run.".DB_RUNNING_DISTANCE." as running_".DB_RUNNING_DISTANCE.", run.".DB_RUNNING_TRAVELED." as running_".DB_RUNNING_TRAVELED.", run.".DB_RUNNING_FITNESS." as running_".DB_RUNNING_FITNESS.", foot.".DB_FOOTBALL_PLAYERSPERTEAM." as football_".DB_FOOTBALL_PLAYERSPERTEAM.", ten.".DB_TENNIS_DOUBLE." as tennis_".DB_TENNIS_DOUBLE.",(SELECT COUNT(*) FROM ".DB_ACTIVITIES_JOINS_TABLE." WHERE ".DB_ACTIVITIES_JOINS_ACTIVITY." = act.".DB_ACTIVITIES_ID.") as joinedPlayers FROM ".DB_ACTIVITIES_TABLE." AS act".
            " LEFT JOIN ".DB_RUNNING_TABLE." AS run ON (act.".DB_ACTIVITIES_SPORT." = 1 AND act.".DB_ACTIVITIES_ID." = run.".DB_RUNNING_ID.")".
            " LEFT JOIN ".DB_FOOTBALL_TABLE." AS foot ON (act.".DB_ACTIVITIES_SPORT." = 2 AND act.".DB_ACTIVITIES_ID." = foot.".DB_FOOTBALL_ID.")".
            " LEFT JOIN ".DB_BICYCLE_TABLE." AS bike ON (act.".DB_ACTIVITIES_SPORT." = 3 AND act.".DB_ACTIVITIES_ID." = bike.".DB_BICYCLE_ID.")".
            " LEFT JOIN ".DB_TENNIS_TABLE." AS ten ON (act.".DB_ACTIVITIES_SPORT." = 4 AND act.".DB_ACTIVITIES_ID." = ten.".DB_TENNIS_ID.")".
            " WHERE (act.".DB_ACTIVITIES_CREATEDBY." = ? OR act.".DB_ACTIVITIES_ID." IN (SELECT ".DB_ACTIVITIES_JOINS_ACTIVITY." FROM ".DB_ACTIVITIES_JOINS_TABLE." WHERE ".DB_ACTIVITIES_JOINS_USER." = ?))".
            ($status !== NULL ? " AND act.".DB_ACTIVITIES_STATUS." = ?" : "").
            ($sport !== NULL ? " AND act.".DB_ACTIVITIES_SPORT." = ?" : "").
            ($orderBy !==NULL && $order!==NULL ? " ORDER BY act.$orderBy $order" : " ORDER BY act.".DB_ACTIVITIES_STARTTIME." ASC");
        $paramTypes = "ii";
        $parameters = array($userId,$userId);
        if($status!==NULL)
        {
            $paramTypes.="i";
            array_push($parameters, $status);
        }
        if($sport!==NULL)
        {
            $paramTypes.="i";
            array_push($parameters, $sport);
        }
        $activities = dbSelect($query, $paramTypes, $parameters);
        $activities = normalizeActivitiesArray($activities);
        return $activities;
    }
    function normalizeActivitiesArray($activities)
    {
        $normalized = array();
        foreach($activities as $activity)
        {
            $activity = normalizeActivity($activity);
            array_push($normalized, $activity);
        }
        return $normalized;
    }
    function normalizeActivity($activity)
    {
        switch($activity[DB_ACTIVITIES_SPORT])
        {
            case Sports::RUNNING:
                $activity = array_remove_keys_starts($activity, "bicycle_");
                $activity = array_remove_keys_starts($activity, "football_");
                $activity = array_remove_keys_starts($activity, "tennis_");
                $activity = array_rename_keys_starts($activity, "running_");
                break;
            case Sports::FOOTBALL:
                $activity = array_remove_keys_starts($activity, "bicycle_");
                $activity = array_remove_keys_starts($activity, "tennis_");
                $activity = array_remove_keys_starts($activity, "running_");
                $activity = array_rename_keys_starts($activity, "football_");
                break;
            case Sports::BICYCLE:
                $activity = array_remove_keys_starts($activity, "football_");
                $activity = array_remove_keys_starts($activity, "tennis_");
                $activity = array_remove_keys_starts($activity, "running_");
                $activity = array_rename_keys_starts($activity, "bicycle_");
                break;
            case Sports::TENNIS:
                $activity = array_remove_keys_starts($activity, "bicycle_");
                $activity = array_remove_keys_starts($activity, "football_");
                $activity = array_remove_keys_starts($activity, "running_");
                $activity = array_rename_keys_starts($activity, "tennis_");
                break;
        }
        return $activity;
    }
?>