<?php
require_once(dirname(__FILE__) . '/configuration.php');
class panchf { // class содержит все дни месяца и инициирует всю последовательность расчетов
	
	private $year;		// текущий год
	private $month;		// текущий месяц
	private $latitude;		// широта градусы
	private $longitude;		// долгота градусы
	private $height;		// высота над уровнем моря
	
//	public $masa_arr;	// хранит номера masa попавших в этот месяц
	public $days_arr;
	public $sunrise;
	public $sunset;
	public $moonrise;
	public $moonset;
	public $tithi;
	
	public $fix_date;	// массив из всех fix_date от -2 до +2
	public $fix_date_m;	// предыдущий месяц
	public $fix_date_mm;	// предпредыдущий месяц
	public $fix_date_p;	// следующий месяц
	public $fix_date_pp;	// послеследующий месяц
	public $day_count; // сколько дней в месяце (надо +2)
	public $first_day; // Порядковый номер в году первого дня текущего месяца
	private $main_month_year;
	
/*
Сокращения:
	tithi:
			d - date
			p - paksha
			n - номер tithi
			am - amavasya masa
			pm - purnimant masa
			l - leap
			ay - amavasya Hindu year
			py - purnimant Hindu year

*/	

	public function panchf(&$initial) { // запускает создание и наполнение всех полей days_arr
		
		$this->year = $initial->year;
		$this->month = $initial->month;
		$this->latitude = $initial->latitude;
		$this->longitude = $initial->longitude; 
		$this->height = $initial->height;
		$this->test = $initial->test;
		
		$this->first_day = date("z", gmmktime(12, 0, 0, $this->month, 1, $this->year));
		$this->main_month_year = $this->year*100+$this->month;
		$this->day_count = $this->day_count_foo($this->month, $this->year);
		
		$this->fix_date[-2] = $this->fix_date_mm = $this->fix_date(0, $this->month-2, $this->year);
		$this->fix_date[-1] = $this->fix_date_m = $this->fix_date(0, $this->month-1, $this->year);
		$this->fix_date[0] = array('day'=>$this->day_count, 'month'=>$this->month, 'year'=>$this->year);
		$this->fix_date[1] = $this->fix_date_p = $this->fix_date(0, $this->month+1, $this->year);
		$this->fix_date[2] = $this->fix_date_pp = $this->fix_date(0, $this->month+2, $this->year);
		
		$cache_panch = $GLOBALS["swecache"].$this->year.'-'.$this->month.'-'.$this->latitude.'-'.$this->longitude.'-'.$this->height.'-'.$initial->masa_type.'-'.date("Z", mktime(6, 0, 0, $this->month, 1, $this->year))/60/60;
//		echo $cache_panch ;
		if (file_exists($cache_panch) && $GLOBALS['ifcache']) {
			$panch_arr = unserialize(file_get_contents($cache_panch));
			//$this->masa_arr = &$panch_arr['masa_arr'];
			$this->days_arr = &$panch_arr['days_arr'];
			$this->sunrise = &$panch_arr['sunrise'];
			$this->sunset = &$panch_arr['sunset'];
			$this->moonrise = &$panch_arr['moonrise'];
			$this->moonset = &$panch_arr['moonset'];
			$this->tithi = &$panch_arr['tithi'];
			}
		else {
			$this->riset();
			$this->sauramasa();
			$this->tithif();
			$this->weekday();
			$this->nakshatra();
			$this->panchfix(); //фиксит пробелы в расчетах
			$this->eclipse();
			//$panch_arr['masa_arr'] = &$this->masa_arr;
			$panch_arr['days_arr'] = &$this->days_arr;
			$panch_arr['sunrise'] = &$this->sunrise;
			$panch_arr['sunset'] = &$this->sunset;
			$panch_arr['moonrise'] = &$this->moonrise;
			$panch_arr['moonset'] = &$this->moonset;
			$panch_arr['tithi'] = &$this->tithi;
			if ($GLOBALS['ifcache']) file_put_contents($cache_panch, serialize($panch_arr), LOCK_EX);
			}
		}
	
