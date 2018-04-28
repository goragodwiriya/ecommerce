<?php
// admin/intro.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // ภาษาที่เลือก
  $lang = $_GET['write_language'];
  $lang = in_array($lang, $install_languages) ? $lang : LANGUAGE;
  // ข้อความ intro
  $search = $db->basicSearch(DB_LANGUAGE, 'key', 'INTRO_PAGE_DETAIL');
  // title
  $title = $lng['LNG_INTRO_PAGE'];
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '{LNG_INTRO_PAGE}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-write>'.$title.'</h1></header>';
  $content[] = '<form id=write_frm class=setup_frm method=post action=index.php>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span>{LNG_INTRO_PAGE}</span></legend>';
  $content[] = '<aside class=message>{LNG_INTRO_PAGE_COMMENT}</aside>';
  // intro
  $content[] = '<div class=item>';
  $content[] = '<div class="table collapse">';
  $content[] = '<label for=write_mode>{LNG_SETTINGS}</label>';
  $content[] = '<span class="g-input icon-config"><select name=write_mode id=write_mode title="{LNG_PLEASE_SELECT} {LNG_INTRO_PAGE}">';
  foreach ($lng['OPEN_CLOSE'] AS $i => $item) {
    $sel = $config['show_intro'] == $i ? ' selected' : '';
    $content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '</div>';
  $content[] = '</div>';
  // language
  $content[] = '<div class=item>';
  $content[] = '<label for=write_language>{LNG_LANGUAGE}</label>';
  $content[] = '<div class="table collapse">';
  $content[] = '<div class=td>';
  $content[] = '<span class="g-input icon-language"><select name=write_language id=write_language title="{LNG_PLEASE_SELECT} {LNG_LANGUAGE}">';
  foreach ($install_languages AS $item) {
    $sel = $lang == $item ? ' selected' : '';
    $content[] = '<option value='.$item.$sel.'>'.$item.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '</div>';
  $content[] = '<div class=td>&nbsp;<a id=write_go class="button go">{LNG_GO}</a></div>';
  $content[] = '</div>';
  $content[] = '</div>';
  // detail
  $content[] = '<div class=item>';
  $content[] = '<label for=write_detail>{LNG_CONTENTS}</label>';
  $content[] = '<div><textarea name=write_detail id=write_detail>'.gcms::detail2TXT($search[$lang]).'</textarea></div>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  // submit
  $content[] = '<fieldset class=submit>';
  $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
  $content[] = '<input type=hidden name=intro value=1>';
  $content[] = '</fieldset>';
  $content[] = '</form>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = 'CKEDITOR.replace("write_detail", {';
  $content[] = 'toolbar:"Document",';
  $content[] = 'language:"'.LANGUAGE.'",';
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
  $content[] = 'new GForm("write_frm","savewrite.php").onsubmit(doFormSubmit);';
  $content[] = 'callClick("write_go", function(){window.location = "index.php?module=intro&lang=" + $E("write_language").value});';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'intro';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
