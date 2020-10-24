<?php
	// modules/board/post.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer
	if (gcms::isReferer()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$save = array();
			$save['topic'] = $db->sql_trim_str($_POST['board_topic']);
			$save['detail'] = gcms::txtClean($_POST['board_detail']);
			$password = $db->sql_trim_str($_POST['board_password']);
			$email = $db->sql_trim_str($_POST['board_email']);
			$category_id = (int)$_POST['board_category'];
			$board_id = (int)$_POST['board_id'];
			$module_id = (int)$_POST['module_id'];
			$picture = $_FILES['board_picture'];
			// อ่านโมดูลและ config
			if ($board_id > 0) {
				// login
				$login = $_SESSION['login'];
				// แก้ไขคำถาม อ่านข้อมูลจาก $board_id
				$sql = "SELECT Q.`picture`,Q.`module_id`,Q.`member_id`,M.`module`,C.`category_id`";
				$sql .= ",(CASE WHEN ISNULL(C.`config`) THEN M.`config` ELSE CONCAT(M.`config`,'\n',C.`config`) END) AS `config`";
				$sql .= " FROM `".DB_BOARD_Q."` AS Q";
				$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=Q.`module_id`";
				$sql .= " LEFT JOIN `".DB_CATEGORY."` AS C ON C.`category_id`='$category_id'";
				$sql .= " WHERE Q.`id`='$board_id' AND Q.`module_id`='$module_id'";
				$sql .= " LIMIT 1";
			} else {
				// อ่านข้อมูลจากโมดูลและหมวดที่เลือก
				$sql = "SELECT M.`id` AS `module_id`,M.`module`,C.`category_id`";
				$sql .= ",(CASE WHEN ISNULL(C.`config`) THEN M.`config` ELSE CONCAT(M.`config`,'\n',C.`config`) END) AS `config`";
				$sql .= " FROM `".DB_MODULES."` AS M";
				$sql .= " LEFT JOIN `".DB_CATEGORY."` AS C ON C.`category_id`='$category_id'";
				$sql .= " WHERE M.`id`='$module_id'";
				$sql .= " LIMIT 1";
			}
			$index = $db->customQuery($sql);
			if (sizeof($index) == 1) {
				$index = $index[0];
				// config
				gcms::r2config($index['config'], $index);
				// ไม่รูปภาพอัปโหลดและอัปโหลดได้
				$requireDetail = $picture['tmp_name'] == '' && $index['img_upload_type'] != '';
				// คำถาใหม่หรือแก้ไขคำถามและไม่เคยอัปโหลดรูป
				$requireDetail = $requireDetail && ($board_id == 0 || ($board_id > 0 && $index['picture'] == ''));
				// login
				$isMember = gcms::isMember();
				// ผู้ดูแล
				$moderator = $isMember && gcms::canConfig(explode(',', $index['moderator']));
				// can reply
				$can_post = explode(',', $index['can_post']);
				// true = guest โพสต์ได้
				$guest = in_array(-1, $can_post);
				// login ใช้ email และ password ของคน login
				if ($isMember) {
					$email = $_SESSION['login']['email'];
					$password = $_SESSION['login']['password'];
				}
			} else {
				$index = false;
			}
			// ตรวจสอบค่าที่ส่งมา
			$ret = array();
			if (!$index) {
				// ไม่พบบอร์ด
				$ret['error'] = 'ACTION_ERROR';
			} elseif ($save['topic'] == '') {
				// คำถาม ไม่ได้กรอกคำถาม
				$ret['input'] = 'board_topic';
				$ret['ret_board_topic'] = 'this';
			} elseif ($index['categories'] > 0 && $category_id == 0) {
				// คำถาม มีหมวด ไม่ได้เลือกหมวด
				$ret['input'] = 'board_category';
				$ret['ret_board_category'] = 'this';
			} elseif ($save['detail'] == '' && $requireDetail) {
				// ไม่ได้กรอกรายละเอียด และ ไม่มีรูป
				$ret['error'] = 'DETAIL_EMPTY';
				$ret['input'] = 'board_detail';
				$ret['ret_board_detail'] = 'DETAIL_EMPTY';
			} elseif ($_POST['board_antispam'] != $_SESSION[$_POST['board_antispamid']]) {
				// antispam ไม่ถูกต้อง
				$ret['ret_board_antispam'] = 'this';
				$ret['input'] = 'board_antispam';
			} elseif ($board_id == 0) {
				// ตั้งคำถามใหม่ ตรวจสอบสมาชิก
				if ($email == '') {
					// ไม่ได้กรอกอีเมล
					$ret['error'] = 'EMAIL_EMPTY';
					$ret['input'] = 'board_email';
					$ret['ret_board_email'] = 'EMAIL_EMPTY';
				} elseif ($password == '' && !$guest) {
					// สมาชิกเท่านั้น ไม่ได้กรอก รหัสผ่าน
					$ret['error'] = 'PASSWORD_EMPTY';
					$ret['input'] = 'board_password';
					$ret['ret_board_password'] = 'PASSWORD_EMPTY';
				} elseif ($email != '' && $password != '') {
					// ตรวจสอบสมาชิก
					$user = gcms::CheckLogin($email, $password);
					if ($user == 0 || $user == 3) {
						$ret['error'] = 'EMAIL_OR_PASSWORD_INCORRECT';
						$ret['input'] = $user == 0 ? 'board_email' : 'board_password';
						$ret['ret_'.$ret['input']] = 'EMAIL_OR_PASSWORD_INCORRECT';
					} elseif ($user == 1) {
						$ret['error'] = 'MEMBER_NO_ACTIVATE';
						$ret['input'] = 'board_email';
						$ret['ret_board_email'] = 'MEMBER_NO_ACTIVATE';
					} elseif ($user == 2) {
						$ret['error'] = 'MEMBER_BAN';
						$ret['input'] = 'board_email';
						$ret['ret_board_email'] = 'MEMBER_BAN';
					} elseif (!in_array($user['status'], $can_post)) {
						$ret['error'] = 'DO_NOT_POST';
						$ret['input'] = 'board_email';
						$ret['ret_board_email'] = 'DO_NOT_POST';
					} else {
						// ชื่อสมาชิกใช้งานได้
						$save['member_id'] = $user['id'];
						$save['email'] = $user['email'];
					}
				} else {
					// ตรวจสอบอีเมลซ้ำกับสมาชิก สำหรับบุคคลทั่วไป
					$sql = "SELECT `id` FROM `".DB_USER."` WHERE `email`='$email' LIMIT 1";
					$user2 = $db->customQuery($sql);
					if (sizeof($user2) > 0) {
						$ret['error'] = 'EMAIL_EXISTS';
						$ret['input'] = 'board_email';
						$ret['ret_board_email'] = 'EMAIL_EXISTS';
					} elseif (!gcms::validMail($email)) {
						$ret['error'] = 'REGISTER_INVALID_EMAIL';
						$ret['input'] = 'board_email';
						$ret['ret_board_email'] = 'REGISTER_INVALID_EMAIL';
					} else {
						// ผู้มาเยือน
						$save['member_id'] = 0;
						$save['email'] = $email;
					}
				}
			} elseif (!($index['member_id'] == $login['id'] || $moderator)) {
				// แก้ไขกระทู้ ตรวจสอบ เจ้าของหรือผู้ดูแล
				$ret['error'] = 'ACTION_ERROR';
			}
			// flood กระทู้
			if (sizeof($ret) == 0) {
				// ตรวจสอบโพสต์ซ้ำภายใน 1 วัน
				$sql = "SELECT `id` FROM `".DB_BOARD_Q."`";
				$sql .= " WHERE `last_update`>".($mmktime - 86400)." AND `email`='$save[email]' AND `topic`='".addslashes($save['topic'])."' AND `detail`='".addslashes($save['detail'])."'";
				$sql .= " LIMIT 1";
				$flood = $db->customQuery($sql);
				if (sizeof($flood) > 0) {
					$ret['error'] = 'BOARD_FLOOD_QUESTION';
				}
			}
			// รูปภาพอัปโหลด
			if (sizeof($ret) == 0 && $index['img_upload_type'] != '' && $picture['tmp_name'] != '') {
				// ตรวจสอบไฟล์อัปโหลด
				$info = gcms::isValidImage(explode(',', $index['img_upload_type']), $picture);
				if (!$info) {
					$ret['error'] = 'INVALID_FILE_TYPE';
					$ret['input'] = 'board_picture';
					$ret['ret_board_picture'] = 'INVALID_FILE_TYPE';
				} elseif ($picture['size'] > ($index['img_upload_size'] * 1024)) {
					$ret['error'] = 'FILE_TOO_BIG';
					$ret['input'] = 'board_picture';
					$ret['ret_board_picture'] = 'FILE_TOO_BIG';
				} else {
					// ชื่อไฟล์
					$save['picture'] = "$mmktime.$info[ext]";
					while (is_file(DATA_PATH."board/$save[picture]")) {
						$mmktime++;
						$save['picture'] = "$mmktime.$info[ext]";
					}
					// อัปโหลดรูป
					$tw = max(32, (int)$index['thumb_width']);
					if (!gcms::cropImage($picture['tmp_name'], DATA_PATH."board/thumb-$save[picture]", $info, $tw, $tw)) {
						$ret['error'] = 'DO_NOT_UPLOAD';
						$ret['input'] = 'board_picture';
						$ret['ret_board_picture'] = 'DO_NOT_UPLOAD';
					} elseif (!@move_uploaded_file($picture['tmp_name'], DATA_PATH."board/$save[picture]")) {
						$ret['error'] = 'DO_NOT_UPLOAD';
						$ret['input'] = 'board_picture';
						$ret['ret_board_picture'] = 'DO_NOT_UPLOAD';
					} else {
						$save['pictureW'] = $info['width'];
						$save['pictureH'] = $info['height'];
						// ลบไฟล์เก่า
						if ($index['picture'] != '') {
							@unlink(DATA_PATH."board/thumb-$index[picture]");
							@unlink(DATA_PATH."board/$index[picture]");
						}
					}
				}
			} elseif (sizeof($ret) == 0 && $board_id == 0 && $index['img_law'] == 1 && $index['img_upload_type'] != '') {
				// ต้องมีรูปภาพอัปโหลดเสมอสำหรับการตั้งกระทู้
				$ret['error'] = 'REQUIRE_PICTURE';
				$ret['input'] = 'board_picture';
				$ret['ret_board_picture'] = 'REQUIRE_PICTURE';
			}
			if (sizeof($ret) == 0) {
				// บันทึกลงฐานข้อมูล
				$save['category_id'] = (int)$index['category_id'];
				$save['last_update'] = $mmktime;
				if ($board_id > 0) {
					// แก้ไข
					$db->edit(DB_BOARD_Q, $board_id, $save);
					$ret['error'] = 'BOARD_POST_EDIT_SUCCESS';
				} else {
					// ตั้งกระทู้ใหม่
					$save['ip'] = gcms::getip();
					$save['create_date'] = $mmktime;
					$save['module_id'] = $index['module_id'];
					$board_id = $db->add(DB_BOARD_Q, $save);
					// อัปเดตสมาชิก
					if ($save['member_id'] > 0) {
						// อัปเดต post
						$sql = "UPDATE `".DB_USER."` SET `post`=`post`+1 WHERE `id`='$save[member_id]' LIMIT 1";
						$db->query($sql);
					}
					$ret['error'] = 'BOARD_POST_SUCCESS';
				}
				if ($save['category_id'] > 0) {
					// อัปเดตจำนวนกระทู้ และ ความคิดเห็น ในหมวด
					$sql1 = "SELECT COUNT(*) FROM `".DB_BOARD_Q."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]'";
					$sql2 = "SELECT `id` FROM `".DB_BOARD_Q."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]'";
					$sql2 = "SELECT COUNT(*) FROM `".DB_BOARD_R."` WHERE `index_id` IN ($sql2) AND `module_id`='$index[module_id]'";
					$sql = "UPDATE `".DB_CATEGORY."` AS C SET C.`c1`=($sql1),C.`c2`=($sql2) WHERE C.`module_id`='$index[module_id]'";
					$db->query($sql);
				}
				// เคลียร์ antispam
				unset($_SESSION[$_POST['board_antispamid']]);
				// คืนค่า url ของบอร์ด
				$ret['location'] = rawurlencode("index.php?module=$index[module]&wbid=$board_id&visited=$mmktime");
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
