<?php
	// modules/member/sendmail.php
	if (defined('MAIN_INIT')) {
		// id ของผู้ที่ต้องการส่งข้อความถึง ส่งได้หลายคน คั่นด้วย ,
		// admin สำหรับส่งเมล์ไปยัง admin
		$to = trim($_REQUEST['to']);
		// ส่งถึง admin แทนที่ด้วย id ของ admin
		$to = str_replace('admin', "(SELECT `id` FROM `".DB_USER."` WHERE `status`='1')", $to);
		// array ของผู้รับ
		$emails = array();
		$ids = array();
		$status = array();
		if ($to != '') {
			// อ่านและตรวจสอบอีเมลของผู้รับ
			$sql = "SELECT `id`,`status`,`email`,`displayname` FROM `".DB_USER."` WHERE `id` IN($to)";
			foreach ($db->customQuery($sql) AS $item) {
				// ไม่สามารถส่งถึงตัวเองได้
				if ($item['email'] != $_SESSION['login']['email']) {
					$emails[] = $item['displayname'] == '' ? $item['email'] : $item['displayname'];
					$ids[] = $item['id'];
					$status[] = $item['status'];
				}
			}
		}
		if (sizeof($ids) == 0) {
			// ไม่มีผู้รับ
			$title = $lng['SEND_MAIL_ERROR'];
			$content = '<div class=error>'.$title.'</div>';
		} else {
			// antispam
			$register_antispamchar = gcms::rndname(32);
			$_SESSION[$register_antispamchar] = gcms::rndname(4);
			// title
			$title = $lng['LNG_SENDMAIL_TITLE'];
			// breadcrumbs
			$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
			$breadcrumbs = array();
			// หน้าหลัก
			$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
			// แสดงผล member/sendmail.html
			$patt = array('/{BREADCRUMS}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{TITLE}/', '/{SENDER}/', '/{RECIEVER}/', '/{RECIEVERID}/', '/{ANTISPAM}/', '/{ANTISPAMVAL}/', '/{FILEBROWSER}/', '/{LANGUAGE}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = 'gcms::getLng';
			$replace[] = $title;
			$replace[] = $_SESSION['login']['email'];
			$replace[] = implode(',', $emails);
			$replace[] = implode(',', $ids);
			$replace[] = $register_antispamchar;
			$replace[] = $isAdmin ? $_SESSION[$register_antispamchar] : '';
			if ($isAdmin) {
				$fck = array();
				if (is_dir(ROOT_PATH.'ckfinder/')) {
					$fck[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html",';
					$fck[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Images",';
					$fck[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images",';
				} else {
					$connector = urlencode(WEB_URL.'/ckeditor/filemanager/connectors/php/connector.php');
					$fck[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Connector='.$connector.'",';
					$fck[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector='.$connector.'",';
					$fck[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php?Type=Image",';
				}
				$replace[] = implode("\n", $fck);
			} else {
				$replace[] = '';
			}
			$replace[] = LANGUAGE;
			$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate('member', 'member', 'sendmail'));
			// เลือกเมนู
			$menu = 'sendmail';
		}
	} else {
		$title = $lng['LNG_NOT_LOGIN'];
		$content = '<div class=error>'.$title.'</div>';
	}
