<?php
// modules/product/admin_config_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, admin
if (gcms::isReferer() && gcms::canConfig($config['product_can_config'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // โหลด config ของโมดูล
    $config = array();
    if (is_file(CONFIG)) {
      include CONFIG;
    }
    // ค่าที่ส่งมา
    $config['product_id_format'] = $db->sql_trim_str($_POST['config_id_format']);
    $config['product_thumbnail_width'] = max(32, (int)$_POST['config_thumbnail_width']);
    $config['product_thumbnail_height'] = max(32, (int)$_POST['config_thumbnail_height']);
    $config['product_image_width'] = max(100, (int)$_POST['config_image_width']);
    $config['product_order_no'] = $db->sql_trim_str($_POST['config_order_no']);
    $config['product_transportation'] = (double)$_POST['config_transportation'];
    $config['product_discount'] = (double)$_POST['config_discount'];
    $config['product_order_delete'] = (int)$_POST['config_order_delete'];
    $config['product_cut_stock'] = (int)$_POST['config_cut_stock'];
    $config['product_picture_count'] = (int)$_POST['config_upload_count'];
    $config['product_show_menu'] = (int)$_POST['config_show_menu'];
    $config['product_rows'] = (int)$_POST['config_product_rows'];
    $config['product_cols'] = (int)$_POST['config_product_cols'];
    $config['product_sort'] = (int)$_POST['config_product_sort'];
    // ตรวจสอบค่าที่ส่งมา
    $ret['ret_config_id_format'] = '';
    $ret['ret_config_image_type'] = '';
    $ret['ret_config_order_no'] = '';
    $ret['ret_config_transportation'] = '';
    if ($config['product_id_format'] == '') {
      $ret['error'] = 'FORMAT_NO_EMPTY';
      $ret['input'] = 'config_id_format';
      $ret['ret_config_id_format'] = 'FORMAT_NO_EMPTY';
    } elseif (!isset($_POST['config_image_type'])) {
      $ret['error'] = 'IMAGE_TYPE_EMPTY';
      $ret['input'] = 'config_image_type';
      $ret['ret_config_image_type'] = 'IMAGE_TYPE_EMPTY';
    } elseif ($config['product_order_no'] == '') {
      $ret['error'] = 'FORMAT_ORDER_NO_EMPTY';
      $ret['input'] = 'config_order_no';
      $ret['ret_config_order_no'] = 'FORMAT_ORDER_NO_EMPTY';
    } else {
      $config['product_image_type'] = $_POST['config_image_type'];
      $config['product_can_write'] = $_POST['config_can_write'];
      $config['product_can_write'][] = 1;
      $config['product_moderator'] = $_POST['config_moderator'];
      $config['product_moderator'][] = 1;
      $config['product_salesman'] = $_POST['config_salesman'];
      $config['product_salesman'][] = 1;
      $config['product_can_config'] = $_POST['config_can_config'];
      $config['product_can_config'][] = 1;
      // บันทึก config.php
      if (gcms::saveconfig(CONFIG, $config)) {
        $ret['error'] = 'SAVE_COMPLETE';
        $ret['location'] = 'reload';
      } else {
        $ret['error'] = 'DO_NOT_SAVE';
      }
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
