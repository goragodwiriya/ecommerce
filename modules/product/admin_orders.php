<?php
// modules/product/admin_orders.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_salesman'])) {
  // ตรวจสอบโมดูลที่เรียก
  $sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 0) {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    $index = $index[0];
    unset($url_query['id']);
    // ค่าที่ส่งมา
    $y = (int)$_GET['y'];
    $y = $y == 0 ? $myear : $y;
    $m = (int)$_GET['m'];
    $m = $m == 0 ? $mmonth : $m;
    $status = max(1, (int)$_GET['status']);
    $currency_unit = $lng['CURRENCY_UNITS'][$config['currency_unit']];
    // query
    $q = array("O.`order_status`=$status");
    if ($m > 0) {
      $q[] = "MONTH(O.`order_date`)='$m'";
    }
    $q[] = "YEAR(O.`order_date`)='$y'";
    // ค้นหา
    $search = $db->sql_trim_str($_GET['search']);
    if ($search != '') {
      $q[] = "(O.`order_no`='$search' OR U.`email` LIKE '%$search%' OR U.`fname` LIKE '%$search%' OR U.`lname` LIKE '%$search%')";
      $url_query['search'] = urlencode($search);
    }
    //$q[] = "O.`module_id`='$index[id]'";
    // title
    if ($m == -1) {
      $title = sprintf($lng['LNG_SALES_REPORT'][1], $lng['PAYMENT_STATUS'][$status], (string)($y + $lng['YEAR_OFFSET']));
    } else {
      $title = sprintf($lng['LNG_SALES_REPORT'][0], $lng['PAYMENT_STATUS'][$status], $lng['MONTH_LONG'][$m - 1].' '.($y + $lng['YEAR_OFFSET']));
    }
    $a = array();
    $a[] = '<span class=icon-product>{LNG_PRODUCT}</span>';
    $a[] = $lng['PAYMENT_STATUS'][$status];
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-report>'.$title.'</h1></header>';
    // form
    $content[] = '<form class=table_nav method=get action=index.php>';
    $content[] = '<fieldset>';
    $content[] = '<label>{LNG_MONTH} <select name=m>';
    $sel = -1 == $m ? ' selected' : '';
    $content[] = '<option value=-1'.$sel.'>{LNG_ALL_ITEMS}</option>';
    foreach ($lng['MONTH_LONG'] AS $i => $item) {
      $i++;
      $sel = $i == $m ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
    }
    $content[] = '</select></label>';
    $content[] = '<label>{LNG_YEAR} <select name=y>';
    for ($i = 2012; $i <= $myear; $i++) {
      $sel = $i == $y ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.($i + $lng['YEAR_OFFSET']).'</option>';
    }
    $content[] = '</select></label>';
    $content[] = '</fieldset>';
    // submit
    $content[] = '<fieldset>';
    $content[] = '<label><input type=submit class="button go" value="{LNG_GO}"></label>';
    $content[] = '</fieldset>';
    // search
    $content[] = '<fieldset class=search>';
    $content[] = '<label accesskey=f><input type=text name=search value="'.$search.'" placeholder="{LNG_SEARCH_TITLE}" title="{LNG_SEARCH_TITLE}"></label>';
    $content[] = '<input type=submit value="&#xE607;" title="{LNG_SEARCH}">';
    $content[] = '<input type=hidden name=module value=product-orders>';
    $content[] = '<input type=hidden name=status value='.$status.'>';
    $content[] = '</fieldset>';
    $content[] = '</form>';
    // ตารางข้อมูล
    $content[] = '<table id=product class="tbl_list fullwidth">';
    $content[] = '<caption>{LNG_SALES_REPORT_SUBTITLE}</caption>';
    $content[] = '<thead>';
    $content[] = '<tr>';
    $content[] = '<th id=c0 scope=col>{LNG_ORDER_NO}</th>';
    $content[] = '<th id=c1 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
    $content[] = '<th id=c2 scope=col>{LNG_CUSTOMER}</th>';
    $content[] = '<th id=c3 scope=col class=center>{LNG_TRANSACTION_ON}</th>';
    $content[] = '<th id=c4 scope=col class=center>{LNG_PAYMENT_STATUS}</th>';
    $content[] = '<th id=c5 scope=col class=center>{LNG_TOTAL_AMOUNT}</th>';
    $content[] = '</tr>';
    $content[] = '</thead>';
    $content[] = '<tbody>';
    // query
    $total = 0;
    // query
    $sql = "SELECT O.`id`,O.`order_no`,O.`order_date`,O.`order_status`,O.`member_id`";
    $sql .= ",O.`total`,O.`transport`,O.`tax`,O.`vat`,O.`discount`";
    $sql .= ",(SELECT CONCAT(`fname`,' ',`lname`) FROM `".DB_USER."` WHERE `id`=O.`member_id`) AS `customer`";
    $sql .= " FROM `".DB_ORDERS."` AS O";
    $sql .= " WHERE ".implode(' AND ', $q);
    $sql .= " ORDER BY O.`order_date`";
    foreach ($db->customQuery($sql) AS $item) {
      $id = $item['id'];
      $price = $item['total'] + $item['transport'] + $item['tax'] + $item['vat'] - $item['discount'];
      $total += $price;
      $tr = '<tr id=M_'.$id.'>';
      $tr .= '<th headers=c0 id=r'.$id.' scope=row><a class="no topic" href="{URLQUERY?module=product-view&id='.$id.'&src=product-orders&spage='.$page.'}" title="{LNG_PREVIEW}">'.$item['order_no'].'</a></th>';
      $tr .= '<td headers="r'.$id.' c1" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
      $tr .= '<td headers="r'.$id.' c2"><a href="{URLQUERY?module=editprofile&id='.$item['member_id'].'&src=product-orders&spage='.$page.'}" title="{LNG_MEMBER_EDIT_TITLE}">'.trim($item['customer']).'</a></td>';
      $tr .= '<td headers="r'.$id.' c3" class=date>'.$db->sql_date2date($item['order_date']).'</td>';
      $tr .= '<td headers="r'.$id.' c4" class=center>'.$lng['PAYMENT_STATUS'][$item['order_status']].'</td>';
      $tr .= '<td headers="r'.$id.' c5" class=right>'.gcms::int2Curr($price).' '.$currency_unit.'</td>';
      $tr .= '</tr>';
      $content[] = $tr;
    }
    $content[] = '</tbody>';
    $content[] = '<tfoot>';
    $content[] = '<tr>';
    $content[] = '<td headers=c0>&nbsp;</td>';
    $content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
    $content[] = '<td headers=c4 colspan=3 class=right>{LNG_TOTAL_AMOUNT}</td>';
    $content[] = '<td headers=c5 class="right subtotal">'.gcms::int2Curr($total).' '.$currency_unit.'</td>';
    $content[] = '</tr>';
    $content[] = '</tfoot>';
    $content[] = '</table>';
    // sel action
    $content[] = '<div class=table_nav>';
    $content[] = '<select id=sel_action><option value=delete_order>{LNG_DELETE}</option></select>';
    $content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action><span>{LNG_SELECT_ACTION}</span></label>';
    $content[] = '</div>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    $content[] = "inintCheck('product');";
    $content[] = "inintTR('product', /M_[0-9]+/);";
    $content[] = 'callAction("btn_action", function(){return $E("sel_action").value}, "product", "'.WEB_URL.'/modules/product/admin_action.php");';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'product-orders';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
