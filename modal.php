<?php
// modal.php
header("content-type: text/html; charset=UTF-8");
// inint
include dirname(__FILE__).'/bin/inint.php';
// ตรวจสอบ referer
if (gcms::isReferer() && preg_match('/^([a-z]+)$/', $_POST['module'], $match)) {
    if (is_file(ROOT_PATH.SKIN."$match[1].html")) {
        $patt = array('/{(LNG_[A-Z0-9_]+)}/e', '/{SKIN}/', '/{WEBURL}/', '/{TITLE}/', '/{DESCRIPTION}/', '/{LANGUAGE}/');
        $replace = array('gcms::getLng', SKIN, WEB_URL, $config['web_title'], $config['web_description'], LANGUAGE);
        echo gcms::pregReplace($patt, $replace, gcms::loadfile(ROOT_PATH.SKIN."$match[1].html"));
    } else {
        echo '<div class=error>'.$lng['PAGE_NOT_FOUND'].'</div>';
    }
}
