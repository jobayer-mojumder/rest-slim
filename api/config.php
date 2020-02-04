<?php
//ob_start("ob_gzhandler");
error_reporting(0);
session_start();

/* DATABASE CONFIGURATION */
// define('DB_SERVER', 'database-1.cdatrlmmreql.us-east-1.rds.amazonaws.com');
// define('DB_USERNAME', 'admin');
// define('DB_PASSWORD', '123$123aA');
// define('DB_DATABASE', 'GDSD_schema');
// define("BASE_URL", "https://hs-marketplace.herokuapp.com/demo/api/");
// define("SITE_KEY", 'jobayerMojumder');


/* DATABASE CONFIGURATION */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'gsd');
define("BASE_URL", "http://localhost/restapi/api/");
define("SITE_KEY", 'jobayerMojumder');


function getDB() 
{
	$dbhost=DB_SERVER;
	$dbuser=DB_USERNAME;
	$dbpass=DB_PASSWORD;
	$dbname=DB_DATABASE;
	$dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbConnection->exec("set names utf8");
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbConnection;
}
/* DATABASE CONFIGURATION END */

/* API key encryption */
function apiToken($session_uid)
{
$key=md5(SITE_KEY.$session_uid);
return hash('sha256', $key);
}



?>