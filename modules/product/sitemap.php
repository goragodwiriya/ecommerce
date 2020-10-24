<?php
// modules/product/sitemap.php
if (is_array($owners['product'])) {
  // หมวด
  $sql = "SELECT `category_id`,`module_id` FROM `".DB_CATEGORY."`";
  // ตรวจสอบข้อมูลจาก cache
  $datas = $cache->get($sql);
  if (!$datas) {
    $datas = $db->customQuery($sql);
    $cache->save($sql, $datas);
  }
  foreach ($datas AS $item) {
    $link = gcms::getURL($modules[$item['module_id']], '', $item[category_id]);
    echo sitemap($link, $cdate);
  }
  // สินค้า
  $sql = "SELECT P.`id`,P.`module_id`,P.`last_update`,P.`alias`";
  $sql .= " FROM `".DB_PRODUCT."` AS P";
  $sql .= " WHERE P.`published`='1'";
  // ตรวจสอบข้อมูลจาก cache
  $datas = $cache->get($sql);
  if (!$datas) {
    $datas = $db->customQuery($sql);
    $cache->save($sql, $datas);
  }
  foreach ($datas AS $item) {
    if ($config['module_url'] == '1') {
      $link = gcms::getURL($modules[$item['module_id']], $item['alias']);
    } else {
      $link = gcms::getURL($modules[$item['module_id']], '', 0, $item['id']);
    }
    echo sitemap($link, date("Y-m-d", $item['last_update']));
  }
}
