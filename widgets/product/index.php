<?php
// widgets/product/index.php
if (defined('MAIN_INIT')) {
    list($rows, $cols, $typ, $cat) = explode('_', $module);
    $cols = max($config['product_cols'], intval($cols));
    $rows = max(1, intval($rows));
    $count = $rows * $cols;
    // โมดูล product
    $index = $install_modules['product'];
    $cat = (int) $cat;
    // หน่วยเงิน
    $currency = $_SESSION['currency'];
    $currency_unit = $lng['CURRENCY_UNITS'][$currency];
    // หมวดสินค้า
    $categories = array();
    $sql = "SELECT `category_id`,`topic`,`subcategory` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]' ORDER BY `category_id`";
    $list = $cache->get($sql);
    if (!$list) {
        $list = $db->customQuery($sql);
        $cache->save($sql, $list);
    }
    foreach ($list as $item) {
        $categories[$item['category_id']] = gcms::ser2Str($item['topic']);
    }
    // query
    $q = array();
    // หมวด
    if ($cat > 0) {
        $q['cat'] = "G.`category_id`=$cat";
    }
    // icon (hot,new,recommend)
    foreach ($lng['PRODUCT_CUSTOM_ICONS'] as $k => $v) {
        if ($typ == $k) {
            $q[] = "P.`$k`='1'";
            $qs[] = "typ=$k";
        }
    }
    $q[] = 'P.`module_id`='.(int) $index['module_id'];
    $q[] = "P.`published`='1'";
    // รายการสินค้า
    $sql1 = 'FROM `'.DB_PRODUCT.'` AS P';
    $sql1 .= " INNER JOIN `".DB_PRODUCT_DETAIL."` AS D ON D.`id`=P.`id` AND D.`language` IN ('".LANGUAGE."','')";
    $sql1 .= " LEFT JOIN `".DB_PRODUCT_CATEGORY."` AS G ON G.`id`=P.`id`";
    $sql1 .= ' WHERE '.implode(' AND ', $q).' GROUP BY P.`id`';
    $sql = "SELECT P.`id`,P.`product_no`,P.`visited`,P.`comments`,P.`alias`,P.`new`,P.`hot`,P.`recommend`,D.`topic`,D.`description`";
    $sql .= " $sql1 ORDER BY P.`id` DESC LIMIT $count";
    $list = $cache->get($sql);
    if (!$list) {
        $list = $db->customQuery($sql);
        $cache->save($sql, $list);
    }
    foreach ($list as $item) {
        $products[$item['id']] = $item;
    }
    unset($list);
    // product additional
    if (sizeof($products) > 0) {
        // id ของสินค้าที่เลือก
        $ids = implode(',', array_keys($products));
        // thumbnail
        $sql = "SELECT `product_id`,`thumbnail` FROM `".DB_PRODUCT_IMAGE."` WHERE `product_id` IN ($ids) GROUP BY `product_id`";
        $datas = $cache->get($sql);
        if (!$datas) {
            $datas = $db->customQuery($sql);
            $cache->save($sql, $datas);
        }
        foreach ($datas as $i => $item) {
            $products[$item['product_id']]['thumbnail'] = $item['thumbnail'];
        }
        // additional
        $skin = gcms::loadtemplate($index['module'], 'product', 'additionalitem');
        $patt = array('/{ID}/', '/{TOPIC}/', '/{PRICE}/');
        $sql = "SELECT * FROM `".DB_PRODUCT_ADDITIONAL."` WHERE `product_id` IN ($ids) ORDER BY `id`";
        $datas = $cache->get($sql);
        if (!$datas) {
            $datas = $db->customQuery($sql);
            $cache->save($sql, $datas);
        }
        foreach ($datas as $i => $item) {
            $price = $item["price_$currency"];
            $net = $item["net_$currency"];
            if (empty($products[$item['product_id']]['net'])) {
                $products[$item['product_id']]['price'] = $price;
                $products[$item['product_id']]['net'] = $net;
            }
            $replace = array();
            $replace[] = $item['id'];
            $replace[] = $item['topic'];
            $replace[] = gcms::int2Curr($net);
            $products[$item['product_id']]['options'][] = preg_replace($patt, $replace, $skin);
        }
    }
    $widget = array('<div class=ggrid>');
    // list
    $patt = array('/{RND}/', '/{QID}/', '/{NO}/', '/{TOPIC}/', '/{DETAIL}/', '/{THUMB}/', '/{VISITED}/', '/{COMMENTS}/', '/{ADDITIONAL}/', '/{PRICE}/', '/{DISCOUNT}/', '/{SAVED}/', '/{NET}/', '/{SAVE}/', '/{STOCK}/', '/{ICONS}/', '/{URL}/', '/{ADDTOCART}/');
    $skin = gcms::loadtemplate($index['module'], 'product', 'widgetitem');
    $i = 0;
    foreach ($products as $product_id => $item) {
        if ($i > 0 && $cols > 1 && $i % $cols == 0) {
            $widget[] = '</div><div class=ggrid>';
        }
        $i++;
        $replace = array();
        $replace[] = rand(0, 4);
        $replace[] = $product_id;
        $replace[] = $item['product_no'];
        $replace[] = $item['topic'];
        $replace[] = $item['description'];
        if ($item['thumbnail'] == '' || !is_file(DATA_PATH."product/$item[thumbnail]")) {
            $replace[] = WEB_URL.'/modules/product/img/nopicture.png';
        } else {
            $replace[] = DATA_URL."product/$item[thumbnail]";
        }
        $replace[] = $item['visited'];
        $replace[] = $item['comments'];
        $instock = is_array($item['options']);
        $discount = $item['price'] - $item['net'];
        $replace[] = $instock ? implode('', $item['options']) : '';
        $replace[] = gcms::int2Curr($item['price']);
        $replace[] = gcms::int2Curr($discount);
        $replace[] = round($item['net'] == 0 ? 0 : (100 * $discount) / $item['net']);
        $replace[] = gcms::int2Curr($item['net']);
        $replace[] = $item['price'] == 0 || $item['price'] == $item['net'] ? 'hidden' : 'saved';
        $replace[] = $instock ? 'InStock' : 'OutOfStock';
        $icon = '';
        foreach ($lng['PRODUCT_CUSTOM_ICONS'] as $key => $value) {
            $icon .= $item[$key] == 1 ? '<span class='.$key.'></span>' : '';
        }
        $replace[] = $icon;
        if ($config['module_url'] == '1') {
            $replace[] = gcms::getURL($index['module'], $item['alias']);
        } else {
            $replace[] = gcms::getURL($index['module'], '', 0, $product_id);
        }
        $replace[] = $instock ? 'addtocart' : 'hidden';
        $widget[] = preg_replace($patt, $replace, $skin);
    }
    $widget[] = '</div>';
    $blocks = array(1 => 12, 2 => 6, 3 => 4, 4 => 3, 6 => 2);
    $patt = array('/{LIST}/', '/{BLOCK}/', '/{ITEMW}/', '/{UNIT}/');
    $replace = array();
    $replace[] = implode('', $widget);
    $replace[] = $blocks[$cols];
    $replace[] = 100 / $cols;
    $replace[] = $lng['CURRENCY_UNITS'][$currency];
    $widget = preg_replace($patt, $replace, gcms::loadtemplate($module, 'product', 'widget'));
}
