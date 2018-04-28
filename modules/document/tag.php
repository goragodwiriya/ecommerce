<?php
	// modules/document/tag.php
	// หน้าแสดงรายการจาก tag
	if (defined('MAIN_INIT')) {
		if ($modules[4] != '') {
			$tag = $modules[4];
			// แสดงรายการ list
			include (ROOT_PATH.'modules/document/list.php');
		} else {
			$title = $lng['PAGE_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		}
	}
