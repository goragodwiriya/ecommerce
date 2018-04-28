<?php
// modules/product/admin_subcategory_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// ตรวจสอบ referer และ สมาชิก
if (gcms::isReferer() && gcms::canConfig($config['product_can_config'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // หมวดที่เลือก
    $sql = "SELECT C.`id`,C.`module_id`";
    $sql .= " FROM `".DB_CATEGORY."` AS C";
    $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id` AND M.`owner`='product'";
    $sql .= " WHERE C.`id`=".(int)$_POST['write_id']." LIMIT 1";
    $category = $db->customQuery($sql);
    if (sizeof($category) == 1) {
      $category = $category[0];
      $topic = array();
      $categories = array();
      foreach ($_POST AS $k => $items) {
        if ($k != 'write_id') {
          foreach ($items AS $i => $v) {
            $v = $db->sql_trim_str(gcms::oneLine($v));
            if ($v != '') {
              $topic[$i + 1][$k] = $v;
              $categories[$k][$i + 1] = $v;
            }
          }
        }
      }
      // save
      if (sizeof($topic) > 0) {
        $db->edit(DB_CATEGORY, $category['id'], array('subcategory' => gcms::array2Ser($topic)));
      } else {
        $db->edit(DB_CATEGORY, $category['id'], array('subcategory' => ''));
      }
      // คืนค่า
      $ret['location'] = 'close';
      $ret['error'] = 'SAVE_COMPLETE';
      foreach ($config['languages'] AS $l) {
        if (is_array($categories[$l])) {
          $ret["subcategory_$category[id]_$l"] = rawurlencode(implode(',', $categories[$l]));
        }
      }
      if (!isset($ret["subcategory_$category[id]_$l"])) {
        $ret["subcategory_$category[id]_$l"] = '';
      }
    } else {
      $ret['error'] = 'ACTION_ERROR';
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
