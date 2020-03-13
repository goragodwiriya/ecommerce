<?php
// admin/language_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// ตรวจสอบ referer และ แอดมิน
if (gcms::isReferer() && gcms::isAdmin()) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    $save = array();
    if ($_POST['languageadd'] == 1) {
      $name = strtolower($db->sql_trim_str($_POST['lang_name']));
      $copy = $db->sql_trim_str($_POST['lang_copy']);
      $id = $db->sql_trim_str($_POST['save_id']);
      // ตรวจสอบภาษาที่ติดตั้ง
      $install_languages = array();
      $l = array('id', 'key', 'type', 'owner', 'js');
      foreach ($db->customQuery("SHOW FIELDS FROM ".DB_LANGUAGE) AS $item) {
        $install_languages[$item['Field']] = (int)(!in_array($item['Field'], $l));
      }
      // ไอคอน
      $icon = $_FILES['lang_icon'];
      if (mb_strlen($name) != 2 || !preg_match('/^[a-z]{2,2}$/', $name)) {
        // ชื่อภาษาไม่ถูกต้อง
        $ret['error'] = 'LANGUAGE_INVALID';
        $ret['input'] = 'lang_name';
        $ret['ret_lang_name'] = 'LANGUAGE_INVALID';
      } elseif ($id == '' && $icon['tmp_name'] == '') {
        // ใหม่ ไม่พบไอคอน
        $ret['ret_lang_icon'] = 'REQUIRE_PICTURE';
        $ret['input'] = 'lang_icon';
        $ret['error'] = 'REQUIRE_PICTURE';
      } elseif ($icon['tmp_name'] != '') {
        // ตรวจสอบชนิดของไฟล์
        $info = gcms::isValidImage(array('gif'), $icon);
        if (!$info) {
          $ret['ret_lang_icon'] = 'INVALID_FILE_TYPE';
          $ret['input'] = 'lang_icon';
          $ret['error'] = 'INVALID_FILE_TYPE';
        }
      }
      if (!isset($ret['error'])) {
        if ($id == '') {
          // เพิ่มภาษา
          if (isset($install_languages[$name])) {
            // ไม่สามารถใช้ชื่อภาษานี้ได้
            $ret['error'] = $install_languages[$name] == 1 ? 'LANGUAGE_EXISTS' : 'LANGUAGE_ERROR';
            $ret['input'] = 'lang_name';
            $ret['ret_lang_name'] = $ret['error'];
          } else {
            if (@copy($icon['tmp_name'], DATA_PATH."language/$name.gif")) {
              $db->query("ALTER TABLE `".DB_LANGUAGE."` ADD `$name` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
              if ($copy != '' && $install_languages[$copy] == 1) {
                $db->query("UPDATE `".DB_LANGUAGE."` SET `$name`=`$copy`");
              }
              // อ่านไฟล์ภาษาใหม่
              gcms::saveLanguage();
              // คืนค่า
              $ret['error'] = 'SAVE_COMPLETE';
              $ret['location'] = rawurlencode(WEB_URL.'/admin/index.php?module=languages');
            } else {
              $ret['ret_lang_icon'] = 'DO_NOT_UPLOAD';
              $ret['input'] = 'lang_icon';
              $ret['error'] = 'DO_NOT_UPLOAD';
            }
          }
        } else {
          // แก้ไขภาษา
          if (!isset($install_languages[$id]) || $install_languages[$id] !== 1) {
            // ไม่พบภาษาที่แก้ไข
            $ret['error'] = 'ACTION_ERROR';
          } elseif ($name != $id && isset($install_languages[$name])) {
            // ไม่สามารถใช้ชื่อภาษานี้ได้
            $ret['error'] = $install_languages[$id] == 1 ? 'LANGUAGE_EXISTS' : 'LANGUAGE_ERROR';
            $ret['input'] = 'lang_name';
            $ret['ret_lang_name'] = $ret['error'];
          } else {
            if ($icon['tmp_name'] != '' && !@copy($icon['tmp_name'], DATA_PATH."language/$name.gif")) {
              $ret['ret_lang_icon'] = 'DO_NOT_UPLOAD';
              $ret['input'] = 'lang_icon';
              $ret['error'] = 'DO_NOT_UPLOAD';
            }
            if ($name != $id) {
              // แก้ไขชื่อภาษา
              $db->query("ALTER TABLE `".DB_LANGUAGE."` CHANGE `$id` `$name` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
              // อัปเดตไอคอน
              @rename(DATA_PATH."language/$id.gif", DATA_PATH."language/$name.gif");
            }
            // คืนค่า
            $ret['error'] = 'SAVE_COMPLETE';
            $ret['location'] = rawurlencode(WEB_URL.'/admin/index.php?module=languages');
          }
        }
      }
    } elseif ($_POST['languageedit'] == 1) {
      $save['js'] = (int)$_POST['save_js'];
      $save['type'] = $db->sql_trim_str($_POST['save_type']);
      $save['owner'] = $db->sql_trim_str($_POST['save_owner']);
      $save['key'] = $db->sql_trim_str($_POST['save_key']);
      $id = (int)$_POST['save_id'];
      if ($save['key'] == '') {
        $ret['input'] = 'save_key';
        $ret['ret_save_key'] = 'DO_NOT_EMPTY';
        $ret['error'] = 'DO_NOT_EMPTY';
      } else {
        // ตรวจสอบภาษาที่ติดตั้ง
        $language = array();
        foreach ($_POST AS $key => $value) {
          if (preg_match('/^language_([a-z]{2,2})$/', $key, $match)) {
            $language[] = $match[1];
          }
        }
        // ข้อความ
        $save2 = array();
        foreach ($_POST['save_array'] AS $key => $value) {
          $temp = '';
          foreach ($language AS $lng) {
            $save2[$lng]["$value"] = $db->sql_trim($_POST["language_$lng"][$key]);
            $temp .= $save2[$lng]["$value"];
          }
          if ($temp == '') {
            foreach ($language AS $lng) {
              unset($save2[$lng]["$value"]);
            }
          }
        }
        if (sizeof($save2[$lng]) > 1) {
          $save['type'] = 'array';
          $save['js'] = 0;
        }
        // ตรวจสอบคีย์ซ้ำ
        $sql = "SELECT `id` FROM `".DB_LANGUAGE."`";
        $sql .= " WHERE `key`='$save[key]' AND `js`='$save[js]' AND `id`!='$id'";
        $sql .= " LIMIT 1";
        $search = $db->customQuery($sql);
        if (sizeof($search) == 1) {
          $ret['input'] = 'save_key';
          $ret['ret_save_key'] = 'LANGUAGE_KEY_EXISTS';
          $ret['error'] = 'LANGUAGE_KEY_EXISTS';
        } else {
          foreach ($language AS $lng) {
            if ($save['js'] == 1) {
              if ($save['type'] == 'int') {
                $save[$lng] = (int)$save2[$lng][''];
              } else {
                $save[$lng] = trim($save2[$lng]['']);
              }
            } elseif ($save['type'] == 'array') {
              $save[$lng] = serialize($save2[$lng]);
            } elseif ($save['type'] == 'int') {
              $save[$lng] = (int)$save2[$lng][''];
            } else {
              $save[$lng] = $save2[$lng][''];
            }
          }
          // save
          if ($id == 0) {
            $id = $db->add(DB_LANGUAGE, $save);
            $ret['error'] = 'SAVE_COMPLETE';
            $ret['location'] = rawurlencode('index.php?module=language&js='.$save['js']);
          } else {
            $db->edit(DB_LANGUAGE, $id, $save);
            $ret['error'] = 'EDIT_SUCCESS';
            $ret['location'] = 'back';
          }
          // อ่านไฟล์ภาษาใหม่
          gcms::saveLanguage();
        }
      }
    } else {
      $ret['error'] = 'ACTION_ERROR';
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
