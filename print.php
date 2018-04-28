<?php
// print.php
// inint
include 'bin/inint.php';
// โมดูลที่ต้องการ
$module = $_GET['module'];
$content = '';
if (preg_match('/^[a-z]+$/', $module)) {
  // ตรวจสอบโมดูลที่เรียก
  $sql = "SELECT `id`,`module`,`owner`,`config`";
  $sql .= " FROM `".DB_MODULES."` AS M";
  $sql .= " WHERE `module`='$module'";
  $sql .= " LIMIT 1";
  $modules = $cache->get($sql);
  if (!$modules) {
    $modules = $db->customQuery($sql);
    $cache->save($sql, $modules);
  }
  if (sizeof($modules) == 1 && is_file(ROOT_PATH.'modules/'.$modules[0]['owner'].'/print.php')) {
    $modules = $modules[0];
    // login
    $login = $_SESSION['login'];
    // โหลดโมดูลสำหรับพิมพ์
    include (ROOT_PATH."modules/$modules[owner]/print.php");
  }
}
if ($content == '') {
  header("HTTP/1.0 404 Not Found");
  header("Status: 404 Not Found");
} else {
  echo '<!DOCTYPE html>';
  echo '<html lang='.LANGUAGE.' dir=ltr>';
  echo '<head>';
  echo '<title>'.$title.'</title>';
  echo '<meta charset=utf-8>';
  echo '<link rel=stylesheet href='.WEB_URL.'/skin/print.css>';
  echo '</head>';
  echo '<body onload="window.print()">';
  echo gcms::pregReplace('/{(LNG_[A-Z0-9_]+)}/e', 'gcms::getLng', $content);
  echo '</body>';
  echo '</html>';
}
