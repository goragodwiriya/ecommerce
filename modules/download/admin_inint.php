<?php
	// modules/download/admin_inint.php
	if (MAIN_INIT == 'admin' && $isAdmin && ($install_modules['download']['owner'] != 'download' || !defined('DB_DOWNLOAD'))) {
		// เมนูติดตั้ง
		$admin_menus['tools']['install']['download'] = '<a href="index.php?module=install&amp;modules=download"><span>Download</span></a>';
	} else {
		// เมนูแอดมิน
		if (!gcms::canConfig($config['download_can_config'])) {
			unset($admin_menus['modules']['download']['config']);
			unset($admin_menus['modules']['download']['category']);
		}
		if (gcms::canConfig($config['download_can_upload'])) {
			$admin_menus['modules']['download']['setup'] = '<a href="index.php?module=download-setup"><span>{LNG_DOWNLOAD_FILES}</span></a>';
			$admin_menus['modules']['download']['write'] = '<a href="index.php?module=download-write"><span>{LNG_UPLOAD}</span></a>';
		} else {
			unset($admin_menus['modules']['download']['setup']);
		}
	}
