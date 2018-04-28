<?php
// modules/product/admin_category_action.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, member
if (gcms::isReferer() && gcms::canConfig($config['product_can_config'])) {
  // ค่าที่ส่งมา
  if (preg_match('/intro_([0-9]+)/', $_POST['module'], $match)) {
    // intro
    $action = 'intro';
    $id = (int)$match[1];
    $value = $_POST['value'];
  } elseif (preg_match('/^categoryid_([0-9]+)_([0-9]+)$/', $_POST['module'], $match)) {
    $action = 'categoryid';
    $id = (int)$match[2];
    $value = Max(1, (int)$_POST['value']);
  } else {
    $action = $_POST['action'];
    $value = (int)$_POST['value'];
    if (isset($_POST['id'])) {
      foreach (explode(',', $_POST['id']) AS $id) {
        $ids[] = (int)$id;
      }
      $id = implode(',', $ids);
    }
  }
  // ตรวจสอบ module
  $sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 1) {
    $index = $index[0];
    if ($action == 'delete') {
      // ลบหมวดหมู่ ไม่ลบเนื้อหาปรับเนื้อหาไปอยู่หมวด 0 หากไม่มีภาษาอื่น
      // ตรวจสอบรายการที่เลือก และลบ icon ของหมวด
      $ids = array();
      $sql = "SELECT `id`,`icon` FROM `".DB_CATEGORY."`";
      $sql .= " WHERE `module_id`='$index[id]' AND `id` IN ($id)";
      foreach ($db->customQuery($sql) AS $item) {
        // ลบไอคอนของหมวด
        @unlink(DATA_PATH."image/$item[icon]");
        // รายการที่ลบ category.id
        $ids[] = $item['id'];
      }
      if (sizeof($ids) > 0) {
        // ลบรายการหมวดที่เลือก (category.id)
        $sql = "DELETE FROM `".DB_CATEGORY."`";
        $sql .= " WHERE `module_id`='$index[id]' AND `id` IN (".implode(',', $ids).")";
        $db->query($sql);
      }
    } elseif ($action == 'categoryid') {
      $categories = array();
      $sql1 .= "SELECT `category_id` FROM `".DB_CATEGORY."`";
      $sql1 .= " WHERE `module_id`='$index[id]' AND `id`='$id'";
      $sql2 = "SELECT `id` FROM `".DB_CATEGORY."`";
      $sql2 .= " WHERE `module_id`='$index[id]' AND `category_id`='$value' LIMIT 1";
      $sql = "SELECT `id`,`category_id`,($sql2) AS `id2` FROM `".DB_CATEGORY."`";
      $sql .= " WHERE `module_id`='$index[id]' AND`category_id` IN ($sql1)";
      foreach ($db->customQuery($sql) AS $item) {
        if ((int)$item['id2'] == 0) {
          $categories[] = $item['id'];
          $ret['categoryid_'.$index['id'].'_'.$item['id']] = $value;
        } else {
          $ret['categoryid_'.$index['id'].'_'.$item['id']] = $item['category_id'];
        }
      }
      if (sizeof($categories) > 0) {
        $sql = "UPDATE `".DB_CATEGORY."` SET `category_id`='$value'";
        $sql .= " WHERE `module_id`='$index[id]' AND `id` IN (".implode(',', $categories).")";
        $db->query($sql);
      }
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
