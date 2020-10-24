<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = '6.0.1';
  $languages = array();
  $l = array('id', 'key', 'type', 'owner', 'js');
  foreach ($db->customQuery("SHOW FIELDS FROM ".DB_LANGUAGE) AS $item) {
    if (!in_array($item['Field'], $l)) {
      $languages[] = $item['Field'];
    }
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
        $php[$key]['owner'] = 'index';
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
          $js[$match[1]]['owner'] = 'index';
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
  $db->query("UPDATE `".DB_LANGUAGE."` SET `owner`='index' WHERE `owner`=''");
  // บันทึกไฟล์ภาษา
  gcms::saveLanguage();
  echo '<li class=correct>Update <strong>languages</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
}
