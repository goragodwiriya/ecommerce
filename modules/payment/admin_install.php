<?php
// modules/payment/admin_install.php
if (MAIN_INIT == 'installing') {
  if ($ftp->mkdir(DATA_PATH.'payment/', 0755)) {
    $content[] = '<li class=valid>โฟลเดอร์ <strong>'.DATA_FOLDER.'payment/</strong> สามารถใช้งานได้</li>';
  } else {
    $content[] = '<li class=invalid>โฟลเดอร์ <strong>'.DATA_FOLDER.'payment/</strong> <em>ไม่สามารถเขียนหรือสร้างได้</em> กรุณาสร้างโฟลเดอร์นี้และปรับ chmod ให้เป็น 755 ด้วยตัวเอง</li>';
  }
  // install module
  gcms::installModule('payment', 'payment', 'Payment', 'MAINMENU', 'Payment');
  $content[] = '<li class=valid>ติดตั้งโมดูล <strong>Payment</strong> เรียบร้อย</li>';
  // install sql
  gcms::install(ROOT_PATH.'modules/payment/sql.php');
  // โหลด config ใหม่
  $config = array();
  if (is_file(CONFIG)) {
    include CONFIG;
  }
  // โหลด config ของโมดูล
  include (ROOT_PATH.'modules/payment/default.config.php');
  $config = array_merge($config, $newconfig['payment']);
  // save config
  if (gcms::saveconfig(CONFIG, $config)) {
    $content[] = '<li class=valid>Add <strong>configs</strong> complete.</li>';
  } else {
    $content[] = '<li class=invalid>'.sprintf($lng['ERROR_FILE_READ_ONLY'], 'bin/config.php').'</li>';
  }
  // add vars
  if (sizeof($defines) > 0) {
    if ($ftp->fwrite(ROOT_PATH.'bin/vars.php', 'ab', "\n\t// Module Payment\n\t".implode("\n\t", $defines))) {
      $content[] = '<li class=valid>Add <strong>vars</strong> complete.</li>';
    } else {
      $content[] = '<li class=invalid>'.sprintf($lng['ERROR_FILE_READ_ONLY'], 'bin/vars.php').'</li>';
    }
  }
  // บันทึกภาษา
  gcms::saveLanguage();
  $content[] = '<li class=valid>Add <strong>Language</strong> complete.</li>';
}
