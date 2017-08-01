<?php
	require_once("./configs/app-config.php");
    require_once("./configs/database_tables.php");
	require_once("./service/connections.php");
	require_once("./service/database.php");
    require_once("./service/enums.php");
    require_once("./service/functions.php");
	require_once("./service/push_notifications.php");
	require_once("./service/session_global.php");
    
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
        case "RemoveFriendshipRequest":
            $friendId = getParameter(DB_FRIEND_REQUEST_FRIEND);
            $responseCode = DeleteFriendshipRequest(getLoginParameterFromSession(), $friendId) ? StatusCodes::OK : StatusCodes::FAIL;
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
        case "GetProfileInfo":
            $userId = getParameter(DB_USERS_ID, true);
			$responseContent = GetProfileInfo($userId);
			$responseCode = $responseContent != null ? StatusCodes::OK : StatusCodes::FAIL;
			break;
        case "SearchFriends":
            $search = getParameter("search", true);
            if(strlen(trim($search))>=3)
            {
                $responseContent = SearchFriends($search);
                $responseCode = StatusCodes::OK;
            }
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
        $query = "SELECT u.username, uf.friend_id as ".DB_USERS_ID.", ".FriendshipStatus::IS_FRIEND." as status FROM users_friend as uf JOIN users as u ON u.id = uf.friend_id WHERE user_id = ?".
                 " UNION ". 
                 "SELECT u.username, uf.friend_id as ".DB_USERS_ID.", ".FriendshipStatus::REQUESTED." as status FROM users_friend_request as uf JOIN users as u ON u.id = uf.friend_id WHERE user_id = ?".
                 " UNION ".
                 "SELECT u.username, uf.user_id as ".DB_USERS_ID.", ".FriendshipStatus::RECEIVED." as status FROM users_friend_request as uf JOIN users as u ON u.id = uf.user_id WHERE friend_id = ?";
        return dbSelect($query, "iii", array($userId,$userId,$userId));
    }
    function GetProfileInfo($userId)
	{
        $currentUser = getLoginParameterFromSession();
		$query = "SELECT ".DB_USERS_ID.",".DB_USERS_USERNAME.",".DB_USERS_FIRSTNAME.",".DB_USERS_LASTNAME.",".DB_USERS_EMAIL.",".DB_USERS_BIRTH.",".DB_USERS_PHONE.",".DB_USERS_SEX.
        ", (SELECT COUNT(*) FROM ".DB_FRIEND_TABLE." WHERE ".DB_FRIEND_USER." = ?) as friendsCount".
        ", (SELECT COUNT(*) FROM ".DB_FRIEND_TABLE." WHERE ".DB_FRIEND_USER." = ? AND ".DB_FRIEND_FRIEND." = ? ) as isFriend".
        ", (SELECT COUNT(*) FROM ".DB_FRIEND_REQUEST_TABLE." WHERE ".DB_FRIEND_REQUEST_USER." = ? AND ".DB_FRIEND_REQUEST_FRIEND." = ?) as friendRequest".
        ", (SELECT COUNT(*) FROM ".DB_FRIEND_REQUEST_TABLE." WHERE ".DB_FRIEND_REQUEST_USER." = ? AND ".DB_FRIEND_REQUEST_FRIEND." = ?) as friendReceived".
        " FROM ".DB_USERS_TABLE." WHERE ".DB_USERS_ID." = ?";
		$res = dbSelect($query,"iiiiiiii",array($userId,$currentUser,$userId,$currentUser,$userId,$userId,$currentUser, $userId), true);
        
        if($userId == $currentUser)
            $status = FriendshipStatus::USER_ACCOUNT;
        else if($res["isFriend"])
            $status = FriendshipStatus::IS_FRIEND;
        else if($res["friendRequest"])
            $status = FriendshipStatus::REQUESTED;
        else if($res["friendReceived"])
            $status = FriendshipStatus::RECEIVED;
        else 
            $status = FriendshipStatus::STRANGER;
        
        $res = array_remove_keys_starts($res, "isFriend");
        $res = array_remove_keys_starts($res, "friendRequest");
        $res = array_remove_keys_starts($res, "friendReceived");

        $res["status"] = $status;

        return $res;
	}
    function SearchFriends($search)
    {
        $userId = getLoginParameterFromSession();
        $query = "SELECT ".DB_USERS_ID.",".DB_USERS_USERNAME." FROM ".DB_USERS_TABLE." WHERE ".DB_USERS_USERNAME." LIKE ? AND ".DB_USERS_ID." != ?";
        $s = "%$search%";
        return dbSelect($query, "si", array($s, $userId));
    }
?>