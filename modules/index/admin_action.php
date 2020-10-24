<?php
	// modules/index/admin_action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer และ แอดมิน
	if (gcms::isReferer() && gcms::isAdmin()) {
		$action = $_POST['action'];
		if ($_SESSION['login']['account'] == 'demo' && $action != 'get') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			if ($action == 'move' && isset($_POST['data'])) {
				// move menu
				$ids = str_replace('M_', '', $_POST['data']);
				$sql = "SELECT `id`,`level`,`menu_text` FROM `".DB_MENUS."` WHERE `id` IN ($ids)";
				foreach ($db->customQuery($sql) AS $item) {
					$levels[$item['id']] = $item;
				}
				// reorder
				$save['menu_order'] = 0;
				$top_id = 0;
				foreach (explode(',', $ids) AS $i) {
					$save['menu_order']++;
					if ($top_id == 0) {
						$save['level'] = 0;
					} else {
						$save['level'] = max(0, min($levels[$top_id]['level'] + 1, $levels[$i]['level']));
					}
					$top_id = $i;
					// save
					$db->edit(DB_MENUS, $i, $save);
					// คืนค่า
					$text = '';
					for ($b = 0; $b < $save['level']; $b++) {
						$text .= '&nbsp;&nbsp;&nbsp;';
					}
					$ret["r$i"] = rawurlencode(($text == '' ? '' : $text.'↳&nbsp;').$levels[$i]['menu_text']."|$save[level]|$i");
				}
			} elseif ($action == 'left' || $action == 'right') {
				$id = (int)$_POST['id'];
				$top_level = 0;
				// query menu ทั้งหมด
				$sql = "SELECT `id`,`level`,`menu_text` FROM `".DB_MENUS."`";
				$sql .= " WHERE `parent`=(";
				$sql .= "SELECT `parent` FROM `".DB_MENUS."` WHERE `id`='$id' LIMIT 1";
				$sql .= ") ORDER BY `menu_order` ASC";
				foreach ($db->customQuery($sql) AS $a => $item) {
					if ($a == 0) {
						$save['level'] = 0;
					} elseif ($item['id'] == $id) {
						if ($action == 'right') {
							$save['level'] = min($top_level + 1, $item['level'] + 1, 2);
						} else {
							$save['level'] = max(0, $item['level'] - 1);
						}
					} else {
						$save['level'] = max(0, min($top_level + 1, $item['level']));
					}
					$top_level = $save['level'];
					if ($save['level'] != $item['level']) {
						// save
						$db->edit(DB_MENUS, $item['id'], $save);
					}
					// คืนค่า
					$text = '';
					for ($i = 0; $i < $save['level']; $i++) {
						$text .= '&nbsp;&nbsp;&nbsp;';
					}
					$ret["r$item[id]"] = rawurlencode(($text == '' ? '' : $text.'↳&nbsp;').$item['menu_text']."|$save[level]|$item[id]");
				}
			} elseif ($action == 'get' && isset($_POST['parent'])) {
				// query menu
				$sql = "SELECT `id`,`level`,`menu_text`,`menu_tooltip`";
				$sql .= " FROM `".DB_MENUS."` WHERE `parent`='".$db->sql_trim_str($_POST['parent'])."'";
				$sql .= " ORDER BY `menu_order` ASC";
				foreach ($db->customQuery($sql) AS $item) {
					$text = '';
					for ($i = 0; $i < $item['level']; $i++) {
						$text .= '&nbsp;&nbsp;';
					}
					$ret["O_$item[id]"] = rawurlencode(($text == '' ? '' : $text.'↳&nbsp;').($item['menu_text'] == '' ? ($item['menu_tooltip'] == '' ? '---' : $item['menu_tooltip']) : $item['menu_text']));
				}
			} elseif ($action == 'delete') {
				$id = (int)$_POST['id'];
				$t = $_POST['t'];
				if ($t == 'menu') {
					// query menu ทั้งหมด
					$sql = "SELECT * FROM `".DB_MENUS."`";
					$sql .= " WHERE `parent`=(";
					$sql .= "SELECT `parent` FROM `".DB_MENUS."` WHERE `id`='$id' LIMIT 1";
					$sql .= ") ORDER BY `menu_order` ASC";
					foreach ($db->customQuery($sql) AS $a => $item) {
						if ($item['id'] != $id) {
							if ($a == 0) {
								$top_level = 0;
								$menu_order = 1;
							} else {
								$top_level = max(0, min($top_level + 1, $item['level']));
								$menu_order++;
							}
							$changed = false;
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
							$text = '';
							for ($b = 0; $b < $item['level']; $b++) {
								$text .= '&nbsp;&nbsp;&nbsp;';
							}
							$ret["r$item[id]"] = rawurlencode(($text == '' ? '' : $text.'↳&nbsp;').$item['menu_text']."|$item[level]|$item[id]");
						}
					}
					// ลบ
					$db->delete(DB_MENUS, $id);
				} elseif ($t == 'module' || $t == 'index') {
					// ลบโมดูล ไม่ลบข้อมูลของโมดูล
					$sql = "SELECT `id`,`module_id` FROM `".DB_INDEX."` WHERE `index`='1'";
					$sql .= " AND `module_id`=(SELECT `module_id` FROM `".DB_INDEX."` WHERE `id`='$id')";
					$search = $db->customQuery($sql);
					// ลบ index, index_detail
					if (sizeof($search) > 0) {
						$module_id = $search[0]['module_id'];
						$db->query("DELETE FROM `".DB_INDEX."` WHERE `id`='$id' AND `module_id`='$module_id' AND `index`='1'");
						$db->query("DELETE FROM `".DB_INDEX_DETAIL."` WHERE `id`='$id' AND `module_id`='$module_id'");
					}
					// ลบ modules ที่ไม่มีรายการ index
					if (sizeof($search) == 1) {
						$db->query("DELETE FROM `".DB_MODULES."` WHERE `id`='$module_id'");
					}
				}
				// คืนค่า
				$ret['delete_id'] = $id;
				$ret['error'] = 'DELETE_SUCCESS';
			} else if ($action == 'published') {
				$t = $_POST['t'];
				if ($t == 'menu') {
					$menu = $db->getRec(DB_MENUS, $_POST['id']);
					if ($menu) {
						$published = $menu['published'] == '1' ? '0' : '1';
						$db->edit(DB_MENUS, $menu['id'], array('published' => $published));
						// คืนค่า
						$ret['published'] = "menu_$menu[id]|$published|".rawurlencode($lng['LNG_PUBLISHEDS'][$published]);
					}
				} elseif ($t == 'index') {
					$index = $db->getRec(DB_INDEX, $_POST['id']);
					if ($index) {
						$published = $index['published'] == '1' ? '0' : '1';
						$db->edit(DB_INDEX, $index['id'], array('published' => $published));
						// คืนค่า
						$ret['published'] = "index_$index[id]|$published|".rawurlencode($lng['LNG_PUBLISHEDS'][$published]);
					}
				}
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
