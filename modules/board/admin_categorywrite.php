<?php
	// modules/board/admin_categorywrite.php
	if (MAIN_INIT == 'admin' && $isMember) {
		// หมวดที่เรียก
		$category_id = (int)$_GET['cat'];
		$module_id = (int)$_GET['id'];
		if ($category_id == 0) {
			// ใหม่, ตรวจสอบโมดูลที่เรียก
			$sql1 = " SELECT MAX(`category_id`) FROM `".DB_CATEGORY."` WHERE `module_id`=M.`id`";
			$sql = "SELECT 0 AS `id`,M.`id` AS `module_id`,M.`module`,M.`config` AS `mconfig`,1+COALESCE(($sql1),0) AS `category_id`";
			$sql .= " FROM `".DB_MODULES."` AS M";
			$sql .= " WHERE M.`id`=$module_id AND M.`owner`='board' LIMIT 1";
		} else {
			// แก้ไข ตรวจสอบโมดูลและหมวดที่เลือก
			$sql = "SELECT C.*,M.`module`,M.`config` AS `mconfig`";
			$sql .= " FROM `".DB_CATEGORY."` AS C";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=$module_id AND M.`owner`='board'";
			$sql .= " WHERE C.`id`=$category_id AND C.`module_id`=$module_id LIMIT 1";
		}
		$index = $db->customQuery($sql);
		if (sizeof($index) == 1) {
			$index = $index[0];
			// อ่าน config ของโมดูล
			gcms::r2config($index['mconfig'], $index);
			// ตรวจสอบสถานะที่สามารถเข้าหน้านี้ได้
			if (!gcms::canConfig(explode(',', $index['can_config']))) {
				$index = false;
			}
		} else {
			$index = false;
		}
		if ($index) {
			if ($category_id > 0) {
				// config ของหมวด
				gcms::r2config($index['config'], $index);
			}
			// title
			$m = ucwords($index['module']);
			$title = "$lng[LNG_CREATE] - $lng[LNG_EDIT] $lng[LNG_CATEGORY]";
			$a = array();
			$a[] = '<span class=icon-board>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=board-config&id='.$module_id.'}">'.$m.'</a>';
			$a[] = '<a href="{URLQUERY?module=board-category&id='.$module_id.'}">{LNG_CATEGORY}</a>';
			$a[] = $category_id == 0 ? '{LNG_CREATE}' : '{LNG_EDIT}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-write>'.$title.'</h1></header>';
			// ฟอร์มเพิ่ม แก้ไข หมวด
			$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_CATEGORY}</span></legend>';
			// category_id
			$content[] = '<div class=item>';
			$content[] = '<label for=category_id>{LNG_ID}</label>';
			$content[] = '<span class="g-input icon-edit"><input type=number class=number id=category_id name=category_id value='.(int)$index['category_id'].' title="{LNG_CATEGORY_ID_COMMENT}"></span>';
			$content[] = '<div class=comment id=result_category_id>{LNG_CATEGORY_ID_COMMENT}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// topic,detail,icon
			$topic = gcms::ser2Array($index['topic']);
			$detail = gcms::ser2Array($index['detail']);
			$icon = gcms::ser2Array($index['icon']);
			foreach ($config['languages'] AS $item) {
				$content[] = '<fieldset>';
				$content[] = '<legend><span>{LNG_CATEGORY_DETAIL}&nbsp;<img src="'.($item == '' ? "../skin/img/blank.gif" : DATA_URL.'language/'.$item.'.gif').'" alt="'.$item.'"></span></legend>';
				// topic
				$content[] = '<div class=item>';
				$content[] = '<div>';
				$content[] = '<label for=category_topic_'.$item.'>{LNG_CATEGORY}</label>';
				$t = $item == LANGUAGE && !isset($topic[LANGUAGE]) ? $topic[''] : $topic[$item];
				$content[] = '<span class="g-input icon-edit"><input type=text id=category_topic_'.$item.' name=category_topic['.$item.'] maxlength=64 title="{LNG_CATEGORY_COMMENT}" value="'.$t.'"></span>';
				$content[] = '</div>';
				$content[] = '<div class=comment id=result_category_topic_'.$item.'>{LNG_CATEGORY_COMMENT}</div>';
				$content[] = '</div>';
				// detail
				$content[] = '<div class=item>';
				$content[] = '<div>';
				$content[] = '<label for=category_detail_'.$item.'>{LNG_DESCRIPTION}</label>';
				$t = $item == LANGUAGE && !isset($detail[LANGUAGE]) ? $detail[''] : $detail[$item];
				$content[] = '<span class="g-input icon-file"><textarea id=category_detail_'.$item.' name=category_detail['.$item.'] rows=3 maxlength=200 title="{LNG_CATEGORY_DESCRIPTION_COMMENT}">'.gcms::detail2TXT($t).'</textarea></span>';
				$content[] = '</div>';
				$content[] = '<div class=comment id=result_category_detail_'.$item.'>{LNG_CATEGORY_DESCRIPTION_COMMENT}</div>';
				$content[] = '</div>';
				// icon
				$content[] = '<div class=item>';
				$t = $item == LANGUAGE && !isset($icon[LANGUAGE]) ? $icon[''] : $icon[$item];
				$content[] = '<div class=usericon><span><img id=icon_'.$item.' src='.(is_file(DATA_PATH."board/$t") ? DATA_URL."board/$t" : WEB_URL."/$index[default_icon]").' alt=icon></span></div>';
				$content[] = '<div>';
				$content[] = '<label for=category_icon_'.$item.'>{LNG_ICON}</label>';
				$content[] = '<span class="g-input icon-upload"><input class=g-file type=file id=category_icon_'.$item.' name=category_icon_'.$item.' title="{LNG_UPLOAD_CATEGORY_ICON_COMMENT}" accept="'.gcms::getEccept(array('jpg', 'png', 'gif')).'" data-preview=icon_'.$item.'></span>';
				$content[] = '</div>';
				$content[] = '<div class=comment id=result_category_icon_'.$item.'>{LNG_UPLOAD_CATEGORY_ICON_COMMENT}</div>';
				$content[] = '</div>';
				$content[] = '</fieldset>';
			}
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_UPLOADING}</span></legend>';
			// img_upload_type
			$content[] = '<div class=item>';
			$content[] = '<label for=category_img_upload_type_jpg>{LNG_UPLOAD_FILE_TYPIES}</label>';
			$content[] = '<div>';
			$img_upload_typies = explode(',', $index['img_upload_type']);
			foreach (array('jpg', 'gif', 'png') AS $item) {
				$chk = in_array($item, $img_upload_typies) ? ' checked' : '';
				$content[] = '<label><input type=checkbox id=category_img_upload_type_'.$item.$chk.' name=category_img_upload_type[] value='.$item.' title="{LNG_UPLOAD_FILE_TYPE_COMMENT}">&nbsp;'.$item.'</label>';
			}
			$content[] = '</div>';
			$content[] = '<div class=comment>{LNG_UPLOAD_FILE_TYPE_COMMENT}</div>';
			$content[] = '</div>';
			// img_upload_size
			$content[] = '<div class=item>';
			$content[] = '<div>';
			$content[] = '<label for=category_img_upload_size>{LNG_UPLOAD_FILE_SIZE}</label>';
			$content[] = '<span class="g-input icon-config"><select name=category_img_upload_size id=category_img_upload_size title="{LNG_UPLOAD_FILE_SIZE_COMMENT}">';
			foreach (array(100, 200, 300, 400, 500, 600, 700, 800, 900, 1024, 2048) AS $item) {
				$sel = $item == $index['img_upload_size'] ? ' selected' : '';
				$content[] = '<option value='.$item.$sel.'>'.$item.' kb.</option>';
			}
			$content[] = '</select></span>';
			$content[] = '</div>';
			$content[] = '<div class=comment id=result_category_img_upload_size>{LNG_UPLOAD_FILE_SIZE_COMMENT}</div>';
			$content[] = '</div>';
			// img_law
			$content[] = '<div class=item>';
			$content[] = '<div>';
			$content[] = '<label for=category_img_law>{LNG_BOARD_IMG_LAW}</label>';
			$content[] = '<span class="g-input icon-config"><select name=category_img_law id=category_img_law title="{LNG_BOARD_IMG_LAW_COMMENT}">';
			foreach ($lng['BOARD_IMG_LAWS'] AS $i => $item) {
				$sel = $item == $index['img_law'] ? ' selected' : '';
				$content[] = '<option value='.$i.$sel.'>'.$item.' kb.</option>';
			}
			$content[] = '</select></span>';
			$content[] = '</div>';
			$content[] = '<div class=comment id=result_category_img_law>{LNG_BOARD_IMG_LAW_COMMENT}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// กำหนดความสามารถของสมาชิกแต่ละระดับ
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_MEMBER_ROLE_SETTINGS}</span></legend>';
			$content[] = '<div class=item>';
			$content[] = '<table class="responsive config_table">';
			$content[] = '<thead>';
			$content[] = '<tr>';
			$content[] = '<th scope=col id=c1>&nbsp;</th>';
			$content[] = '<th scope=col id=c2 class=col2>{LNG_BOARD_CAN_POST}</th>';
			$content[] = '<th scope=col id=c3>{LNG_CAN_VIEW}</th>';
			$content[] = '<th scope=col id=c4 class=col2>{LNG_CAN_REPLY}</th>';
			$content[] = '<th scope=col id=c5>{LNG_MODERATOR}</th>';
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
				if ($i != 1) {
					$bg = $bg == 'bg1' ? 'bg2' : 'bg1';
					$tr = '<tr class="'.$bg.' status'.$i.'">';
					$tr .= '<th>'.$item.'</th>';
					// can_post
					$tr .= '<td><label data-text="{LNG_BOARD_CAN_POST}" ><input type=checkbox id=category_can_post'.$i.' name=category_can_post[]'.(in_array($i, explode(',', $index['can_post'])) ? ' checked' : '').' value='.$i.' title="{LNG_BOARD_CAN_POST_COMMENT}"></label></td>';
					// can_view
					$tr .= '<td><label data-text="{LNG_CAN_VIEW}" ><input type=checkbox id=category_can_view'.$i.' name=category_can_view[]'.(in_array($i, explode(',', $index['can_view'])) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_VIEW_COMMENT}"></label></td>';
					// can_reply
					$tr .= '<td><label data-text="{LNG_CAN_REPLY}" ><input type=checkbox id=category_can_reply'.$i.' name=category_can_reply[]'.(in_array($i, explode(',', $index['can_reply'])) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_REPLY_COMMENT}"></label></td>';
					// moderator
					if ($i > 1) {
						$tr .= '<td><label data-text="{LNG_MODERATOR}" ><input type=checkbox id=category_moderator'.$i.' name=category_moderator[]'.(in_array($i, explode(',', $index['moderator'])) ? ' checked' : '').' value='.$i.' title="{LNG_BOARD_MODERATOR_COMMENT}"></label></td>';
					} else {
						$tr .= '<td></td>';
					}
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
			$content[] = '<input type=hidden id=write_id name=write_id value='.(int)$index['id'].'>';
			$content[] = '<input type=hidden id=module_id name=module_id value='.$index['module_id'].'>';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = 'inintWriteCategory("board");';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'board-categorywrite';
		} else {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
