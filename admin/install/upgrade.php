<?php
if (INSTALL_INIT == 'upgrade') {
  $config['ftp_host'] = $_SERVER['SERVER_ADDR'];
  $config['ftp_port'] = (int)$config['ftp_port'] == 0 ? 21 : $config['ftp_port'];
  $config['ftp_root'] = $config['ftp_root'] == '' ? 'public_html' : $config['ftp_root'];
  $ftp = false;
  if ($config['ftp_username'] != '' && $config['ftp_password'] != '') {
    $ftp = new ftp($config['ftp_host'], $config['ftp_username'], $config['ftp_password'], $config['ftp_root'], $config['document_root'], $config['ftp_port']);
  }
  if ($ftp && $ftp->connect() && $ftp->is_dir($config['ftp_root'])) {
    $_SESSION['ftp_root'] = $config['ftp_root'];
    include (ROOT_PATH.'admin/install/upgrade1.php');
  } else {
    echo '<h2>ยินดีต้อนรับสู่การปรับรุ่นของ GCMS เวอร์ชั่น '.$version.'</h2>';
    echo '<p><em>เราตรวจพบโฟลเดอร์ <strong>install/</strong> บนเซิร์ฟเวอร์ของคุณ และตรวจพบ <strong>GCMS</strong> เวอร์ชั่นใหม่บนเซิร์ฟเวอร์ของคุณ</em> และต้องการการปรับรุ่น</p>';
    echo '<h3>ระบุที่อยู่ FTP ของโฮสต์</h3>';
    echo '<p>ก่อนอื่นเราแนะนำให้คุณระบุค่ากำหนดต่างๆของ FTP Server ของคุณ FTP จะช่วยให้คุณจัดการกับไฟล์และไดเร็คทอรี่บน GCMS ได้ง่ายขึ้น ถ้าคุณไม่รู้ค่ากำหนดเหล่านี้ คุณสามารถข้ามขั้นตอนนี้ไปก่อนได้</p>';
    echo '<form method=post action=index.php autocomplete=off>';
    echo '<p class=row><label for=ftp_host>โฮสต์</label><input type=text size=70 id=ftp_host name=ftp_host value="'.($_SESSION['ftp_host'] != '' ? $_SESSION['ftp_host'] : $config['ftp_host']).'"></p>';
    echo '<p class="row comment">FTP โดเมน เช่น ftp.domain.tld หรือ ที่อยู่ IP ของโฮสต์</p>';
    echo '<p class=row><label for=ftp_username>ชื่อผู้ใช้</label><input type=text size=70 id=ftp_username name=ftp_username value="'.($_SESSION['ftp_username'] != '' ? $_SESSION['ftp_username'] : $config['ftp_username']).'"></p>';
    echo '<p class="row comment">ชื่อผู้ใช้ของ FTP</p>';
    echo '<p class=row><label for=ftp_password>รหัสผ่าน</label><input type=password size=70 id=ftp_password name=ftp_password value="'.($_SESSION['ftp_password'] != '' ? $_SESSION['ftp_password'] : $config['ftp_password']).'"></p>';
    echo '<p class="row comment">รหัสผ่านของ FTP</p>';
    echo '<p class=row><label for=ftp_root>FTP Root</label><input type=text size=70 id=ftp_root name=ftp_root value="'.($_SESSION['ftp_root'] != '' ? $_SESSION['ftp_root'] : $config['ftp_root']).'"></p>';
    echo '<p class="row comment">ไดเรคทอรี่เริ่มต้นของของโฮสต์เช่น public_html หรือ www</p>';
    echo '<p class=row><label for=ftp_port>พอร์ต</label><input type=text size=70 id=ftp_port name=ftp_port value="'.($_SESSION['ftp_port'] != '' ? $_SESSION['ftp_port'] : $config['ftp_port']).'"></p>';
    echo '<p class="row comment">FTP พอร์ต (ค่าปกติคือ 20)</p>';
    echo '<p class=row><label for=document_root>Document Root</label><input type=text size=70 id=document_root name=document_root value="'.($_SESSION['document_root'] != '' ? $_SESSION['document_root'] : $document_root).'"></p>';
    echo '<p class="row comment">ไดเรคทอรี่เริ่มต้นของเซิร์ฟเวอร์</p>';
    echo '<input type=hidden name=step value=1>';
    echo '<p><input class=button type=submit value="ดำเนินการต่อ."></p>';
    echo '</form>';
  }
}
