<?php
// modules/product/order.php
if (defined('MAIN_INIT')) {
  // ตรวจสอบรายการสั่งซื้อ
  $sql = "SELECT O.* FROM `".DB_ORDERS."` AS O";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`owner`='product' AND M.`id`=O.`module_id`";
  $sql .= " WHERE O.`id`=".(int)$_REQUEST['id']." LIMIT 1";
  $order = $db->customQuery($sql);
  if (sizeof($order) == 1) {
    $order = $order[0];
    // อ่าน user เจ้าของตะกร้า
    $sql = "SELECT U.*,C.`printable_name` AS `country` FROM `".DB_USER."` AS U";
    $sql .= " INNER JOIN `".DB_COUNTRY."` AS C ON C.`iso`=U.`country`";
    $sql .= " WHERE U.`id`='$order[member_id]' LIMIT 1";
    $user = $db->customQuery($sql);
  } else {
    $order = false;
  }
  if (!$order || sizeof($user) == 0) {
    $title = $lng['LNG_CART_NOT_FOUND'];
    $content = '<div class=error>'.$title.'</div>';
  } else {
    $user = $user[0];
    if ($order_id != '' && $user['activatecode'] != '') {
      $db->edit(DB_USER, $user['id'], array('activatecode' => ''));
    }
    // หน่วยเงิน
    $currency_unit = $lng['CURRENCY_UNITS'][$order['currency_unit']];
    $datas = array();
    // query สินค้า
    $sql = "SELECT * FROM `".DB_CART."`";
    $sql .= " WHERE `module_id`='$order[module_id]' AND `order_id`='$order[id]'";
    $sql .= " ORDER BY `product_no` ASC";
    foreach ($db->customQuery($sql) AS $item) {
      $tr = '<tr class="row bg1'.($item['quantity'] == 0 ? ' OutOfStock' : '').'">';
      $tr .= '<td class=no>'.$item['product_no'].'</td>';
      $tr .= '<td class=topic title="'.$item['topic'].'">'.gcms::cutstring($item['topic'], 50).'</td>';
      $tr .= '<td class=center>'.$item['quantity'].'</td>';
      $tr .= '<td class=right>'.gcms::int2Curr($item['net'])." $currency_unit</td>";
      $tr .= '<td class=right>'.gcms::int2Curr($item['quantity'] * $item['net'])." $currency_unit</td>";
      $tr .= '</tr>';
      $datas[] = $tr;
    }
    // payment method
    $method = array();
    if ($order['order_status'] < 3) {
      if (is_array($config['payments_method'])) {
        foreach ($config['payments_method'] AS $i => $item) {
          if ($item != '') {
            $icon = WEB_URL.'/'.$item[1];
            $row = '<tr class=bg1><td class=col1 colspan=2>';
            $row .= '&nbsp;<img src='.(is_file(ROOT_PATH.$item[1]) ? $icon : WEB_URL.'/modules/payment/img/bank.png').' alt=icon>&nbsp;';
            $row .= stripslashes($item[0]).'</td></tr>';
            $method[] = $row;
          }
        }
      }
    }
    $patt = array('/{ORDERID}/', '/{CART}/', '/{METHOD}/', '/{FNAME}/', '/{LNAME}/', '/{EMAIL}/', '/{ADDRESS}/',
      '/{COUNTRY}/', '/{PHONE}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{DETAIL}/', '/{ID}/', '/{TOTAL}/', '/{DISCOUNT}/',
      '/{TAX}/', '/{VAT}/', '/{WEIGHT}/', '/{TRANSPORTION}/', '/{GRANDTOTAL}/', '/{STATUS}/', '/{POSTCODE}/',
      '/{UNIT}/', '/{PAID}/', '/{SHIPPING}/', '/{DATE}/', '/{PAYMENTDATE}/', '/{REFNO}/', '/{PAYMENT}/', '/{URL_PAYMENT}/');
    $replace = array();
    $replace[] = $order['order_no'];
    $replace[] = implode("\n", $datas);
    $replace[] = $order['payment_method'];
    $replace[] = $user['fname'];
    $replace[] = $user['lname'];
    $replace[] = $user['email'];
    $address = array();
    gcms::checkempty($user['address1'], $address);
    gcms::checkempty($user['address2'], $address);
    gcms::checkempty($user['tambon'], $address);
    gcms::checkempty($user['district'], $address);
    gcms::checkempty($user['province'], $address);
    gcms::checkempty($user['zipcode'], $address);
    $replace[] = implode(' ', $address);
    $replace[] = $user['country'];
    $phone = array();
    gcms::checkempty($user['phone1'], $phone);
    gcms::checkempty($user['phone2'], $phone);
    $replace[] = implode(' ', $phone);
    $replace[] = 'gcms::getLng';
    $replace[] = $order['comment'];
    $replace[] = $order['id'];
    $replace[] = gcms::int2Curr($order['total']);
    $replace[] = gcms::int2Curr($order['discount']);
    $replace[] = gcms::int2Curr($order['tax']);
    $replace[] = gcms::int2Curr($order['vat']);
    $replace[] = number_format($order['weight']);
    $replace[] = gcms::int2Curr($order['transport']);
    $replace[] = gcms::int2Curr(($order['total'] + $order['tax'] + $order['vat'] + $order['transport'] - $order['discount']));
    $replace[] = $lng['PAYMENT_STATUS'][$order['order_status']];
    $replace[] = $order['postcode'] == '' ? '' : '<a href="http://track.thailandpost.co.th/trackinternet/" target=_blank>'.$order['postcode'].'</a>';
    $replace[] = $currency_unit;
    $replace[] = gcms::int2Curr($order['paid']);
    $replace[] = $order['transport_method'];
    $replace[] = $db->sql_date2date($order['order_date']);
    $replace[] = $db->sql_date2date($order['payment_date']);
    $replace[] = $order['payment_ref'];
    $replace[] = in_array($order['order_status'], array(1, 2, 5)) ? '' : 'hidden';
    $replace[] = WEB_URL.'/index.php?module=payment&amp;id='.(int)$_REQUEST['id'];
    $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'product', 'order'));
    // title
    $title = "$lng[LNG_ORDER_DETAIL] $lng[LNG_ORDER_NO] $order[order_no]";
    // tab
    $tab = 'product';
  }
}
