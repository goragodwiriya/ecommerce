<?php
// widgets/textlink/admin_inint.php
if (defined('MAIN_INIT') && $isAdmin && (!defined('DB_TEXTLINK') || $lng['LNG_TEXTLINK'] == '')) {
  $admin_menus['tools']['install']['textlink'] = '<a href="index.php?module=install&amp;widgets=textlink"><span>Text Links</span></a>';
  unset($admin_menus['widgets']['textlink']);
}
