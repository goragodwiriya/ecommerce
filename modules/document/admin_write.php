<?php
// modules/document/admin_write.php
if (MAIN_INIT == 'admin' && $isMember) {
  // id ที่เลือก
  $id = (int)$_GET['qid'];
  // id ของโมดูลที่เลือก (module_id)
  $module_id = (int)$_GET['id'];
  // หมวดที่เลือก
  $cat = (int)$_GET['cat'];
  // tab ที่เลือก
  $tab = $_GET['tab'];
  $tab = $tab == '' ? 'detail_'.$config['languages'][0] : $tab;
  if ($id > 0) {
    // แก้ไข ตรวจสอบรายการที่เลือก
    $sql = "SELECT D.*,M.`owner`,M.`module`";
    $sql .= ",(CASE WHEN ISNULL(C.`config`) THEN M.`config` ELSE CONCAT(M.`config`,'\n',C.`config`) END) AS `config`";
    $sql .= " FROM `".DB_INDEX."` AS D";
    $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=$module_id";
    $sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`module_id`=$module_id AND I.`index`='1' AND I.`language` IN ('".LANGUAGE."','')";
    $sql .= " LEFT JOIN `".DB_CATEGORY."` AS C ON C.`category_id`=I.`category_id` AND C.`module_id`=$module_id";
    $sql .= " WHERE D.`id`=$id AND D.`module_id`=$module_id AND D.`index`='0'";
    $sql .= " LIMIT 1";
  } else {
    // ใหม่ ตรวจสอบโมดูล
    $sql = "SELECT M.`id` AS `module_id`,M.`module`,M.`owner`,C.`category_id`,'$mmktime' AS `create_date`";
    $sql .= ",(CASE WHEN ISNULL(C.`config`) THEN M.`config` ELSE CONCAT(M.`config`,'\n',C.`config`) END) AS `config`";
    $sql .= " FROM `".DB_MODULES."` AS M";
    $sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`module_id`=$module_id AND I.`index`='1' AND I.`language` IN ('".LANGUAGE."','')";
    $sql .= " LEFT JOIN `".DB_CATEGORY."` AS C ON C.`category_id`=$cat AND C.`module_id`=$module_id";
    $sql .= " WHERE M.`id`=$module_id AND M.`owner`='document'";
    $sql .= " LIMIT 1";
  }
  $index = $db->customQuery($sql);
  $index = sizeof($index) == 1 ? $index[0] : false;
  if ($index) {
    // config
    gcms::r2config($index['config'], $index, $id == 0);
    // login
    $login = $_SESSION['login'];
    if ($id > 0) {
      // แก้ไข ตรวจสอบเจ้าของหรือ ผู้ดูแล
      $canWrite = ($index['member_id'] == $login['id'] || in_array($login['status'], explode(',', $index['moderator'])));
    } else {
      // เขียนใหม่ ตรวจสอบคนเขียน
      $canWrite = in_array($login['status'], explode(',', $index['can_write']));
    }
  }
  if (!$index) {
    // ไมพบบทความหรือโมดูล
    $title = $lng['PAGE_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } elseif (!$canWrite) {
    // ไม่สามารถเขียนหรือแก้ไขได้
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  } else {
    // title
    $m = ucwords($index['module']);
    $a = array();
    $a[] = '<span class=icon-documents>'.ucwords($index['owner']).'</span>';
    $a[] = '<a href="{URLQUERY?module=document-config&id='.$index['module_id'].'}">'.$m.'</a>';
    $a[] = '<a href="{URLQUERY?module=document-setup&id='.$index['module_id'].'}" title="{LNG_CONTENTS}">{LNG_CONTENTS}</a>';
    if ($id > 0) {
      // โหลดข้อมูลอื่นๆที่แก้ไข
      $sql = "SELECT `language`,`topic`,`keywords`,`relate`,`description`,`detail` FROM `".DB_INDEX_DETAIL."`";
      $sql .= " WHERE `id`='$index[id]' AND `module_id`='$index[module_id]'";
      foreach ($db->customQuery($sql) AS $i => $item) {
        $item['language'] = ($i == 0 && $item['language'] == '') ? $config['languages'][0] : $item['language'];
        $datas[$item['language']] = $item;
      }
      $a[] = '{LNG_EDIT}';
      $title = "$lng[LNG_EDIT] $lng[LNG_CONTENTS] $m";
    } else {
      $a[] = '{LNG_ADD}';
      $title = "$lng[LNG_ADD] $lng[LNG_CONTENTS] $m";
    }
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-write>'.$title.'</h1>';
    $content[] = '<div class=inline><div class=writetab>';
    // menu
    $content[] = '<ul id=accordient_menu>';
    foreach ($config['languages'] AS $item) {
      $content[] = '<li><a id=tab_detail_'.$item.' href="{URLQUERY?module='.$owner.'-write&qid='.$index['id'].'&tab=detail_'.$item.'}">{LNG_DETAIL}&nbsp;<img src='.DATA_URL.'language/'.$item.'.gif alt='.$item.'></a></li>';
    }
    $content[] = '<li><a id=tab_options href="{URLQUERY?module='.$owner.'-write&qid='.$index['id'].'&tab=options}">{LNG_OTHER_DETAILS}</a></li>';
    $content[] = '</ul>';
    $content[] = '</div></div>';
    $content[] = '</header>';
    // ฟอร์มเขียน-แก้ไข
    $content[] = '<form id=setup_frm class="setup_frm accordion" method=post action=index.php>';
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
      // relate
      $content[] = '<div class=item>';
      $content[] = '<label for=write_relate_'.$language.'>{LNG_RELATE}</label>';
      $content[] = '<span class="g-input icon-document"><input type=text id=write_relate_'.$language.' name=write_relate_'.$language.' value="'.$item['relate'].'" maxlength=64 stitle="{LNG_RELATE_COMMENT}"></span>';
      $content[] = '<div class=comment id=result_write_relate_'.$language.'>{LNG_RELATE_COMMENT}</div>';
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
    $content[] = '<fieldset id=options>';
    $content[] = '<legend><span>{LNG_DETAIL_OPTIONS}</span></legend>';
    // alias
    $content[] = '<div class=item>';
    $content[] = '<label for=write_alias>{LNG_ALIAS}</label>';
    $content[] = '<span class="g-input icon-world"><input type=text id=write_alias name=write_alias value="'.$index['alias'].'" maxlength=64 title="{LNG_ALIAS_COMMENT}"></span>';
    $content[] = '<div class=comment id=result_write_alias>{LNG_ALIAS_COMMENT}</div>';
    $content[] = '</div>';
    // create_date
    preg_match('/([0-9]{4,4}\-[0-9]{2,2}\-[0-9]{2,2})\s([0-9]+):([0-9]+)/', date('Y-m-d H:i', $index['create_date']), $match);
    $content[] = '<div class=item>';
    $content[] = '<label for=write_create_date>{LNG_ARTICLE_DATE}</label>';
    $content[] = '<div class="table collapse">';
    $content[] = '<div class=td>{LNG_DATE}&nbsp;</div>';
    $content[] = '<div class=td><span class="g-input icon-calendar"><input type=date id=write_create_date name=write_create_date value="'.$match[1].'" title="{LNG_ARTICLE_DATE_COMMENT}"></span></div>';
    $content[] = '<label class=td>&nbsp;{LNG_TIME}&nbsp;<select name=write_create_hour title="{LNG_HOUR}">';
    for ($i = 0; $i < 24; $i++) {
      $d = sprintf('%02d', $i);
      $sel = $d == $match[2] ? ' selected' : '';
      $content[] = '<option value='.$d.$sel.'>'.$d.'</option>';
    }
    $content[] = '</select></label>';
    $content[] = '<label class=td>&nbsp;:&nbsp;<select name=write_create_minute title="{LNG_MINUTE}">';
    for ($i = 0; $i < 60; $i++) {
      $d = sprintf('%02d', $i);
      $sel = $d == $match[3] ? ' selected' : '';
      $content[] = '<option value='.$d.$sel.'>'.$d.'</option>';
    }
    $content[] = '</select></label>';
    $content[] = '</div>';
    $content[] = '<div class=comment>{LNG_ARTICLE_DATE_COMMENT}</div>';
    $content[] = '</div>';
    if ($index['img_typies'] != '') {
      // picture
      $content[] = '<div class=item>';
      $t = str_replace(array('{T}', '{W}', '{H}'), array($index['img_typies'], $index['icon_width'], $index['icon_height']), $lng['LNG_IMAGE_UPLOAD_COMMENT']);
      $image = is_file(DATA_PATH."document/$index[picture]") ? DATA_URL."document/$index[picture]" : WEB_URL."/$index[default_icon]";
      $content[] = '<div class=usericon><span><img src="'.$image.'" alt=Thumbnail id=imgLogo></span></div>';
      $content[] = '<label for=write_picture>{LNG_THUMBNAIL}</label>';
      $content[] = '<span class="g-input icon-upload"><input class=g-file type=file id=write_picture name=write_picture title="'.$t.'" accept="'.gcms::getEccept(explode(',', $index['img_typies'])).'" data-preview=imgLogo></span>';
      $content[] = '<div class=comment id=result_write_picture>'.$t.'</div>';
      $content[] = '</div>';
    }
    // category
    $content[] = '<div class=item>';
    $content[] = '<label for=category_'.$index['module_id'].'>{LNG_CATEGORY}</label>';
    $content[] = '<span class="g-input icon-category"><select id=category_'.$index['module_id'].' name=write_category title="{LNG_CATEGORY_SELECT}">';
    $content[] = '<option value=0>{LNG_NO_CATEGORY}</option>';
    $sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]' ORDER BY `category_id`";
    foreach ($db->customQuery($sql) AS $item) {
      $sel = $index['category_id'] == $item['category_id'] ? ' selected' : '';
      $content[] = '<option value='.$item['category_id'].$sel.'>'.gcms::ser2Str($item['topic']).'</option>';
    }
    $content[] = '</select></span>';
    $content[] = '<div class=comment id=result_category_'.$index['module_id'].'>{LNG_CATEGORY_SELECT}</div>';
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
    // published date
    $content[] = '<div class=item>';
    $content[] = '<label for=write_published_date>{LNG_PUBLISHED_DATE}</label>';
    $content[] = '<span class="g-input icon-calendar"><input type=date id=write_published_date name=write_published_date value="'.$index['published_date'].'" title="{LNG_PUBLISHED_DATE_COMMENT}"></span>';
    $content[] = '<div class=comment>{LNG_PUBLISHED_DATE_COMMENT}</div>';
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
    $content[] = '</fieldset>';
    // submit
    $content[] = '<fieldset class=submit>';
    $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
    $content[] = '<input type=button id=write_open class="button large preview" value="{LNG_PREVIEW}">';
    $content[] = '<input type=hidden id=write_id name=write_id value='.(int)$index['id'].'>';
    $content[] = '<input type=hidden name=module_id value='.(int)$index['module_id'].'>';
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
      if (is_dir(ROOT_PATH.'ckfinder/')) {
        $content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html",';
        $content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Images",';
        $content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Flash",';
        $content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files",';
        $content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images",';
        $content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash"';
      } else {
        $connector = urlencode(WEB_URL.'/ckeditor/filemanager/connectors/php/connector.php');
        $content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Connector='.$connector.'",';
        $content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector='.$connector.'",';
        $content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector='.$connector.'",';
        $content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php",';
        $content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php?Type=Image",';
        $content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.phpType=Flash"';
      }
      $content[] = '});';
    }
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'new GForm("setup_frm","'.WEB_URL.'/modules/document/admin_write_save.php").onsubmit(doFormSubmit);';
    $content[] = 'checkSaved("write_open", "'.WEB_URL.'/index.php?module='.$index['module'].'", "write_id");';
    $content[] = 'new GValidator("write_alias", "keyup,change", checkAlias, "'.WEB_URL.'/modules/document/checkalias.php", null, "setup_frm");';
    $content[] = 'selectChanged("category_'.$index['module_id'].'","'.WEB_URL.'/modules/index/admin_category_action.php",doFormSubmit);';
    $content[] = 'inintWriteTab("accordient_menu", "'.$tab.'");';
    $content[] = '});';
    $content[] = '</script>';
    // หน้านี้
    $url_query['module'] = 'document-write';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
