<?php
// modules/product/inint.php
if ($install_owners['product']) {
    // หน่วยเงิน
    if (isset($_REQUEST['curr'])) {
        $currency = strtoupper($_REQUEST['curr']);
    } elseif (isset($_SESSION['currency'])) {
        $currency = $_SESSION['currency'];
    } else {
        $currency = 'THB';
    }
    $currency = isset($lng['CURRENCY_UNITS'][$currency]) ? $currency : 'THB';
    $_SESSION['currency'] = $currency;
    // member menu
    $member_tabs['product'] = array('{LNG_PAYMENT_HISTORY}', 'modules/product/member');
    $member_tabs['order'] = array('', 'modules/product/order');
}
