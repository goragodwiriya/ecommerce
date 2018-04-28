<?php
// modules/product/basket.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer
if (gcms::isReferer() && preg_match('/([0-9]+)_([0-9]+)/', $_POST['aid'], $match)) {
  $ret = array();
  // ลิสต์รายการ
  $basket = array();
  // จำนวนสินค้ารวม
  $items = 0;
  // จำนวนเงินรวม
  $total = 0;
  // น้ำหนักรวม
  $weight = 0;
  // ค่าขนส่งต่อชิ้นรวม
  $shipping = 0;
  // ภาษีหัก ณ ที่จ่่าย
  $tax = 0;
  // ภาษีมูลค่าเพิ่ม
  $vat = 0;
  // login
  $login_id = (int)$_SESSION['login']['id'];
  // session
  $session_id = session_id();
  // หน่วยเงิน
  $currency = $_SESSION['currency'];
  $currency_unit = $lng['CURRENCY_UNITS'][$currency];
  // ค่าที่ส่งมา
  $product_id = (int)$match[1];
  $additional_id = (int)$match[2];
  $product_exists = false;
  // ตรวจสอบสินค้าที่เลือก
  $sql = "SELECT P.`module_id`,P.`product_no`,A.`product_id`,D.`topic`";
  $sql .= ",A.`id` AS `additional_id`,A.`price_$currency` AS `price`,A.`net_$currency` AS `net`,A.`weight`,A.`stock`,A.`topic` AS `detail`";
  $sql .= " FROM `".DB_PRODUCT_ADDITIONAL."` AS A";
  $sql .= " INNER JOIN `".DB_PRODUCT."` AS P ON P.`id`=A.`product_id`";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=P.`module_id` AND M.`owner`='product'";
  $sql .= " INNER JOIN `".DB_PRODUCT_DETAIL."` AS D ON D.`id`=P.`id`";
  $sql .= " WHERE A.`product_id`=$product_id AND A.`id`=$additional_id";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 0) {
    $ret['error'] = 'ID_NOT_FOUND';
  } else {
    $index = $index[0];
    $index['topic'] = trim($index['topic'].' '.$index['detail']);
    unset($index['detail']);
    // ตรวจสอบตะกร้าสืนค้า
    define('MAIN_INIT', 'basket');
    include ROOT_PATH.'modules/product/loadbasket.php';
    $tr = '';
    foreach ($basket AS $item) {
      $id = $item['id'];
      if ($item['product_id'] == $product_id && $item['additional_id'] == $additional_id) {
        // สินค้าในตะกร้า
        $ret['highlight'] = 'cart_tr_'.$item['id'];
        if ($item['alert'] == '') {
          $ret['error'] = 'PRODUCT_CART_SUCCESS';
        } else {
          $ret['alert'] = $item['alert'];
        }
      }
      // จำนวนเงิน
      $amount = ($item['quantity'] * $item['net']);
      // ราคารวม
      $total = $total + $amount;
      // tax
      $tax += ($amount * $item['tax']) / 100;
      // vat
      $vat += ($amount * $item['vat']) / 100;
      // จำนวนสินค้า(ชิ้น)รวม
      $items += $item['quantity'];
      // น้ำหนักรวม
      $weight += ($item['quantity'] * $item['weight']);
      // ค่าขนส่ง(ต่อชิ้น)รวม
      $shipping += ($item['quantity'] * $item['transportation']);
      // คืนค่ารายการ
      $tr .= '<tr id="cart_tr_'.$id.'">';
      $tr .= '<td><label><input id="cart_quantity_'.$id.'" type="text" size="1" value="'.$item['quantity'].'" /></label></td>';
      $tr .= '<td class="cart_topic"><span class="cuttext" title="'.$item['topic'].'">'.$item['topic'].'</span></td>';
      $tr .= '<td class="right nowrap"><span id="cart_price_'.$id.'">'.gcms::int2Curr($amount).'</span> '.$currency_unit.'</td>';
      $tr .= '<td><a class="icon-delete" id="cart_delete_'.$id.'"></a></td>';
      $tr .= '</tr>';
    }
    if (!isset($ret['highlight']) && $index['stock'] != 0) {
      // add cart
      $index['quantity'] = 1;
      $index['order_id'] = 0;
      $index['create_date'] = $mmktime;
      $index['member_id'] = $login_id;
      $index['session_id'] = $session_id;
      $index['currency_unit'] = $currency;
      unset($index['id']);
      unset($index['stock']);
      $id = $db->add(DB_CART, $index);
      // จำนวนเงิน
      $amount = $index['net'];
      // ราคารวม
      $total = $total + $amount;
      // tax
      $tax += ($amount * $index['tax']) / 100;
      // vat
      $vat += ($amount * $index['vat']) / 100;
      // จำนวนสินค้า(ชิ้น)รวม
      $items++;
      // น้ำหนักรวม
      $weight += $item['weight'];
      // ค่าขนส่ง(ต่อชิ้น)รวม
      $shipping += $item['transportation'];
      // รายการใหม่
      $tr .= '<tr id="cart_tr_'.$id.'">';
      $tr .= '<td><label><input id="cart_quantity_'.$id.'" type="text" size="1" value="1" /></label></td>';
      $tr .= '<td class="cart_topic"><span class="cuttext" title="'.$index['topic'].'">'.$index['topic'].'</span></td>';
      $tr .= '<td class="right nowrap"><span id="cart_price_'.$id.'">'.gcms::int2Curr($index['net']).'</span> '.$currency_unit.'</td>';
      $tr .= '<td><a class="icon-delete" id="cart_delete_'.$id.'"></a></td>';
      $tr .= '</tr>';
      $ret['highlight'] = 'cart_tr_'.$id;
      $ret['error'] = 'PRODUCT_CART_SUCCESS';
    } elseif ($index['stock'] == 0) {
      $ret['error'] = 'OUT_OF_STOCK';
    }
    $ret['cart_total'] = rawurlencode(gcms::int2Curr($total));
    $ret['cart_items'] = $items;
    $ret['content'] = rawurlencode($tr);
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
