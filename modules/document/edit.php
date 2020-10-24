<?php
	// modules/document/edit.php
	if (defined('MAIN_INIT')) {
		// ค่า ที่ส่งมา
		$rid = (int)$_REQUEST['id'];
		// ตรวจสอบคำตอบที่ต้องการแก้ไข
		$sql = "SELECT R.`id`,R.`index_id`,R.`detail`,R.`module_id`,R.`member_id`,M.`module`,D.`topic`,M.`config`";
		$sql .= " FROM `".DB_COMMENT."` AS R";
		$sql .= " INNER JOIN `".DB_INDEX."` AS Q ON Q.`id`=R.`index_id` AND Q.`index`='0'";
		$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=Q.`id` AND D.`module_id`=Q.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
		$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=R.`module_id`";
		$sql .= " WHERE R.`id`='$rid' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 1) {
			$index = $index[0];
			// config
			gcms::r2config($index['config'], $index);
			// ข้อมูลการ login
			$login = $_SESSION['login'];
			// moderator (ผู้ดูแล)
			$canEdit = in_array($login['status'], explode(',', $index['moderator']));
			$canEdit = $isMember && (($index['member_id'] == $login['id']) || $canEdit);
			// เลือกเมนู
			$menu = $install_modules[$index['module']]['alias'];
			$menu = $menu == '' ? $index['module'] : $menu;
			if ($canEdit) {
				// breadcrumbs
				$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
				$breadcrumbs = array();
				// หน้าหลัก
				$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
				// breadcrumb ของ โมดูล
				$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL($index['module']), $install_modules[$index['module']]['menu_tooltip'], $install_modules[$index['module']]['menu_text'], $breadcrumb);
				// antispam
				$register_antispamchar = gcms::rndname(32);
				$_SESSION[$register_antispamchar] = gcms::rndname(4);
				// แสดงผล
				$patt = array('/{BREADCRUMS}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{ANTISPAM}/', '/{ANTISPAMVAL}/', '/{QID}/', '/{RID}/', '/{DETAIL}/', '/{MODULEID}/', '/{TOPIC}/');
				$replace = array();
				$replace[] = implode("\n", $breadcrumbs);
				$replace[] = 'gcms::getLng';
				$replace[] = $register_antispamchar;
				$replace[] = $isAdmin ? $_SESSION[$register_antispamchar] : '';
				$replace[] = $index['index_id'];
				$replace[] = $index['id'];
				$replace[] = htmlspecialchars(preg_replace('/&#39;/', "'", $index['detail']));
				$replace[] = $index['module_id'];
				$replace[] = $index['topic'];
				$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'document', 'editreply'));
				// title,keywords,description
				$title = "$lng[LNG_EDIT] $index[topic]";
				$keywords = $title;
				$description = $title;
			} else {
				$title = $lng['LNG_DATA_NOT_FOUND'];
				$content = '<div class=error>'.$title.'</div>';
			}
		} else {
			$title = $lng['LNG_DOCUMENT_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		}
	}
