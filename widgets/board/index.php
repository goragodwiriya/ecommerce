<?php
	// widgets/board/index.php
	$widget = '';
	if (defined('MAIN_INIT') && preg_match('/^[a-z0-9]{4,}$/', $module) && is_array($install_modules[$module])) {
		// module
		$index = $install_modules[$module];
		// อ่าน config
		gcms::r2config($index['config'], $index);
		// ค่าที่ส่งมา
		$id = $index['module_id'];
		$cat = (int)$cat;
		$count = (int)$count;
		$new_date = (int)$index['new_date'];
		$interval = isset($interval) ? (int)$interval : 60;
		$count = $count == 0 ? $index['news_count'] : $count;
		if ($count > 0) {
			// แสดงผล
			$patt = array('/{ID}/', '/{DETAIL}/', '/{MODULE}/');
			$replace = array();
			$replace[0] = "widget_{$index[module]}_{$id}_{$cat}_{$count}_{$new_date}_0_1";
			$replace[1] = "<script>getWidgetNews('$replace[0]', 'board', $interval);</script>";
			$replace[2] = $index['module'];
			$widget = preg_replace($patt, $replace, gcms::loadtemplate($index['module'], 'board', 'widget'));
		}
	}
