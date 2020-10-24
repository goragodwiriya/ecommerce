<?php
// modules/product/admin_setup.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_can_write'])) {
  // กำหนดเมนูเรียงลำดับ
  $orders = array();
  $orders[] = array('{LNG_NEW_PRODUCT}', '`id` DESC');
  $orders[] = array('{LNG_PRODUCT} ({LNG_ID} {LNG_NSORT_ASC})', '`id` ASC');
  $orders[] = array('{LNG_LAST_UPDATE}', '`last_update` DESC,`id` DESC');
  $orders[] = array('{LNG_PRODUCT_ID} ({LNG_SORT_ASC})', '`product_no` ASC');
  $orders[] = array('{LNG_PRODUCT_ID} ({LNG_SORT_DESC})', '`product_no` DESC');
  $orders[] = array('{LNG_PRODUCT_TOPIC} {LNG_SORT_ASC}', '`topic` ASC');
  $orders[] = array('{LNG_PRODUCT_TOPIC} {LNG_SORT_DESC}', '`topic` DESC');
  $orders[] = array('{LNG_VIEWS}', '`visited` DESC,`topic` ASC');
  // ตรวจสอบโมดูลที่เรียก
  $sql = "SELECT `id`,`module` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 0) {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    $index = $index[0];
    // รายการเรียงลำดับที่เลือก
    $order = (int)(isset($_GET['order']) ? $_GET['order'] : $_COOKIE['product_order']);
    $order = min(sizeof($orders), max(0, $order));
    $url_query['order'] = $order;
    // เมนูเรียงลำดับ
    $orderoptions = array();
    foreach ($orders AS $i => $item) {
      $sel = $i == $order ? ' selected' : '';
      $orderoptions[] = '<option value='.$i.$sel.'>'.$item[0].'</option>';
    }
    // ค่าที่ส่งมา
    $q = array();
    // หมวด
    $cat = (int)$_GET['cat'];
    if ($cat > 0) {
      $q[] = 'G.`category_id`='.$cat;
      $url_query['cat'] = $cat;
    }
    // ค้นหา
    $search = preg_replace('/[\+\s]+/u', ' ', $db->sql_trim_str($_GET['search']));
    if ($search != '') {
      $q[] = "P.`product_no`='$search' OR (D.`topic` LIKE '%$search%' OR D.`detail` LIKE '%$search%')";
      $url_query['search'] = urlencode($search);
    }
    $q[] = "P.module_id=$index[id]";
    // default query
    $sql1 = " FROM `".DB_PRODUCT."` AS P";
    $sql1 .= " INNER JOIN `".DB_PRODUCT_DETAIL."` AS D ON D.`id`=P.`id` AND D.`language` IN ('".LANGUAGE."','')";
    $sql1 .= " INNER JOIN `".DB_PRODUCT_CATEGORY."` AS G ON G.`id`=P.`id`";
    $sql1 .= " WHERE ".implode(' AND ', $q);
    // จำนวนสินค้าทั้งหมด
    $sql = "SELECT COUNT(*) AS `count` $sql1";
    $count = $db->customQuery($sql);
    // รายการเรียงลำดับที่เลือก
    $order = (int)(isset($_GET['order']) ? $_GET['order'] : $_COOKIE['product_order']);
    $order = min(sizeof($orders), max(0, $order));
    $url_query['order'] = $order;
    // เมนูเรียงลำดับ
    $orderoptions = array();
    foreach ($orders AS $i => $item) {
      $sel = $i == $order ? ' selected' : '';
      $orderoptions[] = '<option value='.$i.$sel.'>'.$item[0].'</option>';
    }
    // รายการต่อหน้า
    $list_per_page = (int)(isset($_GET['count']) ? $_GET['count'] : $_COOKIE['product_listperpage']);
    $list_per_page = $list_per_page == 0 ? 30 : $list_per_page;
    // หน้าที่เลือก
    $page = max(1, (int)$_GET['page']);
    // ตรวจสอบหน้าที่เลือกสูงสุด
    $totalpage = round($count[0]['count'] / $list_per_page);
    $totalpage += ($totalpage * $list_per_page < $count[0]['count']) ? 1 : 0;
    $page = max(1, $page > $totalpage ? $totalpage : $page);
    $start = $list_per_page * ($page - 1);
    // คำนวณรายการที่แสดง
    $s = $start < 0 ? 0 : $start + 1;
    $e = min($count[0]['count'], $s + $list_per_page - 1);
    $patt2 = array('/{SEARCH}/', '/{COUNT}/', '/{PAGE}/', '/{TOTALPAGE}/', '/{START}/', '/{END}/');
    $replace2 = array($search, $count[0]['count'], $page, $totalpage, $s, $e);
    // บันทึกลง cookie
    setCookie('product_order', $order, time() + 3600 * 24 * 365);
    setCookie('product_listperpage', $list_per_page, time() + 3600 * 24 * 365);
    // title
    $title = $lng['LNG_ALL_PRODUCTS'].' '.$lng['LNG_SORT_ORDER'].' '.$orders[$order][0];
    $a = array();
    $a[] = '<span class=icon-product>{LNG_MODULES}</span>';
    $a[] = '<a href="{URLQUERY?module=product-config}">{LNG_PRODUCT}</a>';
    $a[] = '{LNG_PRODUCT_LIST}';
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-list>'.$title.'</h1></header>';
    // form
    $content[] = '<form class=table_nav method=get action=index.php>';
    // เรียงลำดับ
    $content[] = '<fieldset>';
    $content[] = '<label>{LNG_SORT_ORDER} <select name=order>';
    $content[] = implode("\n", $orderoptions);
    $content[] = '</select></label>';
    $content[] = '</fieldset>';
    // รายการต่อหน้า
    $content[] = '<fieldset>';
    $content[] = '<label>{LNG_LIST_PER_PAGE} <select name=count>';
    foreach (array(10, 20, 30, 40, 50, 100) AS $item) {
      $sel = $item == $list_per_page ? ' selected' : '';
      $content[] = '<option value='.$item.$sel.'>'.$item.' {LNG_ITEMS}</option>';
    }
    $content[] = '</select></label>';
    $content[] = '</fieldset>';
    // หมวดหมู่
    $categories = array();
    $sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' ORDER BY `category_id`";
    $content[] = '<fieldset>';
    $content[] = '<label>{LNG_PRODUCT_CATEGORY} <select name=cat>';
    $content[] = '<option value=0>{LNG_ALL_ITEMS}</option>';
    foreach ($db->customQuery($sql) AS $item) {
      $categories[$item['category_id']] = gcms::ser2Str($item['topic']);
      if ($categories[$item['category_id']] == '') {
        $categories[$item['category_id']] = '-';
      }
      $sel = $cat == $item['category_id'] ? ' selected' : '';
      $content[] = '<option value='.$item['category_id'].$sel.'>'.$categories[$item['category_id']].'</option>';
    }
    $content[] = '</select></label>';
    $content[] = '</fieldset>';
    // submit
    $content[] = '<fieldset>';
    $content[] = '<input type=submit class="button go" value="{LNG_GO}">';
    $content[] = '</fieldset>';
    // search
    $content[] = '<fieldset class=search>';
    $content[] = '<label accesskey=f><input type=text name=search value="'.$search.'" placeholder="{LNG_SEARCH_TITLE}" title="{LNG_SEARCH_TITLE}"></label>';
    $content[] = '<input type=submit value="&#xE607;" title="{LNG_SEARCH}">';
    $content[] = '<input type=hidden name=module value=product-setup>';
    $content[] = '<input type=hidden name=page value=1>';
    $content[] = '</fieldset>';
    $content[] = '</form>';
    // ตารางข้อมูล
    $content[] = '<table id=product class="tbl_list fullwidth">';
    $content[] = '<caption>'.preg_replace($patt2, $replace2, $search != '' ? $lng['SEARCH_RESULT'] : $lng['ALL_ITEMS']).'</caption>';
    $content[] = '<thead>';
    $content[] = '<tr>';
    $content[] = '<th id=c0 scope=col>{LNG_PRODUCT_ID}</th>';
    $content[] = '<th id=c1 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
    $content[] = '<th id=c2 scope=col>{LNG_PRODUCT_TOPIC}</th>';
    $content[] = '<th id=c3 scope=col class=center>{LNG_PUBLISHED}</th>';
    $content[] = '<th id=c4 scope=col class="tablet center">{LNG_PRODUCT_CATEGORY}</th>';
    $content[] = '<th id=c5 scope=col class="tablet center">{LNG_LAST_UPDATE}</th>';
    $content[] = '<th id=c6 scope=col class="tablet center">{LNG_VIEWS}</th>';
    $content[] = '<th id=c8 scope=col colspan=2>&nbsp;</th>';
    $content[] = '</tr>';
    $content[] = '</thead>';
    $content[] = '<tbody>';
    // สินค้าทั้งหมด
    $sql = "SELECT P.`id`,P.`product_no`,P.`published`,P.`visited`,P.`comments`,P.`alias`,G.`category_id`,D.`topic`,P.`last_update`";
    $sql .= " $sql1 ORDER BY ".$orders[$order][1]." LIMIT $start,$list_per_page";
    foreach ($db->customQuery($sql) AS $item) {
      $id = $item['id'];
      $tr = '<tr id=M_'.$id.'>';
      $tr .= '<th headers=c0 id=r'.$id.' class="no left" scope=row>'.$item['product_no'].'</th>';
      $tr .= '<td headers="r'.$id.' c1" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
      $tr .= '<td headers="r'.$id.' c2">'.$item['topic'].'</td>';
      $tr .= '<td headers="r'.$id.' c3" class=menu><span class=icon-published'.$item['published'].' title="'.$lng['LNG_PUBLISHEDS'][$item['published']].'"></span></td>';
      $tr .= '<td headers="r'.$id.' c4" class="tablet center">'.(isset($categories[$item['category_id']]) ? $categories[$item['category_id']] : '').'</td>';
      $tr .= '<td headers="r'.$id.' c5" class="tablet date" title="'.$item['ip'].'">'.gcms::mktime2date($item['last_update'], 'd M Y H:i').'</td>';
      $tr .= '<td headers="r'.$id.' c6" class="tablet visited">'.$item['visited'].'</td>';
      $tr .= '<td headers="r'.$id.' c8" class=menu><a href="{URLQUERY?module=product-write&src=product-setup&spage='.$page.'&qid='.$id.'}" title="{LNG_EDIT}" class=icon-edit></a></td>';
      $tr .= '<td headers="r'.$id.' c8" class=menu><a href="{URLQUERY?module=product-upload&src=product-setup&spage='.$page.'&qid='.$id.'}" title="{LNG_PRODUCT_PICTURE}" class=icon-upload>&nbsp;</a></td>';
      $tr .= '</tr>';
      $content[] = $tr;
    }
    $content[] = '</tbody>';
    $content[] = '<tfoot>';
    $content[] = '<tr>';
    $content[] = '<td headers=c0></td>';
    $content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
    $content[] = '<td headers=c2 colspan=7></td>';
    $content[] = '</tr>';
    $content[] = '</tfoot>';
    $content[] = '</table>';
    // แบ่งหน้า
    $maxlink = 9;
    $url = '<a href="{URLQUERY?page=%d}" title="{LNG_DISPLAY_PAGE} %d">%d</a>';
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
    $splitpage = ($start > 2) ? str_replace('%d', 1, $url) : '';
    for ($i = $start; $i <= $totalpage && $maxlink > 0; $i++) {
      $splitpage .= ($i == $page) ? '<strong title="{LNG_DISPLAY_PAGE} '.$i.'">'.$i.'</strong>' : str_replace('%d', $i, $url);
      $maxlink--;
    }
    $splitpage .= ($i < $totalpage) ? str_replace('%d', $totalpage, $url) : '';
    $splitpage = $splitpage == '' ? '<strong title="{LNG_DISPLAY_PAGE} '.$i.'">1</strong>' : $splitpage;
    $content[] = '<p class=splitpage>'.$splitpage.'</p>';
    $content[] = '<div class=table_nav>';
    // sel action
    $content[] = '<fieldset>';
    $sel = array();
    $sel[] = '<select id=sel_action>';
    // delete
    $sel[] = '<option value=delete_product>{LNG_DELETE}</option>';
    // published
    foreach ($lng['LNG_PUBLISHEDS'] AS $i => $value) {
      $sel[] = '<option value=published_'.$i.'>'.$value.'</option>';
    }
    $sel[] = '</select>';
    // คำสั่งทำงานล่าสุด
    $action = $_GET['action'];
    $content[] = str_replace('value='.$action.'>', 'value='.$action.' selected>', implode('', $sel));
    $content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action>{LNG_SELECT_ACTION}</label>';
    $content[] = '</fieldset>';
    // add
    $content[] = '<fieldset>';
    $content[] = '<a class="button add" href="{URLQUERY?module=product-write&src=product-setup&qid=0}"><span class=icon-add>{LNG_ADD_NEW} {LNG_PRODUCT}</span></a>';
    $content[] = '</fieldset>';
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
    $url_query['module'] = 'product-setup';
    $url_query['page'] = $page;
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
