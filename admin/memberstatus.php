<?php
// admin/memberstatus.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // title
  $title = $lng['LNG_MEMBER_STATUS_SETTINGS'];
  $a = array();
  $a[] = '<span class=icon-users>{LNG_USERS}</span>';
  $a[] = '{LNG_MEMBER_STATUS}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-config>'.$title.'</h1></header>';
  $content[] = '<div class=subtitle>{LNG_MEMBER_STATUS_COMMENT}</div>';
  // สถานะสมาชิก
  $content[] = '<dl id=config_status class=editinplace_list>';
  if (is_array($config['member_status'])) {
    foreach ($config['member_status'] AS $i => $item) {
      $row = '<dd id=config_status_'.$i.'>';
      if ($i < 2) {
        $row .= '<span></span>';
      } else {
        $row .= '<span class=icon-delete id=config_status_delete_'.$i.' title="{LNG_DELETE} {LNG_MEMBER_STATUS}"></span>';
      }
      $row .= '<span id=config_status_color_'.$i.' title="'.$config['color_status'][$i].'"></span>';
      $row .= '<span id=config_status_name_'.$i.' title="{LNG_CLICK_TO} {LNG_EDIT}">'.htmlspecialchars($item).'</span>';
      $row .= '</dd>';
      $content[] = $row;
    }
  }
  $content[] = '</dl>';
  // submit
  $content[] = '<div class=submit>';
  $content[] = '<a id=config_status_add class="button add large"><span class=icon-add>{LNG_ADD_NEW} {LNG_MEMBER_STATUS}</span></a>';
  $content[] = '</div>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = 'var CHANGE_COLOR_STATUS = "{LNG_CHANGE_COLOR_STATUS}";';
  $content[] = '$G(window).Ready(function(){';
  $content[] = "inintMemberStatus('config_status');";
  $content[] = '$E("config_status_color_0").focus();';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'memberstatus';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
