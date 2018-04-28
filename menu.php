<?php
// menu.php
header("content-type: text/xml; charset=UTF-8");
// inint
include 'bin/inint.php';
// วันที่วันนี้
$cdate = date("D, d M Y H:i:s +0700", $mmktime);
// rss
echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
echo '<channel>';
echo '<atom:link href="'.WEB_URL.'/menu.rss" rel="self" type="application/rss+xml"/>';
echo '<title>'.$lng['LNG_MENU'].'</title>';
echo '<link>'.WEB_URL.'</link>';
echo '<description>'.$modules['description'].'</description>';
echo "<pubDate>$cdate</pubDate>";
echo "<lastBuildDate>$cdate</lastBuildDate>";
// โหลดโมดูลที่ติดตั้ง เรียงตามลำดับเมนู
$sql = "SELECT M.`module`,U.`menu_text`";
$sql .= ",(SELECT `description` FROM `".DB_INDEX_DETAIL."` AS D WHERE D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language`=I.`language`) AS `description`";
$sql .= " FROM `".DB_MENUS."` AS U";
$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`index`='1' AND I.`id`=U.`index_id` AND I.`language`IN('".LANGUAGE."','')";
$sql .= " LEFT JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
$sql .= " WHERE REPLACE(U.`menu_text`,'&nbsp;','')!=''";
$sql .= " ORDER BY U.`menu_order` ASC";
$menus = $cache->get($sql);
if (!$menus) {
  $menus = $db->customQuery($sql);
  $cache->save($sql, $menus);
}
foreach ($menus AS $item) {
  $url = gcms::getURL($item['module']);
  echo '<item>';
  echo '<title>'.$item['menu_text'].'</title>';
  echo '<link>'.$url.'</link>';
  echo '<description>'.$item['description'].'</description>';
  echo "<pubDate>$cdate</pubDate>";
  echo '<guid>'.$url.'</guid>';
  echo '</item>';
}
echo '</channel>';
echo '</rss>';
