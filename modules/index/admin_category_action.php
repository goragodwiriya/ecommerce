<?php
	// modules/index/admin_category_action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			if (isset($_POST['data'])) {
				list($action, $module, $id) = explode('-', $_POST['data']);
			} elseif (preg_match('/^category_([0-9]+)$/', $_POST['id'], $match)) {
				// เลือก category ตอน เขียน
				$action = 'changecategory';
				$module = (int)$match[1];
				$value = (int)$_POST['value'];
			} elseif (preg_match('/^categoryid_([0-9]+)_([0-9]+)$/', $_POST['module'], $match)) {
				// เปลี่ยน category_id ที่หน้า category
				$action = 'categoryid';
				$module = (int)$match[1];
				$id = (int)$match[2];
				$value = Max(1, (int)$_POST['value']);
			} else {
				$action = $_POST['action'];
				$value = (int)$_POST['value'];
				$module = (int)$_POST['module'];
				if (isset($_POST['id'])) {
					foreach (explode(',', $_POST['id']) AS $id) {
						$ids[] = (int)$id;
					}
					$id = implode(',', $ids);
				}
			}
			// ตรวจสอบ module
			$index = $db->getRec(DB_MODULES, $module);
			if ($index) {
				if (in_array($index['owner'], array('document', 'board'))) {
					// config
					gcms::r2config($index['config'], $index);
					$admin = gcms::canConfig(explode(',', $index['can_config']));
				} else {
					$admin = gcms::canConfig($config[$index['owner'].'_can_config']);
				}
				if ($admin && $action == 'delete') {
					// ลบหมวดหมู่, ตรวจสอบรายการที่เลือก และลบ icon ของหมวด
					$ids = array();
					$categories = array();
					$sql = "SELECT `id`,`icon` FROM `".DB_CATEGORY."` WHERE `id` IN ($id) AND `module_id`='$index[id]'";
					foreach ($db->customQuery($sql) AS $item) {
						if ($item['icon'] != '') {
							foreach (unserialize($item['icon']) AS $icon) {
								if (is_file(DATA_PATH."$index[owner]/$icon")) {
									// ลบไอคอนของหมวด
									unlink(DATA_PATH."$index[owner]/$icon");
								}
							}
						}
						// รายการที่ลบ category_detail
						$ids[] = $item['id'];
					}
					if (sizeof($ids) > 0) {
						// ลบ category
						$db->query("DELETE FROM `".DB_CATEGORY."` WHERE `id` IN (".implode(',', $ids).")");
					}
				} elseif ($admin && $action == 'categoryid') {
					// เปลี่ยน category_id ที่หน้า category
					$sql = "SELECT `id` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' AND `category_id`='$value'";
					$sql = "SELECT C.`id`,C.`category_id`,($sql) AS `s`";
					$sql .= " FROM `".DB_CATEGORY."` AS C";
					$sql .= " WHERE C.`id`='$id' AND C.`module_id`='$index[id]' LIMIT 1";
					$search = $db->customQuery($sql);
					if (sizeof($search) == 1) {
						$search = $search[0];
						if ((int)$search['s'] == 0) {
							$db->edit(DB_CATEGORY, $search['id'], array('category_id' => $value));
							$ret['categoryid_'.$index['id'].'_'.$id] = $value;
						} else {
							$ret['categoryid_'.$index['id'].'_'.$id] = $search['category_id'];
						}
					}
				} elseif ($admin && $action == 'published' && $id != '') {
					// อัปเดต published
					$db->query("UPDATE `".DB_CATEGORY."` SET `published`='".(int)$_POST['value']."' WHERE `id` IN ($id) AND `module_id`='$index[id]'");
				} elseif ($action == 'changecategory') {
					// อ่าน category ขณะเขียน
					if ($value > 0) {
						$ret = array();
						// อ่าน category
						$sql = "SELECT `config` FROM `".DB_CATEGORY."` WHERE `category_id`='$value' AND `module_id`='$index[id]' LIMIT 1";
						$category = $db->customQuery($sql);
						if (sizeof($category) == 1) {
							$category = $category[0];
							// config
							gcms::r2config($category['config'], $category);
							$ret['write_published'] = (int)$category['published'];
							$ret['write_can_reply'] = (int)$category['can_reply'];
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
