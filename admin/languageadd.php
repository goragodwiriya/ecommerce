<?php
// admin/languageadd.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // ภาษาที่ต้องการ
  $id = $_GET['id'];
  // title
  $title = $lng['LNG_LANGUAGE_TITLE'];
  $sub_title = $id == '' ? '{LNG_ADD}' : '{LNG_EDIT}';
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '<a href="{URLQUERY?module=languages}">{LNG_LANGUAGE}</a>';
  $a[] = $sub_title;
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-language>'.$title.'</h1></header>';
  $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span>'.$sub_title.' {LNG_LANGUAGE}</span></legend>';
  $content[] = '<div class=subtitle>{LNG_LANGUAGE_ADD_TITLE}</div>';
  $content[] = '<div class=item>';
  $content[] = '<label for=lang_name>{LNG_LANGUAGE}</label>';
  $content[] = '<span class="g-input icon-language"><input type=text maxlength=2 name=lang_name id=lang_name value="'.$id.'" pattern="[a-z]+"></span>';
  $content[] = '</div>';
  if ($id == '') {
    $content[] = '<div class=item>';
    $content[] = '<label for=lang_copy>{LNG_LANGUAGE_COPY}</label>';
    $content[] = '<span class="g-input icon-language"><select name=lang_copy id=lang_copy>';
    $content[] = '<option value=0>{LNG_NOT_COPY}</option>';
    foreach ($config['languages'] AS $item) {
      $content[] = '<option value='.$item.'>'.$item.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '</div>';
  }
  $icon = is_file(DATA_PATH."language/$id.gif") ? DATA_URL."language/$id.gif" : "../skin/img/blank.gif";
  $content[] = '<div class=item>';
  $content[] = '<div class=usericon><span><img id=language_icon src='.$icon.' alt=Icon></span></div>';
  $content[] = '<label for=lang_icon>{LNG_ICON}</label>';
  $content[] = '<span class="g-input icon-upload"><input class=g-file type=file name=lang_icon id=lang_icon placeholder="{LNG_BROWSE_FILE}" accept="'.gcms::getEccept(array('gif')).'" data-preview=language_icon></span>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  // submit
  $content[] = '<fieldset class=submit>';
  $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
  $content[] = '<input type=hidden name=save_id value="'.$id.'">';
  $content[] = '<input type=hidden name=languageadd value=1>';
  $content[] = '</fieldset>';
  $content[] = '</form>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = 'new GForm("setup_frm", "'.WEB_URL.'/admin/language_save.php").onsubmit(doFormSubmit);';
  $content[] = '});';
  $content[] = '</script>';
  // หน้าปัจจุบัน
  $url_query['module'] = 'languageadd';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
