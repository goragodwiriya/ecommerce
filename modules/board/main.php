<?php
	// modules/board/main.php
	if (defined('MAIN_INIT')) {
		// เลือกไฟล์
		if (isset($_REQUEST['wbid']) || isset($_REQUEST['id'])) {
			// แสดง board ตาม id
			include (ROOT_PATH.'modules/board/view.php');
		} else {
			// แสดงหมวด หรือ ลิสต์รายการกระทู้
			include (ROOT_PATH.'modules/board/list.php');
		}
	}
