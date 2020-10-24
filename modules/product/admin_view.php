<?php
// modules/product/admin_view.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_salesman'])) {
  // ตรวจสอบ order ที่เลือก
  $sql = "SELECT O.`id`,O.`module_id`,O.`payment_date`,O.`payment_method`,O.`payment_ref`,O.`paid`,O.`discount`";
  $sql .= ",O.`order_no`,O.`order_date`,O.`order_status`,O.`total`,O.`transport`,O.`tax`,O.`vat`";
  $sql .= ",O.`postcode`,O.`last_update`,O.`member_id`,O.`currency_unit`,O.`weight`,O.`comment`";
  $sql .= " FROM `".DB_ORDERS."` AS O";
  //$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=O.`module_id` AND M.`owner`='product'";
  $sql .= " WHERE O.`id`=".(int)$_GET['id'];
  $sql .= " LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 0) {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    $index = $index[0];
    // ตรวจสอบลูกค้า
    $sql = "SELECT U.`fname`,U.`lname`,U.`email`,U.`address1`,U.`address2`,U.`province`,U.`zipcode`,U.`phone1`,U.`phone2`";
    $sql .= ",(SELECT `printable_name` FROM `".DB_COUNTRY."` WHERE `iso`=U.`country` LIMIT 1) AS `country`";
    $sql .= " FROM `".DB_USER."` AS U WHERE U.`id`='$index[member_id]' LIMIT 1";

    $user = $db->customQuery($sql);
    $user = sizeof($user) == 1 ? $user[0] : false;
    // หน่วยเงิน
    $currency_unit = $lng['CURRENCY_UNITS'][$index['currency_unit']];
    // title
    $title = "$lng[LNG_ORDER_DETAIL] $lng[LNG_ORDER_NO] $index[order_no]";
    $a = array();
    $a[] = '<a href="{URLQUERY}" class=icon-product>{LNG_CUSTOMER}</a>';
    $a[] = '{LNG_ORDER_DETAIL}';
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-summary>'.$title.'</h1></header>';
    // form
    $content[] = '<form id=order_frm method=get action=index.php>';
    $content[] = '<table id=product class="report tbl_list fullwidth">';
    $content[] = '<tbody>';
    $content[] = '<tr><th scope=rowgroup class=icon-cart id=c0 colspan=2>'.$title.' {LNG_TRANSACTION_ON} '.$db->sql_date2date($index['order_date']).'</th></tr>';
    $content[] = '<tr><td colspan=2>';
    // query สินค้า
    $sql = "SELECT C.*,COALESCE(A.`stock`,0) AS `stock` FROM `".DB_CART."` AS C";
    $sql .= " LEFT JOIN `".DB_PRODUCT_ADDITIONAL."` AS A ON A.`id`=C.`additional_id` AND A.`product_id`=C.`product_id`";
    $sql .= " WHERE C.`order_id`='$index[id]' ORDER BY C.`product_no` ASC";
    $content[] = '<table class="data border"><thead><tr>';
    $content[] = '<th class=center>{LNG_PRODUCT_ID}</th>';
    $content[] = '<th class=center>{LNG_PRODUCT_TOPIC}</th>';
    $content[] = '<th class=center>{LNG_QUANTITY}</th>';
    if ($index['order_status'] < 3) {
      $content[] = '<th class=center>{LNG_STOCK}</th>';
    }
    $content[] = '<th class=center>{LNG_PRICE}</th>';
    $content[] = '<th class=center>{LNG_TOTAL}</th>';
    $content[] = '</tr></thead><tbody id=order_view>';
    $quantity = 0;
    foreach ($db->customQuery($sql) AS $item) {
      $id = $item['id'];
      if ($item['stock'] == 0) {
        $s = 0;
      } elseif ($item['stock'] > 0 && $item['stock'] < $item['quantity']) {
        $s = 1;
      } else {
        $s = 2;
      }
      $tr = '<tr id=M_'.$id.' class="stock'.$s.'">';
      $tr .= '<td class=topic>'.$item['product_no'].'</td>';
      if ($index['order_status'] < 3) {
        $tr .= '<td class=center><label><input class=wide type=text name=cart_topic[] id=cart_topic_'.$id.' size=60 value="'.$item['topic'].'" title="{LNG_PRODUCT_TOPIC}" data-result=view_result></label></td>';
        $tr .= '<td class=center><label><input class=number type=text name=cart_quantity[] id=cart_quantity_'.$id.' size=5 value="'.$item['quantity'].'" title="{LNG_QUANTITY}"></label></td>';
        $tr .= '<td class=center><input type=hidden name=cart_id[] value='.$id.'>'.getStock($item['stock']).'</td>';
        $tr .= '<td class=right><label><input class=currency type=text name=cart_price[] id=cart_price_'.$id.' size=10 value="'.$item['net'].'" title="{LNG_PRICE}">&nbsp;'.$currency_unit.'</label></td>';
      } else {
        $tr .= '<td>'.$item['topic'].'</td>';
        $tr .= '<td class=center>'.$item['quantity'].'</td>';
        $tr .= '<td class=right>'.gcms::int2Curr($item['net'])." $currency_unit</td>";
      }
      $price = $item['quantity'] * $item['net'];
      $tr .= '<td class=right>'.gcms::int2Curr($price)." $currency_unit</td>";
      $tr .= '</tr>';
      $content[] = $tr;
      $quantity = $quantity + $item['quantity'];
    }
    $content[] = '<tr class=total>';
    $content[] = '<td colspan=2>{LNG_TOTAL}</td>';
    $content[] = '<td class=center>'.$quantity.'&nbsp;{LNG_ITEMS}</td>';
    if ($index['order_status'] < 3) {
      $content[] = '<td colspan=2></td>';
    } else {
      $content[] = '<td></td>';
    }
    $content[] = '<td class=right>'.gcms::int2Curr($index['total']).' '.$currency_unit.'</td>';
    $content[] = '</tr>';
    $content[] = '</tbody></table>';
    $content[] = '<p class=comment id=view_result>{LNG_PRODUCT_ORDER_VIEW_COMMENT}</p>';
    $content[] = '</td></tr>';
    // จำนวนเงินที่ชำระ
    $content[] = '<tr class="row bg2">';
    $content[] = '<th scope=row><label for=order_discount><strong>{LNG_DISCOUNT} :</strong></label></th>';
    if ($index['order_status'] < 3) {
      $content[] = '<td><input type=text name=order_discount id=order_discount size=10 class=currency value="'.$index['discount'].'" title="{LNG_DISCOUNT}">&nbsp;'.$currency_unit.'</td>';
    } else {
      $content[] = '<td>'.gcms::int2Curr($index['discount']).' '.$currency_unit.'</td>';
    }
    $content[] = '</tr>';
    // total amount
    $content[] = '<tr class=bg2>';
    $content[] = '<th scope=row><strong>{LNG_TOTAL_AMOUNT} :</strong></th>';
    $content[] = '<td>'.gcms::int2Curr(($index['total'] + $index['transport'] + $index['tax'] + $index['vat'] - $index['discount'])).' '.$currency_unit.'</td>';
    $content[] = '</tr>';
    // ข้อมูลลูกค้า
    $content[] = '<tr><th scope=rowgroup class=icon-profile id=c1 colspan=2>{LNG_PAYMENT_REGISTER_INFORMATION}</th></tr>';
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_FNAME} {LNG_LNAME} :</th>';
    $content[] = '<td>'.$user['fname'].' '.$user['lname'].'</td>';
    $content[] = '</tr>';
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_EMAIL} :</th>';
    $content[] = '<td><a href="{URLQUERY?module=sendmail&src=product-view&id='.$index['id'].'&to='.$index['member_id'].'}" title="{LNG_EMAIL_SEND} {LNG_TO} '.$user['email'].'">'.$user['email'].'</a></td>';
    $content[] = '</tr>';
    // ที่อยู่
    $address = array();
    gcms::checkempty($user['address1'], $address);
    gcms::checkempty($user['address2'], $address);
    gcms::checkempty($user['tambon'], $address);
    gcms::checkempty($user['district'], $address);
    gcms::checkempty($user['province'], $address);
    gcms::checkempty($user['zipcode'], $address);
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_ADDRESS} :</th>';
    $content[] = '<td>'.implode(' ', $address).'</td>';
    $content[] = '</tr>';
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_COUNTRY} :</th>';
    $content[] = '<td>'.$user['country'].'</td>';
    $content[] = '</tr>';
    $phone = array();
    gcms::checkempty($user['phone1'], $phone);
    gcms::checkempty($user['phone2'], $phone);
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_PHONE} :</th>';
    $content[] = '<td>'.implode(' ', $phone).'</td>';
    $content[] = '</tr>';
    // การชำระเงิน
    $content[] = '<tr><th scope=rowgroup class=icon-payment id=c2 colspan=2>{LNG_PAYMENT}</th></tr>';
    // ช่องทางชำระเงิน
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_PAYMENT_METHOD} :</th>';
    $content[] = '<td>'.stripslashes($index['payment_method']).'</td>';
    $content[] = '</tr>';
    // จำนวนเงินที่ชำระ
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_AMOUNT} :</th>';
    $paid = $index['order_status'] == 0 ? '&nbsp;' : gcms::int2Curr($index['paid']).' '.$currency_unit;
    $content[] = '<td>'.$paid.'</td>';
    $content[] = '</tr>';
    // วันที่ชำระเงิน
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_PAYMENT_DATE} :</th>';
    $paid = $index['order_status'] == 0 ? '&nbsp;' : $db->sql_date2date($index['payment_date']);
    $content[] = '<td>'.$paid.'</td>';
    $content[] = '</tr>';
    // เลขที่อ้างอิง
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row>{LNG_PAYMENT_REF_NO} :</th>';
    $content[] = '<td>'.$index['payment_ref'].'</td>';
    $content[] = '</tr>';
    // รายละเอียด
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row><label for=order_comment>{LNG_PAYMENT_DETAIL} :</label></th>';
    $content[] = '<td><textarea class=wide cols=60 rows=3 name=order_comment id=order_comment title="{LNG_PAYMENT_DETAIL_COMMENT}">'.$index['comment'].'</textarea></td>';
    $content[] = '</tr>';
    // สรุปสถานะสินค้า
    $content[] = '<tr><th scope=rowgroup class=icon-summary id=c3 colspan=2>{LNG_PAYMENT_SUMMARY}</th></tr>';
    // last_update
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row><label for=last_update>{LNG_LAST_UPDATE} :</label></th>';
    list($d, $t) = explode(' ', $index['last_update']);
    $content[] = '<td><input type=date name=last_update id=last_update value="'.$d.'"></td>';
    $content[] = '</tr>';
    // สถานะสินค้า
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row><label for=order_status>{LNG_PAYMENT_STATUS} :</label></th>';
    $content[] = '<td><select name=order_status id=order_status>';
    foreach ($lng['PAYMENT_STATUS'] AS $i => $item) {
      if ($item != '') {
        $sel = $i == $index['order_status'] ? ' selected' : '';
        $content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
      }
    }
    $content[] = '</select></td>';
    $content[] = '</tr>';
    // หมายเลขพัสดุ
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row><label for=order_postcode>{LNG_POSTCODE} :</label></th>';
    $content[] = '<td><input class=wide type=text name=order_postcode id=order_postcode value="'.$index['postcode'].'" size=40 maxlength=13></td>';
    $content[] = '</tr>';
    // sendmail
    $content[] = '<tr class=row>';
    $content[] = '<th scope=row><label for=order_sendmail>{LNG_PAYMENT_SENDMAIL} :</label></th>';
    $content[] = '<td><input type=checkbox name=order_sendmail id=order_sendmail value=1></td>';
    $content[] = '</tr>';
    $content[] = '<tr>';
    $content[] = '<td></td>';
    $content[] = '<td>';
    $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
    $content[] = '</td></tr>';
    $content[] = '</tbody>';
    $content[] = '</table>';
    $content[] = '<input type=hidden id=order_id name=order_id value='.$index['id'].'>';
    $content[] = '</form>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'new GForm("order_frm", "'.WEB_URL.'/modules/product/admin_view_save.php").onsubmit(doFormSubmit);';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'product-view';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<div class=error>'.$title.'</div>';
}
