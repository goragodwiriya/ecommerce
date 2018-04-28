<?php
	// modules/download/admin_config.php
	if (MAIN_INIT == 'admin' && gcms::canConfig($config['download_can_config'])) {
		// ตรวจสอบโมดูลที่เรียก
		$sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='download' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 0) {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			$index = $index[0];
			// title
			$title = "$lng[LNG_CONFIG] $lng[LNG_DOWNLOAD]";
			$a = array();
			$a[] = '<span class=icon-download>{LNG_MODULES}</span>';
			$a[] = '{LNG_DOWNLOAD}';
			$a[] = '{LNG_CONFIG}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-config>'.$title.'</h1></header>';
			// form
			$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_UPLOADING}</span></legend>';
			// download_file_typies
			$content[] = '<div class=item>';
			$content[] = '<label for=config_file_typies>{LNG_UPLOAD_FILE_TYPIES}</label>';
			$content[] = '<span class="g-input icon-edit"><input type=text id=config_file_typies name=config_file_typies value="'.$config['download_file_typies'].'" title="{LNG_DOWNLOAD_FILE_TYPIES_COMMENT}"></span>';
			$content[] = '<div class=comment id=result_config_file_typies>{LNG_DOWNLOAD_FILE_TYPIES_COMMENT}</div>';
			$content[] = '</div>';
			//config_upload_size
			$content[] = '<div class=item>';
			$t = str_replace('{SIZE}', ini_get('upload_max_filesize'), $lng['LNG_DOWNLOAD_UPLOAD_SIZE_COMMENT']);
			$content[] = '<label for=config_upload_size>{LNG_UPLOAD_FILE_SIZE}</label>';
			$content[] = '<span class="g-input icon-config"><select name=config_upload_size id=config_upload_size title="'.$t.'">';
			$list = array(2, 4, 6, 8, 16, 32, 64, 128, 256, 512, 1024, 2048);
			foreach ($list AS $i) {
				$a = $i * 1048576;
				$sel = $a == $config['download_upload_size'] ? ' selected' : '';
				$content[] = '<option value='.$a.$sel.'>'.gcms::formatFileSize($a).'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment>'.$t.'</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// การแสดงผล
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_DISPLAY}</span></legend>';
			// list_per_page
			$content[] = '<div class=item>';
			$content[] = '<label for=config_list_per_page>{LNG_QUANTITY} :</label>';
			$content[] = '<span class="g-input icon-config"><select name=config_list_per_page id=config_list_per_page title="{LNG_LIST_PER_PAGE_COMMENT}">';
			foreach (array(10, 20, 30, 40, 50) AS $item) {
				$sel = $item == $config['download_list_per_page'] ? ' selected' : '';
				$content[] = '<option value='.$item.$sel.'>'.$item.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment id=result_config_list_per_page>{LNG_LIST_PER_PAGE_COMMENT}</div>';
			$content[] = '</div>';
			// download_news_count
			$content[] = '<div class=item>';
			$content[] = '<label for=config_news_count>{LNG_NEWS_COUNT} :</label>';
			$content[] = '<span class="g-input icon-config"><select name=config_news_count id=config_news_count title="{LNG_NEWS_COUNT_COMMENT}">';
			foreach (array(0, 10, 15, 20, 25, 30, 35, 40, 45, 50) AS $item) {
				$sel = $item == $config['download_news_count'] ? ' selected' : '';
				$content[] = '<option value='.$item.$sel.'>'.$item.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment>{LNG_NEWS_COUNT_COMMENT}</div>';
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
			$content[] = '<th scope=col>{LNG_DOWNLOAD}</th>';
			$content[] = '<th scope=col>{LNG_UPLOAD}</th>';
			$content[] = '<th scope=col>{LNG_CAN_CONFIG}</th>';
			$content[] = '</tr>';
			$content[] = '</thead>';
			$content[] = '<tbody>';
			// สถานะสมาชิก
			$status = array();
			$status[-1] = '{LNG_GUEST}';
			foreach ($config['member_status'] AS $i => $item) {
				$status[$i] = $item;
			}
			foreach ($status AS $i => $item) {
				$bg = $bg == 'bg1' ? 'bg2' : 'bg1';
				$tr = '<tr class="'.$bg.' status'.$i.'">';
				$tr .= '<th>'.$item.'</th>';
				// download_can_download
				$tr .= '<td><label data-text="{LNG_DOWNLOAD}"><input type=checkbox name=config_can_download[]'.(is_array($config['download_can_download']) && in_array($i, $config['download_can_download']) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_DOWNLOAD_COMMENT}"></label></td>';
				if ($i > 1) {
					// download_can_upload
					$tr .= '<td><label data-text="{LNG_UPLOAD}"><input type=checkbox name=config_can_upload[]'.(is_array($config['download_can_upload']) && in_array($i, $config['download_can_upload']) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_UPLOAD_COMMENT}"></label></td>';
					// download_can_config
					$tr .= '<td><label data-text="{LNG_CAN_CONFIG}"><input type=checkbox name=config_can_config[]'.(is_array($config['download_can_config']) && in_array($i, $config['download_can_config']) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_CONFIG_COMMENT}"></label></td>';
				} else {
					$tr .= '<td colspan=2></td>';
				}
				$tr .= '</tr>';
				$content[] = $tr;
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
			$content[] = 'new GForm("setup_frm", "'.WEB_URL.'/modules/download/admin_config_save.php").onsubmit(doFormSubmit);';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'download-config';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
