<?php
// modules/payment/sigin.php
if (defined('MAIN_INIT') && $index) {
  // antispam
  $register_antispamchar = gcms::rndname(32);
  $_SESSION[$register_antispamchar] = gcms::rndname(4);
  // แสดงผล
  $patt = array('/{WEBURL}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{ANTISPAM}/', '/{ANTISPAMCHAR}/', '/{MODULE}/', '/{FACEBOOK}/');
  $replace = array();
  $replace[] = WEB_URL;
  $replace[] = 'gcms::getLng';
  $replace[] = $register_antispamchar;
  $replace[] = $isAdmin ? $_SESSION[$register_antispamchar] : '';
  $replace[] = $index['owner'];
  $replace[] = ($config['facebook']['appId'] == '' || $config['facebook']['secret'] == '') ? 'hidden' : '';
  $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['owner'], 'payment', 'sigin'));
  // title
  $title = $lng['LNG_CUSTOMER_LOGIN_TITLE'];
} else {
  $title = $lng['PAGE_NOT_FOUND'];
  $content = '<div class=error>'.$title.'</div>';
}
