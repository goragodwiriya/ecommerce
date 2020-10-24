<?php
// modules/product/admin_select.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_can_config'])) {
  // ตรวจสอบรายการที่เลือก
  $type = '';
  foreach ($lng['PRODUCT_CUSTOM_SELECT'] AS $k => $v) {
    if ($_GET['typ'] == $k) {
      $type = $k;
      $type_name = $v;
    }
  }
  // ตรวจสอบโมดูลที่เรียก
  $sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 0 || $type == '') {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    $index = $index[0];
    // title
    $title = "$lng[LNG_ADD] - $lng[LNG_EDIT] $type_name";
    $a = array();
    $a[] = '<span class=icon-product>{LNG_MODULES}</span>';
    $a[] = '<a href="{URLQUERY?module=product-config}">{LNG_PRODUCT}</a>';
    $a[] = $type_name;
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-list>'.$title.'</h1></header>';
    // form
    $content[] = '<form id=select_frm method=post action=index.php>';
    $content[] = '<table class="responsive-v border">';
    $content[] = '<thead><tr><th>{LNG_ID}</th><th colspan='.(sizeof($config['languages']) + 1).'>{LNG_TOPIC}</th></tr></thead>';
    $content[] = '<tbody>';
    // อ่านข้อมูลจาก db
    $list = array();
    $sql = "SELECT `select_id`,GROUP_CONCAT(`topic`,'".chr(2)."',`language` ORDER BY `language` SEPARATOR '".chr(1)."') AS `topic` FROM `".DB_SELECT."`";
    $sql .= " WHERE `module_id`='$index[id]' AND `type`='$type'";
    $sql .= " GROUP BY `select_id` ORDER BY `select_id` ASC";
    foreach ($db->customQuery($sql) AS $item) {
      $ds = array();
      foreach (explode(chr(1), $item['topic']) AS $a) {
        list($t, $l) = explode(chr(2), $a);
        $ds[$l] = array('topic' => $t);
      }
      $ds['select_id'] = $item['select_id'];
      $list[] = $ds;
    }
    if (sizeof($list) == 0) {
      $list[] = array('select_id' => 1);
    }
    foreach ($list AS $i => $item) {
      $row = '<tr id=M_'.$i.'>';
      $row .= '<td><label data-text="{LNG_ID}"><input type=text class=number value="'.$item['select_id'].'" name=select_id[] size=5 title="{LNG_ID}"></label></td>';
      foreach ($config['languages'] AS $l) {
        $row .= '<td><label data-text='.$l.'><input type=text size=40 class=wide name=topic_'.$l.'[] value="'.$item[$l]['topic'].'" style="background-image:url(../datas/language/'.$l.'.gif)" title="{LNG_TOPIC} '.$l.'"></label></td>';
      }
      $row .= '<td class=icons><div><a class=icon-plus title="{LNG_ADD}"></a><a class=icon-minus title="{LNG_DELETE}">&nbsp;</a></div></td>';
      $row .= '</tr>';
      $content[] = $row;
    }
    $content[] = '</tbody>';
    $content[] = '</table>';
    // submit
    $content[] = '<p class=submit>';
    $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
    $content[] = '<input type=hidden name=select_type value='.$type.'>';
    $content[] = '</p>';
    $content[] = '</form>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'new GForm("select_frm", "'.WEB_URL.'/modules/product/admin_select_save.php").onsubmit(doFormSubmit);';
    $content[] = 'inintPMTable("select_frm");';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'product-select';
    $url_query['typ'] = $type;
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
