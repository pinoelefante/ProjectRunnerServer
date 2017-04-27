<?php
    require_once("connections.php");
    require_once("enums.php");
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
        $endpoint = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&key=".GOOGLEMAPS_API_KEY;
        //TODO parsing
        return sendHTTPRequest($endpoint, NULL, "GET");
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