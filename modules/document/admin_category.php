<?php
	// modules/document/admin_category.php
	if (MAIN_INIT == 'admin' && $isMember) {
		// ตรวจสอบโมดูลที่เรียก
		$sql = "SELECT `id`,`module`,`config` FROM `".DB_MODULES."` WHERE `id`=".(int)$_GET['id']." AND `owner`='document' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 1) {
			$index = $index[0];
			// อ่าน config ของโมดูล
			gcms::r2config($index['config'], $index);
			// ตรวจสอบสถานะที่สามารถเข้าหน้านี้ได้
			if (!gcms::canConfig(explode(',', $index['can_config']))) {
				$index = false;
			}
		} else {
			$index = false;
		}
		if ($index) {
			// title
			$m = ucwords($index['module']);
			$title = "$lng[LNG_CREATE] - $lng[LNG_EDIT] $lng[LNG_CATEGORY]";
			$a = array();
			$a[] = '<span class=icon-documents>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=document-config&id='.$index['id'].'}">'.$m.'</a>';
			$a[] = '{LNG_CATEGORY}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-category>'.$title.'</h1></header>';
			// หมวดหมู่
			$content[] = '<table id=tbl_category class="tbl_list fullwidth">';
			$content[] = '<caption>{LNG_CATEGORY_LIST_COMMENT}</caption>';
			$content[] = '<thead>';
			$content[] = '<tr>';
			$content[] = '<th id=c0 scope=col>{LNG_CATEGORIES}</th>';
			$content[] = '<th id=c1 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
			$content[] = '<th id=c2 scope=col class=center>{LNG_LANGUAGE}</th>';
			$content[] = '<th id=c3 scope=col class="center tablet">{LNG_ICON}</th>';
			$content[] = '<th id=c4 scope=col class=center>{LNG_ID}</th>';
			$content[] = '<th id=c5 scope=col class="center tablet">{LNG_CAN_REPLY}</th>';
			$content[] = '<th id=c6 scope=col class="center tablet">{LNG_PUBLISHED}</th>';
			$content[] = '<th id=c7 scope=col class=mobile>{LNG_DESCRIPTION}</th>';
			$content[] = '<th id=c8 scope=col class="center tablet">{LNG_CATEGORY_COUNT}</th>';
			$content[] = '<th id=c9 scope=col></th>';
			$content[] = '</tr>';
			$content[] = '</thead>';
			$content[] = '<tbody>';
			// เรียกหมวดหมู่ทั้งหมด
			$categories = array();
			$sql = "SELECT * FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' ORDER BY `category_id`";
			foreach ($db->customQuery($sql) AS $item) {
				$topics = gcms::ser2Array($item['topic']);
				$details = gcms::ser2Array($item['detail']);
				$icons = gcms::ser2Array($item['icon']);
				foreach ($config['languages'] AS $l) {
					$save = array();
					$save['topic'] = $topics[$l] == '' && $l == LANGUAGE ? $topics[''] : $topics[$l];
					$save['detail'] = $details[$l] == '' && $l == LANGUAGE ? $details[''] : $details[$l];
					$save['icon'] = $icons[$l] == '' && $l == LANGUAGE ? $icons[''] : $icons[$l];
					if ($save['topic'] != '' || $save['detail'] != '' || $save['icon'] != '') {
						$save['category_id'] = $item['category_id'];
						gcms::r2config($item['config'], $save);
						$save['c1'] = $item['c1'];
						$categories[$item['id']][$l] = $save;
					}
				}
				if (sizeof($categories[$item['id']]) == 1) {
					foreach ($categories[$item['id']] AS $k => $v) {
						$categories[$item['id']][''] = $v;
						unset($categories[$item['id']][$k]);
					}
				}
			}
			foreach ($categories AS $id => $items) {
				$c = sizeof($items);
				$i = 0;
				foreach ($items AS $l => $item) {
					$bg = $i == 0 ? ($bg == 'bg1' ? 'bg2' : 'bg1') : $bg;
					$tr = '<tr id=L_'.$id.' class='.$bg.'>';
					$tr .= '<th headers=c0 id=b'.$id.' class=topic scope=row>'.$item['topic'].'</th>';
					if ($i == 0) {
						$tr .= '<td headers="b'.$id.' c1" class=check-column rowspan='.$c.'><a id=check_'.$id.' class=icon-uncheck></a></td>';
					}
					$tr .= '<td headers="b'.$id.' c2" class=menu><img src="'.($l == '' ? "../skin/img/blank.gif" : DATA_URL.'language/'.$l.'.gif').'" alt="'.$l.'"></td>';
					$icon = is_file(DATA_PATH.'document/'.$item['icon']) ? DATA_URL.'document/'.$item['icon'] : WEB_URL.'/'.$index['default_icon'];
					$tr .= '<td headers="b'.$id.' c3" class="center tablet"><img src="'.$icon.'" alt=icon></td>';
					if ($i == 0) {
						$tr .= '<td headers="b'.$id.' c4" rowspan='.$c.' class=center><label><input type=text class=number size=5 id=categoryid_'.$index['id'].'_'.$id.' value="'.$item['category_id'].'" title="{LNG_EDIT}"></label></td>';
						$tr .= '<td headers="b'.$id.' c5" class="menu tablet" rowspan='.$c.'><span class=reply-'.$item['can_reply'].' title="'.$lng['LNG_CAN_REPLIES'][$item['can_reply']].'"></span></td>';
						$tr .= '<td headers="b'.$id.' c6" class="menu tablet" rowspan='.$c.'><span class=published-'.$item['published'].' title="'.$lng['LNG_PUBLISHEDS'][$item['published']].'"></span></td>';
					}
					$tr .= '<td headers="b'.$id.' c7" class="detail mobile">'.$item['detail'].'</td>';
					if ($i == 0) {
						$tr .= '<td headers="b'.$id.' c8" class="visited tablet" rowspan='.$c.'>'.$item['c1'].'</td>';
						$tr .= '<td headers="b'.$id.' c9" class=menu rowspan='.$c.'><a href="index.php?module=document-categorywrite&amp;id='.$index['id'].'&amp;cat='.$id.'&amp;src=document-category" title="{LNG_EDIT}" class=icon-edit></a></td>';
					}
					$tr .= '</tr>';
					$content[] = $tr;
					$i++;
				}
			}
			$content[] = '</tbody>';
			$content[] = '<tfoot>';
			$content[] = '<tr>';
			$content[] = '<td headers=c0></td>';
			$content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
			$content[] = '<td headers=c2 colspan=8></td>';
			$content[] = '</tr>';
			$content[] = '</tfoot>';
			$content[] = '</table>';
			$content[] = '<div class=table_nav>';
			// sel action
			$content[] = '<select id=sel_action><option value=delete_'.$index['id'].'>{LNG_DELETE}</option></select>';
			$content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action>{LNG_SELECT_ACTION}</label>';
			$content[] = '<a class="button add" href="index.php?module=document-categorywrite&amp;src=document-category&amp;id='.$index['id'].'"><span class=icon-add>{LNG_ADD_NEW} {LNG_CATEGORY}</span></a>';
			$content[] = '</div>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = 'inintListCategory();';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'document-category';
		} else {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
