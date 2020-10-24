<?php
// admin/saveconfig.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// referer, admin
if (gcms::isReferer() && gcms::isAdmin()) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // โหลด config ใหม่
    $config = array();
    if (is_file(CONFIG)) {
      include CONFIG;
    }
    // ตรวจสอบค่าที่ส่งมา
    $error = false;
    $input = false;
    // system.php
    // web_title
    if (isset($_POST['web_title'])) {
      $config['web_title'] = $db->sql_trim($_POST['web_title']);
      if ($config['web_title'] == '') {
        $ret['ret_web_title'] = 'DO_NOT_EMPTY';
        $error = !$error ? 'DO_NOT_EMPTY' : $error;
        $input = !$input ? 'web_title' : $input;
      } else {
        $ret['ret_web_title'] = '';
      }
    }
    // web_description
    if (isset($_POST['web_description'])) {
      $config['web_description'] = htmlspecialchars($db->sql_trim($_POST['web_description']), ENT_QUOTES);
      if ($config['web_description'] == '') {
        $ret['ret_web_description'] = 'DO_NOT_EMPTY';
        $error = !$error ? 'DO_NOT_EMPTY' : $error;
        $input = !$input ? 'web_description' : $input;
      } else {
        $ret['ret_web_description'] = '';
      }
    }
    // usericon
    if (isset($_POST['user_icon_w'])) {
      // user_icon_w
      $config['user_icon_w'] = max(16, (int)$_POST['user_icon_w']);
      // user_icon_h
      $config['user_icon_h'] = max(16, (int)$_POST['user_icon_h']);
      if (!isset($_POST['user_icon_typies'])) {
        $ret['ret_user_icon_typies'] = 'PLEASE_SELECT_ONE';
        $error = !$error ? 'PLEASE_SELECT_ONE' : $error;
        $input = !$input ? 'user_icon_typies' : $input;
      } else {
        $ret['ret_user_icon_typies'] = '';
        $config['user_icon_typies'] = $_POST['user_icon_typies'];
      }
      $ret['user_icon_w'] = $config['user_icon_w'];
      $ret['user_icon_h'] = $config['user_icon_h'];
    }
    // 0 or 1
    $keys = array('member_only_ip', 'login_action', 'user_activate', 'use_ajax', 'module_url', 'member_invitation', 'sendmail', 'user_activate', 'email_use_phpMailer', 'email_SMTPAuth', 'cron', 'member_login_phone', 'demo_mode');
    foreach ($keys AS $key) {
      if (isset($_POST[$key])) {
        $config[$key] = (int)$_POST[$key] == 0 ? 0 : 1;
      }
    }
    // numeric
    $keys = array('hour', 'counter_digit', 'member_phone', 'member_idcard');
    foreach ($keys AS $key) {
      if (isset($_POST[$key])) {
        $config[$key] = (int)$_POST[$key];
      }
    }
    // noreply_email
    if (isset($_POST['noreply_email'])) {
      $config['noreply_email'] = $db->sql_trim($_POST['noreply_email']);
      if ($config['noreply_email'] == '') {
        $ret['ret_noreply_email'] = 'DO_NOT_EMPTY';
        $error = !$error ? 'DO_NOT_EMPTY' : $error;
        $input = !$input ? 'noreply_email' : $input;
      } elseif (!gcms::validMail($config['noreply_email'])) {
        $ret['ret_noreply_email'] = 'REGISTER_INVALID_EMAIL';
        $error = !$error ? 'REGISTER_INVALID_EMAIL' : $error;
        $input = !$input ? 'noreply_email' : $input;
      } else {
        $ret['ret_noreply_email'] = '';
      }
    }
    // email_charset
    if (isset($_POST['email_charset'])) {
      $config['email_charset'] = strtolower(trim($_POST['email_charset']));
      $config['email_charset'] = $config['email_charset'] == '' ? 'tis-620' : $config['email_charset'];
      $ret['email_charset'] = $config['email_charset'];
    }
    // email_Port
    if (isset($_POST['email_Port'])) {
      $config['email_Port'] = (int)$_POST['email_Port'];
      $config['email_Port'] = $config['email_Port'] < 1 ? 25 : $config['email_Port'];
      $ret['email_Port'] = $config['email_Port'];
    }
    // email_SMTPSecure
    if (isset($_POST['email_SMTPSecure'])) {
      $config['email_SMTPSecure'] = $_POST['email_SMTPSecure'] !== 'ssl' ? '' : 'ssl';
    }
    // email_Username
    if (isset($_POST['email_Username'])) {
      $config['email_Username'] = $db->sql_trim($_POST['email_Username']);
    }
    // email_Password
    if (isset($_POST['email_Password'])) {
      $password = $db->sql_trim($_POST['email_Password']);
      if ($password != '') {
        $config['email_Password'] = $password;
      }
    }
    // email_Host
    if (isset($_POST['email_Host'])) {
      $config['email_Host'] = $db->sql_trim($_POST['email_Host']);
      if ($config['email_Host'] == '') {
        $config['email_Host'] = 'localhost';
        $config['email_Port'] = 25;
        $config['email_SMTPSecure'] = '';
        $config['email_Username'] = '';
        $config['email_Password'] = '';
        $ret['email_Host'] = $config['email_Host'];
        $ret['email_Port'] = $config['email_Port'];
        $ret['email_SMTPSecure'] = $config['email_SMTPSecure'];
      }
    }
    // other.php
    // member_reserv
    if (isset($_POST['member_reserv'])) {
      $config['member_reserv'] = array();
      foreach (explode("\n", $_POST['member_reserv']) AS $row) {
        $row = $db->sql_trim($row);
        if ($row !== '') {
          $config['member_reserv'][] = $row;
        }
      }
    }
    // wordrude
    if (isset($_POST['wordrude'])) {
      $config['wordrude'] = array();
      $wordrude = explode("\n", stripslashes($_POST['wordrude']));
      foreach ($wordrude AS $row) {
        $row = $db->sql_trim($row);
        if ($row !== '') {
          $config['wordrude'][] = $row;
        }
      }
    }
    // wordrude_replace
    if (isset($_POST['wordrude_replace'])) {
      $config['wordrude_replace'] = $db->sql_trim($_POST['wordrude_replace']);
      $config['wordrude_replace'] = $config['wordrude_replace'] == '' ? 'xxx' : $config['wordrude_replace'];
      $ret['wordrude_replace'] = $config['wordrude_replace'];
    }
    // index_page_cache
    if (isset($_POST['index_page_cache'])) {
      $config['index_page_cache'] = min(999, max(0, (int)$_POST['index_page_cache']));
      $ret['index_page_cache'] = $config['index_page_cache'];
    }
    // user_agent
    if (isset($_POST['user_agent'])) {
      $config['user_agent'] = array();
      $user_agent = explode("\n", strtolower(stripslashes($_POST['user_agent'])));
      foreach ($user_agent AS $row) {
        $row = $db->sql_trim($row);
        if ($row !== '') {
          $config['user_agent'][] = $row;
        }
      }
    }
    // fb_appid
    if (isset($_POST['facebook_appId'])) {
      $config['facebook']['appId'] = $db->sql_trim_str($_POST['facebook_appId']);
    }
    // facebook_picture
    if (isset($_FILES['facebook_picture'])) {
      $facebook_picture = $_FILES['facebook_picture'];
      if ($facebook_picture['tmp_name'] !== '') {
        // ตรวจสอบไฟล์อัปโหลด
        $info = gcms::isValidImage(array('jpg'), $facebook_picture);
        if (!$info) {
          $ret['ret_facebook_picture'] = 'INVALID_FILE_TYPE';
          $error = 'INVALID_FILE_TYPE';
        } else {
          if (@copy($facebook_picture['tmp_name'], DATA_PATH."image/facebook_photo.jpg")) {
            $ret['fbPicture'] = DATA_URL."/image/facebook_photo.jpg?$mmktime";
            $ret['ret_facebook_picture'] = '';
          } else {
            $ret['ret_facebook_picture'] = 'DO_NOT_UPLOAD';
            $error = 'DO_NOT_UPLOAD';
          }
        }
      }
    }
    // string
    $keys = array('google_site_verification', 'google_profile', 'msvalidate', 'ftp_root');
    foreach ($keys AS $key) {
      if (isset($_POST[$key])) {
        $config[$key] = $db->sql_trim_str($_POST[$key]);
      }
    }
    // logo
    if (isset($_POST['delete_logo'])) {
      $ret['logoFile'] = '';
      unset($config['logo']);
      if (is_file(DATA_PATH."image/$config[logo]")) {
        @unlink(DATA_PATH."image/$config[logo]");
      }
    } elseif (isset($_FILES['logo'])) {
      $logo = $_FILES['logo'];
      if ($logo['tmp_name'] != '') {
        // ตรวจสอบไฟล์อัปโหลด
        $info = gcms::isValidImage(array('jpg', 'gif', 'png', 'swf'), $logo);
        if (!$info) {
          $ret['ret_input_logo'] = 'INVALID_FILE_TYPE';
          $error = 'INVALID_FILE_TYPE';
        } else {
          if (@copy($logo['tmp_name'], DATA_PATH."image/logo.$info[ext]")) {
            $ret['ret_input_logo'] = '';
            $config['logo'] = "logo.$info[ext]";
          } else {
            $ret['ret_input_logo'] = 'DO_NOT_UPLOAD';
            $error = 'DO_NOT_UPLOAD';
          }
        }
      }
    }
    // bg image
    if (isset($_POST['delete_bg_image'])) {
      $ret['bgImageFile'] = '';
      unset($config['bg_image']);
      if (is_file(DATA_PATH."image/$config[bg_image]")) {
        @unlink(DATA_PATH."image/$config[bg_image]");
      }
    } elseif (isset($_FILES['bg_image'])) {
      $bg_image = $_FILES['bg_image'];
      if ($bg_image['tmp_name'] !== '') {
        // ตรวจสอบไฟล์อัปโหลด
        $info = gcms::isValidImage(array('jpg', 'gif', 'png'), $bg_image);
        if (!$info) {
          $ret['ret_bg_image'] = 'INVALID_FILE_TYPE';
          $error = 'INVALID_FILE_TYPE';
        } else {
          if (@copy($bg_image['tmp_name'], DATA_PATH."image/bg_image.$info[ext]")) {
            $ret['bgImageFile'] = "bg_image.$info[ext]";
            $ret['ret_bg_image'] = '';
            $config['bg_image'] = "bg_image.$info[ext]";
          } else {
            $ret['ret_bg_image'] = 'DO_NOT_UPLOAD';
            $error = 'DO_NOT_UPLOAD';
          }
        }
      }
    }
    // bg color
    if (isset($_POST['delete_bg_color'])) {
      unset($config['bg_color']);
      $ret['bg_color'] = '';
    } elseif (isset($_POST['bg_color'])) {
      $bgcolor = strtoupper(trim($_POST['bg_color']));
      if ($bgcolor == '' || $bgcolor == 'TRANSPARENT') {
        unset($config['bg_color']);
      } else {
        $config['bg_color'] = $bgcolor;
      }
    }
    // ftp_host
    if (isset($_POST['ftp_host'])) {
      $config['ftp_host'] = $db->sql_trim($_POST['ftp_host']);
      $ret['ftp_host'] = rawurlencode($config['ftp_host']);
    }
    // ftp_port
    if (isset($_POST['ftp_port'])) {
      $config['ftp_port'] = (int)$_POST['ftp_port'];
      $config['ftp_port'] = $config['ftp_port'] == 0 ? 21 : $config['ftp_port'];
      $ret['ftp_port'] = rawurlencode($config['ftp_port']);
    }
    // ftp_username,ftp_password
    if (isset($_POST['ftp_username'])) {
      if ($config['ftp_host'] == '') {
        $config['ftp_username'] = '';
        $config['ftp_password'] = '';
      } else {
        $username = $db->sql_trim($_POST['ftp_username']);
        $password = $db->sql_trim($_POST['ftp_password']);
        if ($username != '' || $password != '') {
          if ($password == '') {
            $error = 'PASSWORD_EMPTY';
            $input = 'ftp_password';
            $ret['ret_ftp_password'] = 'PASSWORD_EMPTY';
          } elseif ($username == '') {
            $error = 'USERNAME_EMPTY';
            $input = 'ftp_username';
            $ret['ret_ftp_username'] = 'USERNAME_EMPTY';
          } else {
            $ret['ret_db_username'] = '';
            $ret['ret_db_password'] = '';
            $config['ftp_username'] = $username;
            $config['ftp_password'] = $password;
          }
        }
      }
    }
    // save config.php
    if (!$error) {
      if (gcms::saveConfig(CONFIG, $config)) {
        $ret['error'] = 'SAVE_COMPLETE';
        $ret['location'] = 'reload';
      } else {
        $ret['error'] = 'DO_NOT_SAVE';
      }
    } else {
      // error
      if ($input != '') {
        $ret['input'] = $input;
      }
      $ret['error'] = $error;
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
