<?php
// widgets/textlink/admin_install.php
if (MAIN_INIT == 'installing') {
  // install sql
  gcms::install(ROOT_PATH.'widgets/textlink/sql.php');
  // add vars
  if (sizeof($defines) > 0) {
    if ($ftp->fwrite(ROOT_PATH.'bin/vars.php', 'ab', "\n\t// Widget Textlink\n\t".implode("\n\t", $defines))) {
      $content[] = '<li class=valid>Add <strong>vars</strong> complete.</li>';
    } else {
      $content[] = '<li class=invalid>'.sprintf($lng['ERROR_FILE_READ_ONLY'], 'bin/vars.php').'</li>';
    }
  }
  // บันทึกภาษา
  gcms::saveLanguage();
  $content[] = '<li class=valid>Add <strong>Language</strong> complete.</li>';
}
