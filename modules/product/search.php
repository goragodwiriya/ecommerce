<?php
// modules/product/search.php
if (defined('MAIN_INIT')) {
  $searchs = array();
  foreach ($words AS $item) {
    $searchs[] = "D.`topic` LIKE '%$item%' OR D.`detail` LIKE '%$item%'";
  }
  $sql = "SELECT P.`id`,P.`alias`,D.`topic`,D.`description`,D.`detail`,0 AS `index`,M.`module`,M.`owner`,9 AS `level`";
  $sql .= " FROM `".DB_PRODUCT."` AS P";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=P.`module_id`";
  $sql .= " INNER JOIN `".DB_PRODUCT_DETAIL."` AS D ON D.`id`=P.`id` AND D.`language` IN ('".LANGUAGE."','')";
  $sql .= " WHERE P.`published`='1' AND ".implode(' AND ', $searchs);
  $sql = "SELECT * FROM ($sql) AS QS GROUP BY `id`";
  $sqls[] = "($sql)";
}
