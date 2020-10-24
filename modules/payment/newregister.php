<?php
// modules/payment/newregister.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer
if (gcms::isReferer()) {
  // ค่าที่ส่งมา
  $password = $db->sql_trim_str($_POST['payment_password']);
  $repassword = $db->sql_trim_str($_POST['payment_repassword']);
  $save['email'] = $db->sql_trim_str($_POST['payment_email']);
  // ตรวจสอบข้อมูลที่กรอก
  $error = false;
  $input = false;
  // email
  if ($save['email'] == '') {
    $ret['ret_payment_email'] = 'EMAIL_EMPTY';
    $input = !$input ? 'payment_email' : $input;
    $error = !$error ? 'EMAIL_EMPTY' : $error;
  } elseif (!gcms::validMail($save['email'])) {
    $ret['ret_payment_email'] = 'REGISTER_INVALID_EMAIL';
    $input = !$input ? 'payment_email' : $input;
    $error = !$error ? 'REGISTER_INVALID_EMAIL' : $error;
  } else {
    // ตรวจสอบ email ซ้ำ
    $sql = "SELECT `id` FROM `".DB_USER."` WHERE `email`='$save[email]' AND `fb`='0' LIMIT 1";
    $search = $db->customQuery($sql);
    if (sizeof($search) == 1) {
      $ret['ret_payment_email'] = 'EMAIL_EXISTS';
      $input = !$input ? 'payment_email' : $input;
      $error = !$error ? 'EMAIL_EXISTS' : $error;
    } else {
      $ret['ret_payment_email'] = '';
    }
  }
  // password
  if ($password == '') {
    $ret['ret_payment_password'] = 'PASSWORD_EMPTY';
    $input = !$input ? 'payment_password' : $input;
    $error = !$error ? 'PASSWORD_EMPTY' : $error;
  } elseif (mb_strlen($password) < 4) {
    $ret['ret_payment_password'] = 'REGISTER_PASSWORD_SHORT';
    $input = !$input ? 'payment_password' : $input;
    $error = !$error ? 'REGISTER_PASSWORD_SHORT' : $error;
  } elseif ($repassword == '') {
    $ret['ret_payment_repassword'] = 'REPASSWORD_EMPTY';
    $input = !$input ? 'payment_repassword' : $input;
    $error = !$error ? 'REPASSWORD_EMPTY' : $error;
  } elseif ($repassword != $password) {
    $ret['ret_payment_repassword'] = 'REPASSWORD_INCORRECT';
    $input = !$input ? 'payment_repassword' : $input;
    $error = !$error ? 'REPASSWORD_INCORRECT' : $error;
  } else {
    $save['password'] = md5($password.$save['email']);
    $ret['ret_payment_password'] = '';
    $ret['ret_payment_repassword'] = '';
  }
  // antispam
  if ($_POST['payment_antispam'] != $_SESSION[$_POST['antispam']]) {
    $ret['ret_payment_antispam'] = 'ANTISPAM_INCORRECT';
    $input = !$input ? 'payment_antispam' : $input;
    $error = !$error ? 'ANTISPAM_INCORRECT' : $error;
  } else {
    $ret['ret_payment_antispam'] = '';
  }
  if (!$error) {
    $save['create_date'] = $mmktime;
    $save['status'] = 0;
    $save['subscrib'] = 1;
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
    // save & login
    $_SESSION['login'] = $save;
    $_SESSION['login']['id'] = $db->add(DB_USER, $save);
    $_SESSION['login']['password'] = $password;
    // clear antispam
    unset($_SESSION[$_POST['antispam']]);
    // reload
    $ret['url'] = rawurlencode(WEB_URL.'/index.php?module=payment-login&ret='.$_POST['module']);
  } else {
    // คืนค่า input ตัวแรกที่ error
    $ret['error'] = $error;
    $ret['input'] = $input;
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
