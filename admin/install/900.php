<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = '9.0.0';
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
  // อัปเดตฐานข้อมูล
  $db->query("ALTER TABLE `".DB_USER."` ADD `session_id` VARCHAR(32) NOT NULL");
  $db->query("OPTIMIZE TABLE `".DB_USER."`");
  echo '<li class=correct>Optimize and Update database <strong>'.DB_USER.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // update categories
  $db->query("DROP TABLE IF EXISTS `".DB_CATEGORY."_tmp`");
  $db->query("CREATE TABLE `".DB_CATEGORY."_tmp` LIKE `".DB_CATEGORY."`");
  $db->query("ALTER TABLE `".DB_CATEGORY."_tmp` ADD `topic` TEXT NOT NULL");
  $db->query("ALTER TABLE `".DB_CATEGORY."_tmp` ADD `detail` TEXT NOT NULL");
  $db->query("ALTER TABLE `".DB_CATEGORY."_tmp` ADD `icon` TEXT NOT NULL");
  $sql = " SELECT C.*,D.`language`,D.`topic`,D.`detail`,D.`icon` FROM `".DB_CATEGORY."` AS C";
  $sql .= " INNER JOIN `".DB_CATEGORY_DETAIL."` AS D ON D.`category_id`=C.`id` AND D.`module_id`=C.`module_id`";
  $sql .= " ORDER BY C.`module_id`,C.`category_id`,D.`language`";
  foreach ($db->customQuery($sql) AS $item) {
    $save = array();
    foreach ($item AS $k => $v) {
      if (!in_array($k, array('id', 'topic', 'detail', 'icon', 'language'))) {
        $categories[$item['module_id']][$item['category_id']][$k] = $v;
      }
    }
    if ($item['topic'] != '') {
      $categories[$item['module_id']][$item['category_id']]['topic'][$item['language']] = $item['topic'];
    }
    if ($item['detail'] != '') {
      $categories[$item['module_id']][$item['category_id']]['detail'][$item['language']] = $item['detail'];
    }
    if ($item['icon'] != '') {
      $categories[$item['module_id']][$item['category_id']]['icon'][$item['language']] = $item['icon'];
    }
  }
  if (is_array($categories)) {
    foreach ($categories AS $modules) {
      foreach ($modules AS $item) {
        $item['topic'] = serialize($item['topic']);
        $item['detail'] = serialize($item['detail']);
        $item['icon'] = serialize($item['icon']);
        $db->add(DB_CATEGORY.'_tmp', $item);
      }
    }
  }
  $db->query("ALTER TABLE `".DB_CATEGORY."` RENAME `".DB_CATEGORY.VERSION."`");
  $db->query("ALTER TABLE `".DB_CATEGORY."_tmp` RENAME `".DB_CATEGORY."`");
  echo '<li class=correct>Update database <strong>'.DB_CATEGORY.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  $db->query("ALTER TABLE `".DB_INDEX_DETAIL."` ADD `relate` VARCHAR( 64 ) NOT NULL");
  $db->query("UPDATE `".DB_INDEX_DETAIL."` SET `relate`=`keywords`");
  echo '<li class=correct>Update database <strong>'.DB_INDEX_DETAIL.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
}
