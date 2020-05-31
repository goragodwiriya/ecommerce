<?php
if (INSTALL_INIT == 'install') {
    $config['ftp_host'] = $_SERVER['SERVER_ADDR'];
    $config['ftp_port'] = empty($config['ftp_port']) ? 21 : $config['ftp_port'];
    $document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $config['ftp_root'] = empty($config['ftp_root']) ? basename($document_root) : $config['ftp_root'];
    echo '<h2>ระบุที่อยู่ FTP ของโฮสต์ </h2>';
    echo '<p>ก่อนอื่นเราแนะนำให้คุณระบุค่ากำหนดเกี่ยวกับ FTP เซิร์ฟเวอร์ของคุณ FTP จะช่วยให้คุณจัดการกับไฟล์และไดเร็คทอรี่บน GCMS ได้ง่ายขึ้น (ถ้าคุณไม่รู้ คุณสามารถข้ามไปยังขั้นตอนต่อไปได้)</p>';
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
    echo '<input type=hidden name=step value=2>';
    echo '<p><input class=button type=submit value="ดำเนินการต่อ."></p>';
    echo '</form>';
}
