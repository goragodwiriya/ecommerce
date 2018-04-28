<?php
	// bin/load.php
	// path
	$root_path = str_replace('/bin/load.php', '', str_replace('\\', '/', __FILE__));
	$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
	if ($document_root == '') {
		// windows server
		$basepath = end(explode('/', $root_path));
	} else {
		$a = strpos($root_path, $document_root);
		if ($a === false) {
			$basepath = '';
		} else {
			if ($a > 0) {
				$document_root = substr($document_root, $a);
			}
			$basepath = str_replace(array("$document_root/", $document_root), array('', ''), $root_path);
		}
	}
	$baseurl = $_SERVER['HTTP_HOST'];
	$baseurl = $baseurl == '' ? $_SERVER['SERVER_NAME'] : $baseurl;
	// root ของ server
	// เช่น D:/htdocs/gcms/
	define('ROOT_PATH', "$root_path/");
	// root ของ document
	// เช่น cms/
	define('BASE_PATH', ($basepath == '' ? '' : "$basepath/"));
	// url ของ server รวม path (ไม่มี / ปิดท้าย)
	// เช่น http://domain.tld/gcms
	if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
		define("WEB_URL", "https://$baseurl".($basepath == "" ? "" : "/$basepath"));
	} else {
		define("WEB_URL", "http://$baseurl".($basepath == "" ? "" : "/$basepath"));
	}
	// โฟลเดอร์สำหรับเก็บข้อมูลต่างๆ นับจาก root ของ server
	define('DATA_FOLDER', 'datas/');
	define('DATA_PATH', ROOT_PATH.DATA_FOLDER);
	define('DATA_URL', WEB_URL.'/'.DATA_FOLDER);
	// load variable
	if (is_file(ROOT_PATH.'bin/vars.php')) {
		include (ROOT_PATH.'bin/vars.php');
	}
	// ไฟล์ config
	define('CONFIG', ROOT_PATH.'bin/config.php');
	// ช่วงเวลาของ counter
	define('COUNTER_REFRESH_TIME', 30);
	// เวลาที่บอกว่า user logout
	// ควรมากกว่า counter_refresh_time อย่างน้อย 2 เท่า
	define('COUNTER_GAP', 60);
	// ชื่อสงวนสำหรับโมดูล ที่ไม่สามารถนำมาตั้งได้ นอกจากชื่อของโฟลเดอร์หรือไฟล์ต่างๆบนระบบ
	// ภาษาอังกฤษตัวพิมพ์เล็กเท่านั้น
	define('MODULE_RESERVE', 'admin,register,forgot,editprofile,sendpm,sendmail,email');
	// tab สำหรับ member
	$member_tabs = array();
	$member_tabs['editprofile'] = array('{LNG_MEMBER_PROFILE}', 'modules/member/editprofile');
	$member_tabs['password'] = array('{LNG_MEMBER_EDIT_PASSWORD}', 'modules/member/editprofile');
	$member_tabs['address'] = array('{LNG_ADDRESS_DETAIL}', 'modules/member/editprofile');
	// language
	$language = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['gcms_language']) ? $_SESSION['gcms_language'] : $_COOKIE['gcms_language']);
	$language = is_file(DATA_PATH."language/$language.php") ? $language : 'th';
	setCookie('gcms_language', $language, time() + 3600 * 24 * 365);
	$_SESSION['gcms_language'] = $language;
	// ภาษาที่เลือก
	define('LANGUAGE', $language);
	// รายการโมดูลที่ติดตั้งแล้วทั้งหมด เรียงตามลำดับเมนู
	$install_modules = array();
	// รายการโมดูลที่ติดตั้ง เรียงตาม owner
	$install_owners = array();
	// รายชื่อโมดูลที่ติดตั้งแล้ว
	$module_list = array();
	// รายชื่อ module และ owner ที่ติดตั้งแล้ว
	$owner_list = array();
	// รายชื่อของหน้าสมาชิกต่างๆ (ไม่สามารถใช้เป็นชื่อโมดูลได้)
	$member_modules = array('login', 'dologin', 'register', 'forgot', 'editprofile', 'sendmail', 'unsubscrib');
	// รายชื่อที่อนุญาติให้ใช้เป็นชื่อโมดูลได้
	$allow_module = array('news', 'contact');
	// config
	$config = array();
	if (is_file(CONFIG)) {
		include CONFIG;
	}
	// โหลดไฟล์ภาษา
	if (is_file(DATA_PATH."language/$language.php")) {
		include DATA_PATH."language/$language.php";
	}
	// connect database class
	if (is_file(ROOT_PATH.'bin/class.pdo.php') && defined('PDO::ATTR_DRIVER_NAME')) {
		// database driver สำหรับ PDO
		define('DATABASE_DRIVER', 'mysql');
		include ROOT_PATH.'bin/class.pdo.php';
	} elseif (is_file(ROOT_PATH.'bin/class.mysqli.php') && function_exists('mysqli_connect')) {
		include ROOT_PATH.'bin/class.mysqli.php';
	} else {
		include ROOT_PATH.'bin/class.mysql.php';
	}
	// gcms class
	include ROOT_PATH.'bin/class.gcms.php';
	// ftp class
	include ROOT_PATH.'bin/class.ftp.php';
	// cache class
	include ROOT_PATH.'bin/class.cache.php';
	// เรียกใช้งาน ftp
	$ftp = new ftp($config['ftp_host'], $config['ftp_username'], $config['ftp_password'], $config['ftp_root'], $document_root, $config['ftp_port']);
	if ($config['skin'] != '' && $config['db_username'] != '' && $config['db_name'] != '') {
		// เรียกใช้งานฐานข้อมูล
		$db = new sql($config['db_server'], $config['db_username'], $config['db_password'], $config['db_name']);
		// เริ่มต้นจับเวลาการประมวลผล
		$time = $db->timer_start();
		// cache
		$cache = new gcmsCache(DATA_PATH.'cache/', $config['index_page_cache'], $ftp);
	}
	// โฟลเดอร์ของ template
	define('SKIN', "skin/$config[skin]/");
