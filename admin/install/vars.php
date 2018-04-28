<?php
// bin/vars.php
// โฟลเดอร์สำหรับเก็บไอคอนของสมาชิก
define('USERICON_PATH', DATA_FOLDER.'member/');
// นับจาก root ของ server
define('USERICON_FULLPATH', ROOT_PATH.USERICON_PATH);
// เวอร์ชั่นของ gcms
define('VERSION', $version);
// ชื่อตัวแปรสำหรับเติมค่าตัวแปรต่างๆ
// เช่น session หรือ db
// เพื่อให้เป็นตัวแปรเฉพาะของเว็บไซต์เท่านั้น
define('PREFIX', 'gcms');
// ชื่อตารางฐานข้อมูลพื้นฐานต่างๆ
// ตารางสมาชิก
define('DB_USER', PREFIX.'_user');
// ตารางเนื้อหา
define('DB_MODULES', PREFIX.'_modules');
define('DB_INDEX', PREFIX.'_index');
define('DB_INDEX_DETAIL', PREFIX.'_index_detail');
define('DB_MENUS', PREFIX.'_menus');
define('DB_COMMENT', PREFIX.'_comment');
define('DB_CATEGORY', PREFIX.'_category');
define('DB_BOARD_R', PREFIX.'_board_r');
define('DB_BOARD_Q', PREFIX.'_board_q');
// ตาราง ภาษา
define('DB_LANGUAGE', PREFIX.'_language');
// ตาราง Email
define('DB_EMAIL_TEMPLATE', PREFIX.'_emailtemplate');
// ตาราง counter
define('DB_COUNTER', PREFIX.'_counter');
// ตาราง useronline
define('DB_USERONLINE', PREFIX.'_useronline');
// ตำบล อำเภอ จังหวัด
define('DB_PROVINCE', PREFIX.'_province');
define('DB_DISTRICT', PREFIX.'_district');
define('DB_TAMBON', PREFIX.'_tambon');
define('DB_ZIPCODE', PREFIX.'_zipcode');
define('DB_COUNTRY', PREFIX.'_country');
// ตารางอื่นๆ