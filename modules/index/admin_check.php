<?php
	// modules/index/admin_check.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer และ แอดมิน
	if (gcms::isReferer() && gcms::isAdmin()) {
		// ค่าที่ส่งมา
		$action = $_POST['action'];
		$language = $db->sql_trim_str($_POST['lng']);
		$value = addslashes($db->sql_trim_str($_POST['val']));
		$id = (int)$_POST['id'];
		if ($action == 'module') {
			if (!preg_match('/^[a-z0-9]{1,}$/', $value)) {
				echo 'EN_NUMBER_ONLY';
			} else {
				if (in_array($value, explode(',', MODULE_RESERVE))) {
					// ชื่อสงวน
					echo 'MODULE_INCORRECT';
				} elseif (!in_array($value, $allow_module) && (is_dir(ROOT_PATH."modules/$value/") || is_dir(ROOT_PATH."widgets/$value/") || is_dir(ROOT_PATH."$value/") || is_file(ROOT_PATH."$value.php"))) {
					// เป็นชื่อโฟลเดอร์หรือชื่อไฟล์ และ ไม่ใช่ชื่อที่อนุญาติ
					echo 'MODULE_INCORRECT';
				} else {
					// ค้นหาชื่อโมดูลซ้ำ
					$sql = "SELECT I.`language`";
					$sql .= " FROM `".DB_MODULES."` AS M";
					$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`index`='1' AND I.`module_id`=M.`id`";
					if ($id > 0) {
						$sql .= " AND I.`id`!='$id'";
					}
					$sql .= " WHERE M.`module`='$value'";
					$error = false;
					foreach ($db->customQuery($sql) AS $item) {
						if ($language == '') {
							$error = true;
						} elseif ($item['language'] == '') {
							$error = true;
						} elseif ($item['language'] == $language) {
							$error = true;
						}
					}
					if ($error) {
						echo 'MODULE_ALREADY_EXISTS';
					}
				}
			}
		} elseif ($action == 'topic') {
			// ค้นหาชื่อไตเติลซ้ำ
			$sql = "SELECT D.`language` FROM `".DB_INDEX_DETAIL."` AS D WHERE D.`topic`='$value'";
			if ($id > 0) {
				$sql .= " AND D.`id`!='$id'";
			}
			$error = false;
			foreach ($db->customQuery($sql) AS $item) {
				if ($language == '') {
					$error = true;
				} elseif ($item['language'] == '') {
					$error = true;
				} elseif ($item['language'] == $language) {
					$error = true;
				}
			}
			if ($error) {
				echo 'TITLE_EXISTS';
			}
		}
	}
