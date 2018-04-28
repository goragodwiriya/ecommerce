<?php
	// modules/gallery/admin_album.php
	if (MAIN_INIT == 'admin' && gcms::canConfig($config['gallery_can_write'])) {
		unset($url_query['id']);
		// ตรวจสอบโมดูลที่เรียก
		$sql = "SELECT `id`,`module` FROM `".DB_MODULES."` WHERE `owner`='gallery' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 0) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			$index = $index[0];
			// จำนวนรายการทั้งหมด
			$sql = "SELECT COUNT(*) AS `count` FROM `".DB_GALLERY_ALBUM."` WHERE `module_id`=$index[id]";
			$count = $db->customQuery($sql);
			$count = $count[0];
			// รายการต่อหน้า
			$list_per_page = (int)(isset($_GET['count']) ? $_GET['count'] : $_COOKIE['gallery_listperpage']);
			$list_per_page = $list_per_page == 0 ? 30 : $list_per_page;
			setCookie('gallery_listperpage', $list_per_page, time() + 3600 * 24 * 365);
			// หน้าที่เรียก
			$page = (int)$_GET['page'];
			$totalpage = round($count['count'] / $list_per_page);
			$totalpage += ($totalpage * $list_per_page < $count['count']) ? 1 : 0;
			$page = $page > $totalpage ? $totalpage : $page;
			$page = $page < 1 ? 1 : $page;
			$start = $list_per_page * ($page - 1);
			// คำนวณรายการที่แสดง
			$s = $start < 0 ? 0 : $start + 1;
			$e = min($count[0]['count'], $s + $list_per_page - 1);
			$patt2 = array('/{COUNT}/', '/{PAGE}/', '/{TOTALPAGE}/', '/{START}/', '/{END}/');
			$replace2 = array($count[0]['count'], $page, $totalpage, $s, $e);
			// title
			$title = "$lng[LNG_CREATE] - $lng[LNG_EDIT] $lng[LNG_GALLERY_ALBUM]";
			$a = array();
			$a[] = '<span class=icon-gallery>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=gallery-config}">{LNG_GALLERY}</a>';
			$a[] = '<a href="{URLQUERY?module=gallery-album}">{LNG_GALLERY_ALBUM}</a>';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-gallery>'.$title.'</h1></header>';
			// form
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
			// submit
			$content[] = '<fieldset>';
			$content[] = '<input type=submit class="button go" value="{LNG_GO}">';
			$content[] = '<input type=hidden name=module value=gallery-album>';
			$content[] = '<input type=hidden name=page value=1>';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			// ตารางข้อมูล
			$content[] = '<table id=gallery class="tbl_list fullwidth">';
			$content[] = '<caption>'.preg_replace($patt2, $replace2, $lng['ALL_ITEMS']).'</caption>';
			$content[] = '<thead>';
			$content[] = '<tr>';
			$content[] = '<th id=c0 scope=col colspan=2>{LNG_GALLERY_ALBUM}</th>';
			$content[] = '<th id=c1 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
			$content[] = '<th id=c2 scope=col class="center mobile">{LNG_VIEWS}</th>';
			$content[] = '<th id=c3 scope=col class=center><span class=mobile>{LNG_IMAGE_COUNT}</span></th>';
			$content[] = '<th id=c4 scope=col class="center tablet">{LNG_LAST_UPDATE}</th>';
			$content[] = '<th id=c5 scope=col colspan=2>&nbsp;</th>';
			$content[] = '</tr>';
			$content[] = '</thead>';
			$content[] = '<tbody>';
			// query
			$sql = "SELECT `image` FROM `".DB_GALLERY."` WHERE `album_id`=A.`id` AND `module_id`=A.`module_id` ORDER BY `count` LIMIT 1";
			$sql = "SELECT A.`id`,A.`topic`,A.`detail`,A.`count`,A.`visited`,A.`last_update`,($sql) AS `image` FROM `".DB_GALLERY_ALBUM."` AS A";
			$sql .= " WHERE A.`module_id`=$index[id] ORDER BY A.`id` DESC LIMIT $start,$list_per_page";
			foreach ($db->customQuery($sql) AS $item) {
				$id = $item['id'];
				$tr = '<tr id=M_'.$id.'>';
				$tr .= '<th headers=c0 id=r'.$id.'><a href="../index.php?module='.$index['module'].'&amp;id='.$id.'" title="{LNG_PREVIEW}" target=_blank class=topic>'.$item['topic'].'</a></th>';
				if (is_file(DATA_PATH."gallery/$item[id]/thumb_$item[image]")) {
					$tr .= '<td headers="r'.$id.' c0" class=thumb><img src="'.DATA_URL.'gallery/'.$item['id'].'/thumb_'.$item['image'].'" alt=thumbnail></td>';
				} else {
					$tr .= '<td headers="r'.$id.' c0" class=thumb><img src="'.WEB_URL.'/modules/gallery/img/nopicture.png" alt=nothumbnail></td>';
				}
				$tr .= '<td headers="r'.$id.' c1" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
				$tr .= '<td headers="r'.$id.' c2" class="visited mobile">'.$item['visited'].'</td>';
				$tr .= '<td headers="r'.$id.' c3" class=count>'.$item['count'].'</td>';
				$tr .= '<td headers="r'.$id.' c4" class="date tablet">'.gcms::mktime2date($item['last_update'], 'd M Y H:i').'</td>';
				$tr .= '<td headers="r'.$id.' c5" class=menu><a href="{URLQUERY?module=gallery-write&src=gallery-album&id='.$id.'}" title="{LNG_EDIT}" class=icon-edit></a></td>';
				$tr .= '<td headers="r'.$id.' c5" class=menu><a href="{URLQUERY?module=gallery-upload&src=gallery-album&id='.$id.'}" title="{LNG_UPLOAD}" class=icon-upload></a></td>';
				$tr .= '</tr>';
				$content[] = $tr;
			}
			$content[] = '</tbody>';
			$content[] = '<tfoot>';
			$content[] = '<tr>';
			$content[] = '<td headers=c0 colspan=2></td>';
			$content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
			$content[] = '<td headers=c2 colspan=5></td>';
			$content[] = '</tr>';
			$content[] = '</tfoot>';
			$content[] = '</table>';
			// แบ่งหน้า
			$maxlink = 9;
			$url = '<a href="{URLQUERY?id='.$index['id'].'&page=%d}" title="{LNG_DISPLAY_PAGE} %d">%d</a>';
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
			$content[] = '<div class=table_nav>';
			// sel action
			$content[] = '<select id=sel_action><option value=delete>{LNG_DELETE}</option></select>';
			$content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action>{LNG_SELECT_ACTION}</label>';
			$content[] = '<a class="button add" href="index.php?module=gallery-write&amp;src=gallery-album"><span class=icon-add>{LNG_ADD_NEW} {LNG_GALLERY_ALBUM}</span></a>';
			$content[] = '</div>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = "inintCheck('gallery');";
			$content[] = "inintTR('gallery', /M_[0-9]+/);";
			$content[] = 'callAction("btn_action", function(){return $E("sel_action").value}, "gallery", "{WEBURL}/modules/gallery/admin_action.php");';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'gallery-album';
			$url_query['page'] = $page;
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
