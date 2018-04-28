<?php
// modules/product/admin_upload_delete.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, can_write
if (gcms::isReferer() && gcms::canConfig($config['product_can_write'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // ตรวจสอบรูปที่เลือก
    $sql = "SELECT `id`,`product_id`,`thumbnail`,`image`";
    $sql .= " FROM `".DB_PRODUCT_IMAGE."`";
    $sql .= " WHERE `product_id`=".(int)$_POST['pid']." AND `id`=".(int)$_POST['id'];
    $sql .= " LIMIT 1";
    $image = $db->customQuery($sql);
    if (sizeof($image) == 1) {
      $image = $image[0];
      // path เก็บไฟล์
      $dir = DATA_PATH.'product/';
      // ลบไฟล์
      @unlink($dir.$image['image']);
      @unlink($dir.$image['thumbnail']);
      // คืนค่า id ที่ลบ
      $ret["delete_$image[id]"] = $image['id'];
      // ลบข้อมูลรูปภาพ
      $db->query("DELETE FROM `".DB_PRODUCT_IMAGE."` WHERE `product_id`='$image[product_id]' AND `id`='$image[id]' LIMIT 1");
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
