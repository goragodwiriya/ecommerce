<?php
// load.php
if (defined('MAIN_INIT')) {
    if (is_dir(ROOT_PATH.'admin/install/') || (!empty($config['maintenance_mode']) && !gcms::isAdmin())) {
        $config['admin_skin'] = $config['admin_skin'] == '' ? 'v8' : $config['admin_skin'];
        // มีโฟลเดอร์ install เข้าสู่โหมดบำรุงรักษา
        $main_patt = array('/{TITLE}/', '/{CONTENT}/', '/{LANGUAGE}/', '/{WEBURL}/', '/{SKIN}/');
        $main_replace = array();
        $main_replace[] = strip_tags($config['web_title']);
        $main_replace[] = $lng['MAINTENANCE_DETAIL'] == '' ? '<p style="padding: 20px; text-align: center; font-weight: bold;">ปิดปรับปรุงเว็บไซต์ชั่วคราว กรุณาลองใหม่ในอีกสักครู่...</p>' : $lng['MAINTENANCE_DETAIL'];
        $main_replace[] = LANGUAGE;
        $main_replace[] = WEB_URL;
        $main_replace[] = SKIN;
        echo preg_replace($main_patt, $main_replace, gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/template.html"));
    } elseif (!empty($config['show_intro']) && str_replace(array(BASE_PATH, '/'), '', $_SERVER['REQUEST_URI']) == '') {
        $config['admin_skin'] = $config['admin_skin'] == '' ? 'v8' : $config['admin_skin'];
        // intro page
        $main_patt = array('/{TITLE}/', '/{CONTENT}/', '/{LANGUAGE}/', '/{WEBURL}/', '/{SKIN}/');
        $main_replace = array();
        $main_replace[] = strip_tags($config['web_title']);
        $main_replace[] = $lng['INTRO_PAGE_DETAIL'];
        $main_replace[] = LANGUAGE;
        $main_replace[] = WEB_URL;
        $main_replace[] = SKIN;
        echo preg_replace($main_patt, $main_replace, gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/template.html"));
    } else {
        // invite
        if (isset($_GET['invite'])) {
            setCookie(PREFIX.'_invite', $_GET['invite'], time() + 3600 * 24 * 30);
        }
        // query จาก URL ที่ส่งมา
        $urls = array();
        foreach ($_GET as $key => $value) {
            if (!in_array($key, array('action', 'lang', 'f', 'c'))) {
                $urls[$key] = "$key=$value";
            }
        }
        // แอเรย์สำหรับ ส่วนต่างๆ META, CSS และ Javasript, เมนูหลัก, เมนูข้าง
        $meta = array();
        // javascript
        $script = array();
        // เมนูหลัก
        foreach ($lng['MENU_PARENTS'] as $key => $value) {
            $mainmenu[$key] = array();
        }
        // กรุณาอย่าเอาออก
        $meta['generator'] = '<meta name=generator content="GCMS AJAX CMS design by http://gcms.in.th">';
        $meta['rss'] = '<link rel=alternate type="application/rss+xml" title="'.sprintf($lng['LNG_RSS_MENU'], strip_tags($config['web_title'])).'" href="'.WEB_URL.'/menu.rss">';
        if (!empty($config['logo']) && is_file(DATA_PATH.'image/'.$config['logo'])) {
            $logo = DATA_URL.'image/'.$config['logo'];
        } else {
            $logo = '';
        }
        // image_src
        if ($logo == '' && is_file(DATA_PATH.'image/facebook_photo.jpg')) {
            $image_src = DATA_URL.'image/facebook_photo.jpg';
        } else {
            $image_src = $logo;
        }
        // canonical
        $canonical = WEB_URL.'/index.php';
        // โมดูลที่เรียกมา
        $module = '';
        if (isset($_REQUEST['module'])) {
            $module = $_REQUEST['module'];
        } else {
            $request_uri = explode('?', rawurldecode($_SERVER['REQUEST_URI']));
            if (preg_match('/^\/(.*)\.html$/u', str_replace(BASE_PATH, '', $request_uri[0]), $match)) {
                $module = $match[1];
            }
        }
        // โหลดเมนูทั้งหมดเรียงตามลำดับเมนู (รายการแรกคือหน้า Home)
        $sql = "SELECT M.`id` AS `module_id`,M.`module`,M.`owner`,M.`config`,U.`index_id`,U.`parent`,U.`level`,U.`menu_text`,U.`menu_tooltip`,U.`accesskey`,U.`menu_url`,U.`menu_target`,U.`alias`,U.`published`";
        $sql .= ",(CASE U.`parent` WHEN 'MAINMENU' THEN 0 WHEN 'BOTTOMMENU' THEN 1 WHEN 'SIDEMENU' THEN 2 ELSE 3 END ) AS `pos`";
        $sql .= " FROM `".DB_MENUS."` AS U";
        $sql .= " LEFT JOIN `".DB_INDEX."` AS I ON I.`id`=U.`index_id` AND I.`index`='1' AND I.`language` IN ('".LANGUAGE."','')";
        $sql .= " LEFT JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id`";
        $sql .= " WHERE U.`language` IN ('".LANGUAGE."','')";
        $sql .= " ORDER BY `pos` ASC,U.`parent` ASC ,U.`menu_order` ASC";
        $menus = $cache->get($sql);
        if (!$menus) {
            $menus = $db->customQuery($sql);
            $cache->save($sql, $menus);
        }
        foreach ($menus as $item) {
            if (!isset($install_modules[$item['module']]) && $item['module'] != '') {
                $install_modules[$item['module']] = $item;
                $install_owners[$item['owner']][] = $item['module'];
                $module_list[] = $item['module'];
            }
        }
        // โหลดโมดูลทั้งหมดที่ติดตั้ง
        $sql = "SELECT `id` AS `module_id`,`module`,`owner`,`config` FROM `".DB_MODULES."`";
        $_modules = $cache->get($sql);
        if (!$_modules) {
            $_modules = $db->customQuery($sql);
            $cache->save($sql, $_modules);
        }
        foreach ($_modules as $item) {
            if (!isset($install_modules[$item['module']])) {
                $install_modules[$item['module']] = $item;
                $install_owners[$item['owner']][] = $item['module'];
                $module_list[] = $item['module'];
            }
        }
        // โมดูลที่ติดตั้ง
        $dir = ROOT_PATH.'modules/';
        $f = @opendir($dir);
        if ($f) {
            while (false !== ($text = readdir($f))) {
                if ($text != '.' && $text != '..') {
                    if (is_dir($dir.$text.'/')) {
                        if (!isset($install_owners[$text])) {
                            $install_owners[$text] = array();
                        }
                        $module_list[] = $text;
                    }
                }
            }
            closedir($f);
        }
        // จัดลำดับโมดูลตามเมนู
        foreach ($menus as $i => $item) {
            if ($item['level'] == 0) {
                $mainmenu[$item['parent']]['toplevel'][$i] = $item;
            } else {
                $mainmenu[$item['parent']][$toplevel[$item['level'] - 1]][$i] = $item;
            }
            $toplevel[$item['level']] = $i;
        }
        // ตรวจสอบโมดูลจาก URL ของเมนูรายการแรกสุด
        if ($module == '') {
            if (!empty($menus[0]['menu_url'])) {
                list($a, $b) = explode('?', $menus[0]['menu_url']);
                if ($b != '') {
                    foreach (explode('&amp;', $b) as $c) {
                        list($d, $e) = explode('=', $c);
                        $$d = $e;
                    }
                }
                if ($module == '' && preg_match('/^(.*\/)?('.implode('|', $module_list).')\.html$/', $a, $match)) {
                    $module = $match[2];
                }
            }
        }
        // ไม่มีโมดูลใช้โมดูลแรกสุด
        $module = $module == '' ? $module_list[0] : $module;
        $script[] = 'window.FIRST_MODULE = "'.$module_list[0].'";';
        if (!empty($config['facebook']['appId'])) {
            $script[] = 'inintFacebook("'.$config['facebook']['appId'].'", "'.LANGUAGE.'");';
        }
        // ตรวจสอบโมดูลที่เรียก
        include ROOT_PATH.'module.php';
        // ตรวจสอบ cron
        if ($config['cron'] == 1) {
            if ((int) @file_get_contents(DATA_PATH.'index.php') != date('d', $mmktime)) {
                $ftp->fwrite(DATA_PATH.'index.php', 'wb', date('d-m-Y H:i:s', $mmktime));
                $cron = true;
            } else {
                $cron = false;
            }
        }
        // โหลด config,ไฟล์ inint, cron ของโมดูลที่ติดตั้ง
        foreach ($install_owners as $owner => $items) {
            if (is_file(ROOT_PATH."modules/$owner/config.php")) {
                include_once ROOT_PATH."modules/$owner/config.php";
            }
            if (is_file(ROOT_PATH."modules/$owner/inint.php")) {
                include_once ROOT_PATH."modules/$owner/inint.php";
            }
            if ($cron && is_file(ROOT_PATH."modules/$owner/cron.php")) {
                include_once ROOT_PATH."modules/$owner/cron.php";
            }
        }
        // โหลดโมดูล login
        include ROOT_PATH.'modules/member/login.php';
        $mainlogin = $content;
        // login
        $isMember = gcms::isMember();
        // admin
        $isAdmin = gcms::isAdmin();
        // บันทึก counter และ useronline
        include ROOT_PATH.'counter.php';
        // ค่า title,description และ keyword ของเว็บหลัก
        $title = $config['web_title'];
        $description = $config['web_description'];
        $keywords = $config['web_description'];
        // แสดงผล template หลัก
        $main_replace = array();
        $main_patt = array();
        // ฟอร์ม login
        $main_patt[] = '/{LOGIN}/';
        $main_replace[] = $mainlogin;
        if (!empty($config['google_site_verification'])) {
            $meta['google-site-verification'] = '<meta name=google-site-verification content='.$config['google_site_verification'].'>';
        }
        if (!empty($config['google_profile'])) {
            $meta['author'] = '<link rel=author href=https://plus.google.com/'.$config['google_profile'].'>';
            $meta['publisher'] = '<link rel=publisher href=https://plus.google.com/'.$config['google_profile'].'>';
        }
        if (is_file(ROOT_PATH."modules/$modules[2]/index.php")) {
            // เรียกหน้าหลักโมดูล
            include ROOT_PATH."modules/$modules[2]/index.php";
        } else {
            $title = $lng['PAGE_NOT_FOUND'];
            $content = '<div class=error>'.$title.'</div>';
        }
        // เนื้อหา
        $main_patt[] = '/{CONTENT}/';
        $main_replace[] = $content;
        // เมนู
        foreach ($mainmenu as $parent => $items) {
            if ($parent != '') {
                $main_patt[$parent] = '/{'.$parent.'}/';
                $mymenu = '';
                if (isset($items['toplevel'])) {
                    foreach ($items['toplevel'] as $level => $name) {
                        if (!empty($items[$level])) {
                            $mymenu .= gcms::getMenu($name, true).'<ul>';
                            foreach ($items[$level] as $level2 => $item2) {
                                if (!empty($items[$level2])) {
                                    $mymenu .= gcms::getMenu($item2, true).'<ul>';
                                    foreach ($items[$level2] as $item3) {
                                        $mymenu .= gcms::getMenu($item3).'</li>';
                                    }
                                    $mymenu .= '</ul></li>';
                                } else {
                                    $mymenu .= gcms::getMenu($item2).'</li>';
                                }
                            }
                            $mymenu .= '</ul>';
                        } else {
                            $mymenu .= gcms::getMenu($name);
                        }
                        $mymenu .= '</li>';
                    }
                }
                $main_replace[$parent] = $mymenu;
            }
        }
        if ($menu != '' && $main_replace['MAINMENU'] != '') {
            // ตรวจสอบเมนูที่เลือก
            $menu = '/class="('.preg_replace('/([\/\-])/', '\\1', preg_quote($menu)).')(.*?)"/';
            if (!preg_match($menu, $main_replace['MAINMENU'])) {
                // ถ้าไม่มีจะใช้่โมดูลแรกสุด
                $menu = $install_modules[$module_list[0]];
                $menu = empty($menu['alias']) ? $menu['module'] : $menu['alias'];
                $menu = '/class="('.preg_replace('/([\/\-])/u', '\\1', preg_quote($menu)).')(.*?)"/';
            }
            $main_replace['MAINMENU'] = preg_replace($menu, 'class="\\1 select\\2"', $main_replace['MAINMENU']);
        }
        // เวอร์ชั่น
        $main_patt[] = '/{VERSION}/';
        $main_replace[] = VERSION;
        // ข้อความบน title bar
        $main_patt[] = '/{TITLE}/';
        $main_replace[] = strip_tags($title);
        // ภาษาที่ติดตั้ง
        $languages = array();
        $skin = gcms::loadfile(ROOT_PATH.SKIN.'language.html');
        foreach ($config['languages'] as $language) {
            $languages[] = preg_replace('/{LNG}/', $language, $skin);
        }
        $script[] = 'changeLanguage("'.implode(',', $config['languages']).'");';
        $main_patt[] = '/{LANGUAGES}/';
        $main_replace[] = implode('', $languages);
        // ขนาดตัวอักษร
        $main_patt[] = '/{FONTSIZE}/';
        $main_replace[] = '<p id=change_display><a class=small title="{LNG_CHANGE_FONT_SMALL}">A<sup>-</sup></a><a class=normal title="{LNG_CHANGE_FONT_NORMAL}">A</a><a class=large title="{LNG_CHANGE_FONT_LARGE}">A<sup>+</sup></a></p>';
        // widgets
        $main_patt[] = '/{WIDGET_([A-Z]+)(([\s_])(.*))?}/e';
        $main_replace[] = 'gcms::getWidgets';
        // meta, keywords และ description
        $meta['description'] = '<meta name=description content="'.$description.'">';
        $meta['keywords'] = '<meta name=keywords content="'.$keywords.'">';
        if (!empty($config['facebook']['appId'])) {
            $meta['facebook_appId'] = '<meta property="fb:app_id" content="'.$config['facebook']['appId'].'">';
        }
        if (!empty($image_src)) {
            $meta['image_src'] = '<link rel=image_src href='.$image_src.'>';
        }
        $meta['canonical'] = '<link rel=canonical href="'.$canonical.'">';
        $meta['hreflang'] = '<link rel=alternate hreflang='.LANGUAGE.' href="'.$canonical.'">';
        $main_patt[] = '/{META}/';
        $main_replace[] = implode("\n", $meta);
        // javascript
        $main_patt[] = '/{SCRIPT}/';
        $main_replace[] = implode("\n", $script);
        // เวลาประมวลผล
        $main_patt[] = '/{ELAPSED}/';
        $main_replace[] = $db->timer_stop();
        // จำนวน query
        $main_patt[] = '/{QURIES}/';
        $main_replace[] = $db->query_count();
        // path ของ tempalate
        $main_patt[] = '/{SKIN}/';
        $main_replace[] = SKIN;
        // ภาษาที่เลือก
        $main_patt[] = '/{LANGUAGE}/';
        $main_replace[] = LANGUAGE;
        // ภาษา
        $main_patt[] = '/{(LNG_[A-Z0-9_]+)}/e';
        $main_replace[] = 'gcms::getLng';
        // URL ของเว็บไซต์
        $main_patt[] = '/{WEBURL}/';
        $main_replace[] = WEB_URL;
        // URL ของ datas/
        $main_patt[] = '/{DATAURL}/';
        $main_replace[] = DATA_URL;
        // ชื่อเว็บ
        $main_patt[] = '/{WEBTITLE}/';
        $main_replace[] = $config['web_title'];
        // คำอธิบายย่อของเว็บ
        $main_patt[] = '/{WEBDESCRIPTION}/';
        $main_replace[] = $config['web_description'];
        // แสดงผล
        echo gcms::pregReplace($main_patt, $main_replace, gcms::loadtemplate('index', '', 'index'));
    }
}
