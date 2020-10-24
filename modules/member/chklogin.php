<?php
	// modules/member/chklogin.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer
	if (gcms::isReferer()) {
		// ค่าคงที่สำหรับป้องกันการเรียกหน้าเพจโดยตรง
		DEFINE('MAIN_INIT', 'chklogin');
		// โหลดหน้า login
		include ROOT_PATH.'modules/member/login.php';
		// คืนค่า
		if ($isMember) {
			$error = str_replace('%s', ($login_result['displayname'] == '' ? $login_result['email'] : $login_result['displayname']), $lng['LOGIN_SUCCESS']);
			$next = trim($_POST['login_next']);
			echo "$error|$config[login_action]|".($next != '' ? $next : rawurlencode($content));
		} else {
			echo "$error|$input";
		}
	}
