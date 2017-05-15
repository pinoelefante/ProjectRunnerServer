<?php
    require_once("config.php");
    require_once("connections.php");
    require_once("enums.php");
    require_once("logger.php");
    define("LATLONGRADIUSKM", 0.00899321606);

    //haversine function
    //$earthRadius default is in kilometers
    function GetDistanceFromLatLong($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return ($angle * $earthRadius);
    }
    function GetAddressFromLatLong($lat, $long)
    {
        //$endpoint = "https://maps.googleapis.com/maps/api/geocode/json?latlng=40.7127070,14.5145280&key=AIzaSyDVPJKCj8wPi50f1x3BV_rUrOKRaDI6ZXM";
        $endpoint = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&key=".GOOGLEMAPS_API_KEY;
        $response = sendHTTPRequest($endpoint, NULL, "GET");
        if($response != NULL)
        {
            $json = json_decode($response, true);
            if($json["status"] == "OK")
            {
                $index = _getStreetAddress($json["results"]);
                if($index>=0)
                {
                    $address = array("latitude" => $lat, "longitude" => $long);
                    $comp = $json["results"][$index]["address_components"];
                    foreach($comp as $info)
                    {
                        switch($info["types"][0])
                        {
                            case "street_number":
                                $address["street_number"] = $info["long_name"];
                                break;
                            case "route":
                                $address["route"] = $info["long_name"];
                                break;
                            //administrative_area_level_3    
                            case "locality":
                                $address["city"] = $info["long_name"];
                                break;
                            case "administrative_area_level_2":
                                $address["province"] = $info["short_name"];
                                break;
                            case "administrative_area_level_1":
                                $address["region"] = $info["long_name"];
                                break;
                            case "country":
                                $address["country"] = $info["short_name"];
                                break;
                            case "postal_code":
                                $address["postal_code"] = $info["long_name"];
                                break;
                        }
                    }
                    return $address;
                }
            }
        }
        return false;
    }
    function _getStreetAddress($addressList)
    {
        $index = 0;
        $length = count($addressList);
        for($index = 0; $index<$length; $index++)
        {
            $types = $addressList[$index]["types"];
            foreach($types as $type)
            {
                switch($type)
                {
                    case "street_address":
                    case "route":
                    case "point_of_interest":
                        return $index;
                }
            }
        }
        return -1;
    }
    function GetLatLongFromAddress($address)
    {
        $endpoint = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=".GOOGLEMAPS_API_KEY;
        //TODO parsing
        return sendHTTPRequest($endpoint, NULL, "GET");
    }
    function IsDistanceBetweenPointsLessThan($distance, $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $unit = MapDistance::KILOMETERS)
    {
        $unit = $unit == NULL ? MapDistance::KILOMETERS : $unit;
        $earthRadius = $unit == MapDistance::KILOMETERS ? 6371 : 6371005;
        $dist = GetDistanceFromLatLong($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius);
        return $dist <= $distance;
    }
?>