<?php
// admin/languageedit.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // ค่าที่ส่งมา
  $id = (int)$_GET['id'];
  if ($id > 0) {
    $language = $db->getRec(DB_LANGUAGE, $id);
  } else {
    $language = array();
  }
  if ($id > 0 && !$language) {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    // title
    $title = $lng['LNG_LANGUAGE_TITLE'];
    $a = array();
    $a[] = '<span class=icon-tools>{LNG_TOOLS}</span>';
    $a[] = '<a href="{URLQUERY?module=language}">{LNG_LANGUAGE}</a>';
    $a[] = '{LNG_LANGUAGE_SETTINGS}';
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-language>'.$title.'</h1></header>';
    $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
    $content[] = '<fieldset>';
    $content[] = '<legend><span>{LNG_LANGUAGE_SETTINGS}</span></legend>';
    // js
    $content[] = '<div class=item>';
    $content[] = '<label for=save_js>{LNG_FILE}</label>';
    $content[] = '<span class="g-input icon-config"><select name=save_js id=save_js title="{LNG_PLEASE_SELECT}">';
    foreach (array('php', 'js') AS $k => $v) {
      $sel = $k == $language['js'] ? ' selected' : '';
      $content[] = '<option value='.$k.$sel.'>'.$v.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment id=result_save_js>{LNG_LANGUAGE_FILE_COMMENT}</div>';
    $content[] = '</div>';
    // type
    $content[] = '<div class=item>';
    $content[] = '<label for=save_type>{LNG_TYPE}</label>';
    $content[] = '<span class="g-input icon-config"><select name=save_type id=save_type title="{LNG_PLEASE_SELECT}">';
    foreach (array('text', 'int', 'array') AS $k) {
      $sel = $k == $language['type'] ? ' selected' : '';
      $content[] = '<option value='.$k.$sel.'>'.$k.'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '</div>';
    // owner
    $content[] = '<div class=item>';
    $content[] = '<label for=save_owner>{LNG_MODULE}</label>';
    $content[] = '<span class="g-input icon-config"><select name=save_owner id=save_owner title="{LNG_PLEASE_SELECT}">';
    $sql = "SELECT `owner` FROM `".DB_LANGUAGE."` WHERE `owner`!=''";
    $sql .= $design_mode == 'design' ? '' : " AND `owner`!='sysadmin'";
    $sql .= " GROUP BY `owner`";
    foreach ($db->customQuery($sql) AS $k) {
      $sel = $k['owner'] == $language['owner'] ? ' selected' : '';
      $content[] = '<option value='.$k['owner'].$sel.'>'.$k['owner'].'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '</div>';
    // key
    $content[] = '<div class=item>';
    $content[] = '<label for=save_key>{LNG_LANGUAGE_VARIABLE}</label>';
    $content[] = '<span class="g-input icon-edit"><input type=text name=save_key id=save_key value="'.htmlspecialchars($language['key']).'" title="{LNG_PLEASE_FILL}" autofocus></span>';
    $content[] = '</div>';
    // language
    $content[] = '<div class=item>';
    $content[] = '<table class="responsive-v border fullwidth" id=languageedit>';
    $content[] = '<thead>';
    $content[] = '<tr>';
    $content[] = '<th class=center>{LNG_KEY}</th>';
    foreach ($install_languages AS $a => $k) {
      $content[] = '<th class=center>'.$k.'</th>';
    }
    $content[] = '<th>&nbsp;</th>';
    $content[] = '</tr>';
    $content[] = '</thead>';
    $content[] = '<tbody>';
    $save = array();
    if ($id > 0) {
      // ตรวจสอบข้อมูลเป็นแอเรย์
      if ($language['type'] == 'array') {
        foreach ($install_languages AS $k) {
          $array = unserialize($language[$k]);
          if (is_array($array)) {
            foreach ($array As $k1 => $v1) {
              $save[$k1][$k] = $v1;
            }
          }
        }
      } else {
        $save[''] = $language;
      }
    } else {
      $save[''] = array();
    }
    $i = 0;
    foreach ($save AS $key => $value) {
      $content[] = '<tr id=M_'.$i.'>';
      $content[] = '<td data-text="{LNG_KEY}"><label class=g-input><input type=text name=save_array[] value="'.$key.'" title="{LNG_KEY}"></label></td>';
      foreach ($install_languages AS $a => $k) {
        $content[] = '<td data-text='.$k.'><label class=g-input><textarea class=wide cols=50 rows=3 name=language_'.$k.'[] title="'.$k.'">'.gcms::detail2TXT($value[$k]).'</textarea></label></td>';
      }
      $content[] = '<td class=icons><div><a class=icon-plus title="{LNG_ADD}"></a><a class=icon-minus title="{LNG_DELETE}"></a></div></td>';
      $content[] = '</tr>';
      $i++;
    }
    $content[] = '</tbody>';
    $content[] = '</table>';
    $content[] = '<div class=comment>{LNG_LANGUAGE_COMMENT}</div>';
    $content[] = '</div>';
    $content[] = '</fieldset>';
    // submit
    $content[] = '<fieldset class=submit>';
    $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
    $content[] = '<input type=hidden id=save_id name=save_id value='.(int)$language['id'].'>';
    $content[] = '<input type=hidden name=languageedit value=1>';
    $content[] = '</fieldset>';
    $content[] = '</form>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'new GForm("setup_frm", "language_save.php").onsubmit(doFormSubmit);';
    $content[] = 'inintPMTable("languageedit");';
    $content[] = '});';
    $content[] = '</script>';
    // หน้าปัจจุบัน
    $url_query['module'] = 'languageedit';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
