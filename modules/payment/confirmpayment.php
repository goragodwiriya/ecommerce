<?php
// modules/payment/confirmpayment.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer
if (gcms::isReferer()) {
  // ค่าที่ส่งมา
  $amount = (double)$_POST['payment_amount'];
  $payment_date = "$_POST[payment_date] $_POST[payment_hour]:$_POST[payment_minute]:00";
  $order_no = $db->sql_trim_str($_POST['payment_orderid']);
  $email = $db->sql_trim_str($_POST['payment_email']);
  // ตรวจสอบค่าที่ส่งมา
  $error = false;
  $input = false;
  if ($email == '') {
    // ไม่ได้กรอกอีเมลหรือโทรศัพท์
    $ret['ret_payment_email'] = 'EMAIL_EMPTY';
    $error = 'EMAIL_EMPTY';
    $input = 'payment_email';
  } else {
    $ret['ret_payment_email'] = '';
  }
  if ($order_no == '') {
    // ไม่ได้กรอกเลขที่ใบสั่งซื้อ
    $error = 'ORDER_NO_EMPTY';
  } else {
    $total = 0;
    $orders = array();
    // เลขที่ใบสั่งซื้อ คั่นแต่ละรายการด้วย ,
    $ordernos = array();
    foreach (explode(',', $order_no) AS $item) {
      $item = trim($item);
      if ($item != '') {
        $ordernos[] = $item;
      }
    }
    $a = sizeof($ordernos);
    $remain = $amount;
    if ($a > 0) {
      // ตรวจสอบรายการที่ต้องการ และ อัปเดตจำนวนเงินที่ชำระในแต่ละรายการ
      $sql = "SELECT O.`id`,O.`order_no`,O.`total`,O.`transport`,O.`discount`,O.`order_status`";
      $sql .= ",U.`fname`,U.`lname`,U.`email`,M.`owner`";
      $sql .= " FROM `".DB_ORDERS."` AS O";
      $sql .= " INNER JOIN `".DB_USER."` AS U ON U.`id`=O.`member_id`";
      $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=O.`module_id`";
      $sql .= " WHERE O.`order_no` IN ('".implode("','", $ordernos)."') AND O.`order_status` < 3";
      foreach ($db->customQuery($sql) AS $item) {
        if ($email == $item['email']) {
          $item['paid'] = $item['total'] + $item['transport'] - $item['discount'];
          $remain = $remain - $item['paid'];
          $orders[] = $item;
          $a--;
          $fname = $item['fname'];
          $lname = $item['lname'];
          $module = $item['owner'];
        }
      }
      if ($a > 0) {
        $error = 'ORDER_NO_ERROR';
      }
    } else {
      $error = 'ORDER_NO_ERROR';
    }
  }
  if ($error) {
    $ret['ret_payment_orderid'] = $error;
    $input = 'payment_orderid';
  } else {
    $ret['ret_payment_orderid'] = '';
  }
  // paid
  if ($amount == 0) {
    $ret['ret_payment_amount'] = 'PAYMENT_PAID_EMPTY';
    $input = !$input ? 'payment_amount' : $input;
    $error = !$error ? 'PAYMENT_PAID_EMPTY' : $error;
  } elseif ($remain < 0) {
    $ret['ret_payment_amount'] = 'PAYMENT_PAID_LESS';
    $input = !$input ? 'payment_amount' : $input;
    $error = !$error ? 'PAYMENT_PAID_LESS' : $error;
  } else {
    $ret['ret_payment_amount'] = '';
  }
  // method
  if (!isset($_POST['payment_method'])) {
    $ret['ret_payment_method'] = 'PAYMENT_METHOD_EMPTY';
    $input = !$input ? 'payment_method_0' : $input;
    $error = !$error ? 'PAYMENT_METHOD_EMPTY' : $error;
  } else {
    $ret['ret_payment_method'] = '';
    $payment_method = stripslashes($config['payments_method'][(int)$_POST['payment_method']][0]);
  }
  // antispam
  if ($_POST['payment_antispam'] != $_SESSION[$_POST['antispam']]) {
    $ret['ret_payment_antispam'] = 'ANTISPAM_INCORRECT';
    $input = !$input ? 'payment_antispam' : $input;
    $error = !$error ? 'ANTISPAM_INCORRECT' : $error;
  } else {
    $ret['ret_payment_antispam'] = '';
  }
  if (!$error) {
    $comment = $db->sql_trim_str($_POST['payment_detail']);
    $ds = array();
    // สำหรับ ตรวจสอบการตัด stock สินค้า
    define('MAIN_INIT', 'confirmpayment');
    $check_stock = is_file(ROOT_PATH.'modules/product/checkstock.php');
    $order_status = 3;
    // อัปเดต order ที่ชำระเงินแล้ว
    foreach ($orders AS $a => $item) {
      $save = array();
      $save['paid'] = $a == 0 ? $item['paid'] + $remain : $item['paid'];
      $save['order_status'] = $order_status;
      $save['payment_date'] = $payment_date;
      $save['last_update'] = $_POST['payment_date'];
      $save['payment_method'] = $payment_method;
      $save['comment'] = $comment;
      $save['payment_ref'] = $db->sql_trim_str($_POST['payment_ref']);
      $save['order'] = '';
      $db->edit(DB_ORDERS, $item['id'], $save);
      $ds[] = $item['order_no'];
      if ($check_stock) {
        // รายละเอียดสินค้าที่สั่งซื้อ
        $sql = "SELECT C.`additional_id`,C.`product_id`,C.`module_id`,C.`quantity`,A.`stock`";
        $sql .= " FROM `".DB_CART."` AS C";
        $sql .= " INNER JOIN `".DB_PRODUCT_ADDITIONAL."` AS A ON A.`id`=C.`additional_id` AND A.`product_id`=C.`product_id` AND A.`module_id`=C.`module_id`";
        $sql .= " WHERE `order_id`='$item[id]'";
        $basket = $db->customQuery($sql);
        // อัปเดต stock
        $last_status = $item['order_status'];
        include ROOT_PATH.'modules/product/checkstock.php';
      }
    }
    // ส่งอีเมลแจ้งการชำระเงิน
    $replace = array();
    $replace['/%FNAME%/'] = $fname;
    $replace['/%LNAME%/'] = $lname;
    $replace['/%IP%/'] = gcms::getip();
    $replace['/%ORDER%/'] = implode(',', $ds);
    $replace['/%COMMENT%/'] = $comment;
    $replace['/%METHOD%/'] = $payment_method;
    $replace['/%DATE%/'] = $db->sql_date2date($payment_date);
    $replace['/%UNIT%/'] = $lng['CURRENCY_UNITS'][$order['currency_unit']];
    $replace['/%PAID%/'] = gcms::int2Curr($amount);
    $err = gcms::sendMail(3, $module, $replace, $item['email']);
    // คืนค่า
    if ($err != '') {
      $ret['alert'] = rawurlencode($err);
    } else {
      $ret['error'] = 'PAYMENT_SUCCESS';
    }
    if (gcms::isMember()) {
      $ret['location'] = rawurlencode(WEB_URL.'/index.php?module=editprofile&tab='.$module);
    } else {
      $ret['location'] = rawurlencode(WEB_URL.'/index.php');
      // clear antispam
      unset($_SESSION[$_POST['antispam']]);
    }
  } else {
    $ret['error'] = $error;
    $ret['input'] = $input;
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
