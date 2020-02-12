<?php
//ob_start("ob_gzhandler");
error_reporting(0);
session_start();

/* DATABASE CONFIGURATION */
// define('DB_SERVER', 'localhost');
// define('DB_USERNAME', 'root');
// define('DB_PASSWORD', 'root');
// define('DB_DATABASE', 'gsd');
// define("BASE_URL", "https://localhost/restapi/api/");
// define("SITE_KEY", 'jobayerMojumder');


/* DATABASE CONFIGURATION */
define('DB_SERVER', '3.134.103.215');
define('DB_USERNAME', 'remote');
define('DB_PASSWORD', 'Remote2019@');
define('DB_DATABASE', 'GDSD_schema');
define("BASE_URL", "http://52.59.232.143/restapi/api/");
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