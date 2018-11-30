<?php
error_reporting(E_ALL);
class holydays { // 
	public $holy;		// массив от 1 до последнего дня месяца, содержит события дня
	private $panch;
	private $initial;
	private $uniq;
	private $hsame;
	private $range;	// массив 

	function holydays (&$panch, &$initial) {
		$this->panch = &$panch;
		$this->initial = &$initial;
//print_r($this->panch);
		$mysql_link_holy = new mysqli($GLOBALS['mysql_server'], $GLOBALS['mysql_user'], $GLOBALS['mysql_password'], $GLOBALS['dbname']);
		$mysql_link_holy->set_charset("utf8");
		
//		$first_calend_day = gmmktime(0, 0, 0, $this->initial->month, 1, $this->initial->year);
//		$last_calend_day =  gmmktime(24, 0, 0, $this->initial->month, $this->panch->day_count, $this->initial->year);
//print_r($this->panch->tithi);
		
		// Взять список праздников из базы и их правила
		// создать объект каждого new holyday(правила) с конструктором 
		$string_h = "SELECT * FROM holyday WHERE owner_id=0 AND id>2;";
		$query_h = $mysql_link_holy->query($string_h);
		$this->uniq=1; // - идентификатор событий
		while($rule = $query_h->fetch_array(MYSQLI_ASSOC)){
//print_r($rule);
			// распарсить правила на переменные
			if ($rule['enable']!==NULL AND $rule['enable']!=0)	{
				$h['id'] = $rule['id'];
				if (isset($rule['m_type'])) {
				$h['m_type'] = $rule['m_type'];	// правило по календарю: 0-амавасьянт, 1-пурнимант
					}
				else {	$h['m_type']=$this->initial->masa_type;	}
				if ($h['m_type'] == 0) { $h['masa_type'] = 'am'; $h['year_type'] = 'ay'; }
				elseif ($h['m_type'] == 1) { $h['masa_type'] = 'pm'; $h['year_type'] = 'py'; }
//				elseif ($h['m_type'] == 2)	{} добавить солнечный календарь
//добавить солнечный календарь
				$h['masa'] = $rule['masa'];	// если любой месяц, то $h['masa'] = 0;
				//$h['week'] = $rule['week'];
				if (isset($rule['b_tithi'])) {
					$h['b_ntithi'] = $rule['b_tithi'];
					$h['b_paksha'] = $rule['b_paksha'];
					$h['spec'] = $rule['special']; // точка отсчета (восход солнца, заход ... луны)
					$h['sp_range'] = $rule['sp_range'];
					$h['kshaya'] = $rule['kshaya'];
					$h['adhika'] = $rule['adhika'];
					if (!isset($rule['e_tithi'])) {
						$h['e_ntithi'] = $rule['b_tithi'];
						$h['e_paksha'] = $rule['b_paksha'];
						}
					else {
						$h['e_ntithi'] = $rule['e_tithi'];
						$h['e_paksha'] = $rule['e_paksha'];
						}
					}
					
				if (isset($rule['week'])) { // день недели
					for ($i=0;$i<5;$i++) {
						if ($rule['week'][$i]!=0) {
							if (!isset($h['week'])) {	$h['week'] = $rule['week'][$i];	}
							$h['week_arr'][] = true;
							}
						else {	$h['week_arr'][] = false;	}
						}
					}
//			$h_end_spec = $rule['e_special']; // точка отсчета (восход солнца, заход ... луны)
				// провести фильтрацию данных из БД на предмет инъекций
			
				// парсить перебором по массиву $panch->tithi
				// можно закешить номера месяцев при первом проходе в переменную $this->masa_arr
				// возможно придется создать $panch->&sun/moonrise/set чтоб их тоже парсить перебором
				// воткнуть проверку $h['masa'] на диапазон $this->panch->masa_arr?
				
				// тест - простейшая выборка только по титхи с восходом солнца
				if ($h['m_type']==0 OR $h['m_type']==1) {
					$this->hsame = 0;	//h - идентификатор однотипных событий
					$this->range[$this->hsame] = array(0,0);
					$res_hc = $this->holydays_calc($h, 0);
					if ((isset($h['b_ntithi']) AND $h['b_ntithi']!==$h['e_ntithi']) OR (isset($h['week']))) { // диапазонное событие, фикс прогалов и переползаний в соседний месяц
						if ($res_hc[0]==true) {	$this->holydays_calc($h, -1);	} // поиск события в предыдущем месяце
						if ($res_hc[1]==true) {	$this->holydays_calc($h, 1);	} // поиск события в следующем месяце
						if ($this->holy[$this->uniq]['days']!=NULL) {
							ksort($this->holy[$this->uniq]['days']);
							}
						/*foreach ($this->holy[$this->uniq]['days'] as $key=>$value) { 
							$month = date('n', $key);
							$year = date('Y', $key);
							$this->holy[$this->uniq]['days'][$key]['month'] = $month;
							$this->holy[$this->uniq]['days'][$key]['year'] = $year;
							}*/
						}
					}
				}
			unset($h);
			}
		// уничтожить объект, взять следующий
		
		$string_h = "SELECT * FROM holyday_sm WHERE owner_id=0;";
		$query_h = $mysql_link_holy->query($string_h);
		while($rule = $query_h->fetch_array(MYSQLI_ASSOC)){
//print_r($rule);
			if ($rule['enable']!==NULL AND $rule['enable']!=0)	{
				$h['id'] = $rule['id'];
				$h['month'] = $rule['month'];
				$h['day'] = $rule['day'];
				$this->holydays_calc_sm($h);
				}
			}
		$mysql_link_holy->close();
		$this->ekadashi();
//print_r($this->holy);
		foreach ($this->holy as $ukey=>$uvalue) {
			$holy_t = $uvalue['days'];
			$this->holy[$ukey]['count']=count($this->holy[$ukey]['days']);
			unset($this->holy[$ukey]['days']);
			if (isset($uvalue['range']) AND $uvalue['range']) {
				reset($holy_t);
				$key = key($holy_t);
				$value = current($holy_t);
				$day_t = $key-86400;
				$this->holy[$ukey]['days'][0] = array('day'=>date('j', $key), 'month'=>date('n', $key),	'year'=>date('Y', $key), 'm'=>$value['m'], 'l'=>$value['l']);
				end($holy_t);
				$key = key($holy_t);
				$value = current($holy_t);
				$this->holy[$ukey]['days'][1] = array('day'=>date('j', $key), 'month'=>date('n', $key),	'year'=>date('Y', $key), 'm'=>$value['m'], 'l'=>$value['l']);
				foreach ($holy_t as $key=>$value) {
					$day_t = $day_t+86400; //фикс выпавших из последовательности дней
					if ($key!=$day_t) {
						$day = date('j', $day_t);
						$month = date('n', $day_t);
						if ($month == $initial->month) {	$this->panch->days_arr[$day]->holy[] = $ukey;	}
						$day_t = $day_t+86400;
						}
					$day = date('j', $key);
					$month = date('n', $key);
					if ($month == $initial->month) {	$this->panch->days_arr[$day]->holy[] = $ukey;	}
					}
				}
			else {
				foreach ($holy_t as $key=>$value) { // перебор всех дней события, добавление в календарь, возврат к нормальным номерам дней
					$day = date('j', $key);
					$month = date('n', $key);
					$year = date('Y', $key);
					if ($month == $initial->month) {	$this->panch->days_arr[$day]->holy[] = $ukey;	}
					$this->holy[$ukey]['days'][$day] = $value;
					}
				$this->holy[$ukey]['count']=count($this->holy[$ukey]['days']);
				}
			}
		}
	
