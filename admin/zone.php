<?php
// admin/zone.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // แก้ไข COUNTRIES_ZONE
  $sql = "SELECT * FROM `".DB_LANGUAGE."` WHERE `key`='COUNTRIES_ZONE' LIMIT 1";
  $language = $db->customQuery($sql);
  $language = sizeof($language) == 0 ? array() : $language[0];
  $save = array();
  // ตรวจสอบข้อมูลเป็นแอเรย์
  if ($language['type'] == 'array') {
    foreach ($config['languages'] AS $k) {
      $array = unserialize(rawurldecode($language[$k]));
      if (is_array($array)) {
        foreach ($array As $k1 => $v1) {
          $save[$k1][$k] = $v1;
        }
      }
    }
  } else {
    $save[''] = $language;
  }
  // title
  $title = "$lng[LNG_ADD]/$lng[LNG_EDIT] $lng[LNG_COUNTRY_ZONE]";
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '<a href="{URLQUERY?module=country}">{LNG_COUNTRY_LIST}</a>';
  $a[] = '{LNG_COUNTRY_ZONE}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-world>'.$title.'</h1></header>';
  // form
  $content[] = '<form id=setup_frm class="setup_frm padding-top" method=post action=index.php>';
  $content[] = '<div class=item>';
  $content[] = '<table class="responsive-v border fullwidth">';
  $content[] = '<thead><tr><th>{LNG_ID}</th><th colspan='.(sizeof($config['languages']) + 1).'>{LNG_COUNTRY_ZONE}</th></tr></thead>';
  $content[] = '<tbody>';
  foreach ($save AS $key => $value) {
    $row = '<tr>';
    $row .= '<td data-text="{LNG_ID}"><label class=g-input><input type=text class=number value="'.$key.'" name=save_array[] placeholder="{LNG_ID}"></label></td>';
    foreach ($config['languages'] AS $l) {
      $row .= '<td data-text='.$l.'><label class=g-input><input type=text name=language_'.$l.'[] value="'.$value[$l].'" style="background-image:url('.DATA_URL.'/language/'.$l.'.gif)" title="{LNG_COUNTRY_ZONE} '.$l.'"></label></td>';
    }
    $row .= '<td class=icons><p><a class=icon-plus title="{LNG_ADD}"></a><a class=icon-minus title="{LNG_DELETE}"></a></p></td>';
    $row .= '</tr>';
    $content[] = $row;
  }
  $content[] = '</tbody>';
  $content[] = '</table>';
  $content[] = '<div class=comment>{LNG_SELECT_COMMENT}</div>';
  $content[] = '</div>';
  // submit
  $content[] = '<div class=submit>';
  $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
  $content[] = '<input type=hidden name=save_id value='.(int)$language['id'].'>';
  $content[] = '<input type=hidden name=save_key value=COUNTRIES_ZONE>';
  $content[] = '<input type=hidden name=save_type value=array>';
  $content[] = '<input type=hidden name=save_js value=0>';
  $content[] = '<input type=hidden name=save_owner value=sysadmin>';
  $content[] = '<input type=hidden name=languageedit value=1>';
  $content[] = '</div>';
  $content[] = '</form>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = 'new GForm("setup_frm", "language_save.php").onsubmit(doFormSubmit);';
  $content[] = 'inintPMTable("setup_frm");';
  $content[] = '});';
  $content[] = '</script>';
  // หน้าปัจจุบัน
  $url_query['module'] = 'zone';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
