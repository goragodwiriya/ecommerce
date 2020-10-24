<?php
	// modules/index/admin_insmod.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// ข้อความค้นหา
		$search = $db->sql_trim_str($_GET['search']);
		// ค้นหาจากชื่อโมดูลหรือหัวข้อ
		if ($search != '') {
			$where = "WHERE `owner`='$search' OR `module`='$search' OR `topic` LIKE '%$search%'";
			$url_query['search'] = urlencode($search);
		} else {
			$where = '';
		}
		$sql1 = " FROM `".DB_INDEX."` AS I";
		$sql1 .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id` AND M.`owner`!='index'";
		$sql1 .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id`";
		$sql1 .= " WHERE I.`index`='1'";
		// จำนวนเรื่องทั้งหมด
		$sql = "SELECT COUNT(*) AS `count` $sql1 $where";
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
		$title = $lng['LNG_INSTALLED_MODULE_TITLE'];
		$a = array();
		$a[] = '<span class=icon-modules>{LNG_MENUS}&nbsp;&amp;&nbsp;{LNG_WEB_PAGES}</span>';
		$a[] = '{LNG_INSTALLED_MODULE}';
		// แสดงผล
		$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
		$content[] = '<section>';
		$content[] = '<header><h1 class=icon-modules>'.$title.'</h1></header>';
		$content[] = '<form class=table_nav method=get action=index.php>';
		// รายการต่อหน้า
		$content[] = '<fieldset>';
		$content[] = '<label>{LNG_LIST_PER_PAGE} <select name=count>';
		foreach (array(10, 20, 30, 40, 50, 100) AS $item) {
			$sel = $item == $list_per_page ? 'selected' : '';
			$content[] = '<option value='.$item.' '.$sel.'>'.$item.' {LNG_ITEMS}</option>';
		}
		$content[] = '</select></label>';
		$content[] = '</fieldset>';
		// submit
		$content[] = '<fieldset>';
		$content[] = '<input type=submit class="button go" value="{LNG_GO}">';
		$content[] = '<input name=module type=hidden value=index-insmod>';
		$content[] = '</fieldset>';
		// add
		$content[] = '<fieldset>';
		$content[] = '<a class="button add" href="{URLQUERY?module=index-add&src=index-insmod}"><span class=icon-add>{LNG_ADD_NEW} {LNG_MODULE}</span></a>';
		$content[] = '</fieldset>';
		// search
		$content[] = '<fieldset class=search>';
		$content[] = '<label accesskey=f><input type=text name=search value="'.$search.'" placeholder="{LNG_SEARCH_TITLE}" title="{LNG_SEARCH_TITLE}"></label>';
		$content[] = '<input type=submit value="&#xE607;" title="{LNG_SEARCH}">';
		$content[] = '</fieldset>';
		$content[] = '</form>';
		// ตารางข้อมูล
		$content[] = '<table id=insmod class="tbl_list fullwidth">';
		$content[] = '<caption>'.preg_replace($patt2, $replace2, $search != '' ? $lng['SEARCH_RESULT'] : $lng['ALL_ITEMS']).'</caption>';
		$content[] = '<thead>';
		$content[] = '<tr>';
		$content[] = '<th id=c0 scope=col>{LNG_TOPIC}</th>';
		$content[] = '<th id=c1 scope=col class=mobile>{LNG_PUBLISHED}</th>';
		$content[] = '<th id=c2 scope=col>{LNG_LANGUAGE}</th>';
		$content[] = '<th id=c3 scope=col>{LNG_MODULE_NAME}</th>';
		$content[] = '<th id=c4 scope=col class=tablet>{LNG_INSTALL_MODULE}</th>';
		$content[] = '<th id=c5 scope=col class="center mobile">{LNG_LAST_UPDATE}</th>';
		$content[] = '<th id=c6 scope=col class="center mobile">{LNG_VIEWS}</th>';
		$content[] = '<th id=c7 scope=col colspan=2>&nbsp;</th>';
		$content[] = '</tr>';
		$content[] = '</thead>';
		$content[] = '<tbody>';
		// query โมดูลที่ติดตั้ง
		$sql = "SELECT I.`id`,D.`topic`,I.`last_update`,I.`visited`,I.`language`,I.`published`";
		$sql .= ",M.`id` AS `module_id`,M.`owner`,M.`module`";
		$sql .= " $sql1 $where ORDER BY M.`owner`,M.`module`,I.`language` LIMIT $start, $list_per_page";
		foreach ($db->customQuery($sql) AS $i => $item) {
			$id = $item['id'];
			$content[] = '<tr id=M_'.$id.'>';
			$content[] = '<th headers=c0 id=r'.$id.' scope=row class=topic><a href="{WEBURL}/index.php?module='.$item['module'].'" title="{LNG_PREVIEW}">'.$item['topic'].'</a></th>';
			$content[] = '<td headers="r'.$id.' c1" class="menu mobile"><a id=published_index_'.$id.' class="icon-published'.$item['published'].'" title="'.$lng['LNG_PUBLISHEDS'][$item['published']].'"></a></td>';
			$content[] = '<td headers="r'.$id.' c2" class=menu>'.($item['language'] == '' ? '&nbsp;' : '<img src='.WEB_URL.'/datas/language/'.$item['language'].'.gif alt="'.$item['language'].'">').'</td>';
			$content[] = '<td headers="r'.$id.' c3">'.$item['module'].'</td>';
			$content[] = '<td headers="r'.$id.' c4" class=tablet>'.$item['owner'].'</td>';
			$content[] = '<td headers="r'.$id.' c5" class="date mobile">'.gcms::mktime2date($item['last_update'], 'd M Y H:i').' {LNG_TIME_DIGIT}</td>';
			$content[] = '<td headers="r'.$id.' c6" class="visited mobile">'.$item['visited'].'</td>';
			$content[] = '<td headers="r'.$id.' c7" class=menu><a class=icon-edit href="{URLQUERY?module=index-write&src=index-insmod&spage='.$page.'&id='.$id.'}" title="{LNG_EDIT}"></a></td>';
			$content[] = '<td headers="r'.$id.' c7" class=menu><a class=icon-delete href="{URLQUERY}" title="{LNG_DELETE}" id=delete_module_'.$id.'></a></td>';
			$content[] = '</tr>';
		}
		$content[] = '</tbody>';
		$content[] = '</table>';
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
		$content[] = '<p class=splitpage>'.$splitpage.'</p>';
		$content[] = '</section>';
		$content[] = '<script>';
		$content[] = '$G(window).Ready(function(){';
		$content[] = "inintTR('insmod', /M_[0-9]+/);";
		$content[] = "inintIndexPages('insmod', false);";
		$content[] = '});';
		$content[] = '</script>';
		// หน้านี้
		$url_query['module'] = 'index-insmod';
		$url_query['page'] = $page;
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
