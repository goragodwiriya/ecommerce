<?php
	// modules/search/main.php
	if (defined('MAIN_INIT')) {
		// รายการแสดงต่อหน้า
		$list_per_page = (int)$config['search_list_per_page'] > 0 ? $config['search_list_per_page'] : 20;
		// ค่าที่ส่งมา
		$search = preg_replace('/[\+\s]+/u', ' ', $db->sql_trim_str($_REQUEST['q']));
		$page = max(1, (int)$_REQUEST['page']);
		// ค้นหาข้อความ
		$word_count = 0;
		$searchs1 = array();
		$searchs2 = array();
		$words = array();
		$search_result = array();
		// inint timer
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$time_start = $mtime[1] + $mtime[0];
		// วันที่วันนี้
		$today = date('Y-m-d', $mmktime);
		// ค้นหา
		$list = array();
		if ($search != '') {
			// ค้นหาข้อมูลทั้งหมดจาก sql
			foreach (explode(' ', $search) AS $item) {
				// แยกข้อความค้นหาออกเป็นคำๆ ค้นหาข้อความที่มีความยาวมากกว่า 1 ตัวอักษร
				if (mb_strlen($item) > 1) {
					$searchs1[] = "D.`topic` LIKE '%$item%' OR D.`detail` LIKE '%$item%'";
					$searchs2[] = "C.`detail` LIKE '%$item%'";
					$words[] = $item;
					$word_count++;
				}
			}
			if (sizeof($searchs1) > 0) {
				$sqls = array();
				$sql1 = "SELECT I.`id`,I.`alias`,D.`topic`,D.`description`,D.`detail`,I.`index`,M.`module`,M.`owner`,7 AS `level`";
				$sql1 .= " FROM `".DB_INDEX_DETAIL."` AS D";
				$sql1 .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`module_id`=D.`module_id` AND I.`published`='1' AND I.`published_date`<='$today' AND I.`language` IN('".LANGUAGE."', '')";
				$sql1 .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
				$sql1 .= " WHERE (".implode(' OR ', $searchs1).") AND D.`language` IN ('".LANGUAGE."','')";
				$sql2 = "SELECT I.`id`,I.`alias`,D.`topic`,D.`description`,D.`detail`,0 AS `index`,M.`module`,M.`owner`,5 AS `level`";
				$sql2 .= "FROM `".DB_COMMENT."` AS C";
				$sql2 .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=C.`index_id` AND I.`module_id`=C.`module_id` AND I.`published`='1' AND I.`published_date`<='$today' AND I.`language` IN('".LANGUAGE."', '')";
				$sql2 .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=C.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
				$sql2 .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id`";
				$sql2 .= " WHERE (".implode(' OR ', $searchs2).")";
				$sqls[] = "(SELECT * FROM (($sql1) UNION ($sql2)) AS Q1 GROUP BY Q1.`id`)";
				// ค้นหาจากโมดูลอื่นๆที่ติดตั้ง
				foreach ($install_owners AS $item => $modules) {
					if (is_file(ROOT_PATH."modules/$item/search.php")) {
						include (ROOT_PATH."modules/$item/search.php");
					}
				}
				$sql = implode('UNION', $sqls);
				$list = $cache->get($sql);
				if (!$list) {
					$list = array();
					foreach ($db->customQuery($sql) AS $i => $item) {
						$v = 0;
						if (mb_stripos($item['topic'], $search) !== false) {
							$v = $word_count * 100;
						} elseif (mb_stripos($item['detail'], $search) !== false) {
							$v = $word_count * 95;
						}
						foreach ($words As $a => $word) {
							if (mb_stripos($item['topic'], $word) !== false) {
								$v = $v + (90 - $a);
							}
							if (mb_stripos($item['detail'], $word) !== false) {
								$v = $v + (85 - $a);
							}
						}
						$search_result[$i]['id'] = $item['id'];
						$search_result[$i]['category_id'] = $item['category_id'];
						$search_result[$i]['topic'] = $item['topic'];
						$search_result[$i]['alias'] = $item['alias'];
						$search_result[$i]['index'] = $item['index'];
						if ($item['description'] == '') {
							$search_result[$i]['detail'] = gcms::cutstring(gcms::html2txt($item['detail']), 149);
						} else {
							$search_result[$i]['detail'] = $item['description'];
						}
						$search_result[$i]['module'] = $item['module'];
						$search_result[$i]['owner'] = $item['owner'];
						$search_result[$i]['value'] = $v * ($item['level'] + $item['index']);
					}
					if (sizeof($search_result) > 0) {
						// เรียงลำดับผลลัพท์ตาม score
						gcms::sortby($search_result, 'value', false);
						// จัดรูปแบบข้อความ
						$searchitem = gcms::loadtemplate('search', 'search', 'searchitem');
						$match = array('/{URL}/', '/{TOPIC}/', '/{DETAIL}/', '/{LINK}/');
						foreach ($search_result AS $item) {
							unset($data);
							if ($item['owner'] == 'index' || $item['index'] == 1) {
								$url1 = gcms::getURL($item['module']);
								$url2 = gcms::getURL($item['module'], '', 0, 0, '', false);
							} elseif ($item['owner'] == 'board') {
								$url1 = gcms::getURL($item['module'], '', 0, 0, "wbid=$item[id]");
								$url2 = $url1;
							} elseif ($item['owner'] == 'document') {
								if ($config['module_url'] == 1) {
									$url1 = gcms::getURL($item['module'], $item['alias'], 0, 0, 'q='.urlencode($search), true);
									$url2 = gcms::getURL($item['module'], $item['alias'], 0, 0, '', false);
								} else {
									$url1 = gcms::getURL($item['module'], '', 0, $item['id'], 'q='.urlencode($search), true);
									$url2 = gcms::getURL($item['module'], '', 0, $item['id'], '', false);
								}
							} else {
								$url1 = gcms::getURL($item['module'], '', 0, 0, "id=$item[id]", false);
								$url2 = $url1;
							}
							$data[] = $url1;
							$data[] = $item['topic'];
							$data[] = $item['detail'];
							$data[] = $url2;
							$list[] = preg_replace($match, $data, $searchitem);
						}
						if (sizeof($list) > 0) {
							// save cache
							$cache->save($sql, $list);
						}
					}
				}
			}
			// จัดการแบ่งหน้าเพื่อแสดงผล
			$rows = sizeof($list);
			$maxlink = 9;
			$totalpage = round($rows / $list_per_page);
			$totalpage += ($totalpage * $list_per_page < $rows) ? 1 : 0;
			$page = ($page < 1) ? 1 : $page;
			$page = $page > $totalpage ? $totalpage : $page;
			$start = $list_per_page * ($page - 1);
			$end = ($start + $list_per_page > $rows) ? $rows : $start + $list_per_page;
			if ($rows > 0) {
				for ($i = $rows - 1; $i >= 0; $i--) {
					if ($i >= $end || $i < $start) {
						unset($list[$i]);
					}
				}
				$url = '<a href="'.gcms::getURL('search', '', 0, 0, 'page=%1&amp;q='.urlencode($search)).'">%1</a>';
				if ($totalpage > $maxlink) {
					$s = $page - floor($maxlink / 2);
					if ($s < 1) {
						$s = 1;
					} elseif ($s + $maxlink > $totalpage) {
						$s = $totalpage - $maxlink + 1;
					}
				} else {
					$s = 1;
				}
				$splitpage = ($s > 1) ? str_replace('%1', 1, $url) : ' ';
				for ($i = $s; $i <= $totalpage && $maxlink > 0; $i++) {
					$splitpage .= ($i == $page) ? '<strong>'.$i.'</strong> ' : str_replace('%1', $i, $url);
					$maxlink--;
				}
				$splitpage .= ($i < $totalpage) ? str_replace('%1', $totalpage, $url) : '';
			}
		}
		// stop timer
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$time_end = $mtime[1] + $mtime[0];
		// breadcrumbs
		$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
		$breadcrumbs = array();
		// หน้าหลัก
		$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
		// หน้าค้นหา
		$canonical = WEB_URL.'/index.php?module=search&q='.urlencode($search);
		$breadcrumbs['MODULE'] = gcms::breadcrumb('', $canonical, '{LNG_SEARCH}', '{LNG_SEARCH}', $breadcrumb);
		// แสดงผล
		$patt = array('/{BREADCRUMS}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{WEBURL}/', '/{MODULE}/', '/{SEARCH}/', '/{RESULT}/', '/{LIST}/', '/{SPLITPAGE}/');
		$replace = array();
		$replace[] = implode("\n", $breadcrumbs);
		$replace[] = 'gcms::getLng';
		$replace[] = WEB_URL;
		$replace[] = $module;
		$replace[] = $search;
		$replace[] = sizeof($list) == 0 ? '' : sprintf($lng['ALL_SEARCH'], $start + 1, $end, $rows, $search, number_format($time_end - $time_start, 4));
		if ($search == '') {
			$replace[] = $lng['LNG_SEARCH_TIP'];
		} elseif (sizeof($list) == 0) {
			$replace[] = sprintf($lng['LNG_SEARCH_NOT_FOUND'], $search, $index['menu_text']).$lng['LNG_SEARCH_TIP'];
		} else {
			$replace[] = gcms::HighlightSearch(implode("\n", $list), $search);
		}
		$replace[] = $splitpage;
		$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate('search', 'search', 'search'));
		// title, keywords, description
		$title = ($search == '' ? "" : "$search - ").$lng['LNG_SEARCH'];
		$keywords = "$title $keywords";
		$description = "$title $description";
	}
