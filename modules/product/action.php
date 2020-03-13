<?php
// modules/product/action.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer
if (gcms::isReferer()) {
  // ค่าที่ส่งมา
  $order_id = (int)$_POST['id'];
  $action = $_POST['action'];
  $login_id = (int)$_SESSION['login']['id'];
  // หน่วยเงิน
  $currency = $_SESSION['currency'];
  $currency_unit = $lng['CURRENCY_UNITS'][$currency];
  $session_id = session_id();
  $ret = array();
  $typies = array();
  $cart = array();
  $basket = array();
  $total = 0;
  $transport = 0;
  $items = 0;
  define('MAIN_INIT', 'basket');
  if ($action == 'removecart' && $order_id > 0) {
    // ลบสินค้าในตะกร้า
    $sql = "SELECT `id`,`module_id`";
    $sql .= " FROM `".DB_CART."`";
    $sql .= " WHERE `id`='$order_id' AND ((`session_id`!='' AND `session_id`='$session_id') OR `member_id`='$login_id') LIMIT 1";
    $index = $db->customQuery($sql);
    if (sizeof($index) == 1) {
      $index = $index[0];
      // ลบ
      $db->delete(DB_CART, $index['id']);
      // ตรวจสอบตะกร้าสินค้า
      include ROOT_PATH.'modules/product/loadbasket.php';
      foreach ($basket AS $item) {
        $total += $item['quantity'] * $item['net'];
        $items += $item['quantity'];
      }
      if ($items == 0) {
        $ret['error'] = 'CART_EMPTY';
      }
      $discount = max(0, ((double)$config['product_discount'] * $total) / 100);
      // ส่งค่ากลับ
      $ret['remove1'] = 'cart_tr_'.$index['id'];
      $ret['remove2'] = 'checkout_tr_'.$index['id'];
      $ret['cart_total'] = rawurlencode(gcms::int2Curr($total));
      $ret['checkout_total'] = $ret['cart_total'];
      $ret['cart_items'] = $items;
      $ret['checkout_amount'] = gcms::int2Curr($total + (double)$config['product_transportation'] - $discount);
      $ret['checkout_discount'] = gcms::int2Curr($discount);
    } else {
      $ret['error'] = 'ID_NOT_FOUND';
    }
  } elseif ($action == 'quantity' && $order_id > 0) {
    // อัปเดตจำนวนสินค้า
    $sql = "SELECT C.`id`,C.`module_id`,A.`stock`";
    $sql .= " FROM `".DB_CART."` AS C";
    $sql .= " INNER JOIN `".DB_PRODUCT."` AS P ON P.`id`=C.`product_id` AND P.`module_id`=C.`module_id`";
    $sql .= " INNER JOIN `".DB_PRODUCT_ADDITIONAL."` AS A ON A.`product_id`=C.`product_id` AND A.`id`=C.`additional_id`";
    $sql .= " WHERE C.`id`='$order_id' AND (C.`session_id`='$session_id' OR C.`member_id`='$login_id') LIMIT 1";
    $index = $db->customQuery($sql);
    if (sizeof($index) == 1) {
      $index = $index[0];
      // อัปเดต จำนวนสินค้าที่ต้องการ มากกว่า 1
      $my_quantity = max(1, (int)$_POST['value']);
      // ตรวจสอบตะกร้าสินค้า
      include ROOT_PATH.'modules/product/loadbasket.php';
      foreach ($basket AS $item) {
        $total += ($item['quantity'] * $item['net']);
        $items += $item['quantity'];
        $weight += ($item['quantity'] * $item['weight']);
        if ($order_id == $item['id']) {
          $ret['cart_price_'.$order_id] = rawurlencode(gcms::int2Curr($item['quantity'] * $item['net']));
          $ret['checkout_price_'.$order_id] = $ret['cart_price_'.$order_id];
          $ret['cart_quantity_'.$order_id] = $my_quantity;
          $ret['checkout_quantity_'.$order_id] = $my_quantity;
          if (isset($ret['alert'])) {
            $ret['input'] = 'checkout_quantity_'.$order_id;
            $ret['input'] = 'cart_quantity_'.$order_id;
          }
        }
      }
      $ret['checkout_amount'] = gcms::int2Curr($total);
      $ret['cart_total'] = $ret['checkout_amount'];
      $ret['checkout_total'] = $ret['checkout_amount'];
      $ret['cart_items'] = $items;
      $ret['checkout_weight'] = number_format($weight);
    }
  } elseif ($action == 'cancleorder') {
    // ลบ order
    if ($order_id == 0) {
      // ลบตะกร้าสินค้าที่ยังไม่ได้สั่งซื้อ)
      $sql = "DELETE FROM `".DB_CART."` WHERE (`session_id`='".session_id()."' OR (`member_id`='".(int)$_SESSION['login']['id']."' AND `member_id`>0))";
      $sql .= " AND `order_id`='0' AND `module_id`=(SELECT `id` FROM `".DB_MODULES."` AS M WHERE M.`owner`='product' LIMIT 1)";
      $index = $db->query($sql);
      // คืนค่า
      $ret['error'] = 'DELETE_SUCCESS';
      $ret['location'] = rawurlencode(WEB_URL.'/index.php');
    } else {
      $order = $db->getRec(DB_ORDERS, $order_id);
      if ($order && $order['member_id'] = $login_id && $order['order_status'] < 3) {
        $db->query("DELETE FROM `".DB_CART."` WHERE `order_id`='$order[id]' AND `member_id`='$login_id'");
        $db->delete(DB_ORDERS, $order['id']);
        // คืนค่า
        $ret['error'] = 'DELETE_SUCCESS';
        $ret['location'] = rawurlencode(WEB_URL.'/index.php?module=editprofile&tab=product');
      }
    }
  } elseif ($_POST['updatedetail'] == 1) {
    // อัปเดตข้อความ
    $comment = $db->sql_trim_str($_POST['cart_detail']);
    $db->edit(DB_ORDERS, $_POST['order_id'], array('comment' => $comment));
    $ret['error'] = 'SAVE_COMPLETE';
  } elseif (preg_match('/^(quote|edit|delete|deleting)-([0-9]+)-([0-9]+)-([0-9]+)-(.*)$/', $_POST['id'], $match)) {
    list($action, $match[2], $match[3], $match[4], $match[5]) = explode('-', $_POST['id']);
    // ตรวจสอบความคิดเห็นที่เลือก
    $sql = "SELECT C.`id`,C.`detail`,C.`member_id`,U.`status`,M.`id` AS `module_id`,M.`module`";
    $sql .= " FROM `".DB_COMMENT."` AS C";
    $sql .= " INNER JOIN `".DB_PRODUCT."` AS Q ON Q.`id`='$match[2]' AND Q.`id`=C.`index_id` AND Q.`module_id`=C.`module_id`";
    $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id` AND M.`owner`='product' AND M.`module`='$match[5]'";
    $sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=C.`member_id`";
    $sql .= " WHERE C.`id`='$match[3]'";
    $sql .= " LIMIT 1";
    $index = $db->customQuery($sql);
    $ret = array();
    if (sizeof($index) == 0) {
      $ret['error'] = 'ACTION_ERROR';
    } else {
      $index = $index[0];
      // ผู้ดูแล,เจ้าของเรื่อง (ลบ-แก้ไข ได้)
      $moderator = gcms::canConfig($config['product_moderator']);
      $moderator = $login_id > 0 && ($moderator || $index['member_id'] == $login_id);
      if ($match[1] == 'quote') {
        // อ้างอิง
        if ($index['detail'] == '') {
          $ret['detail'] = '';
        } else {
          $ret['detail'] = rawurlencode("[quote r=$match[4]]".gcms::txtQuote($index['detail'], true).'[/quote]');
        }
      } elseif ($match[1] == 'delete' && $login_id > 0) {
        // สามารถลบได้ (mod=ลบ,สมาชิก=แจ้งลบ)
        if ($moderator || $index['member_id'] == $login_id) {
          // ลบ
          $ret['confirm'] = 'CONFIRM_DELETE_COMMENT';
          $match[1] = 'deleting';
        } elseif (defined('DB_PM')) {
          // แจ้งลบ
          $ret['confirm'] = 'CONFIRM_SEND_DELETE_COMMENT';
          $ret['url'] = rawurlencode(WEB_URL."/index.php?module=-$match[5]&amp;id=-$match[2]#R_-$match[3]");
          $ret['topic'] = '';
          $match[1] = "senddelete-$match[2]-$match[3]-$match[4]-$match[5]";
        }
      } elseif ($match[1] == 'deleting' && $moderator) {
        // ลบความคิดเห็น, mod หรือ เจ้าของ
        $db->delete(DB_COMMENT, $match[3]);
        // อัปเดตจำนวนคำตอบของคำถาม
        $sql = "UPDATE `".DB_PRODUCT."`";
        $sql .= " SET `comments`=(";
        $sql .= "SELECT COUNT(*) FROM `".DB_COMMENT."` WHERE `index_id`='$match[2]' AND `module_id`='$index[module_id]'";
        $sql .= ") WHERE `id`='$match[2]' LIMIT 1";
        $db->query($sql);
        $ret['remove'] = "R_$match[3]";
      } elseif ($match[1] == 'edit' && $moderator) {
        // แก้ไข mod หรือ เจ้าของ
        $ret['location'] = WEB_URL."/index.php?module=$match[5]-edit&id=$match[3]";
      }
      $ret['action'] = $match[1];
    }
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
