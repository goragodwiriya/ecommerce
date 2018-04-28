<?php
if (INSTALL_INIT == 'install') {
  $config['db_server'] = (string)$_POST['db_server'];
  $config['db_username'] = (string)$_POST['db_username'];
  $config['db_password'] = (string)$_POST['db_password'];
  $config['db_name'] = (string)$_POST['db_name'];
  $reply = (string)$_POST['reply'];
  $_SESSION['db_server'] = $config['db_server'];
  $_SESSION['db_username'] = $config['db_username'];
  $_SESSION['db_password'] = $config['db_password'];
  $_SESSION['db_name'] = $config['db_name'];
  $_SESSION['reply'] = $reply;
  $config['ftp_host'] = $_SESSION['ftp_host'];
  $config['ftp_username'] = $_SESSION['ftp_username'];
  $config['ftp_password'] = $_SESSION['ftp_password'];
  $config['ftp_root'] = $_SESSION['ftp_root'];
  $config['ftp_port'] = $_SESSION['ftp_port'];
  $prefix = (string)$_POST['prefix'];
  $password = $_SESSION['password'];
  $import = (int)$_POST['import'];
  $config['noreply_email'] = $_SESSION['reply'];
  // เรียกใช้งานฐานข้อมูล
  if ($_POST['newdb']) {
    // สร้าง database ใหม่
    $db = new sql($config['db_server'], $config['db_username'], $config['db_password'], '');
    $sql = "CREATE DATABASE IF NOT EXISTS `$config[db_name]` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
    $db->query($sql);
    $db->close();
  }
  $db = new sql($config['db_server'], $config['db_username'], $config['db_password'], $config['db_name']);
  if (!$db->connection()) {
    echo '<h2>ความผิดพลาดในการเชื่อมต่อกับฐานข้อมูล</h2>';
    echo '<p><em>ไม่สามารถเชื่อมต่อกับฐานข้อมูลของคุณได้ในขณะนี้</em> อาจเป็นไปได้ว่า&nbsp;&nbsp;<a href="http://gcms.in.th/index.php?module=howto&id=39" target="_setup"><img src="'.WEB_URL.'/admin/install/img/help.png" alt=help></a></p>';
    echo '<ul>';
    echo '<li>เซิร์ฟเวอร์ของฐานข้อมูลของคุณไม่สามารถใช้งานได้ในขณะนี้</li>';
    echo '<li>ไม่มีฐานข้อมูลที่ต้องการติดตั้ง ให้ลองเลือกให้โปรแกรมสร้างฐานข้อมูลให้</li>';
    if ($_POST['newdb']) {
      echo '<li>ไม่สามารถสร้างฐานข้อมูลได้ อาจเป็นเพราะคุณไม่มีสิทธิ์ ให้ลองเลือกใช้ฐานข้อมูลที่คุณมีอยู่ก่อนแล้ว</li>';
    }
    echo '</ul>';
    echo '<p>หากคุณไม่สามารถดำเนินการแก้ไขข้อผิดพลาดด้วยตัวของคุณเองได้ ให้ติดต่อผู้ดูแลระบบเพื่อขอข้อมูลที่ถูกต้อง</p>';
    echo '<p><a href="index.php?step=4" class=button>กลับไปลองใหม่</a></p>';
  } else {
    // โหลด config ของ widgets ที่ติดตั้ง
    $dir = ROOT_PATH."widgets/";
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
      if ($text != '.' && $text != '..') {
        if (is_file($dir.$text.'/default.config.php')) {
          $newconfig = array();
          include ($dir.$text.'/default.config.php');
          if (is_array($newconfig[$text])) {
            $config = array_merge($config, $newconfig[$text]);
          }
        }
      }
    }
    closedir($f);
    // โหลด config ของโมดูลที่ติดตั้ง
    $dir = ROOT_PATH."modules/";
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
      if ($text != '.' && $text != '..') {
        if (is_file($dir.$text.'/default.config.php')) {
          $newconfig = array();
          include ($dir.$text.'/default.config.php');
          if (is_array($newconfig[$text])) {
            $config = array_merge($config, $newconfig[$text]);
          }
        }
      }
    }
    closedir($f);
    // เรียกใช้งานฐานข้อมูล
    $db = new sql($config['db_server'], $config['db_username'], $config['db_password'], $config['db_name']);
    echo '<h2>ติดตั้ง GCMS เวอร์ชั่น '.$version.' เรียบร้อย</h2>';
    echo '<ol>';
    ob_flush();
    flush();
    gcms::saveConfig(ROOT_PATH.'bin/config.php', $config);
    echo '<li class="'.($f ? 'correct' : 'incorrect').'">Update file <b>config.php</b> ...</li>';
    ob_flush();
    flush();
    $datas = array();
    $datas[] = 'sitemap: '.WEB_URL.'/sitemap.xml';
    $datas[] = 'User-agent: *';
    $datas[] = 'Disallow: /bin/';
    $datas[] = 'Disallow: /ckeditor/';
    $datas[] = 'Disallow: /'.DATA_FOLDER;
    $datas[] = 'Disallow: /js/';
    $datas[] = 'Disallow: /language/';
    $datas[] = 'Disallow: /modules/';
    $datas[] = 'Disallow: /skin/';
    $datas[] = 'Disallow: /widgets/';
    $f = @fopen(ROOT_PATH.'robots.txt', 'wb');
    fwrite($f, implode("\n", $datas));
    fclose($f);
    echo '<li class="'.($f ? 'correct' : 'incorrect').'">Update file <b>robots.txt</b> ...</li>';
    ob_flush();
    flush();
    $dirs = array();
    $dir = ROOT_PATH."modules/";
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
      if ($text != '.' && $text != '..' && $text != 'index') {
        $dirs[$text] ++;
      }
    }
    closedir($f);
    $sqlfiles = array();
    $sqlfiles[] = ROOT_PATH.'admin/install/sql.php';
    foreach ($dirs AS $module => $i) {
      if (is_file($dir.$module.'/sql.php')) {
        $sqlfiles[] = $dir.$module.'/sql.php';
      }
    }
    if ($import == 1) {
      $sqlfiles[] = ROOT_PATH.'admin/install/datas.php';
    }
    $dir = ROOT_PATH."widgets/";
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
      if ($text != '.' && $text != '..') {
        if (is_file($dir.$text.'/sql.php')) {
          $sqlfiles[] = $dir.$text.'/sql.php';
        }
        if ($import == 1 && is_file($dir.$text.'/datas.php')) {
          $sqlfiles[] = $dir.$text.'/datas.php';
        }
      }
    }
    closedir($f);
    $defines = array();
    $dbs = array('DB_USER', 'DB_MODULES', 'DB_INDEX', 'DB_COMMENT', 'DB_CATEGORY', 'DB_COUNTER', 'DB_USERONLINE', 'DB_PROVINCE', 'DB_DISTRICT', 'DB_TAMBON', 'DB_ZIPCODE', 'DB_COUNTRY', 'DB_MENUS');
    // vars.php
    $fr = @file(ROOT_PATH.'bin/vars.php');
    if (is_array($fr)) {
      foreach ($fr AS $value) {
        if (preg_match('/^define\("(DB_[A-Z]+)"(.*)\);$/', trim($value), $match)) {
          if (!defined($match[1]) || !in_array($match[1], $dbs)) {
            $defines[$match[1]] = $match[0];
          }
        }
      }
    }
    foreach ($sqlfiles AS $folder) {
      $fr = file($folder);
      foreach ($fr AS $value) {
        $sql = str_replace(array('{prefix}', '{WEBMASTER}', '\r', '\n'), array($prefix, $_SESSION['email'], "\r", "\n"), trim($value));
        if ($sql != '') {
          if (preg_match('/^<\?.*\?>$/', $sql)) {
            // php code
          } elseif (preg_match('/^define\([\'"]([A-Z_]+)[\'"](.*)\);$/', $sql, $match)) {
            $defines[$match[1]] = $match[0];
          } elseif (preg_match('/DROP[\s]+TABLE[\s]+(IF[\s]+EXISTS[\s]+)?`?([a-z0-9_]+)`?/iu', $sql, $match)) {
            $ret = $db->query($sql);
            echo "<li class=\"".($ret ? 'correct' : 'incorrect')."\">DROP TABLE <b>$match[2]</b> ...</li>";
          } elseif (preg_match('/CREATE[\s]+TABLE[\s]+(IF[\s]+NOT[\s]+EXISTS[\s]+)?`?([a-z0-9_]+)`?/iu', $sql, $match)) {
            $ret = $db->query($sql);
            echo "<li class=\"".($ret ? 'correct' : 'incorrect')."\">CREATE TABLE <b>$match[2]</b> ...</li>";
          } elseif (preg_match('/ALTER[\s]+TABLE[\s]+`?([a-z0-9_]+)`?[\s]+ADD[\s]+`?([a-z0-9_]+)`?(.*)/iu', $sql, $match)) {
            // add column
            $sql = "SELECT * FROM `information_schema`.`columns` WHERE `table_schema`='$config[db_name]' AND `table_name`='$match[1]' AND `column_name`='$match[2]'";
            $search = $db->customQuery($sql);
            if (sizeof($search) == 1) {
              $sql = "ALTER TABLE `$match[1]` DROP COLUMN `$match[2]`";
              $db->query($sql);
            }
            $ret = $db->query($match[0]);
            if (sizeof($search) == 1) {
              echo "<li class=\"".($ret ? 'correct' : 'incorrect')."\">REPLACE COLUMN <b>$match[2]</b> to TABLE <b>$match[1]</b></li>";
            } else {
              echo "<li class=\"".($ret ? 'correct' : 'incorrect')."\">ADD COLUMN <b>$match[2]</b> to TABLE <b>$match[1]</b></li>";
            }
          } elseif (preg_match('/INSERT[\s]+INTO[\s]+`?([a-z0-9_]+)`?(.*)/iu', $sql, $match)) {
            $ret = $db->query($sql);
            if ($q != $match[1]) {
              $q = $match[1];
              echo "<li class=\"".($ret ? 'correct' : 'incorrect')."\">INSERT INTO <b>$match[1]</b> ...</li>";
            }
          } else {
            $db->query($sql);
          }
        }
      }
      ob_flush();
      flush();
    }
    $pass = md5($password.$_SESSION['email']);
    $sql = "INSERT INTO `".$prefix."_user` (`id`, `password`, `email`, `status`, `fb`, `admin_access`) VALUES (1,'$pass','".$_SESSION['email']."',1,'0','1');";
    $db->query($sql);
    // login
    $_SESSION['login']['id'] = 1;
    $_SESSION['login']['password'] = $password;
    $_SESSION['login']['email'] = $_SESSION['email'];
    $_SESSION['login']['status'] = 1;
    $_SESSION['login']['fb'] = 0;
    $_SESSION['login']['admin_access'] = 1;
    // var.php
    if (preg_match('/^(http(s)?:\/\/(.*))\/?$/uU', trim($_POST['db_weburl']), $match)) {
      $db_weburl = $match[1];
    } else {
      $db_weburl = WEB_URL;
    }
    $hostname = str_replace(array('http://', 'www.'), '', trim($_POST['hostname']));
    $_SESSION['db_weburl'] = $db_weburl;
    $_SESSION['hostname'] = $hostname;
    echo '<li class="'.(writeVar($defines) ? 'correct' : 'incorrect').'">Update file <b>vars.php</b> ...</li>';
    ob_flush();
    flush();
    // .htaccess
    $datas = array();
    $datas[] = 'Options -Indexes';
    $datas[] = '<IfModule mod_rewrite.c>';
    $datas[] = 'RewriteEngine On';
    $datas[] = 'RewriteBase /';
    if (isset($_POST['redirect']) && $hostname != '') {
      $datas[] = 'RewriteCond %{HTTP_HOST} ^'.$hostname.'$';
      $datas[] = 'RewriteRule ^(.*)$ http://www.'.$hostname.'/$1 [L,R=301]';
    }
    $datas[] = 'RewriteRule ^(feed|menu|sitemap|BingSiteAuth)\.(xml|rss)$ '.BASE_PATH.'$1.php [L,QSA]';
    $datas[] = 'RewriteRule ^(.*).rss$ '.BASE_PATH.'feed.php?module=$1 [L,QSA]';
    $datas[] = 'RewriteCond %{REQUEST_FILENAME} !-f';
    $datas[] = 'RewriteCond %{REQUEST_FILENAME} !-d';
    $datas[] = 'RewriteRule . '.BASE_PATH.'index.php [L,QSA]';
    $datas[] = '</IfModule>';
    $datas[] = '<IfModule mod_expires.c>';
    $datas[] = 'ExpiresActive On';
    $datas[] = 'ExpiresByType image/x-icon "access plus 2592000 seconds"';
    $datas[] = 'ExpiresByType image/jpeg "access plus 2592000 seconds"';
    $datas[] = 'ExpiresByType image/png "access plus 2592000 seconds"';
    $datas[] = 'ExpiresByType image/gif "access plus 2592000 seconds"';
    $datas[] = 'ExpiresByType application/x-shockwave-flash "access plus 2592000 seconds"';
    $datas[] = 'ExpiresByType text/html "access plus 3600 seconds"';
    $datas[] = 'ExpiresByType application/xhtml+xml "access plus 3600 seconds"';
    $datas[] = '</IfModule>';
    $datas[] = '<IfModule mod_headers.c>';
    $datas[] = '<FilesMatch "\.(ico|jpg|jpeg|png|gif|swf|tpl|eot|svg|ttf|woff)$">';
    $datas[] = 'Header set Cache-Control "max-age=29030400, public"';
    $datas[] = '</FilesMatch>';
    $datas[] = '</IfModule>';
    $f = @fopen(ROOT_PATH.'.htaccess', 'wb');
    if ($f) {
      fwrite($f, implode("\n", $datas));
      fclose($f);
    }
    echo '<li class='.($f ? 'correct' : 'incorrect').'>Update file <b>.htaccess</b> ...</li>';
    ob_flush();
    flush();
    // บันทึกไฟล์ภาษา
    if (!defined('DB_LANGUAGE')) {
      define('DB_LANGUAGE', PREFIX.'_language');
    }
    foreach (gcms::saveLanguage($prefix.'_language') AS $item) {
      @copy(ROOT_PATH."admin/install/img/$item.gif", DATA_PATH."language/$item.gif");
    }
    echo '<li class=correct>Install <strong>languages</strong> ...</li>';
    ob_flush();
    flush();
    if (@rename(ROOT_PATH.'admin/install/', ROOT_PATH."admin/$mmktime/")) {
      echo '<li class=correct>โฟลเดอร์ <i>admin/install/</i> ถูกเปลี่ยนชื่อเป็น <i>admin/'.$mmktime.'/</i></li>';
    } else {
      echo '<li class=correct>กรุณาลบโฟลเดอร์ <i>admin/install/</i> ก่อนดำเนินการต่อ</li>';
    }
    ob_flush();
    flush();
    echo '</ol>';
    echo '<p>การติดตั้งได้ดำเนินการเสร็จเรียบร้อย กรุณาเข้าระบบผู้ดูแลเพื่อตั้งค่าที่จำเป็นอื่นๆโดยใช้ขื่ออีเมล <em>'.$_SESSION['email'].'</em> และ รหัสผ่าน <em>'.$password.'</em></p>';
    echo '<p>คุณควรปรับ chmod ให้โฟลเดอร์ <em>'.DATA_FOLDER.'</em> เป็น 755 ก่อนดำเนินการต่อ (ถ้าคุณได้ทำการปรับ chmod ด้วยตัวเอง)</p>';
    echo '<p>หากคุณต้องการความช่วยเหลือ คุณสามารถ ติดต่อสอบถามได้ที่ <a href="http://www.goragod.com" target="_blank">http://www.goragod.com</a> หรือ <a href="http://gcms.in.th" target="_blank">http://gcms.in.th</a></p>';
    echo '<p><a href="'.WEB_URL.'/admin/index.php?module=system" class=button>เข้าระบบผู้ดูแล</a></p>';
  }
}
