<?php
	// modules/download/admin_action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, can config
	if (gcms::isReferer() && gcms::canConfig($config['download_can_upload'])) {
		if ($_SESSION['login']['account'] != 'demo') {
			// ค่าที่ส่งมา
			$action = $_POST['action'];
			$ids = array();
			foreach (explode(',', $_POST['id']) AS $id) {
				$ids[] = (int)$id;
			}
			$ids = implode(',', $ids);
			if ($action == 'delete' && $ids != '') {
				$sql = "SELECT `id` AS `module_id`,`module` FROM `".DB_MODULES."` WHERE `owner`='download' LIMIT 1";
				$index = $db->customQuery($sql);
				if (sizeof($index) == 1) {
					$index = $index[0];
					// ลบไฟล์
					$sql = "SELECT `file` FROM `".DB_DOWNLOAD."` WHERE `id` IN ($ids) AND `module_id`=$index[module_id]";
					foreach ($db->customQuery($sql) AS $item) {
						if ($item['file'] != '' && is_file(ROOT_PATH.$item['file'])) {
							unlink(ROOT_PATH.$item['file']);
						}
					}
					// ลบ db
					$db->query("DELETE FROM `".DB_DOWNLOAD."` WHERE `id` IN ($ids) AND `module_id`=$index[module_id]");
				}
			}
		}
	}
