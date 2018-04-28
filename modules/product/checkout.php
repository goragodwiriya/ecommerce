<?php
// modules/product/basket.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer
if (gcms::isReferer()) {
  // ตรวจสอบโมดูล
  $sql = "SELECT `id` AS `module_id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 0) {
    $ret['error'] = 'ID_NOT_FOUND';
  } else {
    $index = $index[0];
    // ค่าที่ส่งมา
    $ret = array();
    $basket = array();
    $total = 0;
    $tax = 0;
    $vat = 0;
    $quantity = 0;
    $weight = 0;
    $shipping = 0;
    $login_id = (int)$_SESSION['login']['id'];
    $session_id = session_id();
    // หน่วยเงิน
    $currency = $_SESSION['currency'];
    $currency_unit = $lng['CURRENCY_UNITS'][$currency];
    // ตรวจสอบตะกร้าสืนค้า
    define('MAIN_INIT', 'basket');
    include ROOT_PATH.'modules/product/loadbasket.php';
    if (sizeof($basket) == 0) {
      $ret['error'] = 'CART_EMPTY';
    } else {
      $content = array();
      $content[] = '<article class=checkout>';
      $content[] = '<header><h1 class=icon-cart>'.$lng['LNG_CART'].'</h1></header>';
      $content[] = '<table class=fullwidth><thead>';
      $content[] = '<tr class=center><th></th><th>'.$lng['LNG_QUANTITY'].'</th><th>'.$lng['LNG_DETAIL'].'</th><th>'.$lng['LNG_PRICE'].'</th><th>'.$lng['LNG_TOTAL'].'</th></tr>';
      $content[] = '</thead><tbody id=checkout_list class=product>';
      foreach ($basket AS $item) {
        $id = $item['id'];
        // จำนวนเงิน
        $amount = ($item['quantity'] * $item['net']);
        // ราคารวม
        $total = $total + $amount;
        // tax
        $tax += ($amount * $item['tax']) / 100;
        // vat
        $vat += ($amount * $item['vat']) / 100;
        // จำนวนสินค้า(ชิ้น)รวม
        $quantity += $item['quantity'];
        // น้ำหนักรวม
        $weight += ($item['quantity'] * $item['weight']);
        // ค่าขนส่ง(ต่อชิ้น)รวม
        $shipping += ($item['quantity'] * $item['transportation']);
        // รายการตะกร้าสินค้า
        $tr = '<tr id=checkout_tr_'.$id.'>';
        $tr .= '<td><a class=icon-delete id=checkout_delete_'.$id.'>&nbsp;</a></td>';
        $tr .= '<td class=center><label><input id=checkout_quantity_'.$id.' type=text size=1 value="'.$item['quantity'].'"></label></td>';
        $tr .= '<td>'.$item['topic'].'</td>';
        $tr .= '<td class=right>'.gcms::int2Curr($item['net']).'<span class=mobile>&nbsp;'.$currency_unit.'</span></td>';
        $tr .= '<td class=right><span id=checkout_price_'.$id.'>'.gcms::int2Curr($amount).'</span><span class=mobile>&nbsp;'.$currency_unit.'</span></td>';
        $tr .= '</tr>';
        $content[] = $tr;
        $total = $total + $price;
      }
      $content[] = '</tbody><tfoot>';
      $content[] = '<tr><td colspan=5 class=right>'.$lng['LNG_TOTAL'].'&nbsp;<span id=checkout_total>'.gcms::int2Curr($total).'</span>&nbsp;'.$currency_unit.'</td></tr>';
      $content[] = '<tr><td colspan=5 class=right>'.$lng['LNG_TOTAL_WEIGHT'].'&nbsp;<span id=checkout_weight>'.number_format($weight).'</span>&nbsp;'.$lng['LNG_GRAMS'].'</td></tr>';
      $content[] = '<tr><td colspan=5 class=right>'.$lng['LNG_TOTAL_AMOUNT'].'&nbsp;<span id=checkout_amount class=subtotal>'.gcms::int2Curr($total + $transport - $total_discount).'</span>&nbsp;'.$currency_unit.'</td></tr>';
      $content[] = '</tfoot></table>';
      $content[] = '<div class=message>'.$lng['LNG_PRODUCT_BASKET_COMMENT'].'</div>';
      $content[] = '<div class="table fullwidth center">';
      $content[] = '<span class=td><a id=checkout_close class="button large cancle">'.$lng['LNG_CONTINUE_SHOPPING'].'</a></span>';
      $content[] = '<span class="td right"><a href="'.WEB_URL.'/index.php?module=payment-login&amp;ret=product" class="button large ok">'.$lng['LNG_CHECKOUT'].'</a></span>';
      $content[] = '</div>';
      $content[] = '</article>';
      $content[] = '<script>';
      $content[] = 'inintCart("checkout_list", "product");';
      $content[] = '</script>';
      $ret['content'] = rawurlencode(implode("\n", $content));
    }
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
