<?php
	// modules/member/editprofile.php
	if (defined('MAIN_INIT') && $isMember) {
		// ข้อมูล user ที่ login
		$user = $db->getRec(DB_USER, $_SESSION['login']['id']);
		if (!$user) {
			$title = $lng['LNG_MEMBER_NOT_FOUND'];
			$content = '<div class=error>'.$title.'</div>';
		} else {
			// breadcrumbs
			$breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
			$breadcrumbs = array();
			// หน้าหลัก
			$breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
			// url ของหน้านี้
			$breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL('forgot'), $lng['LNG_MEMBER_EDIT_TITLE'], $lng['LNG_MEMBER_EDIT_TITLE'], $breadcrumb);
			if ($user['fb'] == 1) {
				unset($member_tabs['password']);
			}
			// ตรวจสอบ tab ที่เลือก
			$title = '';
			$tab = $_REQUEST['tab'];
			$file = $member_tabs[$tab][1];
			if ($file == '' || !is_file(ROOT_PATH."$file.php")) {
				// เรียก tab แรก ถ้าไม่มีการระบุ tab มา
				reset($member_tabs);
				$tab = key($member_tabs);
				$file = $member_tabs[$tab][1];
			}
			if ($file != 'modules/member/editprofile') {
				include (ROOT_PATH."$file.php");
			} else {
				// ข้อมูลสมาชิก
				$patt = array('/{LNG_USERICON_COMMENT}/', '/{EMAIL}/', '/{PNAME}/', '/{FNAME}/', '/{LNAME}/', '/{DISPLAYNAME}/',
					'/{SEX}/', '/{SUBSCRIB}/', '/{ADDRESS1}/', '/{ADDRESS2}/', '/{PROVINCE}/', '/{PROVINCES}/', '/{ZIPCODE}/', '/{COUNTRIES}/', '/{COUNTRY}/',
					'/{PHONE1}/', '/{PHONE2}/', '/{STATUS}/', '/{ID}/', '/{WEBSITE}/', '/{COMPANY}/', '/{BIRTHDAY}/', '/{POINT}/');
				$replace = array();
				$t = implode(', ', $config['user_icon_typies']);
				$h = $config['user_icon_h'] == 0 ? $config['user_icon_w'] : $config['user_icon_h'];
				$replace[] = str_replace(array('%1', '%2', '%3'), array($config['user_icon_w'], $h, $t), $lng['LNG_USERICON_COMMENT']);
				$replace[] = $user['email'];
				$replace[] = $user['pname'];
				$replace[] = $user['fname'];
				$replace[] = $user['lname'];
				$replace[] = $user['displayname'];
				// sex
				$datas = array();
				foreach ($lng['SEX'] AS $sex => $name) {
					$sel = $sex == $user['sex'] ? ' selected' : '';
					$datas[] = '<option value='.$sex.$sel.'>'.$name.'</option>';
				}
				$replace[] = implode('', $datas);
				$replace[] = $user['subscrib'] ? 'checked' : '';
				$replace[] = $user['address1'];
				$replace[] = $user['address2'];
				$replace[] = $user['province'];
				// จังหวัด
				$provinces = array();
				$provinces[] = '<option value="">--- '.$lng['LNG_PLEASE_SELECT'].' ---</option>';
				$sql = "SELECT `id`, `name` FROM `".DB_PROVINCE."`";
				$datas = $cache->get($sql);
				if (!$datas) {
					$datas = $db->customQuery($sql);
					$cache->save($sql, $datas);
				}
				foreach ($datas AS $item) {
					$sel = $user['provinceID'] == $item['id'] ? ' selected' : '';
					$provinces[] = '<option value='.$item['id'].$sel.'>'.$item['name'].'</option>';
				}
				$replace[] = implode('', $provinces);
				$replace[] = $user['zipcode'];
				// ประเทศ
				$user['country'] = $user['country'] == '' ? 'TH' : $user['country'];
				$countries = array();
				$sql = "SELECT `iso`,`printable_name` FROM `".DB_COUNTRY."`";
				foreach ($db->customQuery($sql) AS $item) {
					if ($user['country'] == $item['iso']) {
						$country = $item['printable_name'];
						$sel = ' selected';
					} else {
						$sel = '';
					}
					$countries[] = '<option value='.$item['iso'].$sel.'>'.$item['printable_name'].'</option>';
				}
				$replace[] = implode("\n", $countries);
				$replace[] = $country;
				$replace[] = $user['phone1'];
				$replace[] = $user['phone2'];
				$replace[] = $user['status'];
				$replace[] = $user['id'];
				$replace[] = $user['website'];
				$replace[] = $user['company'];
				$replace[] = $user['birthday'];
				$replace[] = $user['point'];
				$content = preg_replace($patt, $replace, gcms::loadtemplate('member', 'member', "$tab"));
			}
			// tabs
			$tabs = array();
			foreach ($member_tabs AS $key => $values) {
				if ($values[0] != '') {
					if ($key == $tab) {
						$class = "tab select $key";
						$title = $title == '' ? $lng[mb_substr($values[0], 1, -1)] : $title;
					} else {
						$class = "tab $key";
					}
					if (preg_match('/^http:\/\/.*/', $values[1])) {
						$tabs[] = '<li class="'.$class.'"><a href="'.$values[1].'">'.$values[0].'</a></li>';
					} else {
						$tabs[] = '<li class="'.$class.'"><a href="{WEBURL}/index.php?module=editprofile&amp;tab='.$key.'">'.$values[0].'</a></li>';
					}
				}
			}
			$patt = array('/{BREADCRUMS}/', '/{TAB}/', '/{DETAIL}/', '/{(LNG_[A-Z0-9_]+)}/e');
			$replace = array();
			$replace[] = implode("\n", $breadcrumbs);
			$replace[] = implode('', $tabs);
			$replace[] = $content;
			$replace[] = 'gcms::getLng';
			$content = gcms::pregReplace($patt, $replace, gcms::loadtemplate('member', 'member', 'main'));
			// เลือกเมนูตาม tab
			$menu = $tab;
		}
	} else {
		$title = $lng['LNG_LOGIN_NOT_FOUND'];
		$content = '<div class=error>'.$title.'</div>';
	}
