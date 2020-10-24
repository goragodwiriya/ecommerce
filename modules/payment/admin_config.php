<?php
// modules/payment/admin_config.php
if (MAIN_INIT == 'admin' && gcms::canConfig($config['payment_can_config'])) {
  // title
  $title = "$lng[LNG_CONFIG] $lng[LNG_PAYMENT]";
  $a = array();
  $a[] = '<span class=icon-payment>{LNG_MODULES}</span>';
  $a[] = '{LNG_PAYMENT}';
  $a[] = '{LNG_CONFIG}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-config>'.$title.'</h1></header>';
  // form
  $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
  // payment_method
  $content[] = '<fieldset id=payment_method>';
  $content[] = '<legend><span>{LNG_PAYMENT_METHOD_SECTION}</span></legend>';
  $content[] = '<div class=item>';
  for ($i = 0; $i < max(1, sizeof($config['payments_method'])); $i++) {
    $item = $config['payments_method'][$i];
    $icon = is_file(ROOT_PATH.$item[1]) ? WEB_URL.'/'.$item[1] : WEB_URL.'/modules/payment/img/bank.png';
    $row = '<div class=input-groups-table id=pm_'.$i.'>';
    $row .= '<label class=width for=method_'.$i.'>{LNG_PAYMENT_METHOD} '.($i + 1).'</label>';
    $row .= '<span class=width><img src="'.$icon.'" id=method_img_'.$i.' alt=bank></span>';
    $row .= '<span class="width g-input icon-edit"><input type=text id=method_'.$i.' name=method[] value="'.htmlspecialchars(stripslashes($item[0])).'" title="{LNG_PLEASE_FILL}" size=60></span>';
    $row .= '<label class=width><input type=file id=method_file_'.$i.' name=method_file[] title="{LNG_PLEASE_SELECT}"></label>';
    $row .= '</div>';
    $content[] = $row;
  }
  $content[] = '<div class=comment id=result_method_0>{LNG_PAYMENT_METHOD_COMMENT}</div>';
  $content[] = '</div>';
  $content[] = '<div class=submit>';
  $content[] = '<a id=pm_add class="button large add"><span class=icon-add>{LNG_ADD} {LNG_PAYMENT_METHOD}</span></a>';
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
  $content[] = '<th scope=col class=col2>{LNG_SALESMAN}</th>';
  $content[] = '<th scope=col>{LNG_CAN_CONFIG}</th>';
  $content[] = '</tr>';
  $content[] = '</thead>';
  $content[] = '<tbody>';
  // สถานะสมาชิก
  $status = array();
  foreach ($config['member_status'] AS $i => $item) {
    if ($i > 1) {
      $bg = $bg == 'bg1' ? 'bg2' : 'bg1';
      $content[] = '<tr class="'.$bg.' status'.$i.'">';
      $content[] = '<th>'.$item.'</th>';
      // payment_saleman
      $content[] = '<td><label data-text="{LNG_SALESMAN}" ><input type=checkbox name=config_saleman[]'.(is_array($config['payment_saleman']) && in_array($i, $config['payment_saleman']) ? ' checked' : '').' value='.$i.' title="{LNG_PAYMENT_SALEMAN_COMMENT}"></label></td>';
      // payment_can_config
      $content[] = '<td><label data-text="{LNG_CAN_CONFIG}" ><input type=checkbox name=config_can_config[]'.(is_array($config['payment_can_config']) && in_array($i, $config['payment_can_config']) ? ' checked' : '').' value='.$i.' title="{LNG_CAN_CONFIG_COMMENT}"></label></td>';
      $content[] = '</tr>';
    }
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
  $content[] = 'new GForm("setup_frm", "'.WEB_URL.'/modules/payment/admin_config_save.php").onsubmit(doFormSubmit);';
  $content[] = 'inintPaymentConfig("{LNG_PAYMENT_METHOD}", "{LNG_PLEASE_FILL}", "{LNG_PLEASE_SELECT}");';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'payment-config';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
