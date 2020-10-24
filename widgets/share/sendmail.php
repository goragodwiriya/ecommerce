<?php
	// widgets/share/sendmail.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include ('../../bin/inint.php');
	// ตรวจสอบ referer
	if (gcms::isReferer()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// ค่าที่ส่งมา
			$reciever = $db->sql_trim_str($_POST['share_reciever']);
			$topic = $db->sql_trim_str($_POST['share_subject']);
			$url = trim($_POST['share_address']);
			$login = $_SESSION['login'];
			// ตรวจสอบค่าที่ส่งมา
			if ($topic == '' || $url == '' || $login['email'] == '') {
				$ret['error'] = 'ACTION_ERROR';
				$ret['location'] = 'close';
			} elseif ($reciever == '') {
				$ret['ret_share_reciever'] = 'this';
				$ret['input'] = 'share_reciever';
			} elseif ($_POST['share_antispam'] != $_SESSION[$_POST['antispam']]) {
				$ret['ret_share_antispam'] = 'this';
				$ret['input'] = 'share_antispam';
			} else {
				// ข้อความในอีเมล
				$replace = array();
				$replace['/%SENDER%/'] = $login['displayname'] == '' ? $login['email'] : $login['displayname'];
				$replace['/%URL%/'] = $url;
				$replace['/%TOPIC%/'] = $topic;
				// send mail
				$error = gcms::sendMail(1, 'share', $replace, $reciever);
				if ($error == '') {
					unset($_SESSION[$_POST['antispam']]);
					$ret['error'] = 'EMAIL_SEND_SUCCESS';
				} else {
					$ret['alert'] = rawurlencode($error);
				}
				$ret['location'] = 'close';
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
