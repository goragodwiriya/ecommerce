<?php
// modules/product/admin_category_copy.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, member
if (gcms::isReferer() && gcms::canConfig($config['product_can_config'])) {
  // ค่าที่ส่งมา
  $cat = (int)$_POST['cat'];
  $language = $db->sql_trim_str($_POST['lng']);
  if ($cat > 0 && $language != '') {
    // ตรวจสอบหมวดที่เลือก
    $sql = "SELECT C.*,C2.`id` AS `cid`";
    $sql .= ",(SELECT MAX(`id`) FROM `".DB_CATEGORY."`) AS `last_id`";
    $sql .= " FROM `".DB_CATEGORY."` AS C";
    $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id` AND M.`owner`='product'";
    $sql .= " LEFT JOIN `".DB_CATEGORY."` AS C2 ON C2.`module_id`=C.`module_id` AND C2.`language`='$language' AND C2.`category_id`=C.`category_id`";
    $sql .= " WHERE C.`id`='$cat'";
    $sql .= " LIMIT 1";
    $category = $db->customQuery($sql);
    if (sizeof($category) == 1) {
      $category = $category[0];
      if ($category['language'] == '') {
        $ret['error'] = 'LANGUAGE_EMPTY';
        $ret['ret_btn_copy'] = 'LANGUAGE_EMPTY';
        $ret['input'] = 'category_language';
      } else {
        if (intval($category['cid']) > 0) {
          $ret['error'] = 'LANGUAGE_COPY_EXISTS';
          $ret['ret_btn_copy'] = 'LANGUAGE_COPY_EXISTS';
          $ret['input'] = 'category_language';
        } else {
          // ภาษาที่ต้องการ copy
          $category['language'] = $language;
          $ret['copy_id'] = $category['id'];
          // id ของหมวดใหม่
          $category['id'] = $category['last_id'] + 1;
          // ไอคอนเดิม
          $icon = $category['icon'];
          // เพิ่มข้อมูลใหม่
          unset($category['last_id']);
          unset($category['cid']);
          $category['topic'] = addslashes($category['topic']);
          $db->add(DB_CATEGORY, $category);
          // คืนค่า
          $ret['write_id'] = $category['id'];
          $ret['error'] = 'LANGUAGE_COPY_SUCCESS';
          $ret['ret_btn_copy'] = '';
        }
      }
    } else {
      $ret['error'] = 'ID_NOT_FOUND';
    }
  } else {
    $ret['error'] = 'ACTION_ERROR';
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
