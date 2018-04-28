<?php
// admin/action.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// ตรวจสอบ referer และ admin
if (gcms::isReferer() && gcms::isAdmin()) {
  // action
  $action = $_POST['action'];
  if ($action == 'clearcache') {
    // เคลียร์แคช
    $cache = new gcmsCache(DATA_PATH.'cache/');
    $errors = $cache->clear();
    if (is_array($errors)) {
      $ret['error'] = 'SOME_FILE_NOT_DELETE';
    } elseif ($errors) {
      $ret['error'] = 'CLEAR_CACHE_COMPLETE';
    }
  } elseif ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    if (preg_match('/(deletemail)_([0-9]+)/', $_POST['data'], $match)) {
      $email = $db->getRec(DB_EMAIL_TEMPLATE, $match[2]);
      if ($email && $email['email_id'] == 0) {
        $db->delete(DB_EMAIL_TEMPLATE, $email['id']);
        // มีการติดตั้ง mailmerge ลบจดหมายที่ส่งแล้ว
        if (defined('DB_MAILMERGE')) {
          $db->query("DELETE FROM `".DB_MAILMERGE."` WHERE `email_id`= '$email[id]'");
        }
        // คืนค่า
        $ret['remove'] = "M_$email[id]";
        $ret['error'] = 'DELETE_SUCCESS';
      } else {
        $ret['error'] = 'ACTION_ERROR';
      }
    } elseif ($action == 'zone' || ($action == 'delete' && $_POST['module'] == 'country')) {
      // ตรวจสอบ id
      $ids = array();
      foreach (explode(',', $_POST['id']) AS $id) {
        $ids[] = (int)$id;
      }
      $ids = implode(',', $ids);
      if ($ids != '') {
        if ($action == 'zone') {
          $db->query("UPDATE `".DB_COUNTRY."` SET `zone`=".(int)$_POST['value']." WHERE `id` IN($ids)");
        } else {
          $db->query("DELETE FROM `".DB_COUNTRY."` WHERE `id` IN($ids)");
        }
      }
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
