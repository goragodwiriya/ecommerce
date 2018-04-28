<?php
	// modules/index/admin_category_check.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		// ค่าที่ส่งมา
		$id = (int)$_POST['id'];
		$mid = (int)$_POST['mid'];
		$category_id = (int)$_POST['cat'];
		if ($category_id == 0) {
			echo 'ID_EMPTY';
		} else {
			// ตรวจสอบ category_id ซ้ำ
			$sql = "SELECT `id` FROM `".DB_CATEGORY."` WHERE `category_id`=$category_id AND `module_id`=$mid AND `id`!=$id";
			if (sizeof($db->customQuery($sql)) > 0) {
				echo 'ID_EXISTS';
			}
		}
	}
