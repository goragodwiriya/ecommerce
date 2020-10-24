<?php
	// modules/member/newregister.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		// ค่าที่ส่งมา
		$password = $db->sql_trim_str($_POST['register_password']);
		$repassword = $db->sql_trim_str($_POST['register_repassword']);
		$save['email'] = $db->sql_trim_str($_POST['register_email']);
		$save['phone1'] = $db->sql_trim_str($_POST['register_phone']);
		$save['idcard'] = $db->sql_trim_str($_POST['register_idcard']);
		// ตรวจสอบข้อมูลที่กรอก
		$error = false;
		$input = false;
		if (isset($_POST['register_accept'])) {
			// email
			if ($save['email'] == '') {
				$ret['ret_register_email'] = 'EMAIL_EMPTY';
				$input = !$input ? 'register_email' : $input;
				$error = !$error ? 'EMAIL_EMPTY' : $error;
			} elseif (!gcms::validMail($save['email'])) {
				$ret['ret_register_email'] = 'REGISTER_INVALID_EMAIL';
				$input = !$input ? 'register_email' : $input;
				$error = !$error ? 'REGISTER_INVALID_EMAIL' : $error;
			} else {
				// ตรวจสอบ email ซ้ำ
				$sql = "SELECT `id` FROM `".DB_USER."` WHERE `email`='$save[email]' AND `fb`='0' LIMIT 1";
				$search = $db->customQuery($sql);
				if (sizeof($search) == 1) {
					$ret['ret_register_email'] = 'EMAIL_EXISTS';
					$input = !$input ? 'register_email' : $input;
					$error = !$error ? 'EMAIL_EXISTS' : $error;
				} else {
					$ret['ret_register_email'] = '';
				}
			}
			// password
			if ($password == '') {
				$ret['ret_register_password'] = 'PASSWORD_EMPTY';
				$input = !$input ? 'register_password' : $input;
				$error = !$error ? 'PASSWORD_EMPTY' : $error;
			} elseif (mb_strlen($password) < 4) {
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
				$save['password'] = md5($password.$save['email']);
				$ret['ret_register_password'] = '';
				$ret['ret_register_repassword'] = '';
			}
			// phone
			if ($save['phone1'] != '') {
				// ตรวจสอบ phone ซ้ำ
				$sql = "SELECT `id` FROM `".DB_USER."` WHERE `phone1`='".addslashes($save['phone1'])."' LIMIT 1";
				$search = $db->customQuery($sql);
				if (sizeof($search) == 1) {
					$ret['ret_register_phone'] = 'PHONE_EXISTS';
					$input = !$input ? 'register_phone' : $input;
					$error = !$error ? 'PHONE_EXISTS' : $error;
				} else {
					$ret['ret_register_phone'] = '';
				}
			} elseif ($config['member_phone'] == 2) {
				$ret['ret_register_phone'] = 'PHONE_EMPTY';
				$input = !$input ? 'register_phone' : $input;
				$error = !$error ? 'PHONE_EMPTY' : $error;
			}
			// idcard
			if ($save['idcard'] != '') {
				if (!gcms::checkIDCard($save['idcard'])) {
					$ret['ret_register_idcard'] = 'IDCARD_INVALID';
					$input = !$input ? 'register_idcard' : $input;
					$error = !$error ? 'IDCARD_INVALID' : $error;
				} else {
					// ตรวจสอบ idcard ซ้ำ
					$sql = "SELECT `id` FROM `".DB_USER."` WHERE `idcard`='$save[idcard]' LIMIT 1";
					$search = $db->customQuery($sql);
					if (sizeof($search) == 1) {
						$ret['ret_register_idcard'] = 'IDCARD_EXISTS';
						$input = !$input ? 'register_idcard' : $input;
						$error = !$error ? 'IDCARD_EXISTS' : $error;
					} else {
						$ret['ret_register_idcard'] = '';
					}
				}
			} elseif ($config['member_idcard'] == 2) {
				$ret['ret_register_idcard'] = 'IDCARD_EMPTY';
				$input = !$input ? 'register_idcard' : $input;
				$error = !$error ? 'IDCARD_EMPTY' : $error;
			}
			// antispam
			if ($_POST['register_antispam'] != $_SESSION[$_POST['antispam']]) {
				$ret['ret_register_antispam'] = 'this';
				$input = !$input ? 'register_antispam' : $input;
				$error = !$error ? 'ANTISPAM_INCORRECT' : $error;
			} else {
				$ret['ret_register_antispam'] = '';
			}
			// invite
			if (isset($_POST['register_invite'])) {
				$invite = $db->sql_trim_str($_POST['register_invite']);
				if ($invite != '') {
					$counselor = $db->basicSearch(DB_USER, 'email', $invite);
					if ($counselor) {
						$ret['ret_register_invite'] = '';
						$save['invite_id'] = $counselor['id'];
					} elseif ($config['member_invitation'] == 1) {
						$ret['ret_register_invite'] = 'INVITE_NOT_FOUND';
						$input = !$input ? 'register_invite' : $input;
						$error = !$error ? 'INVITE_NOT_FOUND' : $error;
					}
				} elseif ($config['member_invitation'] == 1) {
					$ret['ret_register_invite'] = 'INVITE_EMPTY';
					$input = !$input ? 'register_invite' : $input;
					$error = !$error ? 'INVITE_EMPTY' : $error;
				}
			}
			if (!$error) {
				// clear antispam
				unset($_SESSION[$_POST['antispam']]);
				$save['create_date'] = $mmktime;
				$save['subscrib'] = 1;
				$save['status'] = 0;
				list($displayname, $domain) = explode('@', $save['email']);
				$save['displayname'] = $displayname;
				$a = 0;
				while (true) {
					if (!$db->basicSearch(DB_USER, 'displayname', $save['displayname'])) {
						break;
					} else {
						$a++;
						$save['displayname'] = $displayname.$a;
					}
				}
				// บันทึกลงฐานข้อมูล
				if ($config['user_activate'] > 0 && $config['sendmail'] == 1) {
					// ต้อง activate และ สามารถส่งเมล์ได้
					$save['activatecode'] = gcms::rndname(32);
					// บันทึกลงฐานข้อมูล
					$lastid = $db->add(DB_USER, $save);
					// แสดงข้อความตอบรับการสมัครสมาชิก
					$ret['alert'] = sprintf($lng['NEWREGISTER_ACTIVATE_ALERT'], $save['email']);
					// กลับไปหน้าหลักเว็บไซต์
					$ret['location'] = $_POST['modal'] != 'true' ? rawurlencode(WEB_URL.'/index.php') : 'close';
				} else {
					// บันทึกลงฐานข้อมูล
					$lastid = $db->add(DB_USER, $save);
					// login
					$_SESSION['login'] = $save;
					$_SESSION['login']['id'] = $lastid;
					$_SESSION['login']['password'] = $password;
					// แสดงข้อความตอบรับการสมัครสมาชิก
					$ret['alert'] = sprintf($lng['NEWREGISTER_NOACTIVATE_ALERT'], $save['email']);
					// กลับไปแก้ไขข้อมูลอื่นๆ เพิ่มเติม
					$ret['location'] = $_POST['modal'] != 'true' ? rawurlencode(WEB_URL.'/index.php?module=editprofile&amp;id='.$lastid) : 'close';
				}
				if ($config['sendmail'] == 1) {
					// ข้อมูลอีเมล
					$replace = array();
					$replace['/%EMAIL%/'] = $save['email'];
					$replace['/%PASSWORD%/'] = $password;
					$replace['/%ID%/'] = $save['activatecode'];
					// send mail
					$id = $config['user_activate'] == 0 ? 2 : 1;
					gcms::sendMail($id, 'member', $replace, $save['email']);
				}
				// โหลดโมดูลที่ติดตั้ง เพื่อแจ้งการเพิ่มสมาชิกใหม่ให้กับโมดูล
				define('MAIN_INIT', 'new_register');
				$dir = ROOT_PATH.'modules/';
				$f = opendir($dir);
				while (false !== ($owner = readdir($f))) {
					if ($owner != '.' && $owner != '..') {
						if (is_dir($dir.$owner.'/')) {
							if (is_file($dir.$owner.'/add_member.php')) {
								include ($dir.$owner.'/add_member.php');
							}
						}
					}
				}
				closedir($f);
			} else {
				// คืนค่า input ตัวแรกที่ error
				$ret['error'] = $error;
				$ret['input'] = $input;
			}
		} else {
			$ret['error'] = 'REGISTER_NOT_ACCEPT';
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
