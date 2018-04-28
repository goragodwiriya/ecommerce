<?php
	// modules/member/checkemail.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		$id = (int)$_POST['id'];
		$value = $db->sql_trim_str($_POST['value']);
		// email
		if ($value == '') {
			echo 'EMAIL_EMPTY';
		} elseif (!gcms::validMail($value)) {
			echo 'REGISTER_INVALID_EMAIL';
		} else {
			// ตรวจสอบ email
			$sql = "SELECT `id` FROM `".DB_USER."` WHERE `email`='".addslashes($value)."' AND `fb`='0' LIMIT 1";
			$search = $db->customQuery($sql);
			if (sizeof($search) == 1 && ($id == 0 || $id != $search[0]['id'])) {
				echo 'EMAIL_EXISTS';
			}
		}
	}
