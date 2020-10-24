<?php
// admin/other.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // title
  $title = $lng['LNG_CONFIG_OTHER_SETTINGS'];
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '{LNG_CONFIG_OTHER}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-config>'.$title.'</h1></header>';
  $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span>{LNG_CONFIG_OTHER_TITLE}</span></legend>';
  // member_reserv
  $content[] = '<div class=item>';
  $content[] = '<label for=member_reserv>{LNG_MEMBER_RESERVE}</label>';
  $content[] = '<span class="g-input icon-config"><textarea name=member_reserv id=member_reserv rows=6 cols=60 title="{LNG_MEMBER_RESERVE_COMMENT}" autofocus>'.@implode("\r\n", $config['member_reserv']).'</textarea></span>';
  $content[] = '<div class=comment id=result_member_reserv>{LNG_MEMBER_RESERVE_COMMENT}</div>';
  $content[] = '</div>';
  // wordrude
  $content[] = '<div class=item>';
  $content[] = '<label for=wordrude>{LNG_WORDRUDE}</label>';
  $content[] = '<span class="g-input icon-config"><textarea name=wordrude id=wordrude rows=6 cols=60 title="{LNG_WORDRUDE_COMMENT}">'.@implode("\r\n", $config['wordrude']).'</textarea></span>';
  $content[] = '<div class=comment id=result_wordrude>{LNG_WORDRUDE_COMMENT}</div>';
  $content[] = '</div>';
  // wordrude_replace
  $content[] = '<div class=item>';
  $content[] = '<label for=wordrude_replace>{LNG_WORDRUDE_REPLACE}</label>';
  $content[] = '<span class="g-input icon-config"><input type=text name=wordrude_replace id=wordrude_replace value="'.$config['wordrude_replace'].'" maxlength=3 title="'.strip_tags($lng['LNG_WORDRUDE_REPLACE_COMMENT']).'"></span>';
  $content[] = '<div class=comment id=result_wordrude_replace>{LNG_WORDRUDE_REPLACE_COMMENT}</div>';
  $content[] = '</div>';
  // counter_digit
  $content[] = '<div class=item>';
  $content[] = '<label for=counter_digit>{LNG_COUNTER_DIGIT}</label>';
  $content[] = '<span class="g-input icon-config"><select name=counter_digit id=counter_digit title="{LNG_COUNTER_DIGIT_COMMENT}">';
  for ($i = 3; $i < 8; $i++) {
    $sel = $i == $config['counter_digit'] ? 'selected' : '';
    $content[] = "<option value=$i $sel>$i</option>";
  }
  $content[] = '</select></span>';
  $content[] = '<div class=comment id=result_counter_digit>{LNG_COUNTER_DIGIT_COMMENT}</div>';
  $content[] = '</div>';
  // index_page_cache
  $content[] = '<div class=item>';
  $content[] = '<label for=index_page_cache>{LNG_CACHE}</label>';
  $content[] = '<div class="table collapse">';
  $content[] = '<div class=td><span class="g-input icon-config"><input type=number class=number min=0 max=999 name=index_page_cache id=index_page_cache value="'.$config['index_page_cache'].'" title="{LNG_CACHE_COMMENT}"></span></div>';
  $content[] = '<label class=td>&nbsp;<input type=button class="button clear" id=clear_cache value="{LNG_CACHE_CLEAR}"></label>';
  $content[] = '</div>';
  $content[] = '<div class=comment id=result_index_page_cache>{LNG_CACHE_COMMENT}</div>';
  $content[] = '</div>';
  // user_agent
  $content[] = '<div class=item>';
  $content[] = '<label for=user_agent>{LNG_USER_AGENT}</label>';
  $content[] = '<span class="g-input icon-config"><textarea name=user_agent id=user_agent rows=6 cols=60 title="'.strip_tags($lng['LNG_USER_AGENT_COMMENT']).'">'.@implode("\r\n", $config['user_agent']).'</textarea></span>';
  $content[] = '<div class=comment id=result_user_agent>{LNG_USER_AGENT_COMMENT}</div>';
  $content[] = '</div>';
  // cron
  $content[] = '<div class=item>';
  $content[] = '<label for=cron>{LNG_CRON}</label>';
  $content[] = '<span class="g-input icon-config"><select id=cron name=cron title="{LNG_CRON_COMMENT}">';
  foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
    $sel = $i == $config['cron'] ? ' selected' : '';
    $content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
  }
  $content[] = '</select></span>';
  $content[] = '</div>';
  // cron absolute path
  $content[] = '<div class=item>';
  $content[] = '<label for=cron_1>{LNG_CRON_1}</label>';
  $content[] = '<span class="g-input icon-world"><input id=cron_1 type=text readonly value="'.ROOT_PATH.'cron.php"></span>';
  $content[] = '</div>';
  // cron url
  $content[] = '<div class=item>';
  $content[] = '<label for=cron_2>{LNG_CRON_2}</label>';
  $content[] = '<span class="g-input icon-world"><input id=cron_2 type=text readonly value="'.WEB_URL.'/cron.php"></span>';
  $content[] = '<div class=comment>{LNG_CRON_COMMENT}</div>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span>{LNG_FTP_SETTINGS}</span></legend>';
  // ftp_host
  $content[] = '<div class=item>';
  $ftp_host = $config['ftp_host'] == '' ? $_SERVER['SERVER_ADDR'] : $config['ftp_host'];
  $content[] = '<label for=ftp_host>{LNG_HOST}</label>';
  $content[] = '<span class="g-input icon-world"><input type=text name=ftp_host id=ftp_host value="'.$ftp_host.'" title="{LNG_PLEASE_FILL}"></span>';
  $content[] = '<div class=comment id=result_ftp_host>{LNG_FTP_HOST_COMMENT}</div>';
  $content[] = '</div>';
  // ftp_root
  $content[] = '<div class=item>';
  $content[] = '<label for=ftp_root>{LNG_FTP_ROOT}</label>';
  $content[] = '<span class="g-input icon-config"><input type=text name=ftp_root id=ftp_root value="'.$config['ftp_root'].'" title="{LNG_PLEASE_FILL}"></span>';
  $content[] = '<div class=comment id=result_ftp_root>{LNG_FTP_ROOT_COMMENT}</div>';
  $content[] = '</div>';
  // ftp_port
  $content[] = '<div class=item>';
  $ftp_port = $config['ftp_port'] == '' ? 20 : $config['ftp_port'];
  $content[] = '<label for=ftp_port>{LNG_PORT}</label>';
  $content[] = '<span class="g-input icon-config"><input type=number name=ftp_port id=ftp_port value="'.$ftp_port.'" title="{LNG_PLEASE_FILL}"></span>';
  $content[] = '<div class=comment id=result_ftp_port>{LNG_FTP_PORT_COMMENT}</div>';
  $content[] = '</div>';
  // ftp_username
  $content[] = '<div class=item>';
  $content[] = '<label for=ftp_username>{LNG_USERNAME}</label>';
  $content[] = '<span class="g-input icon-user"><input type=text name=ftp_username id=ftp_username title="{LNG_PLEASE_FILL}" data-result=result_ftp></span>';
  $content[] = '</div>';
  // ftp_password
  $content[] = '<div class=item>';
  $content[] = '<label for=ftp_password>{LNG_PASSWORD}</label>';
  $content[] = '<span class="g-input icon-password"><input type=text name=ftp_password id=ftp_password title="{LNG_PLEASE_FILL}" data-result=result_ftp></span>';
  $content[] = '<div class=comment id=result_ftp>{LNG_FTP_SETTINGS_COMMENT}</div>';
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
  $content[] = 'callClick("clear_cache", clearCache);';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'other';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
