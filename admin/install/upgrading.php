<?php
if (INSTALL_INIT == 'upgrade') {
  if (version_compare($current_version, '5.0.0') == -1) {
    // upgrade to 5.0.0
    include (ROOT_PATH.'admin/install/500.php');
  } elseif (version_compare($current_version, '5.1.0') == -1) {
    // upgrade to 5.1.0
    $current_version = '5.1.0';
    // install sql 5.1.0
    gcms::install(ROOT_PATH.'admin/install/510.php');
  } elseif (version_compare($current_version, '5.2.1') == -1) {
    // upgrade to 5.2.1
    include (ROOT_PATH.'admin/install/521.php');
  } elseif (version_compare($current_version, '6.0.0') == -1) {
    // upgrade to 6.0.0
    include (ROOT_PATH.'admin/install/600.php');
  } elseif (version_compare($current_version, '6.0.1') == -1) {
    // upgrade to 6.0.1
    include (ROOT_PATH.'admin/install/601.php');
  } elseif (version_compare($current_version, '7.1.0') == -1) {
    // upgrade to 7.1.0
    include (ROOT_PATH.'admin/install/710.php');
  } elseif (version_compare($current_version, '8.2.0') == -1) {
    // upgrade to 8.2.0
    include (ROOT_PATH.'admin/install/820.php');
  } elseif (version_compare($current_version, '9.0.0') == -1) {
    // upgrade to 9.0.0
    include (ROOT_PATH.'admin/install/900.php');
  } else {
    $m = str_replace('.', '', $version);
    if (is_file(ROOT_PATH."admin/install/$m.php")) {
      // upgrade
      include (ROOT_PATH."admin/install/$m.php");
    }
  }
}
