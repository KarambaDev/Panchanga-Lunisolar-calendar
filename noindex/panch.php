<?php
header('Content-Type: text/javascript; charset=UTF-8');
require_once(dirname(__FILE__) . '/configuration.php');
//$start_time = microtime(true);
class initial {
	public $year;		// текущий год
	public $month;		// текущий месяц
	public $latitude;		// широта градусы
	public $longitude;		// долгота градусы
	public $height;		// высота над уровнем моря
	public $masa_type;		// тип календаря, нужен для расчета некоторых праздников
	public $test;		// test
}
$initial = new initial;
// проверка введенного региона
//!!! сообщить клиенту с какими входными параметрами был произведен расчет, на случай если была ошибка и были использованы параметры по умолчанию		
if (isset($_GET['tz'])) {	date_default_timezone_set(rawurldecode($_GET['tz']));	}
else	{	date_default_timezone_set('GMT');	$error['tz'] = rawurldecode($_GET['tz']);}
$today = getdate();
if (isset($_GET['y'])) {				// проверка введенного года
	if ((int)$_GET['y'] > 1900 && (int)$_GET['y'] < 2038)	{	$initial->year = (int)$_GET['y'];	}
	else {	$error['year'] = $_GET['y'];}
	}
else {	$error['year'] = null;	}
// проверка введенного месяца
if (isset($_GET['m'])) {
	if ((int)$_GET['m'] >= 1 && (int)$_GET['m'] <= 12)		{	$initial->month = (int)$_GET['m'];	}
	else {	$error['month'] = $today['mon'];	}
	}
else {	$error['month'] = null;	}
// проверка введенных координат
if (isset($_GET['lt']))	{	
	if (abs((int)$_GET['lt']) >= 0 && abs((int)$_GET['lt']) <= 90) {	$initial->latitude = $_GET['lt']; 	}
	else {	$error['lat'] = $_GET['lt'];	}
	}
else {	$error['lat'] = null;	}
if (isset($_GET['lg'])) {
	if (abs((int)$_GET['lg']) >=0 && abs((int)$_GET['lg']) <=180) {	$initial->longitude = $_GET['lg'];	}
	else {	$error['long'] = $_GET['lg'];	}
	}
else {	$error['long'] = null;	}
if (isset($_GET['h'])) {
	if ((int)$_GET['h'] < 0) {	
		$initial->height = 0;
		}
	elseif ((int)$_GET['h'] > 9000) {
		$initial->height = 9000; 
		}
	else {	$initial->height = (int)$_GET['h'];	}
	}
if (isset($_GET['lng'])) {
	if ($_GET['lng']=='en') {	$initial->language=1;	}
	elseif ($_GET['lng']=='ru') {	$initial->language=2;	}
	else {	$initial->language=1;	}
	}
if (isset($_GET['mt'])) {
	if ($_GET['mt']==0) {	$initial->masa_type=0;	}
	elseif ($_GET['mt']==1) {	$initial->masa_type=1;	}
	}
if (isset($_GET['test'])) {	$initial->test=TRUE;	}
if (isset($error)) {
	echo json_encode($error, JSON_FORCE_OBJECT);
	}
