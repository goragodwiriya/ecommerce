<?php
// modules/product/admin_upload_file.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, can_write
if (gcms::isReferer() && gcms::canConfig($config['product_can_write'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // ค่าที่ส่งมา
    list($product_id, $id, $n) = explode('-', $_POST['productpicid']);
    $product_id = (int)$product_id;
    $n = (int)$n;
    // ค่าที่ส่งมา
    $ret = array();
    // ตรวจสอบโมดูล และสินค้า
    $sql = "SELECT COALESCE(I.`id`,-1) AS `id`,P.`id` AS `product_id`,P.`module_id`,I.`image`";
    $sql .= " FROM `".DB_PRODUCT."` AS P";
    $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`owner`='product' AND P.`module_id`=M.`id`";
    $sql .= " LEFT JOIN `".DB_PRODUCT_IMAGE."` AS I ON I.`product_id`=$product_id AND I.`id`=$n";
    $sql .= " WHERE P.`id`=$product_id LIMIT 1";
    $index = $db->customQuery($sql);
    if (sizeof($index) != 1) {
      $ret['error'] = 'ACTION_ERROR';
    } elseif ($n < $config['product_picture_count']) {
      $index = $index[0];
      foreach ($_FILES AS $file) {
        if ($file['tmp_name'] == '') {
          $ret['error'] = 'PRODUCT_PICTURE_EMPTY';
        } else {
          // ตรวจสอบไฟล์อัปโหลด
          $info = gcms::isValidImage($config['product_image_type'], $file);
          if (!$info) {
            $ret['error'] = 'INVALID_FILE_TYPE';
          } else {
            // path เก็บไฟล์
            $dir = DATA_PATH.'product/';
            // ชื่อรูปภาพใหม่
            $gallery['image'] = "$index[product_id]-$n.jpg";
            $gallery['thumbnail'] = "thumb-$index[product_id]-$n.jpg";
            // thumbnail
            if (!gcms::cropImage($file['tmp_name'], $dir.$gallery['thumbnail'], $info, $config['product_thumbnail_width'], $config['product_thumbnail_height'])) {
              $ret['error'] = 'DO_NOT_UPLOAD';
            } else {
              // picture
              $res = gcms::resizeImage($file['tmp_name'], $dir, $gallery['image'], $info, $config['product_image_width']);
              if (!$res) {
                $ret['error'] = 'DO_NOT_UPLOAD';
              } else {
                // บันทึกข้อมูล
                $ret['error'] = '';
                if ($index['id'] != -1) {
                  // แก้ไข ลบรายการเดิม
                  $db->query("DELETE FROM `".DB_PRODUCT_IMAGE."` WHERE `product_id`='$index[product_id]' AND `id`='$index[id]'");
                }
                // บันทึก
                $gallery['id'] = $n;
                $gallery['product_id'] = $index['product_id'];
                $db->add(DB_PRODUCT_IMAGE, $gallery);
                // ลบรูปภาพเดิม
                if ($index['image'] != $gallery['image']) {
                  @unlink($dir.$index['image']);
                }
                // คืนค่า
                $ret['product_id'] = $index['product_id'];
                $ret['index'] = $n;
                $ret['img'] = rawurlencode(DATA_URL."product/$gallery[thumbnail]?$mmktime");
              }
            }
          }
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
