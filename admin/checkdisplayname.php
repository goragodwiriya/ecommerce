<?php
// admin/checkdisplayname.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// referer
if (gcms::isReferer()) {
  $id = (int)$_POST['id'];
  $value = $db->sql_trim_str($_POST['value']);
  // ตรวจสอบชื่อสมาชิก
  if ($value != '') {
    // ตรวจสอบชื่อซ้ำ
    $sql = "SELECT `id` FROM `".DB_USER."` WHERE `displayname`='".addslashes($value)."' LIMIT 1";
    $search = $db->customQuery($sql);
    if (sizeof($search) == 1 && ($id == 0 || $id != $search[0]['id'])) {
      echo 'NAME_EXISTS';
    }
  }
}