else {
//$GLOBALS['start_time'] = microtime(true);
	include_once(dirname(__FILE__) . '/panchfunc.php');
	$panchf = new panchf($initial);
if ($initial->test) {	print_r($panchf);	}
		foreach ($panchf->tithi as $key=>$value) {
			$panchf->tithi[$key]['d']=$value['d']+date('Z',$value['d']);
			}
		foreach ($panchf->days_arr as $key=>$value) {
			if (isset($panchf->days_arr[$key]->sr[0])) {	foreach ($panchf->days_arr[$key]->sr as $k=>$v) {	$panchf->days_arr[$key]->sr[$k]=$v+date('Z',$v);	}}
			if (isset($panchf->days_arr[$key]->ss[0])) {	foreach ($panchf->days_arr[$key]->ss as $k=>$v) {	$panchf->days_arr[$key]->ss[$k]=$v+date('Z',$v);	}}
			if (isset($panchf->days_arr[$key]->mr[0])) {	foreach ($panchf->days_arr[$key]->mr as $k=>$v) {	$panchf->days_arr[$key]->mr[$k]=$v+date('Z',$v);	}}
			if (isset($panchf->days_arr[$key]->ms[0])) {	foreach ($panchf->days_arr[$key]->ms as $k=>$v) {	$panchf->days_arr[$key]->ms[$k]=$v+date('Z',$v);	}}
			if (isset($panchf->days_arr[$key]->na[0])) {	foreach ($panchf->days_arr[$key]->na as $k=>$v) {	if (isset($v['d'])) $panchf->days_arr[$key]->na[$k]['d']=$v['d']+date('Z',$v['d']);	}	}
			//if (isset($panchf->days_arr[$key]->ti[0])) {	foreach ($panchf->days_arr[$key]->ti as $k=>$v) {	if (isset($v['d'])) $panchf->days_arr[$key]->ti[$k]['d']=$v['d']+date('Z',$v['d']);	}	}
			if (isset($panchf->days_arr[$key]->se)) {
				$v=$panchf->days_arr[$key]->se;
				if (isset($v['max'])) $panchf->days_arr[$key]->se['max']=$v['max']+date('Z',$v['max']);
				if (isset($v['p1'])) $panchf->days_arr[$key]->se['p1']=$v['p1']+date('Z',$v['p1']);
				if (isset($v['p2'])) $panchf->days_arr[$key]->se['p2']=$v['p2']+date('Z',$v['p2']);
				if (isset($v['t1'])) $panchf->days_arr[$key]->se['t1']=$v['t1']+date('Z',$v['t1']);
				if (isset($v['t2'])) $panchf->days_arr[$key]->se['t2']=$v['t2']+date('Z',$v['t2']);
				if (isset($v['max_l'])) $panchf->days_arr[$key]->se['max_l']=$v['max_l']+date('Z',$v['max_l']);
				if (isset($v['p1l'])) $panchf->days_arr[$key]->se['p1l']=$v['p1l']+date('Z',$v['p1l']);
				if (isset($v['p2l'])) $panchf->days_arr[$key]->se['p2l']=$v['p2l']+date('Z',$v['p2l']);
				if (isset($v['t1l'])) $panchf->days_arr[$key]->se['t1l']=$v['t1l']+date('Z',$v['t1l']);
				if (isset($v['t2l'])) $panchf->days_arr[$key]->se['t2l']=$v['t2l']+date('Z',$v['t2l']);
				}
			if (isset($panchf->days_arr[$key]->me)) {
				$v=$panchf->days_arr[$key]->me;
				if (isset($v['max'])) $panchf->days_arr[$key]->me['max']=$v['max']+date('Z',$v['max']);
				if (isset($v['p1'])) $panchf->days_arr[$key]->me['p1']=$v['p1']+date('Z',$v['p1']);
				if (isset($v['p2'])) $panchf->days_arr[$key]->me['p2']=$v['p2']+date('Z',$v['p2']);
				if (isset($v['t1'])) $panchf->days_arr[$key]->me['t1']=$v['t1']+date('Z',$v['t1']);
				if (isset($v['t2'])) $panchf->days_arr[$key]->me['t2']=$v['t2']+date('Z',$v['t2']);
				if (isset($v['pu1'])) $panchf->days_arr[$key]->me['pu1']=$v['pu1']+date('Z',$v['pu1']);
				if (isset($v['pu2'])) $panchf->days_arr[$key]->me['pu2']=$v['pu2']+date('Z',$v['pu2']);
				if (isset($v['pul'][0])) $panchf->days_arr[$key]->me['pul'][0]=$v['pul'][0]+date('Z',$v['pul'][0]);
				if (isset($v['pul'][1])) $panchf->days_arr[$key]->me['pul'][1]=$v['pul'][1]+date('Z',$v['pul'][1]);
				if (isset($v['pl'][0])) $panchf->days_arr[$key]->me['pl'][0]=$v['pl'][0]+date('Z',$v['pl'][0]);
				if (isset($v['pl'][1])) $panchf->days_arr[$key]->me['pl'][1]=$v['pl'][1]+date('Z',$v['pl'][1]);
				if (isset($v['tl'][0])) $panchf->days_arr[$key]->me['tl'][0]=$v['tl'][0]+date('Z',$v['tl'][0]);
				if (isset($v['tl'][1])) $panchf->days_arr[$key]->me['tl'][1]=$v['tl'][1]+date('Z',$v['tl'][1]);
				}
			}
	require_once(dirname(__FILE__) . '/holydays.php');
	$holy = new holydays($panchf, $initial);

	foreach ($panchf->days_arr as $key=>$value) {
		if ($key < 1 OR $key > $panchf->day_count) {	
			unset($panchf->days_arr[$key]);	//исключение дней за пределами месяца
			continue;
			}
		if ((!isset($panchf->days_arr[$key]->sr) OR !isset($panchf->days_arr[$key]->ss)) AND ($initial->latitude>66.555 OR $initial->latitude<-66.555)) {
			$panchf->days_arr['warn_arctic']=true;
			}
		}
	if (isset($holy->holy)) $panchf->days_arr['holy'] = &$holy->holy;
	
	$panchf->days_arr['init'] = $initial;
	if (isset($temp_n)) $panchf->days_arr['holy_id'] = $temp_n;
	if ($initial->test) {
		print_r($panchf->days_arr);
		printf ("Время выполнения: %f сек.", $GLOBALS['exec_time']);
		$mysql_link_exec = new mysqli($GLOBALS['mysql_server'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password'], $GLOBALS['dbname']);
		$query = "INSERT INTO exec_time VALUES (now(), ".$GLOBALS['exec_time'].");";
		if (!$mysql_link_exec->query($query))
			{
			die("Could not : " . mysqli_connect_error(). '</br>');
			}
		}
	else {	if (isset($_GET['callback'])) echo $_GET['callback'].'('.json_encode($panchf->days_arr, JSON_FORCE_OBJECT). ')';	}
	
	}
//print_r($holy);
//print_r($panchf->days_arr);
//$holydays = &$holy;
//print_r($panchf);
//require_once(dirname(__FILE__) . '/output.php');
//echo json_encode($holy);
//echo memory_get_usage();
?>