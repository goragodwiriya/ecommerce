<?php
	// modules/gallery/admin_upload_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::canConfig($config['gallery_can_write'])) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// อัลบัมที่อัปโหลด
			$id = (int)$_POST['albumId'];
			$sql = "SELECT MAX(`count`) FROM `".DB_GALLERY."` WHERE `module_id`=M.`id` AND `album_id`=C.`id`";
			$sql = "SELECT C.`id`,C.`module_id`,($sql) AS `count` FROM `".DB_MODULES."` AS M";
			$sql .= " INNER JOIN `".DB_GALLERY_ALBUM."` AS C ON C.`module_id`=M.`id` AND C.`id`=$id";
			$sql .= " WHERE M.`owner`='gallery' LIMIT 1";
			$index = $db->customQuery($sql);
			if (sizeof($index) == 1) {
				$index = $index[0];
				$save = array();
				$save['module_id'] = $index['module_id'];
				$save['album_id'] = $index['id'];
				$save['last_update'] = $mmktime;
				$save['count'] = (int)$index['count'] + 1;
				// path เก็บไฟล์
				$dir = DATA_PATH."gallery/$save[album_id]/";
				foreach ($_FILES AS $file) {
					// ตรวจสอบไฟล์อัปโหลด
					$info = gcms::isValidImage($config['gallery_image_type'], $file);
					if (!$info) {
						$ret['error'] = 'INVALID_FILE_TYPE';
					} else {
						while (is_file($dir."$save[count].$info[ext]")) {
							$save['count']++;
						}
						$save['image'] = "$save[count].$info[ext]";
						// อัปโหลดรูปภาพจริง
						$res = gcms::resizeImage($file['tmp_name'], $dir, $save['image'], $info, $config['gallery_image_w']);
						if (!$res) {
							$ret['error'] = 'DO_NOT_UPLOAD';
						} else {
							$save['image'] = $res['name'];
						}
						// อัปโหลด thumbnail
						if (!gcms::cropImage($file['tmp_name'], $dir."thumb_$save[image]", $info, $config['gallery_thumb_w'], $config['gallery_thumb_h'])) {
							$ret['error'] = 'DO_NOT_UPLOAD';
						} else {
							// บันทึกลง db
							$db->add(DB_GALLERY, $save);
						}
					}
				}
				// อัปเดตจำนวนรูปภาพในอัลบัม
				$sql1 = "SELECT COUNT(*) FROM `".DB_GALLERY."` WHERE `album_id`=C.`id` AND `module_id`='$index[module_id]'";
				$sql = "UPDATE `".DB_GALLERY_ALBUM."` AS C SET C.`count`=($sql1) WHERE C.`id`='$index[id]' AND C.`module_id`='$index[module_id]'";
				$db->query($sql);
			} else {
				$ret['error'] = 'ACTION_ERROR';
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
