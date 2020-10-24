<?php
// admin/savewrite.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// ตรวจสอบ referer และ แอดมิน
if (gcms::isReferer() && gcms::isAdmin() && ($_POST['intro'] == 1 || $_POST['maintenance'] == 1)) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // ภาษาทีต้องการบันทึก
    $lang = $_POST['write_language'];
    $lang = in_array($lang, $config['languages']) ? $lang : LANGUAGE;
    $patt = array();
    $replace = array();
    // ตัด /r/n
    $patt[] = '/[\r\n]{1,}/su';
    $replace[] = '';
    // หน้าว่างๆ
    $patt[] = '/^(&nbsp;|\s){0,}<br[\s\/]+?>(&nbsp;|\s){0,}$/iu';
    $replace[] = '';
    // ตัด PHP
    $patt[] = '/<\?(.*?)\?>/su';
    $replace[] = '';
    $save = array();
    $detail = $db->sql_quote(preg_replace($patt, $replace, $_POST['write_detail']));
    // ตรวจสอบ ข้อความเดิม
    $key = $_POST['intro'] == 1 ? 'INTRO_PAGE_DETAIL' : 'MAINTENANCE_DETAIL';
    $search = $db->basicSearch(DB_LANGUAGE, 'key', $key);
    if (!$search) {
      $save['type'] = 'text';
      $save['owner'] = 'index';
      $save['js'] = 0;
      $save['key'] = $key;
      // รายการใหม่ บันทึกทุกภาษาไว้ก่อน
      foreach ($config['languages'] AS $item) {
        $save[$item] = $detail;
      }
      $db->add(DB_LANGUAGE, $save);
    } else {
      $save[$lang] = $detail;
      $db->edit(DB_LANGUAGE, $search['id'], $save);
    }
    // โหลด config ใหม่
    $config = array();
    if (is_file(CONFIG)) {
      include CONFIG;
    }
    if ($_POST['intro'] == 1) {
      // intro page
      $config['show_intro'] = (int)$_POST['write_mode'];
    } else {
      // maintenance page
      $config['maintenance_mode'] = (int)$_POST['write_mode'];
    }
    // save config
    if (gcms::saveConfig(CONFIG, $config)) {
      // อ่านไฟล์ภาษาใหม่
      gcms::saveLanguage();
      // คืนค่า
      $ret['error'] = 'SAVE_COMPLETE';
      $ret['location'] = 'reload';
    } else {
      $ret['error'] = 'DO_NOT_SAVE';
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
