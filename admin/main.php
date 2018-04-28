<?php
// admin/main.php
if (MAIN_INIT == 'admin' && $canAdmin) {
  // เมนูของแอดมิน
  $admin_menus = array();
  // menu section
  $admin_menus['sections']['home'] = array('h', '<a href="'.WEB_URL.'/admin/index.php?module=dashboard" accesskey=h title="{LNG_HOME}"><span>{LNG_HOME}</span></a>');
  $admin_menus['sections']['settings'] = array('1', '{LNG_SITE_SETTINGS}');
  $admin_menus['sections']['index'] = array('2', '{LNG_MENUS} &amp; {LNG_WEB_PAGES}');
  $admin_menus['sections']['modules'] = array('3', '{LNG_MODULES}');
  $admin_menus['sections']['widgets'] = array('4', '{LNG_WIDGETS}');
  $admin_menus['sections']['users'] = array('5', '{LNG_USERS}');
  $admin_menus['sections']['email'] = array('6', '{LNG_MAILBOX}');
  $admin_menus['sections']['tools'] = array('7', '{LNG_TOOLS}');
  // settings
  $admin_menus['settings']['system'] = '<a href="'.WEB_URL.'/admin/index.php?module=system"><span>{LNG_GENERAL}</span></a>';
  $admin_menus['settings']['mailserver'] = '<a href="'.WEB_URL.'/admin/index.php?module=mailserver"><span>{LNG_EMAIL_SETTINGS}</span></a>';
  $admin_menus['settings']['mailtemplate'] = '<a href="'.WEB_URL.'/admin/index.php?module=mailtemplate"><span>{LNG_EMAIL_TEMPLATE}</span></a>';
  $admin_menus['settings']['template'] = '<a href="'.WEB_URL.'/admin/index.php?module=template"><span>{LNG_TEMPLATE}</span></a>';
  $admin_menus['settings']['skin'] = '<a href="'.WEB_URL.'/admin/index.php?module=skin"><span>{LNG_SKIN_SETTINGS}</span></a>';
  $admin_menus['settings']['maintenance'] = '<a href="'.WEB_URL.'/admin/index.php?module=maintenance"><span>{LNG_MAINTENANCE}</span></a>';
  $admin_menus['settings']['intro'] = '<a href="'.WEB_URL.'/admin/index.php?module=intro"><span>{LNG_INTRO_PAGE}</span></a>';
  $admin_menus['settings']['languages'] = '<a href="'.WEB_URL.'/admin/index.php?module=languages"><span>{LNG_LANGUAGE}</span></a>';
  $admin_menus['settings']['other'] = '<a href="'.WEB_URL.'/admin/index.php?module=other"><span>{LNG_CONFIG_OTHER}</span></a>';
  $admin_menus['settings']['meta'] = '<a href="'.WEB_URL.'/admin/index.php?module=meta"><span>{LNG_SEO_SOCIAL}</span></a>';
  // email
  $admin_menus['email']['sendmail'] = '<a href="'.WEB_URL.'/admin/index.php?module=sendmail"><span>{LNG_EMAIL_SEND}</span></a>';
  // เมนู
  $admin_menus['index']['pages'] = '<a href="'.WEB_URL.'/admin/index.php?module=index-pages"><span>{LNG_WEB_PAGES}</span></a>';
  $admin_menus['index']['insmod'] = '<a href="'.WEB_URL.'/admin/index.php?module=index-insmod"><span>{LNG_INSTALLED_MODULE}</span></a>';
  $admin_menus['index']['menu'] = '<a href="'.WEB_URL.'/admin/index.php?module=index-menus"><span>{LNG_MENUS}</span></a>';
  // เมนูสมาชิก
  $admin_menus['users']['memberstatus'] = '<a href="'.WEB_URL.'/admin/index.php?module=memberstatus"><span>{LNG_MEMBER_STATUS}</span></a>';
  $admin_menus['users']['member'] = '<a href="'.WEB_URL.'/admin/index.php?module=member"><span>{LNG_USERS_TITLE}</span></a>';
  $admin_menus['users']['register'] = '<a href="'.WEB_URL.'/admin/index.php?module=register"><span>{LNG_REGISTER}</span></a>';
  // tools
  $admin_menus['tools']['install'] = array();
  $admin_menus['tools']['database'] = '<a href="'.WEB_URL.'/admin/index.php?module=database"><span>{LNG_DATABASE}</span></a>';
  $admin_menus['tools']['language'] = '<a href="'.WEB_URL.'/admin/index.php?module=language"><span>{LNG_LANGUAGE}</span></a>';
  // ตรวจสอบโมดูลที่ติดตั้ง ตามโฟลเดอร์
  $dir = ROOT_PATH.'modules/';
  $f = @opendir($dir);
  if ($f) {
    while (false !== ($text = readdir($f))) {
      if ($text != "." && $text != "..") {
        $install_owners[$text] = array();
      }
    }
    closedir($f);
  }
  // โหลดโมดูลที่ติดตั้ง เรียงตามลำดับเมนู
  $sql = "SELECT M.`id`,M.`owner`,M.`module`";
  $sql .= " FROM `".DB_MODULES."` AS M";
  $sql .= " WHERE M.`owner`!='index'";
  $sql .= " ORDER BY M.`owner`,M.`id`";
  foreach ($db->customQuery($sql) AS $item) {
    // ตรวจสอบไฟล์ config
    if (is_file(ROOT_PATH."modules/$item[owner]/admin_config.php")) {
      $admin_menus['modules'][$item['module']]['config'] = '<a href="index.php?module='.$item['owner'].'-config&amp;id='.$item['id'].'"><span>{LNG_CONFIG}</span></a>';
    }
    // ตรวจสอบไฟล์ category
    if (is_file(ROOT_PATH."modules/$item[owner]/admin_category.php")) {
      $admin_menus['modules'][$item['module']]['category'] = '<a href="index.php?module='.$item['owner'].'-category&amp;id='.$item['id'].'"><span>{LNG_CATEGORY}</span></a>';
    }
    // ตรวจสอบไฟล์ setup
    if (is_file(ROOT_PATH."modules/$item[owner]/admin_setup.php")) {
      $admin_menus['modules'][$item['module']]['setup'] = '<a href="index.php?module='.$item['owner'].'-setup&amp;id='.$item['id'].'"><span>{LNG_CONTENTS}</span></a>';
    }
    $install_modules[$item['module']] = $item;
    $owner_list[$item['owner']][] = $item['module'];
    $install_owners[$item['owner']][] = $item;
  }
  // โหลดโมดูล ที่ติดตั้ง ตามโฟลเดอร์
  foreach ($install_owners AS $text => $items) {
    // css
    if (is_file(ROOT_PATH."modules/$text/setup.css")) {
      $stylesheet[] = '<link rel=stylesheet href='.WEB_URL.'/modules/'.$text.'/setup.css>';
    }
    // js
    if (is_file(ROOT_PATH."modules/$text/setup.js")) {
      $javascript[] = '<script src='.WEB_URL.'/modules/'.$text.'/setup.js></script>';
    }
    // config ของโมดูล
    if (is_file(ROOT_PATH."modules/$text/config.php")) {
      require_once (ROOT_PATH."modules/$text/config.php");
    }
    // inint ของโมดูล
    if (is_file(ROOT_PATH."modules/$text/admin_inint.php")) {
      require_once (ROOT_PATH."modules/$text/admin_inint.php");
      if (sizeof($admin_menus['modules'][$text]) == 0) {
        unset($admin_menus['modules'][$text]);
      }
    }
  }
  // โหลด widgets ที่ติดตั้ง ตามโฟลเดอร์
  $setup_widgets = array();
  $dir = ROOT_PATH.'widgets/';
  $f = @opendir($dir);
  if ($f) {
    while (false !== ($text = readdir($f))) {
      if ($text != "." && $text != "..") {
        if (is_file($dir.$text.'/admin_setup.php')) {
          $setup_widgets[] = $text;
          $admin_menus['widgets'][$text] = '<a href="'.WEB_URL.'/admin/index.php?module='.$text.'-setup" title="'.$text.'"><span>'.ucfirst($text).'</span></a>';
        }
        // css ของ widgets
        if (is_file(ROOT_PATH."widgets/$text/setup.css")) {
          $stylesheet[] = '<link rel=stylesheet href='.WEB_URL.'/widgets/'.$text.'/setup.css>';
        }
        // js ของ widgets
        if (is_file(ROOT_PATH."widgets/$text/setup.js")) {
          $javascript[] = '<script src='.WEB_URL.'/widgets/'.$text.'/setup.js></script>';
        }
        // config ของ widgets
        if (is_file(ROOT_PATH."widgets/$text/config.php")) {
          require_once (ROOT_PATH."widgets/$text/config.php");
        }
        // inint ของ widgets
        if (is_file(ROOT_PATH."widgets/$text/admin_inint.php")) {
          require_once (ROOT_PATH."widgets/$text/admin_inint.php");
        }
      }
    }
    closedir($f);
  }
  if (!$isAdmin) {
    unset($admin_menus['sections']['settings']);
    unset($admin_menus['sections']['index']);
    unset($admin_menus['sections']['menus']);
    unset($admin_menus['sections']['widgets']);
    unset($admin_menus['sections']['users']);
    unset($admin_menus['sections']['tools']);
  }
  if (sizeof($admin_menus['modules']) == 0) {
    unset($admin_menus['sections']['modules']);
  }
  if (sizeof($admin_menus['widgets']) == 0) {
    unset($admin_menus['sections']['widgets']);
  }
  if (sizeof($admin_menus['tools']['install']) == 0) {
    unset($admin_menus['tools']['install']);
  }
  // แสดงเมนูหลัก
  foreach ($admin_menus['sections'] AS $section => $name) {
    $link = preg_match('/<a.*>.*<\/a>/', $name[1]) ? $name[1] : '<a accesskey='.$name[0].' class=menu-arrow><span>'.$name[1].'</span></a>';
    $menus[] = '<li class="'.$section.'">'.$link;
    if (sizeof($admin_menus[$section]) > 0) {
      $menus[] = '<ul>';
      foreach ($admin_menus[$section] AS $key => $value) {
        if (is_array($value)) {
          $menus[] = '<li class="'.$key.'"><a class=menu-arrow tabindex=0><span>'.ucfirst($key).'</span></a><ul>';
          foreach ($value AS $key2 => $value2) {
            $menus[] = '<li class="'.$key2.'">'.$value2.'</li>';
          }
          $menus[] = '</ul></li>';
        } else {
          $menus[] = '<li class="'.$key.'">'.$value.'</li>';
        }
      }
      $menus[] = '</ul>';
    }
    $menus[] = '</li>';
  }
  // โมดูลที่เรียก
  $module = preg_replace('/[\.\/]/', '', $_GET['module']);
  if (is_file(ROOT_PATH."admin/$module.php")) {
    require_once ROOT_PATH."admin/$module.php";
  } elseif (preg_match('/^('.implode('|', array_keys($install_owners)).')(-(.*))?$/ui', $module, $modules)) {
    if (is_file(ROOT_PATH."modules/$modules[1]/admin_$modules[3].php")) {
      // โมดูลที่เรียก
      require_once ROOT_PATH."modules/$modules[1]/admin_$modules[3].php";
    } elseif (is_file(ROOT_PATH."widgets/$modules[1]/admin_$modules[3].php")) {
      // เรียก widget ชื่อเดียวกับโมดูล
      require_once ROOT_PATH."widgets/$modules[1]/admin_$modules[3].php";
    } else {
      require_once ROOT_PATH."admin/dashboard.php";
    }
  } elseif (preg_match('/^('.implode('|', $setup_widgets).')(-(.*))?$/ui', $module, $modules)) {
    // เรียก widget
    if (is_file(ROOT_PATH."widgets/$modules[1]/admin_$modules[3].php")) {
      // โมดูลที่เรียก
      require_once ROOT_PATH."widgets/$modules[1]/admin_$modules[3].php";
    } else {
      require_once ROOT_PATH."admin/dashboard.php";
    }
  } else {
    require_once ROOT_PATH."admin/dashboard.php";
  }
}
