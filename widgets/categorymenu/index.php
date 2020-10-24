<?php
	// widgets/categorymenu/index.php
	$widget = '';
	if (defined('MAIN_INIT') && preg_match('/^([a-z]+)$/', $module)) {
		// product module
		$module = $install_modules[$module];
		$widget = array();
		$sql = "SELECT `category_id`,`icon`,`subcategory`,`topic` FROM `".DB_CATEGORY."`";
		$sql .= " WHERE `module_id`='$module[module_id]' AND `published`='1' ORDER BY `category_id`";
		$datas = $cache->get($sql);
		if (!$datas) {
			$datas = $db->customQuery($sql);
			$cache->save($sql, $datas);
		}
		foreach ($datas AS $item) {
			$item['topic'] = gcms::ser2Str($item['topic']);
			$item['subcategory'] = gcms::ser2Array($item['subcategory']);
			$item['icon'] = gcms::ser2Str($item['icon']);
			$icon = is_file(DATA_PATH."product/$item[icon]") ? '<img src="'.DATA_URL.'product/'.$item['icon'].'" alt="'.$item['topic'].'">' : '';
			if ($config['product_show_menu'] == 1) {
				$item['topic'] = '';
			} elseif ($config['product_show_menu'] == 2) {
				$icon = '';
			}
			$row = '<li>';
			if (sizeof($item['subcategory']) > 0) {
				$row .= '<a href=# class=menu-arrow><span>'.$icon.$item['topic'].'</span></a>';
				$row .= '<ul>';
				foreach ($item['subcategory'] AS $a => $sub) {
					$row .= '<li><a href="'.gcms::getURL($module['module'], '', 0, 0, "cat=$item[category_id]&amp;sub=$a").'"><span>'.$sub[LANGUAGE].'</span></a></li>';
				}
				$row .= '</ul>';
			} else {
				$row .= '<a href="'.gcms::getURL($module['module'], '', 0, 0, "cat=$item[category_id]").'"><span>'.$icon.$item['topic'].'</span></a>';
			}
			$row .= '</li>';
			$widget[] = $row;
		}
		$widget = implode("\n", $widget);
	}
