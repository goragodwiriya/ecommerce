<?php
	// modules/download/main.php
	if (defined('MAIN_INIT')) {
		// ตรวจสอบโมดูลที่ติดตั้ง
		$sql = "SELECT I.`module_id`,M.`module`,D.`detail`,D.`topic`,D.`description`,D.`keywords`";
		$sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
		$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`module_id`=D.`module_id` AND I.`index`='1' AND I.`language`=D.`language`";
		$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`owner`='download'";
		$sql .= " WHERE D.`language` IN ('".LANGUAGE."','') LIMIT 1";
		$index = $cache->get($sql);
		if (!$index) {
			$index = $db->customQuery($sql);
			$cache->save($sql, $index);
		}
		if (sizeof($index) == 1) {
			$index = $index[0];
			// หมวดทั้งหมด
			$categories = array();
			$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]' ORDER BY `category_id`";
			$saved = $cache->get($sql);
			if (!$saved) {
				$saved = $db->customQuery($sql);
				$cache->save($sql, $saved);
			}
			foreach ($saved AS $category) {
				$categories[$category['category_id']] = gcms::ser2Str($category['topic']);
			}
			// breadcrumbs
			$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
			$breadcrumbs = array();
			// หน้าหลัก
			$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
			// url ของหน้านี้
			$canonical = gcms::getURL($index['module']);
			if ($index['module'] != $module_list[0]) {
				// โมดูล
				$m = $install_modules[$index['module']]['menu_text'];
				$breadcrumbs['MODULE'] = gcms::breadcrumb('', $canonical, $install_modules[$index['module']]['menu_tooltip'], ($m == '' ? $index['module'] : $m), $breadcrumb);
			}
			// default query
			$q = array();
			$q[] = "`module_id`='$index[module_id]'";
			// หมวด
			$cat = (int)$_REQUEST['cat'];
			if ($cat > 0) {
				$q[] = "`category_id`='$cat'";
				if ($categories[$cat] != '') {
					// category
					$breadcrumbs['CATEGORY'] = gcms::breadcrumb('', gcms::getURL($index['module']).'?cat='.$id, $categories[$cat], $categories[$cat], $breadcrumb);
				}
			}
			// ข้อความค้นหา
			$search = $db->sql_trim_str($_REQUEST['q']);
			if ($search != '') {
				$q[] = "(`name` LIKE '%$search%' OR `ext` LIKE '%$search%' OR `detail` LIKE '%$search%')";
			}
			$where = ' WHERE '.implode(' AND ', $q);
			// จำนวนดาวน์โหลดทั้งหมด
			$sql = "SELECT COUNT(*) AS `count` FROM `".DB_DOWNLOAD."` $where";
			$count = $cache->get($sql);
			if (!$count) {
				$count = $db->customQuery($sql);
				$count = $count[0];
				$cache->save($sql, $count);
			}
			if ($count['count'] == 0) {
				// ไม่มีรายการใดๆ
				$content = '<div class=error>'.$lng['LNG_LIST_EMPTY'].'</div>';
			} else {
				// หน้าที่เรียก
				$page = (int)$_REQUEST['page'];
				$totalpage = round($count['count'] / $config['download_list_per_page']);
				$totalpage += ($totalpage * $config['download_list_per_page'] < $count['count']) ? 1 : 0;
				$page = $page > $totalpage ? $totalpage : $page;
				$page = $page < 1 ? 1 : $page;
				$start = $config['download_list_per_page'] * ($page - 1);
				// อ่านรายการลงใน $list
				$list = array();
				$patt = array('/{ID}/', '/{NAME}/', '/{EXT}/', '/{ICON}/', '/{DETAIL}/', '/{LASTUPDATE}/', '/{DOWNLOADS}/', '/{SIZE}/');
				$listitem = gcms::loadtemplate($index['module'], 'download', 'listitem');
				$sql = "SELECT * FROM `".DB_DOWNLOAD."` $where ORDER BY `last_update` DESC LIMIT $start,$config[download_list_per_page]";
				$datas = $cache->get($sql);
				if (!$datas) {
					$datas = $db->customQuery($sql);
					$cache->save($sql, $datas);
				}
				foreach ($datas AS $item) {
					$replace = array();
					$replace[] = $item['id'];
					$replace[] = $item['name'];
					$replace[] = $item['ext'];
					$replace[] = WEB_URL.'/skin/ext/'.(is_file(ROOT_PATH."skin/ext/$item[ext].png") ? $item['ext'] : 'file').'.png';
					$replace[] = $item['detail'];
					$replace[] = gcms::mktime2date($item['last_update']);
					$replace[] = $item['downloads'];
					$replace[] = gcms::formatFileSize($item['size']);
					$list[] = preg_replace($patt, $replace, $listitem);
				}
				// แบ่งหน้า
				$maxlink = 9;
				// query สำหรับ URL
				$url = '<a href="'.gcms::getURL($index['module'], '', $cat, 0, 'page=%1').'">%1</a>';
				if ($totalpage > $maxlink) {
					$start = $page - floor($maxlink / 2);
					if ($start < 1) {
						$start = 1;
					} elseif ($start + $maxlink > $totalpage) {
						$start = $totalpage - $maxlink + 1;
					}
				} else {
					$start = 1;
				}
				$splitpage = ($start > 2) ? str_replace('%1', 1, $url) : '';
				for ($i = $start; $i <= $totalpage && $maxlink > 0; $i++) {
					$splitpage .= ($i == $page) ? '<strong>'.$i.'</strong>' : str_replace('%1', $i, $url);
					$maxlink--;
				}
				$splitpage .= ($i < $totalpage) ? str_replace('%1', $totalpage, $url) : '';
				$splitpage = $splitpage == '' ? '<strong>1</strong>' : $splitpage;
				// แสดงผล list รายการ
				$patt = array('/{BREADCRUMS}/', '/{LIST}/', '/{TOPIC}/', '/{DETAIL}/', '/{SPLITPAGE}/', '/{MODULE}/',
					'/{SEARCH}/', '/{LANGUAGE}/', '/^{WIDGET_([A-Z]+)(([\s_])(.*))?}$/e', '/{(LNG_[A-Z0-9_]+)}/e');
				$replace = array();
				$replace[] = implode("\n", $breadcrumbs);
				$replace[] = implode("\n", $list);
				$replace[] = $index['topic'];
				$replace[] = $index['detail'];
				$replace[] = $splitpage;
				$replace[] = $index['module'];
				$replace[] = $search;
				$replace[] = LANGUAGE;
				$replace[] = 'gcms::getWidgets';
				$replace[] = 'gcms::getLng';
				$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'download', 'main'));
			}
			// title,keywords,description
			$title = $index['topic'];
			$keywords = $index['keywords'];
			$description = $index['description'];
		} else {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		}
	}
