<?php
// modules/product/getprice.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// ตรวจสอบ referer
if (gcms::isReferer() && preg_match('/^(select|item)_([0-9]+)_([0-9]+)$/', $_POST['id'], $match)) {
  // สกุลเงิน
  $currency = $_SESSION['currency'];
  // query
  $sql = "SELECT `price_$currency` AS `price`,`net_$currency` AS `net`,`stock` FROM `".DB_PRODUCT_ADDITIONAL."` WHERE `product_id`='$match[2]' AND `id`='$match[3]' LIMIT 1";
  $result = $db->customQuery($sql);
  if (sizeof($result) == 1) {
    $price = $result[0]['price'];
    $net = $result[0]['net'];
    // ส่งค่ากลับ
    $ret = array();
    $ret["net_$match[2]"] = gcms::int2Curr($net);
    $ret["price_$match[2]"] = gcms::int2Curr($price);
    $ret["saved_$match[2]"] = gcms::int2Curr($net == 0 ? 0 : ((100 * ($price - $net)) / $net));
    $ret["discount_$match[2]"] = gcms::int2Curr($price - $net);
    if ($match[1] == 'item') {
      $ret["additional_$match[2]"] = "$match[2]_$match[3]";
    }
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
