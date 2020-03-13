<?php
	// modules/download/download.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		// ค่าที่ส่งมา
		$action = $_POST['action'];
		if ($action == 'download' || $action == 'downloading') {
			// ค่าที่ส่งมา
			list($module, $id) = explode('-', $_POST['id']);
			// guest = -1
			$status = isset($_SESSION['login']['status']) ? $_SESSION['login']['status'] : -1;
			// ไฟล์ดาวน์โหลด
			$download = $db->getRec(DB_DOWNLOAD, $id);
			$file_path = iconv('UTF-8', 'TIS-620', ROOT_PATH.$download['file']);
			// ตรวจสอบสถานะการดาวน์โหลด
			if (!$download || !is_file($file_path)) {
				$ret['error'] = 'DOWNLOAD_FILE_NOT_FOUND';
			} elseif (!is_array($config['download_can_download'])) {
				$ret['error'] = 'DO_NOT_DOWNLOAD';
			} elseif (!in_array($status, $config['download_can_download'])) {
				$ret['error'] = 'NOT_LOGIN';
			} elseif ($action == 'download') {
				$ret['confirm'] = 'CONFIRM_DOWNLOAD';
			} elseif ($action == 'downloading') {
				// อัปเดตดาวน์โหลด
				$download['downloads']++;
				$db->edit(DB_DOWNLOAD, $download['id'], array('downloads' => $download['downloads']));
				// URL สำหรับดาวน์โหลด
				$fid = gcms::rndname(32);
				$_SESSION[$fid]['file'] = $download['file'];
				$_SESSION[$fid]['size'] = $download['size'];
				$_SESSION[$fid]['name'] = "$download[name].$download[ext]";
				// คืนค่า URL สำหรับดาวน์โหลด
				$ret['href'] = rawurlencode(WEB_URL."/modules/download/filedownload.php?id=$fid");
				$ret['downloads'] = $download['downloads'];
				$ret['id'] = $download['id'];
			}
			$ret['action'] = $action;
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
