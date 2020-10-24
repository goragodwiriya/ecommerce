<?php
// admin/installing.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// ตรวจสอบ referer และ admin
if (gcms::isReferer() && gcms::isAdmin()) {
  $module = $_POST['module'];
  if (preg_match('/^(modules|widgets)\/([a-z]+)$/', $module) && is_file(ROOT_PATH."$module/admin_install.php") && $_SESSION['login']['account'] != 'demo') {
    $content = array();
    $content[] = '<div class=install>';
    $content[] = '<h3>{LNG_INSTALL}</h3>';
    $content[] = '<ol class=install>';
    define('MAIN_INIT', 'installing');
    include (ROOT_PATH."$module/admin_install.php");
    $content[] = '</ol>';
    $content[] = '<p>{LNG_INSTALL_COMPLETE}</p>';
    $content[] = '</div>';
    if ($redirect != '') {
      $content[] = '<meta http-equiv=refresh content="5;url='.$redirect.'">';
    }
    echo gcms::pregReplace('/{(LNG_[A-Z0-9_]+)}/e', 'gcms::getLng', implode('', $content));
  } else {
    echo "<aside class=error>$lng[LNG_DATA_NOT_FOUND]</aside>";
  }
}
