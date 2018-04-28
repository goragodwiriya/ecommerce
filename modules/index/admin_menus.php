<?php
	// modules/index/admin_menus.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// เมนูที่เลือก
		$menu_patents = array_keys($lng['MENU_PARENTS']);
		$menu = strtoupper($_GET['menu']);
		$menu = !in_array($menu, $menu_patents) ? 'MAINMENU' : $menu;
		// title
		$title = $lng['LNG_MENU_PAGE_TITLE'];
		$a = array();
		$a[] = '<span class=icon-modules>{LNG_MENUS}&nbsp;&amp;&nbsp;{LNG_WEB_PAGES}</span>';
		$a[] = '{LNG_MENUS}';
		// แสดงผล
		$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
		$content[] = '<section>';
		$content[] = '<header><h1 class=icon-menus>'.$title.'</h1></header>';
		$content[] = '<form class=table_nav method=get action=index.php>';
		// รายการต่อหน้า
		$content[] = '<fieldset>';
		$content[] = '<label>{LNG_MENU_SELECT} <select name=menu>';
		foreach ($lng['MENU_PARENTS'] AS $key => $value) {
			if ($key != '') {
				$sel = $key == $menu ? ' selected' : '';
				$content[] = '<option value='.$key.$sel.'>'.$value.'</option>';
			}
		}
		$content[] = '</select></label>';
		$content[] = '<input type=submit class="button go" value="{LNG_GO}">';
		$content[] = '<input name=module type=hidden value=index-menus>';
		$content[] = '</fieldset>';
		// add
		$content[] = '<fieldset>';
		$content[] = '<a class="button add" href="{URLQUERY?module=index-menu&src=index-menus&id=0}"><span class=icon-add>{LNG_ADD_NEW} {LNG_MENU}</span></a>';
		$content[] = '</fieldset>';
		$content[] = '</form>';
		// query เมนูที่เลือก เรียงตามลำดับ menu_order
		$sql = "SELECT U.*,M.`module`,I.`language` AS `ilanguage`";
		$sql .= " FROM `".DB_MENUS."` AS U";
		$sql .= " LEFT JOIN `".DB_INDEX."` AS I ON I.`id`=U.`index_id`";
		$sql .= " LEFT JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
		$sql .= " WHERE U.`parent`='$menu' ORDER BY U.`menu_order` ASC";
		$allmenus = $db->customQuery($sql);
		$patt2 = array('/{COUNT}/', '/{MENU}/');
		$replace2 = array(sizeof($allmenus), $lng['MENU_PARENTS'][$menu]);
		// ตารางข้อมูล
		$content[] = '<table id=menus class="tbl_list fullwidth">';
		$content[] = '<caption>'.preg_replace($patt2, $replace2, $lng['ALL_MENUS']).'</caption>';
		$content[] = '<thead>';
		$content[] = '<tr>';
		$content[] = '<th id=c0 scope=col>{LNG_MENU_TEXT}</th>';
		$content[] = '<th id=c1 scope=col class=tablet>{LNG_ALIAS}</th>';
		$content[] = '<th id=c2 scope=col class=center><span class=mobile>{LNG_PUBLISHED}</span></th>';
		$content[] = '<th id=c3 scope=col colspan=3>&nbsp;</th>';
		$content[] = '<th id=c4 scope=col class=mobile>{LNG_LANGUAGE}</th>';
		$content[] = '<th id=c5 scope=col class=mobile>{LNG_TOOLTIP}</th>';
		$content[] = '<th id=c6 scope=col class="center mobile">{LNG_ACCESSKEY}</th>';
		$content[] = '<th id=c7 scope=col class=mobile>{LNG_MODULE_URL}/{LNG_MODULE}</th>';
		$content[] = '<th id=c8 scope=col colspan=2>&nbsp;</th>';
		$content[] = '</tr>';
		$content[] = '</thead>';
		$content[] = '<tbody>';
		$toplvl = -1;
		foreach ($allmenus AS $item) {
			$id = $item['id'];
			$content[] = '<tr id=M_'.$id.' class=sort>';
			$url = $item['menu_url'] == '' ? WEB_URL.'/index.php?module='.$item['module'] : $item['menu_url'];
			$text = '';
			for ($i = 0; $i < $item['level']; $i++) {
				$text .= '&nbsp;&nbsp;&nbsp;';
			}
			$text = ($text == '' ? '' : $text.'↳&nbsp;').$item['menu_text'];
			$content[] = '<th headers=c0 id=r'.$id.' scope=row class=topic>'.$text.'</th>';
			$content[] = '<td headers="r'.$id.' c1" class=tablet>'.$item['alias'].'</td>';
			$content[] = '<td headers="r'.$id.' c2" class=center>'.$lng['LNG_MENU_PUBLISHEDS'][$item['published']].'</td>';
			$content[] = '<td headers="r'.$id.' c3"><a id=move_left_'.$id.' title="{LNG_MOVE_MENU_UP}" class='.($item['level'] == 0 ? 'hidden' : 'icon-move_left').'></a></td>';
			$content[] = '<td headers="r'.$id.' c3"><a id=move_right_'.$id.' title="{LNG_MOVE_MENU_DOWN}" class='.($item['level'] > $toplvl ? 'hidden' : 'icon-move_right').'></a></td>';
			$content[] = '<td headers="r'.$id.' c3"><a id=move_'.$id.' title="{LNG_DRAG_MOVE}" class=icon-move></a></td>';
			$content[] = '<td headers="r'.$id.' c4" class="menu mobile">'.($item['language'] == '' ? '&nbsp;' : '<img src='.WEB_URL.'/datas/language/'.$item['language'].'.gif alt="'.$item['language'].'">').'</td>';
			$content[] = '<td headers="r'.$id.' c5" class=mobile>'.$item['menu_tooltip'].'</td>';
			$content[] = '<td headers="r'.$id.' c6" class="center mobile">'.($item['accesskey'] == '' ? '&nbsp;' : $item['accesskey']).'</td>';
			if ($item['index_id'] == 0) {
				$content[] = '<td headers="r'.$id.' c7" class=mobile>'.$item['menu_url'].'</td>';
			} else {
				$img = $item['ilanguage'] == '' ? '' : '&nbsp;<img src='.DATA_URL.'language/'.$item['ilanguage'].'.gif alt="'.$item['ilanguage'].'">';
				$content[] = '<td headers="r'.$id.' c7" class=mobile>'.$item['module'].$img.'</td>';
			}
			$content[] = '<td headers="r'.$id.' c8" class=menu><a class=icon-edit href="{URLQUERY?src=index-menus&module=index-menu&id='.$item['id'].'&menu='.$menu.'}" title="{LNG_EDIT}"></a></td>';
			$content[] = '<td headers="r'.$id.' c8" class=menu><a class=icon-delete href="{URLQUERY}" title="{LNG_DELETE}" id=delete_menu_'.$item['id'].'></a></td>';
			$content[] = '</tr>';
			$toplvl = $item['level'];
		}
		$content[] = '</tbody>';
		$content[] = '</table>';
		$content[] = '</section>';
		$content[] = '<script>';
		$content[] = '$G(window).Ready(function(){';
		$content[] = "inintTR('menus', /M_[0-9]+/);";
		$content[] = "inintIndexPages('menus', true);";
		$content[] = '});';
		$content[] = '</script>';
		// หน้านี้
		$url_query['module'] = 'index-menus';
		$url_query['menu'] = $menu;
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
