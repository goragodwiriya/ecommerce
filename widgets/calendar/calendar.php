<?php
	// widgets/calendar/calendar.php
	if (defined('MAIN_INIT')) {
		if ($config['calendar_owner'] == '') {
			$config['calendar_owner'] = 'document';
			$config['calendar_db'] = DB_INDEX;
		}
		// วันนี้
		$year = date('Y', $mmktime);
		$month = date('m', $mmktime);
		$today = date('d', $mmktime);
		$d = $_POST['module'];
		if (preg_match('/^calendar[\/-]([0-3]?[0-9])[\-|\s]([0-1]?[0-9])[\-|\s]([0-9]{4,4})$/', $d, $ds)) {
			// วันที่จาก URL
			$c_mkdate = mktime(0, 0, 0, $ds[2] + (int)$_REQUEST['n'], 1, (int)$ds[3] - $lng['YEAR_OFFSET']);
			$c_year = date('Y', $c_mkdate);
			$c_month = date('m', $c_mkdate);
		} else {
			// ไม่ได้เลือกวันที่มา แสดงวันที่วันนี้
			$c_year = $year;
			$c_month = $month;
		}
		$d = "$today-$c_month-".((int)$c_year + $lng['YEAR_OFFSET']);
		// วันที่ 1 ของเดือนนี้
		$mkdate = mktime(0, 0, 0, $c_month, 1, $c_year);
		$weekday = date('w', $mkdate);
		$endday = date('t', $mkdate);
		$day = 1;
		// วันที่ 1 ของเดือนถัดไป
		$mk31th = $c_month == 12 ? mktime(0, 0, 0, 1, 1, $c_year + 1) : mktime(0, 0, 0, $c_month + 1, 1, $c_year);
		// จำนวนวันของเดือนก่อนหน้า
		$days_of_last_month = $c_month == 1 ? date('t', mktime(0, 0, 0, 12, 1, $c_year - 1)) : date('t', mktime(0, 0, 0, $c_month - 1, 1, $c_year));
		// ตรวจสอบรายการที่เกี่ยวข้องจากฐานข้อมูล
		$events = array();
		$sql = "SELECT D.`id`,D.`last_update`,M.`module` FROM `$config[calendar_db]` AS D";
		$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`owner`='$config[calendar_owner]'";
		$sql .= " WHERE D.`last_update`>='$mkdate' AND D.`last_update`<'$mk31th' AND D.`published`='1'";
		$datas = $cache->get($sql);
		if (!$datas) {
			$datas = $db->customQuery($sql);
			$cache->save($sql, $datas);
		}
		foreach ($datas AS $item) {
			$cd = (int)date('d', $item['last_update']);
			$events[$cd]['id'][] = $item['id'];
			$events[$cd]['module'] = $item['module'];
		}
		// แสดงปฏิทิน
		$calendar[] = '<div class="calendar">';
		$calendar[] = '<div>';
		$calendar[] = '<p>';
		$calendar[] = '<a href="'.gcms::getURL('calendar', $d, 0, 0, "n=-1").'" id="calendar-'.$d.'-back">&lt;</a>';
		$calendar[] = '<span>'.$lng['MONTH_LONG'][$c_month - 1].' '.($c_year + $lng['YEAR_OFFSET']).'</span>';
		$calendar[] = '<a href="'.gcms::getURL('calendar', $d, 0, 0, "n=+1").'" id="calendar-'.$d.'-next">&gt;</a>';
		$calendar[] = '</p>';
		$calendar[] = '<table>';
		$calendar[] = '<thead><tr><th>'.implode('</th><th>', $lng['DATE_SHORT']).'</th></tr></thead>';
		$calendar[] = '<tbody>';
		$start = 1;
		$data = '<tr>';
		while ($start <= $weekday) {
			$data .= '<td class="ex">'.($days_of_last_month - $weekday + $start).'</td>';
			$start++;
		}
		$weekday++;
		while ($day <= $endday) {
			if (isset($events[$day])) {
				$e_day = $day.'-'.$c_month.'-'.($c_year + $lng['YEAR_OFFSET']);
				$_day = '<a href="'.gcms::getURL("$config[calendar_owner]-calendar", '', 0, 0, "d=$e_day").'" id="calendar-'.$e_day.'-'.implode('_', $events[$day]['id']).'">'.$day.'</a>';
			} else {
				$_day = $day;
			}
			if ($today == $day && $month == $c_month && $year == $c_year) {
				$c = 'today';
			} else {
				$c = 'curr';
			}
			$data .= '<td class="'.$c.'">'.$_day.'</td>';
			if ($weekday == 7 && $day != $endday) {
				$calendar[] = $data.'</tr>';
				$data = '<tr>';
				$weekday = 0;
			}
			$day++;
			$weekday++;
		}
		$n = 1;
		while ($weekday <= 7) {
			$data .= '<td class="ex">'.$n.'</td>';
			$n++;
			$weekday++;
		}
		$calendar[] = $data.'</tr>';
		$calendar[] = '</tbody>';
		$calendar[] = '<tfoot>';
		$calendar[] = '<tr><td colspan="7"><a href="'.gcms::getURL('calendar').'" id="calendar-'.$d.'-today">'.(int)$today.' '.$lng['MONTH_LONG'][$month - 1].' '.($year + $lng['YEAR_OFFSET']).'</a></td></tr>';
		$calendar[] = '</tfoot>';
		$calendar[] = '</table>';
		$calendar[] = '</div>';
		$calendar[] = '</div>';
	}
