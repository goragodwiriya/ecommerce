<?php
	// modules/board/list.php
	if (defined('MAIN_INIT')) {
		// ค่าที่ส่งมา
		$cat = (int)$_REQUEST['cat'];
		$page = (int)$_REQUEST['page'];
		$module_id = (int)$_REQUEST['mid'];
		// ตรวจสอบโมดูลที่เลือก และ จำนวนหมวดในโมดูล
		$sql = "SELECT M.`id`,M.`module`,D.`detail`,D.`keywords`";
		$sql .= ",(SELECT COUNT(*) FROM `".DB_CATEGORY."` WHERE `module_id`=M.`id`) AS `categories`";
		if ($cat == 0) {
			// ไม่ได้เลือกหมวดมา
			$sql .= ",D.`topic`,D.`description`,M.`config`";
			$sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
		} else {
			// มีการเลือกหมวด
			$sql .= ",CASE WHEN ISNULL(C.`config`) THEN M.`config` ELSE CONCAT(M.`config` ,'\n' ,C.`config`) END AS `config`";
			$sql .= ",C.`category_id`,C.`topic`,C.`detail` AS `description`";
			$sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
			$sql .= " INNER JOIN `".DB_CATEGORY."` AS C ON C.`category_id`='$cat' AND C.`module_id`=D.`module_id`";
		}
		if ($module_id > 0) {
			$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`module_id`='$module_id' AND I.`index`='1' AND I.`language` IN('".LANGUAGE."', '')";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`='$module_id' AND M.`owner`='board'";
		} else {
			$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`module_id`=D.`module_id` AND I.`index`='1' AND I.`language` IN('".LANGUAGE."', '')";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`owner`='board' AND M.`module`='$module'";
		}
		$sql .= " LIMIT 1";
		$index = $cache->get($sql);
		if (!$index) {
			$index = $db->customQuery($sql);
			if (sizeof($index) == 1) {
				$index = $index[0];
				$cache->save($sql, $index);
			} else {
				$index = false;
			}
		}
		if (!$index) {
			$title = $lng['LNG_DOCUMENT_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		} else {
			if ($cat > 0) {
				$index['topic'] = gcms::ser2Str($index['topic']);
				$index['description'] = gcms::ser2Str($index['description']);
				$index['icon'] = gcms::ser2Str($index['icon']);
			}
			// อ่าน config
			gcms::r2config($index['config'], $index);
			// breadcrumbs
			$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
			$breadcrumbs = array();
			// หน้าหลัก
			$canonical = WEB_URL.'/index.php';
			$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', $canonical, $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
			// โมดูล
			if ($index['module'] != $module_list[0]) {
				$m = $install_modules[$index['module']]['menu_text'];
				$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL($index['module']), $install_modules[$index['module']]['menu_tooltip'], ($m == '' ? $index['module'] : $m), $breadcrumb);
			}
			// category
			if ($index['category_id'] > 0 && $index['topic'] != '') {
				$breadcrumbs['CATEGORY'] = gcms::breadcrumb('', gcms::getURL($index['module'], '', $index['category_id']), $index['description'], $index['topic'], $breadcrumb);
			}
			// ข้อมูลการ login
			$login = $_SESSION['login'];
			// guest มีสถานะเป็น -1
			$status = $isMember ? $login['status'] : -1;
			$list = array();
			if ($cat > 0 || $index['categories'] == 0) {
				// เลือกหมวดมาหรือไม่มีหมวดแสดงรายการเรื่อง
				include (ROOT_PATH.'modules/board/stories.php');
			} else {
				// ลิสต์รายชื่อหมวด
				include (ROOT_PATH.'modules/board/categories.php');
			}
			// แสดงผลหน้าเว็บ
			$patt = array('/{BREADCRUMS}/', '/{LIST}/', '/{NEWTOPIC}/', '/{TOPIC}/',
				'/{CATEGORY}/', '/{DETAIL}/', '/{SPLITPAGE}/', '/{LANGUAGE}/',
				'/{WIDGET_([A-Z]+)(([\s_])(.*))?}/e', '/{(LNG_[A-Z0-9_]+)}/e', '/{MODULE}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = sizeof($list) == 0 ? '' : implode("\n", $list);
			$replace[] = $isAdmin || in_array($status, explode(',', $index['can_post'])) ? '' : 'hidden';
			$replace[] = $index['topic'];
			$replace[] = (int)$cat;
			$replace[] = $index['detail'];
			$replace[] = $splitpage;
			$replace[] = LANGUAGE;
			$replace[] = 'gcms::getWidgets';
			$replace[] = 'gcms::getLng';
			$replace[] = $index['module'];
			if (sizeof($list) > 0) {
				$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'board', ($cat > 0 || $index['categories'] == 0 ? 'list' : 'category')));
			} else {
				$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'board', 'empty'));
			}
			// title,keywords,description
			$title = $index['topic'];
			$keywords = $index['keywords'];
			$description = $index['description'];
		}
		// เลือกเมนู
		$menu = $install_modules[$index['module']]['alias'];
		$menu = $menu == '' ? $index['module'] : $menu;
	}
