<?php
// widgets/cart/index.php
if (defined('MAIN_INIT') && array_key_exists('product', $install_owners)) {
    // โมดูล
    $sql = "SELECT `id` AS `module_id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
    $index = $cache->get($sql);
    if (!$index) {
        $index = $db->customQuery($sql);
        $cache->save($sql, $index);
    }
    if (sizeof($index) == 1) {
        $index = $index[0];
        // รายการทำสินค้าล่าสุด
        $basket = array();
        $total = 0;
        $items = 0;
        $login_id = isset($_SESSION['login']['id']) ? (int) $_SESSION['login']['id'] : 0;
        $session_id = isset($_COOKIE[PREFIX.'_cart']) ? $_COOKIE[PREFIX.'_cart'] : session_id();
        // หน่วยเงิน
        $currency = $_SESSION['currency'];
        $currency_unit = $lng['CURRENCY_UNITS'][$currency];
        // ตรวจสอบตะกร้าสืนค้า
        require ROOT_PATH.'modules/product/loadbasket.php';
        $widget = array();
        $widget[] = '<div id=cart_basket class=product>';
        $widget[] = '<table class=fullwidth><tbody id=cart_tbody>';
        $widget[] = '<tr id=cart_empty'.(sizeof($basket) == 0 ? '' : ' class=hidden').'><td class=center><span class=error>{LNG_CART_EMPTY}</span></td></tr>';
        foreach ($basket as $item) {
            $id = $item['id'];
            $price = $item['net'] * $item['quantity'];
            $tr = '<tr id=cart_tr_'.$id.'>';
            $tr .= '<td><label><input id=cart_quantity_'.$id.' type=text size=1 value="'.$item['quantity'].'"></label></td>';
            $tr .= '<td class=cart_topic><span class=cuttext title="'.$item['topic'].'">'.$item['topic'].'</span></td>';
            $tr .= '<td class="right nowrap"><span id=cart_price_'.$id.'>'.gcms::int2Curr($price).'</span> '.$currency_unit.'</td>';
            $tr .= '<td><a class=icon-delete id=cart_delete_'.$id.'></a></td>';
            $tr .= '</tr>';
            $widget[] = $tr;
            $total = $total + $price;
            $items += $item['quantity'];
        }
        $widget[] = '</tbody></table>';
        $widget[] = '<p class="subtotal right">{LNG_TOTAL}&nbsp;<span id=cart_total>'.gcms::int2Curr($total).'</span>&nbsp;'.$currency_unit.'</p>';
        $widget[] = '<p class=center><a class="viewcart button large ok" id=cart_checkout>{LNG_CHECKOUT}</a></p>';
        $widget[] = '</div>';
        $widget[] = '<a id=float_cart title="{LNG_CHECKOUT}">{LNG_CART}&nbsp;{LNG_TOTAL}&nbsp;<em id=cart_items>'.$items.'</em>&nbsp;{LNG_ITEMS}</a>';
        $widget[] = '<script>';
        $widget[] = 'inintCart("cart_tbody", "product");';
        $widget[] = 'if($E("cart_items")){';
        $widget[] = '$E("cart_items").innerHTML = '.$items.';';
        $widget[] = '}';
        $widget[] = '</script>';
        $widget = implode("\n", $widget);
    }
}
