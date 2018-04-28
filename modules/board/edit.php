<?php
	// modules/board/edit.php
	if (defined('MAIN_INIT')) {
		// ค่า ที่ส่งมา
		$rid = (int)$_REQUEST['rid'];
		$qid = (int)$_REQUEST['qid'];
		if ($rid > 0) {
			// คำตอบ
			$sql = "SELECT R.`id` AS `comment_id`,R.`index_id`,R.`detail`,M.`config`";
			$sql .= ",R.`module_id`,M.`module`,Q.`topic`,U.`id` AS `member_id`,U.`status`";
			$sql .= " FROM `".DB_BOARD_R."` AS R";
			$sql .= " INNER JOIN `".DB_BOARD_Q."` AS Q ON Q.`id`=R.`index_id`";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=Q.`module_id`";
			$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=R.`member_id`";
			$sql .= " WHERE R.`id`='$rid' LIMIT 1";
			$form = 'reply';
		} else {
			// คำถาม
			$sql = "SELECT I.`id` AS `index_id`,I.`topic`,I.`detail`,I.`module_id`,I.`category_id`,I.`comments`,";
			$sql .= "M.`module`,U.`id` AS `member_id`,U.`status`,M.`config`";
			$sql .= " FROM `".DB_BOARD_Q."` AS I";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
			$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=I.`member_id`";
			$sql .= " WHERE I.`id`='$qid' LIMIT 1";
			$form = 'post';
		}
		$index = $db->customQuery($sql);
		if (sizeof($index) == 1) {
			$index = $index[0];
			// config
			gcms::r2config($index['config'], $index);
			// ข้อมูลการ login
			$login = $_SESSION['login'];
			// เจ้าของ
			$canEdit = $isMember && $index['member_id'] == $login['id'];
			// ผู้ดูแล
			$moderator = $isMember && gcms::canConfig(explode(',', $index['moderator']));
			// เจ้าของหรือผู้ดูแล แก้ไขได้
			$canEdit = $canEdit || $moderator;
			// เลือกเมนู
			$menu = $install_modules[$index['module']]['alias'];
			$menu = $menu == '' ? $index['module'] : $menu;
		} else {
			$index = false;
		}
		// แก้ไขคำถาม อ่านหมวด
		$categories = array();
		if ($index && $canEdit) {
			// breadcrumbs
			$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
			$breadcrumbs = array();
			// หน้าหลัก
			$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
			// breadcrumb ของ โมดูล
			$m = $install_modules[$index['module']]['menu_text'];
			$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL($index['module']), $install_modules[$index['module']]['menu_tooltip'], ($m == '' ? $index['module'] : $m), $breadcrumb);
			if ($rid == 0) {
				$categories[0] = '<option value=0>{LNG_NO_CATEGORY}</option>';
				$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]' ORDER BY `category_id`";
				foreach ($db->customQuery($sql) AS $item) {
					if ($moderator || $index['category_id'] == $item['category_id']) {
						$sel = $index['category_id'] == $item['category_id'] ? ' selected' : '';
						$categories[$item['category_id']] = "<option value=$item[category_id]$sel>".gcms::ser2Str($item['topic'])."</option>";
					}
				}
				if (sizeof($categories) > 1) {
					unset($categories[0]);
				}
			}
			// antispam
			$register_antispamchar = gcms::rndname(32);
			$_SESSION[$register_antispamchar] = gcms::rndname(4);
			$patt = array('/{BREADCRUMS}/', '/<UPLOAD>(.*)<\/UPLOAD>/s', '/{CATEGORIES}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{ANTISPAM}/',
				'/{ANTISPAMVAL}/', '/{QID}/', '/{RID}/', '/{TOPIC(-([0-9]+))?}/e', '/{DETAIL}/', '/{SIZE}/', '/{TYPE}/', '/{MODULE}/', '/{MODULEID}/');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = $index['img_upload_type'] == '' ? '' : '$1';
			$replace[] = implode("\n", $categories);
			$replace[] = 'gcms::getLng';
			$replace[] = $register_antispamchar;
			$replace[] = $isAdmin ? $_SESSION[$register_antispamchar] : '';
			$replace[] = (int)$index['index_id'];
			$replace[] = (int)$index['comment_id'];
			$replace[] = create_function('$matches', 'return gcms::cutstring("'.$index['topic'].'",(int)$matches[2]);');
			$replace[] = gcms::txtQuote($index['detail']);
			$replace[] = $index['img_upload_size'];
			$replace[] = $index['img_upload_type'];
			$replace[] = $index['module'];
			$replace[] = $index['module_id'];
			$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'board', "edit$form"));
			// title,keywords,description
			$title = "$lng[LNG_EDIT] $index[topic]";
			$keywords = $title;
			$description = $title;
		} else {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		}
	}
