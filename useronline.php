<?php
// useronline.php
header("content-type: text/html; charset=UTF-8");
// inint
include ('bin/inint.php');
// referer
if (gcms::isReferer()) {
  $onlinechanged = false;
  // บอกว่ายังไม่มีคนเปลี่ยนแปลงไว้ก่อน
  $validtime = $mmktime - COUNTER_GAP;
  // เวลาที่บอกว่า user logout ไปแล้ว
  $online = 1;
  // ตัวเอง online อยู่
  $useronline = array();
  // แอเร์ยเก็บชื่อคน online
  $deleteid = array();
  // แอเรย์เก็บ id ที่ต้องการลบ
  $session_id = session_id();
  $updateid = 0;
  $onlinechanged = false;
  $userupdate = false;
  $login = $_SESSION['login'];
  // เพิ่มตัวเอง online
  $my['member_id'] = (int)$login['id'];
  $my['displayname'] = trim(gcms::cutstring($login['displayname'] == '' ? $login['email'] : $login['displayname'], 10));
  $my['icon'] = WEB_URL.'/modules/member/usericon.php?w=50&id='.$login['id'];
  $my['time'] = $mmktime;
  $my['session'] = $session_id;
  if ($my['member_id'] > 0) {
    $useronline[] = array('id' => $my['member_id'], 'icon' => $my['icon'], 'displayname' => $my['displayname']);
  }
  // ตรวจสอบคนที่หมดเวลา
  foreach ($db->customQuery('SELECT * FROM `'.DB_USERONLINE.'`') AS $item) {
    if ($item['time'] < $validtime) {
      // หมดเวลา
      $deleteid[] = $item['id'];
      $onlinechanged = true;
    } elseif ($item['session'] != $session_id) {
      // คนอื่น บอกว่า online ไว้
      $online++;
      if ($item['member_id'] > 0) {
        $useronline[] = array('id' => $item['member_id'], 'icon' => $item['icon'], 'displayname' => $item['displayname']);
      }
    } else {
      // ตัวเอง เก็บ id ไว้อัปเดต
      $updateid = $item['id'];
      if ($item['member_id'] != $my['member_id']) {
        // login หรือ logout
        $userupdate = true;
      }
    }
  }
  // ลบคนที่หมดเวลาออกจาก db
  if (count($deleteid) > 0) {
    $db->query('DELETE FROM `'.DB_USERONLINE.'` WHERE `id` IN('.implode(',', $deleteid).')');
  }
  // บันทึกตัวเองลงบน db
  if ($updateid > 0) {
    $db->edit(DB_USERONLINE, $updateid, $my);
  } else {
    $db->add(DB_USERONLINE, $my);
    $onlinechanged = true;
  }
  // วันนี้
  $counter_day = date('Y-m-d', $mmktime);
  $sql = "SELECT * FROM `".DB_COUNTER."` WHERE `date`='$counter_day' LIMIT 1";
  $my_counter = $db->customQuery($sql);
  $my_counter = $my_counter[0];
  $c = (int)$_POST['counter'];
  if ($onlinechanged || $userupdate || $c != $my_counter['time']) {
    $my_counter['time'] = ($userupdate || $c == 0) ? $mmktime : $my_counter['time'];
    $db->edit(DB_COUNTER, $my_counter['id'], $my_counter);
    $ret['all'] = $my_counter['counter'];
    $ret['today'] = $my_counter['visited'];
    $ret['online'] = $online;
    $ret['pagesview'] = $my_counter['pages_view'];
    $ret['count'] = $my_counter['time'];
    $ret['useronline'] = $useronline;
  }
  // include ไฟล์อื่นๆที่ต้องการประมวลผล
  if (is_array($config['useronline_include'])) {
    foreach ($config['useronline_include'] AS $item) {
      include ROOT_PATH.$item;
    }
  }
  // คืนค่า JSON
  echo json_encode($ret);
}
