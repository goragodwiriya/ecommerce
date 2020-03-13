<?php
	// modules/download/admin_download.php
	session_cache_limiter('none');
	session_start();
	// inint
	include '../../bin/inint.php';
	// referer, can download
	if (gcms::isReferer() && gcms::canConfig($config['download_can_upload'])) {
		$file = $db->getRec(DB_DOWNLOAD, $_GET['id']);
		$file_path = iconv('UTF-8', 'TIS-620', ROOT_PATH.$file['file']);
		if ($file && is_file($file_path)) {
			// อัปเดตดาวน์โหลด
			$db->edit(DB_DOWNLOAD, $file['id'], array('downloads' => $file['downloads'] + 1));
			// ดาวน์โหลดไฟล์
			header('Cache-Control: private');
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment; filename=".iconv('UTF-8', 'TIS-620', "$file[name].$file[ext]"));
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
