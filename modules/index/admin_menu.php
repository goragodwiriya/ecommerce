<?php
	// modules/index/admin_menu.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// 0 = ใหม่, > 0 แก้ไข
		$id = (int)$_GET['id'];
		if ($id > 0) {
			$menu = $db->getRec(DB_MENUS, $id);
		} else {
			$menu = array();
			$menu['parent'] = strtoupper($db->sql_trim_str($_GET['menu']));
			$menu['published'] = 1;
		}
		if ($id > 0 && !$menu) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			$menu['parent'] = !in_array($menu['parent'], array_keys($lng['MENU_PARENTS'])) ? 'MAINMENU' : $menu['parent'];
			// title
			$title = ($id > 0 ? '{LNG_EDIT} ' : '{LNG_CREATE} ').$lng['LNG_MENU'];
			$a = array();
			$a[] = '<span class=icon-modules>{LNG_MENUS}&nbsp;&amp;&nbsp;{LNG_WEB_PAGES}</span>';
			$a[] = '<a href="{URLQUERY?module=index-menus}">{LNG_MENUS}</a>';
			$a[] = $id > 0 ? '{LNG_EDIT}' : '{LNG_ADD_NEW}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-write>'.$title.'</h1></header>';
			$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php autocomplete=off>';
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_MENU_SEC_A}</span></legend>';
			// language
			$content[] = '<div class=item>';
			$content[] = '<label for=write_language>{LNG_LANGUAGE}</label>';
			$content[] = '<div class="table collapse">';
			$content[] = '<div class=td>';
			$content[] = '<span class="g-input icon-language"><select name=write_language id=write_language title="'.strip_tags($lng['LNG_ALL_LANGUAGES_COMMENT']).'" autofocus>';
			$content[] = '<option value="">{LNG_ALL} {LNG_LANGUAGE}</option>';
			foreach ($config['languages'] AS $language) {
				$sel = $language == $menu['language'] ? ' selected' : '';
				$content[] = '<option value='.$language.$sel.'>'.$language.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '</div>';
			$content[] = '<div class=td>&nbsp;<a id=copy_menu title="{LNG_COPY}" class="button copy"><span class=icon-copy></span></a></div>';
			$content[] = '</div>';
			$content[] = '<div class=comment id=result_write_language>{LNG_ALL_LANGUAGES_COMMENT}</div>';
			$content[] = '</div>';
			// menu_text
			$content[] = '<div class=item>';
			$content[] = '<label for=write_menu_text>{LNG_MENU_TEXT}</label>';
			$content[] = '<span class="g-input icon-edit"><input type=text name=write_menu_text id=write_menu_text value="'.$menu['menu_text'].'" maxlength=100 title="{LNG_MENU_COMMENT}" autofocus></span>';
			$content[] = '<div class=comment id=result_write_menu_text>{LNG_MENU_COMMENT}</div>';
			$content[] = '</div>';
			// menu_tooltip
			$content[] = '<div class=item>';
			$content[] = '<label for=write_menu_tooltip>{LNG_TOOLTIP}</label>';
			$content[] = '<span class="g-input icon-edit"><input type=text name=write_menu_tooltip id=write_menu_tooltip value="'.$menu['menu_tooltip'].'" maxlength=100 title="{LNG_TOOLTIP_COMMENT}"></span>';
			$content[] = '<div class=comment id=result_write_menu_tooltip>{LNG_TOOLTIP_COMMENT}</div>';
			$content[] = '</div>';
			// accesskey
			$content[] = '<div class=item>';
			$content[] = '<label for=write_accesskey>{LNG_ACCESSKEY}</label>';
			$content[] = '<span class="g-input icon-keyboard"><input type=text name=write_accesskey id=write_accesskey value="'.$menu['accesskey'].'" maxlength=1 title="{LNG_MENU_ACCESSKEY_COMMENT}"></span>';
			$content[] = '<div class=comment id=result_write_accesskey>{LNG_MENU_ACCESSKEY_COMMENT}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_MENU_SEC_B}</span></legend>';
			// alias
			$content[] = '<div class=item>';
			$content[] = '<label for=write_alias>{LNG_ALIAS}</label>';
			$content[] = '<span class="g-input icon-edit"><input type=text name=write_alias id=write_alias value="'.$menu['alias'].'" maxlength=20 title="{LNG_ALIAS_MENU_COMMENT}"></span>';
			$content[] = '<div class=comment id=result_write_alias>{LNG_ALIAS_MENU_COMMENT}</div>';
			$content[] = '</div>';
			// parent
			$content[] = '<div class=item>';
			$content[] = '<label for=write_parent>{LNG_MENU_POSITION}</label>';
			$content[] = '<span class="g-input icon-config"><select name=write_parent id=write_parent title="{LNG_MENU_POSITION_COMMENT}">';
			foreach ($lng['MENU_PARENTS'] AS $key => $value) {
				$sel = $key == $menu['parent'] ? ' selected' : '';
				$content[] = '<option value='.$key.$sel.'>'.$value.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment id=result_write_parent>{LNG_MENU_POSITION_COMMENT}</div>';
			$content[] = '</div>';
			// level
			$content[] = '<div class=item>';
			$content[] = '<label for=write_type>{LNG_MENU_TYPE}</label>';
			$content[] = '<span class="g-input icon-config"><select name=write_type id=write_type title="{LNG_MENU_TYPE_COMMENT}">';
			if ($menu['menu_order'] == 1) {
				$menu_type = 0;
			} elseif ($menu['level'] == 0) {
				$menu_type = 1;
			} elseif ($menu['level'] == 1) {
				$menu_type = 2;
			} else {
				$menu_type = 3;
			}
			foreach ($lng['LNG_MENU_TYPES'] AS $key => $value) {
				$sel = $menu_type == $key ? ' selected' : '';
				$content[] = '<option value='.$key.$sel.'>'.$value.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment id=result_write_type>{LNG_MENU_TYPE_COMMENT}</div>';
			$content[] = '</div>';
			// menu_order
			$content[] = '<div class=item>';
			$content[] = '<label for=write_order>{LNG_MENU_ORDER}</label>';
			$content[] = '<span class="g-input icon-config"><select name=write_order id=write_order title="{LNG_MENU_ORDER_COMMENT}" size=8></select></span>';
			$content[] = '<div class=comment id=result_write_order>{LNG_MENU_ORDER_COMMENT}</div>';
			$content[] = '</div>';
			// published
			$content[] = '<div class=item>';
			$content[] = '<label for=write_published>{LNG_PUBLISHED}</label>';
			$content[] = '<span class="g-input icon-published1"><select id=write_published name=write_published title="{LNG_PUBLISHED_SETTING}">';
			foreach ($lng['LNG_MENU_PUBLISHEDS'] AS $i => $item) {
				$sel = $menu['published'] == $i ? ' selected' : '';
				$content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment>{LNG_PUBLISHED_SETTING}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			$content[] = '<fieldset id=menu_action>';
			$content[] = '<legend><span>{LNG_MENU_SEC_C}</span></legend>';
			$content[] = '<div class=item>';
			$content[] = '<label for=write_action>{LNG_WHEN_MENU_CLICK}</label>';
			$content[] = '<span class="g-input icon-config"><select name=write_action id=write_action title="{LNG_WHEN_MENU_CLICK_COMMENT}">';
			if ($menu['menu_url'] != '') {
				$m = 2;
			} elseif ($menu['index_id'] == 0) {
				$m = 0;
			} else {
				$m = 1;
			}
			foreach ($lng['LNG_MENU_ACTIONS'] AS $i => $item) {
				$sel = $i == $m ? ' selected' : '';
				$content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment id=result_write_action>{LNG_WHEN_MENU_CLICK_COMMENT}</div>';
			$content[] = '</div>';
			// index_id
			$content[] = '<div class="item action 1">';
			$content[] = '<label for=write_index_id>{LNG_INSTALLED_MODULE}</label>';
			$content[] = '<span class="g-input icon-modules"><select name=write_index_id id=write_index_id title="{LNG_WEB_PAGES_COMMENT}">';
			// query หน้าเว็บทั้งหมด และ โมดูลที่ติดตั้ง
			$sql = "SELECT I.`id`,M.`owner`,M.`module`,D.`topic`,I.`language`";
			$sql .= " FROM `".DB_INDEX."` AS I";
			$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language`=I.`language`";
			$sql .= " LEFT JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
			$sql .= " WHERE I.`index`='1' ORDER BY M.`owner`,I.`module_id`,I.`language`";
			foreach ($db->customQuery($sql) AS $item) {
				$module_menus[$item['owner']][$item['id']] = array($item['module'].($item['language'] == '' ? '' : " [$item[language]]").', '.$item['topic'], '');
			}
			$optgroup = '';
			foreach ($module_menus AS $owner => $values) {
				$content[] = '<optgroup label='.$owner.'>';
				foreach ($values AS $m => $v) {
					$sel = $m == $menu['index_id'] ? ' selected' : '';
					$content[] = '<option value='.$owner.'_'.$m.$sel.'>'.$v[0].'</option>';
				}
				$content[] = '</optgroup>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment id=result_write_index_id>{LNG_WEB_PAGES_COMMENT}</div>';
			$content[] = '</div>';
			// menu_url
			$content[] = '<div class="item action 2">';
			$content[] = '<label for=write_menu_url>{LNG_URL}</label>';
			$content[] = '<span class="g-input icon-world"><input type=text name=write_menu_url id=write_menu_url value="'.$menu['menu_url'].'" maxlength=255 title="{LNG_MENU_URL_COMMENT}"></span>';
			$content[] = '<div class=comment id=result_write_menu_url>{LNG_MENU_URL_COMMENT}</div>';
			$content[] = '</div>';
			// menu_target
			$content[] = '<div class="item action 1 2">';
			$content[] = '<label for=write_target>{LNG_MENU_TARGET}</label>';
			$content[] = '<span class="g-input icon-forward"><select name=write_target id=write_target title="{LNG_MENU_TARGET_COMMENT}">';
			foreach ($lng['MENU_TARGET'] AS $key => $value) {
				$sel = $key == $menu['menu_target'] ? ' selected' : '';
				$content[] = '<option value="'.$key.'"'.$sel.'>'.$value.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment id=result_write_target>{LNG_MENU_TARGET_COMMENT}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset class=submit>';
			$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
			$content[] = '<input type=hidden id=write_id name=write_id value='.(int)$menu['id'].'>';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = "inintMenu();";
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'index-menu';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
