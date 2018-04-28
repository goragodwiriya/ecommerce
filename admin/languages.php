<?php
// admin/languages.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // ค่าที่ส่งมา
  // title
  $title = $lng['LNG_LANGUAGE_SETTINGS'];
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '{LNG_LANGUAGE}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-language>'.$title.'</h1></header>';
  $content[] = '<div>';
  $content[] = '<div class=subtitle>{LNG_LANGUAGE_TITLE}</div>';
  $content[] = '<dl class=editinplace_list id=languages>';
  foreach ($install_languages AS $i => $item) {
    $row = '<dd id=L_'.$item.'>';
    $row .= '<span class=icon-delete id=delete_'.$item.' title="{LNG_DELETE} {LNG_LANGUAGE}"></span>';
    $row .= '<a class=icon-edit href="index.php?module=languageadd&amp;id='.$item.'" title="{LNG_EDIT} {LNG_LANGUAGE}"></a>';
    $sel = is_array($config['languages']) && in_array($item, $config['languages']) ? 'check' : 'uncheck';
    $row .= '<span class=icon-'.$sel.' id=check_'.$item.' title="{LNG_LANGUAGES_COMMENT}"></span>';
    $row .= '<span style="background-image:url('.DATA_URL.'language/'.$item.'.gif)">&nbsp;</span>';
    $row .= '<span>'.$item.'</span>';
    $row .= '</dd>';
    $content[] = $row;
  }
  $content[] = '</dl>';
  $content[] = '</div>';
  // submit
  $content[] = '<div class=submit>';
  $content[] = '<a href="index.php?module=languageadd" class="button add large"><span class=icon-add>{LNG_ADD_NEW} {LNG_LANGUAGE}</span></a>';
  $content[] = '</div>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = 'inintLanguages("languages");';
  $content[] = '});';
  $content[] = '</script>';
  // หน้าปัจจุบัน
  $url_query['module'] = 'languages';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
