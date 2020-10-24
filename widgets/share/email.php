<?php
	// widgets/share/email.php
	header("content-type: text/html; charset=UTF-8");
	// ตัวแปรหลัก
	include ('../../bin/inint.php');
	// referer
	if (gcms::isReferer()) {
		if (!gcms::isMember()) {
			$ret['error'] = 'NOT_LOGIN';
		} else {
			// antispam
			$antispam = gcms::rndname(32);
			$_SESSION[$antispam] = gcms::rndname(4);
			// share form
			$widget = array();
			$widget[] = '<form id=share_frm method=post action=index.php>';
			$widget[] = '<h1 class=icon-email-sent>{LNG_SHARE_TITLE}</h1>';
			$widget[] = '<h2>'.$_POST['t'].'</h2>';
			$widget[] = '<address>'.$_POST['u'].'</address>';
			$widget[] = '<div><label class="g-input icon-email"><input type=text name=share_reciever id=share_reciever placeholder="{LNG_EMAIL_RECIEVER}" title="{LNG_EMAIL_TO_COMMENT}"></label>';
			$widget[] = '<em class=comment id=result_share_reciever>{LNG_EMAIL_TO_COMMENT}</em></div>';
			$widget[] = '<label class="g-input antispam">';
			$widget[] = '<span><img src="'.WEB_URL.'/antispamimage.php?id='.$antispam.'" alt=antispam></span>';
			$widget[] = '<input type=text name=share_antispam id=share_antispam size=10 maxlength=4 value="'.(gcms::isAdmin() ? $_SESSION[$antispam] : '').'" placeholder="{LNG_ANTISPAM}" title="{LNG_ANTISPAM_COMMENT}"></label>';
			$widget[] = '<em class=comment id=result_share_antispam>{LNG_ANTISPAM_COMMENT}</em></div>';
			$widget[] = '<div class=submit><input type=submit class="button large send" value="{LNG_EMAIL_SEND}">';
			$widget[] = '<input type=hidden name=antispam value='.$antispam.'>';
			$widget[] = '<input type=hidden name=share_subject value="'.$_POST['t'].'">';
			$widget[] = '<input type=hidden name=share_address value="'.$_POST['u'].'">';
			$widget[] = '</div></form>';
			$widget[] = '<script>';
			$widget[] = '$G(window).Ready(function(){';
			$widget[] = 'new GForm("share_frm", "'.WEB_URL.'/widgets/share/sendmail.php").onsubmit(doFormSubmit);';
			$widget[] = '});';
			$widget[] = '</script>';
			$ret['content'] = rawurlencode(gcms::pregReplace('/{(LNG_[A-Z0-9_]+)}/e', 'gcms::getLng', implode('', $widget)));
		}
		// คืนค่า JSON
		echo gcms::array2json($ret);
	}
