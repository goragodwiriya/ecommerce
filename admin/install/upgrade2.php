<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = VERSION;
  echo '<h2>ปรับรุ่น GCMS เป็นเวอร์ชั่น '.$version.' เรียบร้อย</h2>';
  echo '<ol>';
  ob_flush();
  flush();
  if (!defined('DB_MENUS')) {
    define('DB_MENUS', PREFIX.'_menus');
  }
  if (!defined('DB_LANGUAGE')) {
    define('DB_LANGUAGE', PREFIX.'_language');
  }
  $defines = array();
  // upgrade ทีละเวอร์ชั่น
  while ($current_version != $version) {
    include (ROOT_PATH.'admin/install/upgrading.php');
    echo '<li class=correct>Upgrade to <strong>'.$current_version.'</strong> <i>complete...</i></li>';
    ob_flush();
    flush();
  }
  // update vars.php
  if (VERSION != $version || sizeof($defines) > 0) {
    $db_weburl = WEB_URL;
    $prefix = PREFIX;
    echo '<li class="'.(writeVar($defines) ? 'correct' : 'incorrect').'">Update file <b>vars.php</b> ...</li>';
    ob_flush();
    flush();
  }
  // optimize tables
  $db->query("OPTIMIZE TABLE `".DB_INDEX."`");
  $db->query("OPTIMIZE TABLE `".DB_MODULES."`");
  $db->query("OPTIMIZE TABLE `".DB_USER."`");
  $db->query("OPTIMIZE TABLE `".DB_LANGUAGE."`");
  echo '<li class=correct>Fix and repair <strong>database</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  if (@rename(ROOT_PATH.'admin/install/', ROOT_PATH."admin/$mmktime/")) {
    echo '<li class=correct>โฟลเดอร์ <i>admin/install/</i> ถูกเปลี่ยนชื่อเป็น <i>admin/'.$mmktime.'/</i></li>';
  } else {
    echo '<li class=correct>กรุณาลบโฟลเดอร์ <i>admin/install/</i> ก่อนดำเนินการต่อ</li>';
  }
  ob_flush();
  flush();
  echo '</ol>';
  echo '<p>ปรับรุ่น GCMS เป็นเวอร์ชั่น <strong>'.$version.'</strong> เรียบร้อยแล้ว</p>';
  echo '<p>หากคุณต้องการความช่วยเหลือ คุณสามารถ ติดต่อสอบถามได้ที่ <a href="http://www.goragod.com" target=_blank>http://www.goragod.com</a> หรือ <a href="http://gcms.in.th" target="_blank">http://gcms.in.th</a></p>';
  echo '<p><a href="'.WEB_URL.'/admin/index.php?module=system" class=button>เข้าระบบผู้ดูแล</a></p>';
}