	function holydays_calc ($h, $d) {
		$month = $this->panch->fix_date[$d]['month'];
		$year = $this->panch->fix_date[$d]['year'];
		$tithi_arr_size = count($this->panch->tithi);
		$maybe_prev = false;
		$maybe_next = false;
		if (isset($h['b_ntithi'])) {
			for ($i=1;$i<$tithi_arr_size;$i++) { // начало с 1 чтоб не было проблем с отсутствующим -1 элементом
				if ($this->panch->tithi[$i]['month']==$month AND $this->panch->tithi[$i]['year']==$year) {
					if (isset($this->panch->days_arr[$this->panch->tithi[$i]['day']]->sr[0]) AND isset($this->panch->days_arr[$this->panch->tithi[$i]['day']]->ss[0])) {
						if ((($this->panch->tithi[$i][$h['masa_type']]==$h['masa'] AND $this->panch->tithi[$i]['l']==0) OR !isset($h['masa'])) AND (($this->panch->tithi[$i]['n']>=$h['b_ntithi'] AND ($h['b_paksha']==$this->panch->tithi[$i]['p'] OR !isset($h['b_paksha']))) AND ($this->panch->tithi[$i]['n']<=$h['e_ntithi'] AND ($h['e_paksha']==$this->panch->tithi[$i]['p'] OR !isset($h['e_paksha']))))) {
							if ($h['spec']==0 OR NULL) { $tithi_space = $this->sunrise($this->panch->tithi[$i-1]['d'], $this->panch->tithi[$i]['d']); }
							if ($h['spec']==1) { $tithi_space = $this->moonrise($this->panch->tithi[$i-1]['d'], $this->panch->tithi[$i]['d']); }
							if ($h['spec']==2) { $tithi_space = $this->pradosha($this->panch->tithi[$i-1]['d'], $this->panch->tithi[$i]['d']); }
							if ($h['spec']==3) { $tithi_space = $this->ratrimana($this->panch->tithi[$i-1]['d'], $this->panch->tithi[$i]['d']); }
							if ($h['spec']==4) { $tithi_space = $this->madhyahana($this->panch->tithi[$i-1]['d'], $this->panch->tithi[$i]['d']); }
							if ($tithi_space==false || !isset($tithi_space)) continue;	// конец расчета, если нет данных для расчета из-за сбоя
							$tithi_space_count = count($tithi_space);
							if ($tithi_space_count == 3) {	$base_time = $tithi_space[1];	}
							elseif ($tithi_space_count == 2) {  //Kshaya (Upari)
								if ($h['kshaya']==1) {	$base_time = $tithi_space[0];	}
								elseif ($h['kshaya']==2 or $h['kshaya']==NULL) {	$base_time = $tithi_space[1];	}
								elseif ($h['kshaya']==3) {	$base_time = $this->panch->tithi[$i-1]['d'];	}
								elseif ($h['kshaya']==4) {	$base_time = $this->panch->tithi[$i]['d'];	}
								}
							elseif ($tithi_space_count == 4) {  // Adhika
								if ($tithi_space[2][2]==$tithi_space[1][2] OR $h['sp_range']==NULL OR $h['sp_range']==1)	{
									if ($h['adhika']==NULL)	{	$base_time = $tithi_space[1];	}
									elseif ($h['adhika']==1)	{	$base_time = $tithi_space[0];	}
									elseif ($h['adhika']==2)	{	$base_time = $tithi_space[2];	}
									elseif ($h['adhika']==3)	{	$base_time = $tithi_space[3];	}
									}
								elseif ($tithi_space[2][2]<$tithi_space[1][2])	{	$base_time = $tithi_space[1];	}
								elseif ($tithi_space[1][2]<$tithi_space[2][2])	{	$base_time = $tithi_space[2];	}
								}
							if ($h['sp_range']==NULL) {
								if (isset($base_time[1])) {	$day_begin = $this->day_begin($base_time[1]);	} 
								else {	$day_begin = $this->day_begin($base_time);	}
								$day_begin_m = date('n', $day_begin);
								if ($day_begin_m == $month) {
									$day_begin_d = date('j', $day_begin);
									if (isset($this->holy[$this->uniq]) AND $this->holy[$this->uniq]['id'] != $h['id'] AND $h['b_ntithi']!==$h['e_ntithi']) {
										$this->uniq++;
										$this->holy[$this->uniq]['range'] = true;
										}
									elseif (isset($this->holy[$this->uniq]) AND $h['b_ntithi']==$h['e_ntithi']) {
										$this->uniq++;
										}
									$ddd = gmmktime(12, 0, 0, $month, $day_begin_d, $year);
									$this->holy[$this->uniq]['id'] = $h['id'];
									$this->holy[$this->uniq]['days'][$ddd]['m'] = $this->panch->days_arr[$day_begin_d]->ti[0][$h['masa_type']];
									$this->holy[$this->uniq]['days'][$ddd]['l'] = $this->panch->days_arr[$day_begin_d]->ti[0]['l'];
									if ($day_begin_d<=2) {	$maybe_prev = true;	}
									elseif ($day_begin_d>=$this->panch->fix_date[$d]['day']-1) {	$maybe_next = true;	}
									}
								}
							elseif ($h['sp_range']==1) {	
								$day_begin = $this->day_begin($base_time);
								$day_begin_m = date('n', $day_begin);
								$day_begin_y = date('Y', $day_begin);
								if ($day_begin_m == $month) {
									$day_begin_d = date('j', $day_begin);
									if (isset($this->holy[$this->uniq]) AND $this->holy[$this->uniq]['id'] != $h['id'] AND $h['b_ntithi']!==$h['e_ntithi']) {
										$this->uniq++;
										$this->holy[$this->uniq]['range'] = true;
										}
									elseif (isset($this->holy[$this->uniq]) AND $h['b_ntithi']==$h['e_ntithi']) {
										$this->uniq++;
										}
									$ddd = gmmktime(12, 0, 0, $month, $day_begin_d, $year);
									$this->holy[$this->uniq]['id'] = $h['id'];
									$this->holy[$this->uniq]['days'][$ddd] = array('from'=>$day_begin, 'till'=>$base_time);
									$this->holy[$this->uniq]['days'][$ddd]['m'] = $this->panch->days_arr[$day_begin_d]->ti[0][$h['masa_type']];
									$this->holy[$this->uniq]['days'][$ddd]['l'] = $this->panch->days_arr[$day_begin_d]->ti[0]['l'];
									if ($day_begin_d<=2) {	$maybe_prev = true;	}
									elseif ($day_begin_d>=$this->panch->fix_date[$d]['day']-1) {	$maybe_next = true;	}
			//							$this->holy_arr[$day_begin_m][$day_begin_d]['id'] = $h['id'];
			//							$this->holy_arr[$day_begin_m][$day_begin_d]['range'] = array($day_begin, $base_time);
			//							$this->holy_arr[$day_begin_m][$day_begin_d]['range_text'] = array(date('d.m.y H:i:s', $day_begin), date('d.m.y H:i:s', $base_time));
									}
								}
							elseif ($h['sp_range']==2) {
								$day_begin = $this->day_begin($base_time[1]);
								$day_begin_m = date('n', $day_begin);
								$day_begin_y = date('Y', $day_begin);
								if ($day_begin_m == $month) {
									$day_begin_d = date('j', $day_begin);
									if (isset($this->holy[$this->uniq]) AND $this->holy[$this->uniq]['id'] != $h['id'] AND $h['b_ntithi']!==$h['e_ntithi']) {
										$this->uniq++;
										$this->holy[$this->uniq]['range'] = true;
										}
									elseif (isset($this->holy[$this->uniq]) AND $h['b_ntithi']==$h['e_ntithi']) {
										$this->uniq++;
										}
									$ddd = gmmktime(12, 0, 0, $month, $day_begin_d, $year);
									$this->holy[$this->uniq]['id'] = $h['id'];
									$this->holy[$this->uniq]['days'][$ddd]= array('from'=>$base_time[0], 'till'=>$base_time[1]);
									$this->holy[$this->uniq]['days'][$ddd]['m'] = $this->panch->days_arr[$day_begin_d]->ti[0][$h['masa_type']];
									$this->holy[$this->uniq]['days'][$ddd]['l'] = $this->panch->days_arr[$day_begin_d]->ti[0]['l'];
									if ($day_begin_d<=2) {	$maybe_prev = true;	}
									elseif ($day_begin_d>=$this->panch->fix_date[$d]['day']-1) {	$maybe_next = true;	}
			//							$this->holy_arr[$day_begin_m][$day_begin_d]['id'] = $h['id'];
			//							$this->holy_arr[$day_begin_m][$day_begin_d]['range'] = array($base_time[0], $base_time[1]);
			//							$this->holy_arr[$day_begin_m][$day_begin_d]['range_text'] = array(date('d.m.y H:i:s', $base_time[0]), date('d.m.y H:i:s', $base_time[1]));
									}
								}
							}


							
						}
					}
				}
			}
		elseif (isset($h['week_arr'])) {		//можно сильно оптимизировать
			$this->uniq_w = true;
			if ($d==-1) {	$gap = -$this->panch->fix_date[-1]['day'];	}
			elseif ($d==1) {	$gap = $this->panch->fix_date[0]['day'];	}
			else {	$gap = 0;	}
			for ($i=1+$gap;$i<=$this->panch->fix_date[$d]['day']+$gap;$i++) {
//if ($h['id']==20) echo " i=$i ";			
				if (isset($this->panch->days_arr[$i]->w) AND $this->panch->days_arr[$i]->w==$h['week']) {
					if (isset($this->panch->days_arr[$i]->ti[0][$h['masa_type']]) AND $this->panch->days_arr[$i]->ti[0][$h['masa_type']]==$h['masa']) {
//if ($h['id']==20) echo " ! ";
						if ($this->panch->days_arr[$i]->ti[0]['p']==0 AND $this->panch->days_arr[$i]->ti[0]['n']==0) {	$tith=15;	}
						else {	$tith=$this->panch->days_arr[$i]->ti[0]['n'];	}
						if ($h['m_type']==1) {	// пурнимант
							$tnn = $this->panch->days_arr[$i]->ti[0]['p']*15+$tith;
							}
						elseif ($h['m_type']==0) {	// амавасьянт
							if ($this->panch->days_arr[$i]->ti[0]['p']==0) {	$paksh=1;	}
							else {	$paksh=0;	}
							$tnn = $paksh*15+$tith;
							}

						$wnum = 0;
//echo " id=".$h['id']." i=$i tnn=$tnn ";

						while ($tnn>0) {
							$tnn=$tnn-7;
							if ($tnn>0) $wnum++;
							}
//echo " wnum=$wnum ";
						if ($h['week_arr'][$wnum]) {
/*										if (isset($this->panch->days_arr[$i]->holy)) {
								$holy_num = count($this->panch->days_arr[$i]->holy);
								$this->panch->days_arr[$i]->holy[$holy_num]['id'] = $h['id'];
								$this->panch->days_arr[$i]->holy[$holy_num]['uniq'] = $this->uniq;
								}
							else {*/
							if ($this->uniq_w) {
								if (isset($this->holy[$this->uniq]) AND $this->holy[$this->uniq]['id'] != $h['id']) {	$this->uniq++;	}
								$this->uniq_w = false;
								}
							$this->holy[$this->uniq]['id'] = $h['id'];
							$ddd = gmmktime(12, 0, 0, $this->initial->month, $i, $this->initial->month);
//if ($h['id']==20) echo " i=$i ddd=$ddd ";
							$this->holy[$this->uniq]['days'][$ddd]['m'] = 0;
							$this->holy[$this->uniq]['days'][$ddd]['l'] = 0;
							$this->holy[$this->uniq]['days'][$ddd]['month'] = $month;
							$this->holy[$this->uniq]['days'][$ddd]['year'] = $year;
							//$this->holy[$this->uniq]['days'][$i]['month'] = 0;
							//$this->holy[$this->uniq]['days'][$i]['year'] = 0;
							//$this->panch->days_arr[$i]->holy[] = $this->uniq;
							if ($i<=7) {	$maybe_prev = true;	}
							elseif ($i>=$this->panch->fix_date[$d]['day']-6) {	$maybe_next = true;	}
							}
						}
					}
				}
			}

			//
			//создать отдельный цикл по перебору $this->panch->days_arr c if ($this->panch->days_arr->week и $this->panch->days_arr->tithi['masa'])
			//создать отдельный цикл по перебору солнечного календаря
		return array($maybe_prev, $maybe_next);
		}
		
