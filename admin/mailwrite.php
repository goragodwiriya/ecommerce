<?php
// admin/mailwrite.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // แม่แบบที่เลือก
  $id = (int)$_GET['id'];
  if ($id > 0) {
    $email = $db->getRec(DB_EMAIL_TEMPLATE, $id);
  } else {
    $email['id'] = 0;
    $email['module'] = 'mailmerge';
  }
  if ($id > 0 && !$email) {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    // title
    $a = array();
    $a[] = '<a href="index.php?module=mailtemplate" class=icon-email>{LNG_EMAIL_TEMPLATE}</a>';
    if ($email['id'] == 0) {
      $a[] = '{LNG_ADD_NEW}';
      $title = "$lng[LNG_ADD_NEW] $lng[LNG_EMAIL_TEMPLATE]";
      // template
      $email['detail'] = file_get_contents(ROOT_PATH.'admin/skin/'.$config['admin_skin'].'/mailtemplate.html');
      $email['language'] = 'th';
    } else {
      $a[] = '{LNG_EDIT}';
      $title = "$lng[LNG_EDIT] $email[name]";
    }
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-write>'.$title.'</h1></header>';
    $content[] = '<form id=write_frm class=setup_frm method=post action=index.php>';
    $content[] = '<fieldset>';
    // from_email
    $content[] = '<div class=item>';
    $content[] = '<label for=email_from_email>{LNG_EMAIL_SENDER}</label>';
    $content[] = '<span class="g-input icon-email"><input type=text name=email_from_email id=email_from_email value="'.$email['from_email'].'" title="{LNG_PLEASE_FILL} {LNG_EMAIL_SENDER}"></span>';
    $content[] = '<div class=comment id=result_email_from_email>{LNG_EMAIL_FROM_COMMENT}</div>';
    $content[] = '</div>';
    if ($email['module'] != 'mailmerge') {
      // copy_to
      $content[] = '<div class=item>';
      $content[] = '<label for=email_copy_to>{LNG_EMAIL_COPY_TO}</label>';
      $content[] = '<span class="g-input icon-email"><input type=text name=email_copy_to id=email_copy_to value="'.$email['copy_to'].'" title="{LNG_EMAIL_COPY_TO_COMMENT}"></span>';
      $content[] = '<div class=comment id=result_email_copy_to>{LNG_EMAIL_COPY_TO_COMMENT}</div>';
      $content[] = '</div>';
    }
    // keywords
    $content[] = '<div class=item>';
    $content[] = '<table class="info border mobile">';
    $content[] = '<caption>{LNG_EMAIL_KEYWORDS_COMMENT}</caption>';
    $content[] = '<tbody>';
    $content[] = '<tr><th id=l0>{LNG_WEB_TITLE}</th><td headers=l0>%WEBTITLE%</td><th id=r0>{LNG_WEBSITE} {LNG_URL}</th><td headers=r0>%WEBURL%</td></tr>';
    $content[] = '<tr><th id=l4>{LNG_EMAIL_RECIEVER}</th><td headers=l4>%EMAIL%</td><th id=r4>{LNG_EMAIL_SENDER}</th><td headers=r4>%ADMINEMAIL%</td></tr>';
    $content[] = '<tr><th id=l6>{LNG_EMAIL_CREATE}</th><td headers=l6>%TIME%</td><th id=r6>{LNG_UNSUBSCRIB_ADDRESS}</th><td headers=r6>%WEBURL%/index.php?module=unsubscrib&amp;email=%EMAIL%</td></tr>';
    if ($email['module'] == 'mailmerge') {
      $content[] = '<tr><th id=l7>{LNG_MAILMERGE_READ_URL}</th><td headers=l7 colspan=3>%WEBURL%/index.php?module=mailmerge&amp;id=%EMAILID%</td></tr>';
    }
    $content[] = '</tbody>';
    $content[] = '</table>';
    $content[] = '</div>';
    // subject
    $content[] = '<div class=item>';
    $content[] = '<label for=email_subject>{LNG_EMAIL_SUBJECT}</label>';
    $content[] = '<span class="g-input icon-edit"><input type=text name=email_subject id=email_subject value="'.$email['subject'].'" maxlength=64 title="{LNG_PLEASE_FILL} {LNG_EMAIL_SUBJECT}"></span>';
    $content[] = '</div>';
    if ($email['module'] != 'mailmerge') {
      // language
      $content[] = '<div class=item>';
      $content[] = '<label for=email_language>{LNG_LANGUAGE}</label>';
      $content[] = '<span class="g-input icon-language"><select name=email_language id=email_language title="{LNG_EMAIL_LANGUAGE_COMMENT}">';
      foreach ($config['languages'] AS $language) {
        $sel = $language == $email['language'] ? ' selected' : '';
        $content[] = '<option value='.$language.$sel.'>'.$language.'</option>';
      }
      $content[] = '</select></span>';
      $content[] = '<div class=comment id=result_email_language>{LNG_EMAIL_LANGUAGE_COMMENT}</div>';
      $content[] = '</div>';
    }
    // detail
    $content[] = '<div class=item>';
    $content[] = '<label for=email_detail>{LNG_DETAIL} </label>';
    $content[] = '<div><textarea name=email_detail id=email_detail>'.gcms::detail2TXT($email['detail']).'</textarea></div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    // submit
    $content[] = '<fieldset class=submit>';
    $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
    $content[] = '<input type=hidden id=email_id name=email_id value='.$email['id'].'>';
    $content[] = '</fieldset>';
    $lastupdate = $email['last_update'] == '' ? '-' : gcms::mktime2date($email['last_update']);
    $content[] = '<div class=lastupdate><span class=comment>{LNG_WRITE_COMMENT}</span>{LNG_LAST_UPDATE}<span id=lastupdate>'.$lastupdate.'</span></div>';
    $content[] = '</form>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'CKEDITOR.replace("email_detail", {';
    $content[] = 'toolbar:"Document",';
    $content[] = 'language:"'.LANGUAGE.'",';
    $content[] = 'contentsCss:"",';
    $content[] = 'height:300,';
    if (is_dir(ROOT_PATH.'ckfinder/')) {
      $content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html",';
      $content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Images",';
      $content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Flash",';
      $content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files",';
      $content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images",';
      $content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash"';
    } else {
      $connector = urlencode(WEB_URL.'/ckeditor/filemanager/connectors/php/connector.php');
      $content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Connector='.$connector.'",';
      $content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector='.$connector.'",';
      $content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector='.$connector.'",';
      $content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php",';
      $content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php?Type=Image",';
      $content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.phpType=Flash"';
    }
    $content[] = '});';
    $content[] = 'new GForm("write_frm","mailwrite_save.php").onsubmit(doFormSubmit);';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'mailwrite';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
