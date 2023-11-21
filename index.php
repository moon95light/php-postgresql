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
