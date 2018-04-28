<?php
// modules/payment/chklogin.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer
if (gcms::isReferer()) {
  // ค่าที่ส่งมา
  $email = $db->sql_trim_str($_POST['payment_login_email']);
  $password = $db->sql_trim_str($_POST['payment_login_password']);
  $error = false;
  $ret = array();
  $ret['ret_payment_login_email'] = '';
  $ret['ret_payment_login_password'] = '';
  // ตรวจสอบการกรอก
  if ($email == '') {
    $error = 'EMAIL_EMPTY';
    $input = 'payment_login_email';
    $ret['ret_payment_login_email'] = $error;
  } elseif ($password == '') {
    $error = 'PASSWORD_EMPTY';
    $input = 'payment_login_password';
    $ret['ret_payment_login_password'] = $error;
  } else {
    // ตรวจสอบการ login
    $sql = "SELECT * FROM `".DB_USER."` WHERE (`email`='$email' OR (`phone1`!='' AND `phone1`='$email')) AND `fb`='0' LIMIT 1";
    $login_result = $db->customQuery($sql);
    if (sizeof($login_result) == 1 && $login_result[0]['password'] == md5($password.$login_result[0]['email'])) {
      // login สำเร็จ
      $_SESSION['login'] = $login_result[0];
      $_SESSION['login']['password'] = $password;
      // reload
      $ret['location'] = rawurlencode(WEB_URL."/index.php?module=payment-login&ret=$_POST[module]&$mmktime");
    } else {
      $error = 'EMAIL_OR_PASSWORD_INCORRECT';
      $input = sizeof($login_result) == 1 ? 'payment_login_password' : 'payment_login_email';
      $ret['ret_'.$input] = $error;
    }
  }
  if ($error) {
    // error
    $ret['error'] = $error;
    $ret['input'] = $input;
  }
  // คืนค่าเป็น JSON
  echo gcms::array2json($ret);
}
