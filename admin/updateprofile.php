<?php
// admin/updateprofile.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// ตรวจสอบ referer และ admin
if (gcms::isReferer() && gcms::isMember()) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // แอดมินสูงสุด สามารถแก้ไขข้อมูลสมาชิกได้ทุกรายการ
    $isAdmin = gcms::isAdmin();
    // ค่าที่ส่งมา
    $password = $db->sql_trim_str($_POST['register_password']);
    $repassword = $db->sql_trim_str($_POST['register_repassword']);
    $save['sex'] = trim($_POST['register_sex']);
    $save['company'] = $db->sql_trim_str($_POST['register_company']);
    $save['pname'] = $db->sql_trim_str($_POST['register_pname']);
    $save['fname'] = $db->sql_trim_str($_POST['register_fname']);
    $save['lname'] = $db->sql_trim_str($_POST['register_lname']);
    $save['address1'] = $db->sql_trim_str($_POST['register_address1']);
    $save['address2'] = $db->sql_trim_str($_POST['register_address2']);
    $save['provinceID'] = $db->sql_trim_str($_POST['register_provinceID']);
    $save['province'] = $db->sql_trim_str($_POST['register_province']);
    $save['zipcode'] = $db->sql_trim_str($_POST['register_zipcode']);
    $save['country'] = $db->sql_trim_str($_POST['register_country']);
    $save['phone1'] = $db->sql_trim_str($_POST['register_phone1']);
    $save['phone2'] = $db->sql_trim_str($_POST['register_phone2']);
    $save['birthday'] = $db->sql_trim_str($_POST['register_birthday']);
    $save['subscrib'] = $_POST['register_subscrib'] == 1 ? 1 : 0;
    $save['admin_access'] = $_POST['register_admin_access'] == 1 ? 1 : 0;
    $save['status'] = (int)$_POST['register_status'];
    $id = (int)$_POST['register_id'];
    // ตรวจสอบข้อมูลที่กรอก
    $error = false;
    $input = false;
    $ret = array();
    if ($id > 0) {
      // ตรวจสอบ id
      $user = $db->getRec(DB_USER, $id);
    }
    if ($id > 0 && !$user) {
      // ไม่พบสมาชิกที่เลือก
      $ret['error'] = 'ID_NOT_FOUND';
    } else {
      if ($user['fb'] == 0) {
        if ($isAdmin) {
          $save['email'] = $db->sql_trim_str($_POST['register_email']);
          if (isset($_POST['register_point'])) {
            $save['point'] = (int)$_POST['register_point'];
          }
          // email
          if ($save['email'] == '') {
            $ret['ret_register_email'] = 'EMAIL_EMPTY';
            $input = !$input ? 'register_email' : $input;
            $error = !$error ? 'EMAIL_EMPTY' : $error;
          } else {
            // ตรวจสอบ email ซ้ำ
            $sql = "SELECT `id` FROM `".DB_USER."` WHERE `email`='".addslashes($save['email'])."' AND `fb`='0' LIMIT 1";
            $search = $db->customQuery($sql);
            if (sizeof($search) == 1 && $user['id'] != $search[0]['id']) {
              $ret['ret_register_email'] = 'EMAIL_EXISTS';
              $input = !$error ? 'register_email' : $input;
              $error = !$error ? 'EMAIL_EXISTS' : $error;
            } else {
              $ret['ret_register_email'] = '';
            }
          }
        } else {
          // ไม่ใช้ admin ใช้ email เดิม
          $save['email'] = $user['email'];
        }
        // password
        if ($password != '' || $repassword != '') {
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
        }
        // มีการเปลี่ยน email ต้องการรหัสผ่าน
        if (!$error && $password == '' && $user['email'] != $save['email']) {
          $ret['ret_register_password'] = 'PASSWORD_EMPTY';
          $ret['ret_register_repassword'] = 'REPASSWORD_INCORRECT';
          $input = !$input ? 'register_password' : $input;
          $error = !$error ? 'PASSWORD_EMPTY' : $error;
        } elseif ($password != '') {
          $save['password'] = md5($password.$save['email']);
        }
      }
      // phone
      if ($save['phone1'] != '') {
        if (!preg_match('/[0-9]{9,10}/', $save['phone1'])) {
          $ret['ret_register_phone1'] = 'INVALID_PHONE_NUMBER';
          $input = !$input ? 'register_phone1' : $input;
          $error = !$error ? 'INVALID_PHONE_NUMBER' : $error;
        } else {
          // ตรวจสอบ phone ซ้ำ
          $sql = "SELECT `id` FROM `".DB_USER."` WHERE `phone1`='$save[phone1]' LIMIT 1";
          $search = $db->customQuery($sql);
          if (sizeof($search) == 1 && $user['id'] != $search[0]['id']) {
            $ret['ret_register_phone1'] = 'PHONE_EXISTS';
            $input = !$input ? 'register_phone1' : $input;
            $error = !$error ? 'PHONE_EXISTS' : $error;
          } else {
            $ret['ret_register_phone1'] = '';
          }
        }
      }
      // displayname
      if (isset($_POST['register_displayname'])) {
        $save['displayname'] = $db->sql_trim_str($_POST['register_displayname']);
        if ($save['displayname'] != '') {
          // ตรวจสอบ displayname
          $sql = "SELECT `id` FROM `".DB_USER."` WHERE `displayname`='".addslashes($save['displayname'])."' LIMIT 1";
          $search = $db->customQuery($sql);
          if (sizeof($search) == 1 && $user['id'] != $search[0]['id']) {
            $ret['ret_register_displayname'] = 'NAME_EXISTS';
            $input = !$input ? 'register_displayname' : $input;
            $error = !$error ? 'NAME_EXISTS' : $error;
          } else {
            $ret['ret_register_displayname'] = '';
          }
        }
      }
      // website
      if (isset($_POST['register_website'])) {
        $save['website'] = trim($_POST['register_website']);
        if ($save['website'] != '') {
          $save['website'] = str_replace(array('http://', 'https://', 'ftp://'), array('', '', ''), $save['website']);
          $patt = '!^(\.?([a-z0-9-]+))+\.[a-z]{2,6}(:[0-9]{1,5})?(/[a-zA-Z0-9.,;\?|\'+&%\$#=~_-]+)*$!i';
          if ($save['website'] != '' && !preg_match($patt, $save['website'])) {
            $ret['ret_register_website'] = 'REGISTER_INVALID_WEBSITE';
            $input = !$input ? 'register_website' : $input;
            $error = !$error ? 'REGISTER_INVALID_WEBSITE' : $error;
          } else {
            $ret['ret_register_website'] = '';
          }
        }
      }
      // ตรวจสอบรูปภาพอัปโหลดสมาชิก
      $register_usericon = $_FILES['register_usericon'];
      if ($register_usericon['tmp_name'] != '') {
        // ตรวจสอบไฟล์อัปโหลด
        $info = gcms::isValidImage($config['user_icon_typies'], $register_usericon);
        if (!$info) {
          $ret['ret_register_usericon'] = 'INVALID_FILE_TYPE';
          $input = !$input ? 'register_usericon' : $input;
          $error = !$error ? 'INVALID_FILE_TYPE' : $error;
        } else {
          if ($user['icon'] != '') {
            @unlink(USERICON_FULLPATH.$user['icon']);
          }
          // สร้างรูป thumbnail
          if ($info['width'] == $config['user_icon_w'] && $info['height'] == $config['user_icon_h']) {
            $save['icon'] = "$user[id].$info[ext]";
            if (!@move_uploaded_file($register_usericon['tmp_name'], USERICON_FULLPATH.$save['icon'])) {
              $ret['ret_register_usericon'] = 'DO_NOT_UPLOAD';
              $input = !$input ? 'register_usericon' : $input;
              $error = !$error ? 'DO_NOT_UPLOAD' : $error;
            }
          } else {
            // ปรับภาพตามขนาดที่กำหนด
            $save['icon'] = "$user[id].jpg";
            if (!gcms::cropImage($register_usericon['tmp_name'], USERICON_FULLPATH.$save['icon'], $info, $config['user_icon_w'], $config['user_icon_h'])) {
              $ret['ret_register_usericon'] = 'DO_NOT_UPLOAD';
              $input = !$input ? 'register_usericon' : $input;
              $error = !$error ? 'DO_NOT_UPLOAD' : $error;
            }
          }
          if (!$error) {
            // คืนค่า url ของรูปใหม่
            $ret['imgIcon'] = rawurlencode(WEB_URL.'/modules/member/usericon.php?w=70&id='.$user['id'].'&'.$mmktime);
            $ret['ret_register_usericon'] = '';
          }
        }
      }
      if (!$error) {
        if (!$isAdmin) {
          // ไม่ใช่แอดมิน ห้ามแก้ไข email,status,point
          unset($save['email']);
          unset($save['status']);
          unset($save['point']);
        }
        if ($user['fb'] != 0) {
          // social ห้ามแก้ไข email,status,password
          unset($save['email']);
          unset($save['status']);
          unset($save['password']);
        }
        if ($_SESSION['login']['id'] == $user['id'] && $save['password'] != '') {
          // เปลี่ยน password ที่ login ใหม่
          $_SESSION['login'] = $save;
          $_SESSION['login']['password'] = $password;
        }
        if ($id == 0) {
          // ใหม่
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
          $db->add(DB_USER, $save);
          // คืนค่า
          $ret['error'] = 'REGISTER_USER_SUCCESS';
          $ret['location'] = rawurlencode(WEB_URL.'/admin/index.php?module=member&order=0&page=1');
        } else {
          // แก้ไข
          if ($user['id'] == 1) {
            $save['admin_access'] = 1;
          }
          $db->edit(DB_USER, $user['id'], $save);
          // คืนค่า
          $ret['error'] = 'REGISTER_UPDATE_SUCCESS';
          $ret['location'] = 'back';
        }
      } else {
        // คืนค่า input ตัวแรกที่ error
        if ($input != '') {
          $ret['input'] = $input;
        }
        $ret['error'] = $error;
      }
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
