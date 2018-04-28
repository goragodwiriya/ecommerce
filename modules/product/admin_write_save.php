<?php
// modules/product/admin_write_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, can_write
if (gcms::isReferer() && gcms::canConfig($config['product_can_write'])) {
  if (isset($_SESSION['login']['account']) && $_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    $input = false;
    $error = false;
    $tab = false;
    // details
    $details = array();
    $alias_topic = '';
    foreach ($config['languages'] AS $value) {
      $topic = $db->sql_trim_str($_POST["write_topic_$value"]);
      $alias = gcms::aliasName($_POST["write_topic_$value"]);
      $keywords = gcms::getTags($_POST["write_keywords_$value"]);
      $description = trim($_POST["write_description_$value"]);
      if ($topic != '') {
        $save = array();
        $save['topic'] = $topic;
        $save['keywords'] = $db->sql_clean(gcms::cutstring(preg_replace('/[\'\"\r\n\s]{1,}/isu', ' ', ($keywords == '' ? gcms::getTags($_POST["write_topic_$value"]) : $keywords)), 149));
        $save['description'] = $db->sql_trim_str(gcms::cutstring(gcms::html2txt($description == '' ? $_POST["write_detail_$value"] : $description), 149));
        $save['detail'] = gcms::ckDetail($_POST["write_detail_$value"]);
        $save['language'] = $value;
        $details[$value] = $save;
        $alias_topic = $alias_topic == '' ? $alias : $alias_topic;
      }
    }
    // product
    $save = array();
    $save['alias'] = gcms::aliasName($_POST['write_alias']);
    $save['product_no'] = $db->sql_trim_str($_POST['write_no']);
    $save['new'] = empty($_POST['write_new']) ? 0 : 1;
    $save['hot'] = empty($_POST['write_hot']) ? 0 : 1;
    $save['recommend'] = empty($_POST['write_recommend']) ? 0 : 1;
    $save['published'] = empty($_POST['write_published']) ? 0 : 1;
    $save['can_reply'] = empty($_POST['write_can_reply']) ? 0 : 1;
    // id ที่แก้ไข
    $id = (int)$_POST['write_id'];
    if ($id > 0) {
      // สินค้าที่ต้องการแก้ไข
      $sql = "SELECT P.*,M.`module`,M.`config`";
      $sql .= " FROM `".DB_PRODUCT."` AS P";
      $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=P.`module_id` AND M.`owner`='product'";
      $sql .= " WHERE P.`id`=$id LIMIT 1";
    } else {
      // สินค้าใหม่ ตรวจสอบโมดูล
      $sql1 = " SELECT MAX(`id`) FROM `".DB_PRODUCT."` WHERE `module_id`=M.`id`";
      $sql = "SELECT M.`id` AS `module_id`,M.`module`,M.`config`,1+COALESCE(($sql1),0) AS `id`";
      $sql .= " FROM `".DB_MODULES."` AS M";
      $sql .= " WHERE M.`owner`='product' LIMIT 1";
    }
    $index = $db->customQuery($sql);
    if (sizeof($index) == 0) {
      $ret['error'] = 'ACTION_ERROR';
    } else {
      $index = $index[0];
      // save as default
      if ($index['config'] != '') {
        $saveasdefault = unserialize($index['config']);
      } else {
        $saveasdefault = array();
      }
      // ชื่อสินค้าและรายละเอียด
      if (sizeof($details) == 0) {
        $item = $config['languages'][0];
        $ret["ret_write_topic_$item"] = 'TOPIC_EMPTY';
        $error = !$error ? 'TOPIC_EMPTY' : $error;
        $input = !$input ? "write_topic_$item" : $input;
        $tab = !$tab ? "detail_$item" : $tab;
      } else {
        foreach ($details AS $item => $values) {
          if ($values['topic'] == '') {
            $ret["ret_write_topic_$item"] = 'TOPIC_EMPTY';
            $error = !$error ? 'TOPIC_EMPTY' : $error;
            $input = !$input ? "write_topic_$item" : $input;
            $tab = !$tab ? "detail_$item" : $tab;
          } else {
            $ret["ret_write_topic_$item"] = '';
          }
        }
      }
      // มีข้อมูลมาภาษาเดียวให้แสดงในทุกภาษา
      if (sizeof($details) == 1) {
        foreach ($details AS $i => $item) {
          $details[$i]['language'] = '';
        }
      }
      // alias
      if (empty($save['alias'])) {
        $save['alias'] = $alias_topic;
      }
      // ตรวจสอบ alias ซ้ำ
      $sql = "SELECT `id` FROM `".DB_PRODUCT."` WHERE `alias`='".addslashes($save['alias'])."' AND `module_id`='$index[module_id]' LIMIT 1";
      $search = $db->customQuery($sql);
      if (sizeof($search) > 0 && ($id == 0 || $id != $search[0]['id'])) {
        $ret['ret_write_alias'] = 'ALIAS_EXISTS';
        $error = !$error ? 'ALIAS_EXISTS' : $error;
        $input = !$input ? 'write_alias' : $input;
        $tab = !$tab ? "options" : $tab;
      } else {
        $ret['ret_write_alias'] = '';
      }
      // รหัสสินค้า
      if (mb_strlen($save['product_no']) < 2) {
        $ret['ret_write_no'] = 'PRODUCT_NO_SHORT';
        $input = !$input ? 'write_no' : $input;
        $error = !$error ? 'PRODUCT_NO_SHORT' : $error;
        $tab = !$tab ? "options" : $tab;
      } else {
        // ตรวจสอบรหัสสินค้าซ้ำ
        $sql = "SELECT `id` FROM `".DB_PRODUCT."` WHERE `product_no`='".addslashes($save['product_no'])."' AND `module_id`='$index[module_id]' LIMIT 1";
        $search = $db->customQuery($sql);
        if (sizeof($search) > 0 && ($id == 0 || $id != $search[0]['id'])) {
          $ret['ret_write_no'] = 'PRODUCT_NO_EXISTS';
          $input = !$input ? 'write_no' : $input;
          $error = !$error ? 'PRODUCT_NO_EXISTS' : $error;
          $tab = !$tab ? "options" : $tab;
        } else {
          $ret['ret_write_no'] = '';
        }
      }
      // หมวดหมู่
      $categories = array();
      if (isset($_POST['category'])) {
        foreach ($_POST['category'] AS $value) {
          $categorie = array();
          list($categorie['category_id'], $categorie['subcategory']) = explode(':', $value);
          $categories[] = $categorie;
        }
      }
      if (sizeof($categories) == 0) {
        $ret['ret_write_category'] = 'CATEGORY_EMPTY';
        $input = !$input ? 'write_category' : $input;
        $error = !$error ? 'CATEGORY_EMPTY' : $error;
        $tab = !$tab ? "options" : $tab;
      } else {
        $ret['ret_first_category'] = '';
      }
      // รายละเอียดของสินค้า
      $additional = array();
      foreach ($_POST['product_topic'] AS $i => $detail) {
        $datas = array();
        foreach ($lng['CURRENCY_UNITS'] AS $unit => $text) {
          $datas["price_$unit"] = (double)$_POST["product_price_$unit"][$i];
          $datas["net_$unit"] = (double)$_POST["product_net_$unit"][$i];
        }
        $datas['topic'] = $db->sql_trim_str($detail);
        $datas['weight'] = (int)$_POST['product_weight'][$i];
        $datas['stock'] = (int)$_POST['product_stock'][$i];
        if ($datas["net_$unit"] > 0 || $datas['topic'] != '') {
          $additional[] = $datas;
        }
      }
      if (!$error) {
        $save['last_update'] = $mmktime;
        if ($id == 0) {
          // สินค้าใหม่
          $save['module_id'] = $index['module_id'];
          $id = $db->add(DB_PRODUCT, $save);
          // ส่งค่ากลับ
          $ret['error'] = 'ADD_COMPLETE';
        } else {
          // แก้ไข
          $db->edit(DB_PRODUCT, $id, $save);
          // ส่งค่ากลับ
          $ret['error'] = 'EDIT_SUCCESS';
        }
        $ret['location'] = rawurlencode("index.php?module=product-write&qid=$id&tab=$_POST[write_tab]");
        // product category
        $db->query("DELETE FROM `".DB_PRODUCT_CATEGORY."` WHERE `id`='$id'");
        foreach ($categories AS $i => $item) {
          $item['id'] = $id;
          $db->add(DB_PRODUCT_CATEGORY, $item);
        }
        // product details
        $db->query("DELETE FROM `".DB_PRODUCT_DETAIL."` WHERE `id`=$id");
        foreach ($details AS $i => $item) {
          $item['id'] = $id;
          $db->add(DB_PRODUCT_DETAIL, $item);
        }
        // รายละเอียดของสินค้า
        $db->query("DELETE FROM `".DB_PRODUCT_ADDITIONAL."` WHERE `product_id`=$id");
        foreach ($additional AS $i => $item) {
          $item['id'] = $i;
          $item['module_id'] = $index['module_id'];
          $item['product_id'] = $id;
          $db->add(DB_PRODUCT_ADDITIONAL, $item);
        }
        if (!empty($_POST['saveasdefault'])) {
          if ($_POST['write_tab'] == 'options') {
            unset($save['alias']);
            unset($save['product_id']);
            unset($save['product_no']);
            unset($save['last_update']);
            $saveasdefault['options'] = $save;
            $saveasdefault['categories'] = $categories;
          }
          if ($_POST['write_tab'] == 'additional') {
            $saveasdefault['additional'] = $additional;
          }
          $db->edit(DB_MODULES, $index['module_id'], array('config' => serialize($saveasdefault)));
        }
      } else {
        $ret['error'] = $error;
        if ($input) {
          $ret['input'] = $input;
        }
        if ($tab) {
          $ret['tab'] = $tab;
        }
      }
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
