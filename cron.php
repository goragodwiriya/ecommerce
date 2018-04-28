<?php
// cron.php
// โหลดไฟล์ inint
include (dirname(__FILE__).'/bin/inint.php');
// ค่าคงที่สำหรับป้องกันการเรียกหน้าเพจโดยตรง
DEFINE('MAIN_INIT', 'cron');
// บันทึกการเรียกใช้ Cron Job
$ftp->fwrite(DATA_PATH.'index.php', 'wb', date('d-m-Y H:i:s', $mmktime));
// โมดูลที่ติดตั้ง
$dir = ROOT_PATH.'modules/';
$f = @opendir($dir);
if ($f) {
  while (false !== ($text = readdir($f))) {
    if ($text != '.' && $text != '..') {
      if (is_file($dir.$text.'/cron.php')) {
        include ($dir.$text.'/cron.php');
      }
    }
  }
  closedir($f);
}
