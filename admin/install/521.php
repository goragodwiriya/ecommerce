<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = '5.2.1';
  // ตรวจสอบไฟล์ภาษาที่ติดตั้งก่อนหน้า
  $dir = ROOT_PATH."language/";
  if (is_dir($dir)) {
    $languages = array();
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
      if ($text != '.' && $text != '..') {
        if (is_file($dir.$text) && preg_match('/^([a-z]{2,2})\.gif$/', $text, $match)) {
          @copy($dir.$text, DATA_PATH.'language/'.$text);
          $languages[$match[1]] = $match[1];
        }
      }
    }
    closedir($f);
  }
  // ตรวจสอบโฟลเดอร์ภาษา (ถ้าอัปเกรดจาก 5.1.0 ขึ้นไป)
  $dir = DATA_PATH."language/";
  if (is_dir($dir)) {
    $languages = array();
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
      if ($text != '.' && $text != '..') {
        if (is_file($dir.$text) && preg_match('/^([a-z]{2,2})\.(php|js)$/', $text, $match)) {
          $languages[$match[1]] = $match[1];
        }
      }
    }
    closedir($f);
  }
  // สร้างตาราง ภาษา
  $db->query("DROP TABLE IF EXISTS `".PREFIX."_language`");
  $sql = "CREATE TABLE `".PREFIX."_language` (";
  $sql .= "`id` int(11) NOT NULL auto_increment,";
  $sql .= "`key` text collate utf8_unicode_ci NOT NULL,";
  foreach ($languages AS $language) {
    $sql .= "`$language` text collate utf8_unicode_ci NOT NULL,";
  }
  $sql .= "`type` varchar(5) collate utf8_unicode_ci NOT NULL,";
  $sql .= "`owner` varchar(20) collate utf8_unicode_ci NOT NULL,";
  $sql .= "`js` tinyint(1) NOT NULL,";
  $sql .= "PRIMARY KEY (`id`)";
  $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
  $db->query($sql);
  echo '<li class=correct>CREATE TABLE <strong>'.PREFIX.'_language</strong> ...</li>';
  ob_flush();
  flush();
  // import new language
  $sqlfiles = array();
  $sqlfiles[] = ROOT_PATH.'admin/install/sql.php';
  $dir = ROOT_PATH."modules/";
  $f = opendir($dir);
  while (false !== ($text = readdir($f))) {
    if ($text != '.' && $text != '..') {
      if (is_file($dir.$text.'/sql.php')) {
        $sqlfiles[] = $dir.$text.'/sql.php';
      }
    }
  }
  closedir($f);
  $dir = ROOT_PATH."widgets/";
  $f = opendir($dir);
  while (false !== ($text = readdir($f))) {
    if ($text != '.' && $text != '..') {
      if (is_file($dir.$text.'/sql.php')) {
        $sqlfiles[] = $dir.$text.'/sql.php';
      }
    }
  }
  closedir($f);
  foreach ($sqlfiles AS $folder) {
    $fr = file($folder);
    foreach ($fr AS $value) {
      $sql = str_replace(array('{prefix}', '{WEBMASTER}', '{NOREPLY}', '\r', '\n'), array(PREFIX, $config['webmaster_email'], $reply, "\r", "\n"), trim($value));
      if ($sql != '') {
        if (preg_match('/INSERT[\s]+INTO[\s]+`'.PREFIX.'_language`.*/iu', $sql, $match)) {
          // install language
          $db->query($sql);
        }
      }
    }
  }
  echo '<li class=correct>Install <strong>new languages</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // import old language
  include (ROOT_PATH.'admin/install/langtool.php');
  // บันทึกไฟล์ภาษา
  gcms::saveLanguage();
  echo '<li class=correct>Import <strong>old languages</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
}
