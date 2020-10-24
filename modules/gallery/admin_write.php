<?php
	// modules/gallery/admin_write.php
	if (MAIN_INIT == 'admin' && gcms::canConfig($config['gallery_can_write'])) {
		// อัลบัมที่แก้ไข
		$album_id = (int)$_GET['id'];
		// ตรวจสอบโมดูลที่เรียก
		if ($album_id > 0) {
			$sql = "SELECT C.*,M.`module`,G.`image` FROM `".DB_MODULES."` AS M";
			$sql .= " INNER JOIN `".DB_GALLERY_ALBUM."` AS C ON C.`module_id`=M.`id` AND C.`id`=$album_id";
			$sql .= " LEFT JOIN `".DB_GALLERY."` AS G ON G.`album_id`=C.`id` AND G.`module_id`=M.`id` AND G.`count`='0'";
		} else {
			$sql = "SELECT 0 AS `id`,M.`id` AS `module_id`,M.`module` FROM `".DB_MODULES."` AS M";
		}
		$sql .= " WHERE M.`owner`='gallery' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 1) {
			$index = $index[0];
			// title
			$title = "$lng[LNG_CREATE] - $lng[LNG_EDIT] $lng[LNG_GALLERY_ALBUM]";
			$a = array();
			$a[] = '<span class=icon-gallery>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=gallery-config&id=0}">{LNG_GALLERY}</a>';
			$a[] = '<a href="{URLQUERY?module=gallery-album&id=0}">{LNG_GALLERY_ALBUM}</a>';
			$a[] = $album_id == 0 ? '{LNG_CREATE}' : '{LNG_EDIT}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-write>'.$title.'</h1></header>';
			// ฟอร์มเพิ่ม แก้ไข หมวด
			$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_GALLERY_ALBUM_DETAIL}</span></legend>';
			// topic
			$content[] = '<div class=item>';
			$content[] = '<label for=gallery_topic>{LNG_GALLERY_ALBUM}</label>';
			$content[] = '<span class="g-input icon-edit"><input type=text id=gallery_topic name=gallery_topic maxlength=64 title="{LNG_GALLERY_ALBUM_COMMENT}" value="'.$index['topic'].'"></span>';
			$content[] = '<div class=comment id=result_gallery_topic>{LNG_GALLERY_ALBUM_COMMENT}</div>';
			$content[] = '</div>';
			// detail
			$content[] = '<div class=item>';
			$content[] = '<label for=gallery_detail>{LNG_DESCRIPTION}</label>';
			$content[] = '<span class="g-input icon-file"><textarea id=gallery_detail name=gallery_detail rows=3 maxlength=200 title="{LNG_GALLERY_ALBUM_DESCRIPTION_COMMENT}">'.gcms::detail2TXT($index['detail']).'</textarea></span>';
			$content[] = '<div class=comment id=result_gallery_detail>{LNG_GALLERY_ALBUM_DESCRIPTION_COMMENT}</div>';
			$content[] = '</div>';
			// icon
			$content[] = '<div class=item>';
			$icon = is_file(DATA_PATH."gallery/$index[id]/thumb_$index[image]") ? DATA_URL."gallery/$index[id]/thumb_$index[image]" : WEB_URL.'/modules/gallery/img/nopicture.png';
			$content[] = '<div class=usericon><span><img src="'.$icon.'" id=imgIcon alt=imgIcon></span></div>';
			$content[] = '<label for=gallery_pic>{LNG_BROWSE_FILE}</label>';
			$content[] = '<span class="g-input icon-upload"><input type=file class=g-file id=gallery_pic name=gallery_pic accept="'.gcms::getEccept($config['gallery_image_type']).'" title="{LNG_IMAGE_UPLOAD_RESIZE_COMMENT}" data-preview=imgIcon></span>';
			$content[] = '<div class=comment id=result_gallery_pic>{LNG_IMAGE_UPLOAD_RESIZE_COMMENT}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset class=submit>';
			$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
			$content[] = '<input type=hidden id=galleryId name=galleryId value='.(int)$index['id'].'>';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = 'new GForm("setup_frm","'.WEB_URL.'/modules/gallery/admin_write_save.php").onsubmit(doFormSubmit);';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'gallery-write';
		} else {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
