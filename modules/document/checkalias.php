<?php
	// modules/document/checkalias.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		// ค่าที่ส่งมา
		$action = $_POST['action'];
		$value = addslashes($db->sql_trim_str($_POST['val']));
		$id = (int)$_POST['id'];
		// ตรวจสอบค่าที่ส่งมา
		if ($action == 'alias') {
			// ค้นหาชื่อเรื่องซ้ำ
			$sql = "SELECT `id` FROM `".DB_INDEX."` WHERE `alias`='$value' LIMIT 1";
			$search = $db->customQuery($sql);
			if (sizeof($search) > 0 && ($id == 0 || $id != $search[0]['id'])) {
				echo 'ALIAS_EXISTS';
			}
		}
	}
