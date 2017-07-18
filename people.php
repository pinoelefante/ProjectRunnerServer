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
		case "RequestFriendship":
            $friendId = getParameter(DB_FRIEND_REQUEST_FRIEND, true);
            $responseCode = RequestFriendship($friendId) ? StatusCodes::OK : StatusCodes::FAIL;
            break;
        case "AcceptFriendship":
            $friendId = getParameter(DB_FRIEND_REQUEST_FRIEND, true);
            $responseCode = AcceptFriendship($friendId) ? StatusCodes::OK : StatusCodes::FAIL;
            break;
        case "RejectFriendship":
            $friendId = getParameter(DB_FRIEND_REQUEST_FRIEND, true);
            $responseCode = RejectFriendship($friendId) ? StatusCodes::OK : StatusCodes::FAIL;
            break;
        case "RemoveFriend":
            $friendId = getParameter(DB_FRIEND_FRIEND, true);
            $responseCode = RemoveFriend($friendId) ? StatusCodes::OK : StatusCodes::FAIL;
            break;
        case "IsFriend":
            $friendId = getParameter(DB_FRIEND_FRIEND, true);
            $responseContent = IsFriend($friendId) ? "true" : "false";
            $responseCode = StatusCodes::OK;
            break;
        case "FriendList":
            $responseCode = StatusCodes::OK;
            $responseContent = FriendList();
            break;
        case "FriendshipRequested":
            $responseCode = StatusCodes::OK;
            $responseContent = FriendshipRequested();
            break;
        case "FriendshipReceived":
            $responseCode = StatusCodes::OK;
            $responseContent = FriendshipReceived();
            break;
        default:
            $responseCode = StatusCodes::METODO_ASSENTE;
            break;
    }
    sendResponse($responseCode, $responseContent);

    function RequestFriendship($friendId)
    {
        $userId = getLoginParameterFromSession();
        if($userId == $friendId || IsFriend($friendId))
            return false;
        $query = "INSERT INTO ".DB_FRIEND_REQUEST_TABLE." (".DB_FRIEND_REQUEST_USER.",".DB_FRIEND_REQUEST_FRIEND.") VALUES (?,?)";
        return dbUpdate($query, "ii", array($userId,$friendId));
    }
    function AcceptFriendship($requestBy)
    {
        $userId = getLoginParameterFromSession();
        if(HasFriendshipRequestFrom($requestBy))
        {
            $query = "INSERT INTO ".DB_FRIEND_TABLE." (".DB_FRIEND_USER.",".DB_FRIEND_FRIEND.") VALUES (?,?), (?,?)";
            DeleteFriendshipRequest($requestBy, $userId);
            return dbUpdate($query, "iiii",array($userId,$requestBy,$requestBy,$userId));
        }
        return false;
    }
    function HasFriendshipRequestFrom($requestBy)
    {
        $userId = getLoginParameterFromSession();
        $query = "SELECT COUNT(*) AS isFriend FROM ".DB_FRIEND_REQUEST_TABLE." WHERE ".DB_FRIEND_REQUEST_USER." = ? AND ".DB_FRIEND_REQUEST_FRIEND." = ?";
        $var = dbSelect($query, "ii", array($requestBy, $userId), true);
        return $var["isFriend"]==1;
    }
    function RejectFriendship($requestBy)
    {
        $userId = getLoginParameterFromSession();
        return DeleteFriendshipRequest($requestBy, $userId);
    }
    function DeleteFriendshipRequest($userId, $friendId)
    {
        $query = "DELETE FROM ".DB_FRIEND_REQUEST_TABLE." WHERE (".DB_FRIEND_REQUEST_USER." = ? AND ".DB_FRIEND_REQUEST_FRIEND." = ?) OR (".DB_FRIEND_REQUEST_USER." = ? AND ".DB_FRIEND_REQUEST_FRIEND." = ?)";
        return dbUpdate($query, "iiii", array($userId,$friendId,$friendId,$userId));
    }
    function RemoveFriend($friendId)
    {
        $userId = getLoginParameterFromSession();
        $query = "DELETE FROM ".DB_FRIEND_TABLE." WHERE (".DB_FRIEND_USER." = ? AND ".DB_FRIEND_FRIEND." = ?) OR (".DB_FRIEND_USER." = ? AND ".DB_FRIEND_FRIEND." = ?)";
        return dbUpdate($query, "iiii", array($userId,$friendId,$friendId,$userId));
    }
    function IsFriend($friendId)
    {
        $userId = getLoginParameterFromSession();
        $query = "SELECT COUNT(*) AS isFriend FROM ".DB_FRIEND_TABLE." WHERE ".DB_FRIEND_USER." = ? AND ".DB_FRIEND_FRIEND." = ?";
        $var = dbSelect($query, "ii", array($userId, $friendId), true);
        return $var["isFriend"]==1;
    }
    function FriendList()
    {
        $userId = getLoginParameterFromSession();
        $query = "SELECT u.".DB_USERS_ID.",u.".DB_USERS_USERNAME.",u.".DB_USERS_FIRSTNAME.",u.".DB_USERS_LASTNAME.",u.".DB_USERS_EMAIL.",u.".DB_USERS_BIRTH.",u.".DB_USERS_PHONE.",u.".DB_USERS_REGISTRATION.",u.".DB_USERS_LASTUPDATE." FROM ".DB_FRIEND_TABLE." AS f JOIN ".DB_USERS_TABLE." AS u ON f.".DB_FRIEND_FRIEND." = u.".DB_USERS_ID." WHERE f.".DB_FRIEND_USER." = ?";
        return dbSelect($query, "i", array($userId));
    }
    function FriendshipReceived()
    {
        $userId = getLoginParameterFromSession();
        $query = "SELECT u.".DB_USERS_ID.",u.".DB_USERS_USERNAME.",u.".DB_USERS_FIRSTNAME.",u.".DB_USERS_LASTNAME.",u.".DB_USERS_EMAIL.",u.".DB_USERS_BIRTH.",u.".DB_USERS_PHONE.",u.".DB_USERS_REGISTRATION.",u.".DB_USERS_LASTUPDATE." FROM ".DB_FRIEND_REQUEST_TABLE." AS f JOIN ".DB_USERS_TABLE." AS u ON f.".DB_FRIEND_REQUEST_USER." = u.".DB_USERS_ID." WHERE f.".DB_FRIEND_REQUEST_FRIEND." = ?";
        return dbSelect($query, "i", array($userId));
    }
    function FriendshipRequested()
    {
        $userId = getLoginParameterFromSession();
        $query = "SELECT u.".DB_USERS_ID.",u.".DB_USERS_USERNAME.",u.".DB_USERS_FIRSTNAME.",u.".DB_USERS_LASTNAME.",u.".DB_USERS_EMAIL.",u.".DB_USERS_BIRTH.",u.".DB_USERS_PHONE.",u.".DB_USERS_REGISTRATION.",u.".DB_USERS_LASTUPDATE." FROM ".DB_FRIEND_REQUEST_TABLE." AS f JOIN ".DB_USERS_TABLE." AS u ON f.".DB_FRIEND_REQUEST_FRIEND." = u.".DB_USERS_ID." WHERE f.".DB_FRIEND_REQUEST_USER." = ?";
        return dbSelect($query, "i", array($userId));
    }
?>