<?php
// modules/member/login.php
if (defined('MAIN_INIT')) {
    if (isset($_POST['login_remember'])) {
        $login_remember = (int) $_POST['login_remember'];
    } elseif (isset($_COOKIE[PREFIX.'_login_remember'])) {
        $login_remember = (int) $_COOKIE[PREFIX.'_login_remember'];
    } else {
        $login_remember = 0;
    }
    if (isset($_REQUEST['login_email']) && isset($_REQUEST['login_password'])) {
        // login
        $login_email = $db->sql_trim_str($_POST['login_email']);
        $login_password = $db->sql_trim_str($_POST['login_password']);
        // ตรวจสอบการกรอก
        if ($login_email == '') {
            $error = $lng['LNG_EMAIL_EMPTY'];
            $input = 'login_email';
        } elseif ($login_password == '') {
            $error = $lng['LNG_PASSWORD_EMPTY'];
            $input = 'login_password';
        }
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'logout') {
        // logout เคลีร์ย cookie, session และ ตัวแปร
        setCookie(PREFIX.'_login_email', '', time(), '/');
        setCookie(PREFIX.'_login_password', '', time(), '/');
        $login_email = '';
        $login_password = '';
        $login_remember = 0;
        unset($_SESSION['login']);
    } elseif (isset($_SESSION['login'])) {
        // มาจากการ เปิดหน้าเพจปกติ หรือจาก refresh อ่านจาก SESSION
        $login_email = $_SESSION['login']['email'];
        $login_password = $_SESSION['login']['password'];
    } elseif (isset($_GET['error']) && $_GET['error'] == 'EMAIL_EXISIS') {
        // facebook มี email อยู่แล้ว
        $error = $lng['LNG_EMAIL_EXISTS'];
    } else {
        // เข้าระบบครั้งแรก ตรวจสอบ cookie
        $login_email = isset($_COOKIE[PREFIX.'_login_email']) ? gcms::decode($_COOKIE[PREFIX.'_login_email']) : '';
        $login_password = isset($_COOKIE[PREFIX.'_login_password']) ? gcms::decode($_COOKIE[PREFIX.'_login_password']) : '';
    }
    $isMember = false;
    $isAdmin = false;
    if ($login_email != '' && $login_password != '') {
        // ตรวจสอบการ login
        $login_result = gcms::CheckLogin($login_email, $login_password);
        if (is_array($login_result)) {
            // login สำเร็จ
            $_SESSION['login'] = $login_result;
            $_SESSION['login']['password'] = $login_password;
            // login
            $isMember = true;
            // admin
            $isAdmin = $isMember && gcms::isAdmin();
            // ตรวจสอบการปันทึกการ login
            if ($login_remember) {
                // บันทึก user, password
                setCookie(PREFIX.'_login_email', gcms::encode($login_result['email']), time() + 3600 * 24 * 365, '/');
                setCookie(PREFIX.'_login_password', gcms::encode($login_password), time() + 3600 * 24 * 365, '/');
            }
            setCookie(PREFIX.'_login_remember', $login_remember, time() + 3600 * 24 * 365, '/');
        } else {
            // ข้อความผิดพลาด
            $error = array();
            $error[] = $lng['LNG_MEMBER_NOT_FOUND'];
            $error[] = $lng['LNG_MEMBER_NO_ACTIVATE'];
            $error[] = $lng['LNG_MEMBER_BAN'];
            $error[] = $lng['LNG_PASSWORD_INCORRECT'];
            $error[] = $lng['LNG_MEMBER_LOGIN_EXISTS'];
            $input = $login_result == 3 ? 'login_password' : 'login_email';
            $error = strip_tags($error[$login_result]);
            $login_email = '';
            $login_password = '';
        }
    }
    if (MAIN_INIT == 'chklogin') {
        // โหลดภาษา,config,ไฟล์ inint ของโมดูลที่ติดตั้ง
        $dir = ROOT_PATH.'modules/';
        $f = opendir($dir);
        while (false !== ($text = readdir($f))) {
            if ($text != '.' && $text != '..' && $text != 'index' && $text != 'member') {
                if (is_file(ROOT_PATH."modules/$text/config.php")) {
                    include_once ROOT_PATH."modules/$text/config.php";
                }
                if (is_file(ROOT_PATH."modules/$text/inint.php")) {
                    include_once ROOT_PATH."modules/$text/inint.php";
                }
            }
        }
        closedir($f);
    }
    // breadcrumbs
    $breadcrumb = gcms::loadtemplate('member', '', 'breadcrumb');
    $breadcrumbs = array();
    // หน้าหลัก
    $breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
    // url ของหน้านี้
    $breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL('login'), strip_tags($lng['LNG_LOGIN_TITLE']), $lng['LNG_LOGIN'], $breadcrumb);
    if (!$isMember) {
        if (!empty($config['custom_login']) && is_file(ROOT_PATH.$config['custom_login'])) {
            // custom login form
            include_once ROOT_PATH.$config['custom_login'];
        } else {
            // ฟอร์ม login
            $patt = array('/{BREADCRUMS}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{WEBTITLE}/', '/{SUBTITLE}/', '/{EMAIL}/',
                '/{PASSWORD}/', '/{REMEMBER}/', '/{WEBURL}/', '/{FACEBOOK}/', '/{NEXT}/');
            $replace = array();
            $replace[] = implode("\n", $breadcrumbs);
            $replace[] = 'gcms::getLng';
            $replace[] = $config['web_title'];
            $replace[] = empty($error) ? $config['web_description'] : '<span class=error>'.$error.'</span>';
            $replace[] = $login_email;
            $replace[] = $login_password;
            $replace[] = $login_remember == 1 ? 'checked' : '';
            $replace[] = WEB_URL;
            $replace[] = (empty($config['facebook']['appId']) || empty($config['facebook']['secret'])) ? 'hidden' : 'facebook';
            $replace[] = empty($next) ? 'back' : $next;
            $template = gcms::loadtemplate('member', 'member', 'login');
            if ($template == '') {
                $template = gcms::loadtemplate('member', 'member', 'loginfrm');
            }
            $content = gcms::pregReplace($patt, $replace, $template);
        }
    } elseif ($config['custom_member'] != '' && is_file(ROOT_PATH.$config['custom_member'])) {
        // custom member form
        include_once ROOT_PATH.$config['custom_member'];
    } else {
        // กรอบข้อมูลสมาชิก
        $patt = array('/{BREADCRUMS}/', '/{WEBTITLE}/', '/{SUBTITLE}/', '/{WEBURL}/', '/{DISPLAYNAME}/',
            '/{ID}/', '/{USERICON}/', '/{POINT}/', '/{STATUS}/', '/{ADMIN}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{FACEBOOK}/');
        $replace = array();
        $replace[] = implode("\n", $breadcrumbs);
        $replace[] = $config['web_title'];
        $replace[] = $error == '' ? $config['web_description'] : '<span class=error>'.$error.'</span>';
        $replace[] = WEB_URL;
        $replace[] = $login_result['displayname'] == '' ? $login_result['email'] : $login_result['displayname'];
        $replace[] = $login_result['id'];
        $replace[] = $login_result['usericon'];
        $replace[] = $login_result['point'];
        $replace[] = $login_result['status'];
        $replace[] = $login_result['admin_access'] == 1 ? 'admin' : ' hidden';
        $replace[] = 'gcms::getLng';
        $replace[] = ($config['facebook']['appId'] == '' || $config['facebook']['secret'] == '') ? 'hidden' : 'facebook';
        $template = gcms::loadtemplate('member', 'member', 'member');
        if ($template == '') {
            $template = gcms::loadtemplate('member', 'member', 'memberfrm');
        }
        $content = gcms::pregReplace($patt, $replace, $template);
    }
    // เลือกเมนู
    $menu = 'login';
}
