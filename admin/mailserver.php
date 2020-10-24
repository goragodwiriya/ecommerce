<?php
// admin/mailserver.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // title
  $title = $lng['LNG_ADMIN_SENDMAIL_TITLE'];
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '{LNG_EMAIL_SETTINGS}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-email>'.$title.'</h1></header>';
  $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php autocomplete=off>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span>{LNG_GENERAL}</span></legend>';
  // sendmail
  $content[] = '<div class=item>';
  $content[] = '<label for=sendmail>{LNG_SENDMAIL}</label>';
  $content[] = '<span class="g-input icon-config"><select title="{LNG_SENDMAIL_COMMENT}" name=sendmail id=sendmail autofocus>';
  foreach ($lng['LNG_SENDMAILS'] AS $i => $value) {
    $sel = $i == $config['sendmail'] ? 'selected' : '';
    $content[] = "<option value=$i $sel>$value</option>";
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_sendmail>{LNG_SENDMAIL_COMMENT}</div>';
  $content[] = '</div>';
  // user_activate
  $content[] = '<div class=item>';
  $content[] = '<label for=user_activate>{LNG_USER_ACTIVATE}</label>';
  $content[] = '<span class="g-input icon-config"><select title="{LNG_USER_ACTIVATE_COMMENT}" name=user_activate id=user_activate>';
  foreach ($lng['LNG_USER_ACTIVATIES'] AS $i => $value) {
    $sel = $i == $config['user_activate'] ? 'selected' : '';
    $content[] = "<option value=$i $sel>$value</option>";
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_user_activate>{LNG_USER_ACTIVATE_COMMENT}</div>';
  $content[] = '</div>';
  // noreply_email
  $content[] = '<div class=item>';
  $content[] = '<label for=noreply_email>{LNG_EMAIL_NOREPLY}</label>';
  $content[] = '<span class="g-input icon-email"><input type=email title="{LNG_EMAIL_NOREPLY_COMMENT}" name=noreply_email id=noreply_email value="'.$config['noreply_email'].'" maxlength=255></span>';
  $content[] = '<div class=comment id=result_noreply_email>{LNG_EMAIL_NOREPLY_COMMENT}</div>';
  $content[] = '</div>';
  // email_use_phpMailer
  $content[] = '<div class=item>';
  $content[] = '<label for=email_use_phpMailer>{LNG_USE_PHPMAILER}</label>';
  $content[] = '<span class="g-input icon-config"><select title="{LNG_USE_PHPMAILER_COMMENT}" name=email_use_phpMailer id=email_use_phpMailer>';
  foreach ($lng['LNG_USE_PHPMAILERS'] AS $i => $value) {
    $sel = $i == $config['email_use_phpMailer'] ? 'selected' : '';
    $content[] = "<option value=$i $sel>$value</option>";
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_email_use_phpMailer>{LNG_USE_PHPMAILER_COMMENT}</div>';
  $content[] = '</div>';
  // email_charset
  $content[] = '<div class=item>';
  $content[] = '<label for=email_charset>{LNG_EMAIL_CHARSET}</label>';
  $content[] = '<span class="g-input icon-config"><input type=text title="{LNG_EMAIL_CHARSET_COMMENT}" name=email_charset id=email_charset value="'.$config['email_charset'].'" maxlength=20></span>';
  $content[] = '<div class=comment id=result_email_charset>{LNG_EMAIL_CHARSET_COMMENT}</div>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span>{LNG_CONFIG_EMAIL}</span></legend>';
  // email_Host
  $content[] = '<div class=item>';
  $content[] = '<label for=email_Host>{LNG_MAILSERVER}</label>';
  $content[] = '<span class="g-input icon-world"><input type=text title="{LNG_MAILSERVER_COMMENT}" name=email_Host id=email_Host value="'.$config['email_Host'].'"></span>';
  $content[] = '<div class=comment id=result_email_Host>{LNG_MAILSERVER_COMMENT}</div>';
  $content[] = '</div>';
  // email_Port
  $content[] = '<div class=item>';
  $content[] = '<label for=email_Port>{LNG_EMAIL_PORT}</label>';
  $content[] = '<span class="g-input icon-config"><input type=number title="{LNG_EMAIL_PORT_COMMENT}" name=email_Port id=email_Port value="'.$config['email_Port'].'"></span>';
  $content[] = '<div class=comment id=result_email_Port>{LNG_EMAIL_PORT_COMMENT}</div>';
  $content[] = '</div>';
  // email_SMTPAuth
  $content[] = '<div class=item>';
  $disabled = $config['email_SMTPAuth'] == 0 ? ' disabled' : '';
  $content[] = '<label for=email_SMTPAuth>{LNG_EMAIL_SMTPAUTH}</label>';
  $content[] = '<span class="g-input icon-config"><select title="{LNG_EMAIL_SMTPAUTH_COMMENT}" name=email_SMTPAuth id=email_SMTPAuth>';
  foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
    $sel = $i == $config['email_SMTPAuth'] ? 'selected' : '';
    $content[] = "<option value=$i $sel>$value</option>";
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_email_SMTPAuth>{LNG_EMAIL_SMTPAUTH_COMMENT}</div>';
  $content[] = '</div>';
  // email_SMTPSecure
  $content[] = '<div class=item>';
  $content[] = '<label for=email_SMTPSecure>{LNG_EMAIL_SMTPSECURE}</label>';
  $content[] = '<span class="g-input icon-config"><select title="{LNG_EMAIL_SMTPSECURE_COMMENT}" id=email_SMTPSecure name=email_SMTPSecure>';
  foreach ($lng['LNG_EMAIL_SMTPSECURIES'] AS $i => $value) {
    $sel = $i == $config['email_SMTPSecure'] ? 'selected' : '';
    $content[] = "<option value='$i' $sel>$value</option>";
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_email_SMTPSecure>{LNG_EMAIL_SMTPSECURE_COMMENT}</div>';
  $content[] = '</div>';
  // email_Username
  $content[] = '<div class=item>';
  $content[] = '<label for=email_Username>{LNG_USERNAME}</label>';
  $content[] = '<span class="g-input icon-user"><input type=text title="{LNG_EMAIL_USERNAME_COMMENT}" id=email_Username name=email_Username value="'.$config['email_Username'].'"'.$disabled.'></span>';
  $content[] = '<div class=comment id=result_email_Username>{LNG_EMAIL_USERNAME_COMMENT}</div>';
  $content[] = '</div>';
  // email_Password
  $content[] = '<div class=item>';
  $content[] = '<label for=email_Password>{LNG_PASSWORD}</label>';
  $content[] = '<span class="g-input icon-password"><input type=text title="{LNG_EMAIL_PASSWORD_COMMENT}" id=email_Password name=email_Password'.$disabled.'></span>';
  $content[] = '<div class=comment id=result_email_Password>{LNG_EMAIL_PASSWORD_COMMENT}</div>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  // submit
  $content[] = '<fieldset class=submit>';
  $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
  $content[] = '</fieldset>';
  $content[] = '</form>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = '$G("email_SMTPAuth").addEvent("change", function(){';
  $content[] = 'var a = this.value.toInt();';
  $content[] = '$E("email_SMTPSecure").disabled = (a == 0);';
  $content[] = '$E("email_Username").disabled = (a == 0);';
  $content[] = '$E("email_Password").disabled = (a == 0);';
  $content[] = '});';
  $content[] = 'new GForm("setup_frm","saveconfig.php").onsubmit(doFormSubmit);';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'mailserver';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
