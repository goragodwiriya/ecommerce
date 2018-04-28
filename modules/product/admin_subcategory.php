<?php
// modules/product/admin_subcategory.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, can_write
if (gcms::isReferer() && gcms::canConfig($config['product_can_config'])) {
  $categories = array();
  $subcategory = array();
  // หมวดที่เลือก
  $sql = "SELECT C.`id`,C.`module_id`,C.`topic`,C.`subcategory`";
  $sql .= " FROM `".DB_CATEGORY."` AS C";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id` AND M.`owner`='product'";
  $sql .= " WHERE C.`id`=".(int)$_POST['id']." LIMIT 1";
  $category = $db->customQuery($sql);
  if (sizeof($category) == 1) {
    $category = $category[0];
    $subcategory = gcms::ser2Array($category['subcategory']);
  }
  if (sizeof($subcategory) == 0) {
    $subcategory[] = array();
  }
  // form
  $content = array();
  $content[] = '<form id=sub_frm class=sub_frm method=post action=index.php>';
  $content[] = '<header><h2 class=icon-subcategory>'.gcms::ser2Str($category['topic']).'</h2></header>';
  $content[] = '<fieldset>';
  $content[] = '<table class="responsive-v border vert-table">';
  $content[] = '<thead><tr><th colspan='.(1 + sizeof($config['languages'])).'>'.$lng['LNG_SUB_CATEGORY'].'</th></tr></thead>';
  $content[] = '<tbody>';
  foreach ($subcategory AS $i => $items) {
    $content[] = '<tr>';
    foreach ($config['languages'] AS $item) {
      $content[] = '<td><label><input type=text class=wide size=20 name='.$item.'[] id=sc_'.$item.'_'.$i.' value="'.$items[$item].'" style="background-image:url('.WEB_URL.'/datas/language/'.$item.'.gif)"></label></td>';
    }
    $content[] = '<td class=icons><div><a class=icon-plus title="'.$lng['LNG_ADD'].'"></a><a class=icon-minus title="'.$lng['LNG_REMOVE'].'"></a></div></td>';
    $content[] = '</tr>';
  }
  $content[] = '</tbody>';
  $content[] = '</table>';
  $content[] = '<div class=comment id=result_subcategory>'.$lng['LNG_SUB_CATEGORY_COMMENT'].'</div>';
  $content[] = '</fieldset>';
  $content[] = '<div>';
  $content[] = '<input type=submit class="button large save" value="'.$lng['LNG_SAVE'].'">';
  $content[] = '<input type=hidden name=write_id value='.(int)$category['id'].'>';
  $content[] = '</div>';
  $content[] = '</form>';
  $content[] = '<script>';
  $content[] = '$G("sub_frm").Ready(function(){';
  $content[] = 'new GForm("sub_frm", "'.WEB_URL.'/modules/product/admin_subcategory_save.php").onsubmit(doFormSubmit);';
  $content[] = 'inintPMTable("sub_frm");';
  $content[] = 'inintTR("tbl_category", null, ["L_'.$category['id'].'"]);';
  $content[] = '});';
  $content[] = '</script>';
  echo rawurlencode(implode("\n", $content));
}
