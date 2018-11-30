<?php  
/*
// Uninstalation file 
// 1. Destroy DB on MySQL

*/

//phpinfo();
error_reporting(E_ALL);
$start_time = microtime(true);
require_once(dirname(__FILE__) . '/configuration.php');

$mysql_link = mysql_connect($mysql_server, $mysql_user, $mysql_password);
if (!$mysql_link) {
    die('Could not connect: ' . mysql_error(). '</br>');
}
echo 'Connected successfully</br>';

//$mysql_db_drop = mysql_query('DROP DATABASE '.$dbname, $mysql_link);
if (!mysql_query('DROP DATABASE '.$dbname, $mysql_link)) {
    die("Could not drop DB $dbname: " . mysql_error(). '</br>');
}
echo "Database $dbname was successfully dropped</br>";

mysql_close($mysql_link);



$exec_time = microtime(true) - $start_time;
printf ("Время выполнения: %f сек.", $exec_time);
?>