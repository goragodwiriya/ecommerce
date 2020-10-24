<?php
	// modules/document/main.php
	if (defined('MAIN_INIT')) {
		// เลือกไฟล์
		if (isset($_REQUEST['id'])) {
			// แสดง document ตาม id
			include (ROOT_PATH.'modules/document/view.php');
		} else {
			// แสดงหมวด หรือ ลิสต์รายการ
			include (ROOT_PATH.'modules/document/list.php');
		}
	}
