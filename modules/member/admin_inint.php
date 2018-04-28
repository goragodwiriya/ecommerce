<?php
	// modules/member/admin_inint.php
	$module_menus['member']['login'] = array('{LNG_LOGIN}', '?module=dologin', 'dologin');
	$module_menus['member']['logout'] = array('{LNG_LOGOUT}', '?action=logout', 'logout');
	$module_menus['member']['register'] = array('{LNG_REGISTER}', '?module=register', 'register');
	$module_menus['member']['forgot'] = array('{LNG_FORGOT}', '?module=forgot', 'forgot');
	$module_menus['member']['editprofile'] = array('{LNG_MEMBER_EDIT_TITLE}', '?module=editprofile', 'editprofile');
	$module_menus['member']['admin'] = array('{LNG_ADMIN_TITLE}', WEB_URL.'/admin/index.php', 'admin');
