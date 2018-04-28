<?php
	// ลบไฟล์
	session_start();
	// config
	include ("../../../../bin/load.php");
	if (gcms::isReferer() && gcms::isAdmin()) {
		if (isset($_POST['did'])) {
			gcms::rm_dir(ROOT_PATH.$_POST['did']);
		} elseif (isset($_POST['fid'])) {
			@unlink(ROOT_PATH.$_POST['fid']);
		}
	}
