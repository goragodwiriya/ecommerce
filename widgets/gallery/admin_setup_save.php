<?php
	// widgets/gallery/admin_setup_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include ('../../bin/inint.php');
	// referer, admin
	if (gcms::isReferer() && gcms::isAdmin()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// โหลด config ใหม่
			$config = array();
			if (is_file(CONFIG)) {
				include CONFIG;
			}
			// ค่าที่ส่งมา
			$config['widget_gallery_rows'] = (int)$_POST['gallery_rows'];
			$config['widget_gallery_cols'] = (int)$_POST['gallery_cols'];
			$config['widget_gallery_tags'] = trim($_POST['gallery_tags']);
			$config['widget_gallery_album_id'] = (int)$_POST['gallery_album_id'];
			$config['widget_gallery_user_id'] = (int)$_POST['gallery_user_id'];
			$config['widget_gallery_width'] = (int)$_POST['gallery_width'];
			$config['widget_gallery_url'] = trim($_POST['gallery_url']);
			// ตรวจสอบค่าที่ส่งมา
			$config['widget_gallery_rows'] = $config['widget_gallery_rows'] < 1 ? 1 : $config['widget_gallery_rows'];
			$config['widget_gallery_cols'] = $config['widget_gallery_cols'] < 1 ? 1 : $config['widget_gallery_cols'];
			$config['widget_gallery_width'] = $config['widget_gallery_width'] < 50 ? 50 : $config['widget_gallery_width'];
			if ($config['widget_gallery_url'] == '') {
				$ret['error'] = 'REGISTER_INVALID_WEBSITE';
				$ret['input'] = 'gallery_url';
			} else {
				// บันทึก config.php
				if (gcms::saveConfig(CONFIG, $config)) {
					$ret['error'] = 'SAVE_COMPLETE';
				} else {
					$ret['error'] = 'DO_NOT_SAVE';
				}
			}
		}
		// คืนค่า JSON
		echo gcms::array2json($ret);
	}
