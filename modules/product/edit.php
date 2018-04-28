<?php
// modules/product/edit.php
if (defined('MAIN_INIT')) {
  // ค่า ที่ส่งมา
  $rid = (int)$_REQUEST['id'];
  // ตรวจสอบคำตอบที่ต้องการแก้ไข
  $sql = "SELECT R.`id`,R.`index_id`,R.`detail`,R.`module_id`,R.`member_id`,M.`module`,D.`topic`";
  $sql .= " FROM `".DB_COMMENT."` AS R";
  $sql .= " INNER JOIN `".DB_PRODUCT."` AS Q ON Q.`id`=R.`index_id` AND Q.`module_id`=R.`module_id`";
  $sql .= " INNER JOIN `".DB_PRODUCT_DETAIL."` AS D ON D.`product_id`=R.`index_id` AND D.`module_id`=R.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=R.`module_id`";
  $sql .= " WHERE R.`id`='$rid' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 1) {
    $index = $index[0];
    // ข้อมูลการ login
    $login = $_SESSION['login'];
    // moderator (ผู้ดูแล)
    $canEdit = in_array($login['status'], explode(',', $index['moderator']));
    $canEdit = $isMember && (($index['member_id'] == $login['id']) || $canEdit);
    // เลือกเมนู
    $menu = $install_modules[$index['module']]['alias'];
    $menu = $menu == '' ? $index['module'] : $menu;
    if ($canEdit) {
      // breadcrumbs
      $breadcrumbs = array();
      $canonical = WEB_URL.'/index.php';
      $breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', $canonical, $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
      if ($index['module'] != $module_list[0]) {
        // url ของหน้านี้
        $canonical = gcms::getURL($index['module']);
        $menu_text = $install_modules[$index['module']]['menu_text'];
        $breadcrumbs['MODULE'] = gcms::breadcrumb('', $canonical, $install_modules[$index['module']]['menu_tooltip'], ($menu_text == '' ? $index['topic'] : $menu_text), $breadcrumb);
      }
      // antispam
      $register_antispamchar = gcms::rndname(32);
      $_SESSION[$register_antispamchar] = gcms::rndname(4);
      // แสดงผล
      $patt = array('//{BREADCRUMS}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{ANTISPAM}/', '/{ANTISPAMVAL}/', '/{QID}/', '/{RID}/', '/{DETAIL}/', '/{MODULEID}/', '/{TOPIC}/');
      $replace = array();
      $replace[] = implode("\n", $breadcrumbs);
      $replace[] = 'gcms::getLng';
      $replace[] = $register_antispamchar;
      $replace[] = $isAdmin ? $_SESSION[$register_antispamchar] : '';
      $replace[] = $index['index_id'];
      $replace[] = $index['id'];
      $replace[] = htmlspecialchars(preg_replace('/&#39;/', "'", $index['detail']));
      $replace[] = $index['module_id'];
      $replace[] = $index['topic'];
      $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['module'], 'product', 'editreply'));
    } else {
      $title = $lng['LNG_DATA_NOT_FOUND'];
      $content = '<div class=error>'.$title.'</div>';
    }
  } else {
    $title = $lng['LNG_DOCUMENT_NOT_FOUND'];
    $content = '<div class=error>'.$title.'</div>';
  }
}
