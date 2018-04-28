<?php
// modules/product/main.php
if (defined('MAIN_INIT')) {
  // เลือกไฟล์
  if (isset($_REQUEST['id'])) {
    // แสดงสินค้าที่เลือก
    include (ROOT_PATH.'modules/product/view.php');
  } else {
    include (ROOT_PATH.'modules/product/list.php');
  }
}
