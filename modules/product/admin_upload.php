<?php
// modules/product/admin_upload.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_can_write'])) {
  // id ของสินค้าที่เลือก
  $id = (int)$_GET['qid'];
  // ครวจสอบสินค้าที่เลือก
  $sql = "SELECT P.*,(SELECT `topic` FROM `".DB_PRODUCT_DETAIL."` WHERE `id`='$id' AND `language` IN ('".LANGUAGE."','')) AS `topic`";
  $sql .= " FROM `".DB_PRODUCT."` AS P WHERE P.`id`=$id LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 1) {
    $index = $index[0];
    // title
    $title = $lng['LNG_PRODUCT_UPLOAD'];
    $a = array();
    $a[] = '<span class=icon-product>{LNG_MODULES}</span>';
    $a[] = '<a href="{URLQUERY?module=product-config&qid=0}">{LNG_PRODUCT}</a>';
    $a[] = '<a href="{URLQUERY?module=product-setup&qid=0}">{LNG_PRODUCT_LIST}</a>';
    $a[] = '<a href="{URLQUERY?module=product-write&qid='.$index['id'].'}" title="'.$index['topic'].'">'.gcms::cutstring($index['topic'], 50).'</a>';
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-gallery>'.$title.'</h1>';
    $content[] = '<div class=inline><div class=writetab>';
    // menu
    $content[] = '<ul id=accordient_menu>';
    foreach ($config['languages'] AS $item) {
      $content[] = '<li><a id=tab_detail_'.$item.' href="{URLQUERY?module=product-write&qid='.$index['id'].'&tab=detail_'.$item.'}">{LNG_DETAIL}&nbsp;<img src=../datas/language/'.$item.'.gif alt='.$item.'></a></li>';
    }
    $content[] = '<li><a id=tab_options href="{URLQUERY?module=product-write&qid='.$index['id'].'&tab=options}">{LNG_PRODUCT_INFO}</a></li>';
    $content[] = '<li><a id=tab_additional href="{URLQUERY?module=product-write&qid='.$index['id'].'&tab=additional}">{LNG_PRODUCT_PRICE}</a></li>';
    $content[] = '<li><a id=tab_upload class=select href="{URLQUERY?module=product-upload&qid='.$index['id'].'}">{LNG_PRODUCT_PICTURE}</a></li>';
    $content[] = '</ul>';
    $content[] = '</div></div>';
    $content[] = '</header>';
    $content[] = '<div id=product_upload class=setup_frm>';
    $content[] = '<div class=subtitle>'.str_replace(array('{T}', '{W}'), array(implode(', ', $config['product_image_type']), $config['product_image_width']), $lng['LNG_PRODUCT_PICTURE_COMMENT']).'</div>';
    // query รูปภาพ
    $gallery = array();
    $sql = "SELECT * FROM `".DB_PRODUCT_IMAGE."` WHERE `product_id`='$index[id]' ORDER BY `id` ASC";
    foreach ($db->customQuery($sql) AS $item) {
      $gallery[$item['id']] = $item;
    }
    $c = max(1, $config['product_picture_count']);
    for ($i = 0; $i < $c; $i++) {
      $img = isset($gallery[$i]) && is_file(DATA_PATH.'product/'.$gallery[$i]['thumbnail']) ? DATA_URL.'product/'.$gallery[$i]['thumbnail'] : WEB_URL."/modules/product/img/nopicture.png";
      $data = '<form id=productpicfrm-'.$i.' method=post action=index.php>';
      $data .= '<table>';
      $data .= '<tr>';
      $data .= '<td rowspan=2><img id=productpicimg-'.$i.' src="'.$img.'" alt=thumbnail class=picture width=50 height=50></td>';
      $data .= '<td><input id=productpicfile-'.$i.' name=file-'.$i.' type=file></td>';
      $data .= '</tr>';
      $data .= '<tr>';
      $data .= '<td>';
      $data .= $i == 0 ? '' : '<span id=productpicdelete-'.$i.' class=icon-delete title="{LNG_DELETE}"></span>';
      $data .= '<input type=hidden id=productpicid-'.$i.' name=productpicid value='.$index['id'].'-'.$gallery[$i]['id'].'-'.$i.'>';
      $data .= $i == 0 ? '<span class=comment>{LNG_PRODUCT_FIRST_PICTURE}</span>' : '';
      $data .= '<span id=productpicwait-'.$i.' class=wait>&nbsp;</span>';
      $data .= '</td>';
      $data .= '</tr>';
      $data .= '</table>';
      $data .= '</form>';
      $content[] = $data;
    }
    $content[] = '</div>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'inintProductUpload("product_upload");';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'product-upload';
  } else {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
