<?php
	// modules/member/recover.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$email = $db->sql_trim_str($_POST['forgot_email']);
			if ($email == '') {
				$ret['input'] = 'forgot_email';
				$ret['error'] = 'EMAIL_EMPTY';
			} else {
				$sql = "SELECT * FROM `".DB_USER."` WHERE (`email`='$email' OR (`phone1`!='' AND `phone1`='$email')) AND `fb`='0' LIMIT 1";
				$user = $db->customQuery($sql);
				if (sizeof($user) == 1) {
					$user = $user[0];
					// สุ่มและอัปเดตรหัสผ่านใหม่
					$password = gcms::rndname(6);
					$save['password'] = md5($password.$user['email']);
					$db->edit(DB_USER, $user['id'], $save);
					// ส่งเมล์แจ้งสมาชิก
					$replace = array();
					$replace['/%PASSWORD%/'] = $password;
					$replace['/%EMAIL%/'] = $user['email'];
					if ($user['activatecode'] != '') {
						$replace['/%ID%/'] = $user['activatecode'];
						// send mail
						$err = gcms::sendMail(1, 'member', $replace, $user['email']);
					} else {
						// send mail
						$err = gcms::sendMail(3, 'member', $replace, $user['email']);
					}
					$ret['alert'] = rawurlencode(sprintf($lng['FORGOT_SUCCESS'], $user['email']));
					$ret['location'] = $_POST['modal'] == 'true' ? 'close' : 'back';
				} else {
					$ret['input'] = 'forgot_email';
					$ret['error'] = 'EMAIL_NOT_FOUND';
				}
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
