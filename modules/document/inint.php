<?php
	// modules/document/inint.php
	if (defined('MAIN_INIT')) {
		// ติดตั้ง RSS
		foreach ($install_modules AS $items) {
			if ($items['owner'] == 'document') {
				// โหลด rss
				$topic = $items['menu_text'] == '' ? ucwords($items['module']) : $items['menu_text'];
				$meta[] = '<link rel=alternate type=application/rss+xml title="'.$topic.'" href="'.WEB_URL.'/'.$items['module'].'.rss">';
			}
		}
		// เมนูแสดงเรื่องที่เขียนโดยสมาชิก
		if (is_file(ROOT_PATH.'modules/document/write.php')) {
			$member_tabs['document'] = array('{LNG_DOCUMENT_MEMBER}', 'modules/document/member');
			// path ของ CKEDITOR
			$script[] = "window.CKEDITOR_BASEPATH='".WEB_URL."/ckeditor/';";
			// ckeditor
			$meta['CKEDITOR'] = '<script src='.WEB_URL.'/ckeditor/ckeditor.js></script>';
		}
	}
