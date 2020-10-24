<?php
// admin/skin.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // ภาษาของ skin
  if (is_file(ROOT_PATH.SKIN.'language/'.LANGUAGE.'.php')) {
    include_once (ROOT_PATH.SKIN.'language/'.LANGUAGE.'.php');
  } elseif (is_file(ROOT_PATH.SKIN.'language/th.php')) {
    include_once (ROOT_PATH.SKIN.'language/th.php');
  }
  // title
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '<a href="index.php?module=template">{LNG_TEMPLATE}</a>';
  $a[] = '{LNG_SKIN_SETTINGS}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header>';
  $content[] = '<h1 class=icon-template>{LNG_THEME_SETTINGS}</h1>';
  $content[] = '</header>';
  // ไตเติล
  $title = $lng['LNG_THEME_SETTINGS'];
  // ฟอร์ม setup ของ skin (default)
  $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span>{LNG_SKIN_SETTINGS_TITLE}</span></legend>';
  // logo
  $content[] = '<div class=item>';
  $image = is_file(DATA_PATH."image/$config[logo]") ? DATA_URL."image/$config[logo]" : '../skin/img/blank.gif';
  $content[] = '<div class=usericon><span><img src="'.$image.'" alt=logo id=logoDemo></span></div>';
  $content[] = '<label for=input_logo>{LNG_UPLOAD_LOGO}</label>';
  $content[] = '<span class="table g-input icon-upload"><input class=g-file type=file name=logo id=input_logo title="'.strip_tags($lng['LNG_UPLOAD_LOGO_COMMENT']).'" placeholder="{LNG_UPLOAD_LOGO}" data-preview=logoDemo></span>';
  $content[] = '<div class=comment id=result_input_logo>{LNG_UPLOAD_LOGO_COMMENT}</div>';
  $content[] = '<label><input type=checkbox id=delete_logo name=delete_logo>&nbsp;{LNG_REMOVE_LOGO}</label>';
  $content[] = '</div>';
  // bg_image
  $content[] = '<div class=item>';
  $image = is_file(DATA_PATH."image/$config[bg_image]") ? DATA_URL."image/$config[bg_image]" : '../skin/img/blank.gif';
  $content[] = '<div class=usericon><span><img src="'.$image.'" alt=logo id=bgDemo></span></div>';
  $content[] = '<label for=bg_image>{LNG_UPLOAD_BACKGROUND}</label>';
  $content[] = '<span class="table g-input icon-upload"><input class=g-file type=file name=bg_image id=bg_image title="'.strip_tags($lng['LNG_UPLOAD_BACKGROUND_COMMENT']).'" placeholder="{LNG_UPLOAD_BACKGROUND}" data-preview=bgDemo></span>';
  $content[] = '<div class=comment id=result_bg_image>{LNG_UPLOAD_BACKGROUND_COMMENT}</div>';
  $content[] = '<label><input type=checkbox id=delete_bg_image name=delete_bg_image>&nbsp;{LNG_REMOVE_BG_IMAGE}</label>';
  $content[] = '</div>';
  // bg_color
  $content[] = '<div class=item>';
  $content[] = '<div>';
  $color = $config['bg_color'] == '' ? ' ' : " value=$config[bg_color] ";
  $content[] = '<label for=bg_color>{LNG_BG_COLOR}</label>';
  $content[] = '<span class="table g-input icon-color"><input type=text class=color name=bg_color id=bg_color'.$color.'title="{LNG_BG_COLOR_COMMENT}"></span>';
  $content[] = '</div>';
  $content[] = '<div class=comment id=result_bg_color>{LNG_BG_COLOR_COMMENT}</div>';
  $content[] = '<label><input type=checkbox id=delete_bg_color name=delete_bg_color>&nbsp;{LNG_REMOVE_BG_COLOR}</label>';
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
  $content[] = 'new GForm("setup_frm","'.WEB_URL.'/admin/saveconfig.php").onsubmit(doFormSubmit);';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'skin';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
