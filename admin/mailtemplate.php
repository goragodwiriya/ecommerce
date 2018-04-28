<?php
// admin/mailtemplate.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // คำสั่งที่ทำงานล่าสุด
  $action = trim($_GET['action']);
  // title
  $title = $lng['LNG_EMAIL_TEMPLATE'];
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '{LNG_EMAIL_TEMPLATE}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-email>'.$title.'</h1></header>';
  if (is_dir(ROOT_PATH.'modules/mailmerge/')) {
    // เขียนจดหมายเวียน
    $content[] = '<div class=table_nav>';
    $content[] = '<a class="button add" href="{URLQUERY?module=mailwrite}"><span class=icon-add>{LNG_ADD_NEW} {LNG_EMAIL_TEMPLATE}</span></a>';
    $content[] = '</div>';
  }
  // ตารางข้อมูล
  $content[] = '<table id=email class="tbl_list fullwidth">';
  $content[] = '<caption>{LNG_EMAIL_TEMPLATE_TITLE}</caption>';
  $content[] = '<thead>';
  $content[] = '<tr>';
  $content[] = '<th id=c0 scope=col>{LNG_EMAIL_TEMPLATE_NAME}</th>';
  $content[] = '<th id=c1 scope=col class="center mobile">{LNG_LANGUAGE}</th>';
  $content[] = '<th id=c2 scope=col class="center mobile">{LNG_MODULE}</th>';
  $content[] = '<th id=c3 scope=col class=center>{LNG_EDIT}</th>';
  $content[] = '<th id=c4 scope=col class=center>{LNG_DELETE}</th>';
  $content[] = '</tr>';
  $content[] = '</thead>';
  $content[] = '<tbody>';
  // จดหมายทั้งหมด
  $sql = "SELECT `id`,`email_id`,`module`,`name`,`subject`,`language`";
  $sql .= " FROM `".DB_EMAIL_TEMPLATE."`";
  $sql .= " ORDER BY `module`,`email_id`,`language`";
  foreach ($db->customQuery($sql) AS $item) {
    $id = $item['id'];
    $tr = '<tr id=M_'.$id.'>';
    $tr .= '<th headers=c0 id=r'.$id.' scope=row class=topic>'.($item['module'] == 'mailmerge' ? $item['subject'] : $item['name']).'</th>';
    $tr .= '<td headers="r'.$id.' c1" class="center mobile">'.($item['language'] == '' ? '&nbsp;' : '<img src='.DATA_URL.'language/'.$item['language'].'.gif alt="'.$item['language'].'">').'</td>';
    $tr .= '<td headers="r'.$id.' c2" class="center mobile">'.$item['module'].'</td>';
    $tr .= '<td headers="r'.$id.' c3" class=center><a href="{URLQUERY?module=mailwrite&id='.$id.'}" title="{LNG_EDIT}" class=icon-edit></a></td>';
    $icon = $item['email_id'] == 0 ? '<a id=deletemail_'.$id.' title="{LNG_DELETE}" class=icon-delete></a>' : '&nbsp;';
    $tr .= '<td headers="r'.$id.' c4" class=center>'.$icon.'</td>';
    $tr .= '</tr>';
    $content[] = $tr;
  }
  $content[] = '</tbody>';
  $content[] = '</table>';
  $content[] = '<p class=splitpage>'.$splitpage.'</p>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = "inintTR('email', /M_[0-9]+/);";
  $content[] = "inintList('email', 'a', /deletemail_([0-9]+)/, 'action.php', doFormSubmit, confirmMailDelete);";
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'mailtemplate';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
