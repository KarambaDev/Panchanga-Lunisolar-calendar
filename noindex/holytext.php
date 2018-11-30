<?php
header('Content-Type: text/javascript; charset=UTF-8');
require_once(dirname(__FILE__) . '/configuration.php');
$language='holy_eng';
if (isset($_GET['lng']) AND isset($_GET['num'])) {
	if ($_GET['lng']=='en') {	$language='holy_eng';	}
	elseif ($_GET['lng']=='ru') {	$language='holy_rus';	}
	
//$arr = array (1,2,3,4,5,6);
	$arr=$_GET['num'];

	$arr_size = count($arr);
	if ($arr_size!=0 AND $arr_size<50) {
		for ($i=0; $i<$arr_size ;$i++) {
			if ($i==0) {$num_string = "id=".(int)$arr[$i];}
			else {$num_string .= " OR id=".(int)$arr[$i];}			
			}
		$mysql_hn = new mysqli($GLOBALS['mysql_server'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password'], $GLOBALS['dbname']);
		$mysql_hn->set_charset("utf8");
		$query = $mysql_hn->query("SELECT * FROM $language WHERE ( $num_string ) LIMIT 50;");
		
		while($result = $query->fetch_array(MYSQLI_ASSOC)) { 
			if ($result['varname']==1) {	
				$query_w = $mysql_hn->query("SELECT * FROM ".$language."_w WHERE id=".$result['id'].";");
				$result_w = $query_w->fetch_array(MYSQLI_ASSOC);
				$result = array_merge($result, $result_w);
			}
			elseif ($result['varname']==2 || $result['varname']==3) {	
				$query_cm = $mysql_hn->query("SELECT * FROM ".$language."_cm WHERE id=".$result['id'].";");
				$result_cm = $query_cm->fetch_array(MYSQLI_ASSOC);
				$result = array_merge($result, $result_cm);
				}
				
			$result_arr[]=$result;
			}
//print_r($result_arr);
		//echo json_encode($result_arr, JSON_FORCE_OBJECT);
		if (isset($_GET['callback'])) echo $_GET['callback'].'('.json_encode($result_arr, JSON_FORCE_OBJECT). ')';
		}
	}
?>