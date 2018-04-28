<?php
// widgets/textlink/admin_write_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, admin
if (gcms::isReferer() && gcms::isAdmin()) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    $error = false;
    $ret = array();
    $save = array();
    $save['description'] = $db->sql_trim_str($_POST['textlink_description']);
    $save['type'] = $_POST['textlink_type'].((int)$_POST['textlink_prefix'] == 0 ? '' : (int)$_POST['textlink_prefix']);
    $save['text'] = $db->sql_trim($_POST['textlink_text']);
    $save['url'] = trim($_POST['textlink_url']);
    if (isset($_POST['textlink_template']) && $_POST['textlink_type'] == 'custom') {
      $save['template'] = preg_replace('/<\?(.*?)\?>/', '', trim($_POST['textlink_template']));
    }
    list($y, $m, $d) = explode('-', $_POST['textlink_publish_start']);
    $save['publish_start'] = mktime(0, 0, 0, (int)$m, (int)$d, (int)$y);
    if ($_POST['textlink_dateless'] == 1) {
      $save['publish_end'] = 0;
    } else {
      list($y, $m, $d) = explode('-', $_POST['textlink_publish_end']);
      $save['publish_end'] = mktime(23, 59, 59, (int)$m, (int)$d, (int)$y);
    }
    $id = (int)$_POST['textlink_id'];
    $logo = $_FILES['textlink_file'];
    if ($id > 0) {
      $sql = "SELECT `id` FROM `".DB_TEXTLINK."` WHERE `id`='$id' LIMIT 1";
    } else {
      $sql = "SELECT 1+COALESCE(MAX(`link_order`),0) FROM `".DB_TEXTLINK."`";
      $sql = "SELECT ($sql) AS `link_order`,(1+COALESCE(MAX(`id`),0)) AS `id` FROM `".DB_TEXTLINK."`";
    }
    $textlink = $db->customQuery($sql);
    if (sizeof($textlink) == 0) {
      $ret['error'] = 'ACTION_ERROR';
      $error = true;
    } elseif ($_POST['textlink_type'] != 'custom' && $save['url'] == '') {
      $ret['ret_textlink_url'] = 'this';
      $ret['input'] = 'textlink_url';
      $error = true;
    } elseif ($save['type'] == '') {
      $ret['error'] = 'TEXTLINK_TYPE_EMPTY';
      $ret['input'] = 'textlink_type';
      $error = true;
    } elseif (!preg_match('/^[a-z0-9]{1,}$/u', $save['type'])) {
      $ret['error'] = 'TEXTLINK_TYPE_ERROR';
      $ret['input'] = 'textlink_type';
      $error = true;
    } else {
      $textlink = $textlink[0];
      if ($logo['tmp_name'] != '') {
        // ตรวจสอบไฟล์อัปโหลด
        $info = gcms::isValidImage(array('jpg', 'gif', 'png', 'swf'), $logo);
        if (!$info) {
          $ret['error'] = 'INVALID_FILE_TYPE';
          $error = true;
        } else {
          $save['width'] = $info['width'];
          $save['height'] = $info['height'];
          // ชื่อไฟล์ใหม่
          $save['logo'] = "$textlink[id].$info[ext]";
          if (!@copy($logo['tmp_name'], DATA_PATH.'image/'.$save['logo'])) {
            $ret['error'] = 'DO_NOT_UPLOAD';
            $error = true;
          }
        }
      }
      if (!$error) {
        $save['text'] = preg_replace('/(&lt;br[\s\/]+&gt;)/iu', '<br>', $save['text']);
        if ($id == 0) {
          // ใหม่
          $save['link_order'] = $textlink['link_order'];
          $save['published'] = 1;
          $id = $db->add(DB_TEXTLINK, $save);
        } else {
          // edit
          $db->edit(DB_TEXTLINK, $id, $save);
        }
        // คืนค่า
        $ret['error'] = 'SAVE_COMPLETE';
        $ret['location'] = rawurlencode(WEB_URL.'/admin/index.php?module=textlink-setup&type='.$save['type']);
      }
    }
  }
  // คืนค่า JSON
  echo gcms::array2json($ret);
}
