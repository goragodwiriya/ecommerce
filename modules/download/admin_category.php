<?php
	// modules/download/admin_category.php
	if (MAIN_INIT == 'admin' && gcms::canConfig($config['download_can_config'])) {
		// ตรวจสอบโมดูลที่เรียก
		$sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='download' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 1) {
			$index = $index[0];
			// title
			$title = "$lng[LNG_CREATE] - $lng[LNG_EDIT] $lng[LNG_CATEGORY]";
			$a = array();
			$a[] = '<span class=icon-download>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=download-config}">{LNG_DOWNLOAD}</a>';
			$a[] = '{LNG_CATEGORY}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-category>'.$title.'</h1></header>';
			// หมวดหมู่
			$content[] = '<div class=subtitle>{LNG_DOWNLOAD_CATEGORY_DETAIL}</div>';
			$content[] = '<dl id=config_category class=editinplace_list>';
			$sql = "SELECT `id`,`category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' ORDER BY `category_id`";
			foreach ($db->customQuery($sql) AS $item) {
				$id = $item['id'];
				$row = '<dd id=config_category_'.$id.'>';
				$row .= '<span class=no>['.$item['category_id'].']</span>';
				$row .= '<span class=icon-delete id=config_category_delete_'.$id.' title="{LNG_DELETE} {LNG_CATEGORY}"></span>';
				$row .= '{LNG_CATEGORY} <span id=config_category_name_'.$id.' title="{LNG_CLICK_TO} {LNG_EDIT}">'.htmlspecialchars(gcms::ser2Str($item['topic'])).'</span>';
				$row .= '</dd>';
				$content[] = $row;
			}
			$content[] = '</dl>';
			// submit
			$content[] = '<div class=submit>';
			$content[] = '<a id=config_category_add class="button large add"><span class=icon-add>{LNG_ADD_NEW} {LNG_CATEGORY}</span></a>';
			$content[] = '</div>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = "inintModuleCategory('config_category', '$index[id]', 'download');";
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'download-category';
		} else {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
