<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = '8.2.0';
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
  if (!defined('DB_INDEX_DETAIL')) {
    define("DB_INDEX_DETAIL", PREFIX."_index_detail");
    $defines['DB_INDEX_DETAIL'] = "define('DB_INDEX_DETAIL', PREFIX.'_index_detail');";
    $db->query("CREATE TABLE `".DB_INDEX.VERSION."` LIKE `".DB_INDEX."`");
    $db->query("INSERT INTO `".DB_INDEX.VERSION."` SELECT * FROM `".DB_INDEX."`");
    $db->query("CREATE TABLE `".DB_INDEX_DETAIL."` LIKE `".DB_INDEX."`");
    $db->query("INSERT INTO `".DB_INDEX_DETAIL."` SELECT * FROM `".DB_INDEX."`");
    $db->query("ALTER TABLE `".DB_INDEX_DETAIL."` CHANGE `id` `id` INT( 11 ) NOT NULL");
    $db->query("ALTER TABLE `".DB_INDEX_DETAIL."` DROP PRIMARY KEY");
    $db->query("ALTER TABLE `".DB_INDEX_DETAIL."` DROP `index`,DROP `category_id`,DROP `sender`,DROP `member_id`,DROP `email`,DROP `ip`,DROP `create_date`,DROP `last_update`,DROP `visited`,DROP `comments`,DROP `comment_id`,DROP `commentator`,DROP `commentator_id`,DROP `comment_date`,DROP `picture`,DROP `pictureW`,DROP `pictureH`,DROP `hassubpic`,DROP `can_reply`,DROP `published`,DROP `pin`,DROP `locked`,DROP `related`,DROP `alias`,DROP `published_date`");
    $db->query("ALTER TABLE `".DB_INDEX."` DROP `topic`,DROP `description`,DROP `detail`,DROP `keywords`");
    echo '<li class=correct>Created database <strong>'.DB_INDEX_DETAIL.'</strong> <i>complete...</i></li>';
    echo '<li class=correct>Update database <strong>'.DB_INDEX.'</strong> <i>complete...</i></li>';
    ob_flush();
    flush();
  }
  if (defined('DB_GALLERY')) {
    if (!defined('DB_GALLERY_ALBUM')) {
      define("DB_GALLERY_ALBUM", PREFIX."_gallery_album");
      $defines['DB_GALLERY_ALBUM'] = "define('DB_GALLERY_ALBUM', PREFIX.'_gallery_album');";
      $db->query("CREATE TABLE `".DB_CATEGORY.VERSION."` LIKE `".DB_CATEGORY."`");
      $db->query("INSERT INTO `".DB_CATEGORY.VERSION."` SELECT * FROM `".DB_CATEGORY."`");
      $db->query("CREATE TABLE `".DB_GALLERY_ALBUM."` LIKE `".DB_CATEGORY."`");
      $db->query("INSERT INTO `".DB_GALLERY_ALBUM."` SELECT * FROM `".DB_CATEGORY."`");
      $db->query("DELETE FROM `".DB_GALLERY_ALBUM."` WHERE `module_id` NOT IN (SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='gallery')");
      $db->query("DELETE FROM `".DB_CATEGORY."` WHERE `module_id` IN (SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='gallery')");
      $db->query("OPTIMIZE TABLE `".DB_GALLERY."`");
      $db->query("OPTIMIZE TABLE `".DB_GALLERY_ALBUM."`");
      echo '<li class=correct>Created database <strong>'.DB_GALLERY_ALBUM.'</strong> <i>complete...</i></li>';
      echo '<li class=correct>Update database <strong>'.DB_CATEGORY.'</strong> <i>complete...</i></li>';
      ob_flush();
      flush();
    }
    $db->query("ALTER TABLE `".DB_GALLERY_ALBUM."` DROP `id`");
    $db->query("ALTER TABLE `".DB_GALLERY_ALBUM."` CHANGE `category_id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,ADD PRIMARY KEY ( `id` ) ;");
    $db->query("ALTER TABLE `".DB_GALLERY_ALBUM."` CHANGE `icon` `image` VARCHAR(24) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `group_id` `last_update` INT(11) UNSIGNED NOT NULL ,CHANGE `c1` `count` INT(11) UNSIGNED NOT NULL ,CHANGE `c2` `visited` INT(11) UNSIGNED NOT NULL");
    $db->query("ALTER TABLE `".DB_GALLERY_ALBUM."` DROP `language`,DROP `icon_w`,DROP `icon_h`,DROP `config`,DROP `image`");
    $db->query("ALTER TABLE `".DB_GALLERY."` DROP `imageW`,DROP `imageH`,DROP `create_date`,DROP `visited`;");
    $db->query("OPTIMIZE TABLE `".DB_GALLERY_ALBUM."`");
    $db->query("OPTIMIZE TABLE `".DB_GALLERY."`");
    echo '<li class=correct>Update database <strong>'.DB_GALLERY.'</strong> <i>complete...</i></li>';
    ob_flush();
    flush();
  }
  if (!defined('DB_CATEGORY_DETAIL')) {
    define("DB_CATEGORY_DETAIL", PREFIX."_category_detail");
    $defines['DB_CATEGORY_DETAIL'] = "define('DB_CATEGORY_DETAIL', PREFIX.'_category_detail');";
    $db->query("CREATE TABLE `".DB_CATEGORY_DETAIL."` LIKE `".DB_CATEGORY."`");
    $db->query("INSERT INTO `".DB_CATEGORY_DETAIL."` SELECT * FROM `".DB_CATEGORY."`");
    $db->query("UPDATE `".DB_CATEGORY_DETAIL."` SET `category_id`=`id`");
    $db->query("ALTER TABLE `".DB_CATEGORY."` DROP `language`,DROP `topic`,DROP `detail`,DROP `icon`,DROP `icon_w`,DROP `icon_h`");
    $db->query("ALTER TABLE `".DB_CATEGORY_DETAIL."` DROP `group_id`,DROP `c1`,DROP `c2`,DROP `icon_w`,DROP `icon_h`");
    echo '<li class=correct>Created database <strong>'.DB_CATEGORY_DETAIL.'</strong> <i>complete...</i></li>';
    echo '<li class=correct>Update database <strong>'.DB_CATEGORY.'</strong> <i>complete...</i></li>';
    ob_flush();
    flush();
  }
  if (!defined('DB_BOARD_R')) {
    define("DB_BOARD_R", PREFIX."_board_r");
    define("DB_BOARD_Q", PREFIX."_board_q");
    $defines['DB_BOARD_R'] = "define('DB_BOARD_R', PREFIX.'_board_r');";
    $defines['DB_BOARD_Q'] = "define('DB_BOARD_Q', PREFIX.'_board_q');";
    $db->query("CREATE TABLE `".DB_COMMENT.VERSION."` LIKE `".DB_COMMENT."`");
    $db->query("INSERT INTO `".DB_COMMENT.VERSION."` SELECT * FROM `".DB_COMMENT."`");
    $db->query("CREATE TABLE `".DB_BOARD_R."` LIKE `".DB_COMMENT."`");
    $db->query("INSERT INTO `".DB_BOARD_R."` SELECT * FROM `".DB_COMMENT."`");
    $db->query("CREATE TABLE `".DB_BOARD_Q."` LIKE `".DB_INDEX."`");
    $db->query("INSERT INTO `".DB_BOARD_Q."` SELECT * FROM `".DB_INDEX."`");
    $db->query("ALTER TABLE `".DB_BOARD_Q."` ADD `topic` VARCHAR(64) NOT NULL");
    $db->query("ALTER TABLE `".DB_BOARD_Q."` ADD `detail` TEXT NOT NULL");
    $db->query("UPDATE `".DB_BOARD_Q."` AS Q SET `topic`=(SELECT `topic` FROM `".DB_INDEX_DETAIL."` AS D WHERE D.`id`=Q.`id` AND D.`module_id`=Q.`module_id` LIMIT 1)");
    $db->query("UPDATE `".DB_BOARD_Q."` AS Q SET `detail`=(SELECT `detail` FROM `".DB_INDEX_DETAIL."` AS D WHERE D.`id`=Q.`id` AND D.`module_id`=Q.`module_id` LIMIT 1)");
    $db->query("DELETE FROM `".DB_BOARD_R."` WHERE `module_id` NOT IN (SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='board')");
    $db->query("DELETE FROM `".DB_BOARD_Q."` WHERE `index`='1' OR `module_id` NOT IN (SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='board')");
    $db->query("DELETE FROM `".DB_INDEX."` WHERE `module_id` IN (SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='board') AND `index`='0'");
    $db->query("DELETE FROM `".DB_COMMENT."` WHERE `module_id` IN (SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='board')");
    $sql = "SELECT `id` FROM `".DB_INDEX."` WHERE `module_id` IN (SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='board') AND `index`='1'";
    $db->query("DELETE FROM `".DB_INDEX_DETAIL."` WHERE `module_id` IN (SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='board') AND `id` NOT IN ($sql)");
    $db->query("ALTER TABLE `".DB_BOARD_Q."` DROP `alias`,DROP `language`,DROP `published_date`,DROP `index`");
    $db->query("ALTER TABLE `".DB_BOARD_Q."` ORDER BY `id`");
    $db->query("ALTER TABLE `".DB_BOARD_R."` ORDER BY `id`");
    $db->query("OPTIMIZE TABLE `".DB_BOARD_Q."`");
    $db->query("OPTIMIZE TABLE `".DB_BOARD_R."`");
  }
  $db->query("ALTER TABLE `".DB_INDEX."` ORDER BY `id`");
  $db->query("OPTIMIZE TABLE `".DB_INDEX."`");
  $db->query("OPTIMIZE TABLE `".DB_INDEX_DETAIL."`");
  $db->query("OPTIMIZE TABLE `".DB_CATEGORY."`");
  $db->query("OPTIMIZE TABLE `".DB_CATEGORY_DETAIL."`");
  echo '<li class=correct>Optimize database <strong>'.DB_INDEX.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // บันทึกไฟล์ภาษา
  gcms::saveLanguage();
  echo '<li class=correct>Update <strong>languages</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  if (sizeof($defines) > 0) {
    // update vars
    echo '<li class="'.(writeVar($defines) ? 'correct' : 'incorrect').'">Update file <b>vars.php</b> ...</li>';
    ob_flush();
    flush();
  }
  // update config
  $config = array();
  include (ROOT_PATH.'bin/config.php');
  // admin theme
  $config['admin_skin'] = 'v8';
  gcms::saveConfig(ROOT_PATH.'bin/config.php', $config);
  echo '<li class="'.($f ? 'correct' : 'incorrect').'">Update file <b>config.php</b> ...</li>';
}
