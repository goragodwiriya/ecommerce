<?php
// modules/product/admin_category.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['product_can_config'])) {
  // ตรวจสอบโมดูลที่เรียก
  $sql = "SELECT `id` FROM `".DB_MODULES."` WHERE `owner`='product' LIMIT 1";
  $index = $db->customQuery($sql);
  if (sizeof($index) == 1) {
    $index = $index[0];
    // title
    $title = "$lng[LNG_CREATE] - $lng[LNG_EDIT] $lng[LNG_CATEGORY]";
    $a = array();
    $a[] = '<span class=icon-product>{LNG_MODULES}</span>';
    $a[] = '<a href="{URLQUERY?module=product-config}">{LNG_PRODUCT}</a>';
    $a[] = '{LNG_CATEGORY}';
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-category>'.$title.'</h1></header>';
    // หมวดหมู่
    $content[] = '<table id=tbl_category class="tbl_list fullwidth">';
    $content[] = '<caption>{LNG_CATEGORY_LIST_COMMENT}</caption>';
    $content[] = '<thead>';
    $content[] = '<tr>';
    $content[] = '<th id=c0 scope=col>{LNG_CATEGORIES}</th>';
    $content[] = '<th id=c1 scope=col class=check-column><a class="checkall icon-uncheck"></a></th>';
    $content[] = '<th id=c2 scope=col class=center>{LNG_LANGUAGE}</th>';
    $content[] = '<th id=c3 scope=col class="center tablet">{LNG_ICON}</th>';
    $content[] = '<th id=c4 scope=col class=center>{LNG_ID}</th>';
    $content[] = '<th id=c5 scope=col class="center tablet">{LNG_PUBLISHED}</th>';
    $content[] = '<th id=c6 scope=col class="center mobile">{LNG_SUB_CATEGORY}</th>';
    $content[] = '<th id=c7 scope=col colspan=2></th>';
    $content[] = '</tr>';
    $content[] = '</thead>';
    $content[] = '<tbody>';
    // เรียกหมวดหมู่ทั้งหมด
    $categories = array();
    $sql = "SELECT * FROM `".DB_CATEGORY."` WHERE `module_id`='$index[id]' ORDER BY `category_id`";
    foreach ($db->customQuery($sql) AS $item) {
      $topic = unserialize($item['topic']);
      $icon = unserialize($item['icon']);
      $subcategory = array();
      foreach (gcms::ser2Array($item['subcategory']) AS $v) {
        foreach ($v AS $a => $b) {
          $subcategory[$a][] = $b;
        }
      }
      foreach ($config['languages'] AS $l) {
        $item['language'] = $l;
        $item['topic'] = $topic[$l] == '' && $l == LANGUAGE ? $topic[''] : $topic[$l];
        $item['icon'] = $icon[$l] == '' && $l == LANGUAGE ? $icon[''] : $icon[$l];
        $item['subcategory'] = is_array($subcategory[$l]) ? implode(',', $subcategory[$l]) : '';
        if ($item['topic'] != '' || $item['icon'] != '') {
          $categories[$item['id']][$l] = $item;
        }
      }
    }
    foreach ($categories AS $items) {
      $c = sizeof($items);
      $i = 0;
      foreach ($items AS $item) {
        $bg = $i == 0 ? ($bg == 'bg1' ? 'bg2' : 'bg1') : $bg;
        $id = $item['id'];
        $tr = '<tr id=L_'.$id.' class='.$bg.'>';
        $tr .= '<th headers=c0 id=b'.$id.' class=topic scope=row>'.$item['topic'].'</th>';
        if ($i == 0) {
          $tr .= '<td headers="b'.$id.' c1" class=check-column rowspan='.$c.'><a id=check_'.$id.' class=icon-uncheck></a></td>';
        }
        $tr .= '<td headers="b'.$id.' c2" class=menu><img src="'.($item['language'] == '' ? "../skin/img/blank.gif" : '../datas/language/'.$item['language'].'.gif').'" alt="'.$item['language'].'"></td>';
        // ไอคอนของหมวด
        $icon = is_file(DATA_PATH."product/$item[icon]") ? DATA_URL."product/$item[icon]" : "../skin/img/blank.gif";
        $tr .= '<td headers="b'.$id.' c3" class="center tablet"><img src="'.$icon.'" alt=icon></td>';
        if ($i == 0) {
          $tr .= '<td headers="b'.$id.' c4" class=center rowspan='.$c.'><label><input type=text class=number size=5 id=categoryid_'.$index['id'].'_'.$id.' value="'.$item['category_id'].'" title="{LNG_EDIT}"></label></td>';
          $tr .= '<td headers="b'.$id.' c5" class="menu tablet" rowspan='.$c.'><span class=icon-published'.$item['published'].' title="'.$lng['LNG_PUBLISHEDS'][$item['published']].'"></span></td>';
        }
        $tr .= '<td headers="b'.$id.' c6" class="center mobile"><span id=subcategory_'.$id.'_'.$item['language'].'>'.$item['subcategory'].'</span></td>';
        if ($i == 0) {
          $tr .= '<td headers="b'.$id.' c6" rowspan='.$c.' class=menu><a title="{LNG_SUB_CATEGORY}" id=subbtn_'.$id.' class=icon-subcategory></a></td>';
          $tr .= '<td headers="b'.$id.' c7" rowspan='.$c.' class=menu><a href="{URLQUERY?module=product-categorywrite&src=product-category&cid='.$id.'}" title="{LNG_EDIT}" class=icon-edit></a></td>';
        }
        $tr .= '</tr>';
        $content[] = $tr;
        $i++;
      }
    }
    $content[] = '</tbody>';
    $content[] = '<tfoot>';
    $content[] = '<tr>';
    $content[] = '<td headers=c0></td>';
    $content[] = '<td headers=c1 class=check-column><a class="checkall icon-uncheck"></a></td>';
    $content[] = '<td headers=c2 colspan=7></td>';
    $content[] = '</tr>';
    $content[] = '</tfoot>';
    $content[] = '</table>';
    $content[] = '<div class=table_nav>';
    // sel action
    $sel = array();
    $sel[] = '<select id=sel_action>';
    $sel[] = '<option value=delete_'.$index['id'].'>{LNG_DELETE}</option>';
    foreach ($lng['LNG_PUBLISHEDS'] AS $i => $item) {
      $sel[] = '<option value=published_'.$index['id'].'_'.$i.'>'.$item.'</option>';
    }
    $sel[] = '</select>';
    $action = $_GET['action'];
    $content[] = str_replace('value='.$action.'>', 'value='.$action.' selected>', implode('', $sel));
    $content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action>{LNG_SELECT_ACTION}</label>';
    $content[] = '<a class="button add" href="index.php?module=product-categorywrite&amp;src=product-category&amp;id='.$index['id'].'"><span class=icon-add>{LNG_ADD_NEW} {LNG_CATEGORY}</span></a>';
    $content[] = '</div>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = 'inintProductCategory("product");';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'product-category';
  } else {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
