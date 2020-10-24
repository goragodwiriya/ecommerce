<?php
// modules/product/news.php
if (isset($index)) {
  $news = array();
  // query
  $replace = array();
  $sql = "SELECT I.`id`,I.`topic`,I.`last_update`,I.`description`";
  $sql .= " FROM `".DB_PRODUCT_DETAIL."` AS I";
  $sql .= " WHERE I.`module_id`='$index[module_id]' AND I.`published`='1' ORDER BY `";
  $sql .= $index['news_sort'] == 0 ? 'last_update' : 'create_date';
  $sql .= "` DESC LIMIT $index[news_count]";
  // template
  $skin = gcms::loadtemplate($index['module'], 'product', 'newsitem');
  $patt = array('/{BG}/', '/{URL}/', '/{TOPIC-([0-9]+)}/e', '/{DETAIL-([0-9]+)}/e', '/{DATE}/', '/{UID}/', '/{SENDER}/', '/{STATUS}/', '/{ICON}/');
  foreach ($db->customQuery($sql) AS $item) {
    $bg = $bg == 'bg1' ? 'bg2' : 'bg1';
    $d = $index['news_sort'] == 0 ? $item['last_update'] : $item['create_date'];
    $replace = array();
    $replace[] = $bg;
    $replace[] = gcms::getURL($index['module'], $item['topic'], 0, ($config['module_url'] == 1 ? 0 : $item['id']));
    $replace[] = create_function('$matches', 'return gcms::cutstring("'.$item['topic'].'",(int)$matches[2]);');
    $replace[] = create_function('$matches', 'return gcms::cutstring("'.$item['description'].'",(int)$matches[2]);');
    $replace[] = gcms::mktime2date($d, 'd M Y');
    $replace[] = $item['member_id'];
    $replace[] = $item['displayname'];
    $replace[] = $item['status'];
    $replace[] = ($index['new_date'] > 0 && $d > $valid_date) ? 'new' : '';
    $news[] = gcms::pregReplace($patt, $replace, $skin);
  }
  // เขียนเป็นไฟล์
  $f = @fopen(DATA_PATH."product/$index[module].xml", 'wb');
  if ($f) {
    fwrite($f, implode("\n", gcms::pregReplace('/{(LNG_[A-Z0-9_]+)}/e', 'gcms::getLng', $news)));
    fclose($f);
  }
}
