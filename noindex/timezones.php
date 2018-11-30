<?php 
header('Content-Type: text/javascript; charset=UTF-8');
date_default_timezone_set($_GET['tz']);
if (isset($_GET['callback']) && isset($_GET['tz'])) echo $_GET['callback'].'('.json_encode(array('tzt'=>date("Z", mktime(6, 0, 0, $_GET['month'], 1, $_GET['year']))/60/60), JSON_FORCE_OBJECT). ')';
?>