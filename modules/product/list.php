<?php
// modules/product/list.php
if (defined('MAIN_INIT')) {
  // ตรวจสอบโมดูล
  $sql = "SELECT I.`module_id`,M.`module`,D.`detail`,D.`topic`,D.`description`,D.`keywords`";
  $sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
  $sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`index`='1' AND I.`language`=D.`language` AND I.`module_id`=D.`module_id` ";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id` AND M.`owner`='product'";
  $sql .= " WHERE D.`language` IN ('".LANGUAGE."', '') LIMIT 1";
  $index = $cache->get($sql);
  if (!$index) {
    $index = $db->customQuery($sql);
    $cache->save($sql, $index);
  }
  if (sizeof($index) == 0) {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content = '<div class=error>'.$title.'</div>';
  } else {
    $index = $index[0];
    // เรียงลำดับ
    $orders = array();
    $orders[] = array($lng['PRODUCT_SORT'][0], '`id` DESC');
    $orders[] = array($lng['PRODUCT_SORT'][1], '`topic` ASC');
    $orders[] = array($lng['PRODUCT_SORT'][2], '`topic` DESC');
    $orders[] = array($lng['PRODUCT_SORT'][3], '`price` DESC,`id` ASC');
    $orders[] = array($lng['PRODUCT_SORT'][4], '`price` ASC,`id` ASC');
    $orders[] = array($lng['PRODUCT_SORT'][5], '`visited` DESC,`id` ASC');
    // รายการเรียงลำดับ
    $order = (int)(isset($_REQUEST['order']) ? $_REQUEST['order'] : $config['product_sort']);
    $order = min(sizeof($orders), max(0, $order));
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
    foreach ($list AS $item) {
      $categories[$item['category_id']] = gcms::ser2Str($item['topic']);
    }
    // query
    $qs = array();
    $q = array();
    // หมวด
    $cat = (int)$_REQUEST['cat'];
    if ($cat > 0) {
      $q['cat'] = "G.`category_id`=$cat";
      $qs[] = "cat=$cat";
    }
    // หมวดย่อย
    $sub = (int)$_REQUEST['sub'];
    if ($sub > 0) {
      $q['sub'] = "G.`subcategory`=$sub";
      $qs[] = "sub=$sub";
    }
    // icon (hot,new,recommend)
    $typ = $_REQUEST['typ'];
    foreach ($lng['PRODUCT_CUSTOM_ICONS'] AS $k => $v) {
      if ($typ == $k) {
        $q[] = "P.`$k`='1'";
        $qs[] = "typ=$k";
      }
    }
    $q[] = 'P.`module_id`='.$index['module_id'];
    $q[] = "P.`published`='1'";
    // default query
    $sql1 = 'FROM `'.DB_PRODUCT.'` AS P';
    $sql1 .= " LEFT JOIN `".DB_PRODUCT_CATEGORY."` AS G ON G.`id`=P.`id`";
    $sql1 .= ' WHERE '.implode(' AND ', $q).' GROUP BY P.`id`';
    // breadcrumbs
    $breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
    $breadcrumbs = array();
    // หน้าหลัก
    $canonical = WEB_URL.'/index.php';
    $breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', $canonical, $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
    if ($index['module'] != $module_list[0]) {
      // url ของหน้านี้
      $canonical = gcms::getURL($index['module']);
      $menu_text = $install_modules[$index['module']]['menu_text'];
      $breadcrumbs['MODULE'] = gcms::breadcrumb('', $canonical, $install_modules[$index['module']]['menu_tooltip'], ($menu_text == '' ? $index['topic'] : $menu_text), $breadcrumb);
    }
    if ($cat > 0 && $categories[$cat] != '') {
      // breadcrumb ของ หมวด
      $breadcrumbs['CATEGORY'] = gcms::breadcrumb('', gcms::getURL($index['module'], '', $cat), $categories[$cat], $categories[$cat], $breadcrumb);
    }
    // จำนวนสินค้าทั้งหมด
    $sql = "SELECT COUNT(*) AS `count` FROM (SELECT P.* $sql1) AS Q";
    $count = $cache->get($sql);
    if (!$count) {
      $count = $db->customQuery($sql);
      $cache->save($sql, $count);
    }
    // list รายการ
    $products = array();
    if ($count[0]['count'] > 0) {
      // จำนวนที่ต้องการ
      $product_count = $config['product_rows'] * $config['product_cols'];
      // หน้าที่เรียก
      $page = (int)$_REQUEST['page'];
      $totalpage = round($count[0]['count'] / $product_count);
      $totalpage += ($totalpage * $product_count < $count[0]['count']) ? 1 : 0;
      $page = $page > $totalpage ? $totalpage : $page;
      $page = $page < 1 ? 1 : $page;
      $start = $product_count * ($page - 1);
      // รายการสินค้า
      $sql = "SELECT P.`id`,P.`product_no`,P.`visited`,P.`comments`,P.`alias`,P.`new`,P.`hot`,P.`recommend`";
      $sql .= " $sql1 ORDER BY ".$orders[$order][1]." LIMIT $start,$product_count";
      $list = $cache->get($sql);
      if (!$list) {
        $list = $db->customQuery($sql);
        $cache->save($sql, $list);
      }
      foreach ($list AS $item) {
        $products[$item['id']] = $item;
      }
      unset($list);
      // ราคาสินค้าและตัวเลือกสินค้าอื่นๆ
      if (sizeof($products) > 0) {
        // id ของสินค้าที่เลือก
        $ids = implode(',', array_keys($products));
        // details
        $sql = "SELECT `id`,`topic`,`description` FROM `".DB_PRODUCT_DETAIL."`";
        $sql .= " WHERE `id` IN ($ids) AND `language` IN ('','".LANGUAGE."') ORDER BY `topic` DESC";
        $datas = $cache->get($sql);
        if (!$datas) {
          $datas = $db->customQuery($sql);
          $cache->save($sql, $datas);
        }
        foreach ($datas AS $i => $item) {
          $products[$item['id']]['topic'] = $item['topic'];
          $products[$item['id']]['description'] = $item['description'];
        }
        // thumbnail
        $sql = "SELECT `product_id`,`thumbnail` FROM `".DB_PRODUCT_IMAGE."` WHERE `product_id` IN ($ids) GROUP BY `product_id`";
        $datas = $cache->get($sql);
        if (!$datas) {
          $datas = $db->customQuery($sql);
          $cache->save($sql, $datas);
        }
        foreach ($datas AS $i => $item) {
          $products[$item['product_id']]['thumbnail'] = $item['thumbnail'];
        }
        // additional
        $skin = gcms::loadtemplate($index['module'], 'product', 'additionalitem');
        $patt = array('/{ID}/', '/{TOPIC(-([0-9]+))?}/e', '/{PRICE}/');
        $sql = "SELECT * FROM `".DB_PRODUCT_ADDITIONAL."` WHERE `product_id` IN ($ids) ORDER BY `id`";
        $datas = $cache->get($sql);
        if (!$datas) {
          $datas = $db->customQuery($sql);
          $cache->save($sql, $datas);
        }
        foreach ($datas AS $i => $item) {
          $price = $item["price_$currency"];
          $net = $item["net_$currency"];
          if ((int)$products[$item['product_id']]['net'] == 0) {
            $products[$item['product_id']]['price'] = $price;
            $products[$item['product_id']]['net'] = $net;
          }
          $replace = array();
          $replace[] = $item['id'];
          $replace[] = create_function('$matches', 'return gcms::cutstring("'.$item['topic'].'",(int)$matches[2]);');
          $replace[] = gcms::int2Curr($net);
          $products[$item['product_id']]['options'][] = gcms::pregReplace($patt, $replace, $skin);
        }
      }
      // list
      $items = array();
      $patt = array('/{RND}/', '/{QID}/', '/{NO}/', '/{TOPIC(-([0-9]+))?}/e', '/{DETAIL(-([0-9]+))?}/e', '/{THUMB}/', '/{VISITED}/', '/{COMMENTS}/', '/{ADDITIONAL}/', '/{PRICE}/', '/{DISCOUNT}/', '/{SAVED}/', '/{NET}/', '/{SAVE}/', '/{STOCK}/', '/{ICONS}/', '/{URL}/', '/{ADDTOCART}/');
      $skin = gcms::loadtemplate($index['module'], 'product', 'listitem');
      foreach ($products AS $product_id => $item) {
        $replace = array();
        $replace[] = rand(0, 4);
        $replace[] = $product_id;
        $replace[] = $item['product_no'];
        $replace[] = create_function('$matches', 'return gcms::cutstring("'.$item['topic'].'",(int)$matches[2]);');
        $replace[] = create_function('$matches', 'return gcms::cutstring("'.$item['description'].'",(int)$matches[2]);');
        if ($item['thumbnail'] == '' || !is_file(DATA_PATH."product/$item[thumbnail]")) {
          $replace[] = WEB_URL.'/modules/product/img/nopicture.png';
        } else {
          $replace[] = DATA_URL."product/$item[thumbnail]";
        }
        $replace[] = $item['visited'];
        $replace[] = $item['comments'];
        $instock = is_array($item['options']);
        $prices = $additional[$product_id];
        $discount = $item['price'] - $item['net'];
        $replace[] = $instock ? implode('', $item['options']) : '';
        $replace[] = gcms::int2Curr($item['price']);
        $replace[] = gcms::int2Curr($discount);
        $replace[] = round($item['net'] == 0 ? 0 : (100 * $discount) / $item['net']);
        $replace[] = gcms::int2Curr($item['net']);
        $replace[] = $item['price'] == 0 || $item['price'] == $item['net'] ? 'hidden' : 'saved';
        $replace[] = $instock ? 'InStock' : 'OutOfStock';
        $icon = '';
        foreach ($lng['PRODUCT_CUSTOM_ICONS'] AS $key => $value) {
          $icon .= $item[$key] == 1 ? '<span class='.$key.'></span>' : '';
        }
        $replace[] = $icon;
        if ($config['module_url'] == '1') {
          $replace[] = gcms::getURL($index['module'], $item['alias']);
        } else {
          $replace[] = gcms::getURL($index['module'], '', 0, $product_id);
        }
        $replace[] = $instock ? 'addtocart' : 'hidden';
        $items[] = gcms::pregReplace($patt, $replace, $skin);
      }
      // แบ่งหน้า
      $maxlink = 9;
      $totalpage = round($count[0]['count'] / $product_count);
      $totalpage += ($totalpage * $product_count < $count[0]['count']) ? 1 : 0;
      $qs[] = 'page=%1';
      $url = '<a href="'.gcms::getURL($index['module'], '', 0, 0, implode('&amp;', $qs)).'">%1</a>';
      if ($totalpage > $maxlink) {
        $start = $page - floor($maxlink / 2);
        if ($start < 1) {
          $start = 1;
        } elseif ($start + $maxlink > $totalpage) {
          $start = $totalpage - $maxlink + 1;
        }
      } else {
        $start = 1;
      }
      $splitpage = ($start > 2) ? str_replace('%1', 1, $url) : '';
      for ($i = $start; $i <= $totalpage && $maxlink > 0; $i++) {
        $splitpage .= ($i == $page) ? '<strong>'.$i.'</strong>' : str_replace('%1', $i, $url);
        $maxlink--;
      }
      $splitpage .= ($i < $totalpage) ? str_replace('%1', $totalpage, $url) : '';
    }
    $splitpage = $splitpage == '' ? '<strong>1</strong>' : $splitpage;
    // แสดงผล list รายการ
    $patt = array('/{BREADCRUMS}/', '/{LIST}/', '/{TOPIC}/', '/{DETAIL}/', '/{SPLITPAGE}/', '/{UNIT}/', '/{WIDGET_([A-Z]+)(([\s_])(.*))?}/e', '/{(LNG_[A-Z_]+)}/e', '/{COLS}/');
    $replace = array();
    $replace[] = implode("\n", $breadcrumbs);
    $replace[] = sizeof($items) == 0 ? '<div class=error>'.$lng['LNG_LIST_EMPTY'].'</div>' : implode("\n", $items);
    $replace[] = $index['topic'];
    $replace[] = $index['detail'];
    $replace[] = $splitpage;
    $replace[] = $currency_unit;
    $replace[] = 'gcms::getWidgets';
    $replace[] = 'gcms::getLng';
    $replace[] = $config['product_cols'];
    $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'product', 'list'));
    // title,keywords,description
    $title = $index['topic'];
    $keywords = $index['keywords'];
    $description = $index['description'];
  }
  // เลือกเมนู
  $menu = $install_modules[$index['module']]['alias'];
  $menu = $menu == '' ? $index['module'] : $menu;
}
