<?php
// modules/product/confirmorder.php
if (MAIN_INIT == 'confirmorder' && is_array($user)) {
  $login_id = $user['id'];
  // หน่วยเงิน
  $currency = $_SESSION['currency'];
  // บันทึกข้อมูลตะกร้าสินค้า
  $s = true;
  while ($s) {
    // รหัส order ต้องไม่ซ้ำกัน
    $orderno = gcms::rndname(32);
    $s = $db->basicSearch(DB_ORDERS, 'order', $orderno);
  }
  // ตรวจสอบตะกร้าสินค้า
  $basket = array();
  $session_id = session_id();
  include ROOT_PATH.'modules/product/loadbasket.php';
  $total = 0;
  $tax = 0;
  $vat = 0;
  $quantity = 0;
  $weight = 0;
  // ข้อความรายละเอียดสินค้าที่จะส่งอีเมล
  $details = array();
  foreach ($basket AS $item) {
    // จำนวนเงิน
    $amount = ($item['quantity'] * $item['net']);
    // ราคารวม
    $total = $total + $amount;
    // tax
    $tax += ($amount * $item['tax']) / 100;
    // vat
    $vat += ($amount * $item['vat']) / 100;
    // จำนวนสินค้า(ชิ้น)รวม
    $quantity += $item['quantity'];
    // น้ำหนักรวม
    $weight += ($item['quantity'] * $item['weight']);
    // ค่าขนส่ง(ต่อชิ้น)รวม
    $shipping += ($item['quantity'] * $item['transportation']);
    // รายละเอียดสินค้า
    $details[] = '<tr><td>'.$item['product_no'].'</td><td class=left>'.$item['topic'].'</td><td>'.$item['quantity'].'</td><td class=right>'.$amount.' %UNIT%</td></tr>';
  }
  // ตรวจสอบการตัด stock สินค้า
  $order_status = 1;
  $last_status = 0;
  include ROOT_PATH.'modules/product/checkstock.php';
  // save order
  $order = array();
  $order['module_id'] = $index['module_id'];
  $order['member_id'] = $login_id;
  $order['order_date'] = $db->sql_mktimetodate($mmktime);
  $order['last_update'] = $order['order_date'];
  $order['create_date'] = $mmktime;
  $order['order'] = $orderno;
  $order['order_status'] = $order_status;
  $order['currency_unit'] = $currency;
  $order['discount'] = max(0, (double)$config['product_discount'] * $total / 100);
  $order['total'] = $total;
  $order['id'] = $db->lastId(DB_ORDERS);
  $order['order_no'] = sprintf($config['product_order_no'], $order['id']);
  $db->add(DB_ORDERS, $order);
  // อัปเดตว่าสั่งซื้อแล้ว
  $sql = "UPDATE `".DB_CART."`";
  $sql .= " SET `session_id`='',`order_id`='$order[id]',`member_id`='$login_id'";
  $sql .= " WHERE (`session_id`='$session_id' OR (`member_id`=$login_id AND $login_id>0)) AND `module_id`='$index[module_id]' AND `order_id`=0";
  $db->query($sql);
  // ส่งอีเมลแจ้งการสั่งซื้อ
  if (sizeof($details) > 0 && $config['sendmail'] == 1) {
    $details = preg_replace('/^(<tr><td>)(.*)(<\/td><\/tr>)$/', '\\2', implode('', $details));
    // รายละเอียดสินค้า
    $replace = array();
    $replace['/%FNAME%/'] = $user['fname'];
    $replace['/%LNAME%/'] = $user['lname'];
    $replace['/%ORDER%/'] = $order['order_no'];
    $replace['/%DETAIL%/'] = $details;
    $replace['/%DISCOUNT%/'] = gcms::int2Curr($order['discount']);
    $replace['/%WEIGHT%/'] = $weight;
    $replace['/%TRANSPORT%/'] = gcms::int2Curr($order['transport']);
    $replace['/%PRICE%/'] = gcms::int2Curr($order['total']);
    $replace['/%TOTAL%/'] = gcms::int2Curr($order['total'] + $order['transport'] - $order['discount']);
    $replace['/%UNIT%/'] = $lng['CURRENCY_UNITS'][$currency];
    $replace['/%URL%/'] = WEB_URL."/index.php?module=payment&amp;order=$order[order]";
    $replace['/%IP%/'] = gcms::getip();
    $err = gcms::sendMail(1, 'product', $replace, $user['email']);
  }
  if ($err != '') {
    $ret['alert'] = rawurlencode($err);
  } else {
    $ret['error'] = 'PAYMENT_REGISTER_SUCCESS';
  }
  // ไปหน้าชำระเงิน
  $ret['url'] = rawurlencode(WEB_URL."/index.php?module=payment&id=$order[id]");
} else {
  // ข้อมูลไม่ถูกต้อง
  $ret['error'] = 'ACTION_ERROR';
  $ret['location'] = rawurlencode(WEB_URL.'/index.php');
}
