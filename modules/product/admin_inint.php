<?php
// modules/product/admin_inint.php
if (MAIN_INIT == 'admin' && $isAdmin && ($install_modules['product']['owner'] != 'product' || !defined('DB_PRODUCT'))) {
  // เมนูติดตั้ง
  $admin_menus['tools']['install']['product'] = '<a href="index.php?module=install&amp;modules=product"><span>Shopping Online Store</span></a>';
} else {
  // เมนูแอดมิน
  unset($admin_menus['modules']['product']['setup']);
  if (is_array($lng['PRODUCT_CUSTOM_SELECT'])) {
    foreach ($lng['PRODUCT_CUSTOM_SELECT'] AS $k => $v) {
      $admin_menus['modules']['product']["select$k"] = '<a href="index.php?module=product-select&amp;typ='.$k.'"><span>'.$v.'</span></a>';
    }
  }
  $admin_menus['modules']['product']['setup'] = '<a href="index.php?module=product-setup"><span>{LNG_PRODUCT_LIST}</span></a>';
  $admin_menus['modules']['product']['write'] = '<a href="index.php?module=product-write"><span>{LNG_ADD_NEW} {LNG_PRODUCT}</span></a>';
  $admin_menus['sections']['product'] = array('b', '{LNG_PRODUCT}');
  if (is_array($lng['PAYMENT_STATUS'])) {
    $count = array();
    $sql = "SELECT `order_status`,COUNT(*) AS `count` FROM `".DB_ORDERS."` GROUP BY`order_status`";
    foreach ($db->customQuery($sql) AS $item) {
      $count[$item['order_status']] = $item['count'];
    }
    // เมนูรายงานการสั่งซื้อ
    foreach ($lng['PAYMENT_STATUS'] AS $i => $item) {
      if ($item != '') {
        if (in_array($i, array(1, 2, 3))) {
          $admin_menus['product'][$item] = '<a href="index.php?module=product-orders&amp;status='.$i.'"><span>'.$item.' ('.(int)$count[$i].')</span></a>';
        } else {
          $admin_menus['product'][$item] = '<a href="index.php?module=product-orders&amp;status='.$i.'"><span>'.$item.'</span></a>';
        }
      }
    }
  }
}

function getStock($stock)
{
  if ($stock < 0) {
    return '{LNG_UNLIMITED}';
  } elseif ($stock == 0) {
    return '{LNG_OUT_OF_STOCK}';
  } else {
    return $stock;
  }
}
