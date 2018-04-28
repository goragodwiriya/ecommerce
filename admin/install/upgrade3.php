<?php
if (INSTALL_INIT == 'upgrade') {
  // ลบไฟล์ config
  $f = fopen(ROOT_PATH.'bin/config.php', 'wb');
  if ($f) {
    fwrite($f, '');
    fclose($f);
  }
  header('Location: index.php');
}
