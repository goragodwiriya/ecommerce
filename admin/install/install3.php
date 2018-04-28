<?php
if (INSTALL_INIT == 'install') {
  $password = $_SESSION['password'] == '' ? 'admin' : $_SESSION['password'];
  $email = $_SESSION['email'] == '' ? 'admin@localhost.com' : $_SESSION['email'];
  echo '<h2>ข้อมูลสมาชิกผู้ดูแลระบบ</h2>';
  echo '<p>กรอกข้อมูลส่วนตัวสำหรับการ Login ของผู้ดูแลระบบ</p>';
  if ($error) {
    echo '<p class=error>'.$error.'</p>';
  }
  echo '<form method=post action=index.php autocomplete=off>';
  echo '<p class=row><label for=email>ที่อยู่อีเมล</label><input type=email size=70 id=email name=email maxlength=255 value="'.$email.'" autofocus required></p>';
  echo '<p class="row comment">ที่อยู่อีเมลสำหรับผู้ดูแลระบบสูงสุด ใช้ในการการเข้าระบบ</p>';
  echo '<p class=row><label for=password>รหัสผ่าน</label><input type=text size=70 id=password name=password maxlength=20 value="'.$password.'" required></p>';
  echo '<p class="row comment">ภาษาอังกฤษตัวพิมพ์เล็กและตัวเลข 4-8 หลัก</p>';
  echo '<input type=hidden name=step value=4>';
  echo '<p><input class=button type=submit value="ดำเนินการต่อ."></p>';
  echo '</form>';
}
