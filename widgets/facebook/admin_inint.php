<?php
	// widgets/facebook/admin_inint.php
	if (defined('MAIN_INIT') && $isAdmin) {
		unset($admin_menus['widgets']['facebook']);
		if ($lng['LNG_FACEBOOK_LIKE_BOX'] == '') {
			$admin_menus['tools']['install']['facebook'] = '<a href="index.php?module=install&amp;widgets=facebook"><span>Facebook</span></a>';
		} else {
			$admin_menus['widgets']['facebook'] = '<a href="'.WEB_URL.'/admin/index.php?module=facebook-setup" title="{LNG_FACEBOOK_LIKE_BOX}"><span>{LNG_FACEBOOK_LIKE_BOX}</span></a>';
		}
	}