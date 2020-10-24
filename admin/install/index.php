<?php
if (defined('ROOT_PATH')) {
    // current version
    $version = '10.0.0';
    // header
    echo '<!DOCTYPE html>';
    echo '<html lang=TH dir=ltr>';
    echo '<head>';
    echo '<meta charset=utf-8>';
    if ((defined('VERSION') && version_compare(VERSION, '4.5.0') == -1) || empty($config['skin']) || empty($config['db_username']) || empty($config['db_name'])) {
        DEFINE('INSTALL_INIT', 'install');
        echo '<title>การติดตั้ง GCMS &rsaquo; Setup Configuration File</title>';
    } elseif (!$db->connection()) {
        DEFINE('INSTALL_INIT', 'install');
        echo '<title>การติดตั้ง GCMS &rsaquo; Setup Configuration File</title>';
    } elseif ((defined('VERSION') && version_compare(VERSION, $version) == -1) || is_dir(ROOT_PATH.'language/') || !defined('DB_MODULES')) {
        DEFINE('INSTALL_INIT', 'upgrade');
        echo '<title>การปรับรุ่น GCMS '.$version.' &rsaquo; Setup Configuration File</title>';
    } else {
        echo '<title>ติดตั้ง GCMS เวอร์ชั่น '.$version.' เรียบร้อย</title>';
    }
    echo '<link rel=stylesheet href="'.WEB_URL.'/admin/install/install.css">';
    echo '</head>';
    echo '<body>';
    echo '<h1 id=logo><em>G</em>CMS</h1>';
    ob_flush();
    flush();
    $step = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 0;
    if (INSTALL_INIT == 'install') {
        include ROOT_PATH."admin/install/default.config.php";
        if ($step > 0 && is_file(ROOT_PATH.'admin/install/install'.$step.'.php')) {
            include ROOT_PATH.'admin/install/install'.$step.'.php';
        } else {
            include ROOT_PATH."admin/install/install.php";
        }
    } elseif (INSTALL_INIT == 'upgrade') {
        $error = false;
        if ($step > 0 && is_file(ROOT_PATH.'admin/install/upgrade'.$step.'.php')) {
            include ROOT_PATH.'admin/install/upgrade'.$step.'.php';
        } else {
            include ROOT_PATH."admin/install/upgrade.php";
        }
    } else {
        echo '<h2>ติดตั้ง GCMS เวอร์ชั่น '.$version.' เรียบร้อย</h2>';
        echo '<p>คุณได้ทำการติดตั้ง GCMS เป็นที่เรียบร้อยแล้ว เพื่อความปลอดภัย กรุณาลบโฟลเดอร์ <em>admin/install/</em>';
        if (is_dir(ROOT_PATH.'language/')) {
            echo 'และโฟลเดอร์ <em>language/</em>';
        }
        echo ' ออกก่อนดำเนินการต่อ</p>';
        echo '<p><a href="'.WEB_URL.'/admin/index.php?module=system" class=button>เข้าระบบผู้ดูแล</a></p>';
    }
    // footer
    echo '<div class=footer>GCMS เป็นผลิตภัณฑ์ของ เว็บไซต์ <a href="http://www.goragod.com">Goragod.com</a> สงวนลิขสิทธิ์ ตามพระราชบัญญัติลิขสิทธิ์ พ.ศ. 2539</div>';
    echo '</body>';
    echo '</html>';
} else {
    header('Location: ../index.php');
}

// function
/**
 * @param $defines
 * @return mixed
 */
