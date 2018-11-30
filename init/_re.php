<?php  
/*
// Uninstalation file 
// 1. Destroy DB on MySQL

*/

//phpinfo();
error_reporting(E_ALL);
$start_time = microtime(true);
require_once(dirname(__FILE__) . '/configuration.php');
require_once(dirname(__FILE__) . '/_uninstall.php');
require_once(dirname(__FILE__) . '/_install.php');
?>