<?php
// modules/member/dologin.php
if (defined('MAIN_INIT')) {
    // title
    $title = $lng['LNG_LOGIN'];
    // breadcrumbs
    $breadcrumb = gcms::loadtemplate('member', '', 'breadcrumb');
    $breadcrumbs = array();
    // หน้าหลัก
    $breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', WEB_URL.'/index.php', $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
    // url ของหน้านี้
    $breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL('dologin'), strip_tags($lng['LNG_LOGIN_TITLE']), $lng['LNG_LOGIN'], $breadcrumb);
    if (!gcms::isMember()) {
        // อ่านข้อมูลจาก cookie
        $login_email = isset($_COOKIE[PREFIX.'_login_email']) ? gcms::decode($_COOKIE[PREFIX.'_login_email']) : '';
        $login_password = isset($_COOKIE[PREFIX.'_login_password']) ? gcms::decode($_COOKIE[PREFIX.'_login_password']) : '';
        $login_remember = isset($_COOKIE[PREFIX.'_login_remember']) ? (int) $_COOKIE[PREFIX.'_login_remember'] : 0;
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
            $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate('member', 'member', 'loginfrm'));
        }
    } elseif (!empty($config['custom_member']) && is_file(ROOT_PATH.$config['custom_member'])) {
        // custom member form
        include_once ROOT_PATH.$config['custom_member'];
    } else {
        // อ่านข้อมูลสมาชิก
        $login_result = $db->getRec(DB_USER, $_SESSION['login']['id']);
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
        $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate('member', 'member', 'memberfrm'));
    }
    // เลือกเมนู
    $menu = 'dologin';
}
