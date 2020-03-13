<?php
// xhr.php
header("content-type: text/html; charset=UTF-8");
// inint
include (dirname(__FILE__).'/bin/inint.php');
// ตรวจสอบ referer
if (gcms::isReferer()) {
  if (is_dir(ROOT_PATH.'admin/install/') || ($config['maintenance_mode'] == 1 && !gcms::isAdmin())) {
    $ret['content'] = rawurlencode($lng['MAINTENANCE_DETAIL']);
    $ret['title'] = rawurlencode(strip_tags($config['web_title']));
  } else {
    $ret = array();
    // โมดูลที่เรียก
    $module = $_POST['module'];
    // query จาก URL ที่ส่งมา
    $urls = array();
    foreach ($_POST AS $key => $value) {
      if (!in_array($key, array('action', 'lang', 'f', 'c'))) {
        $urls[$key] = "$key=$value";
      }
    }
    // ค่าคงที่สำหรับป้องกันการเรียกหน้าเพจโดยตรง
    DEFINE('MAIN_INIT', 'xhr');
    // โหลดเมนูทั้งหมดเรียงตามลำดับเมนู (รายการแรกคือหน้า Home)
    $sql = "SELECT M.`id` AS `module_id`,M.`module`,M.`owner`,M.`config`,U.`index_id`,U.`parent`,U.`level`,U.`menu_text`,U.`menu_tooltip`,U.`accesskey`,U.`menu_url`,U.`menu_target`,U.`alias`,U.`published`";
    $sql .= ",(CASE U.`parent` WHEN 'MAINMENU' THEN 0 WHEN 'BOTTOMMENU' THEN 1 WHEN 'SIDEMENU' THEN 2 ELSE 3 END ) AS `pos`";
    $sql .= " FROM `".DB_MENUS."` AS U";
    $sql .= " LEFT JOIN `".DB_INDEX."` AS I ON I.`id`=U.`index_id` AND I.`index`='1' AND I.`language` IN ('".LANGUAGE."','')";
    $sql .= " LEFT JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
    $sql .= " WHERE U.`language` IN ('".LANGUAGE."','')";
    $sql .= " ORDER BY `pos` ASC,U.`parent` ASC ,U.`menu_order` ASC";
    $menus = $cache->get($sql);
    if (!$menus) {
      $menus = $db->customQuery($sql);
      $cache->save($sql, $menus);
    }
    foreach ($menus AS $item) {
      if (!isset($install_modules[$item['module']]) && $item['module'] != '') {
        $install_modules[$item['module']] = $item;
        $install_owners[$item['owner']][] = $item['module'];
        $module_list[] = $item['module'];
      }
    }
    // โหลดโมดูลทั้งหมดที่ติดตั้ง
    $sql = "SELECT `id` AS `module_id`,`module`,`owner`,`config` FROM `".DB_MODULES."`";
    $_modules = $cache->get($sql);
    if (!$_modules) {
      $_modules = $db->customQuery($sql);
      $cache->save($sql, $_modules);
    }
    foreach ($_modules AS $item) {
      if (!isset($install_modules[$item['module']])) {
        $install_modules[$item['module']] = $item;
        $install_owners[$item['owner']][] = $item['module'];
        $module_list[] = $item['module'];
      }
    }
    // โมดูลที่ติดตั้ง
    $dir = ROOT_PATH.'modules/';
    $f = @opendir($dir);
    if ($f) {
      while (false !== ($text = readdir($f))) {
        if ($text != '.' && $text != '..') {
          if (is_dir($dir.$text.'/')) {
            if (!isset($install_owners[$text])) {
              $install_owners[$text] = array();
            }
            $module_list[] = $text;
          }
        }
      }
      closedir($f);
    }
    // ไม่มีโมดูลใช้โมดูลแรกสุด
    $module = $module == '' ? $module_list[0] : $module;
    // ตรวจสอบโมดูลที่เรียก
    include ROOT_PATH.'module.php';
    // โหลดภาษา,config,ไฟล์ inint ของโมดูลที่ติดตั้ง
    foreach ($install_owners AS $owner => $items) {
      if (is_file(ROOT_PATH."modules/$owner/config.php")) {
        include_once (ROOT_PATH."modules/$owner/config.php");
      }
      if (is_file(ROOT_PATH."modules/$owner/inint.php")) {
        include_once (ROOT_PATH."modules/$owner/inint.php");
      }
    }
    // ค่า title,description และ keywords ของเว็บหลัก
    $title = strip_tags($config['web_title']);
    $description = $config['web_description'];
    $keywords = $description;
    // login
    $isMember = gcms::isMember();
    // admin
    $isAdmin = gcms::isAdmin();
    if (is_file(ROOT_PATH."modules/$modules[2]/$modules[3].php")) {
      // เรียกโมดูล
      include ROOT_PATH."modules/$modules[2]/$modules[3].php";
    } else {
      $title = $lng['PAGE_NOT_FOUND'];
      $content = '<div class=error>'.$title.'</div>';
    }
    $patt = array('/{SKIN}/', '/{WEBURL}/', '/{LANGUAGE}/', '/{WEBTITLE}/');
    $replace = array(SKIN, WEB_URL, LANGUAGE, strip_tags($config['web_title']));
    // คืนค่าเนื้อหา
    $ret['menu'] = rawurlencode($menu);
    $ret['title'] = rawurlencode(strip_tags($title));
    $ret['content'] = rawurlencode(preg_replace($patt, $replace, $content));
    if (isset($_POST['to'])) {
      $ret['to'] = rawurlencode($_POST['to']);
    }
    // อัปเดต pagesview
    $counter_day = date('Y-m-d', $mmktime);
    $sql = "UPDATE `".DB_COUNTER."` SET `pages_view`=`pages_view`+1,`time`='$mmktime' WHERE `date`='$counter_day' LIMIT 1";
    $db->query($sql);
    // คืนค่า เวลาที่ใช้ไป
    $ret['db_elapsed'] = $db->timer_stop();
    $ret['db_quries'] = $db->query_count();
  }
  // คืนค่า JSON
  echo gcms::array2json($ret);
}
