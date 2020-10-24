<?php
	// modules/download/admin_write_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::canConfig($config['download_can_upload'])) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$save = array();
			$save['name'] = $db->sql_trim_str($_POST['download_name']);
			$save['detail'] = $db->sql_trim_str($_POST['download_description']);
			$save['category_id'] = (int)$_POST['download_category'];
			$save['file'] = $db->sql_trim_str($_POST['download_file']);
			$file = $_FILES['download_upload'];
			$id = (int)$_POST['write_id'];
			// ตรวจสอบค่าที่ส่งมา
			if ($id > 0) {
				$sql = "SELECT C.*,M.`module` FROM `".DB_DOWNLOAD."` AS C";
				$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id`";
				$sql .= " WHERE C.`id`=$id AND M.`owner`='download' LIMIT 1";
			} else {
				// ใหม่ ตรวจสอบโมดูล
				$sql = "SELECT `id` AS `module_id` FROM `".DB_MODULES."` WHERE `owner`='download' LIMIT 1";
			}
			$index = $db->customQuery($sql);
			$index = $index[0];
			// ตรวจสอบค่าที่ส่งมา
			$error = false;
			$ret['ret_download_file'] = '';
			$ret['ret_download_upload'] = '';
			$ret['ret_download_description'] = '';
			if (sizeof($index) == 0) {
				$ret['error'] = 'FILE_NOT_FOUND';
			} elseif ($save['detail'] == '') {
				$ret['ret_download_description'] = 'DOWNLOAD_DESCRIPTION_EMPTY';
				$ret['error'] = 'DOWNLOAD_DESCRIPTION_EMPTY';
				$ret['input'] = 'download_description';
			} elseif ($file['tmp_name'] == '' && $save['file'] == '' && $id == 0) {
				// ใหม่ ต้องมีไฟล์
				$ret['ret_download_upload'] = 'DOWNLOAD_FILE_EMPTY';
				$ret['error'] = 'DOWNLOAD_FILE_EMPTY';
				$ret['input'] = 'download_upload';
			} elseif ($file['tmp_name'] != '') {
				if (preg_match('/^(.*)\.(.*)$/', $file['name'], $match)) {
					$file_name = $match[1];
					$save['ext'] = $match[2];
				}
				// ตรวจสอบไฟล์อัปโหลด
				if ($file_name == '' || !in_array($save['ext'], explode(',', $config['download_file_typies']))) {
					$ret['ret_download_upload'] = 'INVALID_FILE_TYPE';
					$ret['error'] = 'INVALID_FILE_TYPE';
					$ret['input'] = 'download_upload';
				} elseif ($file['size'] > ($config['download_upload_size'])) {
					$ret['ret_download_file'] = 'FILE_TOO_BIG';
					$ret['error'] = 'FILE_TOO_BIG';
					$ret['input'] = 'download_upload';
				} else {
					// อัปโหลด
					$save['file'] = DATA_FOLDER."download/$mmktime.$save[ext]";
					while (file_exists(ROOT_PATH.$save['file'])) {
						$mmktime++;
						$save['file'] = DATA_FOLDER."download/$mmktime.$save[ext]";
					}
					if (!@copy($file['tmp_name'], ROOT_PATH.$save['file'])) {
						$ret['ret_download_upload'] = 'DO_NOT_UPLOAD';
						$ret['error'] = 'DO_NOT_UPLOAD';
						$ret['input'] = 'download_upload';
					} else {
						if ($save['name'] == '') {
							$save['name'] = $file_name;
						}
						$save['size'] = $file['size'];
						if ($save['file'] != $index['file']) {
							@unlink(ROOT_PATH.$index['file']);
						}
					}
				}
			} elseif ($save['file'] != '') {
				$f = iconv('UTF-8', 'TIS-620', ROOT_PATH.$save['file']);
				if (!is_file($f)) {
					$ret['ret_download_file'] = 'DOWNLOAD_FILE_NOT_FOUND';
					$ret['error'] = 'DOWNLOAD_FILE_NOT_FOUND';
					$ret['input'] = 'download_file';
				} else {
					$save['size'] = filesize($f);
					if ($save['name'] == '') {
						$info = pathinfo($save['file']);
						$save['name'] = $info['filename'];
						$save['ext'] = $info['extension'];
					}
				}
			}
			if (!isset($ret['error'])) {
				$save['last_update'] = $mmktime;
				if ($id == 0) {
					// ใหม่
					$save['module_id'] = $index['module_id'];
					$save['create_date'] = $mmktime;
					$db->add(DB_DOWNLOAD, $save);
				} else {
					// แก้ไข
					$db->edit(DB_DOWNLOAD, $id, $save);
				}
				// คืนค่า
				$ret['error'] = 'SAVE_COMPLETE';
				$ret['location'] = rawurlencode(WEB_URL.'/admin/index.php?module=download-setup');
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
