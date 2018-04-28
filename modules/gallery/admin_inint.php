<?php
	// modules/gallery/admin_inint.php
	if (MAIN_INIT == 'admin' && $isAdmin && ($install_modules['gallery']['owner'] != 'gallery' || !defined('DB_GALLERY'))) {
		// เมนูติดตั้ง
		$admin_menus['tools']['install']['gallery'] = '<a href="index.php?module=install&amp;modules=gallery"><span>Gallery</span></a>';
	} else {
		// เมนูแอดมิน
		if (!gcms::canConfig($config['gallery_can_config'])) {
			unset($admin_menus['modules']['gallery']['config']);
		}
		if (gcms::canConfig($config['gallery_can_write'])) {
			$admin_menus['modules']['gallery']['album'] = '<a href="index.php?module=gallery-album"><span>{LNG_GALLERY_ALBUM}</span></a>';
			$admin_menus['modules']['gallery']['write'] = '<a href="index.php?module=gallery-write"><span>{LNG_ADD_NEW} {LNG_GALLERY_ALBUM}</span></a>';
		}
	}
