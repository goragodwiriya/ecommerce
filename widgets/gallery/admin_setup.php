<?php
	// widgets/gallery/admin_setup.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// ตรวจสอบค่า default
		$config['widget_gallery_cols'] = empty($config['widget_gallery_cols']) ? 2 : $config['widget_gallery_cols'];
		$config['widget_gallery_rows'] = empty($config['widget_gallery_rows']) ? 3 : $config['widget_gallery_rows'];
		$config['widget_gallery_width'] = empty($config['widget_gallery_width']) ? 75 : $config['widget_gallery_width'];
		$config['widget_gallery_url'] = empty($config['widget_gallery_url']) ? 'http://gallery.gcms.in.th/gallery.rss' : $config['widget_gallery_url'];
		// title
		$title = $lng['LNG_WIDGETS_GALLERY_SETTINGS'];
		$a = array();
		$a[] = '<span class=icon-widgets>{LNG_WIDGETS}</span>';
		$a[] = '{LNG_GALLERY}';
		// แสดงผล
		$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
		$content[] = '<section>';
		$content[] = '<header><h1 class=icon-gallery>'.$title.'</h1></header>';
		$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
		$content[] = '<fieldset>';
		$content[] = '<legend><span>{LNG_DETAIL_OPTIONS}</span></legend>';
		// widget_gallery_cols, widget_gallery_rows
		$content[] = '<div class=item>';
		$content[] = '<label for=gallery_cols>{LNG_DISPLAY}</label>';
		$content[] = '<div class=input-groups-table>';
		$content[] = '<label class=width for=gallery_cols>{LNG_COLS}</label>';
		$content[] = '<span class="width g-input icon-height"><input type=number name=gallery_cols id=gallery_cols value='.$config['widget_gallery_cols'].' title="{LNG_DISPLAY_ROWS_COLS_COMMENT}"></span>';
		$content[] = '<label class=width for=gallery_rows>{LNG_ROWS}</label>';
		$content[] = '<span class="width g-input icon-width"><input type=number name=gallery_rows id=gallery_rows value='.$config['widget_gallery_rows'].' title="{LNG_DISPLAY_ROWS_COLS_COMMENT}"></span>';
		$content[] = '</div>';
		$content[] = '<div class=comment>{LNG_DISPLAY_ROWS_COLS_COMMENT}</div>';
		$content[] = '</div>';
		// widget_gallery_tags
		$content[] = '<div class=item>';
		$content[] = '<label for=gallery_tags>{LNG_TAGS}</label>';
		$content[] = '<span class="g-input icon-tags"><input type=text id=gallery_tags name=gallery_tags value="'.$config['widget_gallery_tags'].'" title="{LNG_WIDGETS_GALLERY_TAGS_COMMENT}"></span>';
		$content[] = '<div class=comment id=result_gallery_tags>{LNG_WIDGETS_GALLERY_TAGS_COMMENT}</div>';
		$content[] = '</div>';
		// widget_gallery_album_id
		$content[] = '<div class=item>';
		$content[] = '<label for=gallery_album_id>{LNG_WIDGETS_GALLERY_AID}</label>';
		$content[] = '<span class="g-input icon-gallery"><input type=number id=gallery_album_id name=gallery_album_id value="'.$config['widget_gallery_album_id'].'" title="{LNG_WIDGETS_GALLERY_AID_COMMENT}"></span>';
		$content[] = '<div class=comment id=result_gallery_album_id>{LNG_WIDGETS_GALLERY_AID_COMMENT}</div>';
		$content[] = '</div>';
		// widget_gallery_user_id
		$content[] = '<div class=item>';
		$content[] = '<label for=gallery_user_id>{LNG_WIDGETS_GALLERY_UID}</label>';
		$content[] = '<span class="g-input icon-user"><input type=number id=gallery_user_id name=gallery_user_id value="'.$config['widget_gallery_user_id'].'" title="{LNG_WIDGETS_GALLERY_UID_COMMENT}"></span>';
		$content[] = '<div class=comment id=result_gallery_user_id>{LNG_WIDGETS_GALLERY_UID_COMMENT}</div>';
		$content[] = '</div>';
		// widget_gallery_width
		$content[] = '<div class=item>';
		$content[] = '<label for=gallery_width>{LNG_SIZE_OF} {LNG_IMAGE}</label>';
		$content[] = '<span class="g-input icon-width"><input type=number id=gallery_width name=gallery_width value="'.$config['widget_gallery_width'].'" title="{LNG_WIDGETS_GALLERY_SIZE_COMMENT}"></span>';
		$content[] = '<div class=comment id=result_gallery_width>{LNG_WIDGETS_GALLERY_SIZE_COMMENT}</div>';
		$content[] = '</div>';
		// widget_gallery_url
		$content[] = '<div class=item>';
		$content[] = '<label for=gallery_url>{LNG_URL}</label>';
		$content[] = '<span class="g-input icon-world"><input type=url id=gallery_url name=gallery_url value="'.$config['widget_gallery_url'].'" title="{LNG_WIDGETS_GALLERY_URL_COMMENT}"></span>';
		$content[] = '<div class=comment id=result_gallery_url>{LNG_WIDGETS_GALLERY_URL_COMMENT}</div>';
		$content[] = '</div>';
		$content[] = '</fieldset>';
		// submit
		$content[] = '<fieldset class=submit>';
		$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
		$content[] = '</fieldset>';
		$content[] = '<aside class=message>{LNG_WIDGETS_GALLERY_INFO}</aside>';
		$content[] = '</form>';
		$content[] = '</section>';
		$content[] = '<script>';
		$content[] = '$G(window).Ready(function(){';
		$content[] = 'new GForm("setup_frm","'.WEB_URL.'/widgets/gallery/admin_setup_save.php").onsubmit(doFormSubmit);';
		$content[] = '});';
		$content[] = '</script>';
		// หน้านี้
		$url_query['module'] = 'gallery-setup';
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
