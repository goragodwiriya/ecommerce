<?php
// admin/mailwrite_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// ตรวจสอบ referer และ แอดมิน
if (gcms::isReferer() && gcms::isAdmin()) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    $error = false;
    $input = false;
    $save = array();
    // id ของอีเมล (0 = ใหม่)
    $id = (int)$_POST['email_id'];
    if ($id > 0) {
      // email ที่แก้ไข
      $email = $db->getRec(DB_EMAIL_TEMPLATE, $id);
    } else {
      // อีเมลที่สร้างใหม่ สำหรับระบบจดหมายเวียน
      $save['module'] = 'mailmerge';
      $save['email_id'] = 0;
    }
    if ($id > 0 && !$email) {
      $ret['error'] = 'ACTION_ERROR';
    } else {
      // ค่าที่ส่งมา
      $save['language'] = $db->sql_trim_str($_POST['email_language']);
      $save['from_email'] = $db->sql_trim_str($_POST['email_from_email']);
      $save['subject'] = $db->sql_trim_str($_POST['email_subject']);
      // มีการแก้ไขภาษา ให้บันทึกเป็นรายการใหม่
      $ret['ret_email_language'] = '';
      if ($id > 0 && $save['language'] != $email['language']) {
        // ตรวจสอบว่ามีรายการในภาษาที่เลือกหรือไม่
        $sql = "SELECT `module`,`email_id`,`name` FROM `".DB_EMAIL_TEMPLATE."`";
        $sql .= " WHERE `email_id`='$email[email_id]' AND `module`='$email[module]' AND `language`='$save[language]'";
        $sql .= " LIMIT 1";
        $search = $db->customQuery($sql);
        if (sizeof($search) == 0) {
          $save['name'] = $email['name'];
          $save['email_id'] = $email['email_id'];
          $save['module'] = $email['module'];
          $id = 0;
        } else {
          $error = 'EMAIL_LANGUAG_EXISTS';
          $ret['ret_email_language'] = 'EMAIL_LANGUAG_EXISTS';
        }
      }
      if (!$error) {
        $patt = array();
        $replace = array();
        // หน้าว่างๆ
        $patt[] = '/^(&nbsp;|\s){0,}<br[\s\/]+?>(&nbsp;|\s){0,}$/iu';
        $replace[] = '';
        // ตัด PHP
        $patt[] = '/<\?(.*?)\?>/su';
        $replace[] = '';
        // ตัด Script
        $patt[] = '@<script[^>]*?>.*?</script>@siu';
        $replace[] = '';
        $save['detail'] = $db->sql_quote(preg_replace($patt, $replace, $_POST['email_detail']));
        // ตรวจสอบค่าที่ส่งมา
        // copy_to
        $emails = array();
        foreach (explode(',', $_POST['email_copy_to']) AS $email) {
          $email = trim($email);
          if ($email != '') {
            if (gcms::validMail($email)) {
              $emails[] = $email;
            } else {
              $error = true;
            }
          }
        }
        if ($error) {
          $ret['ret_email_copy_to'] = 'REGISTER_INVALID_EMAIL';
          $input = !$error ? 'email_copy_to' : $input;
          $error = !$error ? 'REGISTER_INVALID_EMAIL' : $error;
        } else {
          $save['copy_to'] = implode(',', $emails);
          $ret['ret_email_copy_to'] = '';
        }
        // from_email
        if ($save['from_email'] != '' && !gcms::validMail($save['from_email'])) {
          $ret['ret_email_from_email'] = 'REGISTER_INVALID_EMAIL';
          $input = !$error ? 'email_from_email' : $input;
          $error = !$error ? 'REGISTER_INVALID_EMAIL' : $error;
        } else {
          $ret['ret_email_from_email'] = '';
        }
        // subject
        if ($save['subject'] == '') {
          $ret['ret_email_subject'] = 'TOPIC_EMPTY';
          $input = !$error ? 'email_subject' : $input;
          $error = !$error ? 'TOPIC_EMPTY' : $error;
        } else {
          $ret['ret_email_subject'] = '';
        }
      }
      if (!$error) {
        $save['last_update'] = $mmktime;
        if ($id == 0) {
          // ใหม่
          $id = $db->add(DB_EMAIL_TEMPLATE, $save);
        } else {
          // แก้ไข
          $db->edit(DB_EMAIL_TEMPLATE, $id, $save);
        }
        $ret['lastupdate'] = gcms::mktime2date($mmktime);
        $ret['email_copy_to'] = rawurlencode($save['copy_to']);
        $ret['email_id'] = $id;
        $ret['error'] = 'SAVE_COMPLETE';
        $ret['location'] = 'back';
      } else {
        // คืนค่า input ตัวแรกที่ error
        if ($input) {
          $ret['input'] = $input;
        }
        $ret['error'] = $error;
      }
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
