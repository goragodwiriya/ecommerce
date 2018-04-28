<?php
	// widgets/facebook/admin_setup.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// ตรวจสอบค่า default
		$config['facebook_width'] = empty($config['facebook_width']) ? 295 : $config['facebook_width'];
		$config['facebook_height'] = empty($config['facebook_height']) ? 250 : $config['facebook_height'];
		$config['facebook_user'] = empty($config['facebook_user']) ? 'gcms7s' : $config['facebook_user'];
		// title
		$title = $lng['LNG_FACEBOOK_SETTINGS'];
		$a = array();
		$a[] = '<span class=icon-widgets>{LNG_WIDGETS}</span>';
		$a[] = '{LNG_FACEBOOK_LIKE_BOX}';
		// แสดงผล
		$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
		$content[] = '<section>';
		$content[] = '<header><h1 class=icon-facebook>'.$title.'</h1></header>';
		$content[] = '<div class=setup_frm>';
		$content[] = '<form id=setup_frm class=paper method=post action=index.php>';
		$content[] = '<fieldset>';
		$content[] = '<legend><span>{LNG_FACEBOOK_LIKE_BOX}</span></legend>';
		// width, height
		$content[] = '<div class=item>';
		$content[] = '<div class=input-groups>';
		$content[] = '<div class=width50>';
		$content[] = '<label for=facebook_width>{LNG_WIDTH}</label>';
		$content[] = '<span class="g-input icon-width"><input type=number name=facebook_width id=facebook_width value="'.$config['facebook_width'].'" title="{LNG_FACEBOOK_SIZE_COMMENT}"></span>';
		$content[] = '</div>';
		$content[] = '<div class=width50>';
		$content[] = '<label for=facebook_height>{LNG_HEIGHT}</label>';
		$content[] = '<span class="g-input icon-height"><input type=number name=facebook_height id=facebook_height value="'.$config['facebook_height'].'" title="{LNG_FACEBOOK_SIZE_COMMENT}"></span>';
		$content[] = '</div>';
		$content[] = '</div>';
		$content[] = '<div class=comment>{LNG_FACEBOOK_SIZE_COMMENT}</div>';
		$content[] = '</div>';
		// facebook_user
		$content[] = '<div class=item>';
		$content[] = '<label for=facebook_user>{LNG_USERNAME}</label>';
		$content[] = '<div class="table collapse">';
		$content[] = '<span class="td mobile">http://www.facebook.com/&nbsp;</span>';
		$content[] = '<div class=td><span class="g-input icon-facebook"><input type=text id=facebook_user name=facebook_user value="'.$config['facebook_user'].'" title="{LNG_WIDGETS_FACEBOOK_USER_COMMENT}"></span></div>';
		$content[] = '</div>';
		$content[] = '<div class=comment id=result_facebook_user>{LNG_FACEBOOK_USER_COMMENT}</div>';
		$content[] = '</div>';
		// facebook_faces
		$content[] = '<div class=item>';
		$content[] = '<label for=facebook_faces>{LNG_FACEBOOK_SHOW_FACES}</label>';
		$content[] = '<span class="g-input icon-config"><select id=facebook_faces name=facebook_faces title="{LNG_PLEASE_SELECT}">';
		foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
			$sel = $i == $config['facebook_faces'] ? ' selected' : '';
			$content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
		}
		$content[] = '</select></span>';
		$content[] = '</div>';
		// facebook_stream
		$content[] = '<div class=item>';
		$content[] = '<label for=facebook_stream>{LNG_FACEBOOK_SHOW_STREAM}</label>';
		$content[] = '<span class="g-input icon-config"><select id=facebook_stream name=facebook_stream title="{LNG_PLEASE_SELECT}">';
		foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
			$sel = $i == $config['facebook_stream'] ? ' selected' : '';
			$content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
		}
		$content[] = '</select></span>';
		$content[] = '</div>';
		// facebook_header
		$content[] = '<div class=item>';
		$content[] = '<label for=facebook_header>{LNG_FACEBOOK_SHOW_HEADER}</label>';
		$content[] = '<span class="g-input icon-config"><select id=facebook_header name=facebook_header title="{LNG_PLEASE_SELECT}">';
		foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
			$sel = $i == $config['facebook_header'] ? ' selected' : '';
			$content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
		}
		$content[] = '</select></span>';
		$content[] = '</div>';
		// facebook_border
		$content[] = '<div class=item>';
		$content[] = '<label for=facebook_border>{LNG_FACEBOOK_BORDER}</label>';
		$content[] = '<span class="g-input icon-config"><select id=facebook_border name=facebook_border>';
		foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
			$sel = $i == $config['facebook_border'] ? ' selected' : '';
			$content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
		}
		$content[] = '</select></span>';
		$content[] = '</div>';
		$content[] = '</fieldset>';
		// submit
		$content[] = '<fieldset class=submit>';
		$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
		$content[] = '</fieldset>';
		$content[] = '</form>';
		$content[] = '<div class=map-demo><iframe style="height:'.($config['facebook_height'] + 20).'px;width:'.$config['facebook_width'].'px" src="'.WEB_URL.'/widgets/facebook/facebook.php"></iframe></div>';
		$content[] = '</div>';
		$content[] = '</section>';
		$content[] = '<script>';
		$content[] = '$G(window).Ready(function(){';
		$content[] = 'new GForm("setup_frm","'.WEB_URL.'/widgets/facebook/admin_setup_save.php").onsubmit(doFormSubmit);';
		$content[] = '});';
		$content[] = '</script>';
		// หน้านี้
		$url_query['module'] = 'facebook-setup';
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
