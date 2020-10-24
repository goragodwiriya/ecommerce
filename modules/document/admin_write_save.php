<?php
	// modules/document/admin_write_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer และ สมาชิก
	if (gcms::isReferer() && gcms::isMember()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			$input = false;
			$error = false;
			$tab = false;
			// details
			$details = array();
			foreach ($config['languages'] AS $value) {
				$topic = $db->sql_trim_str($_POST["write_topic_$value"]);
				$alias = gcms::aliasName($_POST["write_topic_$value"]);
				$relate = $db->sql_trim($_POST["write_relate_$value"]);
				$keywords = gcms::getTags($_POST["write_keywords_$value"]);
				$description = $db->sql_trim($_POST["write_description_$value"]);
				if ($topic != '') {
					$save = array();
					$save['topic'] = $topic;
					$save['keywords'] = $db->sql_clean(gcms::cutstring(preg_replace('/[\'\"\r\n\s]{1,}/isu', ' ', ($keywords == '' ? gcms::getTags($_POST["write_topic_$value"]) : $keywords)), 149));
					$save['description'] = gcms::cutstring(gcms::html2txt($description == '' ? $_POST["write_detail_$value"] : $description), 149);
					$save['detail'] = gcms::ckDetail($_POST["write_detail_$value"]);
					$save['language'] = $value;
					$save['relate'] = $relate == '' ? $save['keywords'] : $relate;
					$details[$value] = $save;
					$alias_topic = $alias_topic == '' ? $alias : $alias_topic;
				}
			}
			$save = array();
			$save['alias'] = gcms::aliasName($_POST['write_alias']);
			// id ที่แก้ไข
			$id = (int)$_POST['write_id'];
			$module_id = (int)$_POST['module_id'];
			if ($id > 0) {
				// ตรวจสอบโมดูล หรือ เรื่องที่เลือก (แก้ไข)
				$sql = "SELECT I.`id`,I.`module_id`,M.`module`,M.`config`,I.`picture`,I.`member_id`";
				$sql .= " FROM `".DB_MODULES."` AS M";
				$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`module_id`=M.`id` AND I.`id`='$id' AND I.`index`='0'";
				$sql .= " WHERE M.`id`='$module_id' AND M.`owner`='document'";
				$sql .= " LIMIT 1";
			} else {
				// ตรวจสอบโมดูล (ใหม่)
				$sql = "SELECT `id` AS `module_id`,`module`,`config`";
				$sql .= ",(SELECT MAX(`id`)+1 FROM `".DB_INDEX."` WHERE `module_id`='$module_id') AS `id`";
				$sql .= " FROM `".DB_MODULES."`";
				$sql .= " WHERE `id`='$module_id'";
				$sql .= " LIMIT 1";
			}
			$index = $db->customQuery($sql);
			if (sizeof($index) == 0) {
				$ret['error'] = 'ACTION_ERROR';
			} else {
				$index = $index[0];
				// config
				gcms::r2config($index['config'], $index);
				// login
				$login = $_SESSION['login'];
				if ($id == 0) {
					// เขียนใหม่ตรวสอบกับ can_write
					$canWrite = in_array($login['status'], explode(',', $index['can_write']));
				} else {
					// แก้ไข ตรวจสอบเจ้าของหรือ ผู้ดูแล
					$canWrite = ($index['member_id'] == $login['id'] || in_array($login['status'], explode(',', $index['moderator'])));
				}
				if ($canWrite) {
					$save['can_reply'] = (int)(isset($_POST['write_can_reply']) ? $_POST['write_can_reply'] : $index['can_reply'] != '');
					// ตรวจสอบข้อมูลที่กรอก
					if (sizeof($details) == 0) {
						$item = $config['languages'][0];
						$ret["ret_write_topic_$item"] = 'TOPIC_EMPTY';
						$error = !$error ? 'TOPIC_EMPTY' : $error;
						$input = !$input ? "write_topic_$item" : $input;
						$tab = !$tab ? "detail_$item" : $tab;
					} else {
						foreach ($details AS $item => $values) {
							if ($values['topic'] == '') {
								$ret["ret_write_topic_$item"] = 'TOPIC_EMPTY';
								$error = !$error ? 'TOPIC_EMPTY' : $error;
								$input = !$input ? "write_topic_$item" : $input;
								$tab = !$tab ? "detail_$item" : $tab;
							} elseif (mb_strlen($values['topic']) < 3) {
								$ret["ret_write_topic_$item"] = 'TOPIC_SHORT';
								$error = !$error ? 'TOPIC_SHORT' : $error;
								$input = !$input ? "write_topic_$item" : $input;
								$tab = !$tab ? "detail_$item" : $tab;
							} else {
								$ret["ret_write_topic_$item"] = '';
							}
						}
					}
					// มีข้อมูลมาภาษาเดียวให้แสดงในทุกภาษา
					if (sizeof($details) == 1) {
						foreach ($details AS $i => $item) {
							$details[$i]['language'] = '';
						}
					}
					// alias
					if ($save['alias'] == '') {
						$save['alias'] = $alias_topic;
					}
					if (in_array($save['alias'], explode(',', MODULE_RESERVE))) {
						// ชื่อสงวน
						$ret['ret_write_alias'] = 'MODULE_INCORRECT';
						$input = !$input ? 'write_alias' : $input;
						$error = !$error ? 'MODULE_INCORRECT' : $error;
						$tab = !$tab ? 'options' : $tab;
					} elseif (is_dir(ROOT_PATH."modules/$save[alias]/") || is_dir(ROOT_PATH."widgets/$save[alias]/")) {
						// เป็นชื่อโฟลเดอร์
						$ret['ret_write_alias'] = 'MODULE_INCORRECT';
						$input = !$input ? 'write_alias' : $input;
						$error = !$error ? 'MODULE_INCORRECT' : $error;
						$tab = !$tab ? 'options' : $tab;
					} else {
						// ค้นหาชื่อเรื่องซ้ำ
						$sql = "SELECT `id` FROM `".DB_INDEX."`";
						$sql .= " WHERE `alias`='$save[alias]' AND `language` IN ('".LANGUAGE."','') AND `index`='0'";
						$sql .= " LIMIT 1";
						$search = $db->customQuery($sql);
						if (sizeof($search) > 0 && ($id == 0 || $id != $search[0]['id'])) {
							$ret['ret_write_alias'] = 'ALIAS_EXISTS';
							$input = !$input ? 'write_alias' : $input;
							$error = !$error ? 'ALIAS_EXISTS' : $error;
							$tab = !$tab ? 'options' : $tab;
						} else {
							$ret['ret_write_alias'] = '';
						}
					}
					// ไฟล์อัปโหลด
					if (!$error && $index['img_typies'] != '') {
						$img_typies = explode(',', $index['img_typies']);
						// path เก็บรูป
						$dir = DATA_PATH."document";
						// อัปโหลด
						foreach ($_FILES AS $key => $file) {
							if (!$error && $file['tmp_name'] != '') {
								// ตรวจสอบไฟล์อัปโหลด
								$info = gcms::isValidImage($img_typies, $file);
								if (!$info) {
									// ชนิดไฟล์อัปโหลด
									$ret["ret_$key"] = 'INVALID_FILE_TYPE';
									$input = !$input ? $key : $input;
									$error = !$error ? 'INVALID_FILE_TYPE' : $error;
								} else {
									$ret["ret_$key"] = '';
									// อัปโหลด
									$k = str_replace('write_', '', $key);
									$save[$k] = "$k-$index[module_id]-$index[id].$info[ext]";
									if (!gcms::cropImage($file['tmp_name'], "$dir/$save[$k]", $info, $index['icon_width'], $index['icon_height'])) {
										$ret["ret_$key"] = 'DO_NOT_UPLOAD';
										$input = !$error ? $key : $input;
										$error = !$error ? 'DO_NOT_UPLOAD' : $error;
									} else {
										$ret["ret_$key"] = '';
										$save[$k.'W'] = $index['icon_width'];
										$save[$k.'H'] = $index['icon_height'];
										// ลบรูปภาพเดิม
										if ($index[$k] != $save[$k]) {
											@unlink(DATA_PATH."document/$index[$k]");
										}
									}
								}
							}
						}
					}
					if (!$error) {
						// บันทึก
						$save['create_date'] = $db->sql_datetime2mktime("$_POST[write_create_date] $_POST[write_create_hour]:$_POST[write_create_minute]:00");
						$save['last_update'] = $mmktime;
						$save['index'] = 0;
						$save['category_id'] = (int)$_POST['write_category'];
						$save['ip'] = gcms::getip();
						$save['published'] = $_POST['write_published'] == '1' ? '1' : '0';
						$save['published_date'] = $db->sql_trim_str($_POST['write_published_date']);
						if ($id == 0) {
							// ใหม่
							$save['module_id'] = $index['module_id'];
							$save['member_id'] = $login['id'];
							$id = $db->add(DB_INDEX, $save);
						} else {
							// แก้ไข
							$db->edit(DB_INDEX, $id, $save);
						}
						// details
						$db->query("DELETE FROM `".DB_INDEX_DETAIL."` WHERE `id`='$id' AND `module_id`='$index[module_id]'");
						foreach ($details AS $save1) {
							$save1['module_id'] = $index['module_id'];
							$save1['id'] = $id;
							$db->add(DB_INDEX_DETAIL, $save1);
						}
						// อัปเดตหมวดหมู่
						if ($save['category_id'] > 0) {
							// อัปเดตจำนวนเรื่อง และ ความคิดเห็น ในหมวด
							$sql1 = "SELECT COUNT(*) FROM `".DB_INDEX."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]' AND `index`='0'";
							$sql2 = "SELECT `id` FROM `".DB_INDEX."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]' AND `index`='0'";
							$sql2 = "SELECT COUNT(*) FROM `".DB_COMMENT."` WHERE `index_id` IN ($sql2) AND `module_id`='$index[module_id]'";
							$sql = "UPDATE `".DB_CATEGORY."` AS C SET C.`c1`=($sql1),C.`c2`=($sql2) WHERE C.`module_id`='$index[module_id]'";
							$db->query($sql);
						}
						// return
						$ret['error'] = 'SAVE_COMPLETE';
						$ret['location'] = 'back';
					} else {
						$ret['error'] = $error;
						if ($input) {
							$ret['input'] = $input;
						}
						if ($tab) {
							$ret['tab'] = $tab;
						}
					}
				} else {
					// ไม่สามารถเขียนหรือแก้ไขได้
					$ret['error'] = 'NOT_ALLOWED';
				}
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
