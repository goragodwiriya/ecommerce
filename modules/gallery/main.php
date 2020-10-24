<?php
	// modules/gallery/main.php
	if (defined('MAIN_INIT')) {
		if (isset($_REQUEST['id'])) {
			// แสดงอัลบัมที่เลือก
			include (ROOT_PATH.'modules/gallery/list.php');
		} else {
			include (ROOT_PATH.'modules/gallery/album.php');
		}
	}
