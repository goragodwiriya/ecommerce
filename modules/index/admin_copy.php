<?php
	// modules/index/admin_copy.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer และ แอดมิน
	if (gcms::isReferer() && gcms::isAdmin()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$id = (int)$_POST['id'];
			$lng = $db->sql_trim_str($_POST['lng']);
			$action = $db->sql_trim_str($_POST['action']);
			if ($id > 0 && $lng != '') {
				if ($action == 'copy_menu') {
					// สำเนาเมนู, ตรวจสอบเมนู
					$menu = $db->getRec(DB_MENUS, $id);
					if ($menu['language'] == '') {
						$ret['error'] = 'LANGUAGE_EMPTY';
					} else {
						// ตรวจสอบเมนูซ้ำ
						$sql = "SELECT `id`";
						$sql .= " FROM `".DB_MENUS."`";
						$sql .= " WHERE `index_id`='$menu[index_id]'";
						$sql .= " AND `parent`='$menu[parent]' AND `level`='$menu[level]' AND `language`='$lng'";
						$sql .= " LIMIT 1";
						$search = $db->customQuery($sql);
						if (sizeof($search) > 0) {
							$ret['error'] = 'LANGUAGE_COPY_EXISTS';
						} else {
							// ข้อมูลเดิม
							$old_lng = $menu['language'];
							// แก้ไขรายการเดิมเป็นภาษาใหม่
							$menu['language'] = $lng;
							$db->edit(DB_MENUS, $menu['id'], $menu);
							unset($menu['id']);
							// เพิ่มรายการใหม่จากรายการเดิม
							$menu['language'] = $old_lng;
							$db->add(DB_MENUS, $menu);
							$ret['error'] = 'LANGUAGE_COPY_SUCCESS';
						}
					}
				} else {
					// ตรวจสอบรายการที่เลือก
					$sql = "SELECT * FROM `".DB_INDEX."` WHERE `id`='$id' AND `index`='1' LIMIT 1";
					$index = $db->customQuery($sql);
					if (sizeof($index) == 0) {
						$ret['error'] = 'ID_NOT_FOUND';
					} else {
						$index = $index[0];
						if ($index['language'] == '') {
							$ret['error'] = 'LANGUAGE_EMPTY';
						} else {
							// ตรวจสอบโมดูลซ้ำ
							$sql = "SELECT `id`";
							$sql .= " FROM `".DB_INDEX."`";
							$sql .= " WHERE `language`='$lng' AND `module_id`='$index[module_id]'";
							$sql .= " LIMIT 1";
							$search = $db->customQuery($sql);
							if (sizeof($search) > 0) {
								$ret['error'] = 'MODULE_ALREADY_EXISTS';
							} else {
								$old_lng = $index['language'];
								// อ่าน detail
								$sql = "SELECT * FROM `".DB_INDEX_DETAIL."` WHERE `id`='$index[id]' AND `module_id`='$index[module_id]' AND `language`='$index[language]' LIMIT 1";
								$detail = $db->customQuery($sql);
								$index['language'] = $lng;
								$db->edit(DB_INDEX, $index['id'], $index);
								$db->query("UPDATE `".DB_INDEX_DETAIL."` SET `language`='$index[language]' WHERE `id`='$index[id]' AND `module_id`='$index[module_id]' AND `language`='$old_lng' LIMIT 1");
								unset($index['id']);
								$index['language'] = $old_lng;
								$detail[0]['id'] = $db->add(DB_INDEX, $index);
								$detail[0]['language'] = $old_lng;
								$db->add(DB_INDEX_DETAIL, $detail[0]);
								$ret['error'] = 'LANGUAGE_COPY_SUCCESS';
							}
						}
					}
				}
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
