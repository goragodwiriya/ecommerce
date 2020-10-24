<?php
	// modules/board/admin_config_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$icon_category_type = @implode(',', $_POST['config_icon_category_type']);
			$default_icon = $_FILES['config_default_icon'];
			$icon_width = max(32, (int)$_POST['config_icon_width']);
			$icon_height = max(32, (int)$_POST['config_icon_height']);
			$news_count = max(0, (int)$_POST['config_news_count']);
			$can_view = $_POST['config_can_view'];
			$can_view[] = 1;
			$moderator = $_POST['config_moderator'];
			$moderator[] = 1;
			$can_post = $_POST['config_can_post'];
			$can_post[] = 1;
			$can_reply = $_POST['config_can_reply'];
			$can_reply[] = 1;
			$can_config = $_POST['config_can_config'];
			$can_config[] = 1;
			// ตรวจสอบรายการที่ต้องการแก้ไข
			$index = $db->getRec(DB_MODULES, $_POST['config_id']);
			if ($index) {
				// config
				gcms::r2config($index['config'], $index);
				if (!gcms::canConfig(explode(',', $index['can_config']))) {
					$index = false;
				}
			}
			if (!$index) {
				// ไม่พบ หรือไม่สามารถแก้ไขได้
				$ret['error'] = 'ACTION_ERROR';
			} elseif ($icon_category_type == '') {
				$ret['error'] = 'ICON_CATEGORY_TYPE_EMPTY';
				$ret['input'] = 'config_icon_category_type';
				$ret['ret_config_icon_category_type'] = 'ICON_CATEGORY_TYPE_EMPTY';
			} else {
				$ret['ret_config_icon_category_type'] = '';
				$cfg = array();
				// ไอคอนของหมวด
				if ($default_icon['tmp_name'] != '') {
					// ตรวจสอบไฟล์อัปโหลด
					$info = gcms::isValidImage(explode(',', $icon_category_type), $default_icon);
					if (!$info) {
						$ret['error'] = 'INVALID_FILE_TYPE';
						$ret['input'] = 'config_default_icon';
						$ret['ret_config_default_icon'] = 'INVALID_FILE_TYPE';
					} else {
						// ชื่อไฟล์ใหม่
						$icon = DATA_FOLDER."board/default-$index[id]";
						if ($info['width'] <= $icon_width && $info['height'] <= $icon_height) {
							// รูปภาพต้นฉบับ เท่ากับ หรือ เล็กกว่าที่กำหนดให้อัปโหลดเลย
							if (!@move_uploaded_file($default_icon['tmp_name'], ROOT_PATH."$icon.$info[ext]")) {
								$ret['error'] = 'DO_NOT_UPLOAD';
								$ret['input'] = 'config_default_icon';
								$ret['ret_config_default_icon'] = 'DO_NOT_UPLOAD';
							} else {
								$cfg[] = "icon_w=$info[width]";
								$cfg[] = "icon_h=$info[height]";
								$cfg[] = "default_icon=$icon.$info[ext]";
								$ret['img_default_icon'] = rawurlencode(WEB_URL."/$icon.$info[ext]?$mmktime");
							}
						} elseif (!gcms::cropImage($default_icon['tmp_name'], ROOT_PATH."$icon.jpg", $info, $icon_width, $icon_height)) {
							$ret['error'] = 'DO_NOT_UPLOAD';
							$ret['input'] = 'config_default_icon';
							$ret['ret_config_default_icon'] = 'DO_NOT_UPLOAD';
						} else {
							$cfg[] = "icon_w=$icon_width";
							$cfg[] = "icon_h=$icon_height";
							$cfg[] = "default_icon=$icon.jpg";
							$ret['img_default_icon'] = rawurlencode(WEB_URL."/$icon.jpg?$mmktime");
							$ret['ret_config_default_icon'] = '';
						}
					}
				} else {
					// อ่านไอคอนเดิมมาใช้แทน
					$cfg[] = "icon_w=$index[icon_w]";
					$cfg[] = "icon_h=$index[icon_h]";
					$cfg[] = "default_icon=$index[default_icon]";
				}
				// save
				if (!isset($ret['error'])) {
					$cfg[] = 'icon_width='.$icon_width;
					$cfg[] = 'icon_height='.$icon_height;
					$cfg[] = 'icon_category_type='.$icon_category_type;
					$cfg[] = 'list_per_page='.(int)$_POST['config_list_per_page'];
					$cfg[] = 'category_display='.(int)$_POST['config_category_display'];
					$cfg[] = 'can_view='.implode(',', $can_view);
					$cfg[] = 'viewing='.(int)$_POST['config_viewing'];
					$cfg[] = 'new_date='.(int)$_POST['config_new_date'] * 86400;
					$cfg[] = 'news_count='.$news_count;
					$cfg[] = 'moderator='.implode(',', $moderator);
					$cfg[] = 'can_config='.implode(',', $can_config);
					$cfg[] = 'can_reply='.implode(',', $can_reply);
					$cfg[] = 'img_upload_size='.(int)$_POST['config_img_upload_size'];
					$cfg[] = 'img_law='.(int)$_POST['config_img_law'];
					$cfg[] = 'thumb_width='.(int)$_POST['config_thumb_width'];
					$cfg[] = 'img_upload_type='.@implode(',', $_POST['config_img_upload_type']);
					$cfg[] = 'can_post='.implode(',', $can_post);
					// แก้ไขข้อมูล
					$db->edit(DB_MODULES, $index['id'], array('config' => implode("\n", $cfg)));
					// คืนค่า
					$ret['error'] = 'SAVE_COMPLETE';
					$ret['location'] = 'reload';
				}
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
