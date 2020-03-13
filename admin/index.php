<?php
// admin/index.php
// inint
include '../bin/inint.php';
if (is_file('./install/index.php')) {
  // install
  include ROOT_PATH.'admin/install/index.php';
} else {
  // inline javascript
  $script = array();
  // เมนู
  $menus = array();
  // ภาษาที่ติดตั้ง
  $install_languages = array();
  // ไฟล์ css
  $stylesheet = array();
  // ไฟล์ javascript
  $javascript = array();
  // inline css
  $css = array();
  // colors
  $colors = array('#7E57C2', '#FF5722', '#E91E63', '#259B24', '#607D8B', '#2CB6D5', '#FD971F', '#26A694', '#FF5722', '#00BCD4', '#8BC34A', '#616161', '#FFD54F', '#03A9F4', '#795548');
  foreach ($colors AS $l => $c) {
    $css[] = ".bg-$l{background-color:$c}";
    $css[] = ".bdr-$l{border-color:$c}";
    $css[] = ".c-$l{color:$c}";
  }
  // รายชือเมนูที่สามารถใช้งานได้
  $module_menus = array();
  // dashboard menu
  $dashboard_menus = array();
  // font size
  $fontSize = (int)$_COOKIE['fontSize'];
  $fontSize = $fontSize == 0 ? 12 : $fontSize;
  $fontSize = max(8, min(18, $fontSize));
  $css[] = 'body{font-size:'.$fontSize.'px}';
  $css[] = 'body.cke-body{font-size:'.$fontSize.'px}';
  // query จาก URL ที่ส่งมา
  $url_query = array();
  foreach ($_GET AS $key => $value) {
    if ($key == 'spage') {
      $url_query['page'] = $value;
    } elseif (!in_array($key, array('count', 'src', 'page', 'lang', 'action', 'owner', 'x', 'y')) && $value != '') {
      $url_query[$key] = $value;
    }
  }
  // ภาษาที่ติดตั้ง
  $languages = array();
  $l = array('id', 'key', 'type', 'owner', 'js');
  foreach ($db->customQuery("SHOW FIELDS FROM ".DB_LANGUAGE) AS $item) {
    if (!in_array($item['Field'], $l)) {
      $install_languages[] = $item['Field'];
      $languages[] = '<a href="{URLQUERY?lang='.$item['Field'].'}" title="{LNG_LANGUAGE} '.strtoupper($item['Field']).'" style="background-image:url('.DATA_URL.'language/'.$item['Field'].'.gif)" tabindex=1>&nbsp;</a>';
    }
  }
  // ภาษา js
  $javascript[] = '<script src='.DATA_URL.'/language/'.LANGUAGE.'.js></script>';
  // javscript ของ widget
  $install_widgets = array();
  $dir = ROOT_PATH.'widgets/';
  $f = @opendir($dir);
  if ($f) {
    while (false !== ($text = readdir($f))) {
      if ($text != '.' && $text != '..') {
        $install_widgets[] = $text;
        if (is_file($dir.$text.'/admin.js')) {
          $javascript[] = '<script src='.WEB_URL.'/widgets/'.$text.'/admin.js></script>';
        }
      }
    }
    closedir($f);
  }
  // ค่าที่ส่งมา
  $action = $_GET['action'];
  if ($action == 'recover') {
    // ขอรหัสผ่านใหม่
    $forgot_email = $db->sql_trim_str($_POST['email']);
    if ($forgot_email == '') {
      $message_type = 'error';
      $message = "$lng[LNG_PLEASE_FILL] $lng[LNG_EMAIL]";
      $action = 'forgot';
    } else {
      // ส่งอีเมลขอรหัสผ่านใหม่
      $sql = "SELECT * FROM `".DB_USER."` WHERE (`email`='$forgot_email' OR `phone1`='$forgot_email') AND `fb`='0' LIMIT 1";
      $user = $db->customQuery($sql);
      if (sizeof($user) == 1) {
        // สุ่มและอัปเดตรหัสผ่านใหม่
        $password = gcms::rndname(6);
        $save['password'] = md5($password.$user[0]['email']);
        $db->edit(DB_USER, $user[0]['id'], $save);
        // ส่งเมล์แจ้งสมาชิก
        $replace = array();
        $replace['/%PASSWORD%/'] = $password;
        $replace['/%EMAIL%/'] = $user[0]['email'];
        if ($user['activatecode'] != '') {
          $replace['/%ID%/'] = $user['activatecode'];
          // send mail
          $error = gcms::sendMail(1, 'member', $replace, $user[0]['email']);
        } else {
          // send mail
          $error = gcms::sendMail(3, 'member', $replace, $user[0]['email']);
        }
        if ($error == '') {
          $message_type = 'message';
          $message = sprintf($lng['FORGOT_SUCCESS'], $user[0]['email']);
        } else {
          $message_type = 'error';
          $message = $error;
        }
      } else {
        $message_type = 'error';
        $message = $lng['LNG_EMAIL_NOT_FOUND'];
        $action = 'forgot';
      }
    }
  } elseif ($action == 'logout') {
    // logout
    $login_email = '';
    $login_password = '';
    $login_remember = 0;
    $message_type = 'message';
    $message = $lng['LOGOUT_SUCCESS'];
    // ลบ cookie และ session
    unset($_SESSION['login']);
    setCookie(PREFIX.'_login_email', '', time());
    setCookie(PREFIX.'_login_password', '', time());
    setCookie(PREFIX.'_login_remember', '', time());
  } elseif (isset($_POST['email'])) {
    // มาจากการ login
    $login_email = $db->sql_trim_str($_POST['email']);
    $login_password = $db->sql_trim_str($_POST['password']);
    $login_remember = (int)$_POST['remember'];
    if ($login_email == '') {
      $message_type = 'error';
      $message = "$lng[LNG_PLEASE_FILL] $lng[LNG_EMAIL]";
    } elseif ($login_password == '') {
      $message_type = 'error';
      $message = "$lng[LNG_PLEASE_FILL] $lng[LNG_PASSWORD]";
    }
  } elseif (isset($_SESSION['login'])) {
    // login อยู่ก่อนแล้ว
    $login_email = $_SESSION['login']['email'];
    $login_password = $_SESSION['login']['password'];
  } else {
    // เข้ามาครั้งแรก อ่านจาก cookie
    $login_email = gcms::decode($_COOKIE[PREFIX.'_login_email']);
    $login_password = gcms::decode($_COOKIE[PREFIX.'_login_password']);
    $login_remember = (int)$_COOKIE[PREFIX.'_login_remember'];
  }
  // ตรวจสอบการ login
  $login_success = false;
  if ($login_email != '' && $login_password != '') {
    $login_result = gcms::CheckLogin($login_email, $login_password);
    $login_success = is_array($login_result) && ($login_result['status'] == 1 || $login_result['admin_access'] == 1);
    if (!$login_success && $action == 'login') {
      $login_result = is_array($login_result) ? 5 : $login_result;
      $message_type = 'error';
      // ข้อความผิดพลาด
      $error = array();
      $error[] = $lng['LNG_MEMBER_NOT_FOUND'];
      $error[] = $lng['LNG_MEMBER_NO_ACTIVATE'];
      $error[] = $lng['LNG_MEMBER_BAN'];
      $error[] = $lng['LNG_PASSWORD_INCORRECT'];
      $error[] = $lng['LNG_MEMBER_LOGIN_EXISTS'];
      $error[] = $lng['ACTION_FORBIDDEN'];
      $input = $login_result == 3 ? 'login_password' : 'login_email';
      $message = strip_tags($error[$login_result]);
    }
  }
  if ($login_success) {
    // บันทึกการ login ลง cookie และ session
    $_SESSION['login'] = $login_result;
    $_SESSION['login']['password'] = $login_password;
    if ($login_remember == 1) {
      setCookie(PREFIX.'_login_email', gcms::encode($login_result['email']), time() + 3600 * 24 * 365, '/');
      setCookie(PREFIX.'_login_password', gcms::encode($login_password), time() + 3600 * 24 * 365, '/');
      setCookie(PREFIX.'_login_remember', $login_remember, time() + 3600 * 24 * 365, '/');
    }
  } else {
    // ลบ cookie และ session
    unset($_SESSION['login']);
    setCookie(PREFIX.'_login_email', '', time(), '/');
    setCookie(PREFIX.'_login_password', '', time(), '/');
  }
  if ($login_success) {
    // เข้าระบบเรียบร้อย
    // title
    $title = $config['web_title'];
    // สถานะของ member และ admin
    $isMember = gcms::isMember();
    $isAdmin = gcms::isAdmin();
    $canAdmin = $_SESSION['login']['admin_access'] == 1;
    // ป้องกันการเรียกหน้าเพจโดยตรง
    DEFINE('MAIN_INIT', 'admin');
    $content = array();
    list($main_header, $main_footer) = explode('{CONTENT}', gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/main.html"));
    $content[] = $main_header;
    // โหลดหน้าแอดมิน
    include ('main.php');
    $content[] = $main_footer;
  } elseif ($action == 'forgot') {
    // title
    $title = $lng['LNG_FORGOT_TITLE'];
    // forgot form
    $content[] = gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/forgot.html");
  } else {
    if ($config['demo_mode'] === 1 && $login_email == '' && $login_password == '') {
      $login_email = 'demo';
      $login_password = 'demo';
    }
    // title
    $title = $lng['LNG_ADMIN_TITLE'];
    // login form
    $content[] = gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/login.html");
  }
  // web url ใช้ตาม addressbar
  preg_match('/^(http(s)?:\/\/)(.*)(\/(.*))?$/U', WEB_URL, $match);
  $script[] = "window.WEB_URL = '$match[1]' + window.location.hostname + '$match[4]/';";
  $script[] = "window.SKIN = '".SKIN."';";
  // สีของสมาชิก
  if (is_array($config['color_status'])) {
    foreach ($config['color_status'] AS $i => $item) {
      $css[] = "html > body .status$i{color:$item !important}";
    }
  }
  // แสดงผล
  $replace = array();
  $patt = array('/{CONTENT}/', '/{MENUS}/', '/{LANGUAGES}/', '/{MSG}/', '/{MESSAGE}/', '/{STYLESHEET}/',
    '/{JAVASCRIPT}/', '/{SCRIPT}/', '/{CSS}/', '/{TITLE}/', '/{WEBTITLE}/', '/{LOGINNAME}/', '/{LOGINID}/',
    '/{(LNG_[A-Z0-9_]+)}/e', '/{VERSION}/', '/{LANGUAGE}/', '/{SKIN}/', '/{WEBURL}/', '/{EMAIL}/',
    '/{PASSWORD}/', '/{DATAURL}/', '/{SRC}/', '/{REMEMBER}/', '/{LOGINEMAIL}/', '/{LOGINPASSWORD}/');
  $replace[] = implode("\n", $content);
  $replace[] = implode('', $menus);
  $replace[] = implode('', $languages);
  $replace[] = $message_type == '' ? 'hidden' : $message_type;
  $replace[] = $message;
  // javascript ของ template
  if (is_file(ROOT_PATH.SKIN.'/admin.js')) {
    $javascript[] = '<script src='.WEB_URL.'/'.SKIN.'/admin.js></script>';
  }
  $replace[] = implode("\n", $stylesheet);
  $replace[] = implode("\n", $javascript);
  $replace[] = implode("\n", $script);
  $replace[] = implode("\n", $css);
  $replace[] = strip_tags($title);
  $replace[] = $config['web_title'];
  $name = trim("$login_result[pname] $login_result[fname] $login_result[lname]");
  $name = $name == '' ? $login_result['display_name'] : $name;
  $replace[] = $name == '' ? $login_result['email'] : $name;
  $replace[] = $login_result['id'];
  $replace[] = 'gcms::getLng';
  $replace[] = VERSION;
  $replace[] = LANGUAGE;
  $replace[] = $config['admin_skin'];
  $replace[] = WEB_URL;
  $replace[] = $input == 'login_email' ? 'autofocus' : '';
  $replace[] = $input == 'login_password' ? 'autofocus' : '';
  $replace[] = DATA_URL;
  $replace[] = $_GET['src'];
  $replace[] = $login_remember == 1 ? 'checked' : '';
  $replace[] = $login_email;
  $replace[] = $login_password;
  echo preg_replace_callback('/{URLQUERY(\?([a-zA-Z0-9=&\-_@\.]+))?}/', function($matches) use ($url_query) {
    return gcms::adminURL($url_query, $matches[2]);
  }, gcms::pregReplace($patt, $replace, gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/index.html")));
}
