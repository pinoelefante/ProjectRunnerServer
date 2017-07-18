<?php
    require_once(__DIR__."/../configs/app-config.php");
    require_once("enums.php");
    require_once("logger.php");
    
    //ritorna percorso salvataggio immagine
    function SalvaImmagine($immagineBytes, $folder = "images")
    {
        if(empty($immagineBytes))
            return NULL;

        if(empty($folder) || strlen($folder) > 34) //nome file 30 + 34 path
            $folder = "images";
            
        $result = NULL;
        $closed = false;
        if(!empty($immagineBytes))
        {
            @mkdir($folder, 0664, true); // 0664 = lettura/scrittura proprietario&gruppo, lettura utenti
            $filename = GeneraUniqueFileName($folder, "IMG");
            $fp = fopen("./$folder/$filename", "wb");
            if(fwrite($fp, $immagineBytes))
            {
                $closed = fclose($fp);
                $result = "$folder/$filename";
                $ext = GetFileExtension("./$folder/$filename");
                if(IsImage($ext) && rename("./$folder/$filename", "./$folder/$filename$ext"))
                {
                    $result = "$result$ext";
                    $thumbSave = SalvaThumb($folder,$filename, $ext);
                    LogMessage("thumb salvata ($filename$ext): $thumbSave","thumbs.log");
                }
                else //il file non è un'immagine valida
                {
                    if(!unlink("./$result"))
                        sendEmailAdmin("[PostApp] File non valido","E' stato caricato un file che non è un'immagine ma non è stato possibile cancellarlo\n<br>Nome file: $result");
                    $result = NULL;
                }
            }
            if(!$closed)
                fclose($fp);
            
        }
        return $result;
    }
    function SalvaThumb($folder,$filename, $ext)
    {
        switch($ext)
        {
            case ".jpg";
                $image = imagecreatefromjpeg("./$folder/$filename$ext");
                break;
            case ".png":
                $image = imagecreatefrompng("./$folder/$filename$ext");
                break;
            case ".gif":
                $image = imagecreatefromgif("./$folder/$filename$ext");
                break;
            default:
                return false;
        }
        $size = getimagesize("./$folder/$filename$ext");
        $newHeight = (64*$size[1])/$size[0];
        $imageResized = imagescale($image, 64, $newHeight);
        switch($ext)
        {
            case ".jpg";
                return imagejpeg($imageResized, "./$folder/thumb.$filename$ext");
            case ".png":
                return imagepng($imageResized, "./$folder/thumb.$filename$ext");
            case ".gif":
                return imagegif($imageResized, "./$folder/thumb.$filename$ext");
        }
        return false;
    }
    function GetFileExtension($filepath)
    {
        $mime = mime_content_type($filepath);
        switch($mime)
        {
            case "image/jpeg":
                return ".jpg";
            case "image/png":
                return ".png";
            case "image/gif":
                return ".gif";
            /* Non c'è il supporto per la creazione del thumb
            case "image/bmp":
                return ".bmp";
            */
            case "application/pdf":
                return ".pdf";
            /*
            case "":
                return "";
            */
        }
    }
    function IsImage($extension)
    {
        switch($extension)
        {
            case ".jpg":
            case ".png":
            case ".gif":
            case ".bmp":
                return true;
        }
        return false;
    }
    function GeneraUniqueFileName($folder, $prefix = "IMG")
	{
		do 
		{
			$filename = uniqid("IMG", true);
			usleep(2);
		}
        while(file_exists($folder."/".$filename));
		return $filename;
	}
    
    function costTimeHashPassword($timeTarget = 0.05 /*50ms*/)
    {
        if($timeTarget == NULL)
            $timeTarget = 0.05;

        $cost = 5;
        do {
            $cost++;
            $start = microtime(true);
            password_hash("testtest", PASSWORD_BCRYPT, array("cost" => $cost));
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);

        return $cost;
    }
    function GetQRCode($code, $dim = "360x360")
    {
        return "https://chart.googleapis.com/chart?cht=qr&chl=$code&chs=$dim&choe=UTF-8&chld=L|2";
    }
    function GeneraCodice($prefix = "", $appendix = "")
    {
        $code = uniqid($prefix, false).$appendix;
        return $code;
    }
    function array_remove_keys_starts($array, $keyStart)
    {
        $allKeys = array_keys($array);
        foreach($allKeys as $key)
        {
            if(strpos($key, $keyStart) === 0)
                unset($array[$key]);
        }
        return $array;
    }
    function array_rename_keys_starts($array, $keyStart)
    {
        $allKeys = array_keys($array);
        $keyStartLen = strlen($keyStart);
        foreach($allKeys as $key)
        {
            if(strpos($key, $keyStart) === 0)
            {
                $value = $array[$key];
                unset($array[$key]);
                $newKey = substr($key, $keyStartLen);
                $array[$newKey] = $value;
            }
        }
        return $array;
    }
    function array_get_value($array, $key, $default = NULL)
    {
        if(isset($array[$key]))
            return $array[$key];
        return $default;
    }
?>