<?php
// admin/install.php
if (isset($_GET['modules'])) {
  $module = $_GET['modules'];
  $src = 'modules';
} else {
  $module = $_GET['widgets'];
  $src = 'widgets';
}
if (MAIN_INIT == 'admin' && $isAdmin && preg_match('/^[a-z]+$/', $module) && is_file(ROOT_PATH."$src/$module/admin_install.php")) {
  // title
  $title = ucfirst($module)." ($lng[LNG_FIRST_INSTALL])";
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul>';
  $content[] = '<li><span class=icon-'.$src.'>{LNG_'.strtoupper($src).'}</span></li>';
  $content[] = '<li><span>'.ucfirst($module).'</span></li>';
  $content[] = '</ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-import>{LNG_FIRST_INSTALL}</h1></header>';
  $content[] = '<div class=setup_frm>';
  $content[] = '<h2><span class=icon-'.$module.'>'.ucfirst($module).'</span></h2>';
  if (isset($install_modules[$module])) {
    $content[] = '<aside class=error>'.str_replace('{MODULE}', $module, $lng['LNG_INSTALL_MODULE_EXISTS']).'</aside>';
  } else {
    // ติดตั้งครั้งแรก
    $content[] = '<div id=install>';
    $content[] = '<aside class=message>{LNG_FIRST_INSTALL_DETAIL}</aside>';
    $content[] = '<div class=submit>';
    $content[] = '<a class="button large ok" id=install_btn><span class=icon-continue>{LNG_INSTALL}</span></a>';
    $content[] = '</div>';
    $content[] = '</div>';
    $content[] = '<script>';
    $content[] = "callInstall('install_btn', '$src/$module');";
    $content[] = '</script>';
  }
  $content[] = '</div>';
  $content[] = '</section>';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
