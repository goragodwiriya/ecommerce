<?php
// modules/product/checkstock.php
if (defined('MAIN_INIT') && is_array($basket)) {
  $action = '';
  if ($last_status < $config['product_cut_stock'] && $order_status >= $config['product_cut_stock']) {
    // ตัด stock
    $action = 'cut';
  } elseif ($last_status >= $config['product_cut_stock'] && $order_status < $config['product_cut_stock']) {
    // คืน stock
    $action = 'restore';
  }
  if ($action != '') {
    foreach ($basket AS $item) {
      if ($item['stock'] > -1) {
        $stock = $action == 'cut' ? max(0, $item['stock'] - $item['quantity']) : $item['stock'] + $item['quantity'];
        $sql = "UPDATE `".DB_PRODUCT_ADDITIONAL."` SET `stock`='$stock'";
        $sql .= " WHERE `product_id`='$item[product_id]' AND `id`='$item[additional_id]' AND `module_id`='$item[module_id]'";
        $sql .= " LIMIT 1";
        $db->query($sql);
      }
    }
  }
}
