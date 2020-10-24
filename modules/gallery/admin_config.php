<?php
	// modules/gallery/admin_config.php
	if (MAIN_INIT == 'admin' && gcms::canConfig($config['gallery_can_config'])) {
		// ตรวจสอบโมดูลที่เรียก
		$sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='gallery' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 0) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			$index = $index[0];
			// title
			$title = "$lng[LNG_CONFIG] $lng[LNG_GALLERY]";
			$a = array();
			$a[] = '<span class=icon-gallery>{LNG_MODULES}</span>';
			$a[] = '{LNG_GALLERY}';
			$a[] = '{LNG_CONFIG}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-config>'.$title.'</h1></header>';
			// form
			$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
			// รายละเอียดของแกลอรี่
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_GALLERY_DETAILS}</span></legend>';
			// gallery_thumb_w,gallery_thumb_h
			$content[] = '<div class=item>';
			$content[] = '<span class=label>{LNG_THUMBNAIL} ({LNG_PX})</span>';
			$content[] = '<div class=input-groups-table>';
			$content[] = '<label class=width for=config_thumb_w>{LNG_WIDTH}&nbsp;</label>';
			$content[] = '<span class="width g-input icon-width"><input type=number name=config_thumb_w id=config_thumb_w value="'.$config['gallery_thumb_w'].'"></span>';
			$content[] = '<label class=width for=config_thumb_h>&nbsp;{LNG_HEIGHT}&nbsp;</label>';
			$content[] = '<span class="width g-input icon-height"><input type=number name=config_thumb_h id=config_thumb_h value="'.$config['gallery_thumb_h'].'"></span>';
			$content[] = '</div>';
			$content[] = '<div class=comment>{LNG_GALLERY_THUMB_WIDTH_COMMENT}</div>';
			$content[] = '</div>';
			// gallery_image_w
			$content[] = '<div class=item>';
			$content[] = '<label for=config_image_w>{LNG_IMAGE} {LNG_WIDTH} ({LNG_PX})</label>';
			$content[] = '<span class="table g-input icon-width"><input type=number min=50 name=config_image_w id=config_image_w value="'.$config['gallery_image_w'].'" title="{LNG_WIDTH} {LNG_PX}"></span>';
			$content[] = '<div class=comment>{LNG_GALLERY_SIZE_COMMENT}</div>';
			$content[] = '</div>';
			// gallery_image_type
			$content[] = '<div class=item>';
			$content[] = '<label for=config_image_type>{LNG_IMAGE_FILE_TYPIES}</label>';
			$content[] = '<div>';
			foreach (array('jpg', 'gif', 'png') AS $i => $item) {
				$chk = is_array($config['gallery_image_type']) && in_array($item, $config['gallery_image_type']) ? ' checked' : '';
				$d = $item == 'jpg' ? ' id=config_image_type' : '';
				$content[] = '<label><input type=checkbox'.$chk.$d.' name=config_image_type[] value='.$item.' title="{LNG_IMAGE_UPLOAD_TYPE_COMMENT}"> '.$item.'</label>';
			}
			$content[] = '</div>';
			$content[] = '<div class=comment id=result_config_image_type>{LNG_IMAGE_UPLOAD_TYPE_COMMENT}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// การแสดงผล
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_DISPLAY}</span></legend>';
			// gallery_cols,gallery_rows
			$content[] = '<div class=item>';
			$content[] = '<label for=config_cols>{LNG_GALLERY_ALBUM}</label>';
			$content[] = '<div class=input-groups-table>';
			$content[] = '<label class=width for=config_cols>{LNG_COLS}&nbsp;</label>';
			$content[] = '<span class="width20 g-input icon-height"><select name=config_cols id=config_cols>';
			foreach (array(1,2,3,4,6,12) AS $i) {
				$sel = $i == $config['gallery_cols'] ? ' selected' : '';
				$content[] = '<option value='.$i.$sel.'>'.$i.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<label class=width for=config_rows>&nbsp;{LNG_ROWS}&nbsp;</label>';
			$content[] = '<span class="width20 g-input icon-width"><select name=config_rows id=config_rows>';
			for ($i = 1; $i < 20; $i++) {
				$sel = $i == $config['gallery_rows'] ? ' selected' : '';
				$content[] = '<option value='.$i.$sel.'>'.$i.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '</div>';
			$content[] = '<div class=comment>{LNG_GALLERY_DISPLAY_COMMENT}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// กำหนดความสามารถของสมาชิกแต่ละระดับ
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_MEMBER_ROLE_SETTINGS}</span></legend>';
			$content[] = '<div class=item>';
			$content[] = '<table class="responsive config_table">';
			$content[] = '<thead>';
			$content[] = '<tr>';
			$content[] = '<th>&nbsp;</th>';
			$content[] = '<th scope=col>{LNG_UPLOADING}</th>';
			$content[] = '<th scope=col>{LNG_CAN_CONFIG}</th>';
			$content[] = '</tr>';
			$content[] = '</thead>';
			$content[] = '<tbody>';
			// สถานะสมาชิก
			foreach ($config['member_status'] AS $i => $item) {
				if ($i > 1) {
					$bg = $bg == 'bg1' ? 'bg2' : 'bg1';
					$tr = '<tr class="'.$bg.' status'.$i.'">';
					$tr .= '<th>'.$item.'</th>';
					// can_write
					$tr .= '<td><label data-text="{LNG_UPLOADING}"><input type=checkbox name=config_can_write[]'.(is_array($config['gallery_can_write']) && in_array($i, $config['gallery_can_write']) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_UPLOAD_COMMENT}"></label></td>';
					// can_config
					$tr .= '<td><label data-text="{LNG_CAN_CONFIG}"><input type=checkbox name=config_can_config[]'.(is_array($config['gallery_can_config']) && in_array($i, $config['gallery_can_config']) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_CONFIG_COMMENT}"></label></td>';
					$tr .= '</tr>';
					$content[] = $tr;
				}
			}
			$content[] = '</tbody>';
			$content[] = '</table>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset class=submit>';
			$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = 'new GForm("setup_frm", "'.WEB_URL.'/modules/gallery/admin_config_save.php").onsubmit(doFormSubmit);';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'gallery-config';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
