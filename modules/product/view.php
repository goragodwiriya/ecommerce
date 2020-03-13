<?php
// modules/product/view.php
if (defined('MAIN_INIT')) {
  // ค่า ที่ส่งมา
  $id = (int)$_REQUEST['id'];
  $page = (int)$_REQUEST['page'];
  $cat = (int)$_REQUEST['cat'];
  $a = (int)$_REQUEST['a'];
  $search = preg_replace('/[+\s]+/u', ' ', $_REQUEST['q']);
  // query ข้อมูล
  $sql = "SELECT P.*,M.`module`,D.`topic`,D.`description`,D.`keywords`,D.`detail`";
  $sql .= " FROM `".DB_PRODUCT."` AS P";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=P.`module_id` AND M.`owner`='product'";
  $sql .= " INNER JOIN `".DB_PRODUCT_DETAIL."` AS D ON D.`id`=P.`id` AND D.`language` IN ('".LANGUAGE."','')";
  $sql .= " WHERE ".($modules[4] == '' ? "P.`id`='$id'" : "P.`alias`='".addslashes($modules[4])."'");
  $sql .= " LIMIT 1";
  $index = $cache->get($sql);
  if (!$index) {
    $index = $db->customQuery($sql);
    $index = sizeof($index) == 0 ? false : $index[0];
  }
  if ((int)$index['id'] == 0) {
    $title = $lng['PAGE_NOT_FOUND'];
    $content = '<div class=error>'.$title.'</div>';
  } else {
    // อัปเดตเปิดดู
    if ((int)$_REQUEST['visited'] == 0) {
      $index['visited'] ++;
      $db->edit(DB_PRODUCT, $index['id'], array('visited' => $index['visited']));
    }
    if (!$cache->is_cache()) {
      $cache->save($sql, $index);
    }
    // หน่วยเงิน
    $currency = $_SESSION['currency'];
    $currency_unit = $lng['CURRENCY_UNITS'][$currency];
    $curr_options = array();
    foreach ($lng['CURRENCY_UNITS'] AS $u => $t) {
      $sel = $u == $currency ? ' selected' : '';
      $curr_options[] = '<option value='.$u.$sel.'>'.$t.'</option>';
    }
    // query รูปภาพ
    $sql = "SELECT `thumbnail`,`image` FROM `".DB_PRODUCT_IMAGE."` WHERE `product_id`='$index[id]' ORDER BY `id` ASC";
    $result = $cache->get($sql);
    if (!$result) {
      $result = $db->customQuery($sql);
      $cache->save($sql, $result);
    }
    // รูปภาพ slide show
    $image_src = '';
    $pictures = array();
    if (sizeof($result) > 0) {
      // โฟลเดอร์เก็บรูป
      $dir = DATA_URL.'product/';
      foreach ($result AS $item) {
        if ($image_src == '') {
          // image สำหรับ facebook
          $image_src = $dir.$item['image'];
        }
        $pictures[] = array(
          'picture' => $dir.$item['image'],
          'url' => ''
        );
      }
    }
    // ข้อมูลการ login
    $login = $_SESSION['login'];
    // แสดงความคิดเห็น และ ฟอร์ม reply
    $canReply = $index['can_reply'] == 1 && in_array($login['status'], $config['product_can_reply']);
    // ความคิดเห็น
    $comments = array();
    // ลิสต์ความคิดเห็น
    $patt = array('/{DETAIL}/', '/{UID}/', '/{DISPLAYNAME}/', '/{CREATE}/', '/{TIME}/', '/{IP}/', '/{NO}/', '/{QID}/', '/{RID}/', '/{RATING}/');
    $skin = gcms::loadtemplate($index['module'], 'product', 'commentitem');
    // query
    $sql = "SELECT C.*";
    $sql .= " FROM `".DB_COMMENT."` AS C";
    $sql .= " WHERE C.`index_id`='$index[id]' AND C.`module_id`='$index[module_id]' AND C.`published`=1";
    $sql .= " ORDER BY C.`id` ASC";
    $datas = $cache->get($sql);
    if (!$datas) {
      $datas = $db->customQuery($sql);
      $cache->save($sql, $datas);
    }
    foreach ($datas AS $i => $item) {
      // rating
      $star = '';
      for ($n = 1; $n < 6; $n++) {
        $star .= '<span class="icon-star'.($n <= $item['rating'] ? '2' : '0').'"></span>';
      }
      $replace = array();
      $replace[] = gcms::HighlightSearch(gcms::showDetail($item['detail'], true), $search);
      $replace[] = (int)$item['member_id'];
      $replace[] = $item['email'];
      $replace[] = gcms::mktime2date($item['last_update']);
      $replace[] = date('Y-m-d H:i', $item['last_update']);
      $replace[] = gcms::showip($item['ip']);
      $replace[] = $i + 1;
      $replace[] = $item['index_id'];
      $replace[] = $item['id'];
      $replace[] = $star;
      $comments[] = preg_replace($patt, $replace, $skin);
    }
    $instock = false;
    // product additional
    $prices = array();
    $skin = gcms::loadtemplate($index['module'], 'product', 'additionalitem');
    $patt = array('/{ID}/', '/{TOPIC}/', '/{PRICE}/');
    $sql = "SELECT * FROM `".DB_PRODUCT_ADDITIONAL."` WHERE `product_id`='$index[id]' ORDER BY `id`";
    $datas = $cache->get($sql);
    if (!$datas) {
      $datas = $db->customQuery($sql);
      $cache->save($sql, $datas);
    }
    foreach ($datas AS $i => $item) {
      $price = $item["price_$currency"];
      $net = $item["net_$currency"];
      if ($i == 0) {
        $prices['id'] = $item['id'];
        $prices['price'] = $price;
        $prices['net'] = $net;
        $prices['stock'] = $item['stock'];
      }
      if ($item['stock'] != 0) {
        $replace = array();
        $replace[] = $item['id'];
        $replace[] = $item["topic"] == "" ? $index["topic"] : $item["topic"];
        $replace[] = gcms::int2Curr($net);
        $prices['options'][] = preg_replace($patt, $replace, $skin);
        // มีสินค้า
        $instock = true;
      }
    }
    if ($canReply) {
      // antispam
      $register_antispamchar = gcms::rndname(32);
      $_SESSION[$register_antispamchar] = gcms::rndname(4);
    }
    // breadcrumbs
    $breadcrumb = gcms::loadtemplate($index['module'], '', 'breadcrumb');
    $breadcrumbs = array();
    // หน้าหลัก
    $canonical = WEB_URL.'/index.php';
    $breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', $canonical, $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
    // โมดูล
    if ($index['module'] != $module_list[0]) {
      $m = $install_modules[$index['module']]['menu_text'];
      $breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL($index['module']), $install_modules[$index['module']]['menu_tooltip'], ($m == '' ? $index['module'] : $m), $breadcrumb);
    }
    // category
    if ($cat > 0 && $index['category'] != '') {
      $breadcrumbs['CATEGORY'] = gcms::breadcrumb('', gcms::getURL($index['module'], '', $index['category_id']), gcms::ser2Str($index['cat_tooltip']), gcms::ser2Str($index['category']), $breadcrumb);
    }
    if ($cat > 0 && $index['category'] != '') {
      // หมวด
      $breadcrumbs['CATEGORY'] = array(gcms::getURL($index['module'], '', $index['category_id']), $index['category'], $index['category']);
    }
    // url ของสินค้านี้
    if ($config['module_url'] == '1') {
      $canonical = gcms::getURL($index['module'], $index['alias']);
    } else {
      $canonical = gcms::getURL($index['module'], '', 0, $index['id']);
    }
    // แสดงผล list รายการ
    $patt = array('/{BREADCRUMS}/', '/{COMMENTLIST}/', '/{DETAIL}/', '/{DESCRIPTION}/', '/{LASTUPDATE}/', '/{TIMEUPDATE}/', '/{VISITED}/',
      '/{COMMENTS}/', '/{REPLYFORM}/', '/{IMAGE}/', '/{PICTURES}/', '/{ADDITIONAL}/', '/{ANTISPAM}/', '/{ANTISPAMVAL}/',
      '/{DELETE}/', '/{QID}/', '/{NO}/', '/{TOPIC}/', '/{PRICE}/', '/{DISCOUNT}/', '/{SAVED}/', '/{NET}/',
      '/{SAVE}/', '/{AID}/', '/{ICONS}/', '/{STOCK}/', '/{URL}/', '/{UNIT}/', '/{CURRENCYUNIT}/', '/{CURRENCYOPTIONS}/', '/{CATEGORY}/',
      '/{WIDGET_([A-Z]+)(([\s_])(.*))?}/e', '/{(LNG_[A-Z_]+)}/e', '/{MODULEID}/');
    $replace = array();
    $replace[] = implode("\n", $breadcrumbs);
    $replace[] = !empty($comments) ? implode("\n", $comments) : '<div class=message>{LNG_REVIEW_EMPTY}</div>';
    $replace[] = gcms::HighlightSearch(gcms::showDetail($index['detail'], true, false), $search);
    $replace[] = $index['description'];
    $replace[] = gcms::mktime2date($index['last_update'], 'd M Y');
    $replace[] = date('Y-m-d H:i', $index['last_update']);
    $replace[] = $index['visited'];
    $replace[] = $index['comments'];
    $replace[] = $canReply ? gcms::loadtemplate($index['module'], 'product', 'reply') : '<div class=error>{LNG_REVIEW_ERROR}</div>';
    $replace[] = $image_src;
    $replace[] = json_encode($pictures);
    $replace[] = $instock && is_array($prices['options']) ? implode('', $prices['options']) : '';
    $replace[] = $register_antispamchar;
    $replace[] = $isAdmin ? $_SESSION[$register_antispamchar] : '';
    $replace[] = $moderator ? '{LNG_DELETE}' : '{LNG_SEND_DELETE}';
    $replace[] = $index['id'];
    $replace[] = $index['product_no'];
    $replace[] = $index['topic'];
    $discount = $prices['price'] - $prices['net'];
    $replace[] = gcms::int2Curr($prices['price']);
    $replace[] = gcms::int2Curr($discount);
    $replace[] = gcms::int2Curr($prices['net'] == 0 ? 0 : ((100 * $discount) / $prices['net']));
    $replace[] = gcms::int2Curr($prices['net']);
    $replace[] = $prices['price'] == 0 ? 'hidden' : 'saved';
    $replace[] = $prices['id'];
    $stat = array();
    $stat[] = $index['new'] == 1 ? '<span class=new></span>' : '';
    $stat[] = $index['hot'] == 1 ? '<span class=hot></span>' : '';
    $stat[] = $index['recommend'] == 1 ? '<span class=recommend></span>' : '';
    $replace[] = implode('', $stat);
    $replace[] = $instock ? 'InStock' : 'OutOfStock';
    $replace[] = $canonical;
    $replace[] = $lng['CURRENCY_UNITS'][$currency];
    $replace[] = $currency;
    $replace[] = implode('', $curr_options);
    $replace[] = $index['category_id'];
    $replace[] = 'gcms::getWidgets';
    $replace[] = 'gcms::getLng';
    $replace[] = $index['module_id'];
    $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'product', 'view'));
    // title,keywords,description
    $title = $index['topic'];
    $keywords = $index['keywords'];
    $description = $index['description'];
  }
  // เลือกเมนู
  $menu = $install_modules[$index['module']]['alias'];
  $menu = $menu == '' ? $index['module'] : $menu;
}
