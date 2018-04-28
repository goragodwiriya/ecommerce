<?php
// modules/product/inint.php
if ($install_owners['product']) {
  // หน่วยเงิน
  $currency = isset($_REQUEST['curr']) ? strtoupper($_REQUEST['curr']) : $_SESSION['currency'];
  $currency = isset($lng['CURRENCY_UNITS'][$currency]) ? $currency : 'THB';
  $_SESSION['currency'] = $currency;
  // member menu
  $member_tabs['product'] = array('{LNG_PAYMENT_HISTORY}', 'modules/product/member');
  $member_tabs['order'] = array('', 'modules/product/order');
}
