<?php
	// widget/contact/sendmail.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		if ($_SESSION['login']['account'] == 'demo') {
			$ret['error'] = 'EX_MODE_ERROR';
		} else {
			// อีเมลผู้รับ
			if ($_SESSION['emails'] != '') {
				$emails = explode(',', $_SESSION['emails']);
			} else {
				$emails = array();
			}
			if ($_POST['mail_reciever'] == 'admin') {
				$reciever = array();
				$sql = "SELECT `email` FROM `".DB_USER."` WHERE `status`='1'";
				foreach ($db->customQuery($sql) AS $item) {
					$reciever[] = $item['email'];
				}
				$reciever = implode(',', $reciever);
			} else {
				$reciever = $emails[(int)$_POST['mail_reciever']];
			}
			// ค่าที่ส่งมา
			$topic = htmlspecialchars(trim($_POST['mail_topic']));
			$detail = gcms::txtClean($_POST['mail_detail']);
			$sender = $_POST['mail_sender'];
			$ret = array();
			// ตรวจสอบค่าที่ส่งมา
			if ($sender == '') {
				$ret['error'] = 'SENDER_EMPTY';
				$ret['input'] = 'mail_sender';
			} elseif (!gcms::validMail($sender)) {
				$ret['error'] = 'REGISTER_INVALID_EMAIL';
				$ret['input'] = 'mail_sender';
			} elseif ($reciever == '') {
				$ret['error'] = 'ACTION_ERROR';
				$ret['input'] = 'mail_reciever';
			} elseif ($sender == $reciever) {
				$ret['error'] = 'EMAIL_SEND_SELF';
				$ret['input'] = 'mail_sender';
			} elseif ($topic == '') {
				$ret['error'] = 'TOPIC_EMPTY';
				$ret['input'] = 'mail_topic';
			} elseif ($detail == '') {
				$ret['error'] = 'DETAIL_EMPTY';
			} elseif ($_POST['mail_antispam'] != $_SESSION[$_POST['antispam']]) {
				$ret['ret_mail_antispam'] = 'this';
				$ret['input'] = 'mail_antispam';
			} else {
				// ส่งอีเมล
				$error = gcms::customMail($reciever, $sender, $topic, $detail);
				// clear antispam
				unset($_SESSION['emails']);
				unset($_SESSION[$_POST['antispam']]);
				// คืนค่า
				if ($error == '') {
					$ret['error'] = 'EMAIL_SEND_SUCCESS';
					$ret['location'] = 'back';
				} else {
					$ret['alert'] = rawurlencode($error);
				}
			}
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
