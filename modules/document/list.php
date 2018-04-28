<?php
	// modules/document/list.php
	if (defined('MAIN_INIT')) {
		// ค่าที่ส่งมา
		$cat = (int)$_REQUEST['cat'];
		$page = (int)$_REQUEST['page'];
		$module_id = (int)$_REQUEST['mid'];
		$sqls = array();
		// breadcrumbs
		$breadcrumb = gcms::loadtemplate('document', '', 'breadcrumb');
		$breadcrumbs = array();
		// หน้าหลัก
		$canonical = WEB_URL.'/index.php';
		$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', $canonical, $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
		if (isset($ds) || $tag != '') {
			// tag หรือ calendar
			$index = $default['document'];
			$index['categories'] = 0;
			if (isset($ds)) {
				// เรียกจากวันที่
				$selday = mktime(0, 0, 0, $ds[2], $ds[1], (int)$ds[3] - $lng['YEAR_OFFSET']);
				$nextday = $selday + 86400;
				$sqls[] = "I.`last_update` >= $selday";
				$sqls[] = "I.`last_update` < $nextday";
				$index['topic'] = $lng['LNG_DOCUMENT_DATE'].' '.gcms::mktime2date($selday, 'd F Y');
				// breadcrumb ของวันที่ที่เรียก
				$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL('calendar', "$ds[1]-$ds[2]-$ds[3]"), $index['topic'], $index['topic'], $breadcrumb);
			} elseif ($tag != '') {
				// เรียกตาม tags
				$index['topic'] = $lng['LNG_TAGS'].' '.$tag;
				// breadcrumb ของ tag ที่เรียก
				$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL('tag', $tag), $index['topic'], $index['topic'], $breadcrumb);
			}
			$index['keywords'] = "$index[topic],$keywords";
			$index['description'] = "$index[topic],$description";
			$index['menu_text'] = $index['topic'];
			$index['module'] = 'document';
			include (ROOT_PATH.'modules/document/default.config.php');
			$index = array_merge($default['document'], $index);
		} else {
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
				$sql .= ",C.`category_id`,C.`topic`,C.`detail` AS `description`,C.`icon`";
				$sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
				$sql .= " INNER JOIN `".DB_CATEGORY."` AS C ON C.`category_id`=$cat AND C.`module_id`=D.`module_id`";
			}
			$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`index`='1' AND I.`module_id`=D.`module_id` AND I.`language`=D.`language`";
			if ($module_id > 0) {
				$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=$module_id AND M.`module`='$module' AND M.`owner`='document'";
			} else {
				$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`module`='$module' AND M.`owner`='document'";
			}
			$sql .= " WHERE D.`language` IN ('".LANGUAGE."', '') LIMIT 1";
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
			if ($index) {
				// โมดูล
				if ($index['module'] != $module_list[0]) {
					$m = $install_modules[$index['module']]['menu_text'];
					$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL($index['module']), $install_modules[$index['module']]['menu_tooltip'], ($m == '' ? $index['module'] : $m), $breadcrumb);
				}
				// อ่าน config
				gcms::r2config($index['config'], $index);
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
			$list = array();
			// category
			if ($cat > 0 && $index['topic'] != '') {
				$breadcrumbs['CATEGORY'] = gcms::breadcrumb('', gcms::getURL($index['module'], '', (int)$index['category_id']), $index['description'], $index['topic'], $breadcrumb);
			}
			if ($cat > 0 || $index['categories'] == 0 || $index['category_display'] == 0) {
				// เลือกหมวดมาหรือไม่มีหมวดแสดงรายการเรื่อง
				include (ROOT_PATH.'modules/document/stories.php');
				// template
				$template = 'list';
			} else {
				// ลิสต์รายชื่อหมวด
				include (ROOT_PATH.'modules/document/categories.php');
				// template
				$template = 'category';
			}
			// แสดงผลหน้าเว็บ
			$patt = array('/{BREADCRUMS}/', '/{NEWTOPIC}/', '/{CATEGORY}/', '/{TOPIC}/', '/{DETAIL}/', '/{LIST}/', '/{SPLITPAGE}/',
				'/{LANGUAGE}/', '/{MODULE}/', '/{WIDGET_([A-Z]+)(([\s_])(.*))?}/e', '/{(LNG_[A-Z0-9_]+)}/e');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = is_file(ROOT_PATH.'modules/document/write.php') && gcms::canConfig(explode(',', $index['can_write'])) ? '' : 'hidden';
			$replace[] = (int)$cat;
			$replace[] = $index['topic'];
			$replace[] = $index['detail'];
			$replace[] = sizeof($list) > 0 ? implode("\n", $list) : '<p class=error>'.$lng['LNG_LIST_EMPTY'].'</p>';
			$replace[] = $splitpage;
			$replace[] = LANGUAGE;
			$replace[] = $index['module'];
			$replace[] = 'gcms::getWidgets';
			$replace[] = 'gcms::getLng';
			$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'document', $template));
			// title,keywords,description
			$title = $index['topic'];
			$keywords = $index['keywords'];
			$description = $index['description'];
			// เลือกเมนู
			$menu = $install_modules[$index['module']]['alias'];
			$menu = $menu == '' ? $index['module'] : $menu;
		}
	}
