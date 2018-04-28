<?php
	// widgets/contact/index.php
	if (defined('MAIN_INIT')) {
		// antispam
		$antispam = gcms::rndname(32);
		$_SESSION[$antispam] = gcms::rndname(4);
		// contact form
		$widget[] = '<form id=contact_frm class=mainform method=post action=index.php>';
		$widget[] = '<div class=item><label for=mail_sender>{LNG_EMAIL_SEND} {LNG_TO}</label><span class="g-input icon-email-sent"><select name=mail_reciever id=mail_reciever>';
		$emails = array();
		if ($module != '') {
			foreach (explode(',', $module) AS $item) {
				if (gcms::validMail($item)) {
					$emails = explode(',', $module);
				} else {
					$subject = $item;
				}
			}
			$_SESSION['emails'] = implode(',', $emails);
		}
		$widget[] = '<option value=admin>{LNG_ADMIN}</option>';
		foreach ($emails AS $i => $email) {
			$widget[] = '<option value='.$i.'>'.$email.'</option>';
		}
		$widget[] = '</select></span></div>';
		// sender
		$widget[] = '<div class=item><label for=mail_sender>{LNG_EMAIL_SENDER}</label><span class="g-input icon-email"><input type=text name=mail_sender id=mail_sender value="'.$_SESSION['login']['email'].'"></span></div>';
		// subject
		$widget[] = '<div class=item><label for=mail_topic>{LNG_EMAIL_SUBJECT}</label><span class="g-input icon-edit"><input type=text name=mail_topic id=mail_topic value="'.$subject.'"></span></div>';
		// detail
		$widget[] = '<div class=item><label for=mail_detail>{LNG_DETAIL}</label><span class="g-input icon-file"><textarea id=mail_detail name=mail_detail rows=10></textarea></span></div>';
		// anti spam
		$widget[] = '<div class=item><label class="g-input antispam"><span><img src="'.WEB_URL.'/antispamimage.php?id='.$antispam.'" alt=Antispam></span>';
		$widget[] = '<input type=text name=mail_antispam id=mail_antispam maxlength=4 value="'.(gcms::isAdmin() ? $_SESSION[$antispam] : '').'" placeholder="{LNG_ANTISPAM_COMMENT}">';
		$widget[] = '</span></div>';
		$widget[] = '<div class=item>';
		$widget[] = '<input type=submit id=mail_submit class="button large send" value="{LNG_SEND_MESSAGE}">';
		$widget[] = '<input type=hidden name=antispam value="'.$antispam.'">';
		$widget[] = '</div>';
		$widget[] = '</form>';
		$widget[] = '<script>';
		$widget[] = '$G(window).Ready(function(){';
		$widget[] = 'new GForm("contact_frm", "'.WEB_URL.'/widgets/contact/sendmail.php", null, false).onsubmit(doFormSubmit);';
		$widget[] = '});';
		$widget[] = '</script>';
		$widget = implode("\n", $widget);
	}
