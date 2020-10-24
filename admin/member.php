<?php
// admin/member.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // กำหนดเมนูเรียงลำดับ
  $orders = array();
  $orders[] = array('{LNG_SORT_ID_DESC}', 'U.`id` DESC');
  $orders[] = array('{LNG_SORT_ID_ASC}', 'U.`id` ASC');
  $orders[] = array('{LNG_MEMBER_NOT_CONFIRM}', '`activate` DESC,U.`id` DESC');
  $orders[] = array('{LNG_DISPLAYNAME} {LNG_SORT_ASC}', 'U.`displayname` ASC');
  $orders[] = array('{LNG_DISPLAYNAME} {LNG_SORT_DESC}', 'U.`displayname` DESC');
  $orders[] = array('{LNG_FNAME} {LNG_LNAME} {LNG_SORT_ASC}', 'U.`fname` ASC,U.`lname` ASC');
  $orders[] = array('{LNG_FNAME} {LNG_LNAME} {LNG_SORT_DESC}', 'U.`fname` DESC,U.`lname` DESC');
  $orders[] = array('{LNG_EMAIL} {LNG_SORT_ASC}', 'U.`email` ASC');
  $orders[] = array('{LNG_EMAIL} {LNG_SORT_DESC}', 'U.`email` DESC');
  $orders[] = array('{LNG_MEMBER_BAN}', 'U.`ban_date` DESC,U.`ban_count` ASC,U.`id` DESC');
  $orders[] = array('{LNG_ADMIN_ACCESS}', 'U.`admin_access` DESC,U.`status` DESC,U.`email`');
  // รายการเรียงลำดับที่เลือก
  $order = (int)(isset($_GET['order']) ? $_GET['order'] : $_COOKIE['member_order']);
  $order = min(sizeof($orders), max(0, $order));
  // เมนูเรียงลำดับ
  $orderoptions = array();
  foreach ($orders AS $i => $item) {
    $sel = $i == $order ? ' selected' : '';
    $orderoptions[] = '<option value='.$i.$sel.'>'.$item[0].'</option>';
  }
  // ข้อความค้นหา
  $search = $db->sql_trim_str($_GET['search']);
  // ค้นหาสมาชิกจาก ชื่อ และ email
  if ($search != '') {
    $where = "U.`fname` LIKE '%$search%' OR U.`lname` LIKE '%$search%' OR U.`displayname` LIKE '%$search%' OR U.`email` LIKE '%$search%'";
    $url_query['search'] = urlencode($search);
  } else {
    $where = '';
  }
  // สถานะของสมาชิกที่ต้องการ
  $bystatus = (int)(isset($_GET['bystatus']) ? $_GET['bystatus'] : (isset($_COOKIE['member_bystatus']) ? $_COOKIE['member_bystatus'] : -1));
  // ค้นหาสมาชิกจากสถานะ
  if ($bystatus > -1) {
    $where = $where != '' ? "U.`status`='$bystatus' AND ($where)" : "U.`status`='$bystatus'";
    $url_query['bystatus'] = $bystatus;
  }
  $where = $where == '' ? '' : "WHERE $where";
  // จำนวนสมาชิกทั้งหมด
  $sql = "SELECT COUNT(*) AS `count` FROM `".DB_USER."` AS U $where";
  $count = $db->customQuery($sql);
  // รายการต่อหน้า
  $list_per_page = (int)(isset($_GET['count']) ? $_GET['count'] : $_COOKIE['member_list_per_page']);
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
  setCookie('member_order', $order, time() + 3600 * 24 * 365);
  setCookie('member_bystatus', $bystatus, time() + 3600 * 24 * 365);
  setCookie('member_list_per_page', $list_per_page, time() + 3600 * 24 * 365);
  // title
  $title = $lng['LNG_USERS_REGISTERED'];
  $a = array();
  $a[] = '<span class=icon-users>{LNG_USERS}</span>';
  $a[] = '{LNG_USERS_TITLE}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-users>'.$title.'</h1></header>';
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
  // สถานะสมาชิก
  $content[] = '<fieldset>';
  $content[] = '<label>{LNG_MEMBER_STATUS} <select name=bystatus>';
  $content[] = '<option value=-1>{LNG_MEMBER_ALL_STATUS}</option>';
  foreach ($config['member_status'] AS $i => $item) {
    $sel = $i == $bystatus ? 'selected' : '';
    $content[] = '<option value='.$i.' '.$sel.'>'.$item.'</option>';
  }
  $content[] = '</select></label>';
  $content[] = '</fieldset>';
  // submit
  $content[] = '<fieldset>';
  $content[] = '<input type=submit class="button go" value="{LNG_GO}">';
  $content[] = '<input type=hidden name=module value=member>';
  $content[] = '</fieldset>';
  // search
  $content[] = '<fieldset class=search>';
  $content[] = '<label accesskey=f><input type=text name=search value="'.$search.'" placeholder="{LNG_SEARCH_TITLE}" title="{LNG_SEARCH_TITLE}"></label>';
  $content[] = '<input type=submit value="&#xE607;" title="{LNG_SEARCH}">';
  $content[] = '</fieldset>';
  $content[] = '</form>';
  // ตารางข้อมูล
  $content[] = '<table id=member class="tbl_list fullwidth">';
  $content[] = '<caption>'.preg_replace($patt2, $replace2, $search != '' ? $lng['SEARCH_RESULT'] : $lng['ALL_MEMBER']).'</caption>';
  $content[] = '<thead>';
  $content[] = '<tr>';
  $content[] = '<th id=c0 scope=col>{LNG_EMAIL}</th>';
  $content[] = '<th id=c1 class=menu>&nbsp;</th>';
  $content[] = '<th id=c2 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
  $content[] = '<th id=c3 scope=col>{LNG_PHONE}</th>';
  $content[] = '<th id=c4 scope=col class=mobile>{LNG_FNAME} {LNG_LNAME}</th>';
  $content[] = '<th id=c5 scope=col class="center tablet">{LNG_SEX}</th>';
  $content[] = '<th id=c6 scope=col class=tablet>{LNG_WEBSITE}</th>';
  $content[] = '<th id=c7 scope=col class="center tablet">{LNG_CREATED}</th>';
  $content[] = '<th id=c8 scope=col class="center tablet">{LNG_LAST_VISITED} ({LNG_COUNT})</th>';
  $content[] = '<th id=c9>&nbsp;</th>';
  $content[] = '</tr>';
  $content[] = '</thead>';
  $content[] = '<tbody>';
  // เรียกสมาชิกทั้งหมด
  $sql = "SELECT U.*,(CASE WHEN U.`activatecode`='' THEN 0 ELSE 1 END) AS `activate`";
  $sql .= " FROM `".DB_USER."` AS U";
  $sql .= " $where ORDER BY ".$orders[$order][1];
  $sql .= " LIMIT $start, $list_per_page";
  foreach ($db->customQuery($sql) AS $item) {
    $id = $item['id'];
    $tr = '<tr id=user_'.$id.'>';
    $t = $item['ban_date'] > 0 ? " class=ban title=".sprintf($lng['LNG_BAN_DETAIL'], $item['ban_count'], gcms::mktime2date($item['ban_date'], 'd M Y')) : '';
    $c = array();
    $c[] = "status$item[status]";
    $c[] = $item['fb'] == 1 ? ' facebook' : '';
    $tr .= '<th headers=c0 id=r'.$id.' scope=row'.$t.'><a class="'.implode(' ', $c).'" href="{URLQUERY?module=sendmail&src=member&spage='.$page.'&to='.$id.'}" title="{LNG_EMAIL_SEND} {LNG_TO} '.$item['email'].'">'.$item['email'].'</a></th>';
    $tr .= '<td headers="r'.$id.' c1" ><span class="icon-valid '.($item['admin_access'] == 1 ? 'access' : 'disabled').'" title="{LNG_ADMIN_ACCESS}"></span></td>';
    $tr .= '<td headers="r'.$id.'" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
    $tr .= '<td headers="r'.$id.' c3">'.$item['phone1'].'</td>';
    $u = array();
    gcms::checkempty($item['fname'], $u);
    gcms::checkempty($item['lname'], $u);
    if (sizeof($u) > 0) {
      if ($item['displayname'] != '') {
        $u[] = "($item[displayname])";
      }
    } elseif ($item['displayname'] != '') {
      $u[] = $item['displayname'];
    } else {
      $u[] = '{LNG_UNNAME}';
    }
    $u = implode(' ', $u);
    $tr .= '<td headers="r'.$id.' c4" class=mobile>'.$u.'</td>';
    $sex = in_array($item['sex'], array_keys($lng['SEX'])) ? $item['sex'] : 'u';
    $tr .= '<td headers="r'.$id.' c5" class="center tablet"><span class=icon-sex-'.$sex.'></span></td>';
    $website = $item['website'] == '' ? '&nbsp;' : '<a href="http://'.$item['website'].'" target=_blank>'.$item['website'].'</a>';
    $tr .= '<td headers="r'.$id.' c6" class="website tablet">'.$website.'</td>';
    $tr .= '<td headers="r'.$id.' c7" class="date tablet">'.gcms::mktime2date($item['create_date'], 'd M Y').'</td>';
    if (trim($item['activatecode']) == '') {
      $tr .= '<td headers="r'.$id.' c8" class="date tablet">'.gcms::mktime2date($item['lastvisited'], 'd M Y H:i').' <span class=visited>('.$item['visited'].')</span></td>';
    } else {
      $tr .= '<td headers="r'.$id.' c8" class="noactivate center tablet">{LNG_NOACTIVATE}</td>';
    }
    $tr .= '<td headers="r'.$id.' c9" class=menu><a href="{URLQUERY?id='.$id.'&module=editprofile&src=member&spage='.$page.'}" title="{LNG_MEMBER_EDIT_TITLE}" class=icon-edit>&nbsp;</a></td>';
    $tr .= '</tr>';
    $content[] = $tr;
  }
  $content[] = '</tbody>';
  $content[] = '<tfoot>';
  $content[] = '<tr>';
  $content[] = '<td headers=c0 colspan=2></td>';
  $content[] = '<td headers=c2 class=check-column><a class="checkall icon-uncheck"></a></td>';
  $content[] = '<td headers=c3 colspan=8></td>';
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
  $sel[] = '<option value=accept>{LNG_ACCEPT_ACTIVATE}</option>';
  $sel[] = '<option value=activate>{LNG_NEW_ACTIVATE}</option>';
  $sel[] = '<option value=sendpassword>{LNG_FORGOT_SUBMIT}</option>';
  $sel[] = '<option value=unban>{LNG_UNBAN_USER}</option>';
  $sel[] = '<option value=delete>{LNG_DELETE}</option>';
  $sel[] = '</select>';
  // คำสั่งที่ทำงานล่าสุด
  $action = $_GET['action'];
  $content[] = str_replace('value='.$action.'>', 'value='.$action.' selected>', implode('', $sel));
  $content[] = '<label accesskey=e for=sel_action id=member_action class="button ok"><span>{LNG_SELECT_ACTION}</span></label>';
  $content[] = '</fieldset>';
  // status
  $content[] = '<fieldset>';
  $content[] = '<select id=status>';
  if (is_array($config['member_status'])) {
    foreach ($config['member_status'] AS $i => $item) {
      $content[] = '<option value='.$i.'>'.$item.'</option>';
    }
  }
  $content[] = '</select>';
  $content[] = '<label accesskey=c for=status id=member_status class="button ok"><span>{LNG_CHANGE_STATUS}</span></label>';
  $content[] = '</fieldset>';
  // ban
  $content[] = '<fieldset>';
  $content[] = '<select id=ban>';
  foreach (array(3, 5, 7, 15, 30) AS $value) {
    $content[] = '<option value="'.$value.'">'.$value.' {LNG_DAYS}</option>';
  }
  $content[] = '</select>';
  $content[] = '<label accesskey=u for=ban id=member_ban class="button ok"><span>{LNG_BAN_USER}</span></label>';
  $content[] = '</fieldset>';
  $content[] = '</div>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = "inintCheck('member');";
  $content[] = "inintTR('member', /user_[0-9]+/);";
  $content[] = 'callAction("member_action", function(){return $E("sel_action").value}, "member", "'.WEB_URL.'/admin/memberaction.php");';
  $content[] = 'callAction("member_status", "status", "member", "'.WEB_URL.'/admin/memberaction.php");';
  $content[] = 'callAction("member_ban", "ban", "member", "'.WEB_URL.'/admin/memberaction.php");';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'member';
  $url_query['page'] = $page;
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
