<?php
// modules/product/admin_categorywrite.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_can_config'])) {
  // หมวดที่เรียก
  $category_id = (int)$_GET['cid'];
  if ($category_id == 0) {
    // ใหม่, ตรวจสอบโมดูลที่เรียก
    $sql1 = " SELECT MAX(`category_id`) FROM `".DB_CATEGORY."` WHERE `module_id`=M.`id`";
    $sql = "SELECT 0 AS `id`,M.`id` AS `module_id`,M.`module`,1+COALESCE(($sql1),0) AS `category_id`";
    $sql .= " FROM `".DB_MODULES."` AS M";
    $sql .= " WHERE M.`owner`='product' LIMIT 1";
  } else {
    // แก้ไข ตรวจสอบโมดูลและหมวดที่เลือก
    $sql = "SELECT C.*,M.`module`";
    $sql .= " FROM `".DB_CATEGORY."` AS C";
    $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id` AND M.`owner`='product'";
    $sql .= " WHERE C.`id`=$category_id LIMIT 1";
  }
  $index = $db->customQuery($sql);
  if (sizeof($index) == 1) {
    $index = $index[0];
    if ($id == 0) {
      // ใหม่
      $index['published'] = 1;
    }
    // title
    $title = "$lng[LNG_CREATE] - $lng[LNG_EDIT] $lng[LNG_CATEGORY]";
    $a = array();
    $a[] = '<span class=icon-product>{LNG_MODULES}</span>';
    $a[] = '<a href="{URLQUERY?module=product-config}">{LNG_PRODUCT}</a>';
    $a[] = '<a href="{URLQUERY?module=product-category}">{LNG_CATEGORY}</a>';
    $a[] = $category_id == 0 ? '{LNG_CREATE}' : '{LNG_EDIT}';
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-category>'.$title.'</h1></header>';
    // ฟอร์มเพิ่ม แก้ไข หมวด
    $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_CATEGORY}</span></legend>';
    // category_id
    $content[] = '<div class=item>';
    $content[] = '<label for=category_id>{LNG_ID}</label>';
    $content[] = '<span class="g-input icon-category"><input type=number id=category_id name=category_id value='.(int)$index['category_id'].' title="{LNG_CATEGORY_ID_COMMENT}"></span>';
    $content[] = '<div class=comment id=result_category_id>{LNG_CATEGORY_ID_COMMENT}</div>';
    $content[] = '</div>';
    // published
    $content[] = '<div class=item>';
    $content[] = '<label for=category_published>{LNG_PUBLISHED}</label>';
    $content[] = '<span class="g-input icon-published1"><select name=category_published id=category_published title="{LNG_PUBLISHED_SETTING}">';
    foreach ($lng['LNG_PUBLISHEDS'] AS $i => $item) {
      $sel = $index['published'] == $i ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment>{LNG_CATEGORY_CAN_PUBLISHED_REPLY_COMMENT}</div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    // topic,detail,icon
    $topic = gcms::ser2Array($index['topic']);
    $icon = gcms::ser2Array($index['icon']);
    foreach ($config['languages'] AS $item) {
      $content[] = '<fieldset>';
      $content[] = '<legend><span>{LNG_CATEGORY_DETAIL}&nbsp;<img src="'.WEB_URL.'/datas/language/'.$item.'.gif" alt="'.$item.'"></span></legend>';
      // topic
      $content[] = '<div class=item>';
      $content[] = '<label for=category_topic_'.$item.'>{LNG_CATEGORY}</label>';
      $t = $item == LANGUAGE && !isset($topic[LANGUAGE]) ? $topic[''] : $topic[$item];
      $content[] = '<span class="g-input icon-edit"><input type=text id=category_topic_'.$item.' name=category_topic['.$item.'] maxlength=64 title="{LNG_CATEGORY_COMMENT}" value="'.$t.'"></span>';
      $content[] = '<div class=comment id=result_category_topic_'.$item.'>{LNG_CATEGORY_COMMENT}</div>';
      $content[] = '</div>';
      // icon
      $content[] = '<div class=item>';
      $t = $item == LANGUAGE && !isset($icon[LANGUAGE]) ? $icon[''] : $icon[$item];
      $content[] = '<div class=usericon><span><img id=icon_'.$item.' src='.(is_file(DATA_PATH."product/$t") ? DATA_URL."product/$t" : "../skin/img/blank.gif").' alt=icon></span></div>';
      $content[] = '<label for=category_icon_'.$item.'>{LNG_ICON}</label>';
      $content[] = '<span class="g-input icon-upload"><input type=file class=g-file id=category_icon_'.$item.' name=category_icon_'.$item.' placeholder="{LNG_BROWSER_FILE}" title="{LNG_UPLOAD_CATEGORY_ICON_COMMENT}" accept="'.gcms::getEccept(array('jpg', 'gif', 'png')).'" data-preview=icon_'.$item.'></span>';
      $content[] = '<div class=comment id=result_category_icon_'.$item.'>{LNG_UPLOAD_CATEGORY_ICON_COMMENT}</div>';
      $content[] = '</div>';
      $content[] = '</fieldset>';
    }
    // submit
    $content[] = '<fieldset class=submit>';
    $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
    $content[] = '<input type=hidden id=write_id name=write_id value='.(int)$index['id'].'>';
    $content[] = '<input type=hidden id=module_id name=module_id value='.$index['module_id'].'>';
    $content[] = '</fieldset>';
    $content[] = '</form>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = 'inintWriteCategory("product");';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'product-categorywrite';
  } else {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