	function holydays_calc_sm($h) {
		for ($i=1;$i<=$this->panch->fix_date[0]['day'];$i++) {
			if ($this->initial->month==$h['month'] AND $i==$h['day']) {
				$this->uniq++;
				$ddd = gmmktime(12, 0, 0, $h['month'], $h['day'], $this->initial->year);
				$this->holy[$this->uniq]['id'] = $h['id'];
				$this->holy[$this->uniq]['days'][$ddd]['m'] = $this->initial->masa_type;
				$this->holy[$this->uniq]['days'][$ddd]['l'] = $this->panch->days_arr[$i]->ti[0]['l'];
				}
			}
		}
		
	function ekadashi() {  // http://www.gopal.home.sk/gcal/docs/GCalVaisnavaCalculation.pdf
		$tithi_arr_size = count($this->panch->tithi);
		//$maybe_prev = false;
		//$maybe_next = false;
//print_r($this->panch->tithi);
		$pakshavardhini = false;
		for ($i=$tithi_arr_size-1;$i>1;$i--) { // начало с 1 чтоб не было проблем с отсутствующим -1 элементом
			if (($this->panch->tithi[$i]['month']==$this->panch->fix_date[-1]['month'] AND $this->panch->tithi[$i]['year']==$this->panch->fix_date[-1]['year']) OR ($this->panch->tithi[$i]['month']==$this->panch->fix_date[1]['month'] AND $this->panch->tithi[$i]['year']==$this->panch->fix_date[1]['year']) OR ($this->panch->tithi[$i]['month']==$this->panch->fix_date[0]['month'] AND $this->panch->tithi[$i]['year']==$this->panch->fix_date[0]['year'])) {
				if (isset($this->panch->days_arr[$this->panch->tithi[$i]['day']]->sr[0]) AND isset($this->panch->days_arr[$this->panch->tithi[$i]['day']]->ss[0]) AND isset($this->panch->days_arr[$this->panch->tithi[$i]['day']+2]->na[0])) {
					if ($this->panch->tithi[$i]['n']==0 OR $this->panch->tithi[$i]['n']==15) {
						if (!isset($this->panch->tithi[$i]['d'])) {	$pakshavardhini = true;	}	// Адхика пурнима или амавасья
						else {	$pakshavardhini = false;	}
						}
					if ($this->panch->tithi[$i]['n']==11) {
						$t_ek_sr = $this->panch->days_arr[$this->panch->tithi[$i]['day']]->sr[0];
						$t_dv_sr = $this->panch->days_arr[$this->panch->tithi[$i+1]['day']]->sr[0];
						$t_dv_ss = $this->panch->days_arr[$this->panch->tithi[$i+1]['day']]->ss[0];
						$t_tr_sr = $this->panch->days_arr[$this->panch->tithi[$i+1]['day']+1]->sr[0];
						$t_tr_ss = $this->panch->days_arr[$this->panch->tithi[$i+1]['day']+1]->ss[0];
						$t_tr_13 = intval($t_tr_sr+($t_tr_ss-$t_tr_sr)/3);
						$t_dv_e = $this->panch->tithi[$i+1]['d'];
						$t_na_n = $this->panch->days_arr[$this->panch->tithi[$i+1]['day']]->na[0]['n'];
						$t_na_dv = !isset($this->panch->days_arr[$this->panch->tithi[$i+1]['day']]->na[0]['d']);
if ($this->initial->test) {	print_r("!!!");	print_r($this->panch->tithi[$i+1]['day']+2); print_r($t_na_tr=$this->panch->days_arr[$this->panch->tithi[$i+1]['day']+2]);	}
						if (isset($this->panch->days_arr[$this->panch->tithi[$i+1]['day']+1]->na[0]['d'])) {	$t_na_tr = $this->panch->days_arr[$this->panch->tithi[$i+1]['day']+1]->na[0]['d'];	}
						else {	$t_na_tr=$this->panch->days_arr[$this->panch->tithi[$i+1]['day']+2]->na[0]['d'];	}

						
						if ($this->panch->tithi[$i]['d0']<$t_ek_sr-5760) {	//Shuddha
							$ek['day'] = $this->panch->tithi[$i]['day'];
							$ek['n'] = 11;
							$ek['t'] = $this->panch->tithi[$i];
							$ek['b'][0] = $t_ek_sr-5760;
							//1
							if ($t_dv_e<$t_dv_sr+($t_dv_ss-$t_dv_sr)/3) {	$par_e = $t_dv_e;	}
							else {	$par_e = intval($t_dv_sr+($t_dv_ss-$t_dv_sr)/3);	}
							if ($this->panch->tithi[$i+1]['d0']+($t_dv_e-$this->panch->tithi[$i+1]['d0'])/4<$t_dv_sr) {
								$par_b = $t_dv_sr;
								}
							else {	$par_b = intval($this->panch->tithi[$i+1]['d0']+($t_dv_e-$this->panch->tithi[$i+1]['d0'])/4);	}
							if ($par_b<$par_e) {	$ek['e'][0]=$par_b;	$ek['e'][1]=$par_e;	}
							else {	$ek['e'][0]=$par_b;	}
							}
						else { // Viddha
							$ek['day'] = $this->panch->tithi[$i]['day']+1;
							$ek['n'] = 12;
							$ek['t'] = $this->panch->tithi[$i+1];
							$ek['b'][0] = $t_dv_sr-5760;
							//2
							if ($this->panch->tithi[$i+2]['d']<$t_tr_sr+($t_tr_ss-$t_tr_sr)/3) {	
								$par_e = $this->panch->tithi[$i+2]['d'];
								}
							else {	$par_e = intval($t_tr_sr+($t_tr_ss-$t_tr_sr)/3);	}
							$par_b = $t_tr_sr;
							$ek['e'][0]=$par_b;
							$ek['e'][1]=$par_e;
							}
						if ($this->panch->tithi[$i+1]['n']==12) { // махадвадаши
							if ($this->panch->tithi[$i]['day']+1<$this->panch->tithi[$i+1]['day']) {	//Унмилини Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 1;
								$ek['t'] = $this->panch->tithi[$i+1];
								$ek['b'][0] = $t_dv_sr-5760;
								//3
								if ($t_dv_e<$t_dv_sr+($t_dv_ss-$t_dv_sr)/3) {
									$par_e = $t_dv_e;
									}
								else {
									$par_e = intval($t_dv_sr+($t_dv_ss-$t_dv_sr)/3);
									}
								$par_b = $t_tr_sr;
								$ek['e'][0]=$par_b;
								$ek['e'][1]=$par_e;
								}
							else if ($t_na_n==7 AND $t_na_dv AND $t_dv_ss<$t_dv_e) {	//Пунарвасу накшатра, Джайа Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 5;
								$ek['t'] = $this->panch->tithi[$i+1];
								$ek['b'][0] = $t_dv_sr-5760;
								//7
								if ($t_dv_e>=$t_tr_sr AND $t_na_tr<=$t_dv_e AND $t_na_tr<=$t_tr_13) {
									$par_b = $t_na_tr;
									if ($t_dv_e<$t_tr_13) {	$par_e = $t_dv_e;	}
									else {	$par_e = $t_tr_13;	}
									}
								elseif ($t_dv_e>=$t_tr_sr AND $t_na_tr<=$t_dv_e AND $t_na_tr>$t_tr_13) {
									$par_b = $t_na_tr;
									$par_e = $t_dv_e;
									}
								elseif ($t_dv_e>=$t_tr_sr AND $t_na_tr>=$t_dv_e) {
									$par_b = $t_tr_sr;
									if ($t_dv_e<$t_tr_13) {	$par_e = $t_dv_e;	}
									else {	$par_e = $t_tr_13;	}
									}
								elseif ($t_dv_e<=$t_tr_sr AND $t_na_tr<=$t_tr_13) {
									$par_b = $t_na_tr;
									$par_e = $t_tr_13;
									}
								elseif ($t_dv_e<=$t_tr_sr AND $t_na_tr>$t_tr_13) {
									$par_b = $t_na_tr;
									}
								$ek['e'][0]=$par_b;
								if (isset($par_e)) $ek['e'][1]=$par_e;
								}
							elseif ($t_na_n==4 AND $t_na_dv AND $t_dv_ss<$t_dv_e) {	//Рохини накшатра, Джайанти Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 6;
								$ek['t'] = $this->panch->tithi[$i+1];
								$ek['b'][0] = $t_dv_sr-5760;
								//6
								if ($t_dv_e>=$t_tr_sr AND $t_na_tr<=$t_dv_e AND $t_na_tr<=$t_tr_13) {
									$par_b = $t_na_tr;
									if ($t_dv_e<$t_tr_13) {	$par_e = $t_dv_e;	}
									else {	$par_e = $t_tr_13;	}
									}
								elseif ($t_dv_e>=$t_tr_sr AND $t_na_tr<=$t_dv_e AND $t_na_tr>$t_tr_13) {
									$par_b = $t_na_tr;
									$par_e = $t_dv_e;
									}
								elseif ($t_dv_e>=$t_tr_sr AND $t_na_tr>=$t_dv_e) {
									$par_b = $t_tr_sr;
									if ($t_dv_e<$t_tr_13) {	$par_e = $t_dv_e;	}
									else {	$par_e = $t_tr_13;	}
									}
								elseif ($t_dv_e<=$t_tr_sr) {
									$par_b = $t_tr_sr;
									if ($t_na_tr<$t_tr_13) {	$par_e = $t_na_tr;	}
									else {	$par_e = $t_tr_13;	}
									}
								$ek['e'][0]=$par_b;
								$ek['e'][1]=$par_e;
								}
							elseif ($t_na_n==8 AND $t_na_dv AND $t_dv_ss<$t_dv_e) {	//Пушья накшатра, Папа Нашини Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 7;
								$ek['t'] = $this->panch->tithi[$i+1];
								$ek['b'][0] = $t_dv_sr-5760;
								//7
								if ($t_dv_e>=$t_tr_sr AND $t_na_tr<=$t_dv_e AND $t_na_tr<=$t_tr_13) {
									$par_b = $t_na_tr;
									if ($t_dv_e<$t_tr_13) {	$par_e = $t_dv_e;	}
									else {	$par_e = $t_tr_13;	}
									}
								elseif ($t_dv_e>=$t_tr_sr AND $t_na_tr<=$t_dv_e AND $t_na_tr>$t_tr_13) {
									$par_b = $t_na_tr;
									$par_e = $t_dv_e;
									}
								elseif ($t_dv_e>=$t_tr_sr AND $t_na_tr>=$t_dv_e) {
									$par_b = $t_tr_sr;
									if ($t_dv_e<$t_tr_13) {	$par_e = $t_dv_e;	}
									else {	$par_e = $t_tr_13;	}
									}
								elseif ($t_dv_e<=$t_tr_sr AND $t_na_tr<=$t_tr_13) {
									$par_b = $t_na_tr;
									$par_e = $t_tr_13;
									}
								elseif ($t_dv_e<=$t_tr_sr AND $t_na_tr>$t_tr_13) {
									$par_b = $t_na_tr;
									}
								$ek['e'][0]=$par_b;
								if (isset($par_e)) $ek['e'][1]=$par_e;
								}
							elseif ($t_na_n==22 AND $t_na_dv) {	//Шравана накшатра, Виджайа Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 8;
								$ek['t'] = $this->panch->tithi[$i+1];
								$ek['b'][0] = $t_dv_sr-5760;
								//6
								if ($t_dv_e>=$t_tr_sr AND $t_na_tr<=$t_dv_e AND $t_na_tr<=$t_tr_13) {
									$par_b = $t_na_tr;
									if ($t_dv_e<$t_tr_13) {	$par_e = $t_dv_e;	}
									else {	$par_e = $t_tr_13;	}
									}
								elseif ($t_dv_e>=$t_tr_sr AND $t_na_tr<=$t_dv_e AND $t_na_tr>$t_tr_13) {
									$par_b = $t_na_tr;
									$par_e = $t_dv_e;
									}
								elseif ($t_dv_e>=$t_tr_sr AND $t_na_tr>=$t_dv_e) {
									$par_b = $t_tr_sr;
									if ($t_dv_e<$t_tr_13) {	$par_e = $t_dv_e;	}
									else {	$par_e = $t_tr_13;	}
									}
								elseif ($t_dv_e<=$t_tr_sr) {
									$par_b = $t_tr_sr;
									if ($t_na_tr<$t_tr_13) {	$par_e = $t_na_tr;	}
									else {	$par_e = $t_tr_13;	}
									}
								$ek['e'][0]=$par_b;
								$ek['e'][1]=$par_e;
								}
							elseif ($t_tr_sr<$this->panch->tithi[$i+1]['d'] AND $this->panch->tithi[$i]['d0']<$t_ek_sr-5760) {	// Вьянджули Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 2;
								$ek['t'] = $this->panch->tithi[$i+1];
								$ek['b'][0] = $t_dv_sr-5760;
								//4
								$par_b = $t_tr_sr;
								if ($t_dv_e<$t_tr_sr+($t_tr_ss-$t_tr_sr)/3) {
									$par_e = $t_dv_e;
									}
								else {
									$par_e = intval($t_tr_sr+($t_tr_ss-$t_tr_sr)/3);
									}
								$ek['e'][0]=$par_b;
								$ek['e'][1]=$par_e;
								}
							elseif ($pakshavardhini) {	// Пакшавардини Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 4;
								$ek['t'] = $this->panch->tithi[$i+1];
								$ek['b'][0] = $t_dv_sr-5760;
								//1
								if ($t_dv_e<$t_dv_sr+($t_dv_ss-$t_dv_sr)/3) {	$par_e = $t_dv_e;	}
								else {	$par_e = intval($t_dv_sr+($t_dv_ss-$t_dv_sr)/3);	}
								if ($this->panch->tithi[$i+1]['d0']+($t_dv_e-$this->panch->tithi[$i+1]['d0'])/4<$t_dv_sr) {
									$par_b = $t_dv_sr;
								}
								else {	$par_b = intval($this->panch->tithi[$i+1]['d0']+($t_dv_e-$this->panch->tithi[$i+1]['d0'])/4);	}
								$ek['e'][0]=$par_b;
								if ($par_b<$par_e) $ek['e'][1]=$par_e;
								}
							elseif ($this->panch->tithi[$i]['day']==$this->panch->tithi[$i+1]['day']) {	//	Триспарша Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 3;
								$ek['t'] = $this->panch->tithi[$i+1];
								$ek['b'][0] = $t_ek_sr-5760;
								//5
								$par_b = $t_tr_sr;
								if ($this->panch->tithi[$i+2]['d']<$t_tr_sr+($t_tr_ss-$t_tr_sr)/3) {
									$par_e = $this->panch->tithi[$i+2]['d'];
									}
								else {
									$par_e = intval($t_tr_sr+($t_tr_ss-$t_tr_sr)/3);
									}
								$ek['e'][0]=$par_b;
								$ek['e'][1]=$par_e;
								}
							/*elseif ($this->panch->tithi[$i]['d']-$t_ek_sr<5760) {	//	Махадвадаши
								$ek['day'] = $this->panch->tithi[$i+1]['day'];
								$ek['n'] = 0;
								$ek['t'] = $this->panch->tithi[$i];
								}*/
							}
						}
					if (isset($ek['n']) AND $ek['day']>0 AND $ek['day']<=$this->panch->fix_date[0]['day']) {
//print_r($ek);
						$this->uniq++;
						if ($ek['t']['p']==0) {	$this->holy[$this->uniq]['id']=26;	}
						elseif ($ek['t']['p']==1) {	$this->holy[$this->uniq]['id']=27;	}
						$this->holy[$this->uniq]['count']=1;
						$this->holy[$this->uniq]['ek']=$ek['n'];
						$ddd = gmmktime(12, 0, 0, $this->panch->fix_date[0]['month'], $ek['day'], $this->panch->fix_date[0]['year']);
						$this->holy[$this->uniq]['days'][$ddd]['m']=$ek['t']['pm'];
						$this->holy[$this->uniq]['days'][$ddd]['l']=$ek['t']['l'];
						if (isset($ek['b'])) $this->holy[$this->uniq]['days'][$ddd]['b']=$ek['b'];
						if (isset($ek['e'])) $this->holy[$this->uniq]['days'][$ddd]['e']=$ek['e'];
						unset($ek);
//print_r($this->holy[$this->uniq]);
						}
					}
				}
			}
		}
		
