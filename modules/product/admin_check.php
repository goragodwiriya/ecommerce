<?php
// modules/product/admin_check.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, can_write
if (gcms::isReferer() && gcms::canConfig($config['product_can_write'])) {
  $id = (int)$_POST['id'];
  $action = $_POST['action'];
  $val = addslashes($db->sql_trim_str($_POST['val']));
  if ($action == 'productno') {
    $sql = "SELECT `id` FROM `".DB_PRODUCT."` WHERE `product_no`='$val' AND `id`!='$id'LIMIT 1";
    $search = $db->customQuery($sql);
    if (sizeof($search) > 0) {
      echo 'PRODUCT_NO_EXISTS';
    }
  } elseif ($action == 'alias') {
    $sql = "SELECT `id` FROM `".DB_PRODUCT."` WHERE `alias`='$val' AND `id`!='$id'LIMIT 1";
    $search = $db->customQuery($sql);
    if (sizeof($search) > 0) {
      echo 'ALIAS_EXISTS';
    }
  }
}
