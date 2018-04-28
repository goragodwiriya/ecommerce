<?php
// modules/payment/main.php
if (defined('MAIN_INIT')) {
  // โมดูลที่ติดตั้ง
  $index = $install_modules[$install_owners['payment'][0]];
  // id ที่ต้องการ
  $id = intval($_REQUEST['id']);
  // ตรวจสอบรายการสั่งซื้อ
  $sql = "SELECT O.`order_status`,O.`order_no`,O.`total`,O.`discount`,O.`currency_unit`,O.`transport`,O.`comment`,O.`member_id`,U.`activatecode`,U.`email`";
  $sql .= " FROM `".DB_ORDERS."` AS O";
  $sql .= " INNER JOIN `".DB_USER."` AS U ON U.`id`=O.`member_id`";
  if ($id > 0) {
    // มาจากการสั่งซื้อสินค้า
    $sql .= " WHERE O.`id`='$id' LIMIT 1";
    $order = $db->customQuery($sql);
    $order = sizeof($order) == 1 ? $order[0] : false;
  } elseif (isset($_GET['order'])) {
    // มาจาก email
    $sql .= " WHERE O.`order`='".$db->sql_trim_str($_GET['order'])."' LIMIT 1";
    $order = $db->customQuery($sql);
    $order = sizeof($order) == 1 ? $order[0] : false;
    if ($order['activatecode'] != '') {
      // มาจาก email ให้ activate
      $db->edit(DB_USER, $order['member_id'], array('activatecode' => ''));
    }
  } else {
    $id = -1;
  }
  if ($order['order_status'] > 2) {
    $title = $lng['LNG_PAYMENT_ALREADY'];
    $content = "<div class=error>$title</div>";
  } elseif ($id > -1 && !$order) {
    $title = $lng['LNG_PAYMENT_NOT_FOUND'];
    $content = "<div class=error>$title</div>";
  } else {
    // breadcrumbs
    $breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
    $breadcrumbs = array();
    // หน้าหลัก
    $breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
    // โมดูล
    $breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL($index['module']), '{LNG_PAYMENT_TITLE}', '{LNG_PAYMENT}', $breadcrumb);
    // antispam
    $antispam = gcms::rndname(32);
    $_SESSION[$antispam] = gcms::rndname(4);
    // แสดงผล
    $patt = array('/{BREADCRUMS}/', '/{ORDERNO}/', '/{EMAIL}/', '/{AMOUNT}/', '/{CURRENCYUNIT}/', '/{DETAIL}/', '/{WEBURL}/', '/{ANTISPAM}/', '/{ANTISPAMCHAR}/', '/{HOUR}/', '/{MINUTE}/', '/{PAYMENTMETHOD}/', '/{(LNG_[A-Z_]+)}/e');
    $replace = array();
    $replace[] = implode("\n", $breadcrumbs);
    $replace[] = $order['order_no'];
    $replace[] = $order['email'];
    $replace[] = !$order ? '' : gcms::int2Curr($order['total'] + $order['transport'] - $order['discount'], '');
    $replace[] = $lng['CURRENCY_UNITS'][$order['currency_unit']];
    $replace[] = $order['comment'];
    $replace[] = WEB_URL;
    $replace[] = $antispam;
    $replace[] = $isAdmin ? $_SESSION[$antispam] : '';
    $datas = array();
    for ($i = 0; $i < 24; $i++) {
      $sel = intval(date('H', $mmktime)) == $i ? ' selected' : '';
      $a = sprintf('%02d', $i);
      $datas[] = '<option value='.$a.$sel.'>'.$a.'</option>';
    }
    $replace[] = implode('', $datas);
    $datas = array();
    for ($i = 0; $i < 60; $i++) {
      $sel = intval(date('i', $mmktime)) == $i ? ' selected' : '';
      $a = sprintf('%02d', $i);
      $datas[] = '<option value='.$a.$sel.'>'.$a.'</option>';
    }
    $replace[] = implode('', $datas);
    $datas = array();
    if (is_array($config['payments_method'])) {
      foreach ($config['payments_method'] AS $i => $item) {
        if (is_file(ROOT_PATH.$item[1])) {
          $icon = WEB_URL.'/'.$item[1];
        } else {
          $icon = WEB_URL.'/modules/payment/img/bank.png';
        }
        $row = '<li><label><input type=radio name=payment_method id=payment_method_'.$i.' value='.$i.'>';
        $row .= '&nbsp;<img src="'.$icon.'" alt=icon>&nbsp;'.stripslashes($item[0]).'</label></li>';
        $datas[] = $row;
      }
    }
    $replace[] = implode('', $datas);
    $replace[] = 'gcms::getLng';
    $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate('payment', 'payment', 'main'));
    // title
    $title = $lng['LNG_PAYMENT_TITLE'];
    // เลือกเมนู
    $menu = $install_modules[$index['module']]['alias'];
    $menu = $menu == '' ? $index['module'] : $menu;
  }
} else {
  $title = $lng['PAGE_NOT_FOUND'];
  $content = "<div class=error>$title</div>";
}
