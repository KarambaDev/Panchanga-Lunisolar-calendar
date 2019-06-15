<?php
require_once(dirname(__FILE__) . '/../noindex/configuration.php');
$mysql_link = new mysqli($GLOBALS['mysql_server'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password'], $GLOBALS['dbname']);
if (mysqli_connect_errno()) {
    die("Connect failed: %s\n". mysqli_connect_error().'</br>');
	}
else { 
	echo 'Connected successfully</br>';
	$mysql_link->set_charset("utf8");
	if (!$mysql_link->query('CREATE TABLE users (owner_id INT, name TEXT, PRIMARY KEY (owner_id))'))
		{
		die("Could not create table <b>users</b>: " . mysqli_connect_error(). '</br>');
		}
	else {
		echo "Table <b>users</b> was successfully created</br>";
/*
INSERT INTO users VALUES (0, 'share');
owner_id - 0 = default id
name - ��� 
*/
		$mysql_link->query("INSERT INTO users VALUES (0, 'share');");
		}
	}
	
$mysql_link->close();
?>