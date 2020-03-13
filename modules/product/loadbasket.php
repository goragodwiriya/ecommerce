<?php
// modules/product/loadbasket.php
if (defined('MAIN_INIT')) {
  // หน่วยเงิน
  $currency = $_SESSION['currency'];
  $currency_unit = $lng['CURRENCY_UNITS'][$currency];
  // รายการที่หมดจาก stock
  $delete_ids = array();
  // โหลดรายการทั้งหมดในตะกร้า
  $sql = "SELECT Q2.*,A.`price_$currency` AS `price`,A.`net_$currency` AS `net`,A.`stock`,A.`weight` FROM (";
  $sql .= "SELECT C.* FROM `".DB_CART."` AS C";
  $sql .= " WHERE C.`order_id`='0' AND C.`module_id`='$index[module_id]' AND (".($login_id == 0 ? '' : "C.`member_id`='$login_id' OR ")."C.`session_id`='$session_id')";
  $sql .= ") AS Q2";
  $sql .= " INNER JOIN `".DB_PRODUCT_ADDITIONAL."` AS A ON A.`product_id`=Q2.`product_id` AND A.`id`=Q2.`additional_id`";
  $sql .= " ORDER BY Q2.`id` ASC";
  foreach ($db->customQuery($sql) AS $item) {
    if ($my_quantity > 0 && $order_id == $item['id']) {
      // ปรับปรุงจำนวนสินค้าที่เลือก
      $quantity = $my_quantity;
    } else {
      $quantity = $item['quantity'];
      if ($product_id == $item['product_id'] && $additional_id == $item['additional_id']) {
        // เพิ่มสินค้าที่มีอยู่ในตะกร้าแล้ว
        $quantity++;
        $add_quantity = $quantity;
      }
    }
    if ($item['stock'] == 0) {
      // สินค้าหมด
      $delete_ids[] = $item['id'];
    } else {
      // คืนค่า
      if ($item['stock'] > -1) {
        // จำกัดจำนวนไม่เกิน stock
        $quantity = min($quantity, $item['stock']);
      }
      if ($quantity != $item['quantity']) {
        $item['quantity'] = $quantity;
        // อัปเดตจำนวนสินค้าในตะกร้า
        $db->edit(DB_CART, $item['id'], array('quantity' => $quantity));
      }
      // จำกัดจำนวนสินค้า
      if ($product_id == $item['product_id'] && $additional_id == $item['additional_id']) {
        if ($item['stock'] > -1 && $add_quantity > $quantity) {
          $item['alert'] = sprintf($lng['PRODUCT_REMAIN_ITEMS'], $item['stock']);
        }
      } elseif ($order_id == $item['id']) {
        if ($item['stock'] > -1 && $my_quantity > $quantity) {
          $ret['alert'] = sprintf($lng['PRODUCT_REMAIN_ITEMS'], $item['stock']);
        }
      }
      if ($order_id == $item['id']) {
        $my_quantity = $quantity;
      }
      $basket[] = $item;
    }
  }
  if (sizeof($delete_ids) > 0) {
    // ลบรายการที่ ไม่มีสินค้าในสต๊อกเหลือ
    $db->query("DELETE FROM `".DB_CART."` WHERE `id` IN (".implode(',', $delete_ids).")");
  }
}
