<?php
	// modules/gallery/admin_action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::canConfig($config['gallery_can_write'])) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			$action = $_POST['action'];
			$ids = array();
			foreach (explode(',', $_POST['id']) AS $id) {
				$ids[] = (int)$id;
			}
			if (sizeof($ids) > 0) {
				$ids = implode(',', $ids);
				if ($action == 'delete') {
					// ลบอัลบัม, ตรวจสอบ id
					$sql = "SELECT `id` FROM `".DB_GALLERY_ALBUM."` WHERE `id` IN ($ids) AND `module_id`=(SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='gallery')";
					$ids = array();
					foreach ($db->customQuery($sql) AS $item) {
						// ลบโฟลเดอร์และรูป
						gcms::rm_dir(DATA_PATH."gallery/$item[id]/");
						// id ที่ลบ
						$ids[] = $item['id'];
					}
					if (sizeof($ids) > 0) {
						$ids = implode(',', $ids);
						// ลบอัลบัม
						$db->query("DELETE FROM `".DB_GALLERY_ALBUM."` WHERE `id` IN ($ids)");
						// ลบรูปภาพ
						$db->query("DELETE FROM `".DB_GALLERY."` WHERE `album_id` IN ($ids)");
					}
					// กลับไปหน้าอัลบัม
					$ret['error'] = 'DELETE_SUCCESS';
					$ret['location'] = rawurlencode('index.php?module=gallery-album');
				} elseif ($action == 'deletep') {
					// ลบรูปในอัลบัม
					$album_id = (int)$_POST['album'];
					// ลบรูปภาพ
					$sql = "SELECT `id`,`album_id`,`image` FROM `".DB_GALLERY."` WHERE `id` IN ($ids) AND `album_id`=$album_id";
					foreach ($db->customQuery($sql) AS $item) {
						// ลบรูปภาพ
						@unlink(DATA_PATH."gallery/$item[album_id]/$item[image]");
						@unlink(DATA_PATH."gallery/$item[album_id]/thumb_$item[image]");
						$ret['remove'.$item['id']] = 'L_'.$item['id'];
					}
					// ลบb
					$db->query("DELETE FROM `".DB_GALLERY."` WHERE `id` IN ($ids) AND `album_id`=$album_id");
					// อัปเดตจำนวนรูปภาพในอัลบัม
					$sql = "SELECT COUNT(*) FROM `".DB_GALLERY."` WHERE `module_id`=C.`module_id` AND `album_id`=$album_id";
					$sql = "UPDATE `".DB_GALLERY_ALBUM."` AS C SET C.`count`=($sql) WHERE C.`id`=$album_id";
					$db->query($sql);
					// คืนค่า
					$ret['error'] = 'DELETE_SUCCESS';
				}
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
