<?php
// widgets/textlink/admin_action.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, admin
if (gcms::isReferer() && gcms::isAdmin() && $_SESSION['login']['account'] != 'demo') {
  // ค่าที่ส่งมา
  $action = $_POST['action'];
  $id = $_POST['id'];
  $value = (int)$_POST['value'];
  if ($action == 'delete') {
    $sql = "SELECT `logo` FROM `".DB_TEXTLINK."` WHERE `id` IN($id) AND logo != ''";
    foreach ($db->customQuery($sql) AS $item) {
      @unlink(DATA_PATH.'image/'.$item['logo']);
    }
    $db->query("DELETE FROM `".DB_TEXTLINK."` WHERE `id` IN($id)");
  } elseif ($action == 'published') {
    $db->query("UPDATE `".DB_TEXTLINK."` SET `published`='$value' WHERE `id` IN($id)");
  } elseif ($action == 'move') {
    // move menu
    $max = 1;
    foreach (explode(',', str_replace('user-', '', $_POST['data'])) As $i) {
      $db->query("UPDATE `".DB_TEXTLINK."` SET `link_order`=".$max." WHERE `id`=".$i." LIMIT 1");
      $max++;
    }
  } elseif ($action == 'styles') {
    // styles
    include (ROOT_PATH.'widgets/textlink/styles.php');
    // template
    if ($_POST['val'] == 'custom') {
      $textlink = $db->getRec(DB_TEXTLINK, $_POST['id']);
      echo $textlink['template'];
    } else {
      echo $textlink_typies[$_POST['val']];
    }
  }
}
