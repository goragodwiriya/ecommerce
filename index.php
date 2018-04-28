<?php
// index.php
$dir = dirname(__FILE__);
if (is_file($dir.'/bin/config.php') && is_file($dir.'/bin/vars.php')) {
  // โหลดไฟล์ inint
  include ($dir.'/bin/inint.php');
  // ค่าคงที่สำหรับป้องกันการเรียกหน้าเพจโดยตรง
  DEFINE('MAIN_INIT', 'load');
  // โหลด gcms
  include (ROOT_PATH.'load.php');
} else {
  // install
  $host = $_SERVER['HTTP_HOST'];
  $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
  header("Location: http://$host$uri/admin/index.php");
  exit;
}