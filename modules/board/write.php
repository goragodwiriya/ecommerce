<?php
	// modules/board/write.php
	if (defined('MAIN_INIT')) {
		// หมวดที่เลือก
		$cat = (int)$_REQUEST['cat'];
		// อ่านข้อมูลจากโมดูลและหมวดที่เลือก
		$sql = "SELECT M.`id` AS `module_id`,M.`module`,C.`category_id`";
		$sql .= ",(CASE WHEN ISNULL(C.`config`) THEN M.`config` ELSE CONCAT(M.`config`,'\n',C.`config`) END) AS `config`";
		$sql .= " FROM `".DB_MODULES."` AS M";
		$sql .= " LEFT JOIN `".DB_CATEGORY."` AS C ON C.`category_id`='$cat'";
		$sql .= " WHERE M.`module`='$module'";
		$sql .= " LIMIT 1";
		$index = $cache->get($sql);
		if (!$index) {
			$index = $db->customQuery($sql);
			if (sizeof($index) > 0) {
				$cache->save($sql, $index);
			}
		}
		if (sizeof($index) == 0) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		} else {
			$index = $index[0];
			// config
			gcms::r2config($index['config'], $index);
			// login
			$login = $_SESSION['login'];
			// breadcrumbs
			$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
			$breadcrumbs = array();
			// หน้าหลัก
			$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
			// breadcrumb ของ โมดูล
			$m = $install_modules[$index['module']]['menu_text'];
			$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL($index['module']), $install_modules[$index['module']]['menu_tooltip'], ($m == '' ? $index['module'] : $m), $breadcrumb);
			// หมวด
			$categories = array();
			$categories[0] = '<option value=0>{LNG_NO_CATEGORY}</option>';
			$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]' ORDER BY `category_id`";
			$list = $cache->get($sql);
			if (!$list) {
				$list = $db->customQuery($sql);
				$cache->save($sql, $list);
			}
			foreach ($list AS $item) {
				if ($isAdmin || $cat == $item['category_id']) {
					$sel = $cat == $item['category_id'] ? ' selected' : '';
					$categories[$item['category_id']] = "<option value=$item[category_id]$sel>".gcms::ser2Str($item['topic'])."</option>";
				}
			}
			if (sizeof($categories) > 1) {
				unset($categories[0]);
			}
			// antispam
			$register_antispamchar = gcms::rndname(32);
			$_SESSION[$register_antispamchar] = gcms::rndname(4);
			// แสดงผล
			$patt = array('/{BREADCRUMS}/', '/<MEMBER>(.*)<\/MEMBER>/s', '/<UPLOAD>(.*)<\/UPLOAD>/s',
				'/{CATEGORIES}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{LOGIN_PASSWORD}/',
				'/{LOGIN_EMAIL}/', '/{ANTISPAM}/', '/{ANTISPAMVAL}/', '/{SIZE}/', '/{TYPE}/', '/{MODULEID}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = $isMember ? '' : '$1';
			$replace[] = $index['img_upload_type'] == '' ? '' : '$1';
			$replace[] = implode("\n", $categories);
			$replace[] = 'gcms::getLng';
			$replace[] = $login['password'];
			$replace[] = $login['email'];
			$replace[] = $register_antispamchar;
			$replace[] = $isAdmin ? $_SESSION[$register_antispamchar] : '';
			$replace[] = $index['img_upload_size'];
			$replace[] = $index['img_upload_type'] == '' ? '&nbsp;' : $index['img_upload_type'];
			$replace[] = $index['module_id'];
			$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'board', 'write'));
			// title,description,keywords
			$title = $lng['LNG_BOARD_NEW'].' '.$install_modules[$index['module']]['menu_text'];
			$description = $index['tags'];
			$keywords = $index['keywords'];
			// เลือกเมนู
			$menu = $install_modules[$index['module']]['alias'];
			$menu = $menu == '' ? $index['module'] : $menu;
		}
	}
