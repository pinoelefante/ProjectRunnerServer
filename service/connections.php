<?php
    require_once("config.php");
    require_once("enums.php");
    require_once("logger.php");

    $requestId = LogRequest();
    checkUserAgent();

    function getParameter($par,$required = false, $maxLenght = -1)
    {
        $parameter = NULL;
        if(isset($_POST[$par]))
            $parameter = getValue($_POST,$par);
        else if(isset($_GET[$par]))
            $parameter = getValue($_GET,$par);
        if($required && $parameter === NULL)
        {
            sendResponse(StatusCodes::RICHIESTA_MALFORMATA, "$par is required");
            die();
        }
        if($maxLenght > 0 && $parameter!==NULL && strlen($parameter) > $maxLenght)
        {
            sendResponse(StatusCodes::RICHIESTA_MALFORMATA, "$par max lenght is $maxLenght");
            die();
        }
        return $parameter;
    }
    function getValue($array, $key)
    {
        if(isset($array[$key]))
        {
            if(empty($array[$key]))
            {
                if(is_numeric($array[$key]))
                    return 0;
                else
                    return NULL;
            }
            return $array[$key];
        }
        return NULL;
    }
    function getParametersStartingBy($startsBy, $required = false, $keep_startsBy = false)
    {
        $results = array();
        $allKeys = array_merge(array_keys($_POST), array_keys($_GET));
        $substart = $keep_startsBy ? 0 : strlen($startsBy);
        foreach($allKeys as $key)
        {
            if(strpos($key, $startsBy) === 0)
                $results[substr($key, $substart)] = getParameter($key, $required);
        }
        return $results;
    }
    function sendResponse($response, $content = "")
    {
        $array = array('response' => $response, 
                       'time' => date("Y-m-d H:i:s"),
                       'content' => empty($content) ? "" : $content );
        header('Content-Type: application/json');
        $responseJson = json_encode($array);
        LogResponse($responseJson, $GLOBALS['requestId']);
        echo $responseJson;

        if($response<0)
        {
            $debug = GetDebugMessage();
            $corpoMail = "E' stata rilevata una richiesta fallita al server ($response). Ecco la richiesta\n\n<br><br>$debug";
            sendEmailAdmin("[PostApp] Richiesta fallita",$corpoMail);
        }
    }
    function sendHTTPRequest($url, $data = NULL, $method = "POST")
    {
        //$url = 'http://server.com/path';
        //$data = array('key1' => 'value1', 'key2' => 'value2');

        // use key 'http' even if you send the request to https://...
        if($data == NULL)
            $data = array();
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => $method,
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) 
        { 
            /* Handle error */
            return NULL;
        }
        return $result;
    }
    function checkUserAgent()
    {
        if(CHECK_USER_AGENT)
        {
            if($_SERVER['HTTP_USER_AGENT']==CLIENT_USER_AGENT)
                return true;
            sendResponse(StatusCodes::INVALID_CLIENT);
            exit();
        }
        return true;
    }
    function GetUserIP()
    {
        //https://www.cloudflare.com/ips/
        //http://stackoverflow.com/questions/14985518/cloudflare-and-logging-visitor-ip-addresses-via-in-php
        $user_ip = REVERSE_PROXY_ENABLED ? $_SERVER[REVERSE_PROXY_REMOTE_ADDRESS] : $_SERVER['REMOTE_ADDR'];
        return $user_ip;
    }
    function sendEmail($destinatario, $oggetto, $corpo)
    {
        //mail($destinatario, $oggetto, $corpo);
    }
    function sendEmailAdmin($oggetto, $corpo)
    {
        sendEmail(ADMIN_EMAIL, $oggetto, $corpo);
    }
?>