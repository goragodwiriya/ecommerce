<?php
	// modules/index/admin_pages.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		unset($url_query['id']);
		// query หน้าเว็บทั้งหมดที่สร้างจากโมดูล Index
		$sql1 .= "FROM `".DB_MODULES."` AS M";
		$sql1 .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`module_id`=M.`id`";
		$sql1 .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`module_id`=M.`id` AND I.`language`=D.`language`";
		// ข้อความค้นหา
		$search = $db->sql_trim_str($_GET['search']);
		// ค้นหาจากชื่อโมดูลหรือหัวข้อ
		$sql1 .= " WHERE M.`owner`='index'";
		if ($search != '') {
			$sql1 .= " AND (D.`topic` LIKE '%$search%' OR D.`keywords` LIKE '%$search%' OR D.`detail` LIKE '%$search%')";
			$url_query['search'] = urlencode($search);
		}
		// จำนวนเรื่องทั้งหมด
		$sql = "SELECT COUNT(*) AS `count` $sql1";
		$count = $db->customQuery($sql);
		// รายการต่อหน้า
		$list_per_page = (int)(isset($_GET['count']) ? $_GET['count'] : $_COOKIE['index_listperpage']);
		$list_per_page = $list_per_page == 0 ? 30 : $list_per_page;
		// หน้าที่เลือก
		$page = max(1, (int)$_GET['page']);
		// ตรวจสอบหน้าที่เลือกสูงสุด
		$totalpage = round($count[0]['count'] / $list_per_page);
		$totalpage += ($totalpage * $list_per_page < $count[0]['count']) ? 1 : 0;
		$page = max(1, $page > $totalpage ? $totalpage : $page);
		$start = $list_per_page * ($page - 1);
		// คำนวณรายการที่แสดง
		$s = $start < 0 ? 0 : $start + 1;
		$e = min($count[0]['count'], $s + $list_per_page - 1);
		$patt2 = array('/{SEARCH}/', '/{COUNT}/', '/{PAGE}/', '/{TOTALPAGE}/', '/{START}/', '/{END}/');
		$replace2 = array($search, $count[0]['count'], $page, $totalpage, $s, $e);
		// save ฟิลเตอร์ลง cookie
		setCookie('index_listperpage', $list_per_page, time() + 3600 * 24 * 365);
		// title
		$title = $lng['LNG_WEB_PAGES_TITLE'];
		$a = array();
		$a[] = '<span class=icon-modules>{LNG_MENUS}&nbsp;&amp;&nbsp;{LNG_WEB_PAGES}</span>';
		$a[] = '{LNG_WEB_PAGES}';
		// แสดงผล
		$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
		$content[] = '<section>';
		$content[] = '<header><h1 class=icon-index>'.$title.'</h1></header>';
		$content[] = '<form class=table_nav method=get action=index.php>';
		// รายการต่อหน้า
		$content[] = '<fieldset>';
		$content[] = '<label>{LNG_LIST_PER_PAGE} <select name=count>';
		foreach (array(10, 20, 30, 40, 50, 100) AS $item) {
			$sel = $item == $list_per_page ? 'selected' : '';
			$content[] = '<option value='.$item.' '.$sel.'>'.$item.' {LNG_ITEMS}</option>';
		}
		$content[] = '</select></label>';
		$content[] = '<label><input type=submit class="button go" value="{LNG_GO}"></label>';
		$content[] = '<input name=module type=hidden value=index-pages>';
		$content[] = '</fieldset>';
		// add
		$content[] = '<fieldset>';
		$content[] = '<a class="button add" href="{URLQUERY?module=index-write&owner=index&src=index-pages}"><span class=icon-add>{LNG_ADD_NEW} {LNG_PAGE}</span></a>';
		$content[] = '</fieldset>';
		// search
		$content[] = '<fieldset class=search>';
		$content[] = '<label accesskey=f><input type=text name=search value="'.$search.'" placeholder="{LNG_SEARCH_TITLE}" title="{LNG_SEARCH_TITLE}"></label>';
		$content[] = '<input type=submit value="&#xE607;" title="{LNG_SEARCH}">';
		$content[] = '</fieldset>';
		$content[] = '</form>';
		// ตารางข้อมูล
		$content[] = '<table id=pages class="tbl_list fullwidth">';
		$content[] = '<caption>'.preg_replace($patt2, $replace2, $search != '' ? $lng['SEARCH_RESULT'] : $lng['ALL_ITEMS']).'</caption>';
		$content[] = '<thead>';
		$content[] = '<tr>';
		$content[] = '<th id=c0 scope=col>{LNG_TOPIC}</th>';
		$content[] = '<th id=c1 scope=col><span class=mobile>{LNG_PUBLISHED}</span></th>';
		$content[] = '<th id=c2 scope=col>{LNG_LANGUAGE}</th>';
		$content[] = '<th id=c3 scope=col>{LNG_MODULE_NAME}</th>';
		$content[] = '<th id=c4 scope=col class="center tablet">{LNG_LAST_UPDATE}</th>';
		$content[] = '<th id=c5 scope=col class="center tablet">{LNG_VIEWS}</th>';
		$content[] = '<th id=c6 scope=col colspan=2>&nbsp;</th>';
		$content[] = '</tr>';
		$content[] = '</thead>';
		$content[] = '<tbody>';
		// query
		$sql = "SELECT I.`id`,D.`topic`,I.`last_update`,I.`language`,I.`visited`,I.`published`,M.`id` AS `module_id`,M.`owner`,M.`module`";
		$sql .= " $sql1 ORDER BY M.`module`,I.`language`";
		$sql .= " LIMIT $start, $list_per_page";
		foreach ($db->customQuery($sql) AS $item) {
			$id = $item['id'];
			$content[] = '<tr id=M_'.$id.'>';
			$content[] = '<th headers=c0 id=r'.$id.' scope=row class=topic><a href="{WEBURL}/index.php?module=index&amp;id='.$id.'&amp;visited" title="{LNG_PREVIEW}" target=preview>'.$item['topic'].'</a></th>';
			$content[] = '<td headers="r'.$id.' c1" class=menu><a id=published_index_'.$id.' class="icon-published'.$item['published'].'" title="'.$lng['LNG_PUBLISHEDS'][$item['published']].'"></a></td>';
			$content[] = '<td headers="r'.$id.' c2" class=menu>'.($item['language'] == '' ? '&nbsp;' : '<img src='.WEB_URL.'/datas/language/'.$item['language'].'.gif alt="'.$item['language'].'">').'</td>';
			$content[] = '<td headers="r'.$id.' c3">'.$item['module'].'</td>';
			$content[] = '<td headers="r'.$id.' c4" class="date tablet">'.gcms::mktime2date($item['last_update'], 'd M Y H:i').' {LNG_TIME_DIGIT}</td>';
			$content[] = '<td headers="r'.$id.' c5" class="visited tablet">'.$item['visited'].'</td>';
			$content[] = '<td headers="r'.$id.' c6" class=menu><a class=icon-edit href="{URLQUERY?module=index-write&src=index-pages&spage='.$page.'&id='.$id.'}" title="{LNG_EDIT}"></a></td>';
			$content[] = '<td headers="r'.$id.' c6" class=menu><a class=icon-delete href="{URLQUERY}" title="{LNG_DELETE}" id=delete_index_'.$id.'></a></td>';
			$content[] = '</tr>';
		}
		$content[] = '</tbody>';
		// แบ่งหน้า
		$maxlink = 9;
		$url = '<a href="{URLQUERY?page=%d}" title="{LNG_DISPLAY_PAGE} %d">%d</a>';
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
		$splitpage = ($start > 2) ? str_replace('%d', 1, $url) : '';
		for ($i = $start; $i <= $totalpage && $maxlink > 0; $i++) {
			$splitpage .= ($i == $page) ? '<strong title="{LNG_DISPLAY_PAGE} '.$i.'">'.$i.'</strong>' : str_replace('%d', $i, $url);
			$maxlink--;
		}
		$splitpage .= ($i < $totalpage) ? str_replace('%d', $totalpage, $url) : '';
		$splitpage = $splitpage == '' ? '<strong title="{LNG_DISPLAY_PAGE} '.$i.'">1</strong>' : $splitpage;
		$content[] = '</table>';
		$content[] = '<p class=splitpage>'.$splitpage.'</p>';
		$content[] = '</section>';
		$content[] = '<script>';
		$content[] = '$G(window).Ready(function(){';
		$content[] = "inintTR('pages', /M_[0-9]+/);";
		$content[] = "inintIndexPages('pages', false);";
		$content[] = '});';
		$content[] = '</script>';
		// หน้านี้
		$url_query['module'] = 'index-pages';
		$url_query['page'] = $page;
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
