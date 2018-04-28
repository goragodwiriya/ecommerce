<?php
// widgets/productrecommend/index.php
$widget = '';
if (defined('MAIN_INIT') && preg_match('/([a-z]+)_([0-9]+)/', $module, $match)) {
  $count = max(1, intval($match[2]));
  $widget = array();
  $widget[] = '<div id=productrand>';
  $list = array();
  // default query
  $sql .= "SELECT P.`id`,D.`topic`,D.`description`,G.`thumbnail`,M.`module`";
  $sql .= " FROM `".DB_PRODUCT."` AS P";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=P.`module_id`";
  $sql .= " INNER JOIN `".DB_PRODUCT_DETAIL."` AS D ON D.`id`=P.`id` AND D.`language` IN ('".LANGUAGE."','')";
  $sql .= " LEFT JOIN `".DB_PRODUCT_IMAGE."` AS G ON G.`product_id`=P.`id`";
  $sql .= " LEFT JOIN `".DB_PRODUCT_ADDITIONAL."` AS A ON A.`product_id`=P.`id`";
  $sql .= " WHERE M.`owner`='product' AND P.`recommend`=1 AND P.`published`=1 AND A.`stock`!=0";
  $sql .= " GROUP BY P.`id` ORDER BY RAND() LIMIT $count";
  foreach ($db->customQuery($sql) AS $item) {
    $list[] = $item;
  }
  if (!empty($list)) {
    foreach ($list AS $item) {
      if ($item[thumbnail] == '' || !is_file(DATA_PATH."product/$item[thumbnail]")) {
        $thumb = WEB_URL.'/'.SKIN."$config[skin]/product/img/nopicture.png";
      } else {
        $thumb = DATA_URL."product/$item[thumbnail]";
      }
      $row = '<a href="'.gcms::getUrl($item['module'], $item['topic'], 0, 0, "id=$item[id]").'" style="background-image:url('.$thumb.');';
      $row .= 'width:'.$config['product_thumb_width'].'px;';
      $row .= 'height:'.$config['product_thumb_height'].'px" title="'.$item['topic'].'">&nbsp;</a>';
      $widget[] = $row;
    }
  }
  $widget[] = '</div>';
  $widget = implode("\n", $widget);
}
