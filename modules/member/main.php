<?php
	// modules/member/main.php
	if (defined('MAIN_INIT') && $isMember) {
		// query ข้อมูลสมาชิก
		$sql = "SELECT * FROM `".DB_USER."` WHERE `id`=".(int)$_REQUEST['id']." LIMIT 1";
		$result = $cache->get($sql);
		if (!$result) {
			$result = $db->customQuery($sql);
			if (sizeof($result) == 1) {
				$result = $result[0];
				$cache->save($sql, $result);
			} else {
				$result = false;
			}
		}
		if ($result) {
			// breadcrumbs
			$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
			$breadcrumbs = array();
			// หน้าหลัก
			$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
			// แสดงผล
			$patt = array('/{BREADCRUMS}/', '/{WEBURL}/', '/{WEBTITLE}/', '/{SKIN}/', '/{ID}/', '/{COLOR}/',
				'/{DISPLAYNAME}/', '/{EMAIL}/', '/{SEX}/', '/{CREATE}/', '/{WEBSITE}/', '/{VISITED}/',
				'/{SOCIAL}/', '/{LASTVISITED}/', '/{POST}/', '/{REPLY}/', '/{STATUS}/', '/{POINT}/', '/{(LNG_[A-Z0-9_]+)}/e');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = WEB_URL;
			$replace[] = $config['web_title'];
			$replace[] = SKIN;
			$replace[] = $result['id'];
			$replace[] = $result['status'];
			$u = array();
			gcms::checkempty($result['pname'], $u);
			gcms::checkempty($result['fname'], $u);
			gcms::checkempty($result['lname'], $u);
			if (sizeof($u) > 0) {
				if ($result['displayname'] != '') {
					$u[] = "($result[displayname])";
				}
			} elseif ($result['displayname'] != '') {
				$u[] = $result['displayname'];
			} else {
				$u[] = $result['email'];
			}
			$replace[] = implode(' ', $u);
			if (is_file(ROOT_PATH.'modules/pm/send.php')) {
				$replace[] = '<a class=icon-email-sent title="{LNG_PM_SEND_TITLE}" href="index.php?module=pm-send&amp;to='.$result['id'].'">&nbsp;</a>';
			} else {
				$replace[] = '<a class=icon-email-sent title="{LNG_MAIL_TO}" href="index.php?module=sendmail&amp;to='.$result['id'].'">&nbsp;</a>';
			}
			$replace[] = in_array($result['sex'], array_keys($lng['SEX'])) ? $result['sex'] : 'u';
			$replace[] = gcms::mktime2date($result['create_date'], 'd M Y');
			$replace[] = ($result['website'] == '' ? '-' : "<a href=\"http://$result[website]\" target=_blank>$result[website]</a>");
			$replace[] = $result['visited'];
			$replace[] = $result['fb'] == 1 ? 'icon-facebook' : '';
			$replace[] = gcms::mktime2date($result['lastvisited'], 'd M Y');
			$replace[] = $result['post'];
			$replace[] = $result['reply'];
			$replace[] = $config['member_status'][(int)$result['status']];
			$replace[] = $result['point'];
			$replace[] = 'gcms::getLng';
			$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate('member', 'member', 'view'));
		} else {
			$title = $lng['LNG_ID_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content = '<div class=error>'.$title.'</div>';
	}
