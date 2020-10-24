<?php
// modules/product/admin_categorywrite_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, admin
if (gcms::isReferer() && gcms::canConfig($config['product_can_config'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    $save = array();
    $ret = array();
    $error = false;
    $input = false;
    $topic = array();
    $icon = array();
    foreach ($_POST['category_topic'] AS $k => $v) {
      $v = $db->sql_trim_str(gcms::oneLine($v));
      if ($v != '') {
        $topic[$k] = $v;
      }
    }
    // ค่าที่ส่งมา
    $id = (int)$_POST['write_id'];
    $category_id = (int)$_POST['category_id'];
    if ($id > 0) {
      // แก้ไข, ตรวจสอบหมวดที่เลือก
      $sql = "SELECT `id` FROM `".DB_CATEGORY."` WHERE `category_id`=$category_id AND `module_id`=C.`module_id` LIMIT 1";
      $sql = "SELECT C.`id`,C.`module_id`,($sql) AS `cid`";
      $sql .= " FROM `".DB_CATEGORY."` AS C";
      $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id` AND M.`owner`='product'";
      $sql .= " WHERE C.`id`=$id LIMIT 1";
    } else {
      // ตรวจสอบโมดูล (หมวดใหม่)
      $sql = "SELECT `id` FROM `".DB_CATEGORY."` WHERE `category_id`=$category_id AND `module_id`=M.`id` LIMIT 1";
      $sql = "SELECT M.`id` AS `module_id`,($sql) AS `cid`";
      $sql .= " FROM `".DB_MODULES."` AS M WHERE M.`owner`='product' LIMIT 1";
    }
    $index = $db->customQuery($sql);
    if (sizeof($index) == 1) {
      $index = $index[0];
      // ตรวจสอบค่าที่ส่งมา
      $ret['ret_category_topic_'.LANGUAGE] = '';
      $ret['ret_category_id'] = '';
      if ($category_id == 0) {
        $ret['ret_category_id'] = 'ID_EMPTY';
        $input = 'category_id';
        $error = 'ID_EMPTY';
      } elseif ($index['cid'] > 0 && $index['cid'] != $id) {
        $ret['ret_category_id'] = 'ID_EXISTS';
        $input = 'category_id';
        $error = 'ID_EXISTS';
      }
      // icon
      if (!$error) {
        foreach ($_FILES AS $key => $value) {
          if ($value['tmp_name'] != '') {
            $ret["ret_$key"] = '';
            // ภาษา
            $k = str_replace('category_icon_', '', $key);
            // ตรวจสอบไฟล์อัปโหลด
            $info = gcms::isValidImage(array('jpg', 'gif', 'png'), $value);
            if (!$info) {
              $ret["ret_$key"] = 'ICON_INVALID_TYPE';
              $input = $key;
              $error = 'ICON_INVALID_TYPE';
            } else {
              $icon[$k] = "cat-$k-$category_id.$info[ext]";
              // อัปโหลด
              if (!@move_uploaded_file($value['tmp_name'], DATA_PATH."product/$icon[$k]")) {
                $ret["ret_$key"] = 'DO_NOT_UPLOAD';
                $input = $key;
                $error = 'DO_NOT_UPLOAD';
              } else {
                $ret["icon_$k"] = rawurlencode(DATA_URL."product/$icon[$k]?$mmktime");
              }
            }
          }
        }
        if (sizeof($icon) > 0) {
          $save['icon'] = gcms::array2Ser($icon);
        }
      }
      if (!$error) {
        $save['category_id'] = $category_id;
        $save['topic'] = gcms::array2Ser($topic);
        $save['published'] = $_POST['category_published'] == 1 ? 1 : 0;
        // save
        if ($id == 0) {
          // เพิ่มหมวดใหม่
          $save['module_id'] = $index['module_id'];
          $db->add(DB_CATEGORY, $save);
          // คืนค่า
          $ret['error'] = 'ADD_COMPLETE';
        } else {
          // แก้ไข
          $db->edit(DB_CATEGORY, $index['id'], $save);
          // คืนค่า
          $ret['error'] = 'EDIT_SUCCESS';
        }
        $ret['location'] = 'back';
      } else {
        // error
        $ret['error'] = $error;
        if ($input) {
          $ret['input'] = $input;
        }
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
