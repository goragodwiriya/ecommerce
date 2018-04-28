<?php
	// widgets/rss/admin_seup_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, admin
	if (gcms::isReferer() && gcms::isAdmin()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			$ret = array();
			$save = array();
			// ค่าที่ส่งมา
			$topic = $db->sql_trim_str($_POST['rss_topic']);
			$cols = max(1, (int)$_POST['rss_cols']);
			$rows = max(1, (int)$_POST['rss_rows']);
			$url = trim($_POST['rss_url']);
			$index = (int)$_POST['rss_index'];
			$index = $index == 0 ? '' : $index;
			$id = (int)$_POST['rss_id'];
			if ($url == '') {
				$ret['error'] = 'DATA_EMPTY';
				$ret['input'] = 'rss_url';
			} elseif ($topic == '') {
				$ret['error'] = 'DATA_EMPTY';
				$ret['input'] = 'rss_topic';
			} else {
				$ret['ret_rss_topic'] = '';
				$ret['ret_rss_url'] = '';
				// โหลด config ใหม่
				$config = array();
				if (is_file(CONFIG)) {
					include CONFIG;
				}
				if ($id == 0 || sizeof($config['rss_tabs']) == 0) {
					// ตรวจสอบ id ของรายการ
					$id = sizeof($config['rss_tabs']) + 1;
					$add = true;
				}
				$config['rss_tabs'][$id] = array($url, $topic, $index, $rows, $cols);
				if ($add) {
					$tr = '<tr id="L_'.$id.'" class="sort">';
					$tr .= '<th headers="c1" id="r'.$id.'" scope="row"><a href="'.$url.'" target="_blank">'.$url.'</a></th>';
					$tr .= '<td headers="r'.$id.' c2" class="check-column"><a id="check_'.$id.'" class="uncheck"></a></td>';
					$tr .= '<td headers="r'.$id.' c3">'.$topic.'</td>';
					$tr .= '<td headers="r'.$id.' c4" class="move mobile"><a id="move_'.$id.'" title="'.$lng['LNG_DRAG_MOVE'].'">&nbsp;</a></td>';
					$tr .= '<td headers="r'.$id.' c5" class="center">'.$index.'</td>';
					$tr .= '<td headers="r'.$id.' c6" class="center mobile">'.$rows.' * '.$cols.'</td>';
					$tr .= '<td headers="r'.$id.' c7" class="menu"><a id="edit_'.$id.'" href="'.WEB_URL.'/admin/index.php?module=rss-setup&amp;id='.$id.'" class="edit">&nbsp;</a></td>';
					$tr .= '</tr>';
					$ret['content'] = rawurlencode($tr);
				} else {
					$ret['url'] = rawurlencode($url);
					$ret['topic'] = rawurlencode($topic);
					$ret['index'] = rawurlencode($index);
					$ret['display'] = rawurlencode("$rows * $cols");
				}
				$ret['id'] = $id;
				// บันทึก config.php
				if (gcms::saveconfig(CONFIG, $config)) {
					$ret['error'] = 'SAVE_COMPLETE';
				} else {
					$ret['error'] = 'DO_NOT_SAVE';
				}
			}
		}
		// คืนค่า JSON
		echo gcms::array2json($ret);
	}
