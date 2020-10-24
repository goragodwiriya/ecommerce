<?php
	// modules/document/admin_categorywrite_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			$save = array();
			$ret = array();
			$error = false;
			$input = false;
			$topic = array();
			$detail = array();
			foreach ($_POST['category_topic'] AS $k => $v) {
				$v = $db->sql_trim_str(gcms::oneLine($v));
				if ($v != '') {
					$topic[$k] = $v;
				}
				$v = $db->sql_trim_str(gcms::oneLine($_POST['category_detail'][$k]));
				if ($v != '') {
					$detail[$k] = $v;
				}
			}
			// ค่าที่ส่งมา
			$id = (int)$_POST['write_id'];
			$category_id = (int)$_POST['category_id'];
			$module_id = (int)$_POST['module_id'];
			if ($id > 0) {
				// แก้ไข, ตรวจสอบหมวดที่เลือก
				$sql = "SELECT C.`id`,C.`module_id`,C.`icon`,CONCAT(M.`config` ,'\n' ,C.`config`) AS `config`";
				$sql .= ",(SELECT `id` FROM `".DB_CATEGORY."` WHERE `category_id`=$category_id AND `module_id`=$module_id) AS `cid`";
				$sql .= " FROM `".DB_CATEGORY."` AS C";
				$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=$module_id AND M.`owner`='document'";
				$sql .= " WHERE C.`id`=$id AND C.`module_id`=$module_id LIMIT 1";
			} else {
				// ตรวจสอบโมดูล (หมวดใหม่)
				$sql1 = "SELECT MAX(`id`) FROM `".DB_CATEGORY."`";
				$sql = "SELECT M.`id` AS `module_id`,M.`config`,1+COALESCE(($sql1),0) AS `id`";
				$sql .= ",(SELECT `id` FROM `".DB_CATEGORY."` WHERE `category_id`=$category_id AND `module_id`=$module_id) AS `cid`";
				$sql .= " FROM `".DB_MODULES."` AS M";
				$sql .= " WHERE M.`id`=$module_id AND M.`owner`='document' LIMIT 1";
			}
			$index = $db->customQuery($sql);
			if (sizeof($index) == 1) {
				$index = $index[0];
				// config
				gcms::r2config($index['config'], $index);
				// ตรวจสอบค่าที่ส่งมา
				$ret['ret_category_topic_'.LANGUAGE] = '';
				$ret['ret_category_detail_'.LANGUAGE] = '';
				$ret['ret_category_id'] = '';
				if (!gcms::canConfig(explode(',', $index['can_config']))) {
					$error = 'ACTION_ERROR';
				} elseif ($category_id == 0) {
					$ret['ret_category_id'] = 'ID_EMPTY';
					$input = 'category_id';
					$error = 'ID_EMPTY';
				} elseif ($index['cid'] > 0 && $index['cid'] != $index['id']) {
					$ret['ret_category_id'] = 'ID_EXISTS';
					$input = 'category_id';
					$error = 'ID_EXISTS';
				} elseif (sizeof($topic) == 0) {
					$ret['ret_category_topic_'.LANGUAGE] = 'CATEGORY_TOPIC_EMPTY';
					$error = 'CATEGORY_TOPIC_EMPTY';
					$input = 'category_topic_'.LANGUAGE;
				} elseif (sizeof($detail) == 0) {
					$ret['ret_category_detail_'.LANGUAGE] = 'CATEGORY_DETAIL_EMPTY';
					$error = 'CATEGORY_DETAIL_EMPTY';
					$input = 'category_detail_'.LANGUAGE;
				}
				// icon
				if (!$error && $index['img_typies'] != '') {
					// old icon
					$icon = gcms::ser2Array($icon);
					if (sizeof($icon) == 1) {
						$icon = array();
					}
					// ชนิดของไฟล์ที่ยอมรับ
					$img_typies = explode(',', $index['img_typies']);
					foreach ($_FILES AS $key => $value) {
						if ($value['tmp_name'] != '') {
							$ret["ret_$key"] = '';
							// ภาษา
							$k = str_replace('category_icon_', '', $key);
							// ตรวจสอบไฟล์อัปโหลด
							$info = gcms::isValidImage($img_typies, $value);
							if (!$info) {
								$ret["ret_$key"] = 'INVALID_FILE_TYPE';
								$input = $key;
								$error = 'INVALID_FILE_TYPE';
							} else {
								$icon[$k] = "cat-$k-$index[id].$info[ext]";
								if ($info['width'] <= $index['icon_width'] && $info['height'] <= $index['icon_height']) {
									// รูปภาพต้นฉบับ เท่ากับ หรือ เล็กกว่าที่กำหนดให้อัปโหลดเลย
									if (!@move_uploaded_file($value['tmp_name'], DATA_PATH."document/$icon[$k]")) {
										$ret["ret_$key"] = 'DO_NOT_UPLOAD';
										$input = $key;
										$error = 'DO_NOT_UPLOAD';
									} else {
										$ret["icon_$k"] = rawurlencode(DATA_URL."document/$icon[$k]?$mmktime");
									}
								} elseif (!gcms::cropImage($value['tmp_name'], DATA_PATH."document/$icon[$k]", $info, $index['icon_width'], $index['icon_height'])) {
									$ret["ret_$key"] = 'DO_NOT_UPLOAD';
									$input = $key;
									$error = 'DO_NOT_UPLOAD';
								} else {
									$ret["icon_$k"] = rawurlencode(DATA_URL."document/$icon[$k]?$mmktime");
								}
							}
						}
					}
					if (sizeof($icon) > 0) {
						$save['icon'] = gcms::array2Ser($icon);
					}
				}
				if (!$error) {
					$save['category_id'] = $category_id;
					$save['topic'] = gcms::array2Ser($topic);
					$save['detail'] = gcms::array2Ser($detail);
					// config
					$cfg = array();
					$cfg[] = "published=".(int)$_POST['category_published'];
					$cfg[] = "can_reply=".(int)$_POST['category_can_reply'];
					$save['config'] = implode("\n", $cfg);
					// save
					if ($id == 0) {
						// เพิ่มหมวดใหม่
						$save['id'] = $index['id'];
						$save['module_id'] = $index['module_id'];
						$db->add(DB_CATEGORY, $save);
						// คืนค่า
						$ret['error'] = 'ADD_COMPLETE';
					} else {
						// แก้ไข
						$db->edit(DB_CATEGORY, $index['id'], $save);
						// คืนค่า
						$ret['error'] = 'EDIT_SUCCESS';
					}
					$ret['location'] = 'back';
					// อัปเดตจำนวนเรื่อง และ ความคิดเห็น ในหมวด
					$sql1 = "SELECT COUNT(*) FROM `".DB_INDEX."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]' AND `index`='0'";
					$sql2 = "SELECT `id` FROM `".DB_INDEX."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]' AND `index`='0'";
					$sql2 = "SELECT COUNT(*) FROM `".DB_COMMENT."` WHERE `index_id` IN ($sql2) AND `module_id`='$index[module_id]'";
					$sql = "UPDATE `".DB_CATEGORY."` AS C SET C.`c1`=($sql1),C.`c2`=($sql2) WHERE C.`module_id`='$index[module_id]'";
					$db->query($sql);
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
