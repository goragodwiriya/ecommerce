<?php
	// modules/index/admin_write.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// 0 = ใหม่, > 0 แก้ไข
		$id = (int)$_GET['id'];
		if ($id > 0) {
			$sql = "SELECT I.`id`,I.`language`,D.`topic`,D.`keywords`,D.`description`,D.`detail`,I.`last_update`,I.`published`,I.`published_date`";
			$sql .= ",M.`module`,M.`owner`";
			$sql .= " FROM `".DB_INDEX."` AS I";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
			$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language`=I.`language`";
			$sql .= " WHERE I.`id`=$id AND M.`id`=I.`module_id` AND I.`index`='1'";
			$sql .= " LIMIT 1";
			$index = $db->customQuery($sql);
			$index = sizeof($index) == 0 ? false : $index[0];
		} else {
			$index['owner'] = $db->sql_trim_str($_GET['owner']);
			$index['id'] = 0;
			$index['published'] = 1;
		}
		if ($id > 0 && !$index) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			// title
			$m = ucwords($index['owner']);
			$title = $index['module'] != '' ? '{LNG_EDIT}' : '{LNG_CREATE}';
			$title .= $index['owner'] == 'index' ? ' {LNG_WEB_PAGES}' : ' {LNG_MODULE}';
			$title .= ' '.$index['module'];
			$a = array();
			$a[] = '<span class=icon-modules>{LNG_MENUS}&nbsp;&amp;&nbsp;{LNG_WEB_PAGES}</span>';
			if ($index['owner'] == 'index') {
				$a[] = '<a href="{URLQUERY?module=index-pages&id=0}">{LNG_WEB_PAGES}</a>';
			} else {
				$a[] = '<a href="{URLQUERY?module=index-insmod&id=0}">{LNG_INSTALLED_MODULE}</a>';
			}
			$a[] = $index['module'] != '' ? '{LNG_EDIT}' : '{LNG_ADD_NEW}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-write>'.$title.' ('.$m.')</h1></header>';
			$content[] = '<form id=write_frm class=setup_frm method=post action=index.php>';
			$content[] = '<fieldset>';
			// language
			$content[] = '<div class=item>';
			$content[] = '<label for=write_language>{LNG_LANGUAGE}</label>';
			$content[] = '<div class="table collapse">';
			$content[] = '<div class=td>';
			$content[] = '<span class="g-input icon-published1"><select name=write_language id=write_language title="{LNG_ALL_LANGUAGES_COMMENT}" autofocus>';
			$content[] = '<option value="">{LNG_ALL} {LNG_LANGUAGE}</option>';
			foreach ($config['languages'] AS $language) {
				$sel = $language == $index['language'] ? ' selected' : '';
				$content[] = '<option value='.$language.$sel.'>'.$language.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '</div>';
			$content[] = '<div class=td>&nbsp;<a id=btn_copy title="{LNG_COPY}" class="button copy"><span class=icon-copy></span></a></div>';
			$content[] = '</div>';
			$content[] = '<div class=comment id=result_write_language>{LNG_ALL_LANGUAGES_COMMENT}</div>';
			$content[] = '</div>';
			// module
			$content[] = '<div class=item>';
			$r = $index['owner'] == 'index' || isset($config[$index['owner']]['description']) ? '' : ' disabled';
			$content[] = '<label for=write_module>{LNG_MODULE_NAME}</label>';
			$content[] = '<span class="g-input icon-modules"><input type=text name=write_module id=write_module value="'.$index['module'].'" maxlength=20 title="{LNG_MODULE_NAME_COMMENT}"'.$r.'></span>';
			$content[] = '<div class=comment id=result_write_module>{LNG_MODULE_NAME_COMMENT}</div>';
			$content[] = '</div>';
			// topic
			$content[] = '<div class=item>';
			$content[] = '<label for=write_topic>{LNG_TITLE}</label>';
			$content[] = '<span class="g-input icon-edit"><input type=text name=write_topic id=write_topic value="'.$index['topic'].'" maxlength=64 title="{LNG_TITLE_COMMENT}"></span>';
			$content[] = '<div class=comment id=result_write_topic>{LNG_TITLE_COMMENT}</div>';
			$content[] = '</div>';
			// keywords
			$content[] = '<div class=item>';
			$content[] = '<label for=write_keywords>{LNG_KEYWORDS}</label>';
			$content[] = '<span class="g-input icon-tags"><textarea name=write_keywords id=write_keywords rows=3 maxlength=149 title="{LNG_KEYWORDS_COMMENT}">'.gcms::detail2TXT($index['keywords']).'</textarea></span>';
			$content[] = '<div class=comment id=result_write_keywords>{LNG_KEYWORDS_COMMENT}</div>';
			$content[] = '</div>';
			// description
			$content[] = '<div class=item>';
			$content[] = '<label for=write_description>{LNG_DESCRIPTION}</label>';
			$content[] = '<span class="g-input icon-file"><textarea name=write_description id=write_description rows=3 maxlength=149 title="{LNG_DESCRIPTION_COMMENT}">'.gcms::detail2TXT($index['description']).'</textarea></span>';
			$content[] = '<div class=comment id=result_write_description>{LNG_DESCRIPTION_COMMENT}</div>';
			$content[] = '</div>';
			// detail
			$content[] = '<div class=item>';
			$content[] = '<label for=write_detail>{LNG_DETAIL}</label>';
			$content[] = '<div><textarea name=write_detail id=write_detail>'.gcms::detail2TXT($index['detail']).'</textarea></div>';
			$content[] = '</div>';
			// published date
			$content[] = '<div class=item>';
			$content[] = '<label for=write_published_date>{LNG_PUBLISHED_DATE}</label>';
			$content[] = '<span class="g-input icon-calendar"><input type=date id=write_published_date name=write_published_date value="'.$index['published_date'].'" title="{LNG_PUBLISHED_DATE_COMMENT}"></span>';
			$content[] = '<div class=comment>{LNG_PUBLISHED_DATE_COMMENT}</div>';
			$content[] = '</div>';
			// published
			$content[] = '<div class=item>';
			$content[] = '<label for=write_published>{LNG_PUBLISHED}</label>';
			$content[] = '<span class="g-input icon-published1"><select id=write_published name=write_published title="{LNG_PUBLISHED_SETTING}">';
			foreach ($lng['LNG_PUBLISHEDS'] AS $i => $item) {
				$sel = $index['published'] == $i ? ' selected' : '';
				$content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment>{LNG_PUBLISHED_SETTING}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset class=submit>';
			$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
			$content[] = '<input type=button id=write_open class="button large preview" value="{LNG_PREVIEW}">';
			$content[] = '<input type=hidden id=write_id name=write_id value='.$index['id'].'>';
			$content[] = '<input type=hidden id=write_owner name=write_owner value='.$index['owner'].'>';
			$content[] = '</fieldset>';
			$lastupdate = $index['last_update'] == '' ? '-' : gcms::mktime2date($index['last_update']);
			$content[] = '<div class=lastupdate><span class=comment>{LNG_WRITE_COMMENT}</span>{LNG_LAST_UPDATE}<span id=lastupdate>'.$lastupdate.'</span></div>';
			$content[] = '</form>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = 'CKEDITOR.replace("write_detail", {';
			$content[] = 'toolbar:"Document",';
			$content[] = 'language:"'.LANGUAGE.'",';
			$content[] = 'height:300,';
			if (is_dir(ROOT_PATH.'ckfinder/')) {
				$content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html",';
				$content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Images",';
				$content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Flash",';
				$content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files",';
				$content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images",';
				$content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash"';
			} else {
				$connector = urlencode(WEB_URL.'/ckeditor/filemanager/connectors/php/connector.php');
				$content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Connector='.$connector.'",';
				$content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector='.$connector.'",';
				$content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector='.$connector.'",';
				$content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php",';
				$content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php?Type=Image",';
				$content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.phpType=Flash"';
			}
			$content[] = '});';
			$content[] = 'inintIndexWrite();';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'index-write';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
