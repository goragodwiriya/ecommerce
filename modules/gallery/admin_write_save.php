<?php
	// modules/gallery/admin_write_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::canConfig($config['gallery_can_write'])) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			$save = array();
			$save2 = array();
			$ret = array();
			$error = false;
			$input = false;
			// ค่าที่ส่งมา
			$save['topic'] = $db->sql_trim_str($_POST['gallery_topic']);
			$save['detail'] = $db->sql_trim_str($_POST['gallery_detail']);
			$file = $_FILES['gallery_pic'];
			// แก้ไขอัลบัม
			$id = (int)$_POST['galleryId'];
			// ตรวจสอบรายการและโมดูลที่เลือก
			if ($id > 0) {
				$sql = "SELECT C.`id`,C.`module_id`,M.`module`,G.`id` AS `image_id`,G.`image`";
				$sql .= " FROM `".DB_MODULES."` AS M";
				$sql .= " INNER JOIN `".DB_GALLERY_ALBUM."` AS C ON C.`module_id`=M.`id` AND C.`id`=$id";
				$sql .= " INNER JOIN `".DB_GALLERY."` AS G ON G.`album_id`=C.`id` AND G.`module_id`=M.`id` AND G.`count`='0'";
			} else {
				$sql1 = "SELECT MAX(`id`) FROM `".DB_GALLERY_ALBUM."` WHERE `module_id`=M.`id`";
				$sql = "SELECT 0 AS `image_id`,M.`id` AS `module_id`,M.`module`,1+COALESCE(($sql1),0) AS `id` FROM `".DB_MODULES."` AS M";
			}
			$sql .= " WHERE M.`owner`='gallery' LIMIT 1";
			$index = $db->customQuery($sql);
			if (sizeof($index) == 1) {
				$index = $index[0];
				// ตรวจสอบค่าที่ส่งมา
				$ret['ret_gallery_topic'] = '';
				$ret['ret_gallery_detail'] = '';
				$ret['ret_gallery_pic'] = '';
				if ($save['topic'] == '') {
					$ret['ret_gallery_topic'] = 'TOPIC_EMPTY';
					$error = 'TOPIC_EMPTY';
					$input = 'gallery_topic';
				} elseif ($save['detail'] == '') {
					$ret['ret_gallery_detail'] = 'DETAIL_EMPTY';
					$error = 'DETAIL_EMPTY';
					$input = 'gallery_detail';
				} elseif ($file['tmp_name'] == '' && $id == 0) {
					// อัลบัมใหม่ ต้องมีรูปภาพเสมอ
					$ret['ret_gallery_pic'] = 'REQUIRE_PICTURE';
					$error = 'REQUIRE_PICTURE';
					$input = 'gallery_pic';
				} else {
					// อัปโหลดรูปภาw
					if ($file['tmp_name'] != '') {
						// ตรวจสอบไฟล์อัปโหลด
						$info = gcms::isValidImage($config['gallery_image_type'], $file);
						if (!$info) {
							$ret['ret_gallery_pic'] = 'INVALID_FILE_TYPE';
							$input = 'gallery_pic';
							$error = 'INVALID_FILE_TYPE';
						} elseif (!gcms::testDir(DATA_PATH."gallery/$index[id]/")) {
							$ret['ret_gallery_pic'] = 'DO_NOT_UPLOAD';
							$input = 'gallery_pic';
							$error = 'DO_NOT_UPLOAD';
						} else {
							// อัปโหลดรูปภาพจริง
							$res = gcms::resizeImage($file['tmp_name'], DATA_PATH."gallery/$index[id]/", "0.$info[ext]", $info, $config['gallery_image_w']);
							if (!$res) {
								$ret['ret_gallery_pic'] = 'DO_NOT_UPLOAD';
								$input = 'gallery_pic';
								$error = 'DO_NOT_UPLOAD';
							} else {
								$save2['image'] = $res['name'];
							}
							// อัปโหลด thumbnail
							if (!gcms::cropImage($file['tmp_name'], DATA_PATH."gallery/$index[id]/thumb_$save2[image]", $info, $config['gallery_thumb_w'], $config['gallery_thumb_h'])) {
								$ret['ret_gallery_pic'] = 'DO_NOT_UPLOAD';
								$input = 'gallery_pic';
								$error = 'DO_NOT_UPLOAD';
							}
							// ลบไฟล์เดิม
							if ($index['image'] != '' && $save2['image'] != $index['image']) {
								@unlink(DATA_PATH."gallery/$index[id]/$index[image]");
								@unlink(DATA_PATH."gallery/$index[id]/thumb_$index[image]");
							}
						}
					}
				}
				if (!$error) {
					// save
					$save['last_update'] = $mmktime;
					if ($id == 0) {
						// เพิ่มอัลบัมใหม่
						$save['id'] = $index['id'];
						$save['module_id'] = $index['module_id'];
						$save['count'] = 1;
						$db->add(DB_GALLERY_ALBUM, $save);
						$save2['album_id'] = $index['id'];
						$save2['module_id'] = $index['module_id'];
						$save2['last_update'] = $mmktime;
						$save2['count'] = 0;
						$db->add(DB_GALLERY, $save2);
						// คืนค่า
						$ret['error'] = 'ADD_COMPLETE';
						$ret['location'] = rawurlencode(WEB_URL."/admin/index.php?module=gallery-upload&id=$save[id]");
					} else {
						// แก้ไข
						$db->edit(DB_GALLERY_ALBUM, $index['id'], $save);
						if (sizeof($save2) > 0) {
							$save2['last_update'] = $mmktime;
							$db->edit(DB_GALLERY, $index['image_id'], $save2);
						}
						// อัปเดตจำนวนรูปภาพในอัลบัม
						$sql1 = "SELECT COUNT(*) FROM `".DB_GALLERY."` WHERE `album_id`=C.`id` AND `module_id`='$index[module_id]'";
						$sql = "UPDATE `".DB_GALLERY_ALBUM."` AS C SET C.`count`=($sql1) WHERE C.`module_id`='$index[module_id]'";
						$db->query($sql);
						// คืนค่า
						$ret['error'] = 'EDIT_SUCCESS';
						$ret['location'] = 'back';
					}
				} else {
					// error
					$ret['error'] = $error;
					if ($input) {
						$ret['input'] = $input;
					}
				}
			} else {
				$ret['error'] = 'ACTION_ERROR';
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