	private function sauramasa () {	// функция получает две даты: начало $fdate интервала и конец $ldate.
		// Возвращает: число смен месяца в интервале и unix время начала каждого нового месяца.
		$mysql_link_saura = new mysqli($GLOBALS['mysql_server'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password'], $GLOBALS['dbname']);
		$f_unix_date = gmmktime(0, 0, 0, $this->month-1, -3, $this->year);
		$l_unix_date = gmmktime(0, 0, 0, $this->month+2, 2, $this->year);
		$query = $mysql_link_saura->query("SELECT * FROM souramasa WHERE year=". ($this->year - 1) .";");
		if (!$query->num_rows) {	$this->sauramasa_calc($this->year-1, $mysql_link_saura);	}
		$query = $mysql_link_saura->query("SELECT * FROM souramasa WHERE year=" .$this->year. ";");
		if (!$query->num_rows) {	$this->sauramasa_calc($this->year, $mysql_link_saura);	}
		$query = $mysql_link_saura->query("SELECT * FROM souramasa WHERE year=". ($this->year + 1) .";");
		if (!$query->num_rows) {	$this->sauramasa_calc($this->year+1, $mysql_link_saura);	}
		$query = $mysql_link_saura->query("SELECT * FROM souramasa WHERE date>$f_unix_date AND date<$l_unix_date ORDER BY date;");
		while($result = $query->fetch_array(MYSQLI_ASSOC)){ $sauramasa[] = $result; }
			
		foreach ($sauramasa as $key=>$value) {
			$sm_loc_date = $value['date']; // перевод времени из UT в локал
			$sm_day = $this->critical_sr(true, $sm_loc_date);
			$this->days_arr[$sm_day]->sm['m'] = $value['number'];
			$this->days_arr[$sm_day]->sm['t'] = $sm_loc_date;
			}
		if ($sauramasa[0]['number']!=0) {	$sauramasa[-1]['number']=$sauramasa[0]['number']-1;	}
		else {	$sauramasa[-1]['number']=11;	}
		ksort($sauramasa);
		ksort($this->days_arr);
		$current_sm=current($sauramasa);
		if ($next_sm = next($sauramasa)) {
			$sm_day = $this->critical_sr(true, $next_sm['date']);
			foreach ($this->days_arr as $key=>$value) {
				if ($key==$sm_day) {
					$current_sm = current($sauramasa);
					if ($next_sm = next($sauramasa)) {	$sm_day = $this->critical_sr(true, $next_sm['date']);	}
					}
				else {	$this->days_arr[$key]->sm['m']=$current_sm['number'];	}
				}
			}
		$mysql_link_saura->close();
		}
	
	public function sauramasa_calc ($year, $mysql_link_saura)	{	// расчет солнечных месяцев sauramasa и отправление их в кеш БД
		$jd = unixtojd(gmmktime(0, 0, 0, 1, 1, $year));
		$swestring = $GLOBALS["swetest"]." -p0 -bj$jd -head -edir".$GLOBALS["sweephe"]." -fls -sid1";
		exec($swestring ,$get_sm);
if ($this->test) {	print_r($swestring);	print_r($get_sm);	}
		$SunLong=(float)substr($get_sm[0],1,11);
		$SunMasaN = ceil($SunLong/30);
		for ($i=1; $i<=12; $i++) {
			$flag = 0;
			$TSunLong = 30 * ($SunMasaN );
			while ($flag < 1) {
				$get_sm = "";
				$swestring = $GLOBALS["swetest"]." -p0 -bj$jd -head -edir".$GLOBALS["sweephe"]." -fls -sid1";
				exec($swestring ,$get_sm);
if ($this->test) {	print_r($swestring);	print_r($get_sm);	}
				$SunLong=(float)substr($get_sm[0],1,11);
				$SunSpeed=(float)substr($get_sm[0],13,11);
				$asp1 = $TSunLong - $SunLong;
				if ($asp1 > 180) {$asp1 -= 360;}
				if ($asp1 < -180) {$asp1 += 360;}
				if (abs($asp1) > 0.001) {
					$jd += $asp1 / $SunSpeed; 
					$flag = 0;
					}
				else {
					$flag = 1;
					$jdtounix_hms = $this->jdtounix_hms($jd);
					$temp_query = "INSERT INTO souramasa VALUES ($jdtounix_hms, $SunMasaN, ". gmdate('n', $jdtounix_hms). ", ". gmdate('Y', $jdtounix_hms) .");";
					$mysql_link_saura->query($temp_query);
					$SunMasaN++;
					if ($SunMasaN == 12) {$SunMasaN = 0;}
					}
				}
			}
		}
	
	public function riset () {		// расчет восходов/заходов солнца/луны с учетом ошибки вывода swetest, когда нет восхода
		// Восход/Заход Солнца
		$interval_begin = $this->fix_date(-2, $this->month-1, $this->year);
		$interval_end = 99;
		$swestring = $GLOBALS["swetest"]." -p0 -rise -b".$interval_begin['day'].".".$interval_begin['month'] .".".$interval_begin['year'] ." -n".$interval_end." -geopos".$this->longitude.",".$this->latitude.",".$this->height." -hindu -head";
		exec($swestring ,$sun_rs);
if ($this->test) {	print_r($swestring);	print_r($sun_rs);	}
		$i = 1;
		for ($j = 1;$j<=$interval_end;$j++) {
			if (substr($sun_rs[$i],12,3) == "not" AND ($j == 1 OR $j == 2)) {
				$sun_rs = "";
				$i = 1;
				$swestring = $GLOBALS["swetest"]." -p0 -rise -b".($interval_begin['day']+$j).".".$interval_begin['month'] .".".$interval_begin['year'] ." -n".($interval_end-$j)." -geopos".$this->longitude.",".$this->latitude.",".$this->height." -hindu -head";
				exec($swestring ,$sun_rs);
if ($this->test) {	print_r($swestring);	print_r($sun_rs);	}
				}
			elseif (substr($sun_rs[$i],12,3) == "not" AND $j > 2) {	// пересчет если ошибка rise or set not found for planet
				$i = 1;
				$sun_rs = "";
				$swestring = $GLOBALS["swetest"]." -p0 -rise -b".($interval_begin['day']+$j-1).".".$interval_begin['month'].".".$interval_begin['year']." -n".($interval_end-$j)." -geopos".$this->longitude.",".$this->latitude.",".$this->height." -hindu -head";
				exec($swestring ,$sun_rs);
if ($this->test) {	print_r($swestring);	print_r($sun_rs);	}
				}
			else {$i++;}
			if ($i!=1) $sun_rs_arr[$j]=$sun_rs[$i-1];
			}
		foreach ($sun_rs_arr as $key=>$value) {
			$if_r = 0;
			$r_day = (int)substr($value,9,2);
			$r_month = (int)substr($value,12,2);
			$r_year = (int)substr($value,15,4);
			$r_hour = (int)substr($value,22,2);
			$r_minut = (int)substr($value,25,2);
			$r_second = (float)substr($value,28,4);
			if ($r_day!=0 AND $r_month!=0 AND $r_year!=0)	{	// если поле пустое, значит нет восхода
				$sunrise_time = gmmktime($r_hour, $r_minut, $r_second, $r_month, $r_day, $r_year);
				$day_index = $this->critical_sr(false, $sunrise_time);
/*				$c_rise_day = date('j', $sunrise_time);
				$c_rise_month = date('n', $sunrise_time);
				if ($c_rise_month==$this->month) {	$day_index = $c_rise_day;	}
				elseif ($c_rise_month == $this->fix_date_p['month']) {	$day_index = $this->day_count + $c_rise_day;	}
				elseif ($c_rise_month ==$interval_begin['month']) {	$day_index = $c_rise_day - $this->day_count_foo($this->month-1, $this->year);	}
*/				
				$this->days_arr[$day_index]->sr[] = $sunrise_time;
				$this->sunrise[] = &$this->days_arr[$day_index]->sr[key($this->days_arr[$day_index]->sr)];
				}
			else {	$if_r=4;	} // если нет восхода, то сдвиг в парсинге заката

			$s_day = (int)substr($value,45+$if_r,2);
			$s_month = (int)substr($value,48+$if_r,2);
			$s_year = (int)substr($value,51+$if_r,4);
			$s_hour = (int)substr($value,58+$if_r,2);
			$s_minut = (int)substr($value,61+$if_r,2);
			$s_second = (float)substr($value,64+$if_r,4);
			if ($s_day!=0 AND $s_month!=0 AND $s_year!=0)	{	// если поле пустое, значит нет захода
				$sunset_time = gmmktime($s_hour, $s_minut, $s_second, $s_month, $s_day, $s_year);
				$day_index = $this->critical_sr(false, $sunset_time);
/*				$c_set_day = date('j', $sunset_time);
				$c_set_month = date('n', $sunset_time);
				if ($c_set_month==$this->month) {$day_index = $c_set_day;}
				elseif ($c_set_month == $this->fix_date_p['month']) {	$day_index = $this->day_count + $c_set_day;	}
				elseif ($c_set_month ==$interval_begin['month']) {	$day_index = $c_set_day - $this->day_count_foo($this->month-1, $this->year);	}
*/
				$this->days_arr[$day_index]->ss[] = $sunset_time;
				//$this->mdays_arr[$c_rise_month][$c_rise_day]->sr[] = &$this->days_arr[$day_index]->ss[key($this->days_arr[$day_index]->ss)];
				$this->sunset[] = &$this->days_arr[$day_index]->ss[key($this->days_arr[$day_index]->ss)];
				}
			}
		// Восход/Заход Луны
		$swestring = $GLOBALS["swetest"]." -p1 -rise -b".$interval_begin['day'].".".$interval_begin['month'] .".".$interval_begin['year'] ." -n".$interval_end." -geopos".$this->longitude.",".$this->latitude.",".$this->height." -hindu -head";
		exec($swestring, $moon_rs);
if ($this->test) {	print_r($swestring);	print_r($moon_rs);	}
		$i = 1;
		for ($j = 1;$j<=$interval_end;$j++) {
			if (substr($moon_rs[$i],12,3) == "not" AND ($j == 1 OR $j == 2)) {
				$moon_rs = "";
				$i = 1;
				$swestring = $GLOBALS["swetest"]." -p1 -rise -b".($interval_begin['day']+$j).".".$interval_begin['month'] .".".$interval_begin['year'] ." -n".($interval_end-$j)." -geopos".$this->longitude.",".$this->latitude.",".$this->height." -hindu -head";
				exec($swestring ,$moon_rs);
if ($this->test) {	print_r($swestring);	print_r($moon_rs);	}
				}
			elseif (substr($moon_rs[$i],12,3) == "not" AND $j > 2) {	// пересчет если ошибка rise or set not found for planet
				$i = 1;
				$moon_rs = "";
				$swestring = $GLOBALS["swetest"]." -p1 -rise -b".($interval_begin['day']+$j-1).".".$interval_begin['month'].".".$interval_begin['year']." -n".($interval_end-$j)." -geopos".$this->longitude.",".$this->latitude.",".$this->height." -hindu -head";
				exec($swestring, $moon_rs);
if ($this->test) {	print_r($swestring);	print_r($moon_rs);	}
				}
			else {$i++;}
			if ($i!=1) $moon_rs_arr[$j]=$moon_rs[$i-1];
			}
		foreach ($moon_rs_arr as $key=>$value) {
			$if_r = 0;
			$r_day = (int)substr($value,9,2);
			$r_month = (int)substr($value,12,2);
			$r_year = (int)substr($value,15,4);
			$r_hour = (int)substr($value,22,2);
			$r_minut = (int)substr($value,25,2);
			$r_second = (float)substr($value,28,4);
			if ($r_day!=0 AND $r_month!=0 AND $r_year!=0)	{	// если поле пустое, значит нет восхода
				$moonrise_time = gmmktime($r_hour, $r_minut, $r_second, $r_month, $r_day, $r_year);
				$day_index = $this->critical_sr(true, $moonrise_time);
				$this->days_arr[$day_index]->mr[] = $moonrise_time;
				$this->moonrise[] = &$this->days_arr[$day_index]->mr[key($this->days_arr[$day_index]->mr)];
				}
			else {	$if_r=4;	} // если нет восхода, то сдвиг в парсинге заката

			$s_day = (int)substr($value,45+$if_r,2);
			$s_month = (int)substr($value,48+$if_r,2);
			$s_year = (int)substr($value,51+$if_r,4);
			$s_hour = (int)substr($value,58+$if_r,2);
			$s_minut = (int)substr($value,61+$if_r,2);
			$s_second = (float)substr($value,64+$if_r,4);
			if ($s_day!=0 AND $s_month!=0 AND $s_year!=0)	{	// если поле пустое, значит нет захода
				$moonset_time = gmmktime($s_hour, $s_minut, $s_second, $s_month, $s_day, $s_year);		
				$day_index = $this->critical_sr(true, $moonset_time);
				$this->days_arr[$day_index]->ms[] = $moonset_time;
				$this->moonset[] = &$this->days_arr[$day_index]->ms[key($this->days_arr[$day_index]->ms)];
				}
			}
		}
	
	public function tithif () {
		$mysql_link_tithi = new mysqli($GLOBALS['mysql_server'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password'], $GLOBALS['dbname']);
		for ($i=-2; $i<=3;$i++) {
			$fix_date = $this->fix_date(0,$this->month+$i, $this->year);
			$query = $mysql_link_tithi->query("SELECT * FROM tithi WHERE month=". $fix_date['month'] ." AND year=". $fix_date['year'] .";");
			if ($query->num_rows<5) {
			// if ($query->num_rows<5 || $this->test) {	
				$this->tithi_calc($fix_date['month'], $fix_date['year'], $mysql_link_tithi);
				}
			}
		$query = $mysql_link_tithi->query("SELECT * FROM tithi WHERE (n=15 OR n=0) AND ((month=". $this->fix_date_m['month'] ." AND year=". $this->fix_date_m['year'] .") OR (month=". $this->month ." AND year=". $this->year .") OR (month=". $this->fix_date_p['month'] ." AND year=". $this->fix_date_p['year'] ."));");
		$masa_check=true;
//test		$masa_check=false;
		while($result = $query->fetch_array(MYSQLI_ASSOC))	{	if ($result['am']==NULL) $masa_check=false;	}
		if (!$masa_check) {
			$start = gmmktime(0,0,0,$this->month-2,1,$this->year);
			$stop =  gmmktime(0,0,0,$this->month+4,-1,$this->year);
			$starts = gmmktime(0,0,0,$this->month-3,1,$this->year);
			$string_am = "SELECT * FROM tithi WHERE (n=15 OR n=0) AND d>=$start AND d<=$stop ORDER BY d;";
//echo $string_am."<br>";
			$query_am = $mysql_link_tithi->query($string_am);
			$i=0;
			while($result = $query_am->fetch_array(MYSQLI_ASSOC)){
//$testttt[] = $result;
				if ($result['p']==0) {
					$Amavasya[$i] = $result;
					$string_sm = "SELECT * FROM souramasa WHERE date<=". $Amavasya[$i]['d'] ." AND date>= $starts ORDER BY date DESC;";
//echo $string_sm."<br>";
					$query_sm = $mysql_link_tithi->query($string_sm);
					if ($mysql_link_tithi->sqlstate == 0) {$Sauramasa[$i] = $query_sm->fetch_array(MYSQLI_ASSOC);} // Sauramasa - солнечный месяц в эту амавасью
//print_r($Sauramasa);
					if (isset($Sauramasa[$i])) {
						$x = $Sauramasa[$i]['number'] + 2;
						$y = 12;
						$Chandramasa[$i+1] = $y + $x + $y * floor($x / -$y);
//						echo "s=".$Sauramasa[$i]['number']." x=$x y=$y chandra=".$Chandramasa[$i+1] ."<br>";
						}
					if ($i>0) {
						if ($Sauramasa[$i-1]['number'] == $Sauramasa[$i]['number']) { $Leap[$i] = 1; }
						else { $Leap[$i] = 0; }	
						}
		
					if (isset($Amavasya[$i-1]) AND isset($Leap[$i]) AND isset($Chandramasa[$i])) {
						$calendarYear = $this->calendarYear($Amavasya[$i-1]['d']);
						$Hyear[$i-1] = ($calendarYear - 3044 - (($Leap[$i]) && ($Chandramasa[$i] == 1) ? -1 : 0));
						}
					$i++;
					}
				elseif($result['p']==1) {
					$Purnima[$i] = $result;
					}
				}

//print_r($Amavasya); print_r($Sauramasa); print_r($Leap); print_r($Chandramasa); print_r($Hyear); print_r($Purnima);
			while($i>=0)	{
				if (isset($Amavasya[$i]) AND isset($Leap[$i]) AND isset($Chandramasa[$i]) AND isset($Hyear[$i]))	{
//echo "<br>i=$i <br>";
					$string = "UPDATE tithi SET am=". $Chandramasa[$i] .", l=". $Leap[$i] .", ay=". $Hyear[$i] ." WHERE d=". $Amavasya[$i]['d'] .";";
//echo $string."<br>";
					$mysql_link_tithi->query($string);
					if (isset($Purnima[$i])) {
						$string = "UPDATE tithi SET am=". $Chandramasa[$i] .", l=". $Leap[$i] .", ay=". $Hyear[$i] ." WHERE d=". $Purnima[$i]['d'] .";";
//echo $string."<br>";
						$mysql_link_tithi->query($string);
						}
					}
				$i--;
				}
			}

		$start = gmmktime(0,0,0,$this->month-2,1,$this->year);
		$stop =  gmmktime(0,0,0,$this->month+3,1,$this->year);
		$string = "SELECT * FROM tithi WHERE d>$start AND d<$stop ORDER BY d;";
		$query = $mysql_link_tithi->query($string);
		while($result = $query->fetch_array(MYSQLI_ASSOC)){ $this->tithi[] = $result; }
		$string = "SELECT * FROM tithi WHERE (n=15 OR n=0) AND ((month=". $this->fix_date_m['month'] ." AND year=". $this->fix_date_m['year'] .") OR (month=". $this->month ." AND year=". $this->year .") OR (month=". $this->fix_date_p['month'] ." AND year=". $this->fix_date_p['year'] .") OR (month=". $this->fix_date_pp['month'] ." AND year=". $this->fix_date_pp['year'] .")) ORDER BY d;";
//echo $string."<br>";
		$query = $mysql_link_tithi->query($string);
		while($result = $query->fetch_array(MYSQLI_ASSOC)){ $critical[] = $result; }
//echo " critical "; print_r($critical);
		// данные титх сопоставить с часовым поясом и отправить с panch->days_arr
		$tithi_size = count($this->tithi);
		$curr_crit = reset($critical);
		$next = false;
		for ($i=0; $i < $tithi_size; $i++) {
		$counted_day_number = $this->critical_sr(true, $this->tithi[$i]['d']);
			if (isset($this->days_arr[$counted_day_number]->ti[0]['d'])) {	// date2 - время вторых титхи в текущие сутки, если есть
				$this->days_arr[$counted_day_number]->ti[1] = &$this->tithi[$i];
				if ($i!=0) {	$this->days_arr[$counted_day_number]->ti[1]['d0'] = &$this->tithi[$i-1]['d'];	}
				$this->tithi[$i]['day'] = $counted_day_number;
				}
			else {
				$this->days_arr[$counted_day_number]->ti[0] = &$this->tithi[$i];
				if ($i!=0) {	$this->days_arr[$counted_day_number]->ti[0]['d0'] = &$this->tithi[$i-1]['d'];	}
				$this->tithi[$i]['day'] = $counted_day_number;
				}
			
			if (($this->tithi[$i]['month']==$this->fix_date_m['month'] AND $this->tithi[$i]['year']==$this->fix_date_m['year']) OR ($this->tithi[$i]['month']==$this->month AND $this->tithi[$i]['year']==$this->year) OR ($this->tithi[$i]['month']==$this->fix_date_p['month'] AND $this->tithi[$i]['year']==$this->fix_date_p['year'])) {
				if ($this->tithi[$i]['d']==$curr_crit['d']) {	$next=true;	}
				else {
					$this->tithi[$i]['am'] = $curr_crit['am'];	// Амавасьянт паньчанга
					$this->tithi[$i]['l'] = $curr_crit['l'];
					$this->tithi[$i]['ay'] = $curr_crit['ay'];
					}
				if ($this->tithi[$i]['p']==1) {					// Пурнимант паньчанга
					$this->tithi[$i]['pm'] = $curr_crit['am'];	
					$this->tithi[$i]['py'] = $curr_crit['ay'];
					}
				elseif ($this->tithi[$i]['p']==0) {
					if ($this->tithi[$i]['l']==1) {
						$this->tithi[$i]['pm'] = $curr_crit['am'];	
						$this->tithi[$i]['py'] = $curr_crit['ay'];
						}
					else {
						if ($curr_crit['am']==12) {
							$this->tithi[$i]['pm'] = 1;	
							$this->tithi[$i]['py'] = $curr_crit['ay']+1;
							}
						else {
							$this->tithi[$i]['pm'] = $curr_crit['am']+1;	
							$this->tithi[$i]['py'] = $curr_crit['ay'];
							}
						}
					}
				if ($next) {	$curr_crit=next($critical); $next=false;	}

				$update_string = "UPDATE tithi SET am=". $this->tithi[$i]['am'] .", pm=". $this->tithi[$i]['pm'] .", l=". $this->tithi[$i]['l'] .", ay=". $this->tithi[$i]['ay'] .", py=". $this->tithi[$i]['py'] ." WHERE d=". $this->tithi[$i]['d'] .";";
				$mysql_link_tithi->query($update_string);
				}
			}
		$mysql_link_tithi->close();
		//array_unique($this->masa_arr[0]); array_unique($this->masa_arr[1]);
		//sort($this->masa_arr[0]); sort($this->masa_arr[1]);
		ksort($this->days_arr);
		}
	
	public function tithi_calc ($month, $year, $mysql_link_tithi) {
		$fday_time = gmmktime(0,0,0,$month, 1, $year);
		$day_count = $this->day_count_foo($month, $year);
		$jd = unixtojd($fday_time);
		$swestring = $GLOBALS["swetest"]." -p01 -bj$jd -n1 -edir".$GLOBALS["sweephe"]." -head -fl";
		exec($swestring, $get_sm);
if ($this->test) {	print_r(['tithi_calc', '$swestring', $swestring, '$get_sm', $get_sm]);	}
		$sunL=(float)substr($get_sm[0],1,11);
		$moonL=(float)substr($get_sm[1],1,11);
		$difference=$moonL-$sunL;
		if ($moonL < $sunL) $difference += 360;
		$ftithi=floor($difference/12);
		if ($this->test) {	print_r(['$sunL', $sunL, '$moonL', $moonL, '$difference', $difference, '$ftithi', $ftithi]);	}
		do	{
			$flag = 0;
			$aspect = 12 * $ftithi;
			while ($flag < 1) {
				$get_sm = "";
				$swestring = $GLOBALS["swetest"]." -p01 -bj$jd -n1 -edir".$GLOBALS["sweephe"]." -head -fls";
				exec($swestring, $get_sm);
if ($this->test) {	print_r(['$swestring', $swestring, '$get_sm', $get_sm]);	}
				$sunL=(float)substr($get_sm[0],1,11);
				$moonL=(float)substr($get_sm[1],1,11);
				$moonS=(float)substr($get_sm[1],13,11);
				$a = $this->fix360($sunL + $aspect);           // точка, где должна быть Луна
				$asp1 = $a - $moonL;                    // pасстояние от Луны до нужной точки
				if ($asp1 > 180) $asp1 -= 360;
				if ($asp1 < -180) $asp1 += 360;
				if (abs($asp1) > 0.001) {$jd += ($asp1 / ($moonS - 1)); $flag = 0;}
				else {$flag = 1;}
				}
			if ($ftithi == 0) {
				$paksha = 0;
				$ntithi = 15;
				$ftithi++;
				}
			elseif ($ftithi == 29) {
				$paksha = 0;
				$ntithi = 14;
				$ftithi=0;
				}
			elseif ($ftithi < 16) {
				$paksha = 1;
				$ntithi = $ftithi;
				$ftithi++;
				}
			elseif ($ftithi >= 16) {
				$paksha = 0;
				$ntithi = $ftithi - 15;
				$ftithi++;
				}
			//echo "jd=$jd <br>";
			$unix_date = $this->jdtounix_hms($jd);
			//echo "unix_date=$unix_date <br>";
			$r_month = gmdate('n', $unix_date);
			if ($month == $r_month) {
				if ($paksha==0 AND $ntithi==15) {	$cntithi = 0;	}
				else {	$cntithi = $ntithi;	}
				$query_string = "INSERT INTO tithi VALUES ($unix_date, $paksha, $cntithi, $r_month, ". gmdate('Y', $unix_date).", NULL, NULL, NULL, NULL, NULL);";
				if ($this->test) {	print_r($query_string);	}
				$mysql_link_tithi->query($query_string);
				}
			$calc_fin = $fday_time + ($day_count - 1)* 86400;	// время начала месяца + 1месяц
			if ($this->test) {	print_r(['$unix_date', $unix_date, '$calc_fin', $calc_fin, '$fday_time', $fday_time, '$day_count', $day_count]);	}
			} while ($unix_date < $calc_fin);
		}

	public function nakshatra () {
		$mysql_link_nakshatra = new mysqli($GLOBALS['mysql_server'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password'], $GLOBALS['dbname']);

foreach ($this->fix_date as $key=>$value) {
	$query = $mysql_link_nakshatra->query("SELECT * FROM nakshatra WHERE month=". $value['month'] ." AND year=" .$value['year']. ";");
	if ($query->num_rows<5) {	
		$this->nakshatra_calc($value['month'], $value['year'], $mysql_link_nakshatra);
		}
	}
/*		$fix_date_m = $this->fix_date(1,$this->month-1, $this->year);
		$query = $mysql_link_nakshatra->query("SELECT * FROM nakshatra WHERE month=". $fix_date_m['month'] ." AND year=" .$fix_date_m['year']. ";");
		if ($query->num_rows<5) {	
			$this->nakshatra_calc($fix_date_m['month'], $fix_date_m['year'], $mysql_link_nakshatra);
			}
		$query = $mysql_link_nakshatra->query("SELECT * FROM nakshatra WHERE month=". $this->month ." AND year=". $this->year .";");
		if ($query->num_rows<5) {	
			$this->nakshatra_calc($this->month, $this->year, $mysql_link_nakshatra);
			}
		$fix_date_p = $this->fix_date(1,$this->month+1, $this->year);
		$query = $mysql_link_nakshatra->query("SELECT * FROM nakshatra WHERE month=". $fix_date_p['month'] ." AND year=" .$fix_date_p['year']. ";");
		if ($query->num_rows<5) {	
			$this->nakshatra_calc($fix_date_p['month'], $fix_date_p['year'], $mysql_link_nakshatra);
			}
*/
		$start = gmmktime(0,0,0,$this->month-1,-1,$this->year);
		$stop =  gmmktime(0,0,0,$this->month+2,2,$this->year);
		$string = "SELECT * FROM nakshatra WHERE date>$start AND date<$stop ORDER BY date;";
		$query = $mysql_link_nakshatra->query($string);
		while($result = $query->fetch_array(MYSQLI_ASSOC)){ $nakshatra[] = $result; }
		// данные накшатр сопоставить с часовым поясом и отправить с panch->days_arr
		$nakshatra_size = count($nakshatra);
//print_r($nakshatra);
		for ($i=0; $i < $nakshatra_size; $i++) {
			$counted_day_number = $this->critical_sr(true, $nakshatra[$i]['date']);

			if (isset($this->days_arr[$counted_day_number]->na[0]['d'])) { 	// date2 - время вторых титхи в текущие сутки, если есть
				$this->days_arr[$counted_day_number]->na[1]['n'] = $nakshatra[$i]['nnakshatra'];
				$this->days_arr[$counted_day_number]->na[1]['d'] = $nakshatra[$i]['date'];
				}
			else {
				$this->days_arr[$counted_day_number]->na[0]['n'] = $nakshatra[$i]['nnakshatra'];
				$this->days_arr[$counted_day_number]->na[0]['d'] = $nakshatra[$i]['date'];
				}
			}
		$mysql_link_nakshatra->close();
		}
	
	public function nakshatra_calc ($month, $year, $mysql_link_nakshatra) {
		$fday_time = gmmktime(0,0,0,$month, 1, $year);
		$day_count = $this->day_count_foo($month, $year);
		$jd = unixtojd($fday_time)-0.5;
		$swestring = $GLOBALS["swetest"]." -p1 -bj$jd -head -edir".$GLOBALS["sweephe"]." -fls -sid1";
		exec($swestring, $get_m);
if ($this->test) {	print_r($swestring);	print_r($get_m);	}
		$moonL=(float)substr($get_m[0],1,11);
//echo "jd=".$jd." L=".$moonL."</br>";
		$fnakshatra=floor($moonL/(360/27));
		do	{
			$flag = 0;
			$aspect = (360/27) * $fnakshatra; 
//echo "fnakshatra = $fnakshatra <br>";
//echo "aspect= $aspect </br>";
			while ($flag < 1) {
				$get_m = "";
				$swestring = $GLOBALS["swetest"]." -p1 -bj$jd -head -edir".$GLOBALS["sweephe"]." -fls -sid1";
				exec($swestring, $get_m);
if ($this->test) {	print_r($swestring);	print_r($get_m);	}
				$moonL=(float)substr($get_m[0],1,11);
				$moonS=(float)substr($get_m[0],13,11);
//echo "L=".$moonL." S=".$moonS."<br>";
			    $asp1 = $aspect - $moonL;
				if ($asp1 > 180) $asp1 -= 360;
				if ($asp1 < -180) $asp1 += 360;
				if (abs($asp1) > 0.001) {$jd += ($asp1 / $moonS); $flag = 0;}
				else {$flag = 1;}
//				echo "jd=$jd asp1=$asp1 aspect=$aspect moonL=$moonL <br>";
				}
//echo "fnakshatra = $fnakshatra <br>";

			if ($fnakshatra == 27) {
				$fnakshatra=0; 
				$nnakshatra=27;
				}
			if ($fnakshatra == 0) {
				$fnakshatra=1; 
				$nnakshatra=27;
				}
			else {
				$nnakshatra=$fnakshatra;
				$fnakshatra++; 
				}
//echo "fnakshatra = $fnakshatra <br>";
//echo "nnakshatra = $nnakshatra <br><br>";
			$unix_date = $this->jdtounix_hms($jd);
//			echo $unix_date.date('c', $unix_date)."<br>";
				$r_month = gmdate('n', $unix_date);
//				echo "month=$month r_month=$r_month <br><br>";
				if ($month == $r_month) {
					$mysql_link_nakshatra->query("INSERT INTO nakshatra VALUES ($unix_date, $nnakshatra, $r_month, ". gmdate('Y', $unix_date).");");
					}
			$calc_fin = $fday_time + ($day_count - 1)* 86400;	// время начала месяца + 1месяц

			
			//echo "fnakshatra = $fnakshatra </br>";
			} while ($unix_date < $calc_fin);
		}

	public function panchfix () {
			foreach ($this->days_arr as $key=>$value) {
				if (isset($this->days_arr[$key]->ti)) {$temp = $this->days_arr[$key]->ti;}
				if (isset($temp[1]) AND !isset($this->days_arr[$key]->ti)) {
					$this->days_arr[$key]->ti[0]['l']=$temp[1]['l'];
					$this->days_arr[$key]->ti[0]['am']=$temp[1]['am'];
					$this->days_arr[$key]->ti[0]['ay']=$temp[1]['ay'];
					$this->days_arr[$key]->ti[0]['pm']=$temp[1]['pm'];
					$this->days_arr[$key]->ti[0]['py']=$temp[1]['py'];
					$this->days_arr[$key]->ti[0]['day']=$temp[1]['day'];
					}
				elseif (isset($temp[0]) AND !isset($this->days_arr[$key]->ti)) {
					$this->days_arr[$key]->ti[0]['l']=$temp[0]['l'];
					$this->days_arr[$key]->ti[0]['am']=$temp[0]['am'];
					$this->days_arr[$key]->ti[0]['ay']=$temp[0]['ay'];
					$this->days_arr[$key]->ti[0]['pm']=$temp[0]['pm'];
					$this->days_arr[$key]->ti[0]['py']=$temp[0]['py'];
					$this->days_arr[$key]->ti[0]['day']=$temp[0]['day'];
					}
				if (!isset($this->days_arr[$key]->ti[0]['n']) AND isset($this->days_arr[$key+1]->ti[0]['n'])) {//добавление данных в дни с пустыми титхами из следующего дня
					$this->days_arr[$key]->ti[0]['n']=$this->days_arr[$key+1]->ti[0]['n'];
					$this->days_arr[$key]->ti[0]['p']=$this->days_arr[$key+1]->ti[0]['p'];
					$this->days_arr[$key]->ti[0]['l']=$this->days_arr[$key+1]->ti[0]['l'];
					$this->days_arr[$key]->ti[0]['am']=$this->days_arr[$key+1]->ti[0]['am'];
					$this->days_arr[$key]->ti[0]['ay']=$this->days_arr[$key+1]->ti[0]['ay'];
					$this->days_arr[$key]->ti[0]['pm']=$this->days_arr[$key+1]->ti[0]['pm'];
					$this->days_arr[$key]->ti[0]['py']=$this->days_arr[$key+1]->ti[0]['py'];
					$this->days_arr[$key]->ti[0]['day']=$this->days_arr[$key+1]->ti[0]['day'];
					}
				if (!isset($this->days_arr[$key]->na[0]['n']) AND isset($this->days_arr[$key+1]->na[0]['n'])) {//добавление данных в дни с пустыми накшатрами из следующего дня
					$this->days_arr[$key]->na[0]['n']=$this->days_arr[$key+1]->na[0]['n'];
				}
			}
		}
		
	public function eclipse () {
		$fday_time = gmmktime(0,0,0,$this->month,1,$this->year);
		$jd = unixtojd($fday_time)+0.5;
		// Солнечные затмения
		$swestring = $GLOBALS["swetest"]." -solecl -b1.".$this->fix_date_m['month'].".".$this->fix_date_m['year']." -n2 -edir".$GLOBALS["sweephe"]." -head -fl";
		exec($swestring ,$get_es);
if ($this->test) {	print_r($swestring);	print_r($get_es);	}
		$e_count=count($get_es);
//echo "swestring: $swestring </br>";
//echo "get_es: ";
//print_r($get_es);
		for ($i=1;$i<$e_count;$i=$i+3) {
			$parts=preg_split("/[\t]+/", $get_es[$i]);
//echo "parts i=$i: ";
//print_r($parts);			
			$e_type=preg_split("/[\s]+/", $parts[0]);
			if (trim($e_type[0]) == 'annular') {
				$e_type_r = 1;
				if (trim($e_type[1]) == 'non-central') {	
					$e_type_r = 2;
					$e_type[1] = $e_type[2];
					}
				}
			elseif (trim($e_type[0]) == 'total') {	$e_type_r = 3;	}
			elseif (trim($e_type[0]) == 'ann-tot') {	$e_type_r = 4;	}
			elseif (trim($e_type[0]) == 'partial') {	$e_type_r = 5;	}
//echo "type: ";
//print_r($e_type);			
			$e_date=preg_split("/[\s.]+/", $e_type[1]);
//echo "date: ";
//print_r($e_date);
			$e_time_max=preg_split("/[\s:]+/", trim($parts[1]));
//echo "time: ";
//print_r($e_time_max);

			$e_time_max_gm=gmmktime((int)$e_time_max[0], (int)$e_time_max[1], (int)$e_time_max[2], $e_date[1], $e_date[0], $e_date[2]);
//print_r($e_time_max_gm);
			if ($this->month==date('n', $e_time_max_gm) AND $this->year==date('Y', $e_time_max_gm)) {
				$e_day = $this->critical_sr(true, $e_time_max_gm);
				$this->days_arr[$e_day]->se['type'] = $e_type_r;
				$this->days_arr[$e_day]->se['max'] = $e_time_max_gm;
				$this->days_arr[$e_day]->se['saros'] = substr($parts[4],6,7);
				//$e_times=preg_split("/[\s-]+/", trim($get_es[$i+1]));
				$e_times=preg_split("/[\s]+/", trim($get_es[$i+1]));
				$e_p1=preg_split("/[\s:]+/", trim($e_times[0]));
//print_r($e_times);
//print_r($e_p1);
				if (trim($e_p1[0])!='-') {
					$this->days_arr[$e_day]->se['p1']=gmmktime((int)$e_p1[0], (int)$e_p1[1], (int)$e_p1[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->se['p1']>$e_time_max_gm) {	$this->days_arr[$e_day]->se['p1']=gmmktime((int)$e_p1[0], (int)$e_p1[1], (int)$e_p1[2], $e_date[1], $e_date[0]-1, $e_date[2]);	}
					}
				$e_t1=preg_split("/[\s:]+/", trim($e_times[1]));
//print_r($e_t1);
				if (trim($e_t1[0])!='-') {
					$this->days_arr[$e_day]->se['t1']=gmmktime((int)$e_t1[0], (int)$e_t1[1], (int)$e_t1[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->se['t1']>$e_time_max_gm) {	$this->days_arr[$e_day]->se['t1']=gmmktime((int)$e_t1[0], (int)$e_t1[1], (int)$e_t1[2], $e_date[1], $e_date[0]-1, $e_date[2]);	}
					}
				$e_t2=preg_split("/[\s:]+/", trim($e_times[2]));
//print_r($e_t2);
				if (trim($e_t2[0])!='-') {
					$this->days_arr[$e_day]->se['t2']=gmmktime((int)$e_t2[0], (int)$e_t2[1], (int)$e_t2[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->se['t2']<$e_time_max_gm) {	$this->days_arr[$e_day]->se['t2']=gmmktime((int)$e_t2[0], (int)$e_t2[1], (int)$e_t2[2], $e_date[1], $e_date[0]+1, $e_date[2]);	}
					}
				$e_p2=preg_split("/[\s:]+/", trim($e_times[3]));
//print_r($e_p2);
				if (trim($e_p2[0])!='-') {
					$this->days_arr[$e_day]->se['p2']=gmmktime((int)$e_p2[0], (int)$e_p2[1], (int)$e_p2[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->se['p2']<$e_time_max_gm) {	$this->days_arr[$e_day]->se['p2']=gmmktime((int)$e_p2[0], (int)$e_p2[1], (int)$e_p2[2], $e_date[1], $e_date[0]+1, $e_date[2]);	}
					}
				$e_max=preg_split("/[\s°'a-z]+/", trim($get_es[$i+2]));
//echo "get_es[".($i+2)."]: ";
//print_r($get_es[$i+2]);
//echo "e_max: ";
//print_r($e_max);
				if ($e_max[0]>0) {	$this->days_arr[$e_day]->se['long'] = $e_max[0]+(float)$e_max[1]/60;	}
				else {	$this->days_arr[$e_day]->se['long'] = $e_max[0]-(float)$e_max[1]/60;	}
				if ($e_max[2]>0) {	$this->days_arr[$e_day]->se['lat'] = $e_max[2]+(float)$e_max[3]/60;	}
				else {	$this->days_arr[$e_day]->se['lat'] = $e_max[2]-(float)$e_max[3]/60;	}
				//$this->days_arr[$e_day]->se['tm'] = (int)$e_max[4];
				//$this->days_arr[$e_day]->se['ts'] = (int)$e_max[5];

				$swestring = $GLOBALS["swetest"]." -local -solecl -b".$e_date[0].".".$e_date[1].".".$e_date[2]." -n1 -edir".$GLOBALS["sweephe"]." -geopos".$this->longitude.",".$this->latitude.",".$this->height." -head -fl";
				exec($swestring ,$get_es_l);
if ($this->test) {	print_r($swestring); print_r($get_es_l);	}
				$parts_l=preg_split("/[\t]+/", $get_es_l[$i]);
				$e_type_l=preg_split("/[\s]+/", $parts_l[0]);
				$e_date_l=preg_split("/[\s.]+/", $e_type_l[1]);
				$e_time_max_l=preg_split("/[\s:]+/", trim($parts_l[1]));
				$e_time_max_gm_l=gmmktime((int)$e_time_max[0], (int)$e_time_max[1], (int)$e_time_max[2], $e_date_l[1], $e_date_l[0], $e_date_l[2]);

				if (date('n', $e_time_max_gm)==date('n', $e_time_max_gm_l) AND date('Y', $e_time_max_gm)==date('Y', $e_time_max_gm_l)) { 
					$e_day_l = $this->critical_sr(true, $e_time_max_gm_l);
					if ($e_day==$e_day_l) {
						if (trim($e_type_l[0]) == 'annular') {	$this->days_arr[$e_day]->se['typel'] = 1;	}
						elseif (trim($e_type_l[0]) == 'annular non-central') {	$this->days_arr[$e_day]->se['typel'] = 2;	}
						elseif (trim($e_type_l[0]) == 'total') {	$this->days_arr[$e_day]->se['typel'] = 3;	}
						elseif (trim($e_type_l[0]) == 'ann-tot') {	$this->days_arr[$e_day]->se['typel'] = 4;	}
						elseif (trim($e_type_l[0]) == 'partial') {	$this->days_arr[$e_day]->se['typel'] = 5;	}
						$this->days_arr[$e_day]->se['max_l'] = $e_time_max_gm_l;
						$e_time_l=preg_split("/[\s]+/", $get_es_l[$i+1]);
						if ((int)$e_time_l[1]!=0 AND (int)$e_time_l[3]!=0) {
							$this->days_arr[$e_day]->se['tm_l'] = (int)$e_time_l[1]; // ?
							$this->days_arr[$e_day]->se['ts_l'] = (int)$e_time_l[3]; // ?
							}
						$e_p1_l=preg_split("/[\s:]+/", trim($e_time_l[5]));
						if (trim($e_p1_l[0])!='-') {
							$this->days_arr[$e_day]->se['p1l']=gmmktime((int)$e_p1_l[0], (int)$e_p1_l[1], (int)$e_p1_l[2], $e_date_l[1], $e_date_l[0], $e_date_l[2]);
							if ($this->days_arr[$e_day]->se['p1l']>$e_time_max_gm_l) {	$this->days_arr[$e_day]->se['p1l']=gmmktime((int)$e_p1_l[0], (int)$e_p1_l[1], (int)$e_p1_l[2], $e_date_l[1], $e_date_l[0]-1, $e_date_l[2]);	}
							}
						$e_t1_l=preg_split("/[\s:]+/", trim($e_time_l[6]));
						if (trim($e_t1_l[0])!='-') {
							$this->days_arr[$e_day]->se['t1l']=gmmktime((int)$e_t1_l[0], (int)$e_t1_l[1], (int)$e_t1_l[2], $e_date_l[1], $e_date_l[0], $e_date_l[2]);
							if ($this->days_arr[$e_day]->se['t1l']>$e_time_max_gm_l) {	$this->days_arr[$e_day]->se['t1l']=gmmktime((int)$e_t1_l[0], (int)$e_t1_l[1], (int)$e_t1_l[2], $e_date_l[1], $e_date_l[0]-1, $e_date_l[2]);	}
							}
						$e_t2_l=preg_split("/[\s:]+/", trim($e_time_l[7]));
						if (trim($e_t2_l[0])!='-') {
							$this->days_arr[$e_day]->se['t2l']=gmmktime((int)$e_t2_l[0], (int)$e_t2_l[1], (int)$e_t2_l[2], $e_date_l[1], $e_date_l[0], $e_date_l[2]);
							if ($this->days_arr[$e_day]->se['t2l']<$e_time_max_gm_l) {	$this->days_arr[$e_day]->se['t2l']=gmmktime((int)$e_t2_l[0], (int)$e_t2_l[1], (int)$e_t2_l[2], $e_date_l[1], $e_date_l[0]-1, $e_date_l[2]);	}
							}
						$e_p2_l=preg_split("/[\s:]+/", trim($e_time_l[8]));
						if (trim($e_p2_l[0])!='-') {
							$this->days_arr[$e_day]->se['p2l']=gmmktime((int)$e_p2_l[0], (int)$e_p2_l[1], (int)$e_p2_l[2], $e_date_l[1], $e_date_l[0], $e_date_l[2]);
							if ($this->days_arr[$e_day]->se['p2l']<$e_time_max_gm_l) {	$this->days_arr[$e_day]->se['p2l']=gmmktime((int)$e_p2_l[0], (int)$e_p2_l[1], (int)$e_p2_l[2], $e_date_l[1], $e_date_l[0]-1, $e_date_l[2]);	}
							}
						}
					}
				}
			else {
				break;
				}

			}
		// Лунные затмения
		$swestring = $GLOBALS["swetest"]." -lunecl -b1.".$this->fix_date_m['month'].".".$this->fix_date_m['year']." -n2 -edir".$GLOBALS["sweephe"]." -head -fl";
		exec($swestring ,$get_em);
if ($this->test) {	print_r($swestring);	print_r($get_em);	}
		$e_count=count($get_em);
//print_r($get_em);
		
//print_r($this->fix_date_p);
//print_r($this->fix_date_m);
		
		for ($i=1;$i<$e_count;$i=$i+1) {
			$parts=preg_split("/[\t]+/", $get_em[$i]);
//print_r($parts);
			$e_type=preg_split("/[\s]+/", $parts[0]);
//print_r($e_type);
			$e_date=preg_split("/[\s.]+/", trim($parts[1]));
//print_r($e_date);
			$e_time_max=preg_split("/[\s:]+/", trim($parts[2]));
//print_r($e_time_max);
			$e_time_max_gm=gmmktime((int)$e_time_max[0], (int)$e_time_max[1], (int)$e_time_max[2], $e_date[1], $e_date[0], $e_date[2]);
			if ($this->month==date('n', $e_time_max_gm) AND $this->year==date('Y', $e_time_max_gm)) {
//print_r($e_time_max_gm);
				$e_day = $this->critical_sr(true, $e_time_max_gm);

				if (trim($e_type[0]) == 'total') {	$this->days_arr[$e_day]->me['type'] = 3;	}
				elseif (trim($e_type[0]) == 'penumb.') {	$this->days_arr[$e_day]->me['type'] = 6;	}
				elseif (trim($e_type[0]) == 'partial') {	$this->days_arr[$e_day]->me['type'] = 5;	}
				$this->days_arr[$e_day]->me['max'] = $e_time_max_gm;
				$this->days_arr[$e_day]->me['saros'] = substr($parts[4],6,7);
				$e_times=preg_split("/[\s]+/", trim($get_em[$i+1]));
//print_r($e_times);
				$e_pu1=preg_split("/[\s:]+/", trim($e_times[0]));
				if (trim($e_pu1[0])!='-') {
					$this->days_arr[$e_day]->me['pu1']=gmmktime((int)$e_pu1[0], (int)$e_pu1[1], (int)$e_pu1[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->me['pu1']>$e_time_max_gm) {	$this->days_arr[$e_day]->me['pu1']=gmmktime((int)$e_pu1[0], (int)$e_pu1[1], (int)$e_pu1[2], $e_date[1], $e_date[0]-1, $e_date[2]);	}
					}
				$e_p1=preg_split("/[\s:]+/", trim($e_times[1]));
				if (trim($e_p1[0])!='-') {
					$this->days_arr[$e_day]->me['p1']=gmmktime((int)$e_p1[0], (int)$e_p1[1], (int)$e_p1[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->me['p1']>$e_time_max_gm) {	$this->days_arr[$e_day]->me['p1']=gmmktime((int)$e_p1[0], (int)$e_p1[1], (int)$e_p1[2], $e_date[1], $e_date[0]-1, $e_date[2]);	}
					}
				$e_t1=preg_split("/[\s:]+/", trim($e_times[2]));
				if (trim($e_t1[0])!='-') {
					$this->days_arr[$e_day]->me['t1']=gmmktime((int)$e_t1[0], (int)$e_t1[1], (int)$e_t1[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->me['t1']>$e_time_max_gm) {	$this->days_arr[$e_day]->me['t1']=gmmktime((int)$e_t1[0], (int)$e_t1[1], (int)$e_t1[2], $e_date[1], $e_date[0]-1, $e_date[2]);	}
					}
				$e_t2=preg_split("/[\s:]+/", trim($e_times[3]));
				if (trim($e_t2[0])!='-') {
					$this->days_arr[$e_day]->me['t2']=gmmktime((int)$e_t2[0], (int)$e_t2[1], (int)$e_t2[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->me['t2']<$e_time_max_gm) {	$this->days_arr[$e_day]->me['t2']=gmmktime((int)$e_t2[0], (int)$e_t2[1], (int)$e_t2[2], $e_date[1], $e_date[0]+1, $e_date[2]);	}
					}
				$e_p2=preg_split("/[\s:]+/", trim($e_times[4]));
				if (trim($e_p2[0])!='-') {
					$this->days_arr[$e_day]->me['p2']=gmmktime((int)$e_p2[0], (int)$e_p2[1], (int)$e_p2[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->me['p2']<$e_time_max_gm) {	$this->days_arr[$e_day]->me['p2']=gmmktime((int)$e_p2[0], (int)$e_p2[1], (int)$e_p2[2], $e_date[1], $e_date[0]+1, $e_date[2]);	}
					}
				$e_pu2=preg_split("/[\s:]+/", trim($e_times[5]));
				if (trim($e_pu2[0])!='-') {
					$this->days_arr[$e_day]->me['pu2']=gmmktime((int)$e_pu2[0], (int)$e_pu2[1], (int)$e_pu2[2], $e_date[1], $e_date[0], $e_date[2]);
					if ($this->days_arr[$e_day]->me['pu2']<$e_time_max_gm) {	$this->days_arr[$e_day]->me['pu2']=gmmktime((int)$e_pu2[0], (int)$e_pu2[1], (int)$e_pu2[2], $e_date[1], $e_date[0]+1, $e_date[2]);	}
					}
				if (isset($this->days_arr[$e_day-1]) AND isset($this->days_arr[$e_day+1])) {	//выборка всех moonrise и moonset в предыдущем, текущем и следующем днях
					for ($k=-1;$k<2;$k++) {
						if (isset($this->days_arr[$e_day+$k]->mr)) {
							$mr_count=count($this->days_arr[$e_day+$k]->mr);
							for ($i=0;$i<$mr_count;$i++) {
								$m[]=array('time'=>&$this->days_arr[$e_day+$k]->mr[$i], 'type'=>0);
								}
							}
						if (isset($this->days_arr[$e_day+$k]->ms)) {
							$ms_count=count($this->days_arr[$e_day+$k]->ms);
							for ($i=0;$i<$ms_count;$i++) {
								$m[]=array('time'=>&$this->days_arr[$e_day+$k]->ms[$i], 'type'=>1);
								}
							}
						}
//print_r($m);
					$by = 'time'; 
					usort($m, function($first, $second) use( $by ) {	//сортировка по времени, чтоб убрать ошибки 
					if ($first[$by]>$second[$by]) { return 1; }
					elseif ($first[$by]<$second[$by]) { return -1; }
					return 0;
					});
//print_r($m);
					$m_count=count($m);
					for ($i=$r_temp=0;$i<$m_count;$i++) {	//создание массива из пар 0-восход, 1-заход луны
						if ($m[$i]['type']==0) {	$r_temp=$m[$i]['time'];	}
						elseif ($m[$i]['type']==1) {
							if ($r_temp==0) {	continue;	}
							else {
								$mrs[]=array(0=>$r_temp, 1=>$m[$i]['time']);
								//date('d.m.y H:i:s', 
								$r_temp=0;
								}
							}
						}
//print_r($mrs);
					$range_temp = $this->moon_ecl_loc($mrs, $this->days_arr[$e_day]->me['pu1'], $this->days_arr[$e_day]->me['pu2']);
					if ($range_temp[0]!=0) {
						$this->days_arr[$e_day]->me['typel'] = 6; //penumbral
						$this->days_arr[$e_day]->me['pul'] = $range_temp;
						if (isset($this->days_arr[$e_day]->me['p1']) AND isset($this->days_arr[$e_day]->me['p2'])) {
							$range_temp = $this->moon_ecl_loc($mrs, $this->days_arr[$e_day]->me['p1'], $this->days_arr[$e_day]->me['p2']);
							if ($range_temp[0]!=0) {
								$this->days_arr[$e_day]->me['typel'] = 5; //partial
								$this->days_arr[$e_day]->me['pl'] = $range_temp;
								if (isset($this->days_arr[$e_day]->me['t1']) AND isset($this->days_arr[$e_day]->me['t2'])) {
									$range_temp = $this->moon_ecl_loc($mrs, $this->days_arr[$e_day]->me['t1'], $this->days_arr[$e_day]->me['t2']);
									if ($range_temp[0]!=0) {
										$this->days_arr[$e_day]->me['typel'] = 3; //total
										$this->days_arr[$e_day]->me['tl'] = $range_temp;
										}
									}
								}
							}
						}
					}
				}
			else {
				break;
				}
			}
		}
	
	public function weekday () {
		reset($this->days_arr);
		while($day=each($this->days_arr)) {
			$this->days_arr[$day['key']]->w = date('N', mktime(12,0,0,$this->month,$day['key'],$this->year));
			}
		}
/*	public function masa_array ($masa, $type) {
		if (isset($this->masa_arr[$type])) {
			if ($masa != end($this->masa_arr[$type])) {$this->masa_arr[$type][]=$masa;}
			}
		else {$this->masa_arr[$type][]=$masa;}
		}
*/
	private function moon_ecl_loc (&$mrs, $beg, $end) {
		$mrs_count=count($mrs);
		for ($i=0;$i<$mrs_count;$i++) {
			if ($end>$mrs[$i][0] AND $beg<$mrs[$i][1]) {
				if ($mrs[$i][0]>$beg) $beg=$mrs[$i][0];
				if ($mrs[$i][1]<$end) $end=$mrs[$i][1];
				return array ($beg,$end);
				}
			}
		return array (0,0);
		}
		
	public function critical_sr($sr_fix, $date) {		
		$tithi_day = date('j', $date);
		$tithi_month = date('n', $date);
		$tithi_year = date('Y', $date);
		$month_year = $tithi_year*100+$tithi_month;
		$year_day = date("z", $date);
		if ($month_year==$this->main_month_year) {	$counted_day_number = $tithi_day;	}
		elseif ($this->main_month_year < $month_year) {
			if ($this->first_day<$year_day) {	$counted_day_number = $year_day + 1 - $this->first_day;	}
			else {	$counted_day_number = ($year_day+1) + (date("z", gmmktime(12, 0, 0, 12, 31, $this->year)) - $this->first_day)+1;	}	//попало на следующий год
			}
		elseif ($this->main_month_year > $month_year) {
			if ($this->first_day>$year_day) {	$counted_day_number = ($year_day+1) - $this->first_day;	}
			else {	$counted_day_number = $year_day - date("z", gmmktime(12, 0, 0, 12, 31, $this->year-1)) - $this->first_day;	}	//попало на предыдущий год
			}
			if (isset($this->days_arr[$counted_day_number]->sr[0]) AND $sr_fix) {
			if ($this->days_arr[$counted_day_number]->sr[0]>$date) {	// дата титх по восходу солнца
				$tithi_day = date('j', $date - 86400);	// 86400=24часа  если титхи до восхода солнца, то
				$tithi_month = date('n', $date - 86400);
				$year_day = date("z", $date - 86400);
				$month_year = $tithi_year*100+$tithi_month;
				if ($month_year==$this->main_month_year) {	$counted_day_number = $tithi_day;	}
				elseif ($this->main_month_year < $month_year) {
					if ($this->first_day<$year_day) {	$counted_day_number = $year_day + 1 - $this->first_day;	}
					else {	$counted_day_number = ($year_day+1) + (date("z", gmmktime(12, 0, 0, 12, 31, $this->year)) - $this->first_day)+1;	}	//попало на следующий год
					}
				elseif ($this->main_month_year > $month_year) {
					if ($this->first_day>$year_day) {	$counted_day_number = ($year_day+1) - $this->first_day;	}
					else {	$counted_day_number = $year_day - date("z", gmmktime(12, 0, 0, 12, 31, $this->year-1)) - $this->first_day;	}	//попало на предыдущий год
					}
				}
			}
		return $counted_day_number;
		}
	
	public function fix_date($day, $month, $year) {
		if ($day==0) {
			$utdate = gmmktime(12, 0, 0, $month, 1, $year);
			return array('day' => $this->day_count_foo($month, $year), 'month' => gmdate('n', $utdate), 'year' => gmdate('Y', $utdate));		
			}
		else {
			$utdate = gmmktime(12, 0, 0, $month, $day, $year);
			return array('day' => gmdate('j', $utdate), 'month' => gmdate('n', $utdate), 'year' => gmdate('Y', $utdate));
			}
		}
	
	public function day_count_foo($month, $year) {
		$utdate = gmmktime(12, 0, 0, $month+1, 0, $year);
		return gmdate('j', $utdate);
	}
	
	public function calendarYear($tee)	{
		$jdtee = $this->unixtojd_hms(gmdate('Y', $tee), gmdate('n', $tee), gmdate('j', $tee), gmdate('G', $tee), gmdate('i', $tee), gmdate('s', $tee));
		$rdtee = $jdtee - 1721425.5;
		$OHEPOCH = -1132958;
		$swestring = $GLOBALS["swetest"]." -p0 -bj$jdtee -head -edir".$GLOBALS["sweephe"]."/ -fl -sid1";
		exec($swestring ,$get_sm);
if ($this->test) {	print_r($swestring);	print_r($get_sm);	}
		$solarLongitude=(float)substr($get_sm[0],1,11);
		return round(($rdtee - $OHEPOCH) / 365.2587564814815 - $solarLongitude / 360);
		}
	public function fix360($v) { //clear
		while($v < 0.0) {$v += 360.0;}
		while($v > 360.0) {$v -= 360.0;}
		return $v;
		}
	public function jdtounix_hms($jd) { // конвертирует Julian врямя в unix с H M S
		$z1 = $jd + 0.5;
		$z2 = floor($z1);
		$f = $z1 - $z2;
		if($z2 < 2299161)$a = $z2;
		else {
			$alf = floor(($z2 - 1867216.25)/36524.25);
			$a = $z2 + 1 + $alf - floor($alf/4);
			}
		$b = $a + 1524;
		$c = floor(($b - 122.1)/365.25);
		$d = floor(365.25*$c);
		$e = floor(($b - $d)/30.6001);
		$days = $b - $d - floor(30.6001*$e) + $f;
		$day = floor($days);
		if($e < 13.5)$month = $e - 1;
		else $month = $e - 13;
		if($month > 2.5)$year = $c - 4716;
		if($month < 2.5)$year = $c - 4715;
		$hh1 = ($days - $day)*24;
		$khr = floor($hh1);
		$kmin = $hh1 - $khr;
		$ksek = $kmin*60;
		$kmin = floor($ksek);
		$ksek = floor(($ksek - $kmin)*60);
		$date = gmmktime($khr, $kmin, $ksek, $month, $day, $year);
		return $date;
		}
	public function unixtojd_hms($y, $m, $d, $h, $mn, $s ) {
		if( $m > 2 ) {
			$jy = $y;
			$jm = $m + 1;
			}
		else {
			$jy = $y - 1;
			$jm = $m + 13;
			}
		$intgr = floor(floor(365.25*$jy) + floor(30.6001*$jm) + $d + 1720995);
		//check for switch to Gregorian calendar
		$gregcal = 15 + 31*( 10 + 12*1582 );
		if( $d + 31*($m + 12*$y) >= $gregcal ) {
			$ja = floor(0.01*$jy);
			$intgr += 2 - $ja + floor(0.25*$ja);
			}
		//correct for half-day offset
		$dayfrac = $h/24.0 - 0.5;
		if( $dayfrac < 0.0 ) {
			$dayfrac += 1.0;
			--$intgr;
			}
		//now set the fraction of a day
		$frac = $dayfrac + ($mn + $s/60.0)/60.0/24.0;
		//round to nearest second
		$jd0 = ($intgr + $frac)*100000;
		$jd  = floor($jd0);
		if( $jd0 - $jd > 0.5 ) ++$jd;
		return $jd/100000;
		}
	}
?>