function writeVar($defines)
{
    global $version, $prefix;
    foreach (array(ROOT_PATH.'admin/install/vars.php', ROOT_PATH.'bin/vars.php') as $_var) {
        if (is_file($_var)) {
            $fr = file($_var);
            foreach ($fr as $value) {
                if (preg_match('/^define\([\'"]([A-Z_]+)[\'"](.*)\);$/', trim($value), $match)) {
                    $defines[$match[1]] = $match[0];
                }
            }
        }
    }
    // update vars.php
    unset($defines['ROOT_PATH']);
    unset($defines['BASE_PATH']);
    unset($defines['WEB_URL']);
    unset($defines['DATA_FOLDER']);
    unset($defines['DATA_PATH']);
    unset($defines['DATA_URL']);
    unset($defines['COUNTER_REFRESH_TIME']);
    unset($defines['COUNTER_GAP']);
    unset($defines['MODULE_RESERVE']);
    unset($defines['LANGUAGE']);
    unset($defines['SKIN']);
    // vars.php
    $datas = array();
    $datas[] = '<'.'?php';
    $datas[] = '// bin/vars.php';
    $datas[] = '// โฟลเดอร์สำหรับเก็บไอคอนของสมาชิก';
    $datas[] = getVar($defines, 'USERICON_PATH', "DATA_FOLDER.'member/'");
    $datas[] = '// นับจาก root ของ server';
    $datas[] = getVar($defines, 'USERICON_FULLPATH', "ROOT_PATH.USERICON_PATH");
    $datas[] = '// เวอร์ชั่นของ gcms';
    $datas[] = 'define(\'VERSION\', \''.$version.'\');';
    unset($defines['VERSION']);
    $datas[] = '// ชื่อตัวแปรสำหรับเติมค่าตัวแปรต่างๆ';
    $datas[] = '// เช่น session หรือ db';
    $datas[] = '// เพื่อให้เป็นตัวแปรเฉพาะของเว็บไซต์เท่านั้น';
    unset($defines['PREFIX']);
    $datas[] = 'define(\'PREFIX\', \''.$prefix.'\');';
    $datas[] = '// ชื่อตารางฐานข้อมูลพื้นฐานต่างๆ';
    $datas[] = '// ตารางสมาชิก';
    $datas[] = getVar($defines, 'DB_USER', 'PREFIX.\'_user\'');
    $datas[] = '// ตารางเนื้อหา';
    $datas[] = getVar($defines, 'DB_MODULES', 'PREFIX.\'_modules\'');
    $datas[] = getVar($defines, 'DB_INDEX', 'PREFIX.\'_index\'');
    $datas[] = getVar($defines, 'DB_INDEX_DETAIL', 'PREFIX.\'_index_detail\'');
    $datas[] = getVar($defines, 'DB_MENUS', 'PREFIX.\'_menus\'');
    $datas[] = getVar($defines, 'DB_COMMENT', 'PREFIX.\'_comment\'');
    $datas[] = getVar($defines, 'DB_CATEGORY', 'PREFIX.\'_category\'');
    $datas[] = getVar($defines, 'DB_BOARD_R', 'PREFIX.\'_board_r\'');
    $datas[] = getVar($defines, 'DB_BOARD_Q', 'PREFIX.\'_board_q\'');
    $datas[] = '// ตาราง ภาษา';
    $datas[] = getVar($defines, 'DB_LANGUAGE', 'PREFIX.\'_language\'');
    $datas[] = '// ตาราง Email';
    $datas[] = getVar($defines, 'DB_EMAIL_TEMPLATE', 'PREFIX.\'_emailtemplate\'');
    $datas[] = '// ตาราง counter';
    $datas[] = getVar($defines, 'DB_COUNTER', 'PREFIX.\'_counter\'');
    $datas[] = '// ตาราง useronline';
    $datas[] = getVar($defines, 'DB_USERONLINE', 'PREFIX.\'_useronline\'');
    $datas[] = '// ตำบล อำเภอ จังหวัด';
    $datas[] = getVar($defines, 'DB_PROVINCE', 'PREFIX.\'_province\'');
    $datas[] = getVar($defines, 'DB_DISTRICT', 'PREFIX.\'_district\'');
    $datas[] = getVar($defines, 'DB_TAMBON', 'PREFIX.\'_tambon\'');
    $datas[] = getVar($defines, 'DB_ZIPCODE', 'PREFIX.\'_zipcode\'');
    $datas[] = getVar($defines, 'DB_COUNTRY', 'PREFIX.\'_country\'');
    $datas[] = '// ค่าคีย์สำหรับการเข้ารหัส';
    $datas[] = getVar($defines, 'EN_KEY', gcms::rndname(4, '123456789'));
    $datas[] = '// ตารางอื่นๆ';
    foreach ($defines as $define) {
        $datas[] = $define;
    }
    $f = @fopen(ROOT_PATH.'bin/vars.php', 'wb');
    if ($f) {
        fwrite($f, implode("\n\t", $datas));
        fclose($f);
    }

    return $f;
}

/**
 * @param $defines
 * @param $a
 * @param $b
 * @return mixed
 */
function getVar(&$defines, $a, $b)
{
    if (isset($defines[$a])) {
        $ret = $defines[$a];
        unset($defines[$a]);

        return $ret;
    } else {
        return 'define(\''.$a.'\', '.$b.');';
    }
}

/**
 * @param $dir
 * @param $todir
 */
function copyDir($dir, $todir)
{
    global $ftp;
    $f = opendir($dir);
    $ftp->mkdir($todir, 0755);
    while (false !== ($text = readdir($f))) {
        if (!in_array($text, array('.', '..'))) {
            if (is_dir($dir.$text.'/')) {
                if (!in_array($text, array('cache', 'counter', 'language', '_thumbs'))) {
                    copyDir($dir.$text.'/', $todir.$text.'/');
                }
            } else {
                copy($dir.$text, $todir.$text);
            }
        }
    }
    closedir($f);
}
