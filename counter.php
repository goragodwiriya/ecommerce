<?php
// counter.php
if (defined('MAIN_INIT')) {
    // วันนี้
    $y = (int) date('Y', $mmktime);
    $m = (int) date('m', $mmktime);
    $d = (int) date('d', $mmktime);
    // โฟลเดอร์ของ counter
    $counter_dir = DATA_PATH.'counter/';
    // ตรวจสอบโฟลเดอร์
    gcms::testDir($counter_dir, 0755);
    // ตรวจสอบวันใหม่
    $c = (int) @file_get_contents($counter_dir.'index.php');
    if ($d != $c) {
        $f = @fopen($counter_dir.'index.php', 'wb');
        if ($f) {
            fwrite($f, $d);
            fclose($f);
        }
        $f = @opendir($counter_dir);
        if ($f) {
            while (false !== ($text = readdir($f))) {
                if ($text != '.' && $text != '..') {
                    if ($text != $y) {
                        gcms::rm_dir($counter_dir."$text/");
                    }
                }
            }
            closedir($f);
        }
    }
    // ตรวจสอบ + สร้าง โฟลเดอร์
    gcms::testDir("$counter_dir/$y/", 0755);
    gcms::testDir("$counter_dir/$y/$m/", 0755);
    // ip ปัจจุบัน
    $counter_ip = gcms::getip();
    // session ปัจจุบัน
    $counter_ssid = session_id();
    // วันนี้
    $counter_day = date('Y-m-d', $mmktime);
    // อ่านข้อมูล counter ล่าสุด
    $sql = "SELECT * FROM `".DB_COUNTER."` ORDER BY `id` DESC LIMIT 1";
    $my_counter = $db->customQuery($sql);
    $my_counter = sizeof($my_counter) == 1 ? $my_counter[0] : array();
    if ($my_counter['date'] != $counter_day) {
        // วันใหม่
        $my_counter['visited'] = 0;
        $my_counter['pages_view'] = 0;
        $my_counter['date'] = $counter_day;
        $counter_add = true;
    } else {
        $counter_add = false;
    }
    // บันทึกลง log และตรวจสอบว่าเคยเยี่ยมชมวันนี้หรือไม่
    $counter_visited = false;
    $counter_log = "$counter_dir/$y/$m/$d.dat";
    $i = 0;
    if (is_file($counter_log)) {
        // ตรวจสอบว่าเคยเยี่ยมชมวันนี้หรือไม่
        $logs = file($counter_log);
        foreach ($logs as $item) {
            list($sid, $sip, $sref, $sagent) = explode(chr(1), $item);
            if ($sid == $counter_ssid || $sip == $counter_ip) {
                $counter_visited = true;
                break;
            }
        }
        // บันทึกต่อจากของเดิม
        $f = @fopen($counter_log, 'ab');
    } else {
        // สร้างไฟล์ log ใหม่
        $f = @fopen($counter_log, 'wb');
    }
    $data = $counter_ssid.chr(1).$counter_ip.chr(1).(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '').chr(1).$_SERVER['HTTP_USER_AGENT']."\n";
    @fwrite($f, $data);
    @fclose($f);
    if (!$counter_visited) {
        // ยังไม่เคยเยี่ยมชมในวันนี้
        $my_counter['visited']++;
        $my_counter['counter']++;
    }
    $my_counter['pages_view']++;
    $my_counter['time'] = $mmktime;
    if ($counter_add) {
        unset($my_counter['id']);
        $db->add(DB_COUNTER, $my_counter);
    } else {
        $id = $my_counter['id'];
        unset($my_counter['id']);
        $db->edit(DB_COUNTER, $id, $my_counter);
    }
}
