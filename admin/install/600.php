<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = '6.0.0';
  $languages = array();
  $l = array('id', 'key', 'type', 'owner', 'js');
  foreach ($db->customQuery("SHOW FIELDS FROM ".DB_LANGUAGE) AS $item) {
    if (!in_array($item['Field'], $l)) {
      $languages[] = $item['Field'];
    }
  }
  // update language
  $sql = "SELECT * FROM `".DB_LANGUAGE."` WHERE `type`='array'";
  foreach ($db->customQuery($sql) AS $item) {
    foreach ($languages AS $l) {
      $item[$l] = rawurlencode($item[$l]);
    }
    $db->edit(DB_LANGUAGE, $item['id'], $item);
  }
  $php = array();
  $js = array();
  foreach ($languages AS $language) {
    if (is_file(ROOT_PATH."admin/install/language/$language.php")) {
      unset($lng);
      include (ROOT_PATH."admin/install/language/$language.php");
      foreach ($lng AS $key => $value) {
        if (is_array($value)) {
          $php[$key][$language] = rawurlencode(serialize($value));
          $php[$key]['type'] = 'array';
        } elseif (is_int($value)) {
          $php[$key][$language] = (int)$value;
          $php[$key]['type'] = 'int';
        } else {
          $php[$key][$language] = addslashes(str_replace(array('\n', "\'"), array("\n", "'"), $value));
          $php[$key]['type'] = 'text';
        }
        $php[$key]['js'] = 0;
        $php[$key]['key'] = $key;
      }
    }
    if (is_file(ROOT_PATH."admin/install/language/$language.js")) {
      foreach (file(ROOT_PATH."admin/install/language/$language.js") AS $item) {
        if (preg_match('/^var[\s]+([A-Z0-9_]+)[\s]+=[\s]+([\\\'"]?(.*?)[\\\'"]?);$/', trim($item), $match)) {
          if ($match[3] != $match[2]) {
            $js[$match[1]]['type'] = 'text';
          } else {
            $js[$match[1]]['type'] = 'int';
          }
          $js[$match[1]][$language] = addslashes(str_replace(array('\n', "\'"), array("\n", "'"), $match[3]));
          $js[$match[1]]['js'] = 1;
          $js[$match[1]]['key'] = $match[1];
        }
      }
    }
  }
  foreach ($php AS $key => $values) {
    $search = $db->customQuery("SELECT * FROM `".DB_LANGUAGE."` WHERE `key`='$key' AND `js`='0' LIMIT 1");
    if (sizeof($search) == 1) {
      $search = $search[0];
      foreach ($languages AS $l) {
        $search[$l] = addslashes(str_replace('\n', "\n", $search[$l]));
      }
    } else {
      $search = false;
    }
    if (!$search) {
      $db->add(DB_LANGUAGE, $values);
    } elseif ($search['th'] != $values['th']) {
      $db->edit(DB_LANGUAGE, $search['id'], $values);
    }
  }
  foreach ($js AS $key => $values) {
    $search = $db->customQuery("SELECT * FROM `".DB_LANGUAGE."` WHERE `key`='$key' AND `js`='1' LIMIT 1");
    if (sizeof($search) == 1) {
      $search = $search[0];
      foreach ($languages AS $l) {
        $search[$l] = addslashes(str_replace('\n', "\n", $search[$l]));
      }
    } else {
      $search = false;
    }
    if (!$search) {
      $db->add(DB_LANGUAGE, $values);
    } elseif ($search['th'] != $values['th']) {
      $db->edit(DB_LANGUAGE, $search['id'], $values);
    }
  }
  // .htaccess
  $datas = array();
  foreach (file(ROOT_PATH.'.htaccess') AS $line) {
    if (preg_match('/^(RewriteRule.*feed\|menu\|sitemap).*?(\).*L,QSA\])$/', trim($line), $match)) {
      $datas[] = "$match[1]|BingSiteAuth$match[2]";
    } else {
      $datas[] = trim($line);
    }
  }
  $f = @fopen(ROOT_PATH.'.htaccess', 'wb');
  if ($f) {
    fwrite($f, implode("\n", $datas));
    fclose($f);
  }
  echo '<li class='.($f ? 'correct' : 'incorrect').'>Update file <b>.htaccess</b> ...</li>';
  ob_flush();
  flush();
  $config = array();
  include (ROOT_PATH.'bin/config.php');
  // update config
  $config['mimeTypes']['swf'] = 'application/x-shockwave-flash';
  $config['mimeTypes']['gif'] = 'image/gif';
  $config['mimeTypes']['jpg'] = 'image/jpeg';
  $config['mimeTypes']['png'] = 'image/png';
  // บันทึก config
  gcms::saveConfig(ROOT_PATH.'bin/config.php', $config);
  echo '<li class=correct>Update <strong>config</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // บันทึกไฟล์ภาษา
  gcms::saveLanguage();
  echo '<li class=correct>Update <strong>languages</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // update tb_user
  $db->query("ALTER TABLE `".PREFIX."_user` ADD `fb` TINYINT(1)UNSIGNED NOT NULL");
  echo '<li class=correct>Update <strong>'.PREFIX.'_user</strong> <i> table complete...</i></li>';
  ob_flush();
  flush();
}
