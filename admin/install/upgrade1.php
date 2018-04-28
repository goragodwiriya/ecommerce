<?php
if (INSTALL_INIT == 'upgrade') {
  // อัปเกรดฐานข้อมูลจาก 4.5.0
  echo '<h2>เริ่มต้นการปรับรุ่น GCMS เวอร์ชั่น '.$version.'</h2>';
  echo '<p>ตัวปรับรุ่นจะดำเนินการปรับปรุงฐานข้อมูล(เวอร์ชั่น <strong>4.5.0</strong> ขึ้นไปเท่านั้น) และแฟ้มระบบที่จำเป็น คุณควรสำรองฐานข้อมูลทั้งหมดของคุณก่อนเริ่มดำเนินการปรับรุ่น</p>';
  echo '<p>โปรแกรมจะทำการอัปเกรดให้เฉพาะส่วนที่เป็นแกน (core) ของ GCMS เท่านั้น โมดูลที่ติดตั้งเพิ่มเติมในภายหลังไม่ได้ถูกอัปเกรดไปด้วย คุณอาจจะต้องดำเนินการอัปเกรดโมดูลอื่นๆที่ไม่ใช่โมดูลมาตรฐานของ GCMS ด้วยตัวเอง</p>';
  echo '<p>คุณอาจต้องการ "ติดตั้งใหม่" ถ้าคุณต้องการเริ่มต้นการติดตั้ง GCMS ใหม่แบบสะอาดหมดจด หรือหากการปรับรุ่นไม่สามารถดำเนินการได้ ซึ่งจะมีผลให้ข้อมูลทั้งหมดของคุณสูญหายไปด้วย</p>';
  // ตรวจสอบไฟ์ลและโฟลเดอร์
  echo '<h3>ตรวจสอบองค์ประกอบต่างๆ ที่จำเป็นสำหรับการติดตั้ง</h3>';
  echo '<ol>';
  if (isset($_POST['ftp_host'])) {
    // connect ftp
    $_SESSION['ftp_host'] = trim($_POST['ftp_host']);
    $_SESSION['ftp_username'] = trim($_POST['ftp_username']);
    $_SESSION['ftp_password'] = trim($_POST['ftp_password']);
    $_SESSION['ftp_root'] = trim($_POST['ftp_root']);
    $ftp_port = trim($_POST['ftp_port']);
    $_SESSION['ftp_port'] = $ftp_port = '' ? 21 : $ftp_port;
    $_SESSION['document_root'] = trim($_POST['document_root']);
    $ftp = new ftp($_SESSION['ftp_host'], $_SESSION['ftp_username'], $_SESSION['ftp_password'], $_SESSION['ftp_root'], $_SESSION['document_root'], $_SESSION['ftp_port']);
  }
  if ($ftp->connect()) {
    if ($ftp->is_dir($_SESSION['ftp_root'])) {
      echo '<li class=correct><strong>FTP</strong> <i>เชื่อมต่อสำเร็จ</i></li>';
    } else {
      echo '<li class=incorrect><strong>FTP Root</strong> <em>ไม่ถูกต้อง</em> กรุณาตรวจสอบจากโฮสต์ของคุณ</li>';
    }
  } else {
    echo '<li class=incorrect><strong>FTP</strong> <em>ไม่สามารถใช้งานได้</em></li>';
  }
  if (is_dir(DATA_PATH)) {
    echo '<li>ตรวจสอบโฟลเดอร์ <strong>'.DATA_FOLDER.'</strong></li>';
    ob_flush();
    flush();
    $datas_dir = ROOT_PATH."$mmktime/";
    if (@rename(DATA_PATH, $datas_dir)) {
      copyDir($datas_dir, DATA_PATH);
    }
    gcms::rm_dir($datas_dir);
    echo '<li class=correct>ตรวจสอบโฟลเดอร์ <strong>'.DATA_FOLDER.'</strong> <i>เรียบร้อย</i></li>';
    ob_flush();
    flush();
  }
  $files = array();
  $files[] = ROOT_PATH.'bin/config.php';
  $files[] = ROOT_PATH.'bin/vars.php';
  foreach ($files AS $file) {
    if (!is_writeable($file)) {
      $ftp->chmod($file, 0646);
    }
    if (is_writeable($file)) {
      echo '<li class=correct>ไฟล์ <strong>'.str_replace(ROOT_PATH, '', $file).'</strong> <i>สามารถเขียนได้</i></li>';
    } else {
      $error = true;
      echo '<li class=incorrect>ไฟล์ <strong>'.str_replace(ROOT_PATH, '', $file).'</strong> <em>ไม่สามารถเขียนได้</em> กรุณาสร้างไฟล์เปล่าๆและปรับ chmod ให้เป็น 755 ด้วยตัวเอง</li>';
    }
  }
  $folders[] = DATA_PATH.'language/';
  foreach ($folders AS $folder) {
    if ($ftp->mkdir($folder, 0755)) {
      echo '<li class=correct>โฟลเดอร์ <strong>'.str_replace(ROOT_PATH, '', $folder).'</strong> <i>สามารถใช้งานได้</i></li>';
    } else {
      $error = true;
      echo '<li class=incorrect>โฟลเดอร์ <strong>'.str_replace(ROOT_PATH, '', $folder).'</strong> <em>ไม่สามารถเขียนหรือสร้างได้</em> กรุณาสร้างโฟลเดอร์นี้และปรับ chmod ให้เป็น 755 ด้วยตัวเอง</li>';
    }
  }
  echo '</ol>';
  echo '<p>ถ้าทุกอย่างพร้อมแล้ว...เลือกการดำเนินการที่คุณต้องการต่อไปด้านล่าง</p>';
  echo '<p><a href="index.php?step=3" class=button>ติดตั้งใหม่!</a>&nbsp;&nbsp;<a href="index.php?step=2" class=button>เริ่มการปรับรุ่น!</a></p>';
}
