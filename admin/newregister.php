<?php
// admin/newregister.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// ตรวจสอบ referer และ admin
if (gcms::isReferer() && gcms::isAdmin()) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // ค่าที่ส่งมา
    $save['email'] = $db->sql_trim_str($_POST['register_email']);
    $password = $db->sql_trim_str($_POST['register_password']);
    $repassword = $db->sql_trim_str($_POST['register_repassword']);
    $save['status'] = (int)$_POST['register_status'];
    // ตรวจสอบข้อมูลที่กรอก
    $error = false;
    $input = false;
    $ret = array();
    // email
    if ($save['email'] == '') {
      $ret['ret_register_email'] = 'EMAIL_EMPTY';
      $input = !$input ? 'register_email' : $input;
      $error = !$error ? 'EMAIL_EMPTY' : $error;
    } else {
      // ตรวจสอบ email
      $sql = "SELECT `id` FROM `".DB_USER."` WHERE `email`='".addslashes($save['email'])."' AND `fb`='0' LIMIT 1";
      $search = $db->customQuery($sql);
      if (sizeof($search) == 1) {
        $ret['ret_register_email'] = 'EMAIL_EXISTS';
        $input = !$error ? 'register_email' : $input;
        $error = !$error ? 'EMAIL_EXISTS' : $error;
      } else {
        $ret['ret_register_email'] = '';
      }
    }
    // password
    if ($password == '') {
      $ret['ret_register_password'] = 'PASSWORD_EMPTY';
      $input = !$input ? 'register_password' : $input;
      $error = !$error ? 'PASSWORD_EMPTY' : $error;
    } elseif (mb_strlen($password) < 4) {
      $ret['ret_register_password'] = 'REGISTER_PASSWORD_SHORT';
      $input = !$input ? 'register_password' : $input;
      $error = !$error ? 'REGISTER_PASSWORD_SHORT' : $error;
    } elseif ($repassword == '') {
      $ret['ret_register_repassword'] = 'REPASSWORD_EMPTY';
      $input = !$input ? 'register_repassword' : $input;
      $error = !$error ? 'REPASSWORD_EMPTY' : $error;
    } elseif ($repassword != $password) {
      $ret['ret_register_repassword'] = 'REPASSWORD_INCORRECT';
      $input = !$input ? 'register_repassword' : $input;
      $error = !$error ? 'REPASSWORD_INCORRECT' : $error;
    } else {
      $ret['ret_register_password'] = '';
      $ret['ret_register_repassword'] = '';
    }
    if (!$error) {
      list($displayname, $domain) = explode('@', $save['email']);
      $save['displayname'] = $displayname;
      $a = 0;
      while (true) {
        if (!$db->basicSearch(DB_USER, 'displayname', $save['displayname'])) {
          break;
        } else {
          $a++;
          $save['displayname'] = $displayname.$a;
        }
      }
      $save['create_date'] = $mmktime;
      $save['subscrib'] = 1;
      $save['password'] = md5($password.$save['email']);
      // เพิ่มสมาชิกใหม่
      $lastid = $db->add(DB_USER, $save);
      // โหลดโมดูลที่ติดตั้ง เพื่อแจ้งการเพิ่มสมาชิกใหม่ให้กับโมดูล
      define('MAIN_INIT', 'new_register');
      $dir = ROOT_PATH.'modules/';
      $f = opendir($dir);
      while (false !== ($owner = readdir($f))) {
        if ($owner != '.' && $owner != '..') {
          if (is_dir($dir.$owner.'/')) {
            if (is_file($dir.$owner.'/add_member.php')) {
              require_once ($dir.$owner.'/add_member.php');
            }
          }
        }
      }
      closedir($f);
      // คืนค่า
      $ret['error'] = 'REGISTER_USER_SUCCESS';
      $ret['location'] = rawurlencode(WEB_URL.'/admin/index.php?module=member&order=0&page=1');
    } else {
      // คืนค่า input ตัวแรกที่ error
      $ret['input'] = $input;
      $ret['error'] = $error;
    }
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
