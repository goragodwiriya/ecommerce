<?php
	@set_time_limit(0);
	// modules/download/admin_setup_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer และ แอดมิน
	if (gcms::isReferer() && gcms::canConfig($config['download_can_upload'])) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$id = (int)$_POST['download_id'];
			$original_name = $db->sql_trim_str($_POST['download_name']);
			$name = str_replace(array('"', "'", '|', ':', '\\', '/', '*', '?', '&', '[', ']'), '', $original_name);
			$save['description'] = $db->sql_trim_str($_POST['download_description']);
			// ไฟล์อัปโหลด
			$file_upload = $_FILES['download_file'];
			$thumbnail = $_FILES['download_thumbnail'];
			if ($id > 0) {
				// ตรวจสอบไฟล์
				$file = $db->getRec(DB_DOWNLOAD, $_POST['download_id']);
			}
			if ($id > 0 && !$file) {
				$ret['error'] = 'FILE_NOT_FOUND';
			} elseif ($id == 0 && $file_upload['tmp_name'] == '') {
				$ret['error'] = 'DOWNLOAD_FILE_EMPTY';
				$ret['ret_download_file'] = 'DOWNLOAD_FILE_EMPTY';
			} elseif ($id > 0 && $file_upload['tmp_name'] == '' && $name == '') {
				$ret['error'] = 'DOWNLOAD_NAME_EMPTY';
				$ret['ret_download_name'] = 'DOWNLOAD_NAME_EMPTY';
			} elseif ($name != $original_name) {
				$ret['error'] = 'DOWNLOAD_NAME_EMPTY';
				$ret['ret_download_name'] = 'DOWNLOAD_INVALID_NAME';
			} else {
				$error = false;
				if ($file_upload['tmp_name'] != '' || $thumbnail['tmp_name'] != '') {
					// อ่าน id สุดท้ายเก็บไว้ ถ้ามีการส่งไฟล์มาด้วย
					// และเป็นดาวน์โหลดใหม่
					if ($id == 0) {
						$last_id = $db->lastId(DB_DOWNLOAD);
					} else {
						$last_id = $id;
					}
				}
				// ชื่อของไฟล์
				$name = $name == '' && $file_upload['tmp_name'] != '' ? $file_upload['name'] : $name;
				if ($file_upload['tmp_name'] != '') {
					$ext = strtolower(end(explode('.', $file_upload['name'])));
				}
				if (preg_match('/^(.*)\.([a-zA-Z0-1]{2,4})$/u', $name, $match)) {
					$save['name'] = $match[1];
					$save['ext'] = $ext != '' ? $ext : $match[2];
				} else {
					$save['name'] = $name;
					$save['ext'] = $id == 0 || $ext != '' ? $ext : $file['ext'];
				}
				if (!$error && $file_upload['tmp_name'] != '') {
					// อัปโหลดไฟล์
					if (!in_array($ext, explode(',', $config['download_file_typies']))) {
						$ret['error'] = 'INVALID_FILE_TYPE';
						$ret['ret_download_file'] = 'INVALID_FILE_TYPE';
						$error = true;
					} elseif ($file_upload['size'] > $config['download_upload_size'] * 1024) {
						$ret['error'] = 'FILE_TOO_BIG';
						$ret['ret_download_file'] = 'FILE_TOO_BIG';
						$error = true;
					} else {
						if (!@copy($file_upload['tmp_name'], DATA_PATH."download/file-$last_id.$ext")) {
							$ret['error'] = 'DO_NOT_UPLOAD';
							$ret['ret_download_file'] = 'DO_NOT_UPLOAD';
							$error = true;
						} else {
							$save['file'] = "file-$last_id.$ext";
							$save['size'] = $file_upload['size'];
						}
					}
				}
				if (!$error && $thumbnail['tmp_name'] != '') {
					// ตรวจสอบไฟล์อัปโหลด (thumbnail)
					$info = gcms::isValidImage($config['download_thumb_type'], $thumbnail);
					if (!$info) {
						$ret['error'] = 'INVALID_FILE_TYPE';
						$ret['ret_download_thumbnail'] = 'INVALID_FILE_TYPE';
						$error = true;
					} else {
						$thumb = "thumb-$last_id";
						if ($info['width'] <= $config['download_thumb_width'] && $info['height'] <= $config['download_thumb_width']) {
							// รูปภาพต้นฉบับ เท่ากับ หรือ เล็กกว่าที่กำหนดให้อัปโหลดเลย
							if (!@copy($thumbnail['tmp_name'], DATA_PATH."download/$thumb.$info[ext]")) {
								$ret['error'] = 'DO_NOT_UPLOAD';
								$ret['ret_download_thumbnail'] = 'DO_NOT_UPLOAD';
								$error = true;
							} else {
								$save['thumb'] = "$thumb.$info[ext]";
								$save['thumb_w'] = $info['width'];
								$save['thumb_h'] = $info['height'];
							}
						} elseif (!gcms::cropImage($thumbnail['tmp_name'], DATA_PATH."download/$thumb.jpg", $info, $config['download_thumb_width'], $config['download_thumb_width'])) {
							$ret['error'] = 'DO_NOT_UPLOAD';
							$error = true;
						} else {
							$save['thumb'] = "$thumb.jpg";
							$save['thumb_w'] = $config['download_thumb_width'];
							$save['thumb_h'] = $config['download_thumb_width'];
						}
					}
				}
				if (!$error) {
					$save['count'] = $id > 0 ? $file['count'] : 0;
					$save['last_update'] = $mmktime;
					// thumb
					if ($save['thumb'] != '') {
						$icon = DATA_URL.'download/'.$save['thumb'];
					} elseif ($id > 0 && $file['thumb'] != '') {
						$icon = DATA_URL.'download/'.$file['thumb'];
					}
					$icon = $icon == '' ? WEB_URL.'/'.$config['download_thumbnail'] : $icon;
					if ($id == 0) {
						// ใหม่
						$id = $db->add(DB_DOWNLOAD, $save);
						$ret['action'] = 'new';
					} else {
						// แก้ไข
						$db->edit(DB_DOWNLOAD, $id, $save);
						$ret['action'] = 'edit';
					}
					// id
					$ret['id'] = $id;
					// รายการที่ส่งกลับ
					$tr = '<tr id="M_'.$id.'">';
					$tr .= '<th headers="c0" id="r'.$id.'" scope="row"><a id="edit-'.$id.'" href="'.WEB_URL.'/admin/index.php?module=download-setup" title="'.$lng['LNG_EDIT'].'">'.$save['name'].'.'.$save['ext'].'</a></th>';
					$tr .= '<td headers="r'.$id.' c0" class="menu"><img src="'.$icon.'" alt="thumbnail" width="16" height="16"></td>';
					$tr .= '<td headers="r'.$id.' c1" class="check-column"><a id="check_'.$id.'" class="uncheck"></a></td>';
					$tr .= '<td headers="r'.$id.' c2" class="no">{WIDGET_DOWNLOAD_'.$id.'}</td>';
					$tr .= '<td headers="r'.$id.' c3">'.gcms::cutstring($save['description'], 50).'</td>';
					$tr .= '<td headers="r'.$id.' c4" class="size">'.gcms::formatFileSize($save['size']).'</td>';
					$tr .= '<td headers="r'.$id.' c5" class="date">'.gcms::mktime2date($save['last_update']).'</td>';
					$tr .= '<td headers="r'.$id.' c6" class="visited">'.$save['count'].'</td>';
					$icon = "modules/download/icons/$save[ext].png";
					$icon = WEB_URL.(is_file(ROOT_PATH.$icon) ? "/$icon" : "/modules/download/icons/file.png");
					$tr .= '<td headers="r'.$id.' c7" class="menu">';
					$tr .= '<a href="'.WEB_URL.'/modules/download/admin_download.php?file='.$save['file'].'&amp;size='.$save['size'].'" target="_blank" title="'.$lng['LNG_CLICK_TO'].' '.$lng['LNG_DOWNLOAD'].'"><img src="'.$icon.'" alt="'.$save['ext'].'" width="16" height="16"></a>';
					$tr .= '</td>';
					$tr .= '</tr>';
					$ret['content'] = rawurlencode($tr);
				}
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
