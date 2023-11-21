<?php

/**
 * @Description: Get the user info from Casdoor
 */




/**
 * Connection PostgreSQL
 */
$host = "host = postgresql-154883-0.cloudclusters.net";
$port = "port = 19815";
$dbname = "dbname = marketplace";
$credentials = "user = root password=E3kSs77s1Npem0bR";

$db = pg_connect("$host $port $dbname $credentials");
if (!$db) {
    echo "Error : Unable to open database\n";
} else {
    echo "Opened database successfully\n";
}


/**
 * Upsert the data
 */

$id = 2;

$sql = "INSERT INTO views (id, total)
                VALUES ($id, 0)
                ON CONFLICT (id) DO UPDATE SET total = views.total + 1 RETURNING total";
$result = pg_query($db, $sql);

$row = pg_fetch_assoc($result);

echo ("total =>" . $row['total']);
print_r("\n");
if ($result) {
    echo "Data upserted successfully.\n";
} else {
    echo "Error: Failed to upsert data.\n";
}

$userId = "85";
$mycasdoorId = "sd788-54e4-6465s-df5e55";

/**
 *@  INSERT jsonb data  
 * */ 
$query = "INSERT INTO subscriptions (id, info)
    VALUES ('$mycasdoorId', '[]')
    ON CONFLICT (id) DO UPDATE
    SET info = CASE 
                WHEN subscriptions.info @> JSONB_BUILD_ARRAY('$userId') 
                THEN subscriptions.info 
                ELSE subscriptions.info || JSONB_BUILD_ARRAY('$userId')
              END
    WHERE subscriptions.id = '$mycasdoorId'
    RETURNING *;
";


/**
 * @ description : delete jsonb data
 *   */ 
$query = "UPDATE subscriptions
SET info = info - '$userId'
WHERE subscriptions.id = '$mycasdoorId';";

$result = pg_query($db, $query);


/**
 * @description  : Created at 5 years ago
 */

 function timeAgo($time_ago)
{
    $time_ago = strtotime($time_ago);
    $cur_time   = time();
    $time_elapsed   = $cur_time - $time_ago;
    $seconds    = $time_elapsed ;
    $minutes    = round($time_elapsed / 60 );
    $hours      = round($time_elapsed / 3600);
    $days       = round($time_elapsed / 86400 );
    $weeks      = round($time_elapsed / 604800);
    $months     = round($time_elapsed / 2600640 );
    $years      = round($time_elapsed / 31207680 );
    // Seconds
    if($seconds <= 60){
        return "just now";
    }
    //Minutes
    else if($minutes <=60){
        if($minutes==1){
            return "Created at 1 minute ago";
        }
        else{
            return "Created at $minutes minutes ago";
        }
    }
    //Hours
    else if($hours <=24){
        if($hours==1){
            return "Created at an hour ago";
        }else{
            return "Created at $hours hrs ago";
        }
    }
    //Days
    else if($days <= 7){
        if($days==1){
            return "Created at yesterday";
        }else{
            return "Created at $days days ago";
        }
    }
    //Weeks
    else if($weeks <= 4.3){
        if($weeks==1){
            return "Created at a week ago";
        }else{
            return "Created at $weeks weeks ago";
        }
    }
    //Months
    else if($months <=12){
        if($months==1){
            return "Created at a month ago";
        }else{
            return "Created at $months months ago";
        }
    }
    //Years
    else{
        if($years==1){
            return "Created at 1 year ago";
        }else{
            return "Created at $years years ago";
        }
    }
}