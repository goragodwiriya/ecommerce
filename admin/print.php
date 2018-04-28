<?php
// admin/print.php
// inint
include '../bin/inint.php';
// โมดูลที่ต้องการ
preg_match('/^([a-z]+)-(.*)$/', $_GET['module'], $match);
$content = array();
if (is_file(ROOT_PATH."modules/$match[1]/print_$match[2].php")) {
  // สถานะของ member และ admin
  $isMember = gcms::isMember();
  $isAdmin = gcms::isAdmin();
  $canAdmin = $_SESSION['login']['admin_access'] == 1;
  // ป้องกันการเรียกหน้าเพจโดยตรง
  DEFINE('MAIN_INIT', 'admin');
  // โหลดโมดูลสำหรับพิมพ์
  include (ROOT_PATH."modules/$match[1]/print_$match[2].php");
}
if (sizeof($content) == 0) {
  header("HTTP/1.0 404 Not Found");
  header("Status: 404 Not Found");
} else {
  echo '<!DOCTYPE html>';
  echo '<html lang='.LANGUAGE.' dir=ltr>';
  echo '<head>';
  echo '<title>'.$title.'</title>';
  echo '<meta charset=utf-8>';
  echo '<link rel=stylesheet href='.WEB_URL.'/'.SKIN.'/print.css>';
  echo '</head>';
  echo '<body>';
  echo gcms::pregReplace('/{(LNG_[A-Z0-9_]+)}/e', 'gcms::getLng', implode("\n", $content));
  echo '</body>';
  echo '</html>';
}
