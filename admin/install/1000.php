<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = '9.3.0';
  // upgrade language
  $a = array('id', 'owner', 'key', 'type', 'js');
  $sql = "SELECT * FROM `".DB_LANGUAGE."` WHERE `type`='array'";
  foreach ($db->customQuery($sql) AS $item) {
    foreach ($item AS $key => $value) {
      if (in_array($key, $a)) {
        $item[$key] = $value;
      } else {
        $item[$key] = rawurldecode($value);
      }
    }
    $db->edit(DB_LANGUAGE, $item['id'], $item);
  }
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
              $value = serialize($value);
              $array = true;
            } else {
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
            $sql = "SELECT * FROM `".DB_LANGUAGE."` WHERE `js`='0' AND `key`='$key' AND `$match[1]`!='".addslashes($value)."' LIMIT 1";
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
  // บันทึกไฟล์ภาษา
  gcms::saveLanguage();
  echo '<li class=correct>Update <strong>languages</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // update vars
  $defines = array();
  echo '<li class="'.(writeVar($defines) ? 'correct' : 'incorrect').'">Update file <b>vars.php</b> ...</li>';
  ob_flush();
  flush();
  $db->query("ALTER TABLE `".DB_PROVINCE."` CHANGE `id` `id` SMALLINT( 3 ) UNSIGNED NOT NULL");
  $db->query("ALTER TABLE `".DB_PROVINCE."` ADD PRIMARY KEY ( `id` ) ;");
  echo '<li class=correct>Update database <strong>'.DB_PROVINCE.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // ubdate db_menus
  $db->query("ALTER TABLE `".DB_MENUS."` CHANGE `published` `published` ENUM('0', '1', '2','3') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '1'");
  echo '<li class=correct>Update database <strong>'.DB_MENUS.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  $db->query("UPDATE `".DB_USER."` SET `address2`=CONCAT(`address2`,' ',`tambon`,' ',`district`)");
  $db->query("ALTER TABLE `".DB_USER."` DROP `tambon`,DROP `district`,DROP `tambonID`,DROP `districtID`,DROP `introduce`");
  $db->query("ALTER TABLE `".DB_USER."` ADD `pname` VARCHAR( 50 ) NULL AFTER `password`");
  $db->query("ALTER TABLE `".DB_USER."` ADD `admin_access` ENUM('0','1') NOT NULL DEFAULT '0';");
  $db->query("UPDATE `".DB_USER."` SET `admin_access`='1' WHERE `id` IN (SELECT * FROM (SELECT `id` FROM `".DB_USER."` WHERE `status` IN (".implode(',', $config['admin_access']).")) AS Z);");
  echo '<li class=correct>Update database <strong>'.DB_USER.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  $db->query("UPDATE `".DB_BOARD_Q."` SET `create_date`=`last_update` WHERE `create_date`=0");
  echo '<li class=correct>Update database <strong>'.DB_BOARD_Q.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  if (defined('DB_TEXTLINK')) {
    $db->query("ALTER TABLE `".DB_TEXTLINK."` CHANGE `type` `type` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
    $db->query("ALTER TABLE `".DB_TEXTLINK."` ADD `template` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
    echo '<li class=correct>Update database <strong>'.DB_TEXTLINK.'</strong> <i>complete...</i></li>';
    ob_flush();
    flush();
  }
}
