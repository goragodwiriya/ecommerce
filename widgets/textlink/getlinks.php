<?php
// widgets/textlink/getlinks.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer
if (gcms::isReferer()) {
  // config
  include (ROOT_PATH.'widgets/textlink/styles.php');
  // ค่าที่ส่งมา
  $module = $_POST['type'];
  // ค่าคงที่สำหรับป้องกันการเรียกหน้าเพจโดยตรง
  DEFINE('MAIN_INIT', __FILE__);
  // เรียกโมดูล textlink
  include (ROOT_PATH.'widgets/textlink/index.php');
  // แสดงผล
  echo $widget;
}
