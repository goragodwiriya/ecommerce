<?php
	// modules/download/admin_write.php
	if (MAIN_INIT == 'admin' && gcms::canConfig($config['download_can_upload'])) {
		// id ที่เลือก
		$id = (int)$_GET['id'];
		// ตรวจสอบค่าที่ส่งมา
		if ($id > 0) {
			$sql = "SELECT C.*,M.`module` FROM `".DB_DOWNLOAD."` AS C";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id`";
			$sql .= " WHERE C.`id`=$id AND M.`owner`='download' LIMIT 1";
		} else {
			// ใหม่ ตรวจสอบโมดูล
			$sql = "SELECT `id` AS `module_id` FROM `".DB_MODULES."` WHERE `owner`='download' LIMIT 1";
		}
		$index = $db->customQuery($sql);
		if (sizeof($index) == 1) {
			$index = $index[0];
			// title
			$title = "$lng[LNG_UPLOAD]/$lng[LNG_EDIT] $lng[LNG_DOWNLOAD_FILES]";
			$a = array();
			$a[] = '<span class=icon-download>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=download-config&id=0}">{LNG_DOWNLOAD}</a>';
			$a[] = '<a href="{URLQUERY?module=download-setup&id=0}">{LNG_DOWNLOAD_FILES}</a>';
			$a[] = $id == 0 ? '{LNG_UPLOAD}' : '{LNG_EDIT}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-write>'.$title.'</h1></header>';
			// form
			$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
			$content[] = '<fieldset>';
			$content[] = '<legend><span>'.$a[3].'</span></legend>';
			// download_name
			$content[] = '<div class=item>';
			$content[] = '<label for=download_name>{LNG_DOWNLOAD_NAME}</label>';
			$content[] = '<span class="g-input icon-edit"><input type=text id=download_name name=download_name title="{LNG_DOWNLOAD_NAME_COMMENT}" value="'.$index['name'].'"></span>';
			$content[] = '<div class=comment id=result_download_name>{LNG_DOWNLOAD_NAME_COMMENT}</div>';
			$content[] = '</div>';
			// download_description
			$content[] = '<div class=item>';
			$content[] = '<label for=download_description>{LNG_DESCRIPTION}</label>';
			$content[] = '<span class="g-input icon-file"><input type=text id=download_description name=download_description title="{LNG_DOWNLOAD_DESCRIPTION_COMMENT}" value="'.$index['detail'].'"></span>';
			$content[] = '<div class=comment id=result_download_description>{LNG_DOWNLOAD_DESCRIPTION_COMMENT}</div>';
			$content[] = '</div>';
			// category
			$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]' ORDER BY `category_id`";
			$content[] = '<div class=item>';
			$content[] = '<label for=download_category>{LNG_CATEGORY}</label>';
			$content[] = '<span class="g-input icon-category"><select name=download_category id=download_category title="{LNG_CATEGORY_SELECT}">';
			foreach ($db->customQuery($sql) AS $item) {
				$sel = $item['category_id'] == $index['category_id'] ? ' selected' : '';
				$content[] = '<option value='.$item['category_id'].$sel.'>'.gcms::ser2Str($item['topic']).'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment id=result_download_category>{LNG_CATEGORY_SELECT}</div>';
			$content[] = '</div>';
			// download_file
			$content[] = '<div class=item>';
			$content[] = '<label for=download_file>{LNG_DOWNLOAD_FILE}</label>';
			$content[] = '<span class="g-input icon-world"><input type=text id=download_file name=download_file title="{LNG_DOWNLOAD_FILE_COMMENT}" value="'.$index['file'].'"></span>';
			$content[] = '<div class=comment id=result_download_file>{LNG_DOWNLOAD_FILE_COMMENT}</div>';
			$content[] = '</div>';
			// download_upload
			$content[] = '<div class=item>';
			$t = str_replace(array('{TYPE}', '{SIZE}'), array(str_replace(',', ', ', $config['download_file_typies']), gcms::formatFileSize($config['download_upload_size'])), $lng['LNG_DOWNLOAD_FILE_BROWSER_COMMENT']);
			$content[] = '<label for=download_upload>{LNG_BROWSE_FILE}</label>';
			$content[] = '<span class="g-input icon-upload"><input type=file class=g-file id=download_upload name=download_upload title="'.$t.'" placeholder="'.$index['file'].'"></span>';
			$content[] = '<div class=comment id=result_download_upload>'.$t.'</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset class=submit>';
			$content[] = '<input type=submit class="button large save" value="{LNG_DOWNLOAD_SAVE}">';
			$content[] = '<input type=hidden id=write_id name=write_id value='.(int)$index['id'].'>';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = 'new GForm("setup_frm","'.WEB_URL.'/modules/download/admin_write_save.php").onsubmit(doFormSubmit);';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'download-write';
		} else {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
