<?php
if (isset($_GET['lang'])) {
	if ($_GET['lang']=='ru' OR $_GET['lang']=='RU') {
		require_once(dirname(__FILE__) . '/language_ru.php');
		}
	elseif ($_GET['lang']=='en' OR $_GET['lang']=='EN') {
		require_once(dirname(__FILE__) . '/language_en.php');
		}
	else {	require_once(dirname(__FILE__) . '/language_ru.php');}
	}
elseif (isset($set_language)) {
	if ($set_language=='ru') require_once(dirname(__FILE__) . '/language_ru.php');
	else require_once(dirname(__FILE__) . '/language_en.php');
	}
else {	require_once(dirname(__FILE__) . '/language_en.php');	}
$lng = json_encode($array, JSON_FORCE_OBJECT);
if (isset($_GET['callback'])) {
	header('Content-Type: text/javascript; charset=UTF-8');
	$callback = $_GET['callback'];
	echo $callback.'('.$lng. ')';
	}
elseif (isset($set_language)) {	
	echo "var lng = $lng;\n";
	}
?>