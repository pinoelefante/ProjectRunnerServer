<?php
    define("ANDROID_DEVICE", 1);
    define("IOS_DEVICE", 2);
    define("WIN10_DEVICE", 3);

    require_once(__DIR__."/../configs/app-config.php");
    require_once("logger.php");
    require_once("enums.php");
    require_once("database.php");
    require_once("session_global.php");

    function sendPushNotification($titolo,$corpo,$autore,$id_news,$devices)
    {
        $dev_android = array_filter($devices, function($item) {return $item["deviceOS"]==ANDROID_DEVICE;} );
        $dev_ios = array_filter($devices, function($item) {return $item["deviceOS"]==IOS_DEVICE;} );
        $dev_win10 = array_filter($devices, function($item) {return $item["deviceOS"]==WIN10_DEVICE;} );
        $anteprima = $corpo; //TODO tagliare il corpo della news
        
        sendPush_Android($id_news, $titolo, $anteprima, $autore, GetTokenArray($dev_android));
        sendPush_iOS($titolo, $anteprima, $autore, $id_news, $dev_ios);
        sendPush_Windows($titolo, $anteprima, $autore, $id_news, $dev_win10);
    }
    function GetTokenArray($array)
    {
        $tokens = array();
        foreach($array as $dev)
            array_push($tokens,$dev["token"]);
        return $tokens;
    }
    function elaborateResponseAndroid($response)
    {
        //echo $response;
        LogMessage("GCM response: $response");
    }
    function sendPush_Android($id_news, $titolo, $anteprima, $autore, $devices)
    {
        if(count($devices)==0)
            return;
        // prep the bundle
        $msg = array
        (
            'message'   => $anteprima,
            'title'     => $titolo,
            'id'        => $id_news,
            'author'    => $autore,
            'vibrate'   => 1,
            'sound'     => 1
        );
        $fields = array
        (
            'registration_ids'  => $devices,
            'data'              => $msg
        );
        $headers = array
        (
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        elaborateResponseAndroid($result);
    }
    function sendPush_iOS($titolo, $anteprima, $autore, $id_news, $devices)
    {
        if(count($devices)==0)
            return;
    }
    function sendPush_Windows($titolo, $anteprima, $autore, $id_news, $devices)
    {
        if(count($devices)==0)
            return;
        //https://arjunkr.quora.com/How-to-Windows-10-WNS-Windows-Notification-Service-via-PHP
    }
    function SendNotificationAllUsers($titolo,$testo,$autore,$id_news)
    {
        $registrationIds = GetAllDevices();
        sendPushNotification($titolo, $testo, $autore,$id_news, $registrationIds);
    }
    
    function GetAllDevices()
    {
        $query = "SELECT id_user as user,token,deviceOS FROM push_devices";
        return dbSelect($query);
    }
    function RegistraDevice($token, $device, $deviceId)
	{
		$loginParameter = getLoginParameterFromSession();
		$query = "INSERT INTO push_devices (id_user,token,deviceOS,deviceId) VALUES (?,?,?,?)";
		return dbUpdate($query,"isis",array($loginParameter,$token,$device,$deviceId)) ? StatusCodes::OK : StatusCodes::FAIL;
	}
    function UnRegistraDevice($token, $device,$deviceId)
	{
		$loginParameter = getLoginParameterFromSession();
		$query = "DELETE FROM push_devices WHERE id_user=? AND token=? AND deviceOS=? AND deviceId = ?";
		return dbUpdate($query,"isis",array($loginParameter,$token,$device,$deviceId), DatabaseReturns::RETURN_AFFECTED_ROWS) ? StatusCodes::OK : StatusCodes::FAIL;
	}
?>