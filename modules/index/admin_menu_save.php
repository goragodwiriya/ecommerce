<?php
	// admin/index/admin_menu_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer และ แอดมิน
	if (gcms::isReferer() && gcms::isAdmin()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$save['menu_text'] = $db->sql_trim_str($_POST['write_menu_text']);
			$save['menu_tooltip'] = $db->sql_trim_str($_POST['write_menu_tooltip']);
			$save['accesskey'] = strtolower($db->sql_trim_str($_POST['write_accesskey']));
			$save['alias'] = $db->sql_trim_str($_POST['write_alias']);
			$save['parent'] = strtoupper($db->sql_trim_str($_POST['write_parent']));
			$save['menu_url'] = $db->sql_trim_str($_POST['write_menu_url']);
			$save['menu_target'] = $db->sql_trim_str($_POST['write_target']);
			$save['language'] = $db->sql_trim_str($_POST['write_language']);
			$save['published'] = (int)$_POST['write_published'];
			$type = (int)$_POST['write_type'];
			$toplvl = (int)$_POST['write_order'];
			$action = (int)$_POST['write_action'];
			if ($action == 1 && preg_match('/^([a-z]+)_(([a-z]+)(_([a-z0-9]+))?|([0-9]+))$/', $_POST['write_index_id'], $match)) {
				if ($match[6] == '') {
					if (is_file(ROOT_PATH."modules/$match[1]/admin_inint.php")) {
						$install_owners[$match[1]][] = array('module' => $match[5], 'owner' => $match[1]);
						include (ROOT_PATH."modules/$match[1]/admin_inint.php");
						$action = 2;
						$save['menu_url'] = $module_menus[$match[1]][$match[2]][1];
						$save['alias'] = $save['alias'] == '' ? $module_menus[$match[1]][$match[2]][2] : $save['alias'];
					}
				} else {
					$save['index_id'] = $match[6];
				}
			}
			// id ของเมนู (0 = ใหม่)
			$id = (int)$_POST['write_id'];
			if ($id > 0) {
				$menu = $db->getRec(DB_MENUS, $id);
			}
			// ตรวจสอบค่าที่ส่งมา
			$error = false;
			$input = false;
			$ret = array();
			if ($id > 0 && !$menu) {
				$error = 'ID_NOT_FOUND';
			} else {
				// accesskey
				$ret['ret_write_accesskey'] = '';
				if ($save['accesskey'] != '') {
					if (!preg_match('/[a-z0-9]{1,1}/', $save['accesskey'])) {
						$ret['ret_write_accesskey'] = 'INVALID_ACCESSKEY';
						$input = !$input ? 'write_accesskey' : $input;
						$error = !$error ? 'INVALID_ACCESSKEY' : $error;
					}
				}
				// menu order (top level)
				$ret['ret_write_order'] = '';
				if ($type != 0 && $toplvl == 0) {
					$ret['ret_write_order'] = 'MENU_ORDER_INVALID';
					$input = !$input ? 'write_order' : $input;
					$error = !$error ? 'MENU_ORDER_INVALID' : $error;
				} elseif ($action == 1 && $save['index_id'] == 0) {
					$ret['ret_write_order'] = 'MENU_ORDER_INVALID';
					$input = !$input ? 'write_order' : $input;
					$error = !$error ? 'MENU_ORDER_INVALID' : $error;
				}
				// menu_url
				$ret['ret_write_menu_url'] = '';
				if ($action == 2 && $save['menu_url'] == '') {
					$ret['ret_write_menu_url'] = 'MENU_URL_EMPTY';
					$input = !$input ? 'write_menu_url' : $input;
					$error = !$error ? 'MENU_URL_EMPTY' : $error;
				}
				if ($action != 2) {
					$save['menu_url'] = '';
				}
				if ($action != 1) {
					$save['index_id'] = 0;
				}
			}
			if (!$error) {
				if ($type == 0) {
					// เป็นเมนูลำดับแรกสุด
					$save['menu_order'] = 1;
					$save['level'] = 0;
					$menu_order = 1;
					$toplvl = 0;
				} else {
					$save['level'] = $type - 1;
					$menu_order = 0;
				}
				$top_level = 0;
				// query menu ทั้งหมด, เรียงลำดับเมนูตามที่กำหนด
				$sql = "SELECT `id`,`level`,`menu_order` FROM `".DB_MENUS."`";
				$sql .= " WHERE `parent`='$save[parent]'";
				$sql .= " ORDER BY `menu_order` ASC";
				foreach ($db->customQuery($sql) AS $item) {
					if ($item['id'] != $menu['id']) {
						$changed = false;
						$menu_order++;
						$top_level = $menu_order == 1 ? 0 : min($top_level + 1, $item['level']);
						if ($menu_order != $item['menu_order']) {
							// อัปเดต menu_order
							$item['menu_order'] = $menu_order;
							$changed = true;
						}
						if ($top_level != $item['level']) {
							// อัปเดต level
							$item['level'] = $top_level;
							$changed = true;
						}
						if ($changed) {
							$db->edit(DB_MENUS, $item['id'], $item);
						}
						if ($toplvl == $item['id']) {
							$menu_order++;
							$save['menu_order'] = $menu_order;
							$save['level'] = min($item['level'] + 1, $save['level']);
						}
					}
				}
				if ($id > 0) {
					// แก้ไข
					$db->edit(DB_MENUS, $menu['id'], $save);
					$ret['error'] = 'EDIT_SUCCESS';
				} else {
					// ใหม่
					$db->add(DB_MENUS, $save);
					$ret['error'] = 'ADD_COMPLETE';
				}
				$ret['location'] = 'back';
			} else {
				// คืนค่า input ตัวแรกที่ error
				if ($input) {
					$ret['input'] = $input;
				}
				$ret['error'] = $error;
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
