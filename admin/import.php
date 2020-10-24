<?php
// admin/import.php
header("content-type: text/html; charset=UTF-8");
// inint
include ('../bin/inint.php');
// ไฟล์ที่ส่งมา
$file = $_FILES['import_file'];
// แอดมินเท่านั้น
if (gcms::isReferer() && gcms::isAdmin() && $file['tmp_name'] != '') {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // long time
    set_time_limit(0);
    // อัปโหลด
    $fr = file($file['tmp_name']);
    // query ทีละบรรทัด
    foreach ($fr AS $value) {
      $sql = str_replace(array('\r', '\n', '{prefix}', '/{WEBMASTER}/'), array("\r", "\n", PREFIX, $_SESSION['login']['email']), trim($value));
      if ($sql != '') {
        $db->query($sql);
      }
    }
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
