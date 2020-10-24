<?php
// skin/.../css.php
header('Content-type: text/css; charset: UTF-8');
// inint
$folder = basename(dirname(__FILE__));
require (str_replace(array('\\', 'skin/'.$folder.'/css.php'), array('/', ''), __FILE__).'/bin/inint.php');
// cache 1 เดือน
$expirse = 60 * 60 * 24 * 30;
$gmt = gmdate("D, d M Y H:i:s", time() + $expirse).' GMT';
header("Expires: $gmt");
header("Last-Modified: $gmt");
header("Cache-Control: max-age=$expirse, must-revalidate, public");
// โหลด css หลัก
$data = preg_replace('/url\(([\'\"])fonts\//isu', 'url($1'.WEB_URL.'/skin/fonts/', @file_get_contents('../fonts.css'));
$data .= @file_get_contents('../gcss.css');
$data .= @file_get_contents('style.css');
$body = '';
if ($config['bg_image'] != '' && is_file(DATA_PATH.'image/'.$config['bg_image'])) {
  $body .= 'background-image:url('.DATA_URL.'image/'.$config['bg_image'].');';
  $body .= 'background-repeat:repeat;';
}
if ($config['bg_color'] != '') {
  $body .= 'background-color:'.$config['bg_color'].';';
}
if ($body != '') {
  $data .= 'body{'.$body.'}';
}
if ($config['logo'] != '' && is_file(DATA_PATH."image/$config[logo]")) {
  $info = getImageSize(DATA_PATH."image/$config[logo]");
  $ext = strtolower(end(explode('.', $config['logo'])));
  if (in_array($ext, array('jpg', 'gif', 'png'))) {
    $data .= "html > body #logo{background-image:url(".DATA_URL."image/$config[logo])";
  } elseif ($ext == 'swf') {
    $data .= "html > body #logo{background-image:url()";
  }
  $data .= ";height:$info[1]px;}";
}
// สีของสมาชิก
if (is_array($config['color_status'])) {
  foreach ($config['color_status'] AS $i => $item) {
    $data .= ".status$i{color:$item !important}";
  }
}
// โหลด modules
$modules = array();
$dir = ROOT_PATH.'modules/';
$f = opendir($dir);
while (false !== ($text = readdir($f))) {
  if ($text != "." && $text != "..") {
    if (is_dir($dir.$text.'/')) {
      $modules[$text] = $text;
    }
  }
}
closedir($f);
// โหลด css ของหน้าที่ติดตั้ง (ถ้ามี)
$sql = 'SELECT `module` FROM `'.DB_MODULES."`";
foreach ($db->customQuery($sql) AS $item) {
  $modules[$item['module']] = $item['module'];
}
foreach ($modules AS $module) {
  if (is_file(ROOT_PATH.SKIN.$module.'/style.css')) {
    $data .= preg_replace('/url\((img|fonts)\//isu', 'url('.WEB_URL.'/'.SKIN.$module.'/\\1/', file_get_contents(ROOT_PATH.SKIN.$module.'/style.css'));
  }
}
// โหลด css ของ widgets
$dir = ROOT_PATH.'widgets/';
$f = opendir($dir);
while (false !== ($text = readdir($f))) {
  if ($text != "." && $text != "..") {
    if (is_dir($dir.$text.'/')) {
      if (is_file(ROOT_PATH."widgets/$text/style.css")) {
        $data .= preg_replace('/url\(img\//isu', 'url('.WEB_URL.'/widgets/'.$text.'/img/', file_get_contents(ROOT_PATH."widgets/$text/style.css"));
      }
    }
  }
}
closedir($f);
// compress css
$data = preg_replace(array('/\{[\s]+/', '/[\s;]+\}/', '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '/\s?([:;,>\{\}])\s?/s'), array('{', '}', '', '\\1', ' '), $data);
// ตัด > ใน ie ต่ำกว่า 7
if (preg_match('|MSIE ([0-9].[0-9]{1,2})|', $_SERVER['HTTP_USER_AGENT'], $matched)) {
  if ((int)$matched[1] < 7) {
    $data = str_replace('>', ' ', $data);
  }
}
echo preg_replace(array('/[\r\n\t]/s', '/[\s]{2,}/s', '/;}/'), array('', ' ', '}'), $data);
