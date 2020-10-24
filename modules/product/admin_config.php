<?php
// modules/product/admin_config.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_can_config'])) {
  // ตรวจสอบโมดูลที่เรียก
  $sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 0) {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    $index = $index[0];
    // title
    $title = "$lng[LNG_CONFIG] $lng[LNG_PRODUCT]";
    $a = array();
    $a[] = '<span class=icon-product>{LNG_MODULES}</span>';
    $a[] = '{LNG_PRODUCT}';
    $a[] = '{LNG_CONFIG}';
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-config>'.$title.'</h1></header>';
    // form
    $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
    // รายละเอียดของสินค้า
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_PRODUCT_DETAILS}</span></legend>';
    // product_id_format
    $content[] = '<div class=item>';
    $content[] = '<label for=config_id_format>{LNG_PRODUCT_ID}</label>';
    $content[] = '<span class="g-input icon-edit"><input type=text name=config_id_format id=config_id_format value="'.$config['product_id_format'].'" title="{LNG_FORMAT_NO_COMMENT}"></span>';
    $content[] = '<div class=comment id=result_config_id_format>{LNG_FORMAT_NO_COMMENT}</div>';
    $content[] = '</div>';
    // product_thumbnail_width,product_thumbnail_height
    $content[] = '<div class=item>';
    $content[] = '<span class=label>{LNG_THUMBNAIL}</span>';
    $content[] = '<div class=input-groups-table>';
    $content[] = '<label class=width for=config_thumbnail_width>{LNG_WIDTH}</label>';
    $content[] = '<span class="width g-input icon-width"><input type=number min=50 name=config_thumbnail_width id=config_thumbnail_width value="'.$config['product_thumbnail_width'].'"></span>';
    $content[] = '<label class=width for=config_thumbnail_height>{LNG_HEIGHT}</label>';
    $content[] = '<span class="width g-input icon-height"><input type=number min=50 name=config_thumbnail_height id=config_thumbnail_height value="'.$config['product_thumbnail_height'].'"></span>';
    $content[] = '</div>';
    $content[] = '<div class=comment>{LNG_THUMB_WIDTH_COMMENT}</div>';
    $content[] = '</div>';
    // product_image_width
    $content[] = '<div class=item>';
    $content[] = '<label for=config_image_width>{LNG_IMAGE_WIDTH}</label>';
    $content[] = '<div class=input-groups-table><span class="width g-input icon-width"><input type=number name=config_image_width id=config_image_width value='.(int)$config['product_image_width'].' title="{LNG_IMAGE_WIDTH_COMMENT}"></span><span class=width>{LNG_PX}</span></div>';
    $content[] = '<div class=comment id=result_config_image_width>{LNG_IMAGE_WIDTH_COMMENT}</div>';
    $content[] = '</div>';
    // product_image_type
    $content[] = '<div class=item>';
    $content[] = '<span class=label>{LNG_IMAGE_FILE_TYPIES}</span>';
    $content[] = '<div>';
    foreach (array('jpg', 'jpeg', 'gif', 'png') AS $i => $item) {
      $chk = is_array($config['product_image_type']) && in_array($item, $config['product_image_type']) ? ' checked' : '';
      $d = $item == 'jpg' ? ' id=config_image_type' : '';
      $content[] = '<label><input type=checkbox'.$chk.$d.' name=config_image_type[] value='.$item.' title="{LNG_IMAGE_UPLOAD_TYPE_COMMENT}"> '.$item.'</label>';
    }
    $content[] = '</div>';
    $content[] = '<div class=comment id=result_config_image_type>{LNG_IMAGE_UPLOAD_TYPE_COMMENT}</div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    // รายละเอียดการสั่งซื้อ
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_ORDER_DETAIL}</span></legend>';
    // product_order_no
    $content[] = '<div class=item>';
    $content[] = '<label for=config_order_no>{LNG_ORDER_NO}</label>';
    $content[] = '<span class="g-input icon-edit"><input type=text name=config_order_no id=config_order_no value="'.$config['product_order_no'].'" title="{LNG_FORMAT_NO_COMMENT}"></span>';
    $content[] = '<div class=comment id=result_config_order_no>{LNG_FORMAT_NO_COMMENT}</div>';
    $content[] = '</div>';
    // product_order_delete
    $content[] = '<div class=item>';
    $content[] = '<label for=config_order_delete>{LNG_ORDER_DELETE}</label>';
    $content[] = '<div class=input-groups-table><span class="width g-input icon-edit"><input type=number id=config_order_delete name=config_order_delete value="'.$config['product_order_delete'].'" title="{LNG_ORDER_DELETE_COMMENT}"></span><span class=width>{LNG_DAYS}</span></div>';
    $content[] = '<div class=comment id=result_config_order_delete>{LNG_ORDER_DELETE_COMMENT}</div>';
    $content[] = '</div>';
    // product_cut_stock
    $content[] = '<div class=item>';
    $content[] = '<label for=config_cut_stock>{LNG_CUT_STOCK}</label>';
    $content[] = '<span class="g-input icon-config"><select name=config_cut_stock id=config_cut_stock title="{LNG_CUT_STOCK_COMMENT}">';
    foreach ($lng['PAYMENT_STATUS'] AS $u => $value) {
      if ($value != '') {
        $sel = $u == $config['product_cut_stock'] ? ' selected' : '';
        $content[] = '<option value='.$u.$sel.'>'.$value.'</option>';
      }
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment>{LNG_CUT_STOCK_COMMENT}</div>';
    $content[] = '</div>';
    // product_picture_count
    $content[] = '<div class=item>';
    $content[] = '<label for=config_upload_count>{LNG_IMAGE_COUNT}</label>';
    $content[] = '<span class="g-input icon-config"><select name=config_upload_count id=config_upload_count title="{LNG_IMAGE_UPLOAD_COUNT_COMMENT}">';
    for ($i = 1; $i < 10; $i++) {
      $sel = $i == $config['product_picture_count'] ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.$i.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment>{LNG_IMAGE_UPLOAD_COUNT_COMMENT}</div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    // การแสดงผล
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_DISPLAY}</span></legend>';
    // product_show_menu
    $content[] = '<div class=item>';
    $content[] = '<label for=config_show_menu>{LNG_CATEGORY}</label>';
    $content[] = '<span class="g-input icon-category"><select name=config_show_menu id=config_show_menu title="{LNG_PRODUCT_CATEGORY_MENU_COMMENT}">';
    foreach ($lng['LNG_SHOW_MENU'] AS $key => $value) {
      $sel = $key == $config['product_show_menu'] ? ' selected' : '';
      $content[] = '<option value='.$key.$sel.'>'.$value.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment id=result_config_show_menu>{LNG_PRODUCT_CATEGORY_MENU_COMMENT}</div>';
    $content[] = '</div>';
    // product_rows,product_cols
    $content[] = '<div class=item>';
    $content[] = '<label for=config_product_rows>{LNG_LIST_PER_PAGE}</label>';
    $content[] = '<div class=input-groups-table>';
    $content[] = '<span class=width>{LNG_ROWS}</span>';
    $content[] = '<span class="width g-input icon-width"><select name=config_product_rows id=config_product_rows>';
    for ($i = 1; $i <= 50; $i++) {
      $sel = $i == $config['product_rows'] ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.$i.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<label class=width for=config_product_cols>{LNG_COLS}</label>';
    $content[] = '<span class="width g-input icon-height"><select name=config_product_cols id=config_product_cols>';
    foreach (array(1, 2, 4, 6, 8, 12) as $i) {
      $sel = $i == $config['product_cols'] ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.$i.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '</div>';
    $content[] = '<div class=comment>{LNG_PRODUCT_DISPLAY_COMMENT}</div>';
    $content[] = '</div>';
    // product_sort
    $content[] = '<div class=item>';
    $content[] = '<label for=config_product_sort>{LNG_SORT}</label>';
    $content[] = '<span class="g-input icon-sort"><select name=config_product_sort id=config_product_sort title="{LNG_SORT_COMMENT}">';
    foreach ($lng['PRODUCT_SORT'] AS $k => $v) {
      $sel = $k == $config['product_sort'] ? ' selected' : '';
      $content[] = '<option value='.$k.$sel.'>'.$v.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment>{LNG_PRODUCT_SORT_COMMENT}</div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    // กำหนดความสามารถของสมาชิกแต่ละระดับ
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_MEMBER_ROLE_SETTINGS}</span></legend>';
    $content[] = '<div class=item>';
    $content[] = '<table class="responsive config_table">';
    $content[] = '<thead>';
    $content[] = '<tr>';
    $content[] = '<th>&nbsp;</th>';
    $content[] = '<th scope=col>{LNG_MODERATOR}</th>';
    $content[] = '<th scope=col>{LNG_CAN_WRITE}</th>';
    $content[] = '<th scope=col>{LNG_SALESMAN}</th>';
    $content[] = '<th scope=col>{LNG_CAN_CONFIG}</th>';
    $content[] = '</tr>';
    $content[] = '</thead>';
    $content[] = '<tbody>';
    // สถานะสมาชิก
    foreach ($config['member_status'] AS $i => $item) {
      $bg = $bg == 'bg1' ? 'bg2' : 'bg1';
      $tr = '<tr class="'.$bg.' status'.$i.'">';
      $tr .= '<th>'.$item.'</th>';
      if ($i > 1) {
        // moderator
        $tr .= '<td><label data-text="{LNG_MODERATOR}"><input type=checkbox name=config_moderator[]'.(is_array($config['product_moderator']) && in_array($i, $config['product_moderator']) ? ' checked' : '').' value='.$i.' title="{LNG_MODERATOR_COMMENT}"></label></td>';
        // can_write
        $tr .= '<td><label data-text="{LNG_CAN_WRITE}"><input type=checkbox name=config_can_write[]'.(is_array($config['product_can_write']) && in_array($i, $config['product_can_write']) ? ' checked' : '').' value='.$i.' title="{LNG_PRODUCT_CAN_WRITE_COMMENT}"></label></td>';
        // salesman
        $tr .= '<td><label data-text="{LNG_SALESMAN}"><input type=checkbox name=config_salesman[]'.(is_array($config['product_salesman']) && in_array($i, $config['product_salesman']) ? ' checked' : '').' value='.$i.' title="{LNG_SALESMAN_STATUS}"></label></td>';
        // can_config
        $tr .= '<td><label data-text="{LNG_CAN_CONFIG}"><input type=checkbox name=config_can_config[]'.(is_array($config['product_can_config']) && in_array($i, $config['product_can_config']) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_CONFIG_COMMENT}"></label></td>';
      } else {
        $tr .= '<td colspan=4></td>';
      }
      $tr .= '</tr>';
      $content[] = $tr;
    }
    $content[] = '</tbody>';
    $content[] = '</table>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    // submit
    $content[] = '<fieldset class=submit>';
    $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
    $content[] = '</fieldset>';
    $content[] = '</form>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'new GForm("setup_frm", "'.WEB_URL.'/modules/product/admin_config_save.php").onsubmit(doFormSubmit);';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'product-config';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
