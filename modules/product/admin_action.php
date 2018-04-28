<?php
// modules/product/admin_action.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, can write
if (gcms::isReferer() && gcms::canConfig($config['product_can_write'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // ค่าที่ส่งมา
    $action = $_POST['action'];
    $module = $_POST['module'];
    if (isset($_POST['id'])) {
      foreach (explode(',', $_POST['id']) AS $id) {
        $ids[] = (int)$id;
      }
      $id = implode(',', $ids);
    }
    define('MAIN_INIT', 'admin_action');
    // ตรวจสอบ module
    $sql = "SELECT `id` AS `module_id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
    $index = $db->customQuery($sql);
    if (sizeof($index) == 1 && $id != '') {
      $index = $index[0];
      if ($action == 'delete' && $module == 'order') {
        // ตรวจสอบ order ที่ต้องคืน stock
        $sql = "SELECT `id`,`order_status` FROM `".DB_ORDERS."`";
        $sql .= " WHERE `id` IN ($id) AND `order_status`<='$config[product_cut_stock]' AND `module_id`='$index[module_id]'";
        // คืน stock สินค้า
        $order_status = 0;
        foreach ($db->customQuery($sql) AS $item) {
          // รายละเอียดสินค้าที่สั่งซื้อใน order ที่ลบ
          $sql = "SELECT C.`additional_id`,C.`product_id`,C.`module_id`,C.`quantity`,A.`stock`";
          $sql .= " FROM `".DB_CART."` AS C";
          $sql .= " INNER JOIN `".DB_PRODUCT_ADDITIONAL."` AS A ON A.`id`=C.`additional_id` AND A.`product_id`=C.`product_id`AND A.`module_id`=C.`module_id`";
          $sql .= " WHERE `order_id`='$item[id]'";
          $basket = $db->customQuery($sql);
          $last_status = $item['order_status'];
          include ROOT_PATH.'modules/product/checkstock.php';
        }
        // ลบคำสั่งซื้อ
        $db->query("DELETE FROM `".DB_CART."` WHERE `order_id` IN ($id) AND `module_id`='$index[module_id]'");
        $db->query("DELETE FROM `".DB_ORDERS."` WHERE `id` IN ($id) AND `module_id`='$index[module_id]'");
      } elseif ($action == 'delete' && $module == 'product') {
        // สินค้า
        $sql = "SELECT `thumbnail`,`image` FROM `".DB_PRODUCT_IMAGE."` WHERE `product_id` IN ($id)";
        foreach ($db->customQuery($sql) AS $item) {
          // ลบรูปภาพสินค้า
          @unlink(DATA_PATH."product/$item[image]");
          @unlink(DATA_PATH."product/$item[thumbnail]");
        }
        $db->query("DELETE FROM `".DB_PRODUCT_IMAGE."` WHERE `product_id` IN ($id)");
        // ลบรายละเอียดสินค้า
        $db->query("DELETE FROM `".DB_PRODUCT_DETAIL."` WHERE `id` IN ($id)");
        // ลบราคา
        $db->query("DELETE FROM `".DB_PRODUCT_ADDITIONAL."` WHERE `product_id` IN ($id)");
        // ลบสินค้า
        $db->query("DELETE FROM `".DB_PRODUCT."` WHERE `id` IN ($id) AND `module_id`='$index[module_id]'");
      } elseif ($action == 'delete' && $module == 'review') {
        // ลบ Review
        $sql = "DELETE FROM `".DB_COMMENT."`";
        $sql .= " WHERE `id` IN ($id) AND `module_id`='$index[module_id]'";
        $db->query($sql);
      } elseif ($action == 'published' && $module == 'review') {
        // สถานะการเผยแพร่ Review
        $sql = "UPDATE `".DB_COMMENT."` SET `published`='".($_POST['value'] == 1 ? 1 : 0);
        $sql .= "' WHERE `id` IN ($id) AND `module_id`='$index[module_id]'";
        $db->query($sql);
      } elseif ($action == 'published') {
        // สถานะการเผยแพร่
        $sql = "UPDATE `".DB_PRODUCT."` SET `published`='".($_POST['value'] == 1 ? 1 : 0);
        $sql .= "' WHERE `id` IN ($id) AND `module_id`='$index[module_id]'";
        $db->query($sql);
      }
    } else {
      $ret['error'] = 'ACTION_ERROR';
    }
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
