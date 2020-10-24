<?php
// sitemap.php
header("content-type: text/xml; charset=UTF-8");
// inint
include 'bin/inint.php';
echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
echo '<urlset';
echo '  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
echo '  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
echo '  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9';
echo '  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
echo "\n";
// วันที่วันนี้
$cdate = date('Y-m-d', $mmktime);
echo sitemap(WEB_URL.'/index.php', $cdate);
// query โมดูลที่ติดตั้งแล้วทั้งหมด
$sql = "SELECT M.`module`,M.`owner`,I.`language`,M.`id`";
$sql .= " FROM `".DB_MODULES."` AS M";
$sql .= " LEFT JOIN `".DB_INDEX."` AS I ON I.`module_id`=M.`id` AND I.`index`='1'";
$datas = $cache->get($sql);
if (!$datas) {
    $datas = $db->customQuery($sql);
    $cache->save($sql, $datas);
}
$modules = array();
$owners = array();
foreach ($datas as $item) {
    $modules[$item['id']] = $item['module'];
    $owners[$item['owner']][] = $item['id'];
    echo sitemap(gcms::getURL($item['module'], '', 0, 0, ($item['language'] == '' ? '' : "lang=$item[language]"), false), $cdate);
}
// modules
$dir = ROOT_PATH.'modules/';
$f = @opendir($dir);
if ($f) {
    while (false !== ($owner = readdir($f))) {
        if ($owner != '.' && $owner != '..') {
            if (is_file($dir."$owner/sitemap.php")) {
                include $dir."$owner/sitemap.php";
            }
        }
    }
    closedir($f);
}
echo '</urlset>';

/**
 * @param $url
 * @param $date
 */
function sitemap($url, $date)
{
    return '<url><loc>'.$url.'</loc><lastmod>'.$date.'</lastmod><changefreq>daily</changefreq><priority>0.5</priority></url>'."\n";
}
