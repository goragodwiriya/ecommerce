<?php
	// modules/index/admin_write_save.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer และ แอดมิน
	if (gcms::isReferer() && gcms::isAdmin()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$owner = strtolower($db->sql_trim_str($_POST['write_owner']));
			// โหลด admin_inint
			if (is_file(ROOT_PATH."modules/$owner/admin_inint.php")) {
				include ROOT_PATH."modules/$owner/admin_inint.php";
			}
			$id = (int)$_POST['write_id'];
			// ตรวจสอบค่าที่ส่ง
			$ret = array();
			$error = false;
			$input = false;
			$module_id = 0;
			$index_save = array();
			$detail_save = array();
			$module_save = array();
			if ($id > 0) {
				// หน้าที่แก้ไข
				$sql = "SELECT I.`id`,I.`language`,I.`module_id` FROM `".DB_INDEX."` AS I";
				$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language`=I.`language`";
				$sql .= " WHERE I.`id`='$id' LIMIT 1";
				$index = $db->customQuery($sql);
				$index = sizeof($index) == 0 ? false : $index[0];
			}
			if (($id > 0 && !$index) || !preg_match('/^[a-z]+$/', $owner) || !is_dir(ROOT_PATH."modules/$owner/")) {
				$ret['error'] = 'ACTION_ERROR';
			} else {
				// ค่าที่ส่งมา
				$language = $db->sql_trim_str($_POST['write_language']);
				$module = strtolower($db->sql_trim_str($_POST['write_module']));
				$detail_save['topic'] = $db->sql_trim_str($_POST['write_topic']);
				$keywords = gcms::getTags($_POST['write_keywords']);
				$detail_save['keywords'] = $db->sql_clean(gcms::cutstring(preg_replace('/[\'\"\r\n\s]{1,}/isu', ' ', ($keywords == '' ? gcms::getTags($_POST['write_topic']) : $keywords)), 149));
				$detail_save['detail'] = gcms::ckDetail($_POST['write_detail']);
				$description = trim($_POST['write_description']);
				$detail_save['description'] = $db->sql_trim_str(gcms::cutstring(gcms::html2txt($description == '' ? $_POST['write_detail'] : $description), 149));
				$index_save['published_date'] = $db->sql_trim_str($_POST['write_published_date']);
				$index_save['published'] = $_POST['write_published'] == '0' ? '0' : '1';
				// owner ที่สามารถใช้ซ้ำได้
				if ($owner == 'index' || isset($config[$owner]['description'])) {
					// ตรวจสอบชื่อโมดูล
					if ($module == '') {
						$ret['ret_write_module'] = 'MODULE_EMPTY';
						$input = !$error ? 'write_module' : $input;
						$error = !$error ? 'MODULE_EMPTY' : $error;
					} elseif (!preg_match('/^[a-z0-9]{1,}$/', $module)) {
						$ret['ret_write_module'] = 'EN_NUMBER_ONLY';
						$input = !$error ? 'write_module' : $input;
						$error = !$error ? 'EN_NUMBER_ONLY' : $error;
					} else {
						if (in_array($module, explode(',', MODULE_RESERVE))) {
							// ชื่อสงวน
							$ret['ret_write_module'] = 'MODULE_INCORRECT';
							$input = !$error ? 'write_module' : $input;
							$error = !$error ? 'MODULE_INCORRECT' : $error;
						} elseif (!(sizeof($allow_module) > 0 && in_array($module, $allow_module)) && (is_dir(ROOT_PATH."modules/$module/") || is_dir(ROOT_PATH."widgets/$module/") || is_dir(ROOT_PATH."$module/") || is_file(ROOT_PATH."$module.php"))) {
							// เป็นชื่อโฟลเดอร์หรือชื่อไฟล์
							$ret['ret_write_module'] = 'MODULE_INCORRECT';
							$input = !$error ? 'write_module' : $input;
							$error = !$error ? 'MODULE_INCORRECT' : $error;
						} else {
							// ค้นหาชื่อโมดูลซ้ำ
							$sql = "SELECT I.`language`,I.`module_id`";
							$sql .= " FROM `".DB_MODULES."` AS M";
							$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`module_id`=M.`id` AND I.`index`='1'";
							if ($id > 0) {
								$sql .= " AND I.`id`!='$id'";
							}
							$sql .= " WHERE M.`module`='$module'";
							foreach ($db->customQuery($sql) AS $item) {
								if ($language == '') {
									$ret['ret_write_language'] = 'MODULE_ALREADY_EXISTS';
									$input = !$error ? 'write_language' : $input;
									$error = !$error ? 'MODULE_ALREADY_EXISTS' : $error;
								} elseif ($item['language'] == '') {
									$ret['ret_write_language'] = 'MODULE_ALREADY_EXISTS';
									$input = !$error ? 'write_language' : $input;
									$error = !$error ? 'MODULE_ALREADY_EXISTS' : $error;
								} elseif ($item['language'] == $language) {
									$ret['ret_write_module'] = 'MODULE_ALREADY_EXISTS';
									$input = !$error ? 'write_module' : $input;
									$error = !$error ? 'MODULE_ALREADY_EXISTS' : $error;
								}
								$module_id = $item['module_id'];
							}
							if (!$error) {
								$ret['ret_write_language'] = '';
								$ret['ret_write_module'] = '';
								$module_save['module'] = $module;
							}
						}
					}
				}
				// topic
				$topic = strtolower(stripslashes($detail_save['topic']));
				if ($topic == '') {
					$ret['ret_write_topic'] = 'TITLE_EMPTY';
					$input = !$error ? 'write_topic' : $input;
					$error = !$error ? 'TITLE_EMPTY' : $error;
				} elseif (mb_strlen($topic) < 3) {
					$ret['ret_write_topic'] = 'TITLE_SHORT';
					$input = !$error ? 'write_topic' : $input;
					$error = !$error ? 'TITLE_SHORT' : $error;
				} else {
					// ค้นหาชื่อไตเติลซ้ำ
					$sql = "SELECT `id` FROM `".DB_INDEX_DETAIL."`";
					$sql .= " WHERE `topic`='".addslashes($detail_save['topic'])."' AND `language` IN ('$language','')";
					$sql .= " LIMIT 1";
					$search = $db->customQuery($sql);
					if (sizeof($search) > 0 && ($id == 0 || $id != $search[0]['id'])) {
						$ret['ret_write_topic'] = 'TITLE_EXISTS';
						$input = !$error ? false : $input;
						$error = !$error ? 'TITLE_EXISTS' : $error;
					} else {
						$ret['ret_write_topic'] = '';
					}
				}
				if (!$error && $id == 0) {
					// config ของโมดูล (default) บันทึกลง db
					$cfg = array();
					if (is_file(ROOT_PATH."modules/$owner/default.config.php")) {
						include (ROOT_PATH."modules/$owner/default.config.php");
					}
					if (is_array($default[$owner])) {
						foreach ($default[$owner] AS $key => $value) {
							$cfg[] = "$key=$value";
							if ($key == 'default_icon') {
								$info = gcms::imageInfo(ROOT_PATH.$value);
								$cfg[] = "icon_w=$info[width]";
								$cfg[] = "icon_h=$info[height]";
							}
						}
						if (sizeof($cfg) > 0) {
							$module_save['config'] = implode("\n", $cfg);
						}
					}
					// config หลัก (บันทึกลง config.php)
					if (is_array($newconfig[$owner])) {
						// โหลด config ใหม่
						$config = array();
						if (is_file(CONFIG)) {
							include CONFIG;
						}
						foreach ($newconfig[$owner] AS $key => $value) {
							if (!isset($config[$key])) {
								$config[$key] = $value;
							}
						}
						// บันทึก config.php
						if (!gcms::saveConfig(CONFIG, $config)) {
							$error = 'DO_NOT_SAVE';
						}
					}
				}
				if (!$error) {
					$index_save['ip'] = gcms::getip();
					$index_save['last_update'] = $mmktime;
					$index_save['member_id'] = $_SESSION['login']['id'];
					$index_save['language'] = $language;
					if ($id == 0) {
						// รายการใหม่
						if ($module_id == 0) {
							// โมดูลใหม่
							$module_save['owner'] = $owner;
							$module_id = $db->add(DB_MODULES, $module_save);
						}
						// index
						$index_save['create_date'] = $mmktime;
						$index_save['index'] = 1;
						$index_save['module_id'] = $module_id;
						$id = $db->add(DB_INDEX, $index_save);
					} else {
						// แก้ไข
						$module_id = $index['module_id'];
						$db->edit(DB_INDEX, $id, $index_save);
						$db->edit(DB_MODULES, $module_id, $module_save);
						$db->query("DELETE FROM `".DB_INDEX_DETAIL."` WHERE `id`='$id' AND `module_id`='$module_id' AND `language`='$index[language]'");
					}
					// detail
					$detail_save['id'] = $id;
					$detail_save['module_id'] = $module_id;
					$detail_save['language'] = $language;
					$db->add(DB_INDEX_DETAIL, $detail_save);
					// ส่งค่ากลับ
					$ret['error'] = 'SAVE_COMPLETE';
					$ret['location'] = 'back';
				} else {
					// คืนค่า input ตัวแรกที่ error
					if ($input) {
						$ret['input'] = $input;
					}
					$ret['error'] = $error;
				}
			}
		}
	} else {
		$ret['error'] = 'ACTION_ERROR';
	}
	// คืนค่าเป็น JSON
	echo gcms::array2json($ret);
