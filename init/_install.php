<?php  
/*
// Instalation file for new hosting
// 1. Create DB on MySQL

*/

//phpinfo();
error_reporting(E_ALL);
$start_time = microtime(true);
require_once(dirname(__FILE__) . '/../noindex/configuration.php');

$mysql_link = new mysqli($mysql_server, $mysql_user, $mysql_password);
if (mysqli_connect_errno()) {
    die("Connect failed: %s\n". mysqli_connect_error().'</br>');
}
else { 
	echo 'Connected successfully</br>';
	if (!$mysql_link->query('CREATE DATABASE '.$dbname. ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci')) {
		die("Could not create DB".$dbname.' '. mysqli_connect_error(). '</br>');
		}
	else {
		echo "Database ".$dbname." was successfully created</br>";
		$mysql_link->select_db($dbname);
		if (!$mysql_link->query('CREATE TABLE tithi (d INT, p TINYINT, n TINYINT, month TINYINT, year SMALLINT, am TINYINT, pm TINYINT, l TINYINT, ay SMALLINT, py SMALLINT, PRIMARY KEY (d))'))
			{
			die("Could not create table <b>tithi</b>: " . mysqli_connect_error(). '</br>');
			}
		else {
			echo "Table <b>tithi</b> was successfully created</br>";
			}
			
		if (!$mysql_link->query('CREATE TABLE nakshatra (date INT, nnakshatra TINYINT, month TINYINT, year SMALLINT, PRIMARY KEY (date))'))
			{
			die("Could not create table <b>nakshatra</b>: " . mysqli_connect_error(). '</br>');
			}
		else {
			echo "Table <b>nakshatra</b> was successfully created</br>";
			}
			
		if (!$mysql_link->query('CREATE TABLE souramasa (date INT, number TINYINT, month TINYINT, year SMALLINT, PRIMARY KEY (date))'))
			{
			die("Could not create table <b>souramasa</b>: " . mysqli_connect_error(). '</br>');
			}
		else {
			echo "Table <b>souramasa</b> was successfully created</br>";
			}
		}
	}
$mysql_link->close();
require_once(dirname(__FILE__) . '/holyadd.php');
require_once(dirname(__FILE__) . '/useradd.php');


$exec_time = microtime(true) - $start_time;
printf ("Время выполнения: %f сек.", $exec_time);
?>