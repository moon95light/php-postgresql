<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '/var/www/html/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

echo "<pre>";


// 1. GET ACCESS TOKEN

$req['grant_type'] = 'authorization_code';
$req['client_id'] = '5ba66fc84fa1946c9b55';
$req['client_secret'] = 'a09ae018e9b562b7add5af8230d7921161fa853e';
$req['code'] = trim($_GET['code']);
//print_r( $req );
$cURLConnection = curl_init('https://tubie.casdoor.com/api/login/oauth/access_token');
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $req);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
$apiResponse = curl_exec($cURLConnection);
curl_close($cURLConnection);
$curl = json_decode($apiResponse, true);
//print_r( $curl );
//echo $curl["access_token"];

// 2. GET UDER ID 



$user_id_curl = curl_init('https://tubie.casdoor.com/api/userinfo');
curl_setopt($user_id_curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $curl["access_token"],]);
curl_setopt($user_id_curl, CURLOPT_RETURNTRANSFER, true);
$user_info_response = curl_exec($user_id_curl);
if ($user_info_response === false) {
    die('Error: ' . curl_error($user_id_curl));
}
$user_info_response = json_decode($user_info_response, true);
$userId = trim((string) $user_info_response["preferred_username"]);
//print_r($user_info_response);
//echo $userId;
//echo "\n";

$data = array(
    'id' => "built-in/$userId",
    'clientId' => $req['client_id'],
    'clientSecret' => $req['client_secret']
);

// 3. GET USER INFO

$url = "https://tubie.casdoor.com/api/get-user?" . http_build_query($data);
//echo "\n";
//echo $url;
//echo "\n";  
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$resp = curl_exec($curl);
curl_close($curl);
//echo $resp;
$user = json_decode($resp, true);
// print_r($user);

// 4. UPSERT USER INFO INTO POSTGRESQL  

// $host = 'postgresql-154883-0.cloudclusters.net';
// $db = 'marketplace';
// $user = 'root';
// $password = 'E3kSs77s1Npem0bR';

// try {
//     $dsn = "pgsql:host=$host;port=19815;dbname=$db;";
//     $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
//     if ($pdo) {
//         //echo "Connected to the $db database successfully!";
//         $data = [
//             'id' => (string) $userId,
//             'info' => (string) $resp,
//         ];
//         $sql = "INSERT INTO users (id, info) VALUES (:id, :info)";
//         $stmt = $pdo->prepare($sql);

//         //setcookie("user", (string) $userId, time() + (86400 * 30), "/");
//         //header("Location: https://marketplace.tube/");
//     }
// } catch (PDOException $e) {
//     die($e->getMessage());
// } finally {
//     if ($pdo) {
//         $pdo = null;
//     }
// }

/**
 * @auth : Mykhailo 
 * @description : PostgreSQL connection and Upsert code 
 */
$host = "host = postgresql-154883-0.cloudclusters.net";
$port = "port = 19815";
$dbname = "dbname = marketplace";
$credentials = "user = root password=E3kSs77s1Npem0bR";

$db = pg_connect("$host $port $dbname $credentials");
if (!$db) {
    echo "Error : Unable to open database\n";
} else {
    //echo "Opened database successfully\n";
}

// Upsert users table..

$userInfo = json_encode($user["data"]);
$userid = (string) $userId;

$upsertQuery = "INSERT INTO users (id, info)
                VALUES ('$userId', '$userInfo')
                ON CONFLICT (id) DO UPDATE SET info = excluded.info";
$result = pg_query($db, $upsertQuery);

if ($result) {
    //echo "Data upserted successfully.\n";
} else {
    echo "Error: Failed to upsert data.\n";
}

setcookie("user", (string) $userId, time() + (86400 * 30), "/");
header("Location: https://marketplace.tube/");


pg_close($db);

// echo 200;
?>