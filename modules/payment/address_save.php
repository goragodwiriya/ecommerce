<?php
// modules/payment/address_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer
if (gcms::isReferer()) {
  $ret = array();
  $user = false;
  $index = false;
  $login_id = (int)$_SESSION['login']['id'];
  // ตรวจสอบ สมาชิกที่ทำรายการอยู่ (login)
  if ($login_id > 0) {
    $user = $db->getRec(DB_USER, $login_id);
  }
  $module = strtolower($db->sql_trim_str($_POST['module']));
  if (preg_match('/^[a-z0-9]+$/', $module) && is_file(ROOT_PATH."modules/$module/confirmorder.php")) {
    $sql = "SELECT `id` AS `module_id` FROM `".DB_MODULES."` WHERE `owner`='$module' LIMIT 1";
    $index = $db->customQuery($sql);
    $index = sizeof($index) == 1 ? $index[0] : false;
  }
  if (!$user || !$index) {
    // ไม่พบสมาชิกที่เลือก หรือ ไม่ได้ login หรือ $module ไม่ถูกต้อง
    $ret['error'] = 'ACTION_ERROR';
    $ret['location'] = rawurlencode(WEB_URL.'/index.php');
  } else {
    // ค่าที่ส่งมา
    $user['fname'] = $db->sql_trim_str($_POST['product_fname']);
    $user['lname'] = $db->sql_trim_str($_POST['product_lname']);
    $user['phone1'] = $db->sql_trim_str($_POST['product_phone1']);
    $user['phone2'] = $db->sql_trim_str($_POST['product_phone2']);
    $user['address1'] = $db->sql_trim_str($_POST['product_address1']);
    $user['address2'] = $db->sql_trim_str($_POST['product_address2']);
    $user['province'] = $db->sql_trim_str($_POST['product_province']);
    $user['zipcode'] = $db->sql_trim_str($_POST['product_zipcode']);
    $user['country'] = $db->sql_trim_str($_POST['product_country']);
    foreach (array('zipcode', 'province', 'address1', 'phone1', 'lname', 'fname') AS $input) {
      if ($user[$input] == '') {
        $ret['input'] = "product_$input";
        $ret['error'] = 'DO_NOT_EMPTY';
        $ret["ret_product_$input"] = 'DO_NOT_EMPTY';
      } else {
        $ret["ret_product_$input"] = '';
      }
    }
    if (!isset($ret['error'])) {
      // อัปเดตข้อมูล
      $db->edit(DB_USER, $user['id'], $user);
      // อัปเดตสั่งซื้อสินค้า
      define('MAIN_INIT', 'confirmorder');
      // ยืนยันการสั่งซื้อที่โมดูล
      include (ROOT_PATH."modules/$module/confirmorder.php");
    }
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
