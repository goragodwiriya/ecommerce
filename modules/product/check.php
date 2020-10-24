<?php
// modules/product/check.php
header("content-type: text/html; charset=UTF-8");
// inint
include ('../../bin/inint.php');
// referer, member
if (gcms::isReferer() && gcms::isMember()) {
  // ค่าที่ส่งมา
  $action = $_POST['action'];
  $value = $db->sql_trim_str($_POST['val']);
  $id = (int)$_POST['id'];
  // ตรวจสอบค่าที่ส่งมา
  if ($action == 'topic') {
    // ค้นหาชื่อเรื่องซ้ำ
    $sql = "SELECT `id` FROM `".DB_PRODUCT_DETAIL."` WHERE `topic`='$value' LIMIT 1";
    $search = $db->customQuery($sql);
    if (sizeof($search) > 0 && ($id == 0 || $id != $search[0]['id'])) {
      echo 'TOPIC_EXISTS';
    }
  } elseif ($action == 'productno') {
    // ค้นหา product_no ซ้ำ
    $sql = "SELECT `id` FROM `".DB_PRODUCT_DETAIL."` WHERE `product_no`='$value' LIMIT 1";
    $search = $db->customQuery($sql);
    if (sizeof($search) > 0 && ($id == 0 || $id != $search[0]['id'])) {
      echo 'PRODUCT_PRODUCT_NO_EXISTS';
    }
  }
}