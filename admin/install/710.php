<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = '7.1.0';
  // upgrade language
  $dir = ROOT_PATH.'admin/install/language/';
  if (is_dir($dir)) {
    $patt = '/^var[\s]+([A-Z0-9_]+)[\s]{0,}=[\s]{0,}[\'"](.*)[\'"];$/';
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
      if (preg_match('/([a-z]+)\.(php|js)/', $text, $match)) {
        if ($match[2] == 'php') {
          $lng = array();
          include ($dir.$text);
          foreach ($lng AS $key => $value) {
            if (is_array($value)) {
              $value = rawurlencode(serialize($value));
              $array = true;
            } else {
              $value = addslashes($value);
              $array = false;
            }
            // ตรวจสอบ key
            $sql = "SELECT * FROM `".DB_LANGUAGE."` WHERE `js`='0' AND `key`='$key' LIMIT 1";
            $search = $db->customQuery($sql);
            if (sizeof($search) == 0) {
              $save = array();
              $save['js'] = 0;
              $save['owner'] = 'index';
              $save['key'] = $key;
              $save[$match[1]] = $value;
              $save['type'] = $array ? 'array' : 'text';
              $db->add(DB_LANGUAGE, $save);
            }
            // ตรวจสอบ value
            $sql = "SELECT * FROM `".DB_LANGUAGE."` WHERE `js`='0' AND `key`='$key' AND `$match[1]`!='$value' LIMIT 1";
            $search = $db->customQuery($sql);
            if (sizeof($search) == 1) {
              $db->edit(DB_LANGUAGE, $search[0]['id'], array($match[1] => $value));
            }
          }
        } else {
          foreach (file($dir.$text) AS $item) {
            $item = trim($item);
            if ($item != '') {
              if (preg_match($patt, $item, $match2)) {
                // ตรวจสอบ key
                $sql = "SELECT * FROM `".DB_LANGUAGE."` WHERE `js`='1' AND `key`='$match2[1]' LIMIT 1";
                $search = $db->customQuery($sql);
                if (sizeof($search) == 0) {
                  $save = array();
                  $save['js'] = 1;
                  $save['owner'] = 'index';
                  $save['key'] = $match2[1];
                  $save[$match[1]] = $match2[2];
                  $save['type'] = 'text';
                  $db->add(DB_LANGUAGE, $save);
                }
                // ตรวจสอบ value
                $value = addslashes($match2[2]);
                $sql = "SELECT * FROM `".DB_LANGUAGE."` WHERE `js`='1' AND `key`='$match2[1]' AND `$match[1]`!='$value' LIMIT 1";
                $search = $db->customQuery($sql);
                if (sizeof($search) == 1) {
                  $db->edit(DB_LANGUAGE, $search[0]['id'], array($match[1] => $value));
                }
              }
            }
          }
        }
      }
    }
    closedir($f);
  }
  // update database
  if (!$db->fieldexists(DB_MENUS, 'alias')) {
    if ($db->query("ALTER TABLE `".DB_MENUS."` ADD `alias` VARCHAR(20) NOT NULL")) {
      echo '<li class=correct>Update database <strong>'.DB_MENUS.'</strong> <i>add alias complete...</i></li>';
    } else {
      echo '<li class=incorrect>Error update database <b>'.DB_MENUS.'</b> add alias</li>';
    }
    ob_flush();
    flush();
  }
  if (!$db->fieldexists(DB_INDEX, 'published_date')) {
    if ($db->query("ALTER TABLE `".DB_INDEX."` ADD `published_date` DATE NOT NULL")) {
      $db->query("UPDATE `".DB_INDEX."` SET `published_date`=NOW()");
      echo '<li class=correct>Update database <strong>'.DB_INDEX.'</strong> <i>add published_date complete...</i></li>';
    } else {
      echo '<li class=incorrect>Error update database <b>'.DB_INDEX.'</b> add published_date</li>';
    }
    ob_flush();
    flush();
  }
  if (!$db->fieldexists(DB_MENUS, 'published')) {
    if ($db->query("ALTER TABLE `".DB_MENUS."` ADD `published` ENUM( '0', '1' ) NOT NULL DEFAULT '1'")) {
      $db->query("UPDATE `".DB_INDEX."` SET `published`='1',`published_date`=NOW() WHERE `index`='1'");
      echo '<li class=correct>Update database <strong>'.DB_MENUS.', '.DB_INDEX.'</strong> <i>add published complete...</i></li>';
    } else {
      echo '<li class=incorrect>Error update database <b>'.DB_MENUS.', '.DB_INDEX.'</b> add published</li>';
    }
    ob_flush();
    flush();
  }
  if (!$db->fieldexists(DB_INDEX, 'related')) {
    if ($db->query("ALTER TABLE `".DB_INDEX."` ADD `related` VARCHAR(149) NOT NULL")) {
      echo '<li class=correct>Update database <strong>'.DB_INDEX.'</strong> <i>add related complete...</i></li>';
    } else {
      echo '<li class=incorrect>Error update database <b>'.DB_INDEX.'</b> add related</li>';
    }
    ob_flush();
    flush();
  }
  if (!$db->fieldexists(DB_INDEX, 'alias')) {
    if ($db->query("ALTER TABLE `".DB_INDEX."` ADD `alias` VARCHAR(64) NOT NULL")) {
      $db->query("UPDATE `".DB_INDEX."` SET `alias`=`topic`");
      echo '<li class=correct>Update database <strong>'.DB_INDEX.'</strong> <i>add alias complete...</i></li>';
    } else {
      echo '<li class=incorrect>Error update database <b>'.DB_INDEX.'</b> add alias</li>';
    }
    ob_flush();
    flush();
  }
  if (!$db->fieldexists(DB_COUNTRY, 'zone')) {
    if ($db->query("ALTER TABLE `".DB_COUNTRY."` ADD `zone` TINYINT(1) UNSIGNED NOT NULL")) {
      $db->query("ALTER TABLE `".DB_COUNTRY."` DROP `name`,DROP `iso3`,DROP `numcode`");
      $db->query("ALTER TABLE `".DB_COUNTRY."` DROP PRIMARY KEY");
      $db->query("ALTER TABLE `".DB_COUNTRY."` ADD `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
      echo '<li class=correct>Update database <strong>'.DB_COUNTRY.'</strong> <i>add zone complete...</i></li>';
    } else {
      echo '<li class=incorrect>Error update database <b>'.DB_COUNTRY.'</b> add zone</li>';
    }
    ob_flush();
    flush();
  }
  // published
  $db->query("UPDATE `".DB_INDEX."` SET `published`='1'");
  $db->query("UPDATE `".DB_MENUS."` SET `published`='1'");
  // บันทึกไฟล์ภาษา
  gcms::saveLanguage();
  echo '<li class=correct>Update <strong>languages</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // update config
  $config = array();
  include (ROOT_PATH.'admin/install/default.config.php');
  include (ROOT_PATH.'bin/config.php');
  // admin theme
  $config['admin_skin'] = 'responsetive';
  gcms::saveConfig(ROOT_PATH.'bin/config.php', $config);
  echo '<li class="'.($f ? 'correct' : 'incorrect').'">Update file <b>config.php</b> ...</li>';
  ob_flush();
  flush();
}
