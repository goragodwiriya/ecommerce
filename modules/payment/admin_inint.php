<?php
// modules/payment/admin_inint.php
if (MAIN_INIT == 'admin' && $isAdmin && $install_modules['payment']['owner'] != 'payment') {
  // เมนูติดตั้ง
  $admin_menus['tools']['install']['payment'] = '<a href="index.php?module=install&amp;modules=payment"><span>Payment</span></a>';
}