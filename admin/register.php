<?php
// admin/register.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // title
  $title = $lng['LNG_REGISTER_TITLE'];
  // แสดงผล
  $patt2 = array('/{STATUS}/');
  $replace2 = array();
  $statuses = array();
  foreach ($config['member_status'] AS $i => $value) {
    $statuses[] = '<option value='.$i.$sel.'>'.$value.'</option>';
  }
  $replace2[] = implode('', $statuses);
  $content[] = preg_replace($patt2, $replace2, gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/register.html"));
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
