<?php
	// widgets/map/admin_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer และ admin
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
			$config['map_width'] = (int)$_POST['map_width'];
			$config['map_height'] = (int)$_POST['map_height'];
			$config['map_info'] = $db->sql_trim($_POST['map_info']);
			$config['map_zoom'] = (int)$_POST['map_zoom'];
			$config['map_latigude'] = trim($_POST['map_latigude']);
			$config['map_lantigude'] = trim($_POST['map_lantigude']);
			$config['map_info_latigude'] = trim($_POST['info_latigude']);
			$config['map_info_lantigude'] = trim($_POST['info_lantigude']);
			if ($config['map_width'] < 100) {
				$ret['alert'] = $lng['LNG_MAP_WIDTH_TITLE'];
				$ret['input'] = 'map_width';
			} elseif ($config['map_height'] < 100) {
				$ret['alert'] = $lng['LNG_MAP_HEIGHT_TITLE'];
				$ret['input'] = 'map_height';
			} else {
				// บันทึก config.php
				if (gcms::saveconfig(CONFIG, $config)) {
					$ret['error'] = 'SAVE_COMPLETE';
					$ret['eval'] = rawurlencode('window.location.reload()');
				} else {
					$ret['error'] = 'DO_NOT_SAVE';
				}
			}
		}
		// คืนค่า JSON
		echo gcms::array2json($ret);
	}
