<?php
    define('DB_USERS_TABLE', 'users');
    define('DB_USERS_ID', 'id');
    define('DB_USERS_USERNAME', 'username');
    define('DB_USERS_PASSWORD', 'password');
    define('DB_USERS_FIRSTNAME', 'firstName');
    define('DB_USERS_LASTNAME', 'lastName');
    define('DB_USERS_EMAIL', 'email');
    define('DB_USERS_BIRTH', 'birth');
    define('DB_USERS_PHONE', 'phone');
    define('DB_USERS_REGISTRATION', 'registration');
    define('DB_USERS_LASTUPDATE', 'lastUpdate');
    define("DB_USERS_TIMEZONE","timezone");
    define("DB_USERS_NOTIFY_NEARBY","notifyNearbyActivities");
    define("DB_USERS_LOCATION_ID","defaultLocation");
    
    define("DB_ACTIVITIES_JOINS_TABLE","activities_joins");
    define("DB_ACTIVITIES_JOINS_ACTIVITY","id_activity");
    define("DB_ACTIVITIES_JOINS_USER","id_user");

    define("DB_ACTIVITIES_TABLE", "activities");
    define("DB_ACTIVITIES_ID", "id");
    define("DB_ACTIVITIES_CREATEDBY", "createdBy");
    define("DB_ACTIVITIES_STARTTIME", "startTime");
    define("DB_ACTIVITIES_MEETINGPOINT", "meetingPoint");
    define("DB_ACTIVITIES_GUESTUSERS", "guestUsers");
    define("DB_ACTIVITIES_MAXPLAYERS", "maxPlayers");
    define("DB_ACTIVITIES_STATUS", "status");
    define("DB_ACTIVITIES_SPORT", "sport");
    define("DB_ACTIVITIES_FEE", "fee");
    define("DB_ACTIVITIES_CURRENCY", "currency");
    define("DB_ACTIVITIES_FEEDBACK", "requiredFeedback");
    define("DB_ACTIVITIES_ORGANIZERMODE", "isOrganizerMode");
    define("DB_ACTIVITIES_PRIVATE", "isPrivate");

    define("DB_ACTIVITIES_CHAT_TABLE", "activities_chat");
    define("DB_ACTIVITIES_CHAT_ACTIVITY", "id_activity");
    define("DB_ACTIVITIES_CHAT_USER", "id_user");
    define("DB_ACTIVITIES_CHAT_MESSAGE", "message");
    define("DB_ACTIVITIES_CHAT_TIMESTAMP", "timestamp");

    define("DB_FOOTBALL_TABLE", "activities_football");
    define("DB_FOOTBALL_ID", "id_activity");
    define("DB_FOOTBALL_PLAYERSPERTEAM", "playersPerTeam");

    define("DB_RUNNING_TABLE", "activities_running");
    define("DB_RUNNING_ID", "id_activity");
    define("DB_RUNNING_DISTANCE", "distance");
    define("DB_RUNNING_TRAVELED", "traveled");
    define("DB_RUNNING_FITNESS", "fitness");

    define("DB_TENNIS_TABLE", "activities_tennis");
    define("DB_TENNIS_ID", "id_activity");
    define("DB_TENNIS_DOUBLE", "isDouble");

    define("DB_BICYCLE_TABLE", "activities_bicycle");
    define("DB_BICYCLE_ID", "id_activity");
    define("DB_BICYCLE_DISTANCE", "distance");
    define("DB_BICYCLE_TRAVELED", "traveled");

    define ("DB_FRIEND_TABLE", "users_friend");
    define ("DB_FRIEND_USER", "user_id");
    define ("DB_FRIEND_FRIEND", "friend_id");

    define ("DB_FRIEND_REQUEST_TABLE", "users_friend_request");
    define ("DB_FRIEND_REQUEST_USER", "user_id");
    define ("DB_FRIEND_REQUEST_FRIEND", "friend_id");

    define("DB_ADDRESS_TABLE", "addresses");
    define("DB_ADDRESS_ID", "id");
    define("DB_ADDRESS_NAME", "name");
    define("DB_ADDRESS_LATITUDE", "latitude");
    define("DB_ADDRESS_LONGITUDE", "longitude");
    define("DB_ADDRESS_ROUTE", "route");
    define("DB_ADDRESS_STREETNUMBER", "street_number");
    define("DB_ADDRESS_CITY", "city");
    define("DB_ADDRESS_REGION", "region");
    define("DB_ADDRESS_PROVINCE", "province");
    define("DB_ADDRESS_POSTALCODE", "postal_code");
    define("DB_ADDRESS_COUNTRY", "country");
    define("DB_ADDRESS_CREATEDBY", "createdBy");
    define("DB_ADDRESS_ACTIVE", "active");

    define("DB_PUSH_TABLE", "push_devices");
    define("DB_PUSH_USER","id_user");
    define("DB_PUSH_TOKEN","token");
    define("DB_PUSH_DEVICEOS","deviceOS");
    define("DB_PUSH_DEVICEID","deviceId");
?>
