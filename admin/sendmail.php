<?php
// admin/sendmail.php
if (MAIN_INIT == 'admin' && $canAdmin) {
  $emails = array();
  if (isset($_GET['to'])) {
    $sql = "SELECT `id`,`email` FROM `".DB_USER."` WHERE `id` IN($_GET[to])";
    foreach ($db->customQuery($sql) AS $item) {
      if ($item['id'] != $_SESSION['login']['id']) {
        $emails[] = $item['email'];
      }
    }
  } elseif (isset($_GET['mail'])) {
    $emails[] = $db->sql_trim_str($_GET['mail']);
  }
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li><span class=icon-email>{LNG_MAILBOX}</span></li></ul></div>';
  if (isset($_GET['to']) && sizeof($emails) == 0) {
    $title = $lng['SEND_MAIL_ERROR'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    // title
    $title = $lng['LNG_EMAIL_SEND'];
    // แสดงผล
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-email-sent>'.$title.'</h1></header>';
    $content[] = '<form id=write_frm class=setup_frm method=post action=index.php autocomplete=off>';
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_EMAIL_SEND}</span></legend>';
    // email_reciever
    $content[] = '<div class=item>';
    $content[] = '<label for=email_reciever>{LNG_RECIEVER} :</label>';
    $content[] = '<span class="g-input icon-email-sent"><input type=text name=email_reciever id=email_reciever value="'.implode(',', $emails).'" autofocus title="{LNG_EMAIL_TO_COMMENT}"></span>';
    $content[] = '<div class=comment id=result_email_reciever>{LNG_EMAIL_TO_COMMENT}</div>';
    $content[] = '</div>';
    // email_from
    $content[] = '<div class=item>';
    $content[] = '<label for=email_from>{LNG_SENDER}</label>';
    $content[] = '<span class="g-input icon-email"><select name=email_from id=email_from title="{LNG_PLEASE_SELECT} {LNG_EMAIL_SENDER}">';
    if ($isAdmin) {
      $sql = "SELECT `id`,`email` FROM `".DB_USER."` WHERE `status`='1'";
      foreach ($db->customQuery($sql) AS $item) {
        $content[] = '<option value='.$item['id'].'>'.$item['email'].'</option>';
      }
    } else {
      $content[] = '<option value='.$login_result['id'].'>'.$login_result['email'].'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment id=result_email_from>{LNG_PLEASE_SELECT} {LNG_EMAIL_SENDER}</div>';
    $content[] = '</div>';
    // email_subject
    $content[] = '<div class=item>';
    $content[] = '<label for=email_subject>{LNG_EMAIL_SUBJECT}</label>';
    $content[] = '<span class="g-input icon-edit"><input type=text name=email_subject id=email_subject title="{LNG_PLEASE_FILL} {LNG_EMAIL_SUBJECT}"></span>';
    $content[] = '</div>';
    // email_detail
    $content[] = '<div class=item>';
    $content[] = '<div class=comment id=result_email_subject>{LNG_PLEASE_FILL} {LNG_EMAIL_SUBJECT}</div>';
    $content[] = '<label for=email_detail>{LNG_DETAIL}</label>';
    $content[] = '<div><textarea id=email_detail name=email_detail></textarea></div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    // submit
    $content[] = '<fieldset class=submit>';
    $content[] = '<input type=submit class="button large ok" value="{LNG_SEND_MESSAGE}">';
    $content[] = '</fieldset>';
    $content[] = '</form>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = 'CKEDITOR.replace("email_detail", {toolbar:"Email"});';
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'new GForm("write_frm", "mailto.php").onsubmit(doFormSubmit);';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'sendmail';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
