<?php
	// modules/gallery/admin_upload.php
	if (MAIN_INIT == 'admin' && gcms::canConfig($config['gallery_can_write'])) {
		// อัลบัมที่แก้ไข
		$id = (int)$_GET['id'];
		// ตรวจสอบโมดูลที่เรียก
		if ($id > 0) {
			$sql = "SELECT C.`id`,C.`topic`,M.`module` FROM `".DB_MODULES."` AS M";
			$sql .= " INNER JOIN `".DB_GALLERY_ALBUM."` AS C ON C.`module_id`=M.`id` AND C.`id`=$id";
			$sql .= " WHERE M.`owner`='gallery' LIMIT 1";
			$index = $db->customQuery($sql);
		}
		if ($id == 0 || sizeof($index) == 0) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			$index = $index[0];
			// guploads
			$javascript['guploads'] = '<script src='.WEB_URL.'/widgets/guploads/script.js></script>';
			// title
			$title = "$lng[LNG_ADD] - $lng[LNG_DELETE] $lng[LNG_IMAGE]";
			$a = array();
			$a[] = '<span class=icon-gallery>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=gallery-config&id=0}">{LNG_GALLERY}</a>';
			$a[] = '<a href="{URLQUERY?module=gallery-album&id=0}">{LNG_GALLERY_ALBUM}</a>';
			$a[] = '{LNG_GALLERY_UPLOAD}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-upload>'.$title.'&nbsp;{LNG_GALLERY_ALBUM}&nbsp;'.$index['topic'].'</h1></header>';
			$content[] = '<div id=gallery-upload class=setup_frm>';
			$content[] = '<div class=paper>';
			$content[] = '<form id=upload_frm method=post action="'.WEB_URL.'/modules/gallery/admin_upload_save.php">';
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_GALLERY_UPLOAD}</span></legend>';
			$content[] = '<div class=item>';
			$content[] = '<label><input type=file name=fileupload id=fileupload accept="'.gcms::getEccept($config['gallery_image_type']).'"></label>';
			$content[] = '<div class=comment id=result_fileupload>'.str_replace(array('{TYPE}', '{SIZE}', '{COUNT}'), array(implode(', ', $config['gallery_image_type']), $config['gallery_upload_size'], 10), $lng['LNG_GALLERY_UPLOAD_COMMENT']).'</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			$content[] = '<fieldset class=submit>';
			$content[] = '<input type=submit class="button large green" value="{LNG_UPLOAD}">';
			$content[] = '<input type=button class="button large orange" value="{LNG_UPLOAD_CANCLE}" id=btnCancle disabled>';
			$content[] = '<input type=button class="button large red" value="{LNG_DELETE}" id=btnDelete>';
			$content[] = '<input type=hidden id=gallery_module value="'.$index['module'].'">';
			$content[] = '<input type=hidden id=album_id value="'.$index['id'].'">';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			$content[] = '<div id=fsUploadProgress class=clear></div>';
			$content[] = '</div>';
			$content[] = '<div id=tb_upload>';
			$sql = "SELECT * FROM `".DB_GALLERY."` WHERE `album_id`='$index[id]' ORDER BY `count` ASC";
			$td = '';
			foreach ($db->customQuery($sql) AS $i => $item) {
				$id = $item['id'];
				$td .= '<div class=item id=L_'.$id.'>';
				$td .= '<figure>';
				$td .= '<a id=preview_'.$id.' href="'.DATA_URL.'gallery/'.$item['album_id'].'/'.$item['image'].'">';
				$td .= '<img src="'.DATA_URL.'gallery/'.$item['album_id'].'/thumb_'.$item['image'].'" alt='.$id.' style="max-width:'.$config['gallery_thumb_w'].'px">';
				$td .= '</a>';
				if ($i > 0) {
					$td .= '<a class=icon-uncheck id=delete_'.$id.'_'.$item['album_id'].' title="{LNG_DELETE}"></a>';
				}
				$td .= '</figure>';
				$td .= '</div>';
			}
			$content[] = $td;
			$content[] = '</div>';
			$content[] = '</div>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = 'var upload = new gUploads({';
			$content[] = '"form":"upload_frm",';
			$content[] = '"input":"fileupload",';
			$content[] = '"fileprogress":"fsUploadProgress",';
			$content[] = '"oncomplete":galleryUploadResult,';
			$content[] = '"onupload":function(){$E("btnCancle").disabled = ""},';
			$content[] = 'customSettings:{"albumId":'.$index['id'].'}';
			$content[] = '});';
			$content[] = '$G("btnCancle").addEvent("click", function(){upload.cancle()});';
			$content[] = 'inintGalleryUpload("tb_upload");';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'gallery-upload';
		}
	} else {
		$title = $lng['ACTION_FORBIDDEN'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
