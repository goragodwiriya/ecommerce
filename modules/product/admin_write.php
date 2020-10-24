<?php
// modules/product/admin_write.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_can_write'])) {
  // id ที่เลือก
  $id = (int)$_GET['qid'];
  // tab ที่เลือก
  $tab = $_GET['tab'];
  $tab = $tab == '' ? 'detail_'.$config['languages'][0] : $tab;
  if ($id > 0) {
    // สินค้าที่ต้องการแก้ไข
    $sql = "SELECT P.*";
    $sql .= " FROM `".DB_MODULES."` AS M";
    $sql .= " INNER JOIN `".DB_PRODUCT."` AS P ON P.`module_id`=M.`id` AND P.`id`='$id'";
    $sql .= " WHERE M.`owner`='product' LIMIT 1";
  } else {
    // สินค้าใหม่ ตรวจสอบโมดูล
    $sql1 = " SELECT MAX(`id`) FROM `".DB_PRODUCT."` WHERE `module_id`=M.`id`";
    $sql = "SELECT M.`id` AS `module_id`,M.`config`,1+COALESCE(($sql1),0) AS `id`";
    $sql .= " FROM `".DB_MODULES."` AS M";
    $sql .= " WHERE M.`owner`='product' LIMIT 1";
  }
  $index = $db->customQuery($sql);
  if (sizeof($index) == 1) {
    $index = $index[0];
    // title
    $title = $lng['LNG_PRODUCT_DETAILS'];
    $a = array();
    $a[] = '<span class=icon-product>{LNG_MODULES}</span>';
    $a[] = '<a href="{URLQUERY?module=product-config&qid=0}">{LNG_PRODUCT}</a>';
    $a[] = '<a href="{URLQUERY?module=product-setup&qid=0}">{LNG_PRODUCT_LIST}</a>';
    $datas = array();
    $categories = array();
    $additional = array();
    if ($id > 0) {
      // product detail
      $sql = "SELECT `language`,`topic`,`keywords`,`description`,`detail` FROM `".DB_PRODUCT_DETAIL."` WHERE `id`='$index[id]'";
      foreach ($db->customQuery($sql) AS $i => $item) {
        $item['language'] = ($i == 0 && $item['language'] == '') ? $config['languages'][0] : $item['language'];
        $datas[$item['language']] = $item;
      }
      // product category
      $sql = "SELECT `category_id`,`subcategory` FROM `".DB_PRODUCT_CATEGORY."` WHERE `id`='$index[id]' ORDER BY `category_id` ASC";
      foreach ($db->customQuery($sql) AS $item) {
        $categories[] = "$item[category_id]:$item[subcategory]";
      }
      // product additional
      $sql = "SELECT * FROM `".DB_PRODUCT_ADDITIONAL."`";
      $sql .= " WHERE `module_id`='$index[module_id]' AND `product_id`='$index[id]'";
      $sql .= " ORDER BY `id` ASC";
      $additional = $db->customQuery($sql);
      // แก้ไข
      $a[] = '<a href="{URLQUERY?module=product-write&qid='.$id.'}" title="'.$datas[LANGUAGE]['topic'].'">'.gcms::cutstring($datas[LANGUAGE]['topic'], 50).'</a>';
    } else {
      // ใหม่
      $a[] = '<a href="{URLQUERY?module=product-write}">{LNG_ADD}</a>';
      // ข้อมูลเริ่มต้น
      $index['product_no'] = sprintf($config['product_id_format'], $index['id']);
      $index['published'] = 1;
      $index['can_reply'] = sizeof($config['product_can_reply']) > 0 ? 1 : 0;
      $index['stock'] = -1;
      // ตรวจสอบและโหลดรายการ default
      if ($index['config'] != '') {
        $saveasdefault = unserialize($index['config']);
        if (is_array($saveasdefault['additional'])) {
          $additional = $saveasdefault['additional'];
        }
        if (is_array($saveasdefault['custom_fields'])) {
          $index = array_merge($index, $saveasdefault['custom_fields']);
        }
        if (is_array($saveasdefault['options'])) {
          $index = array_merge($index, $saveasdefault['options']);
          if (is_array($saveasdefault['categories'])) {
            foreach ($saveasdefault['categories'] AS $item) {
              $categories[] = "$item[category_id]:$item[subcategory]";
            }
          }
        }
      }
      $index['quantity'] = max(1, $index['quantity']);
    }
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-write>'.$title.'</h1>';
    $content[] = '<div class=inline><div class=writetab>';
    // menu
    $content[] = '<ul id=accordient_menu>';
    foreach ($config['languages'] AS $item) {
      $content[] = '<li><a id=tab_detail_'.$item.' href="{URLQUERY?module=product-write&qid='.$id.'&tab=detail_'.$item.'}">{LNG_DETAIL}&nbsp;<img src=../datas/language/'.$item.'.gif alt='.$item.'></a></li>';
    }
    $content[] = '<li><a id=tab_options href="{URLQUERY?module=product-write&qid='.$id.'&tab=options}">{LNG_PRODUCT_INFO}</a></li>';
    $content[] = '<li><a id=tab_additional href="{URLQUERY?module=product-write&qid='.$id.'&tab=additional}">{LNG_PRODUCT_PRICE}</a></li>';
    $content[] = '<li><a id=tab_upload href="{URLQUERY?module=product-upload&qid='.$id.'}">{LNG_PRODUCT_PICTURE}</a></li>';
    $content[] = '</ul>';
    $content[] = '</div></div>';
    $content[] = '</header>';
    // ฟอร์มเขียน-แก้ไข
    $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
    // menu
    foreach ($config['languages'] AS $language) {
      $item = $datas[$language];
      $content[] = '<fieldset id=detail_'.$language.'>';
      $content[] = '<legend><span>{LNG_DETAIL_IN}&nbsp;&nbsp;<img src="'.DATA_URL.'language/'.$language.'.gif" alt='.$language.'></span></legend>';
      // topic
      $content[] = '<div class=item>';
      $content[] = '<label for=write_topic_'.$language.'>{LNG_TOPIC}</label>';
      $content[] = '<span class="g-input icon-edit"><input type=text id=write_topic_'.$language.' name=write_topic_'.$language.' value="'.$item['topic'].'" maxlength=109 title="{LNG_TOPIC_COMMENT}"></span>';
      $content[] = '<div class=comment id=result_write_topic_'.$language.'>{LNG_TOPIC_COMMENT}</div>';
      $content[] = '</div>';
      // keywords
      $content[] = '<div class=item>';
      $content[] = '<label for=write_keywords_'.$language.'>{LNG_TAGS}</label>';
      $content[] = '<span class="g-input icon-tags"><input type=text id=write_keywords_'.$language.' name=write_keywords_'.$language.' value="'.$item['keywords'].'" title="{LNG_TAGS_COMMENT}"></span>';
      $content[] = '<div class=comment id=result_write_keywords_'.$language.'>{LNG_TAGS_COMMENT}</div>';
      $content[] = '</div>';
      // sdetail
      $content[] = '<div class=item>';
      $content[] = '<label for=write_description_'.$language.'>{LNG_DESCRIPTION}</label>';
      $content[] = '<span class="g-input icon-file"><textarea id=write_description_'.$language.' name=write_description_'.$language.' rows=3 maxlength=149 title="{LNG_DESCRIPTION_COMMENT}">'.gcms::detail2TXT($item['description']).'</textarea></span>';
      $content[] = '<div class=comment id=result_write_description_'.$language.'>{LNG_DESCRIPTION_COMMENT}</div>';
      $content[] = '</div>';
      // detail
      $content[] = '<div class=item>';
      $content[] = '<label for=write_detail_'.$language.'>{LNG_DETAIL}</label>';
      $content[] = '<div><textarea name=write_detail_'.$language.' id=write_detail_'.$language.'>'.gcms::detail2TXT($item['detail']).'</textarea></div>';
      $content[] = '</div>';
      $content[] = '</fieldset>';
    }
    $content[] = '<div id=options>';
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_PRODUCT_INFORMATION}</span></legend>';
    // alias
    $content[] = '<div class=item>';
    $content[] = '<label for=write_alias>{LNG_ALIAS}</label>';
    $content[] = '<span class="g-input icon-world"><input type=text id=write_alias name=write_alias value="'.$index['alias'].'" maxlength=64 title="{LNG_ALIAS_COMMENT}"></span>';
    $content[] = '<div class=comment id=result_write_alias>{LNG_ALIAS_COMMENT}</div>';
    $content[] = '</div>';
    // product_no
    $content[] = '<div class=item>';
    $content[] = '<label for=write_no>{LNG_PRODUCT_ID}</label>';
    $content[] = '<span class="g-input icon-edit"><input type=text id=write_no name=write_no value="'.$index['product_no'].'" maxlength=20 title="{LNG_PRODUCT_NO_COMMENT}" autofocus></span>';
    $content[] = '<div class=comment id=result_write_no>{LNG_PRODUCT_NO_COMMENT}</div>';
    $content[] = '</div>';
    // category (multiple)
    $row = array();
    $sql = "SELECT `category_id`,`topic`,`subcategory` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]' ORDER BY `category_id`";
    foreach ($db->customQuery($sql) AS $category) {
      $c = gcms::ser2Str($category['topic']);
      if ($c == '') {
        $c = '-';
      }
      if ($category['subcategory'] == '') {
        $value = "$category[category_id]:0";
        $sel = in_array($value, $categories) ? ' selected' : '';
        $row[] = '<option value='.$value.$sel.'>'.$c.'</option>';
      } else {
        $row[] = '<optgroup label="'.$c.'">';
        $subcategory = array();
        foreach (gcms::ser2Array($category['subcategory']) AS $c => $v) {
          foreach ($v AS $a => $b) {
            $subcategory[$a][$c] = $b;
          }
        }
        foreach ($subcategory[LANGUAGE] AS $c => $item) {
          $value = "$category[category_id]:$c";
          $sel = in_array($value, $categories) ? ' selected' : '';
          $row[] = '<option value='.$value.$sel.'>'.$item.'</option>';
        }
        $row[] = '</optgroup>';
      }
    }
    if (sizeof($row) == 0) {
      $row[] = '<option value=0>{LNG_NO_CATEGORY}</option>';
    }
    $content[] = '<div class=item>';
    $content[] = '<label for=write_category>{LNG_PRODUCT_CATEGORY}</label>';
    $content[] = '<span class="g-input icon-category"><select id=write_category name=category[] multiple size=5 title="{LNG_PLEASE_SELECT}">'.implode('', $row).'</select></span>';
    $content[] = '<div class=comment id=result_write_category>{LNG_CATEGORY_SELECT}</div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_CONFIG_OTHER}</span></legend>';
    // icons
    $content[] = '<div class=item>';
    $content[] = '<span class=label>{LNG_PRODUCT_ICON}</span>';
    $content[] = '<div>';
    foreach ($lng['PRODUCT_CUSTOM_ICONS'] AS $k => $v) {
      $sel = $index[$k] == '1' ? ' checked' : '';
      $content[] = '<label class='.$k.'><input type=checkbox name=write_'.$k.$sel.' value=1 title="'.$v.'">&nbsp;'.$v.'</label>';
    }
    $content[] = '</div>';
    $content[] = '<div class=comment>{LNG_PRODUCT_ICON_COMMENT}</div>';
    $content[] = '</div>';
    // published
    $content[] = '<div class=item>';
    $content[] = '<label for=write_published>{LNG_PUBLISHED}</label>';
    $content[] = '<span class="g-input icon-published1"><select id=write_published name=write_published title="{LNG_PUBLISHED_SETTING}">';
    foreach ($lng['LNG_PUBLISHEDS'] AS $i => $item) {
      $sel = $index['published'] == $i ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment>{LNG_PUBLISHED_SETTING}</div>';
    $content[] = '</div>';
    // can_reply
    $content[] = '<div class=item>';
    $content[] = '<label for=write_can_reply>{LNG_CAN_REPLY}</label>';
    $content[] = '<span class="g-input icon-comments"><select id=write_can_reply name=write_can_reply title="{LNG_CANREPLY_SETTING}">';
    foreach ($lng['LNG_CAN_REPLIES'] AS $i => $item) {
      $sel = $index['can_reply'] == $i ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment>{LNG_CANREPLY_SETTING}</div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    $content[] = '</div>';
    // product additional
    $content[] = '<div id=additional>';
    $content[] = '<div class=subtitle>{LNG_PRODUCT_ADDITIONAL_SUBTITLE}</div>';
    $content[] = '<div class=item>';
    $content[] = '<table class="responsive-v fullwidth">';
    $content[] = '<thead>';
    $content[] = '<tr>';
    $content[] = '<th>{LNG_PRODUCT_ADDITIONAL_TOPIC}</th>';
    $content[] = '<th>{LNG_PRICE}</th>';
    $content[] = '<th>{LNG_NET_PRICE}</th>';
    $content[] = '<th>{LNG_STOCK}</th>';
    $content[] = '<th><img id=additional_add class=add src=../modules/product/img/plus.png alt=add title="{LNG_ADD_NEW}"></th>';
    $content[] = '</tr>';
    $content[] = '</thead>';
    $content[] = '<tbody id=product_list>';
    if (sizeof($additional) == 0) {
      $additional[] = array('id' => 0, 'stock' => -1);
    }
    foreach ($additional AS $aid => $item) {
      $tr = '<tr id=M_'.$aid.'>';
      $tr .= '<td><label><input type=text size=40 name=product_topic[] value="'.htmlspecialchars($item['topic']).'" title="{LNG_PRODUCT_ADDITIONAL_TOPIC_COMMENT}"></label></td>';
      $tr .= '<td class="price_td bg2">';
      foreach ($lng['CURRENCY_UNITS'] AS $unit => $text) {
        $tr .= '<label><input type=text class=currency size=10 name=product_price_'.$unit.'[] value="'.gcms::int2Curr($item["price_$unit"], '').'" title="{LNG_PLEASE_FILL} ('.$text.')">&nbsp;'.$text.'</label>';
      }
      $tr .= '</td>';
      $tr .= '<td class=price_td>';
      foreach ($lng['CURRENCY_UNITS'] AS $unit => $text) {
        $tr .= '<label><input type=text class=currency size=10 name=product_net_'.$unit.'[] value="'.gcms::int2Curr($item["net_$unit"], '').'" title="{LNG_PLEASE_FILL} ('.$text.')">&nbsp;'.$text.'</label>';
      }
      $tr .= '</td>';
      $tr .= '<td>';
      $tr .= '<label><input type=text size=1 name=product_stock[] id=product_stock_'.$aid.' value="'.$item['stock'].'" title="{LNG_STOCK_COMMENT}"></label>';
      $tr .= '<label>&nbsp;<input type=checkbox name=product_stock[] value=-1'.($item['stock'] == -1 ? ' checked' : '').' class="unlimited product_stock_'.$aid.'" title="{LNG_UNLIMITED}"></label>';
      $tr .= '</td>';
      $tr .= '<td>';
      $tr .= '<img src=../modules/product/img/minus.png width=16 height=16 class=delete alt=delete title="{LNG_DELETE}">';
      $tr .= '<input type=hidden name=product_id[] id=product_id_'.$aid.' value='.(int)$item['id'].'>';
      $tr .= '</td>';
      $tr .= '</tr>';
      $content[] = $tr;
    }
    $content[] = '</tbody>';
    $content[] = '</table>';
    $content[] = '<div class=comment>{LNG_PRODUCT_PRICE_COMMENT}</div>';
    $content[] = '</div>';
    $content[] = '</div>';
    // submit
    $content[] = '<fieldset class=submit>';
    $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
    $content[] = '<label id=saveasdefault>&nbsp;<input type=checkbox name=saveasdefault value=1 title="{LNG_SAVE_AS_DEFAILT}">&nbsp;{LNG_SAVE_AS_DEFAILT}</label>';
    $content[] = '<input type=hidden id=write_id name=write_id value='.($id == 0 ? 0 : $id).'>';
    $content[] = '<input type=hidden id=write_tab name=write_tab>';
    $content[] = '</fieldset>';
    $lastupdate = $index['last_update'] == '' ? '-' : gcms::mktime2date($index['last_update']);
    $content[] = '<div class=lastupdate><span class=comment>{LNG_WRITE_COMMENT}</span>{LNG_LAST_UPDATE}<span id=lastupdate>'.$lastupdate.'</span></div>';
    $content[] = '</form>';
    $content[] = '</section>';
    $content[] = '<script>';
    foreach ($config['languages'] AS $item) {
      $content[] = 'CKEDITOR.replace("write_detail_'.$item.'", {';
      $content[] = 'toolbar:"Document",';
      $content[] = 'language:"'.LANGUAGE.'",';
      $content[] = 'height:300,';
      $connector = urlencode(WEB_URL.'/ckeditor/filemanager/connectors/php/connector.php');
      $content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Connector='.$connector.'",';
      $content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector='.$connector.'",';
      $content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector='.$connector.'",';
      $content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php",';
      $content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php?Type=Image",';
      $content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.phpType=Flash"';
      $content[] = '});';
    }
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'new GForm("setup_frm", "'.WEB_URL.'/modules/product/admin_write_save.php").onsubmit(doProductSubmit);';
    $content[] = 'new GValidator("write_alias", "keyup,change", checkAlias, "'.WEB_URL.'/modules/product/admin_check.php", null, "setup_frm");';
    $content[] = 'new GValidator("write_no", "keyup,change", checkProductNo, "'.WEB_URL.'/modules/product/admin_check.php", null, "setup_frm");';
    $content[] = 'inintProductSetup("product_list");';
    $content[] = 'inintProductWrite("accordient_menu", "'.$tab.'");';
    $content[] = 'doProductInint("setup_frm");';
    //$content[] = 'inintPMTable("product_list");';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'product-write';
  } else {
    $title = $id == 0 ? $lng['LNG_DATA_NOT_FOUND'] : $lng['LNG_ID_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
