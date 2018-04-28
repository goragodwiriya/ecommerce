<?php
	// modules/member/checkidcard.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		$id = (int)$_POST['id'];
		$value = $db->sql_trim_str($_POST['value']);
		// phone
		if ($config['member_idcard'] == 1 && $value == '') {
			echo 'IDCARD_EMPTY';
		} elseif ($value != '') {
			if (!gcms::checkIDCard($value)) {
				echo 'IDCARD_INVALID';
			} else {
				// ตรวจสอบ phone
				$sql = "SELECT `id` FROM `".DB_USER."` WHERE `idcard`='$value' LIMIT 1";
				$search = $db->customQuery($sql);
				if (sizeof($search) == 1 && ($id == 0 || $id != $search[0]['id'])) {
					echo 'IDCARD_EXISTS';
				}
			}
		}
	}
