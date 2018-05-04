<?php
// bin/config.php
// ค่ากำหนดของฐานข้อมูล MySQL
$config['db_server'] = "localhost";
$config['db_username'] = "root";
$config['db_password'] = "";
// ชื่อของฐานข้อมูล
$config['db_name'] = "gcms";
// กำหนดการใช้ ajax กับเว็บไซต์
// 0 = ใช้ ajax เป็นบางส่วน เรียกหน้าเว็บผ่าน URL (รองรับการติดตั้ง Adsense)
// 1 = ใช้ ajax กับทุกส่วนของเว็บไซต์
$config['use_ajax'] = 1;
// รูปแบบของ URL
// 0 = index.php?module=xxx
// 1 = xxx.html (mod rewrite)
$config['module_url'] = 1;
$config['web_description'] = 'GCMS Ajax CMS';
$config['web_title'] = 'Gcms';
$config['user_icon_typies'] = array('jpg', 'gif', 'png');
$config['user_icon_h'] = 50;
$config['user_icon_w'] = 50;
// ต้องการยืนยันการสมัครสมาชิกหรือไม่
// กำหนดเป็น 0 หมายถึงไม่ต้องยืนยัน (สมัครแล้วเข้าระบบได้ทันที)
$config['user_activate'] = 1;
// กำหนดการส่งเมล์ของระบบ
// 0 ไม่ได้
// 1 ได้
$config['sendmail'] = 1;
// อีเมลของแอดมินที่สามารถติดต่อได้
$config['noreply_email'] = 'no-reply@localhost.com';
// ค่ากำหนด Mail Server
// enable SMTP authentication (GMAIL 1,0)
$config['email_SMTPAuth'] = 0;
// sets the prefix to the servier (GMAIL ssl)
$config['email_SMTPSecure'] = '';
// sets GMAIL as the SMTP server (smtp.gmail.com)
$config['email_Host'] = 'localhost';
// sets GMAIL as the SMTP server (smtp.gmail.com)
$config['email_Port'] = '25';
// sets GMAIL as the SMTP server (smtp.gmail.com)
// username
$config['email_Username'] = '';
// password
$config['email_Password'] = '';
// คำหยาบ
$config['wordrude'] = array("ashole", "a s h o l e", "a.s.h.o.l.e", "bitch", "b i t c h", "b.i.t.c.h", "shit", "s h i t", "s.h.i.t", "fuck", "dick", "f u c k", "d i c k", "f.u.c.k", "d.i.c.k", "มึง", "มึ ง", "ม ึ ง", "ม ึง", "มงึ", "มึ.ง", "มึ_ง", "มึ-ง", "มึ+ง", "กู", "ควย", "ค ว ย", "ค.ว.ย", "คอ วอ ยอ", "คอ-วอ-ยอ", "ปี้", "เหี้ย", "ไอ้เหี้ย", "เฮี้ย", "ชาติหมา", "ชาดหมา", "ช า ด ห ม า", "ช.า.ด.ห.ม.า", "ช า ติ ห ม า", "ช.า.ติ.ห.ม.า", "สัดหมา", "สัด", "เย็ด", "หี", "สันดาน", "แม่ง", "ระยำ", "ส้นตีน", "แตด");
$config['wordrude_replace'] = 'xxx';
// ชื่อที่ไม่ต้องการให้ สมาชิก มาสมัครไปใช้
$config['member_reserv'] = array('website', 'webmaster', 'webboard', 'howto', 'blogs', 'cms', 'gcms', 'blog', 'gallary', 'module', 'member', 'members', 'listmember', 'register', 'edit', 'forgot', 'category', 'main', 'goragod', 'about', 'aboutus', 'editprofile', 'recover', 'tag');
// จำนวนหลักของ counter
$config['counter_digit'] = 6;
// ทดเวลาสำหรับ Server เพื่อให้เวลาตรงกัน (บวก หรือ ลบ)
$config['hour'] = +0;
// ภาษาที่ติดตั้ง
$config['languages'] = array('th');
// skin ที่เลือกใช้
$config['skin'] = 'store';
// สถานะของสมาชิก
$config['member_status']['0'] = 'สมาชิกทั่วไป';
$config['member_status']['1'] = 'แอดมิน';
$config['member_status']['2'] = 'ผู้ช่วยแอดมิน';
$config['color_status']['0'] = '#006600';
$config['color_status']['1'] = '#FF0000';
$config['color_status']['2'] = '#0E74FF';
// Charset ของจดหมาย (utf-8,tis-620)
$config['email_charset'] = 'tis-620';
// การใช้งาน phpmailer
// 1 ส่งเมล์ โดยใช้ phpmailer
// 0 ส่งเมล์ โดยใช้ฟังก์ชั่นของ PHP
$config['email_use_phpMailer'] = 1;
// กำหนดช่วงเวลาการแคชหน้าเว็บ (index)
$config['index_page_cache'] = 5;
// cron
$config['cron'] = 1;
// กำหนดวิธีการเข้าระบบ 0=เข้ระบบได้ทั่วไป,1=สมาชิกจะสามารถเข้าระบบได้เพียง IP เดียว ต่อช่วงเวลาหนึ่งเท่านั้น
$config['member_only_ip'] = 0;
// default mime type
$config['mimeTypes']['swf'] = 'application/x-shockwave-flash';
$config['mimeTypes']['gif'] = 'image/gif';
$config['mimeTypes']['jpg'] = 'image/jpeg';
$config['mimeTypes']['png'] = 'image/png';
// admin theme
$config['admin_skin'] = 'v8';
// demo mode
$config['demo_mode'] = 0;
// RSS Tab
$config['rss_tabs']['1']['0'] = 'http://gcms.in.th/news.rss';
$config['rss_tabs']['1']['1'] = 'GCMS News';
$config['rss_tabs']['1']['2'] = '';
$config['rss_tabs']['1']['3'] = 2;
$config['rss_tabs']['1']['4'] = 2;
$config['rss_tabs']['2']['0'] = 'http://news.thaipbs.or.th/rss/education.xml';
$config['rss_tabs']['2']['1'] = 'thaipbs';
$config['rss_tabs']['2']['2'] = '';
$config['rss_tabs']['2']['3'] = 2;
$config['rss_tabs']['2']['4'] = 2;
