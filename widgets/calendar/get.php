<?php
	// widgets/calendar/get.php
	header("content-type: text/html; charset=UTF-8");
	// ตัวแปรหลัก
	include ('../../bin/inint.php');
	if (gcms::isReferer()) {
		// โหลด calendar
		DEFINE('MAIN_INIT', __FILE__);
		include (ROOT_PATH.'widgets/calendar/calendar.php');
		echo implode('', $calendar);
	}