<?php
	// modules/download/admin_category_action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::canConfig($config['download_can_config'])) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$action = $_POST['action'];
			$module_id = (int)$_POST['mid'];
			if (preg_match('/^config_(category)_add$/', $action, $match)) {
				// add category
				$save = array();
				$save_detail = array();
				// new row
				$text = $lng['LNG_CATEGORY'];
				$save['group_id'] = 0;
				$save['module_id'] = $module_id;
				$topic[LANGUAGE] = "$lng[LNG_CLICK_TO] $lng[LNG_EDIT]";
				// id ของ หมวดใหม่
				$sql = "SELECT MAX(`category_id`) AS `category` FROM `".DB_CATEGORY."` WHERE `module_id`='$module_id'";
				$category = $db->customQuery($sql);
				$save['category_id'] = (int)$category[0]['category'] + 1;
				$save['topic'] = gcms::array2Ser($topic);
				// add
				$id = $db->add(DB_CATEGORY, $save);
				// new row
				$row = '<dd id="config_category_'.$id.'">';
				$row .= '<span class="no">['.$save['category_id'].']</span>';
				$row .= '<span class="icon-delete" id="config_category_delete_'.$id.'" title="'.$lng['LNG_DELETE'].' '.$text.'">&nbsp;</span>';
				$row .= $text.' <span id="config_category_name_'.$id.'" title="'.$lng['LNG_CLICK_TO'].' '.$lng['LNG_EDIT'].'">'.$topic[LANGUAGE].'</span>';
				$row .= '</dd>';
				$ret['data'] = rawurlencode($row);
				$ret['newId'] = "config_category_".$id;
			} elseif (preg_match('/^config_(category)_delete_([0-9]+)$/', $action, $match)) {
				// ลบหมวดหมู่
				$db->query("DELETE FROM `".DB_CATEGORY."` WHERE `module_id`='$module_id' AND `id`='$match[2]' LIMIT 1");
				// รายการที่ลบ
				$ret['del'] = "config_$match[1]_".$match[2];
			} elseif (preg_match('/^config_(category)_name_([0-9]+)$/', $action, $match)) {
				// แก้ไขชื่อหมวดหมู่
				$topic[LANGUAGE] = $db->sql_trim_str(gcms::oneLine($_POST['value']));
				$sql = "SELECT `id` FROM `".DB_CATEGORY."` WHERE `module_id`='$module_id' AND `id`='$match[2]' LIMIT 1";
				$category = $db->customQuery($sql);
				if (sizeof($category) == 1) {
					$db->edit(DB_CATEGORY, $category[0]['id'], array('topic' => gcms::array2Ser($topic)));
					// ส่งข้อมูลใหม่ไปแสดงผล
					$ret['edit'] = rawurlencode($topic[LANGUAGE]);
					$ret['editId'] = $action;
				}
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
