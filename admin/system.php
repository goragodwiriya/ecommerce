<?php
// admin/system.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // title
  $title = $lng['LNG_SITE_SETTINGS_TITLE'];
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '{LNG_GENERAL}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-index>'.$title.'</h1></header>';
  $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php autocomplete=off>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span>{LNG_GENERAL}</span></legend>';
  // web_title
  $content[] = '<div class=item>';
  $content[] = '<label for=web_title>{LNG_WEB_TITLE}</label>';
  $content[] = '<span class="g-input icon-home"><input id=web_title name=web_title type=text value="'.$config['web_title'].'" title="{LNG_WEB_TITLE_COMMENT}" maxlength=64 autofocus></span>';
  $content[] = '<div class=comment id=result_web_title>{LNG_WEB_TITLE_COMMENT}</div>';
  $content[] = '</div>';
  // web_description
  $content[] = '<div class=item>';
  $content[] = '<label for=web_description>{LNG_DESCRIPTION}</label>';
  $content[] = '<span class="g-input icon-home"><input id=web_description name=web_description type=text value="'.$config['web_description'].'" title="{LNG_WEB_DESCRIPTION_COMMENT}" maxlength=156></span>';
  $content[] = '<div class=comment id=result_web_description>{LNG_WEB_DESCRIPTION_COMMENT}</div>';
  $content[] = '</div>';
  // use_ajax
  $content[] = '<div class=item>';
  $content[] = '<label for=use_ajax>{LNG_USE_AJAX}</label>';
  $content[] = '<span class="g-input icon-config"><select id=use_ajax name=use_ajax title="{LNG_USE_AJAX_COMMENT}">';
  foreach ($lng['LNG_USE_AJAX_LIST'] AS $i => $value) {
    $sel = $i == $config['use_ajax'] ? 'selected' : '';
    $content[] = "<option value=$i $sel>$value</option>";
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_use_ajax>{LNG_USE_AJAX_COMMENT}</div>';
  $content[] = '</div>';
  // module_url
  $content[] = '<div class=item>';
  $content[] = '<label for=module_url>{LNG_MODULE_SITE_URL}</label>';
  $content[] = '<span class="g-input icon-world"><select id=module_url name=module_url title="{LNG_MODULE_SITE_URL_COMMENT}">';
  foreach ($lng['LNG_MODULE_SITE_URLS'] AS $i => $value) {
    $sel = $i == $config['module_url'] ? 'selected' : '';
    $content[] = "<option value=$i $sel>$value</option>";
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_module_url>{LNG_MODULE_SITE_URL_COMMENT}</div>';
  $content[] = '</div>';
  // hour
  $content[] = '<div class=item>';
  $content[] = '<label for=hour>{LNG_TIME_ZONE}</label>';
  $content[] = '<div class="table collapse">';
  $content[] = '<div class=td><span class="g-input icon-clock"><select id=hour name=hour title="{LNG_TIME_ZONE_COMMENT}">';
  for ($i = -12; $i < 13; $i++) {
    $sel = $i == $config['hour'] ? 'selected' : '';
    $content[] = "<option value=$i $sel>".($i >= 0 ? "+$i" : $i)."</option>";
  }
  $content[] = '</select></span></div>';
  $content[] = '<div class=td>&nbsp;{LNG_SERVER_TIME}&nbsp;<em id=server_time>'.date('H:i:s').'</em>&nbsp;{LNG_LOCAL_TIME}&nbsp;<em id=local_time></em></div>';
  $content[] = '</div>';
  $content[] = '<div class=comment>{LNG_TIME_ZONE_COMMENT}</div>';
  $content[] = '</div>';
  // demo
  $content[] = '<div class=item>';
  $content[] = '<label for=demo_mode>{LNG_EX}</label>';
  $content[] = '<span class="g-input icon-config"><select id=demo_mode name=demo_mode title="{LNG_SITE_EX_COMMENT}">';
  foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
    $sel = $i == $config['demo_mode'] ? ' selected' : '';
    $content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment>{LNG_SITE_EX_COMMENT}</div>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  // user settings
  $content[] = '<fieldset>';
  $content[] = '<legend><span>{LNG_USER_SETTINGS}</span></legend>';
  // member_invitation
  $content[] = '<div class=item>';
  $content[] = '<div class=input-groups>';
  $content[] = '<div class=width33>';
  $content[] = '<label for=member_invitation>{LNG_INVITATION_CODE}</label>';
  $content[] = '<span class="g-input icon-flag"><select id=member_invitation name=member_invitation title="{LNG_MEMBER_OPTIONS_COMMENT}">';
  foreach ($lng['LNG_MEMBER_OPTIONS'] AS $i => $value) {
    if ($i < 2) {
      $sel = $i == $config['member_invitation'] ? ' selected' : '';
      $content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
    }
  }
  $content[] = '</select></span>';
  $content[] = '</div>';
  // member_phone
  $content[] = '<div class=width33>';
  $content[] = '<label for=member_phone>{LNG_PHONE}</label>';
  $content[] = '<span class="g-input icon-phone"><select id=member_phone name=member_phone title="{LNG_MEMBER_OPTIONS_COMMENT}">';
  foreach ($lng['LNG_MEMBER_OPTIONS'] AS $i => $value) {
    $sel = $i == $config['member_phone'] ? ' selected' : '';
    $content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '</div>';
  // member_idcard
  $content[] = '<div class=width33>';
  $content[] = '<label for=member_idcard>{LNG_IDCARD}</label>';
  $content[] = '<span class="g-input icon-address"><select id=member_idcard name=member_idcard title="{LNG_MEMBER_OPTIONS_COMMENT}">';
  foreach ($lng['LNG_MEMBER_OPTIONS'] AS $i => $value) {
    $sel = $i == $config['member_idcard'] ? ' selected' : '';
    $content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '</div>';
  $content[] = '</div>';
  $content[] = '<div class=comment>{LNG_MEMBER_OPTIONS_COMMENT}</div>';
  $content[] = '</div>';
  // member_login_phone
  $content[] = '<div class=item>';
  $content[] = '<label for=member_login_phone>{LNG_LOGIN_BY_PHONE}</label>';
  $content[] = '<span class="g-input icon-phone"><select id=member_login_phone name=member_login_phone title="{LNG_LOGIN_BY_PHONE_COMMENT}">';
  foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
    $sel = $i == $config['member_login_phone'] ? ' selected' : '';
    $content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_member_login_phone>{LNG_LOGIN_BY_PHONE_COMMENT}</div>';
  $content[] = '</div>';
  // user_icon_typies
  $content[] = '<div class=item>';
  $content[] = '<label for=user_icon_typies>{LNG_USERS_ICON}</label>';
  $content[] = '<div>';
  foreach (array('jpg', 'gif', 'png') AS $i => $item) {
    $sel = is_array($config['user_icon_typies']) && in_array($item, $config['user_icon_typies']) ? ' checked' : '';
    $d = $item == 'png' ? ' id=user_icon_typies' : '';
    $content[] = '<label>'.$item.'&nbsp;<input type=checkbox name=user_icon_typies[] value='.$item.$sel.$d.' title="{LNG_USER_ICON_TYPIES_COMMENT}">&nbsp;</label>';
  }
  $content[] = '</div>';
  $content[] = '<div class=comment id=result_user_icon_typies>{LNG_USER_ICON_TYPIES_COMMENT}</div>';
  $content[] = '</div>';
  // user_icon_w, user_icon_h
  $content[] = '<div class=item>';
  $content[] = '<label for=user_icon_w>{LNG_SIZE_OF} {LNG_USERS_ICON}</label>';
  $content[] = '<div class=input-groups>';
  $content[] = '<div class=width25><label for=user_icon_w>{LNG_WIDTH}</label><span class="g-input icon-width"><input type=number class=number min=16 max=200 name=user_icon_w id=user_icon_w value="'.$config['user_icon_w'].'" title="{LNG_USER_ICON_SIZE_COMMENT}"></span></div>';
  $content[] = '<div class=width25><label for=user_icon_h>{LNG_HEIGHT}</label><span class="g-input icon-height"><input type=number class=number min=16 max=200 name=user_icon_h id=user_icon_h value="'.$config['user_icon_h'].'" title="{LNG_USER_ICON_SIZE_COMMENT}"></span></div>';
  $content[] = '</div>';
  $content[] = '<div class=comment>{LNG_USER_ICON_SIZE_COMMENT}</div>';
  $content[] = '</div>';
  // member_only_ip
  $content[] = '<div class=item>';
  $content[] = '<label for=member_only_ip>{LNG_LOGIN_ONLY_IP}</label>';
  $content[] = '<span class="g-input icon-ip"><select id=member_only_ip name=member_only_ip title="{LNG_LOGIN_ONLY_IP_COMMENT}">';
  foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
    $sel = $i == $config['member_only_ip'] ? ' selected' : '';
    $content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_member_only_ip>{LNG_LOGIN_ONLY_IP_COMMENT}</div>';
  $content[] = '</div>';
  // login_action
  $content[] = '<div class=item>';
  $content[] = '<label for=login_action>{LNG_LOGIN_ACTION}</label>';
  $content[] = '<span class="g-input icon-signin"><select id=login_action name=login_action title="{LNG_LOGIN_ACTION_COMMENT}">';
  foreach ($lng['LNG_LOGIN_ACTIONS'] AS $i => $value) {
    $sel = $i == $config['login_action'] ? ' selected' : '';
    $content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_login_action>{LNG_LOGIN_ACTION_COMMENT}</div>';
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
  $content[] = 'new GForm("setup_frm","saveconfig.php").onsubmit(doFormSubmit);';
  $content[] = 'new Clock("local_time");';
  $content[] = 'var t = new Clock("server_time");';
  $content[] = 't.hourOffset($E("hour").value);';
  $content[] = '$G("hour").addEvent("change", function(){t.hourOffset(this.value)});';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'system';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
