<?php
	// modules/member/activate.php
	// inint
	include '../../bin/inint.php';
	// Activate สมาชิก
	if ($_GET['id'] != '') {
		$user = $db->basicSearch(DB_USER, 'activatecode', $_GET['id']);
		if (!$user) {
			$title = $lng['ACTIVATE_TITLE'];
			$detail = $lng['ACTIVATE_NOT_FOUND'];
		} else {
			$newuser['activatecode'] = '';
			$db->edit(DB_USER, $user['id'], $newuser);
			$patt = array('/%DISPLAYNAME%/', '/%WEBTITLE%/', '/%WEBURL%/');
			$replace[] = $user['displayname'] == '' ? $user['email'] : $user['displayname'];
			$replace[] = strip_tags($config['web_title']);
			$replace[] = WEB_URL;
			$title = preg_replace($patt, $replace, $lng['ACTIVATE_NEWREGISTER_HEADER']);
			$detail = preg_replace($patt, $replace, $lng['ACTIVATE_NEWREGISTER_DETAIL']);
		}
	} else {
		$title = $lng['ACTIVATE_TITLE'];
		$detail = $lng['ACTIVATE_ERROR'];
	}
	// แสดงผล
	$main_patt = array();
	$main_replace = array();
	$main_patt[] = '/{(LNG_[A-Z0-9_]+)}/e';
	$main_replace[] = 'gcms::getLng';
	$main_patt[] = '/{VERSION}/';
	$main_replace[] = VERSION;
	$main_patt[] = '/{WEBTITLE}/';
	$main_replace[] = strip_tags($config['web_title']);
	$main_patt[] = '/{WEBDESCRIPTION}/';
	$main_replace[] = strip_tags($config['web_description']);
	$main_patt[] = '/{WEBURL}/';
	$main_replace[] = WEB_URL;
	$main_patt[] = '/{TITLE}/';
	$main_replace[] = $title;
	$main_patt[] = '/{CONTENT}/';
	$main_replace[] = $detail;
	echo gcms::pregReplace($main_patt, $main_replace, file_get_contents(ROOT_PATH.SKIN.'member/activate.html'));
