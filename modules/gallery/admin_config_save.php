<?php
	// modules/gallery/admin_config_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, admin
	if (gcms::isReferer() && gcms::canConfig($config['gallery_can_config'])) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ตรวจสอบค่าที่ส่งมา
			if (!isset($_POST['config_image_type'])) {
				$ret['error'] = 'UPLOAD_TYPE_EMPTY';
				$ret['ret_config_image_type'] = 'UPLOAD_TYPE_EMPTY';
				$ret['input'] = 'config_image_type';
			} else {
				$ret['ret_config_image_type'] = '';
				// โหลด config ใหม่
				$config = array();
				if (is_file(CONFIG)) {
					include CONFIG;
				}
				// ค่าที่ส่งมา
				$config['gallery_image_type'] = $_POST['config_image_type'];
				$config['gallery_thumb_w'] = max(480, (int)$_POST['config_thumb_w']);
				$config['gallery_thumb_h'] = max(360, (int)$_POST['config_thumb_h']);
				$config['gallery_image_w'] = max(600, (int)$_POST['config_image_w']);
				$config['gallery_cols'] = (int)$_POST['config_cols'];
				$config['gallery_rows'] = (int)$_POST['config_rows'];
				$config['gallery_can_write'] = $_POST['config_can_write'];
				$config['gallery_can_write'][] = 1;
				$config['gallery_can_config'] = $_POST['config_can_config'];
				$config['gallery_can_config'][] = 1;
				// บันทึก config.php
				if (gcms::saveconfig(CONFIG, $config)) {
					$ret['error'] = 'SAVE_COMPLETE';
					$ret['location'] = 'reload';
				} else {
					$ret['error'] = 'DO_NOT_SAVE';
				}
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
