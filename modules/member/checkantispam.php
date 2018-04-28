<?php
	// modules/member/action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer() && $db->sql_trim_str($_POST['value']) != $_SESSION[$_POST['antispam']]) {
		echo 'ANTISPAM_INCORRECT';
	}
