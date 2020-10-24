<?php
// modules/product/index.php
if (defined('MAIN_INIT')) {
  // หน้าที่เรียก, ตรวจสอบว่ามีหน้าที่เรียกจริงหรือไม่
  $modules[3] = $modules[3] != '' && is_file(ROOT_PATH."modules/product/$modules[3].php") ? $modules[3] : 'main';
  // โหลดไฟล์ที่เรียก
  include (ROOT_PATH."modules/product/$modules[3].php");
}
