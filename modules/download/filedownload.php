<?php
	// modules/download/filedownload.php
	session_cache_limiter('none');
	session_start();
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		$status = gcms::isMember() ? $_SESSION['login']['status'] : -1;
		if (in_array($status, $config['download_can_download'])) {
			$file = $_SESSION[$_GET['id']];
			$file_path = iconv('UTF-8', 'TIS-620', ROOT_PATH.$file['file']);
			if (is_file($file_path)) {
				// ดาวน์โหลดไฟล์
				header('Cache-Control: private');
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=".iconv('UTF-8', 'TIS-620', $file['name']));
				header('Content-Transfer-Encoding: binary');
				header('Accept-Ranges: bytes');
				set_time_limit(0);
				readfile($file_path);
			} else {
				header("HTTP/1.0 404 Not Found");
			}
		} else {
			header("HTTP/1.0 404 Not Found");
		}
	} else {
		header("HTTP/1.0 404 Not Found");
	}
