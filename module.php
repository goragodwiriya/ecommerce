<?php
// module.php
if (defined('MAIN_INIT')) {
    $modules = array(1 => '');
    // รายชื่อโมดูลที่สามารถเรียกใช้งานได้
    $module_list = array_merge($module_list, array_keys($install_owners));
    // ตรวจสอบโมดูลที่เรียก
    if (in_array($module, $member_modules)) {
        // โมดูลสมาชิก
        $modules[2] = 'member';
        $modules[3] = $module;
    } elseif (preg_match('/^(tag)[\/\-](.*)$/u', $module, $modules)) {
        // ค้นหาจาก tag เรียก document
        $modules[3] = $modules[1];
        $modules[4] = $modules[2];
        $modules[2] = 'document';
    } elseif (preg_match('/^(calendar)-(today|(([0-3]?[0-9])[\-|\s]([0-1]?[0-9])[\-|\s]([0-9]{4,4})))$/', $module, $modules)) {
        // ค้นหาจาก วันที่ เรียก document
        if ($modules[2] == 'today') {
            $ds = array(1 => $mtoday, 2 => $mmonth, 3 => $myear + $lng['YEAR_OFFSET']);
        } else {
            $ds = array(1 => $modules[4], 2 => $modules[5], 3 => $modules[6]);
        }
        $modules[3] = 'main';
        $modules[4] = $modules[2];
        $modules[2] = 'document';
    } elseif (preg_match('/^('.implode('|', $module_list).')[\/\-]([0-9]+)[\/\-]([0-9]+)([\/\-](.*))?$/u', $module, $modules)) {
        // module/category/id/document
        $_REQUEST['cat'] = (int) $modules[2];
        $_REQUEST['id'] = (int) $modules[3];
        $modules[2] = $install_modules[$modules[1]]['owner'];
        if (empty($modules[5])) {
            $modules[3] = 'main';
        } elseif (!is_file(ROOT_PATH."modules/$modules[2]/$modules[5].php")) {
            $modules[3] = 'view';
            $modules[4] = $modules[5];
        }
        unset($modules[5]);
    } elseif (preg_match('/^('.implode('|', $module_list).')[\/\-]([0-9]+)([\/\-](.*))?$/u', $module, $modules)) {
        // module/category/document
        $_REQUEST['cat'] = (int) $modules[2];
        $modules[2] = $install_modules[$modules[1]]['owner'];
        if (empty($modules[4])) {
            $modules[3] = 'main';
        } elseif (!is_file(ROOT_PATH."modules/$modules[2]/$modules[4].php")) {
            $modules[3] = 'view';
        }
    } elseif (preg_match('/^('.implode('|', $module_list).')([\/\-](.*))?$/u', $module, $modules)) {
        if (empty($install_modules[$modules[1]]['owner'])) {
            // ชื่อโมดูลหลัก เช่น index,member
            $modules[2] = $module;
        } else {
            // ตรวจสอบหากเป็นหน้าที่เรียกโดยตรง เช่น module/page/xxx.html
            if (isset($modules[2]) && preg_match('/^\/([a-z]+)[\/\-](.*)$/', $modules[2], $match)) {
                $page = $match[1];
                $modules[4] = $match[2];
            }
            // ชื่อโมดูลที่ติดตั้งแล้ว
            $modules[2] = $install_modules[$modules[1]]['owner'];
        }
        if (empty($modules[3])) {
            $modules[3] = 'main';
        } elseif (is_file(ROOT_PATH."modules/$modules[1]/$modules[3].php")) {
            // เรียกโมดูลตรงๆ
            $modules[2] = $modules[1];
        } elseif (!empty($page) && is_file(ROOT_PATH."modules/$modules[1]/$page.php")) {
            $modules[3] = $page;
        } elseif (!is_file(ROOT_PATH."modules/$modules[2]/$modules[3].php")) {
            $modules[4] = $modules[3];
            $modules[3] = 'view';
        }
    } else {
        // ไม่ได้ส่งชื่อโมดูลมา เช่น ข้อความ.html
        // ให้แสดงเรื่องจากโมดูล document
        unset($modules[1]);
        $modules[2] = 'document';
        $modules[3] = 'view';
        $modules[4] = $module;
    }
    unset($modules[0]);
    // โมดูลที่เรียก
    $module = $modules[1];
    // เลือกเมนู
    $menu = empty($install_modules[$module]['alias']) ? $module : $install_modules[$module]['alias'];
}
