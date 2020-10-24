<?php
	// modules/index/admin_add.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// title
		$title = "$lng[LNG_ADD_NEW] $lng[LNG_MODULE]";
		$a = array();
		$a[] = '<span class=icon-modules>{LNG_MENUS}&nbsp;&amp;&nbsp;{LNG_WEB_PAGES}</span>';
		$a[] = '<a href="{URLQUERY?module=index-insmod}">{LNG_INSTALLED_MODULE}</a>';
		$a[] = '{LNG_ADD_NEW} {LNG_MODULE}';
		// แสดงผล
		$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
		$content[] = '<section>';
		$content[] = '<header><h1 class=icon-new>'.$title.'</h1></header>';
		$content[] = '<form id=setup_frm class=setup_frm method=get action=index.php>';
		$content[] = '<fieldset>';
		$content[] = '<legend><span>{LNG_INSTALL_MODULE_COMMENT}</span></legend>';
		// owner
		$content[] = '<div class=item>';
		$content[] = '<div>';
		$content[] = '<label for=write_owner>{LNG_INSTALL_MODULE}</label>';
		$content[] = '<span class="g-input icon-modules"><select name=owner id=owner title="{LNG_PLEASE_SELECT}" autofocus>';
		foreach ($install_owners AS $owner => $item) {
			if ($config[$owner]['description'] != '') {
				$sel = $document['owner'] == $owner ? ' selected' : '';
				$content[] = '<option value='.$owner.$sel.'>'.$config[$owner]['description'].' ['.$owner.']</option>';
			}
		}
		$content[] = '</select></span>';
		$content[] = '</div>';
		$content[] = '</div>';
		$content[] = '</fieldset>';
		// submit
		$content[] = '<fieldset class=submit>';
		$content[] = '<input type=submit class="button large save" value="{LNG_CREATE}">';
		$content[] = '<input type=hidden name=module value=index-write>';
		foreach ($url_query AS $key => $value) {
			if ($key == 'module') {
				$content[] = '<input type=hidden name=src value="'.$value.'">';
			} elseif ($key == 'page') {
				$content[] = '<input type=hidden name=spage value="'.$value.'">';
			} else {
				$content[] = '<input type=hidden name='.$key.' value="'.$value.'">';
			}
		}
		$content[] = '</fieldset>';
		$content[] = '</form>';
		$content[] = '</section>';
		// หน้านี้
		$url_query['module'] = 'index-add';
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
