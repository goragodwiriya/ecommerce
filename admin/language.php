<?php
// admin/language.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // กำหนดเมนูเรียงลำดับ
  $orders = array();
  $orders[] = array('{LNG_ID}', '`id` DESC');
  $orders[] = array('{LNG_MODULE}', '`owner` ASC,`key` ASC');
  $orders[] = array('{LNG_LANGUAGE_VARIABLE}', '`c` DESC,`key`,`owner`');
  $orders[] = array('{LNG_TYPE}', '`type`,`key`');
  foreach ($install_languages AS $k) {
    $orders[] = array($k, '`c` DESC,`'.$k.'`,`key`,`owner`');
  }
  // รายการเรียงลำดับที่เลือก
  $order = (int)(isset($_GET['order']) ? $_GET['order'] : $_COOKIE['language_order']);
  $order = min(sizeof($orders), max(0, $order));
  $url_query['order'] = $order;
  // เมนูเรียงลำดับ
  $orderoptions = array();
  foreach ($orders AS $i => $item) {
    $sel = $i == $order ? 'selected' : '';
    $orderoptions[] = "<option value=$i $sel>$item[0]</option>";
  }
  // ประเภททีเลือก
  $js = $_GET['js'] == 1 ? 1 : 0;
  $where = "WHERE `js`='$js'";
  $where .= $design_mode == 'design' ? '' : " AND `owner`!='sysadmin'";
  // ข้อความค้นหา
  $search = $db->sql_trim_str($_GET['search']);
  // ค้นหาจาก key และ ภาษา
  if ($search != '') {
    $q = array();
    $q[] = "`key` LIKE '%$search%'";
    foreach ($install_languages AS $k) {
      $q[] = "`$k` LIKE '%$search%'";
    }
    $where .= ' AND ('.implode(' OR ', $q).')';
    $search = stripslashes($search);
    $url_query['search'] = urlencode($search);
  }
  // จำนวนข้อความทั้งหมด
  $sql = "SELECT COUNT(*) AS `count` FROM `".DB_LANGUAGE."` $where";
  $count = $db->customQuery($sql);
  // รายการต่อหน้า
  $list_per_page = (int)(isset($_GET['count']) ? $_GET['count'] : $_COOKIE['language_listperpage']);
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
  // save ฟิลเตอร์ลง cookie
  setCookie('language_order', $order, time() + 3600 * 24 * 365);
  setCookie('language_listperpage', $list_per_page, time() + 3600 * 24 * 365);
  // title
  $title = $lng['LNG_LANGUAGE_TITLE'];
  $a = array();
  $a[] = '<span class=icon-tools>{LNG_TOOLS}</span>';
  $a[] = '<a href="{URLQUERY?module=language}">{LNG_LANGUAGE}</a>';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-language>'.$title.'</h1></header>';
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
  // ไฟล์
  $content[] = '<fieldset>';
  $content[] = '<label>{LNG_FILE} <select name=js>';
  foreach (array('php', 'js') AS $k => $v) {
    $sel = $k == $js ? ' selected' : '';
    $content[] = '<option value='.$k.$sel.'>'.$v.'</option>';
  }
  $content[] = '</select></label>';
  $content[] = '</fieldset>';
  // submit
  $content[] = '<fieldset>';
  $content[] = '<input type=submit class="button go" value="{LNG_GO}">';
  $content[] = '<input name=module type=hidden value=language>';
  $content[] = '</fieldset>';
  // search
  $content[] = '<fieldset class=search>';
  $content[] = '<label accesskey=f><input type=text name=search value="'.$search.'" placeholder="{LNG_SEARCH_TITLE}" title="{LNG_SEARCH_TITLE}"></label>';
  $content[] = '<input type=submit value="&#xE607;" title="{LNG_SEARCH}">';
  $content[] = '</fieldset>';
  $content[] = '</form>';
  // ตารางข้อมูล
  $content[] = '<table id=language class="tbl_list fullwidth">';
  $content[] = '<caption>'.preg_replace($patt2, $replace2, $search != '' ? $lng['SEARCH_RESULT'] : $lng['ALL_ITEMS']).'</caption>';
  $content[] = '<thead>';
  $content[] = '<tr>';
  $content[] = '<th id=c0 scope=col>{LNG_LANGUAGE_VARIABLE}</th>';
  foreach ($install_languages AS $k) {
    $content[] = '<th id=c'.$k.' scope=col class="center '.($k == LANGUAGE ? 'mobile' : 'tablet').'">'.$k.'</th>';
  }
  $content[] = '<th id=c1 scope=col class="center tablet">{LNG_MODULE}</th>';
  $content[] = '<th id=c2 scope=col class="center tablet">{LNG_TYPE}</th>';
  $content[] = '<th id=c3 scope=col colspan=2>&nbsp;</th>';
  $content[] = '</tr>';
  $content[] = '</thead>';
  $content[] = '<tbody>';
  // query
  if ($order == 2) {
    $sql1 = "SELECT COUNT(*) FROM `".DB_LANGUAGE."` WHERE `key`=D.`key` AND `js`=D.`js`";
    $sql = "SELECT *,($sql1) AS `c` FROM `".DB_LANGUAGE."` AS D $where";
  } elseif ($order > 3) {
    $sql1 = "SELECT COUNT(*) FROM `".DB_LANGUAGE."` WHERE `".$orders[$order][0]."`=D.`".$orders[$order][0]."` AND `js`=D.`js`";
    $sql = "SELECT *,($sql1) AS `c` FROM `".DB_LANGUAGE."` AS D $where";
  } else {
    $sql = "SELECT * FROM `".DB_LANGUAGE."` $where";
  }
  $sql .= " ORDER BY ".$orders[$order][1];
  $sql .= " LIMIT $start, $list_per_page";
  foreach ($db->customQuery($sql) AS $item) {
    $id = $item['id'];
    $tr = '<tr id=L_'.$id.'>';
    $tr .= '<th headers=c0 id=r'.$id.' scope=row class=topic>'.$item['key'].'</th>';
    foreach ($install_languages AS $k) {
      if ($item['type'] == 'array') {
        $datas = unserialize($item[$k]);
        $t = implode(', ', $datas);
      } else {
        $t = strip_tags(str_replace(array("\r", "\n"), array('', ' '), $item[$k]));
      }
      $tr .= '<td headers="r'.$id.' c'.$k.'" class="'.($k == LANGUAGE ? 'mobile' : 'tablet').'" title="'.gcms::detail2TXT($t).'">'.gcms::detail2TXT(gcms::cutstring($t, 50)).'</td>';
    }
    $tr .= '<td headers="r'.$id.' c1" class="center tablet">'.$item['owner'].'</td>';
    $tr .= '<td headers="r'.$id.' c2" class="center tablet">'.$item['type'].'</td>';
    $tr .= '<td headers="r'.$id.' c3" class=menu><a class=icon-edit href="{URLQUERY?src=language&module=languageedit&id='.$item['id'].'&spage='.$page.'}" title="{LNG_EDIT}"></a></td>';
    $tr .= '<td headers="r'.$id.' c3" class=menu><a class=icon-delete id=delete_language_'.$id.' title="{LNG_DELETE}"></a></td>';
    $tr .= '</tr>';
    $content[] = $tr;
  }
  $content[] = '</tbody>';
  // แบ่งหน้า
  $maxlink = 9;
  $url = '<a href="{URLQUERY?module=language&page=%d}" title="{LNG_DISPLAY_PAGE} %d">%d</a>';
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
  $content[] = '</table>';
  $content[] = '<p class=splitpage>'.$splitpage.'</p>';
  $content[] = '<div class=table_nav>';
  // เพิ่มข้อความ
  $content[] = '<fieldset>';
  $content[] = '<a class="button large add" id=btn_addb href="index.php?module=languageedit&amp;src=language"><span class=icon-add>{LNG_ADD_NEW} {LNG_LANGUAGE_VARIABLE}</span></a>';
  $content[] = '</fieldset>';
  $content[] = '</div>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = "inintTR('language', /L_[0-9]+/);";
  $content[] = "inintLanguage('language');";
  $content[] = '});';
  $content[] = '</script>';
  // หน้าปัจจุบัน
  $url_query['module'] = 'language';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
