<?php
// modules/product/admin_view_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// ตรวจสอบ referer และ salesmain
if (gcms::isReferer() && gcms::canConfig($config['product_salesman'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // ตรวจสอบ order ที่เลือก
    $sql = "SELECT O.`id`,O.`order_no`,O.`member_id`,O.`tax`,O.`vat`,O.`total`,O.`transport`,O.`currency_unit`";
    $sql .= ",O.`order_status`,O.`paid`,O.`order`";
    $sql .= " FROM `".DB_ORDERS."` AS O WHERE O.`id`=".(int)$_POST['order_id']." LIMIT 1";
    $order = $db->customQuery($sql);
    if (sizeof($order) == 1) {
      $order = $order[0];
      // ตรวจสอบลูกค้า
      $sql = "SELECT `fname`,`lname`,`email`,`country` AS `iso` FROM `".DB_USER."` WHERE `id`='$order[member_id]' LIMIT 1";
      $user = $db->customQuery($sql);
      $user = sizeof($user) == 1 ? $user[0] : false;
      // ตรวจสอบค่าที่ส่งมา
      $error = false;
      $input = false;
      // อัปเดตรายการสินค้า
      $cart = array();
      if (isset($_POST['cart_id'])) {
        foreach ($_POST['cart_id'] AS $k => $v) {
          $id = (int)$v;
          $save = array();
          $save['topic'] = $db->sql_trim_str($_POST['cart_topic'][$k]);
          $save['quantity'] = (int)$_POST['cart_quantity'][$k];
          $save['net'] = (double)$_POST['cart_price'][$k];
          if ($save['topic'] == '') {
            $error = 'PRODUCT_TOPIC_EMPTY';
            $input = "cart_topic_$id";
            $ret["ret_cart_topic_$id"] = 'PRODUCT_TOPIC_EMPTY';
          } else {
            $cart[$id] = $save;
          }
        }
      }
      if (!$error) {
        if (sizeof($cart) > 0) {
          // update cart
          foreach ($cart AS $id => $item) {
            $db->edit(DB_CART, $id, $item);
          }
        }
        // หน่วยเงิน
        $currency = $order['currency_unit'];
        $currency_unit = $lng['CURRENCY_UNITS'][$currency];
        // โหลดรายการทั้งหมดในตะกร้า
        $sql = "SELECT * FROM `".DB_CART."`";
        $sql .= " WHERE `order_id`='$order[id]' ORDER BY `id` DESC";
        $total = 0;
        $tax = 0;
        $vat = 0;
        $quantity = 0;
        $weight = 0;
        // ข้อความรายละเอียดสินค้าที่จะส่งอีเมล
        $details = array();
        foreach ($db->customQuery($sql) AS $item) {
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
          // รายละเอียดสินค้า
          $details[] = '<tr><td>'.$item['product_no'].'</td><td class=left>'.$item['topic'].'</td><td>'.$item['quantity'].'</td><td class=right>'.gcms::int2Curr($amount).' '.$currency_unit.'</td></tr>';
          // ตะกร้าสินค้า
          $basket[] = $item;
        }
        // สถานะการทำรายการเดิม
        $last_status = $order['order_status'];
        // สถานะใหม่
        $order_status = (int)$_POST['order_status'];
        // ตรวจสอบการตัด stock สินค้า
        define('MAIN_INIT', 'admin_view_save');
        include ROOT_PATH.'modules/product/checkstock.php';
        // อัปเดต order
        $order['last_update'] = $db->sql_trim($_POST['last_update']).date(' H:i:s', $mmktime);
        $order['order_status'] = $order_status;
        $order['comment'] = $db->sql_trim_str($_POST['order_comment']);
        $order['postcode'] = $db->sql_trim_str($_POST['order_postcode']);
        $order['weight'] = $weight;
        $order['discount'] = (double)$_POST['order_discount'];
        $order['total'] = $total;
        $db->edit(DB_ORDERS, $order['id'], $order);
        // send email
        if ((int)$_POST['order_sendmail'] == 1 && sizeof($details) > 0 && $user) {
          $details = preg_replace('/^(<tr><td>)(.*)(<\/td><\/tr>)$/', '\\2', implode('', $details));
          // รายละเอียดสินค้า
          $replace = array();
          $replace['/%FNAME%/'] = $user['fname'];
          $replace['/%LNAME%/'] = $user['lname'];
          $replace['/%ORDER%/'] = $order['order_no'];
          $replace['/%DETAIL%/'] = $details;
          $replace['/%DISCOUNT%/'] = gcms::int2Curr($order['discount']);
          $replace['/%WEIGHT%/'] = number_format($order['weight']);
          $replace['/%TRANSPORT%/'] = gcms::int2Curr($order['transport']);
          $replace['/%PRICE%/'] = gcms::int2Curr($order['total']);
          $replace['/%TAX%/'] = gcms::int2Curr($order['tax']);
          $replace['/%VAT%/'] = gcms::int2Curr($order['vat']);
          $replace['/%TOTAL%/'] = gcms::int2Curr($order['total'] + $order['transport'] + $order['tax'] + $order['vat'] - $order['discount']);
          $replace['/%UNIT%/'] = $currency_unit;
          $replace['/%URL%/'] = WEB_URL."/index.php?module=payment".($order['order_status'] < 3 && $order['order'] != '' ? "&amp;order=$order[order]" : '');
          $replace['/%IP%/'] = gcms::getip();
          $replace['/%COMMENT%/'] = $order['comment'];
          $replace['/%SHIPPING%/'] = $order['transport_method'];
          $replace['/%DATE%/'] = gcms::mktime2date($db->sql_datetime2mktime($order['payment_date']));
          $replace['/%EMS%/'] = $order['postcode'];
          $replace['/%PAID%/'] = gcms::int2Curr($order['paid']);
          $err = gcms::sendMail($order['order_status'], 'product', $replace, $user['email']);
          if ($err != '') {
            $ret['alert'] = rawurlencode($err);
          } else {
            $ret['error'] = 'SAVE_AND_EMAIL_SUCCESS';
          }
        } else {
          $ret['error'] = 'ORDER_UPDATE_SUCCESS';
        }
        $ret['location'] = 'reload';
      } else {
        $ret['error'] = $error;
        if ($input) {
          $ret['input'] = $input;
        }
      }
    } else {
      $ret['error'] = 'ACTION_ERROR';
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่า JSON
echo gcms::array2json($ret);
