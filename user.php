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
        case "ModifyField":
			$field = getParameter("field", true);
			$value = getParameter("newValue", true);
			$responseCode = modifyField($field, $value);
			break;
        case "SaveOptions":
			/*
			$locationId = getParameter("", true);
			*/
			break;
		case "RequestImageUpload":
			$checksum = getParameter("checksum", true);
			$ext = getParameter("ext", true); //.jpg .png
			$type = getParameter("type", true);
			$albumId = getParameter("album", $type == UploadImageType::ALBUM ? true : false);
			$con = RequestImageUpload($checksum, $ext, $type);
			if(is_array($con))
			{
				$responseCode = StatusCodes::OK;
				$responseContent = $con;
			}
			else 
				$responseCode = StatusCodes::FAIL;
			break;
		case "UploadImage":
			$id = getParameter(DB_IMAGE_UPLOAD_ID, true);
			$request = getParameter(DB_IMAGE_UPLOAD_REQUEST, true);
			$file = getParameter("content", true);
			$description = getParameter("description");
			$responseCode = SaveImage($id,$request, $file, $description) ? StatusCodes::OK : StatusCodes::FAIL;
			break;
		default:
			break;
    }
    sendResponse($responseCode, $responseContent);

    function modifyField($field, $value)
	{
		$userId = getLoginParameterFromSession();
		$query = "UPDATE ".DB_USERS_TABLE." SET $field = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "si", array($value, $userId)) ? StatusCodes::OK : StatusCodes::FAIL;
	}
    function SaveOptions($defaultLocation, $timezone, $notifyNearbyActivities)
	{
		$userId = getLoginParameterFromSession();
		$query = "UPDATE ".DB_USERS_TABLE." SET ".DB_USERS_LOCATION_ID." = ?, ".DB_USERS_TIMEZONE." = ?, ".DB_USERS_NOTIFY_NEARBY." = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "isii", array($defaultLocation,$timezone,$notifyNearbyActivities,$userId));
	}
	function RequestImageUpload($checksum, $ext, $type = "profile", $albumId = null)
	{
		$userId = getLoginParameterFromSession();
		$request = hash("md5","".microtime(true));
		$path = "./images/users/$userId/$type";
		@mkdir($path, "0664", true);
		$filename = GenerateUniqueFilename($path,"user", $ext);
		$query = "INSERT INTO ".DB_IMAGE_UPLOAD_TABLE." (".DB_IMAGE_UPLOAD_REQUEST.",".DB_IMAGE_UPLOAD_USER.",".DB_IMAGE_UPLOAD_CHECKSUM.",".DB_IMAGE_UPLOAD_TYPE.",".DB_IMAGE_UPLOAD_FILENAME.",".DB_IMAGE_UPLOAD_ALBUM.") VALUES (?,?,?,?,?,?)";
		if(($rowId = dbUpdate($query, "sisssi", array($request, $userId, $checksum, $type, $filename,$albumId), DatabaseReturns::RETURN_INSERT_ID)) > 0)
			return array(DB_IMAGE_UPLOAD_REQUEST => $request, DB_IMAGE_UPLOAD_ID => $rowId);
		return false;
	}
	function GetImageUploadRequest($requestId, $requestHash)
	{
		$userId = getLoginParameterFromSession();
		$query = "SELECT * FROM ".DB_IMAGE_UPLOAD_TABLE." WHERE ".DB_IMAGE_UPLOAD_ID." = ? AND ".DB_IMAGE_UPLOAD_REQUEST." = ? AND ".DB_IMAGE_UPLOAD_USER." = ?";
		return dbSelect($query, "isi", array($requestId, $requestHash, $userId), true);
	}
	function SaveImage($requestId, $requestHash, $fileContent, $description = null)
	{
		$userId = getLoginParameterFromSession();
		$request = GetImageUploadRequest($requestId, $requestHash);
		DeleteImageUploadRequest($requestId);
		if(is_array($request) && count($request) > 0)
		{
			if(CHECKSUM_FILE_ENABLED)
			{
				$contentMd5 = hash("md5", $fileContent);
				if(strcmp($contentMd5, $request[DB_IMAGE_UPLOAD_CHECKSUM])!=0)
					return false;
			}
			$folder = "./images/users/$userId/".$request[DB_IMAGE_UPLOAD_TYPE];
			@mkdir($folder, 0664, true);
			$filepath = "./$folder/".$request[DB_IMAGE_UPLOAD_FILENAME];
			$fp = fopen($filepath, "wb");
			$writeOk = fwrite($fp, $fileContent);
			fclose($fp);
            if($writeOk)
			{
				switch($request[DB_IMAGE_UPLOAD_TYPE])
				{
					case UploadImageType::PROFILE:
						return UpdateProfileImage($request[DB_IMAGE_UPLOAD_FILENAME]);
					case UploadImageType::ALBUM:
						return UpdateAlbum($request[DB_IMAGE_UPLOAD_FILENAME], $request[DB_IMAGE_UPLOAD_ALBUM], $description);
				}
			}
			else 
				unlink($filepath);
		}
		return false;
	}
	function DeleteImageUploadRequest($requestId)
	{
		$userId = getLoginParameterFromSession();
		$query = "DELETE FROM ".DB_IMAGE_UPLOAD_TABLE." WHERE ".DB_IMAGE_UPLOAD_ID." = ? AND ".DB_IMAGE_UPLOAD_USER." = ?";
		return dbUpdate($query, "ii", array($requestId,$userId));
	}
	function UpdateProfileImage($filename)
	{
		$userId = getLoginParameterFromSession();
		$query = "UPDATE ".DB_USERS_TABLE." SET ".DB_USERS_IMAGE." = ? WHERE ".DB_USERS_ID." = ?";
		return dbUpdate($query, "si", array($filename,$userId));
	}
	function UpdateAlbum($filename, $albumId, $description)
	{
		$query = "INSERT INTO ".DB_ALBUM_PICTURES_TABLE." (".DB_ALBUM_PICTURES_ALBUM.",".DB_ALBUM_PICTURES_PICTURE.",".DB_ALBUM_PICTURES_DESCRIPTION.") VALUES (?,?,?)";
		return dbUpdate($query, "iss", array($albumId, $filename,$description));
	}
?>