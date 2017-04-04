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
    
    define("DB_ACTIVITIES_JOINS_TABLE","activities_joins");
    define("DB_ACTIVITIES_JOINS_ACTIVITY","id_activity");
    define("DB_ACTIVITIES_JOINS_USER","id_user");

    define("DB_ACTIVITIES_TABLE", "activities");
    define("DB_ACTIVITIES_ID", "id");
    define("DB_ACTIVITIES_CREATEDBY", "createdBy");
    define("DB_ACTIVITIES_STARTTIME", "startTime");
    define("DB_ACTIVITIES_MPLONG", "meetingPointLongitude");
    define("DB_ACTIVITIES_MPLAT", "meetingPointLatitude");
    define("DB_ACTIVITIES_MPADDR", "meetingPointAddress");
    define("DB_ACTIVITIES_GUESTUSERS", "guestUsers");
    define("DB_ACTIVITIES_REQUIREDPLAYERS", "requiredPlayers");
    define("DB_ACTIVITIES_STATUS", "status");
    define("DB_ACTIVITIES_SPORT", "sport");
    define("DB_ACTIVITIES_FEE", "fee");
    define("DB_ACTIVITIES_FEEDBACK", "requiredFeedback");

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
?>
