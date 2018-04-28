<?php
	// modules/member/checkphone.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		$id = (int)$_POST['id'];
		$value = $db->sql_trim_str($_POST['value']);
		// phone
		if ($config['member_phone'] == 1 && $value == '') {
			echo 'PHONE_EMPTY';
		} elseif ($value != '') {
			if (!preg_match('/[0-9]{9,10}/', $value)) {
				echo 'INVALID_PHONE_NUMBER';
			} else {
				// ตรวจสอบ phone
				$sql = "SELECT `id` FROM `".DB_USER."` WHERE `phone1`='$value' LIMIT 1";
				$search = $db->customQuery($sql);
				if (sizeof($search) == 1 && ($id == 0 || $id != $search[0]['id'])) {
					echo 'PHONE_EXISTS';
				}
			}
		}
	}
