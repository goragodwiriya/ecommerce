<?php
	// modules/download/admin_setup.php
	if (MAIN_INIT == 'admin' && gcms::canConfig($config['download_can_upload'])) {
		unset($url_query['id']);
		// ตรวจสอบโมดูลที่เรียก
		$sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='download' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 0) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			$index = $index[0];
			// ค่าที่ส่งมา
			$sqls = array();
			$sqls[] = "`module_id`='$index[id]'";
			// ข้อความค้นหา
			$search = $db->sql_trim_str($_GET['search']);
			if ($search != '') {
				$sqls[] = "(`name` LIKE '%$search%' OR `ext` LIKE '%$search%' OR `detail` LIKE '%$search%')";
				$url_query['search'] = urlencode($search);
			}
			// หมวดที่เลือก
			$cat = (int)$_GET['cat'];
			if ($cat > 0) {
				$sqls[] = "`category_id`='$cat'";
				$url_query['cat'] = $cat;
			}
			$where = sizeof($sqls) > 0 ? ' WHERE '.implode(' AND ', $sqls) : '';
			// จำนวนของ download
			$sql = "SELECT COUNT(*) AS `count` FROM `".DB_DOWNLOAD."` $where";
			$count = $db->customQuery($sql);
			// รายการต่อหน้า
			$list_per_page = (int)(isset($_GET['count']) ? $_GET['count'] : $_COOKIE['download_listperpage']);
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
			// คำสั่งที่ทำงานล่าสุด
			$action = trim($_GET['action']);
			// save ฟิลเตอร์ลง cookie
			setCookie('download_cat', $cat, time() + 3600 * 24 * 365);
			setCookie('download_listperpage', $list_per_page, time() + 3600 * 24 * 365);
			// title
			$title = "$lng[LNG_ADD]-$lng[LNG_EDIT] $lng[LNG_DOWNLOAD_FILES]";
			$a = array();
			$a[] = '<span class=icon-download>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=download-config&id=0}">{LNG_DOWNLOAD}</a>';
			$a[] = '{LNG_DOWNLOAD_FILES}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-list>'.$title.'</h1></header>';
			$content[] = '<div class=subtitle>{LNG_DOWNLOAD_FILES}</div>';
			$content[] = '<form class=table_nav method=get action=index.php>';
			// รายการต่อหน้า
			$content[] = '<fieldset>';
			$content[] = '<label>{LNG_LIST_PER_PAGE} <select name=count>';
			foreach (array(10, 20, 30, 40, 50, 100) AS $item) {
				$sel = $item == $list_per_page ? ' selected' : '';
				$content[] = '<option value='.$item.$sel.'>'.$item.' {LNG_ITEMS}</option>';
			}
			$content[] = '</select></label>';
			$content[] = '</fieldset>';
			// หมวดหมู่
			$categories = array();
			$categories[0] = '{LNG_ALL} {LNG_CATEGORY}';
			$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' ORDER BY `category_id`";
			foreach ($db->customQuery($sql) AS $item) {
				$categories[$item['category_id']] = gcms::ser2Str($item['topic']);
			}
			$content[] = '<fieldset>';
			$content[] = '<label>{LNG_CATEGORY} <select name=cat>';
			foreach ($categories AS $i => $item) {
				$sel = $i == $cat ? ' selected' : '';
				$content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
			}
			$content[] = '</select></label>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset>';
			$content[] = '<input type=submit class="button go" value="{LNG_GO}">';
			$content[] = '</fieldset>';
			// search
			$content[] = '<fieldset class=search>';
			$content[] = '<label accesskey=f><input type=text name=search value="'.$search.'" placeholder="{LNG_SEARCH_TITLE}" title="{LNG_SEARCH_TITLE}"></label>';
			$content[] = '<input type=submit value="&#xE607;" title="{LNG_SEARCH}">';
			$content[] = '<input type=hidden name=module value=download-setup>';
			$content[] = '<input type=hidden name=page value=1>';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			// ตารางข้อมูล
			$content[] = '<table id=tbl_download class="tbl_list fullwidth">';
			$content[] = '<caption>'.preg_replace($patt2, $replace2, $search != '' ? $lng['SEARCH_RESULT'] : $lng['ALL_ITEMS']).'</caption>';
			$content[] = '<thead>';
			$content[] = '<tr>';
			$content[] = '<th id=c0 scope=col colspan=2>{LNG_DOWNLOAD_NAME}</th>';
			$content[] = '<th id=c1 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
			$content[] = '<th id=c2 scope=col class="center tablet">{LNG_DOWNLOAD_CODE}</th>';
			$content[] = '<th id=c3 scope=col class=mobile>{LNG_DESCRIPTION}</th>';
			$content[] = '<th id=c4 scope=col class=center>{LNG_SIZE_OF} {LNG_FILE}</th>';
			$content[] = '<th id=c5 scope=col class="center mobile">{LNG_DOWNLOAD_FILE_TIME}</th>';
			$content[] = '<th id=c6 scope=col class="center tablet">{LNG_DOWNLOAD}</th>';
			$content[] = '<th id=c7 scope=col>&nbsp;</th>';
			$content[] = '</tr>';
			$content[] = '</thead>';
			$content[] = '<tbody>';
			$sql = "SELECT * FROM `".DB_DOWNLOAD."` $where ORDER BY `last_update` DESC LIMIT $start,$list_per_page";
			foreach ($db->customQuery($sql) AS $item) {
				$id = $item['id'];
				$file_exists = file_exists(iconv('UTF-8', 'TIS-620', ROOT_PATH.$item['file']));
				$icon = "skin/ext/$item[ext].png";
				$icon = WEB_URL.(is_file(ROOT_PATH.$icon) ? "/$icon" : "/skin/ext/file.png");
				$tr = '<tr id="M_'.$id.'">';
				$tr .= '<th headers=c0 id=r'.$id.' scope=row>';
				if ($file_exists) {
					$tr .= '<a href="'.WEB_URL.'/modules/download/admin_download.php?id='.$id.'" target=_blank title="{LNG_CLICK_TO} {LNG_DOWNLOAD}">'.$item['name'].'.'.$item['ext'].'</a>';
				} else {
					$tr .= $item['name'].'.'.$item['ext'];
				}
				$tr .= '</th>';
				$tr .= '<td headers="r'.$id.' c0" class=menu><img src='.$icon.' alt=thumbnail width=16 height=16></td>';
				$tr .= '<td headers="r'.$id.' c1" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
				$tr .= '<td headers="r'.$id.' c2" class="no tablet">{WIDGET_DOWNLOAD_'.$id.'}</td>';
				$tr .= '<td headers="r'.$id.' c3" class=mobile>'.$item['detail'].'</td>';
				$tr .= '<td headers="r'.$id.' c4" class='.($file_exists ? 'size' : 'notfound').'>'.gcms::formatFileSize($item['size']).'</td>';
				$tr .= '<td headers="r'.$id.' c5" class="date mobile">'.gcms::mktime2date($item['last_update']).'</td>';
				$tr .= '<td headers="r'.$id.' c6" class="visited tablet">'.$item['downloads'].'</td>';
				$tr .= '<td headers="r'.$id.' c7" class=menu><a href="{URLQUERY?module=download-write&id='.$id.'}" title="{LNG_EDIT}" class=icon-edit></a></td>';
				$tr .= '</tr>';
				$content[] = $tr;
			}
			$content[] = '</tbody>';
			$content[] = '<tfoot>';
			$content[] = '<tr>';
			$content[] = '<td headers=c0 colspan=2></td>';
			$content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
			$content[] = '<td headers=c2 colspan=6>&nbsp;</td>';
			$content[] = '</tr>';
			$content[] = '</tfoot>';
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
			$content[] = '<div class=splitpage>'.$splitpage.'</div>';
			$content[] = '<div class=table_nav>';
			// sel action
			$content[] = '<fieldset>';
			$content[] = '<select id=sel_action><option value=delete>{LNG_DELETE}</option></select>';
			$content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action><span>{LNG_SELECT_ACTION}</span></label>';
			$content[] = '</fieldset>';
			// add
			$content[] = '<fieldset>';
			$content[] = '<a class="button add" href="{URLQUERY?module=download-write&src=download-setup}"><span class=icon-add>{LNG_UPLOAD}</span></a>';
			$content[] = '</fieldset>';
			$content[] = '</div>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = 'inintCheck("tbl_download");';
			$content[] = 'inintTR("tbl_download", /M_[0-9]+/);';
			$content[] = 'callAction("btn_action", function(){return $E("sel_action").value}, "tbl_download", "'.WEB_URL.'/modules/download/admin_action.php");';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'download-setup';
			$url_query['page'] = $page;
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
