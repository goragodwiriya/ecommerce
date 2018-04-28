<?php
// modules/product/member.php
if (defined('MAIN_INIT') && $isMember) {
  $sql = "SELECT `id` AS `module_id`,`module` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 1) {
    $index = $index[0];
    $login_id = (int)$_SESSION['login']['id'];
    $list = array();
    // ประวัติการสั่งซื้อ
    $sql = "SELECT `id`,`order_no`,`payment_date`,`last_update`,`order_status`,`postcode`";
    $sql .= " FROM `".DB_ORDERS."`";
    $sql .= " WHERE `member_id`='$login_id' AND `module_id`='$index[module_id]'";
    $sql .= " ORDER BY `order_date` DESC";
    foreach ($db->customQuery($sql) AS $item) {
      $tr = '<tr class=order'.$item['order_status'].'>';
      $tr .= '<td><a href="'.WEB_URL.'/index.php?module=product-order&amp;id='.$item['id'].'">'.$item['order_no'].'</a></td>';
      $tr .= '<td class="center date">'.($item['order_status'] == 3 ? $db->sql_date2date($item['payment_date']) : $db->sql_date2date($item['last_update'])).'</td>';
      $tr .= '<td class=center>'.($item['postcode'] == '' ? '-' : '<a href="http://track.thailandpost.co.th/trackinternet/" target=_blank>'.$item['postcode'].'</a>').'</td>';
      $tr .= '<td class=center>'.$lng['PAYMENT_STATUS'][$item['order_status']].'</td>';
      $tr .= '</tr>';
      $list[] = $tr;
    }
    $content = preg_replace('/{LIST}/', implode("\n", $list), gcms::loadtemplate($index['module'], 'product', 'member'));
    // title
    $title = $lng['LNG_PAYMENT_HISTORY'];
  } else {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content = '<div class=error>'.$title.'</div>';
  }
}
