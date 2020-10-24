<?php
	// widgets/rss/admin_inint.php
	if (defined('MAIN_INIT') && $isAdmin) {
		// เมนู setup
		$admin_menus['widgets']['rss'] = '<a href="index.php?module=rss-setup"><span>{LNG_RSS_TAB}</span></a>';
	}
