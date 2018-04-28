<?php
// modules/payment/login.php
if (defined('MAIN_INIT')) {
  $user = array();
  // module ที่ทำรายการ
  $module = $db->sql_trim_str($_REQUEST['ret']);
  if (preg_match('/^[a-z0-9]+$/', $module)) {
    // ตรวจสอบโมดูล และ ตะกร้าสินค้า
    $login_id = (int)$_SESSION['login']['id'];
    $session_id = session_id();
    $sql = "SELECT COUNT(*) FROM `".DB_CART."`";
    $sql .= " WHERE (`session_id`='$session_id' OR (`member_id`='$login_id' AND '$login_id'>0)) AND `order_id`='0' AND `module_id`=M.`id`";
    $sql = "SELECT M.`id` AS `module_id`,M.`owner`,($sql) AS `count` FROM `".DB_MODULES."` AS M WHERE M.`owner`='$module' LIMIT 1";
    $index = $db->customQuery($sql);
    if (sizeof($index) == 1 && $index[0]['count'] > 0) {
      $index = $index[0];
      if ($login_id > 0) {
        $sql = "SELECT U.*,(SELECT `printable_name` FROM `".DB_COUNTRY."` WHERE `iso`=U.`country` LIMIT 1) AS `printable_name`";
        $sql .= " FROM `".DB_USER."` AS U WHERE U.`id`=$login_id LIMIT 1";
        $user = $db->customQuery($sql);
      }
      if (sizeof($user) == 1) {
        $user = $user[0];
        // ตวจสอบที่อยู่
        include ROOT_PATH.'modules/payment/address.php';
      } else {
        // ไม่ได้ login ไปหน้า sigin
        include ROOT_PATH.'modules/payment/sigin.php';
      }
    } else {
      $title = $lng['LNG_CART_EMPTY'];
      $content = '<div class=error>'.$title.'</div>';
    }
  } else {
    $title = $lng['PAGE_NOT_FOUND'];
    $content = '<div class=error>'.$title.'</div>';
  }
}
