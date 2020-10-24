<?php
	// modules/member/forgot.php
	if (defined('MAIN_INIT')) {
		// title
		$title = $lng['LNG_FORGOT_TITLE'];
		// breadcrumbs
		$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
		$breadcrumbs = array();
		// หน้าหลัก
		$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
		// url ของหน้านี้
		$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL('forgot'), $lng['LNG_FORGOT_TITLE'], $lng['LNG_FORGOT_TITLE'], $breadcrumb);
		if ($config['custom_forgot'] != '' && is_file(ROOT_PATH.$config['custom_forgot'])) {
			// custom register form
			include (ROOT_PATH.$config['custom_forgot']);
		} else {
			// แสดงฟอร์ม member/forgotfrm.html
			$patt = array('/{BREADCRUMS}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{WEBURL}/', '/{MODAL}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = 'gcms::getLng';
			$replace[] = WEB_URL;
			$replace[] = $_POST['action'] == 'modal' ? 'true' : 'false';
			$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate('member', 'member', 'forgotfrm'));
		}
		// เลือกเมนู
		$menu = 'forgot';
	}