	function day_begin ($event) {	// возвращает время восхода солнца перед event
		$sr_arr_size = count($this->panch->sunrise);
		for ($i=1;$i<$sr_arr_size-1;$i++) { // начало с 1 чтоб не было проблем с отсутствующим -1 элементом
			if ($this->panch->sunrise[$i-1]<=$event AND $event<$this->panch->sunrise[$i]) { $sr_cr = $this->panch->sunrise[$i-1]; break;}
			}
			if (isset($sr_cr)) {	return $sr_cr;	}
			else {	return false;	}
		}
	function sunrise ($beg, $end) {
		$sr_arr_size = count($this->panch->sunrise);
		for ($i=1;$i<$sr_arr_size-1;$i++) { // начало с 1 чтоб не было проблем с отсутствующим -1 элементом
			if ($this->panch->sunrise[$i-1]<=$beg AND $beg<$this->panch->sunrise[$i]) { $sr_cr[] = $this->panch->sunrise[$i-1]; }
			if (isset($sr_cr[0])) {
				if ($this->panch->sunrise[$i]>$end) {
					$sr_cr[] = $this->panch->sunrise[$i];
					break;
					}
				else {$sr_cr[] = $this->panch->sunrise[$i];}
				}
			}
			if (isset($sr_cr)) {	return $sr_cr;	}
			else {	return false;	}
		}
	function moonrise ($beg, $end) {
		$mr_arr_size = count($this->panch->moonrise);
		for ($i=1;$i<$mr_arr_size-1;$i++) { // начало с 1 чтоб не было проблем с отсутствующим -1 элементом
			if ($this->panch->moonrise[$i-1]<=$beg AND $beg<$this->panch->moonrise[$i]) { $sr_cr[] = $this->panch->moonrise[$i-1]; }
			if (isset($sr_cr[0])) {
				if ($this->panch->moonrise[$i]>$end) {
					$sr_cr[] = $this->panch->moonrise[$i];
					break;
					}
				else {$sr_cr[] = $this->panch->moonrise[$i];}
				}
			}
			if (isset($sr_cr)) {	return $sr_cr;	}
			else {	return false;	}
		}
	function pradosha ($beg, $end) {  // работает
		$ss_arr_size = count($this->panch->sunset);
		for ($i=1;$i<$ss_arr_size-1;$i++) { // начало с 1 чтоб не было проблем с отсутствующим -1 элементом
			if ($this->panch->sunset[$i-1]+2880<=$beg AND $beg<$this->panch->sunset[$i]+2880) { 
				$sr_cr1 = $this->panch->sunset[$i-1]-2880;
				$sr_cr2 = $this->panch->sunset[$i-1]+2880;
				$sr_cr[] = array ($sr_cr1, $sr_cr2, 0);
				}
			if (isset($sr_cr[0])) {
				if ($end<=$this->panch->sunset[$i]-2880) {
					$sr_cr1 = $this->panch->sunset[$i]-2880;
					$sr_cr2 = $this->panch->sunset[$i]+2880;
					$sr_cr[] = array ($sr_cr1, $sr_cr2, 0);
					break;
					}
				else {
					$sr_cr1 = $this->panch->sunset[$i]-2880;
					$sr_cr2 = $this->panch->sunset[$i]+2880;
					$space_beg = $this->panch->sunset[$i]-2880;
					$space_end = $this->panch->sunset[$i]+2880;
					if ($this->panch->sunset[$i]-2880<$beg AND $beg<$this->panch->sunset[$i]+2880) {	$space_beg = $beg;	}
					if ($this->panch->sunset[$i]-2880<$end AND $end<$this->panch->sunset[$i]+2880) {	$space_end = $end;	}
					$sr_cr[] = array ($sr_cr1, $sr_cr2, $space_end-$space_beg);
					}
				}
			}
			if (isset($sr_cr)) {	return $sr_cr;	}
			else {	return false;	}
		}
	function madhyahana ($beg, $end) {  //
		$sr_arr_size = count($this->panch->sunrise);
		$ss_arr_size = count($this->panch->sunset);
		$srss_gap = 0;
		//for ($i=0;$i<$ss_arr_size-1;$i++) { if ($this->panch->sunset[0]<$this->panch->sunrise[$i]) {	$srss_gap = $i;	break;	}	}
		for ($i=0;$i<$sr_arr_size-1;$i++) { if ($this->panch->sunrise[0]<$this->panch->sunset[$i]) {	$srss_gap = $i;	break;	}	}
		for ($i=1;$i<$ss_arr_size-1;$i++) { // начало с 1 чтоб не было проблем с отсутствующим -1 элементом
			$gap_b[0] = ceil(($this->panch->sunset[$i-1+$srss_gap] - $this->panch->sunrise[$i-1])*7/15);
			$gap_f[0] = ceil(($this->panch->sunset[$i-1+$srss_gap] - $this->panch->sunrise[$i-1])*8/15);
			$gap_b[1] = ceil(($this->panch->sunset[$i+$srss_gap] - $this->panch->sunrise[$i])*7/15);
			$gap_f[1] = ceil(($this->panch->sunset[$i+$srss_gap] - $this->panch->sunrise[$i])*8/15);
			if ($this->panch->sunrise[$i-1]+$gap_b[0]<=$beg AND $beg<$this->panch->sunrise[$i]+$gap_b[1]) { //первый элемент
				$sr_cr1 = $this->panch->sunrise[$i-1]+$gap_b[0];
				$sr_cr2 = $this->panch->sunrise[$i-1]+$gap_f[0];
				$sr_cr[] = array ($sr_cr1, $sr_cr2, 0);
				}
			if (isset($sr_cr[0])) {
				if ($end<=$this->panch->sunrise[$i]+$gap_b[1]) { //последний элемент
					$sr_cr1 = $this->panch->sunrise[$i]+$gap_b[1];
					$sr_cr2 = $this->panch->sunrise[$i]+$gap_f[1];
					$sr_cr[] = array ($sr_cr1, $sr_cr2, 0);
					break;
					}
				else {
					$sr_cr1 = $this->panch->sunrise[$i]+$gap_b[1];
					$sr_cr2 = $this->panch->sunrise[$i]+$gap_f[1];
					$space_beg = $this->panch->sunrise[$i]+$gap_b[1];
					$space_end = $this->panch->sunrise[$i]+$gap_f[1];
					if ($this->panch->sunrise[$i]+$gap_b[1]<$beg AND $beg<$this->panch->sunrise[$i]+$gap_f[1]) {	$space_beg = $beg;	}
					if ($this->panch->sunrise[$i]+$gap_b[1]<$end AND $end<$this->panch->sunrise[$i]+$gap_f[1]) {	$space_end = $end;	}
					$sr_cr[] = array ($sr_cr1, $sr_cr2, $space_end-$space_beg);
					}
				}
			}
			if (isset($sr_cr)) {	return $sr_cr;	}
			else {	return false;	}
		}
	function ratrimana ($beg, $end) {  // работает
		$sr_arr_size = count($this->panch->sunrise);
		$ss_arr_size = count($this->panch->sunset);
		$srss_gap = 0;
		for ($i=0;$i<$sr_arr_size-1;$i++) { if ($this->panch->sunset[0]<$this->panch->sunrise[$i]) {	$srss_gap = $i;	break;	}	}
		for ($i=1;$i<$ss_arr_size-1;$i++) { // начало с 1 чтоб не было проблем с отсутствующим -1 элементом
			$gap_b[0] = ceil(($this->panch->sunrise[$i-1+$srss_gap] - $this->panch->sunset[$i-1])*7/15);
			$gap_f[0] = ceil(($this->panch->sunrise[$i-1+$srss_gap] - $this->panch->sunset[$i-1])*8/15);
			$gap_b[1] = ceil(($this->panch->sunrise[$i+$srss_gap] - $this->panch->sunset[$i])*7/15);
			$gap_f[1] = ceil(($this->panch->sunrise[$i+$srss_gap] - $this->panch->sunset[$i])*8/15);
			if ($this->panch->sunset[$i-1]+$gap_b[0]<=$beg AND $beg<$this->panch->sunset[$i]+$gap_b[1]) { //первый элемент
				$sr_cr1 = $this->panch->sunset[$i-1]+$gap_b[0];
				$sr_cr2 = $this->panch->sunset[$i-1]+$gap_f[0];
				$sr_cr[] = array ($sr_cr1, $sr_cr2, 0);
				}
			if (isset($sr_cr[0])) {
				if ($end<=$this->panch->sunset[$i]+$gap_b[1]) { //последний элемент
					$sr_cr1 = $this->panch->sunset[$i]+$gap_b[1];
					$sr_cr2 = $this->panch->sunset[$i]+$gap_f[1];
					$sr_cr[] = array ($sr_cr1, $sr_cr2, 0);
					break;
					}
				else {
					$sr_cr1 = $this->panch->sunset[$i]+$gap_b[1];
					$sr_cr2 = $this->panch->sunset[$i]+$gap_f[1];
					$space_beg = $this->panch->sunset[$i]+$gap_b[1];
					$space_end = $this->panch->sunset[$i]+$gap_f[1];
					if ($this->panch->sunset[$i]+$gap_b[1]<$beg AND $beg<$this->panch->sunset[$i]+$gap_f[1]) {	$space_beg = $beg;	}
					if ($this->panch->sunset[$i]+$gap_b[1]<$end AND $end<$this->panch->sunset[$i]+$gap_f[1]) {	$space_end = $end;	}
					$sr_cr[] = array ($sr_cr1, $sr_cr2, $space_end-$space_beg);
					}
				}
			}
			if (isset($sr_cr)) {	return $sr_cr;	}
			else {	return false;	}
		}
	}

	
/*
function TMToTithi ($paksha, $ntithi, $masa) {
	return $paksha*15+$ntithi+30*$masa;
	}
function NtithiToTM ($tithi) {
	$result['a_masa']=ceil($tithi/30)-1;
	if (ceil(($tithi-$result['a_masa']*30)/15)==1) {$result['paksha']=NULL;}
	else {$result['paksha']=1;}
	$result['ntithi']=$tithi-$result['a_masa']*30-15*$result['paksha'];
	return $result;
	}
*/	
?>