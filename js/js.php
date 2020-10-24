<?php
// js/js.php
header('Content-type: text/javascript; charset: UTF-8');
// inint
include '../bin/inint.php';
// cache
$expirse = 60 * 60 * 24 * 30;
$gmt = gmdate("D, d M Y H:i:s", time() + $expirse).' GMT';
header("Expires: $gmt");
header("Last-Modified: $gmt");
header("Cache-Control: max-age=$expirse, must-revalidate, public");
// default js
$js = array();
$js[] = file_get_contents('gajax.js');
$js[] = file_get_contents('common.js');
$js[] = file_get_contents('gcms.js');
$js[] = file_get_contents('media.js');
$js[] = file_get_contents('editinplace.js');
$js[] = file_get_contents('gddmenu.js');
if (is_file(DATA_PATH.'language/'.LANGUAGE.'.js')) {
    $js[] = file_get_contents(DATA_PATH.'language/'.LANGUAGE.'.js');
}
// js ของโมดูล
$dir = ROOT_PATH.'modules/';
$f = @opendir($dir);
if ($f) {
    while (false !== ($text = readdir($f))) {
        if ($text != "." && $text != "..") {
            if (is_dir($dir.$text.'/')) {
                if (is_file(ROOT_PATH."modules/$text/script.js")) {
                    $js[] = file_get_contents(ROOT_PATH."modules/$text/script.js");
                }
            }
        }
    }
    closedir($f);
}
// js ของ widgets
$dir = ROOT_PATH.'widgets/';
$f = @opendir($dir);
if ($f) {
    while (false !== ($text = readdir($f))) {
        if ($text != "." && $text != "..") {
            if (is_dir($dir.$text.'/')) {
                if (is_file(ROOT_PATH."widgets/$text/script.js")) {
                    $js[] = file_get_contents(ROOT_PATH."widgets/$text/script.js");
                }
            }
        }
    }
    closedir($f);
}
if ((int) $config['counter_digit'] > 0) {
    $js[] = "var counter_digit = $config[counter_digit];";
}
$js[] = "var counter_refresh_time = ".COUNTER_REFRESH_TIME.";";
if ($config['use_ajax'] == 1) {
    $js[] = "var use_ajax = $config[use_ajax];";
}
// web url ใช้ตาม addressbar
if (preg_match('/^(http(s)?:\/\/)(.*)(\/(.*))?$/U', WEB_URL, $match)) {
    $js[] = "window.WEB_URL='$match[1]'+window.location.hostname+'".(isset($match[4]) ? $match[4] : '')."/';";
} else {
    $js[] = "window.WEB_URL=window.location.hostname+'/';";
}
// skin ที่เรียกใช้
$js[] = "window.SKIN='".SKIN."';";
// module url
$js[] = "window.MODULE_URL='$config[module_url]';";
// compress javascript
$patt = array('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#u', '#[\r\t]#', '#\n//.*\n#', '#;//.*\n#', '#[\n]#');
$replace = array();
$replace[] = '';
$replace[] = '';
$replace[] = '';
$replace[] = ";\n";
$replace[] = '';
echo preg_replace($patt, $replace, implode("\n", $js))."\n";
