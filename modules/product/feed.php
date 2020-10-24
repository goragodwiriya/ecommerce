<?php
// modules/product/feed.php
if (is_array($modules)) {
  $lang = isset($_GET['lng']) ? $_GET['lng'] : LANGUAGE;
  $t = $_GET['t'];
  $sqls = array();
  $sqls[] = "S.`module_id`='$modules[id]'";
  $sqls[] = "S.`published`='1'";
  if (isset($_GET['cat'])) {
    $sqls[] = 'S.`category_id`='.(int)$_GET['cat'];
  }
  if ($t == 'new') {
    $sqls[] = "S.`new`='Y'";
  } elseif ($t == 'hot') {
    $sqls[] = "S.`hot`='Y'";
  } elseif ($t == 'recommend') {
    $sqls[] = "S.`recommend`='Y'";
  } else {
    $t = '';
  }
  $sqls[] = "S.`language` IN ('".$lang."','')";
  $sql = "SELECT S.`id`,S.`last_update`,S.`topic`,S.`description`,P.`thumb`,P.`thumbW`,P.`thumbH` FROM `".DB_PRODUCT."` AS S";
  $sql .= " INNER JOIN `".DB_PRODUCT_IMAGE."` AS P ON P.`index_id`=S.`id` AND P.`module_id`='$modules[id]'";
  $sql .= " WHERE ".implode(' AND ', $sqls);
  $sql .= " ORDER BY ".($t == '' ? 'RAND()' : 'S.`last_update` DESC')." LIMIT $count";
  // ตรวจสอบข้อมูลจาก cache
  $datas = $cache->get($sql);
  if (!$datas) {
    $datas = $db->customQuery($sql);
    $cache->save($sql, $datas);
  }
  foreach ($datas as $item) {
    if ($config['module_url'] == '1') {
      $link = gcms::getURL($modules['module'], $item['topic']);
    } else {
      $link = gcms::getURL($modules['module'], '', 0, $item['id']);
    }
    echo '<item>';
    echo "<title>$item[topic]</title>";
    echo "<link>$link</link>";
    echo '<description><![CDATA['.$item['description'].']]></description>';
    if ($item['thumbnail'] != '' && is_file(DATA_PATH."image/$item[thumbnail]")) {
      echo '<enclosure url="'.DATA_URL."image/$item[thumb]\" type=\"image/jpeg\"></enclosure>";
    }
    echo '<guid isPermaLink="true">'.$link.'</guid>';
    echo '<pubDate>'.date("D, d M Y H:i:s +0700", $item['last_update']).'</pubDate>';
    echo '</item>';
  }
}
