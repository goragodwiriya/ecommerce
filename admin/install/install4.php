<?php
if (INSTALL_INIT == 'install') {
    if (isset($_POST['email'])) {
        $_SESSION['password'] = trim($_POST['password']);
        $_SESSION['email'] = trim($_POST['email']);
    }
    if (empty($_SESSION['email'])) {
        $error = 'กรุณากรอก ที่อยู่อีเมล';
    } elseif (!gcms::validMail($_SESSION['email'])) {
        $error = 'ที่อยู่อีเมล ไม่ถูกต้อง';
    } elseif (empty($_SESSION['password'])) {
        $error = 'กรุณากรอก รหัสผ่าน';
    } elseif (!preg_match('/^[a-z0-9]{4,}$/u', $_SESSION['password'])) {
        $error = 'รหัสผ่าน ภาษาอังกฤษตัวพิมพ์เล็กและตัวเลข ไม่น้อยกว่า 4 หลัก';
    } else {
        $error = false;
    }
    if ($error) {
        include ROOT_PATH.'admin/install/install3.php';
    } else {
        $db_weburl = empty($_SESSION['db_weburl']) ? WEB_URL : $_SESSION['db_weburl'];
        $hostname = empty($_SESSION['hostname']) ? str_replace(array('http://', 'www.'), '', WEB_URL) : $_SESSION['hostname'];
        $db_username = empty($_SESSION['db_username']) ? $config['db_username'] : $_SESSION['db_username'];
        $db_password = empty($_SESSION['db_password']) ? $config['db_password'] : $_SESSION['db_password'];
        $db_server = empty($_SESSION['db_server']) ? $config['db_server'] : $_SESSION['db_server'];
        $db_name = empty($_SESSION['db_name']) ? $config['db_name'] : $_SESSION['db_name'];
        $reply = empty($_SESSION['reply']) ? "no-reply@$baseurl" : $_SESSION['reply'];
        echo '<h2>ค่ากำหนดของฐานข้อมูล</h2>';
        echo '<form method=post action=index.php autocomplete=off>';
        echo '<p>ระบุที่อยู่โดเมนที่ถูกต้องของเว็บไซต์</p>';
        echo '<p class=row><label for=db_weburl>ที่อยู่โดเมน</label><input type=text size=50 id=db_weburl name=db_weburl value="'.$db_weburl.'">&nbsp;&nbsp;<a href="http://gcms.in.th/index.php?module=howto&amp;id=72" target=_blank><img src="'.WEB_URL.'/admin/install/img/help.png" alt=help></a></p>';
        echo '<p class="row comment">ระบุที่อยู่โดเมนของคุณหากแตกต่างจากที่กำหนดให้ เช่นชื่อโดเมนในภาษาอื่น รวม path และ ไม่ต้องมี / ปิดท้าย เช่น http://www.ชื่อไทย.com/gcms</p>';
        echo '<p class=row><label for=redirect>&nbsp;</label><input type=checkbox id=redirect name=redirect value=1>เพิ่มคำสั่งให้ URL ต้องมี www เสมอ (.htaccess, ไม่จำเป็นหากชื่อโดเมนเป็นโดเมนย่อย)</p>';
        echo '<p class=row><label for=hostname>ชื่อโฮสต์</label><input type=text size=50 id=hostname name=hostname value="'.$hostname.'">&nbsp;&nbsp;<a href="http://gcms.in.th/index.php?module=howto&amp;id=173" target=_blank><img src="'.WEB_URL.'/admin/install/img/help.png" alt=help></a></p>';
        echo '<p class="row comment">ระบุชื่อโฮสต์ (Host Name) ซึ่งเป็นชื่อโดเมนในภาษาอังกฤษเท่านั้น เช่นที่อยู่โดเมนเป็น ชื่อไทย.com ต้องระบุชื่อโฮสต์เป็น xn--b3c0a7a4b9b8d0a.com (ไม่ต้องมี http://www)</p>';
        echo '<p>ที่อยู่อีเมลสำหรับจดหมายที่ส่งโดยอัตโนมัติจากระบบ</p>';
        echo '<p class=row><label for=reply>ที่อยู่อีเมล</label><input type=email size=50 id=reply name=reply value="'.$reply.'" required></p>';
        echo '<p class="row comment">ที่อยู่อีเมลสำหรับการส่งจดหมายจากระบบ เช่นการสมัครสมาชิก การลืมรหัสผ่าน ซึ่งมักจะไม่ต้องการการตอบกลับมายังผู้ส่ง ปกติจะเป็น no-reply</p>';
        echo '<p>คุณจะต้องระบุข้อมูลการเชื่อมต่อที่ถูกต้องด้านล่างเพื่อเริ่มดำเนินการติดตั้งฐานข้อมูล&nbsp;&nbsp;<a href="http://gcms.in.th/index.php?module=howto&amp;id=24#setup2" target=_blank><img src="'.WEB_URL.'/admin/install/img/help.png" alt=help></a></p>';
        echo '<p class=row><label for=db_username>ชื่อผู้ใช้</label><input type=text size=50 id=db_username name=db_username value="'.$db_username.'"></p>';
        echo '<p class="row comment">ชื่อผู้ใช้ของ MySQL ของคุณ</p>';
        echo '<p class=row><label for=db_password>รหัสผ่าน</label><input type=text size=50 id=db_password name=db_password value="'.$db_password.'"></p>';
        echo '<p class="row comment">รหัสผ่านของ MySQL ของคุณ</p>';
        echo '<p class=row><label for=db_server>โฮสท์ของฐานข้อมูล</label><input type=text size=50 id=db_server name=db_server value="'.$db_server.'"></p>';
        echo '<p class="row comment">ดาตาเบสเซิร์ฟเวอร์ของคุณ (โฮสท์ส่วนใหญ่ใช้ localhost)</p>';
        echo '<p class=row><label for=db_name>ชื่อฐานข้อมูล</label><input type=text size=50 id=db_name name=db_name value="'.$db_name.'"></p>';
        echo '<p class=row><label for=newdb>&nbsp;</label><input type=checkbox id=newdb name=newdb>สร้างฐานข้อมูลใหม่ (ข้อมูลเดิมจะถูกลบออกทั้งหมด)</p>';
        echo '<p class="row comment">เซิร์ฟเวอร์บางแห่งอาจไม่ยอมให้สร้างฐานข้อมูลใหม่ คุณอาจต้องกำหนดเป็นฐานข้อมูลที่คุณมีอยู่แล้ว</p>';
        echo '<p class=row><label for=prefix>คำนำหน้าตารางฐานข้อมูล</label><input type=text size=50 id=prefix name=prefix value="'.(defined('PREFIX') ? PREFIX : 'gcms').'"></p>';
        echo '<p class="row comment">ใช้สำหรับแยกฐานข้อมูลของ GCMS ออกจากฐานข้อมูลอื่นๆ หากมีการติดตั้งข้อมูลอื่นๆร่วมกันบนฐานข้อมูลนี้ หรือมีการติดตั้ง GCMS มากกว่า 1 ตัว บนฐานข้อมูลนี้ (ภาษาอังกฤษตัวพิมพ์เล็กและตัวเลขเท่านั้น เช่น cms4)</p>';
        echo '<p class=row><label for=import>นำเข้าข้อมูลตัวอย่าง</label><input type=checkbox id=import name=import value=1 checked>&nbsp;เอาตัวเลือกนี้ออกหากต้องการติดตั้ง GCMS โดยไม่มีข้อมูลเริ่มต้นใดๆเลย</p>';
        echo '<input type=hidden name=step value=5>';
        echo '<p><input class=button type=submit value=ติดตั้ง.></p>';
        echo '</form>';
    }
}
