<?php
// modules/product/admin_select_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, admin
if (gcms::isReferer() && gcms::canConfig($config['product_can_config'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // ตรวจสอบโมดูลที่เรียก
    $sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
    $index = $db->customQuery($sql);
    if (sizeof($index) == 0) {
      $ret['error'] = 'ACTION_ERROR';
    } else {
      $index = $index[0];
      $error = false;
      $input = false;
      $save = array();
      $ids = array();
      foreach ($_POST AS $key => $values) {
        if ($key == 'select_type') {
          $type = $db->sql_trim_str($values);
        } elseif ($key == 'module_id') {
          $module_id = (int)$_POST['module_id'];
        } else {
          foreach ($values AS $a => $value) {
            if ($key != 'select_id' && $value != '') {
              $n = (int)$_POST['select_id'][$a];
              if ($n < 1) {
                $error = !$error ? 'ID_EMPTY' : $error;
                $input = !$input ? 'select_id_'.$a : $input;
              } else {
                $l = str_replace('topic_', '', $key);
                if (isset($save[$l.$n])) {
                  $error = !$error ? 'ID_EXISTS' : $error;
                  $input = !$input ? 'select_id_'.$a : $input;
                } else {
                  $save[$l.$n] = array('select_id' => $n, 'language' => $l, 'topic' => $db->sql_trim_str($value));
                }
              }
            }
          }
        }
      }
      if (!$error) {
        // remove old select
        $db->query("DELETE FROM `".DB_SELECT."` WHERE `module_id`='$index[id]' AND `type`='$type'");
        // add new select
        foreach ($save AS $i => $item) {
          $item['module_id'] = $index['id'];
          $item['type'] = $type;
          $db->add(DB_SELECT, $item);
        }
        $ret['error'] = 'SAVE_COMPLETE';
        $ret['location'] = 'reload';
      } else {
        $ret['error'] = $error;
        $ret['input'] = $input;
      }
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
