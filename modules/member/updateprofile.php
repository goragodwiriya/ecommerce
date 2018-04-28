<?php
	// modules/member/updateprofile.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer, member
	if (gcms::isReferer() && gcms::isMember()) {
		$ret = array();
		$save = array();
		$error = false;
		// แก้ไขข้อมูลสมาชิก
		$sql = "SELECT *,(SELECT `name` FROM `".DB_PROVINCE."` WHERE `id`='".(int)$_POST['register_provinceID']."' LIMIT 1) AS `province`";
		$sql .= " FROM `".DB_USER."` WHERE `id`=".(int)$_SESSION['login']['id']." LIMIT 1";
		$user = $db->customQuery($sql);
		if (sizeof($user) == 0 || $user[0]['id'] != (int)$_POST['register_id']) {
			$ret['error'] = 'NOT_LOGIN';
		} else {
			$user = $user[0];
			// password
			if ($user['fb'] == 0 && isset($_POST['register_password'])) {
				$password = $db->sql_trim_str($_POST['register_password']);
				if ($password != '') {
					$repassword = $db->sql_trim_str($_POST['register_repassword']);
					if (mb_strlen($password) < 4) {
						$ret['ret_register_password'] = 'REGISTER_PASSWORD_SHORT';
						$input = !$input ? 'register_password' : $input;
						$error = !$error ? 'REGISTER_PASSWORD_SHORT' : $error;
					} elseif ($repassword == '') {
						$ret['ret_register_repassword'] = 'REPASSWORD_EMPTY';
						$input = !$input ? 'register_repassword' : $input;
						$error = !$error ? 'REPASSWORD_EMPTY' : $error;
					} elseif ($repassword != $password) {
						$ret['ret_register_repassword'] = 'REPASSWORD_INCORRECT';
						$input = !$input ? 'register_repassword' : $input;
						$error = !$error ? 'REPASSWORD_INCORRECT' : $error;
					} else {
						// password ใหม่ถูกต้อง
						$save['password'] = md5($password.$user['email']);
						// เปลี่ยน password ที่ login ใหม่
						$_SESSION['login']['password'] = $password;
						$ret['ret_register_password'] = '';
						$ret['ret_register_repassword'] = '';
					}
				}
			}
			// displayname
			if (isset($_POST['register_displayname'])) {
				$save['displayname'] = $db->sql_trim_str($_POST['register_displayname']);
				if (mb_strlen($save['displayname']) < 2) {
					$ret['ret_register_displayname'] = 'REGISTER_DISPLAYNAME_SHORT';
					$input = !$input ? 'register_displayname' : $input;
					$error = !$error ? 'REGISTER_DISPLAYNAME_SHORT' : $error;
				} elseif (in_array($save['displayname'], $config['member_reserv'])) {
					$ret['ret_register_displayname'] = 'REGISTER_RESERV_USER';
					$input = !$input ? 'register_displayname' : $input;
					$error = !$error ? 'REGISTER_RESERV_USER' : $error;
				} else {
					// ตรวจสอบ displayname
					$sql = "SELECT `id` FROM `".DB_USER."` WHERE `displayname`='$save[displayname]' LIMIT 1";
					$search = $db->customQuery($sql);
					if (sizeof($search) == 1 && $user['id'] != $search[0]['id']) {
						$ret['ret_register_displayname'] = 'NAME_EXISTS';
						$input = !$input ? 'register_displayname' : $input;
						$error = !$error ? 'NAME_EXISTS' : $error;
					} else {
						$ret['ret_register_displayname'] = '';
					}
				}
			}
			// website
			if (isset($_POST['register_website'])) {
				$save['website'] = trim($_POST['register_website']);
				$save['website'] = str_replace(array('http://', 'https://', 'ftp://'), array('', '', ''), $save['website']);
				$patt = '!^(\.?([a-z0-9-]+))+\.[a-z]{2,6}(:[0-9]{1,5})?(/[a-zA-Z0-9.,;\?|\'+&%\$#=~_-]+)*$!i';
				if ($save['website'] != '' && !preg_match($patt, $save['website'])) {
					$ret['ret_register_website'] = 'REGISTER_INVALID_WEBSITE';
					$input = !$input ? 'register_website' : $input;
					$error = !$error ? 'REGISTER_INVALID_WEBSITE' : $error;
				} else {
					$ret['ret_register_website'] = '';
				}
				// subscrib
				$save['subscrib'] = (int)$_POST['register_subscrib'];
			}
			// ตรวจสอบรูปภาพอัปโหลดสมาชิก
			$register_usericon = $_FILES['register_usericon'];
			if ($register_usericon['tmp_name'] != '') {
				// ตรวจสอบไฟล์อัปโหลด
				$info = gcms::isValidImage($config['user_icon_typies'], $register_usericon);
				if (!$info) {
					$ret['ret_register_usericon'] = 'INVALID_FILE_TYPE';
					$input = !$input ? 'register_usericon' : $input;
					$error = !$error ? 'INVALID_FILE_TYPE' : $error;
				} else {
					if ($user['icon'] != '') {
						$ftp->unlink(USERICON_FULLPATH.$user['icon']);
					}
					// สร้างรูป thumbnail
					if ($info['width'] == $config['user_icon_w'] && $info['height'] == $config['user_icon_h']) {
						$save['icon'] = "$user[id].$info[ext]";
						if (!$ftp->move_uploaded_file($register_usericon['tmp_name'], USERICON_FULLPATH.$save['icon'])) {
							$ret['ret_register_usericon'] = 'DO_NOT_UPLOAD';
							$input = !$input ? 'register_usericon' : $input;
							$error = !$error ? 'DO_NOT_UPLOAD' : $error;
						}
					} else {
						// ปรับภาพตามขนาดที่กำหนด
						$save['icon'] = "$user[id].jpg";
						if (!gcms::cropImage($register_usericon['tmp_name'], USERICON_FULLPATH.$save['icon'], $info, $config['user_icon_w'], $config['user_icon_h'])) {
							$ret['ret_register_usericon'] = 'DO_NOT_UPLOAD';
							$input = !$input ? 'register_usericon' : $input;
							$error = !$error ? 'DO_NOT_UPLOAD' : $error;
						}
					}
					if (!$error) {
						// คืนค่า url ของรูปใหม่
						$icon = rawurlencode(WEB_URL."/modules/member/usericon.php?w=70&id=$user[id]&$mmktime");
						// ไอคอนของ editprofile
						$ret['imgIcon'] = $icon;
						// ไอคอนในกรอบ login
						$ret['usericon'] = $icon;
						$ret['ret_register_usericon'] = '';
					}
				}
			}
			// fname
			if (isset($_POST['register_fname'])) {
				$save['fname'] = $db->sql_trim_str($_POST['register_fname']);
				if ($save['fname'] == '') {
					$ret['ret_register_fname'] = 'FNAME_EMPTY';
					$input = !$input ? 'register_fname' : $input;
					$error = !$error ? 'FNAME_EMPTY' : $error;
				} elseif (in_array($save['fname'], $config['member_reserv'])) {
					$ret['ret_register_fname'] = 'REGISTER_RESERV_USER';
					$input = !$input ? 'register_fname' : $input;
					$error = !$error ? 'REGISTER_RESERV_USER' : $error;
				} else {
					$ret['ret_register_fname'] = '';
				}
			}
			// lname
			if (isset($_POST['register_lname'])) {
				$save['lname'] = $db->sql_trim_str($_POST['register_lname']);
				if ($save['lname'] == '') {
					$ret['ret_register_lname'] = 'LNAME_EMPTY';
					$input = !$input ? 'register_lname' : $input;
					$error = !$error ? 'LNAME_EMPTY' : $error;
				} elseif (in_array($save['lname'], $config['member_reserv'])) {
					$ret['ret_register_lname'] = 'REGISTER_RESERV_USER';
					$input = !$input ? 'register_lname' : $input;
					$error = !$error ? 'REGISTER_RESERV_USER' : $error;
				} else {
					$ret['ret_register_lname'] = '';
				}
			}
			// phone
			if (isset($_POST['register_phone1'])) {
				$save['phone1'] = $db->sql_trim_str($_POST['register_phone1']);
				if ($save['phone1'] != '') {
					if (!preg_match('/[0-9]{9,10}/', $save['phone1'])) {
						$ret['ret_register_phone1'] = 'INVALID_PHONE_NUMBER';
						$input = !$input ? 'register_phone1' : $input;
						$error = !$error ? 'INVALID_PHONE_NUMBER' : $error;
					} else {
						// ตรวจสอบ phone ซ้ำ
						$sql = "SELECT `id` FROM `".DB_USER."` WHERE `phone1`='$save[phone1]' LIMIT 1";
						$search = $db->customQuery($sql);
						if (sizeof($search) == 1 && $user['id'] != $search[0]['id']) {
							$ret['ret_register_phone1'] = 'PHONE_EXISTS';
							$input = !$input ? 'register_phone1' : $input;
							$error = !$error ? 'PHONE_EXISTS' : $error;
						} else {
							$ret['ret_register_phone1'] = '';
						}
					}
				} elseif ($config['member_phone'] == 2) {
					$ret['ret_register_phone1'] = 'PHONE_EMPTY';
					$input = !$input ? 'register_phone1' : $input;
					$error = !$error ? 'PHONE_EMPTY' : $error;
				}
			}
			$address = array('company', 'address1', 'address2', 'provinceID', 'province', 'zipcode', 'country', 'phone2', 'company', 'sex', 'birthday');
			foreach ($_POST AS $key => $value) {
				$key = str_replace('register_', '', $key);
				if (in_array($key, $address)) {
					$save[$key] = $db->sql_trim_str($value);
				}
			}
			if (!$error) {
				if (sizeof($save) > 0) {
					// จังหวัด
					if ($save['country'] == 'TH') {
						$save['province'] = $user['province'];
					}
					// แก้ไข
					$db->edit(DB_USER, $user['id'], $save);
					$ret['error'] = 'REGISTER_UPDATE_SUCCESS';
				}
			} else {
				// คืนค่า input ตัวแรกที่ error
				$ret['error'] = $error;
				if (isset($input)) {
					$ret['input'] = $input;
				}
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
