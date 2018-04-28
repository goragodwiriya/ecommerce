<?php
// feed.php
header("content-type: text/xml; charset=UTF-8");
// inint
include 'bin/inint.php';
// โมดูลที่ต้องการ
$module = $_REQUEST['module'];
if (preg_match('/^[a-z]+$/', $module)) {
  // จำนวนที่ต้องการ ถ้าไม่กำหนด คืนค่า 10 รายการ
  $count = (int)$_GET['rows'] * (int)$_GET['cols'];
  $count = $count == 0 ? (int)$_GET['count'] : $count;
  $count = $count <= 0 ? 10 : $count;
  // วันที่วันนี้
  $cdate = date("D, d M Y H:i:s +0700", $mmktime);
  $today = date('Y-m-d', $mmktime);
  // ตรวจสอบโมดูลที่เรียก
  $sql = "SELECT M.`id`,M.`module`,M.`owner`,D.`topic`,D.`description`,M.`config`";
  $sql .= " FROM `".DB_INDEX."` AS I";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id` AND M.`module`='$module'";
  $sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
  $sql .= " WHERE I.`module_id`=M.`id` AND I.`index`='1' AND I.`language` IN ('".LANGUAGE."','')";
  $sql .= " AND I.`published`='1' AND I.`published_date`<='$today'";
  $sql .= " LIMIT 1";
  $modules = $db->customQuery($sql);
  if (sizeof($modules) == 1) {
    $modules = $modules[0];
    echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
    echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
    echo '<channel>';
    echo '<atom:link href="'.WEB_URL.'/'.$module.'.rss" rel="self" type="application/rss+xml" />';
    echo "<title>".$modules['topic']."</title>";
    echo '<link>'.gcms::getURL($modules['module']).'</link>';
    echo '<description><![CDATA['.$modules['description'].']]></description>';
    echo "<pubDate>$cdate</pubDate>";
    echo "<lastBuildDate>$cdate</lastBuildDate>";
    if (is_file(ROOT_PATH."modules/$modules[owner]/feed.php")) {
      include (ROOT_PATH."modules/$modules[owner]/feed.php");
    }
    echo '</channel>';
    echo '</rss>';
  }
}
